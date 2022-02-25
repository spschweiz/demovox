<?php

namespace Demovox;

class DbSignatures extends Db
{
	const WHERE_OPTIN              = 0;
	const WHERE_FINISHED           = 5;
	const WHERE_FINISHED_IN_SCOPE  = 1;
	const WHERE_FINISHED_OUT_SCOPE = 2;
	const WHERE_UNFINISHED         = 3;
	const WHERE_DELETED            = 4;

	/**
	 * @var string
	 */
	protected string $tableName = 'demovox_signatures';

	protected string $tableDefinition = '
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          collection_ID int UNSIGNED NOT NULL,
          guid char(36) NOT NULL,
          serial char(6) NULL,
          language char(2) NOT NULL,
          ip_address char(232) NULL,
          title varchar(10) NULL,
          first_name varchar(678) NOT NULL,
          last_name varchar(678) NOT NULL,
          birth_date varchar(188) NULL,
          mail varchar(424) NOT NULL,
          phone varchar(296) NULL,
          country char(2) NULL,
          street varchar(422) NULL,
          street_no varchar(188) NULL,
          zip varchar(200) NULL,
          city varchar(296) NULL,
          gde_no varchar(178) NULL,
          gde_zip varchar(176) NULL,
          gde_name varchar(258) NULL,
          gde_canton varchar(172) NULL,
          is_optin tinyint(4) NULL,
          is_step2_done tinyint(4) DEFAULT 0 NOT NULL,
          is_outside_scope tinyint(4) DEFAULT 0 NOT NULL,
          is_sheet_received tinyint(4) DEFAULT 0 NOT NULL,
          is_exported tinyint(4) DEFAULT 0 NOT NULL,
          is_encrypted tinyint(4) DEFAULT 0 NOT NULL,
          is_deleted tinyint(4) DEFAULT 0 NOT NULL,
          state_confirm_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_sheet_sent tinyint(4) DEFAULT 0 NOT NULL,
          state_remind_signup_sent tinyint(4) DEFAULT 0 NOT NULL,
          link_success varchar(255) NULL,
          link_pdf varchar(255) NULL,
          link_optin varchar(255) NULL,
          creation_date datetime NOT NULL DEFAULT NOW(),
          edit_date datetime NULL,
          sheet_received_date datetime NULL,
          remind_signup_sent_date datetime NULL,
          remind_sheet_sent_date datetime NULL,
          source varchar(127) NULL,
          PRIMARY KEY (ID),
          UNIQUE KEY guid_index (guid),
          INDEX creation_date_index (creation_date)';

	/**
	 * Before adding field here, check if it is set with $this->update(). If yes, $isEncrypted has to be passed
	 *
	 * @var array|null
	 */
	protected ?array $encryptFields = [
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
	 * @param bool $publicValue
	 *
	 * @return string
	 */
	public function countSignatures(bool $publicValue = true)
	{
		$count = $this->count('is_deleted = 0 AND is_step2_done = 1 AND is_outside_scope = 0');
		if ($publicValue) {
			$count += intval(Config::getValue('add_count'));
		}
		return $count ?: '0';
	}

	/**
	 * Count results for a where statement
	 *
	 * @param string|int|null $where
	 *
	 * @return int
	 */
	public function count($where = null) : int
	{
		if (is_int($where)) {
			$where = $this->getWhere($where);
		}
		return parent::count($where);
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	public function getWhere($type): string
	{
		switch ($type) {
			case DbSignatures::WHERE_OPTIN:
				$where = 'is_optin = 1 AND is_deleted = 0';
				break;
			case DbSignatures::WHERE_FINISHED:
				$where = 'is_step2_done <> 0 AND is_deleted = 0';
				break;
			case DbSignatures::WHERE_FINISHED_IN_SCOPE:
				$where = 'is_step2_done <> 0 AND is_deleted = 0 AND is_outside_scope = 0';
				break;
			case DbSignatures::WHERE_FINISHED_OUT_SCOPE:
				$where = 'is_step2_done <> 0 AND is_deleted = 0 AND is_outside_scope = 1';
				break;
			case DbSignatures::WHERE_UNFINISHED:
				$where = 'is_step2_done = 0 AND is_deleted = 0';
				break;
			case DbSignatures::WHERE_DELETED:
				$where = 'is_deleted <> 0';
				break;
			default:
				$where = '';
				break;
		}
		return $where;
	}

	/**
	 * @param DtoSignatures   $dto
	 *
	 * @return false|int
	 */
	public function insert(Dto $dto)
	{
		$success = parent::insert($dto);

		if(!$success) {
			return false;
		}

		$signId = Db::getInsertId();
		$successUpd = $this->updateStatus(
			['serial' => Strings::getSerial($signId)],
			['ID' => $signId]
		);
		if (!$successUpd) {
			Core::logMessage('Could not save serial for ID=' . $signId . '. Reason:' . Db::getLastError());
			return false;
		}
		return $success;
	}

	/**
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return DtoSignatures|null Database query results
	 */
	public function getRow(array $select, ?string $where = null, ?string $sqlAppend = null) : ?DtoSignatures
	{
		$row = parent::getRow($select, $where, $sqlAppend);
		if ($row === null) {
			return null;
		}

		return new DtoSignatures($row, false);
	}

	/**
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return DtoSignatures[] Database query results
	 */
	public function getResults(array $select, ?string $where = null, ?string $sqlAppend = null): array
	{
		$results = parent::getResultsRaw($select, $where, $sqlAppend);
		foreach ($results as &$row) {
			$row = new DtoSignatures($row, false);
		}
		return $results;
	}
}