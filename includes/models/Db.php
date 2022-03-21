<?php

namespace Demovox;

/**
 * The DB service class.
 * Handles DB access (by default table 'demovox_signatures') and en-/decryption
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes/helpers
 * @author     SP Schweiz
 */
abstract class Db
{

	/**
	 * @var string
	 */
	protected string $tableName;
	/**
	 * @var string
	 */
	protected string $tableDefinition;
	protected static string $fieldNameEncrypted = 'is_encrypted';


	/**
	 * Before adding field here, check if it is set with $this->update(). If yes, $isEncrypted has to be passed
	 *
	 * @var array|null
	 */
	protected ?array $encryptFields;

	/**
	 * Select one row from demovox table
	 *
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return \stdClass|null Database query results
	 */
	public function getRow(array $select, ?string $where = null, ?string $sqlAppend = null)
	{
		global $wpdb;

		$sql = $this->prepareSelect($select);
		if ($where) {
			$sql .= " WHERE " . $where;
		}
		if ($sqlAppend) {
			$sql .= ' ' . $sqlAppend;
		}
		$row = $wpdb->get_row($sql);
		if ($row === null) {
			return null;
		}
		return $this->decryptRow($row);
	}

	public function getTableDefinition(): string
	{
		return $this->tableDefinition;
	}

	abstract public function getResults(array $select, ?string $where = null, ?string $sqlAppend = null): array;

	/**
	 * Select multiple rows from demovox table
	 *
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return \stdClass[] Database query results
	 */
	public function getResultsRaw(array $select, ?string $where = null, ?string $sqlAppend = null): array
	{
		global $wpdb;
		$sql = $this->prepareSelect($select);
		if ($where) {
			$sql .= ' WHERE ' . $where;
		}
		if ($sqlAppend) {
			$sql .= ' ' . $sqlAppend;
		}
		$results = $wpdb->get_results($sql);
		if(!$results){
			return [];
		}
		foreach ($results as &$row) {
			$row = $this->decryptRow($row);
		}
		return $results;
	}

	/**
	 * Count results for a where statement
	 *
	 * @param string|null $where
	 *
	 * @return int
	 */
	public function count(?string $where = null): int
	{
		global $wpdb;
		if ($where !== null) {
			$where = 'WHERE ' . $where;
		}
		$tableName = $this->getWpTableName();
		$count     = $wpdb->get_var('SELECT COUNT(ID) as count FROM `' . $tableName . '`' . $where);
		return intval($count);
	}

	/**
	 * Delete entries for a where statement
	 *
	 * @param array $where
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function delete(array $where = null)
	{
		global $wpdb;
		if ($this instanceof DbSignatures) {
			return $this->updateStatus(['is_deleted' => 1], $where);
		} else {
			$wpTableName = $this->getWpTableName();
			return $wpdb->delete($wpTableName, $where);
		}
	}

	/**
	 * @param Dto   $dto
	 *
	 * @return false|int
	 */
	public function insert(Dto $dto)
	{
		global $wpdb;
		if (method_exists($dto, 'prepareInsert')) {
			$dto->prepareInsert();
		}
		$data = $dto->getDataArr();
		if ($this->isTableEncAllowed()) {
			if (Crypt::isEncryptionEnabled()) {
				$data[self::$fieldNameEncrypted] = Crypt::getEncryptionMode();
				$data                            = $this->encryptRow($data);
			} else {
				$data[self::$fieldNameEncrypted] = 0;
			}
		}
		return $wpdb->insert(
			$this->getWpTableName(),
			$data
		);
	}

	/**
	 * @param Dto|array $data
	 * @param array     $where
	 * @param int|null  $isEncrypted
	 *
	 * @return false|int
	 */
	public function update($data, array $where, ?int $isEncrypted = null)
	{
		global $wpdb;
		if(!is_array($data)) {
			$data = $data->getDataArr();
		}
		if ($isEncrypted && $this->isTableEncAllowed()) {
			$data = $this->encryptRow($data);
		}
		return $wpdb->update(
			$this->getWpTableName(),
			$data,
			$where
		);
	}

	/**
	 * Update status fields (avoid encryption)
	 *
	 * @param Dto|array $data
	 * @param array     $where
	 * @return false|int
	 */
	public function updateStatus($data, array $where)
	{
		return $this->update($data, $where, false);
	}

	/**
	 * Truncate table
	 *
	 * @return bool success
	 */
	public function truncate(): bool
	{
		global $wpdb;
		$res = $wpdb->query('TRUNCATE TABLE ' . $this->getWpTableName());
		return $res !== false;
	}

	/**
	 * Run SQL query
	 *
	 * @param string $sql
	 * @return int|bool success | Number of rows affected
	 */
	public static function query(string $sql)
	{
		global $wpdb;
		return $wpdb->query($sql);
	}

	/**
	 * @return int
	 */
	public static function getInsertId() : int
	{
		global $wpdb;
		return $wpdb->insert_id;
	}

	/**
	 * @return string
	 */
	public static function getLastError() : string
	{
		global $wpdb;
		return $wpdb->last_error;
	}

	/**
	 * @return string
	 */
	public static function getLastQuery() : string
	{
		global $wpdb;
		return $wpdb->last_query;
	}

	/**
	 * @param string $tableDefinition   Has to be exactly in the undocumented wordpress internal format or it will most likely fail in a
	 *                                  random way Never quote field names
	 *                                  "It is always safe to ensure that all keyword are separated by one space and between each commas
	 *                                  there shouldn't be any spacing" https://www.hungred.com/how-to/wordpress-dbdelta-function/
	 *
	 * @return array
	 */
	public function createMissingTables(string $tableDefinition): array
	{
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();
		$wpTableName = $this->getWpTableName();
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpTableName . ' (' . $tableDefinition . ') ' . $charsetCollate . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		return dbDelta($sql);
	}

	/**
	 * @return bool
	 */
	public function dropTable(): bool
	{
		global $wpdb;

		$wpTableName = $this->getWpTableName();
		return $wpdb->query("DROP TABLE IF EXISTS `$wpTableName`");
	}

	/**
	 * @return string
	 */
	protected function getTableName(): string
	{
		return $this->tableName;
	}

	/**
	 * @return string
	 */
	public function getWpTableName(): string
	{
		global $wpdb;
		return $wpdb->prefix . $this->getTableName();
	}

	/**
	 * @return bool
	 */
	protected function isTableEncAllowed(): bool
	{
		return $this instanceof DbSignatures;
	}

	/**
	 * add "is_encrypted" if required and convert to string
	 *
	 * @param array $select
	 *
	 * @return string
	 */
	protected function prepareSelect(array $select): string
	{
		if ($this->isTableEncAllowed()) {
			$decryptRequired = false;
			foreach ($select as $fieldName) {
				if (in_array($fieldName, $this->encryptFields)) {
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

		$tableName = $this->getWpTableName();
		$select    = implode(', ', $select);
		return 'SELECT ' . $select . ' FROM ' . $tableName;
	}

	/**
	 * @param array|object|null $row
	 *
	 * @return array|object|null
	 */
	protected function decryptRow($row)
	{
		$isEncrypted = false;
		if (is_object($row)) {
			$isEncrypted = isset($row->{self::$fieldNameEncrypted}) && $row->{self::$fieldNameEncrypted};
		} elseif (is_array($row)) {
			$isEncrypted = isset($row[self::$fieldNameEncrypted]) && $row[self::$fieldNameEncrypted];
		}
		if ($isEncrypted) {
			foreach ($row as $fieldName => &$value) {
				if ($value && in_array($fieldName, $this->encryptFields)) {
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
	protected function encryptRow(array $row): array
	{
		foreach ($row as $fieldName => &$value) {
			if ($value && in_array($fieldName, $this->encryptFields)) {
				$value = Crypt::encrypt($value);
			}
		}

		return $row;
	}
}
