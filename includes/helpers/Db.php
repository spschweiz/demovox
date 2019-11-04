<?php

namespace Demovox;

/**
 * The DB plugin class.
 * Handles DB access (by default table 'demovox_signatures') and en-/decryption
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     Fabian Horlacher / SP Schweiz
 */
class Db
{
	const TABLE_SIGN  = 'demovox_signatures';
	const TABLE_MAILS = 'demovox_mails';
	/**
	 * @var null|string
	 */
	protected static $tableName = null;
	private static $fieldNameEncrypted = 'is_encrypted';
	/**
	 * Before adding field here, check if it is set with self::update(). If yes, $isEncrypted has to be passed
	 *
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

	/**
	 * Select one row from demovox table
	 *
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return object|null Database query results
	 */
	public static function getRow($select, $where = null, $sqlAppend = null)
	{
		global $wpdb;

		$sql = self::prepareSelect($select);
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
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return array|object|null Database query results
	 */
	public static function getResults($select, $where = null, $sqlAppend = null)
	{
		global $wpdb;
		$sql = self::prepareSelect($select);
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
	 *
	 * @return int
	 */
	public static function count($where = null)
	{
		global $wpdb;
		if ($where !== null) {
			$where = 'WHERE ' . $where;
		}
		$tableName = self::getTableName();
		$count     = $wpdb->get_var('SELECT COUNT(ID) as count FROM `' . $tableName . '`' . $where);
		return intval($count);
	}

	/**
	 * Delete entries for a where statement
	 *
	 * @param null|array $where
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function delete($where = null)
	{
		global $wpdb;
		$tableName = self::getTableName();
		if ($tableName === self::TABLE_SIGN) {
			return self::updateStatus(['is_deleted' => 1], $where);
		} else {
			return $wpdb->delete($tableName, $where);
		}
	}

	/**
	 * @param          $data
	 *
	 * @return false|int
	 */
	public static function insert($data)
	{
		global $wpdb;
		if (self::isTableEncAllowed()) {
			if (Crypt::isEncryptionEnabled()) {
				$data[self::$fieldNameEncrypted] = Crypt::getEncryptionMode();
				$data                            = self::encryptRow($data);
			} else {
				$data[self::$fieldNameEncrypted] = 0;
			}
		}
		return $wpdb->insert(
			self::getTableName(),
			$data
		);
	}

	/**
	 * @param array     $data
	 * @param array     $where
	 * @param false|int $isEncrypted
	 *
	 * @return false|int
	 */
	public static function update($data, $where, $isEncrypted)
	{
		global $wpdb;
		if ($isEncrypted && self::isTableEncAllowed()) {
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
	 *
	 * @param array $data
	 * @param array $where
	 *
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
	 * @param string $tableDefinition   Has to be exactly in the undocumented wordpress internal format or it will most likely fail in a
	 *                                  random way Never quote field names
	 *                                  "It is always safe to ensure that all keyword are separated by one space and between each commas
	 *                                  there shouldn't be any spacing" https://www.hungred.com/how-to/wordpress-dbdelta-function/
	 *
	 * @return array
	 */
	static function createUpdateTable($tableDefinition, $tableName)
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$sql            = 'CREATE TABLE ' . $tableName . ' (' . $tableDefinition . ') ' . $charsetCollate . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$dbResult = dbDelta($sql);
		return $dbResult;
	}

	/**
	 * @return bool|false|int
	 */
	public static function dropAllDemovoxTables()
	{
		global $wpdb;

		$tableName = self::TABLE_SIGN;
		$drop      = $wpdb->query("DROP TABLE IF EXISTS `{$tableName}`");

		$tableNameMail = self::TABLE_MAILS;
		$dropMail      = $wpdb->query("DROP TABLE IF EXISTS `{$tableNameMail}`");

		return $drop && $dropMail;
	}

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		if (self::$tableName === null) {
			throw new \Exception('Table ID not set');
		}
		return self::$tableName;
	}

	protected static function isTableEncAllowed()
	{
		if (self::$tableName === null) {
			throw new \Exception('Table ID not set');
		}
		return self::$tableName === self::TABLE_SIGN;
	}

	/**
	 * add "is_encrypted" if required and convert to string
	 *
	 * @param array $select
	 *
	 * @return string
	 */
	protected static function prepareSelect($select)
	{
		if (self::isTableEncAllowed()) {
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

		$tableName = self::getTableName();
		$select    = implode(', ', $select);
		$sql       = 'SELECT ' . $select . ' FROM ' . $tableName;

		return $sql;
	}

	/**
	 * @param array|object|null $row
	 *
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
					$value = Crypt::decrypt($value);
				}
			}
		}

		return $row;
	}

	/**
	 * @param array $row
	 *
	 * @return array
	 */
	protected static function encryptRow($row)
	{
		foreach ($row as $fieldName => &$value) {
			if ($value && in_array($fieldName, self::$encryptFields)) {
				$value = Crypt::encrypt($value);
			}
		}

		return $row;
	}
}