<?php

namespace Demovox;

class DtoSignatures extends Dto
{
	/** @var int */
	public int $ID;
	/** @var string */
	public string $guid, $serial, $language, $ip_address, $title, $first_name, $last_name, $birth_date, $mail, $phone, $country, $street, $street_no,
		$zip, $city, $gde_no, $gde_zip, $gde_name, $gde_canton, $link_pdf, $link_optin, $link_success, $source;
	/** @var int */
	public int $instance, $is_optin, $is_step2_done, $is_sheet_received, $is_exported, $is_encrypted, $is_deleted,
		$state_confirm_sent, $state_remind_sheet_sent, $state_remind_signup_sent, $is_outside_scope;
	/** @var string */
	public string $creation_date, $edit_date, $sheet_received_date, $remind_signup_sent_date, $remind_sheet_sent_date;
	/* virtual */
	/** @var int|null */
	public ?int $creation_date_hours_ago;

	protected array $availableFields = [
		'ID'                  => 'id',
		'language'            => 'Language',
		'title'               => 'Title',
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
		'is_step2_done'       => '2nd form filled',
		'is_sheet_received'   => 'Received signatures',
		'is_outside_scope'    => 'Is outside scope',
		'creation_date'       => 'Creation Date',
		'edit_date'           => 'Edit date',
		'sheet_received_date' => 'Sheet received Date',
		'serial'              => 'Serial (QR code)',
		'source'              => 'Source',
	];

	/**
	 * Init new entry values before insert
	 * @return bool
	 */
	public function prepareInsert(): bool
	{
		if (!parent::prepareInsert()) {
			return false;
		}
		$guid = $this->getGuid();

		$linkOptin = Strings::getPageUrl($guid, Config::getValue('use_page_as_optin_link'));
		$this->link_optin = $linkOptin;

		if (isset($this->creation_date_hours_ago)) {
			$this->creation_date = time() - $this->creation_date_hours_ago * 60 * 60;
			$this->creation_date_hours_ago = null;
		}
		if (isset($this->creation_date) && is_int($this->creation_date)) {
			$this->creation_date = date("Y-m-d H:i:s", $this->creation_date);
		}
		return true;
	}

	/**
	 * @return void
	 */
	protected function initGuid(): void
	{
		if (isset($this->guid) || !$this->isNewRecord) {
			return;
		}

		$this->guid = Strings::createGuid();
	}

	/**
	 * @return string|null
	 */
	public function getGuid()
	{
		if (isset($this->guid)) {
			return $this->guid;
		}

		if (!$this->isNewRecord) {
			return null;
		}

		$this->initGuid();
		return $this->guid;
	}
}