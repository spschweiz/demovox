<?php

namespace Demovox;

class DbSignatures extends Db
{
	const WHERE_OPTIN              = 0;
	const WHERE_FINISHED  = 5;
	const WHERE_FINISHED_IN_SCOPE  = 1;
	const WHERE_FINISHED_OUT_SCOPE = 2;
	const WHERE_UNFINISHED         = 3;
	const WHERE_DELETED            = 4;
	protected static $tableName = 'demovox_signatures';

	private static $availableFields = [
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
	public static function countSignatures($publicValue = true)
	{
		$count = self::count('is_deleted = 0 AND is_step2_done = 1 AND is_outside_scope = 0');
		if ($publicValue) {
			$count += intval(Config::getValue('add_count'));
		}
		return $count ?: '0';
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return self::$availableFields;
	}

	/**
	 * Count results for a where statement
	 *
	 * @param null|int|string $where
	 *
	 * @return int
	 */
	public static function count($where = null)
	{
			if (is_int($where)) {
				$where = self::getWhere($where);
			}
			return parent::count($where);
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	public static function getWhere($type)
	{
		switch ($type) {
			case DbSignatures::WHERE_OPTIN:
				$where = 'is_optin <> 0 AND is_deleted = 0';
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
}