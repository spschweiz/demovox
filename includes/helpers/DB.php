<?php

namespace Demovox;

/**
 * The DB plugin class.
 * Handles DB access (by default table 'demovox_signatures') and en-/decryption
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class DB
{
	const TABLE_SIGN = 1;
	const TABLE_MAIL = 2;
	private static $tableNameSignatures = 'demovox_signatures';
	private static $tableNameMails = 'demovox_mails';
	private static $fieldNameEncrypted = 'is_encrypted';
	/**
	 * Before adding field here, check if it is set with self::update(). If yes, $isEncrypted has to be passed
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
	 * @param null|int $table
	 * @param string|null $sqlAppend Append SQL statements
	 * @return object|null Database query results
	 */
	public static function getRow($select, $where = null, $table = null, $sqlAppend = null)
	{
		global $wpdb;

		$sql = self::prepareSelect($select, $table);
		if ($where) {
			$sql .= " WHERE " . $where;
		}
		if ($sqlAppend) {
			$sql .= ' ' . $sqlAppend;
		}
		$row = $wpdb->get_row($sql);
		$row = self::decryptRow($row);

		return $row;
	}

	/**
	 * Select multiple rows from demovox table
	 *
	 * @param array $select Fields to select
	 * @param string|null $where SQL where statement
	 * @param null|int $table
	 * @param string|null $sqlAppend Append SQL statements
	 * @return array|object|null Database query results
	 */
	public static function getResults($select, $where = null, $table = null, $sqlAppend = null)
	{
		global $wpdb;
		$sql = self::prepareSelect($select, $table);
		if ($where) {
			$sql .= ' WHERE ' . $where;
		}
		if ($sqlAppend) {
			$sql .= ' ' . $sqlAppend;
		}
		$results = $wpdb->get_results($sql);
		foreach ($results as &$result) {
			$result = self::decryptRow($result);
		}
		return $results;
	}

	/**
	 * Count results for a where statement
	 *
	 * @param null|string $where
	 * @param null|int $table
	 * @return int
	 */
	public static function count($where = null, $table = null)
	{
		global $wpdb;
		$tableName = self::getTableName($table);
		if ($where !== null) {
			$where = 'WHERE ' . $where;
		}
		$count = $wpdb->get_var('SELECT COUNT(ID) as count FROM `' . $tableName . '`' . $where);
		return intval($count);
	}

	/**
	 * Delete entries for a where statement
	 *
	 * @param null|array $where
	 * @param null|int $table
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function delete($where = null, $table = null)
	{
		global $wpdb;
		$tableName = self::getTableName($table);
		if ($tableName === self::$tableNameSignatures) {
			return self::updateStatus(['is_deleted' => 1], $where, $table);
		} else {
			return $wpdb->delete($tableName, $where);
		}
	}

	/**
	 * @param $data
	 * @param null|int $table
	 * @return false|int
	 */
	public static function insert($data, $table = null)
	{
		global $wpdb;
		if (self::isEncryptionEnabled() && self::isTableEncAllowed($table)) {
			$row[self::$fieldNameEncrypted] = self::getEncryptionMode();
			$data = self::encryptRow($data);
		}
		return $wpdb->insert(
			self::getTableName($table),
			$data
		);
	}

	/**
	 * @param array $data
	 * @param array $where
	 * @param false|int $isEncrypted
	 * @param null|int $table
	 * @return false|int
	 */
	public static function update($data, $where, $isEncrypted, $table = null)
	{
		global $wpdb;
		if ($isEncrypted && self::isTableEncAllowed($table)) {
			$data = self::encryptRow($data);
		}
		return $wpdb->update(
			self::getTableName($table),
			$data,
			$where
		);
	}

	/**
	 * Update status fields which don't require encryption
	 * @param array $data
	 * @param array $where
	 * @param null|int $table
	 * @return false|int
	 */
	public static function updateStatus($data, $where, $table = null)
	{
		return self::update($data, $where, false, $table);
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
	 * @param null|int $table
	 * @return array
	 */
	static function createUpdateTable($tableDefinition, $table = null)
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$sql = 'CREATE TABLE ' . self::getTableName($table)
			. ' (' . $tableDefinition . ') '
			. $charsetCollate . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$dbResult = dbDelta($sql);
		return $dbResult;
	}

	/**
	 * @return bool|false|int
	 */
	public static function dropTables()
	{
		global $wpdb;

		$tableName = self::getTableName();
		$drop = $wpdb->query("DROP TABLE IF EXISTS `{$tableName}`");

		$tableNameMail = self::getTableName(self::TABLE_MAIL);
		$dropMail = $wpdb->query("DROP TABLE IF EXISTS `{$tableNameMail}`");

		return $drop && $dropMail;
	}

	/**
	 * @param null|int $table
	 * @return string
	 */
	public static function getTableName($table = null)
	{
		global $wpdb;
		if ($table === self::TABLE_MAIL) {
			$name = self::$tableNameMails;
		} else {
			$name = self::$tableNameSignatures;
		}
		return $wpdb->prefix . $name;
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
	 * @param string $value
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
	 * @param string $value
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

	protected static function isTableEncAllowed($table = null)
	{
		return ($table === null || $table === self::TABLE_SIGN);
	}

	/**
	 * add "is_encrypted" if required and convert to string
	 *
	 * @param array $select
	 * @param null|int $table
	 * @return string
	 */
	protected static function prepareSelect($select, $table = null)
	{
		if (self::isTableEncAllowed($table)) {
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
		}

		$tableName = self::getTableName($table);
		$select = implode(', ', $select);
		$sql = "SELECT " . $select . " FROM " . $tableName;

		return $sql;
	}

	/**
	 * @param array|object|null $row
	 * @return array|object|null
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