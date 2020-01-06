<?php

namespace Demovox;

class DbSignatures extends Db
{
	// DTO
	/** @var int */
	public $ID;
	/** @var string */
	public $guid, $serial, $language, $ip_address, $first_name, $last_name, $birth_date, $mail, $phone, $country, $street, $street_no,
		$zip, $city, $gde_no, $gde_zip, $gde_name, $gde_canton, $link_pdf, $link_optin, $link_success, $source;
	/** @var int */
	public $is_optin, $is_step2_done, $is_sheet_received, $is_exported, $is_encrypted, $is_deleted, $state_confirm_sent,
		$state_remind_sheet_sent, $state_remind_signup_sent, $is_outside_scope;
	/** @var string */
	public $creation_date, $edit_date, $sheet_received_date, $reminder_sent_date;

	// Model
	const WHERE_OPTIN              = 0;
	const WHERE_FINISHED           = 5;
	const WHERE_FINISHED_IN_SCOPE  = 1;
	const WHERE_FINISHED_OUT_SCOPE = 2;
	const WHERE_UNFINISHED         = 3;
	const WHERE_DELETED            = 4;

	protected $tableName = 'demovox_signatures';
	protected static $availableFields = [
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
	 * @param bool $publicValue
	 *
	 * @return string
	 */
	public function countSignatures($publicValue = true)
	{
		$count = $this->count('is_deleted = 0 AND is_step2_done = 1 AND is_outside_scope = 0');
		if ($publicValue) {
			$count += intval(Config::getValue('add_count'));
		}
		return $count ?: '0';
	}

	/**
	 * @return array
	 */
	public function getAvailableFields()
	{
		return self::$availableFields;
	}

	/**
	 * @param          $data
	 *
	 * @return false|int
	 */
	public function insert($data)
	{
		$guid         = $this->createGuid();
		$data['guid'] = $guid;
		if (isset($data['creation_date_hours_ago'])) {
			$data['creation_date'] = time() - $data['creation_date_hours_ago'] * 60 * 60;
			unset($data['creation_date_hours_ago']);
		}
		if (isset($data['creation_date']) && is_int($data['creation_date'])) {
			$data['creation_date'] = date("Y-m-d H:i:s", $data['creation_date']);
		}
		return parent::insert($data);
	}

	/**
	 * Count results for a where statement
	 *
	 * @param null|int|string $where
	 *
	 * @return int
	 */
	public function count($where = null)
	{
		if (is_int($where)) {
			$where = $this->getWhere($where);
		}
		return parent::count($where);
	}

	/**
	 * Select multiple rows from demovox table
	 *
	 * @param array           $select    Fields to select
	 * @param int|string|null $where     SQL where statement
	 * @param string|null     $sqlAppend Append SQL statements
	 *
	 * @return array|DbSignatures|null Database query results
	 */
	public function getResults($select, $where = null, $sqlAppend = null)
	{
		if (is_int($where)) {
			$where = $this->getWhere($where);
		}
		return parent::getResults($select, $where, $sqlAppend);
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	public function getWhere($type)
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

	private function createGuid()
	{
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		$data    = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}