<?php

namespace Demovox;

/**
 * The DB plugin class.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class DB
{
	private static $tableName = 'demovox_signatures';
	private static $fieldNameEncrypted = 'is_encrypted';
	/**
	 * Before adding field here, check if it is set with DB::update(). If yes, $isEncrypted has to be passed
	 * @var array
	 */
	private static $encryptFields = [
		'first_name',
		'last_name',
		'birth_date',
		'mail',
		'phone',
		'street',
		'street_no',
		'zip',
		'city',
		'gde_no',
		'gde_zip',
		'gde_name',
		'gde_canton',
		'ip_address',
	];
	private static $exportFields = [
		'ID'                  => 'id',
		'language'            => 'Language',
		'first_name'          => 'First Name',
		'last_name'           => 'Last Name',
		'birth_date'          => 'Birth Date',
		'mail'                => 'Email',
		'phone'               => 'Tel',
		'country'             => 'Country',
		'street'              => 'Street',
		'street_no'           => 'Number',
		'zip'                 => 'Zip Code',
		'city'                => 'City',
		'gde_no'              => 'Commune - Number',
		'gde_zip'             => 'Commune - Zip',
		'gde_name'            => 'Commune - Name',
		'gde_canton'          => 'Commune - Canton',
		'is_optin'            => 'Wants Contact',
		'is_sheet_received'   => 'Received signatures',
		'creation_date'       => 'Creation Date',
		'sheet_received_date' => 'Sheet received Date',
		'serial'              => 'Serial (QR code)',
	];

	/**
	 * @return array
	 */
	public static function getExportFields()
	{
		return self::$exportFields;
	}

	/**
	 * @param bool $publicValue
	 * @return string
	 */
	public static function countSignatures($publicValue = true)
	{
		$count = self::count('is_deleted = 0 AND is_step2_done = 1');
		if ($publicValue) {
			$count += intval(Config::getValue('add_count'));
		}
		return $count ?: '0';
	}

	/**
	 * Select one row from demovox table
	 *
	 * @param array $select Fields to select
	 * @param string|null $where SQL where statement
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * @return array|object|null Database query results
	 */
	public static function getRow($select, $where = null, $output = OBJECT)
	{
		global $wpdb;
		$sql = self::prepareSelect($select);
		if ($where) {
			$sql .= " WHERE " . $where;
		}
		$row = $wpdb->get_row($sql, $output);
		$row = self::decryptRow($row);
		return $row;
	}

	/**
	 * Select multiple rows from demovox table
	 *
	 * @param array $select Fields to select
	 * @param string|null $where SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * @return array|object|null Database query results
	 */
	public static function getResults($select, $where = null, $sqlAppend = null, $output = OBJECT)
	{
		global $wpdb;
		$sql = self::prepareSelect($select);
		if ($where) {
			$sql .= " WHERE " . $where;
		}
		if ($sqlAppend) {
			$sql .= ' ' . $sqlAppend;
		}
		$results = $wpdb->get_results($sql, $output);
		foreach ($results as &$result) {
			$result = self::decryptRow($result);
		}
		return $results;
	}

	/**
	 * Count results for a where statement
	 *
	 * @param null|string $where
	 * @return int
	 */
	public static function count($where = null)
	{
		global $wpdb;
		$tableName = self::getTableName();
		if ($where !== null) {
			$where = 'WHERE ' . $where;
		}
		$count = $wpdb->get_var('SELECT COUNT(ID) as count FROM `' . $tableName . '`' . $where);
		return intval($count);
	}

	/**
	 * @param $data
	 * @return false|int
	 */
	public static function insert($data)
	{
		global $wpdb;
		if (self::isEncryptionEnabled()) {
			$row[self::$fieldNameEncrypted] = self::getEncryptionMode();
			$data = self::encryptRow($data);
		}
		return $wpdb->insert(
			self::getTableName(),
			$data
		);
	}

	/**
	 * @param array $data
	 * @param array $where
	 * @param bool $isEncrypted
	 * @return false|int
	 */
	public static function update($data, $where, $isEncrypted)
	{
		global $wpdb;
		if ($isEncrypted) {
			$data = self::encryptRow($data);
		}
		return $wpdb->update(
			self::getTableName(),
			$data,
			$where
		);
	}

	/**
	 * Update status fields which don't require encryption
	 * @param array $data
	 * @param array $where
	 * @return false|int
	 */
	public static function updateStatus($data, $where)
	{
		return self::update($data, $where, false);
	}

	/**
	 * @return int
	 */
	public static function getInsertId()
	{
		global $wpdb;
		return $wpdb->insert_id;
	}

	/**
	 * @return string
	 */
	public static function getError()
	{
		global $wpdb;
		return $wpdb->last_error;
	}

	/**
	 * @param string $tableDefinition Has to be exactly in the undocumented wordpress internal format or it will most likely fail in a random way
	 *        Never quote field names
	 *        "It is always safe to ensure that all keyword are separated by one space and between each commas there shouldn't be any spacing"
	 *        https://www.hungred.com/how-to/wordpress-dbdelta-function/
	 * @return array
	 */
	static function createUpdateTable($tableDefinition)
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$sql = 'CREATE TABLE ' . DB::getTableName()
			. ' (' . $tableDefinition . ') '
			. $charsetCollate . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$dbResult = dbDelta($sql);
		return $dbResult;
	}

	/**
	 * @return bool|false|int
	 */
	public static function dropTable()
	{
		global $wpdb;
		$tableName = DB::getTableName();
		return $wpdb->query("DROP TABLE IF EXISTS `{$tableName}`");
	}

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix . self::$tableName;
	}

	/**
	 * @return bool
	 */
	protected static function isEncryptionEnabled()
	{
		return self::getEncryptionMode() !== 'disabled';
	}

	/**
	 * @return bool
	 */
	protected static function getEncryptionMode()
	{
		return Config::getValue('encrypt_signees');
	}

	/**
	 * @param $value
	 * @return string|null
	 */
	public static function encrypt($value)
	{
		if (!defined('DEMOVOX_ENC_KEY')) {
			Core::showError('Encryption failed: Constant DEMOVOX_ENC_KEY is not defined in wp-config.php', 500);
		}
		try {
			$key = \Defuse\Crypto\Key::loadFromAsciiSafeString(DEMOVOX_ENC_KEY);
			return $encyrpted = \Defuse\Crypto\Crypto::encrypt($value, $key);
		} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
			Core::showError('Encryption failed: EnvironmentIsBrokenException (' . $e->getMessage() . ')', 500);
		} catch (\TypeError $e) {
			Core::showError('Encryption failed: TypeError (' . $e->getMessage() . ' Value:' . $value . ')', 500);
		} catch (\Defuse\Crypto\Exception\BadFormatException $e) {
			Core::showError('Decryption failed: BadFormatException (' . $e->getMessage() . ')', 500);
		}
		return null;
	}

	/**
	 * @param $value
	 * @return string|null
	 */
	public static function decrypt($value)
	{
		if (!defined('DEMOVOX_ENC_KEY')) {
			Core::showError('Decryption failed: Constant DEMOVOX_ENC_KEY is not defined in wp-config.php', 500);
		}
		try {
			$key = \Defuse\Crypto\Key::loadFromAsciiSafeString(DEMOVOX_ENC_KEY);
			return $decyrpted = \Defuse\Crypto\Crypto::decrypt($value, $key);
		} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
			Core::showError(
				'Decryption failed: WrongKeyOrModifiedCiphertextException (' . $e->getMessage() . ' Value:' . $value . ')',
				500);
		} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
			Core::showError('Decryption failed: EnvironmentIsBrokenException (' . $e->getMessage() . ')', 500);
		} catch (\TypeError $e) {
			Core::showError('Decryption failed: TypeError (' . $e->getMessage() . ')', 500);
		} catch (\Defuse\Crypto\Exception\BadFormatException $e) {
			Core::showError('Decryption failed: BadFormatException (' . $e->getMessage() . ')', 500);
		}
		return null;
	}

	/**
	 * add "is_encrypted" if required and convert to string
	 *
	 * @param $select
	 * @return string
	 */
	protected static function prepareSelect($select)
	{
		$decryptRequired = false;
		foreach ($select as $fieldName) {
			if (in_array($fieldName, self::$encryptFields)) {
				$decryptRequired = true;
				break;
			}
		}
		if ($decryptRequired) {
			if (!in_array(self::$fieldNameEncrypted, $select)) {
				$select[] = self::$fieldNameEncrypted;
			}
		}

		$select = implode(', ', $select);
		return "SELECT " . $select . " FROM " . DB::getTableName();;
	}

	/**
	 * @param array|object|null $row
	 * @return string
	 */
	protected static function decryptRow($row)
	{
		$isEncrypted = false;
		if (is_object($row)) {
			$isEncrypted = isset($row->{self::$fieldNameEncrypted}) && $row->{self::$fieldNameEncrypted};
		} elseif (is_array($row)) {
			$isEncrypted = isset($row[self::$fieldNameEncrypted]) && $row[self::$fieldNameEncrypted];
		}
		if ($isEncrypted) {
			foreach ($row as $fieldName => &$value) {
				if ($value && in_array($fieldName, self::$encryptFields)) {
					$value = self::decrypt($value);
				}
			}
		}

		return $row;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected static function encryptRow($row)
	{
		foreach ($row as $fieldName => &$value) {
			if ($value && in_array($fieldName, self::$encryptFields)) {
				$value = self::encrypt($value);
			}
		}

		return $row;
	}
}