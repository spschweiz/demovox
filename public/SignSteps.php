<?php

namespace Demovox;

class SignSteps
{
	/** @var $nonceId string */
	private string $nonceId;

	/** @var $textColor int[] RGB */
	private array $textColor = [0, 0, 0];

	/**
	 * @param string $nonceId
	 */
	public function __construct(string $nonceId)
	{
		$this->nonceId = $nonceId;
	}

	/**
	 * Ask signee for basic personal information
	 * @param int $collectionId
	 * @return void
	 */
	public function step1(int $collectionId): void
	{
		$textOptin           = Settings::getCValueByUserlang('text_optin');
		$emailConfirmEnabled = !empty(Settings::getCValue('email_confirm'));
		$optinMode           = $this->getOptinMode(1);

		$honeypot        = new Honeypot();
		$honeypotEnabled = $honeypot->isEnabled();
		if ($honeypotEnabled) {
			$honeypotPos     = rand(1, 2);
			$honeypotCaptcha = $honeypot->getChallenge();
		} else {
			$honeypotPos = $honeypotCaptcha = null;
		}
		$mailFieldName = $honeypot->initMailFieldName();

		include Infos::getPluginDir() . 'public/views/sign-1.php';
	}

	/**
	 * @param int    $collectionId
	 * @param string $mailFieldName
	 * @return string guid
	 */
	protected function saveStep1(int $collectionId, string $mailFieldName): string
	{
		$dbSign    = new DbSignatures();
		$lang      = Infos::getUserLanguage();
		$source    = Core::getSessionVar('source');
		$nameFirst = sanitize_text_field($_REQUEST['name_first']);
		$nameLast  = sanitize_text_field($_REQUEST['name_last']);
		$mail      = sanitize_email($_REQUEST[$mailFieldName]);
		$phone     = sanitize_text_field(str_replace(' ', '', $_REQUEST['phone']));
		$optIn     = isset($_REQUEST['is_optin']) && $_REQUEST['is_optin'] ? 1 : 0;

		if (strlen($nameFirst) < 1 || strlen($nameFirst) > 255
			|| strlen($nameLast) < 1
			|| strlen($nameLast) > 255
			|| !is_email($mail)
			|| ($phone && preg_match('/^((\+[1-9])|(0\d[1-9]))\d+$/', $phone) === false)
		) {
			Core::errorDie('Invalid form values received, please try again', 400);
		}

		$data = [
			'collection_ID' => $collectionId,
			'language'      => $lang,
			'first_name'    => $nameFirst,
			'last_name'     => $nameLast,
			'mail'          => $mail,
			'phone'         => $phone,
		];
		if (Settings::getValue('save_ip') && Settings::getValue('encrypt_signees') !== 'disabled') {
			$data['ip_address'] = Infos::getClientIp();
		}
		if ($source) {
			$data['source'] = $source;
		}
		if ($optinMode = $this->getOptinMode(1)) {
			$optIn            = ($optinMode === 'optOut' || $optinMode === 'optOutChk') ? !$optIn : $optIn;
			$data['is_optin'] = $optIn;
		}
		$dtoSign = new SignaturesDto($data);
		$guid    = $dtoSign->getGuid();
		$success = $dbSign->insert($dtoSign);
		if (!$success) {
			Core::errorDie('DB insert failed', 500);
		}
		return $guid;
	}

	/**
	 * Ask signee for additional personal information
	 * @param int $collectionId
	 * @return void
	 */
	public function step2(int $collectionId): void
	{
		$this->verifyNonce();

		$honeypot = new Honeypot();
		if (!$honeypot->validateForm()) {
			$honeypotCaptcha = $honeypot->getChallenge();
			if ($this->isAjax()) {
				Core::jsonResponse(['action' => 'captcha', 'challenge' => $honeypotCaptcha]);
			}
			$this->step1($collectionId);
			return;
		}

		$mailFieldName = $honeypot->getMailFieldName();
		if (!$mailFieldName) {
			Core::logMessage('User tried to save step1 with a missing session - forwarding to step1', 'warning');
			$this->step1($collectionId);
			return;
		}
		$guid = $this->saveStep1($collectionId, $mailFieldName);

		// Prepare view variables
		$textOptin         = Settings::getCValueByUserlang('text_optin');
		$titleEnabled      = !empty(Settings::getCValue('form_title'));
		$apiAddressEnabled = !empty(Settings::getCValue('api_address_url')) && !Infos::isNoEc6();
		$cantons           = i18n::$cantons;
		$allowSwissAbroad  = Settings::getCValue('swiss_abroad_allow') && !Infos::isNoEc6();
		$optinMode         = $this->getOptinMode(2);

		// Render view
		include Infos::getPluginDir() . 'public/views/sign-2.php';
		$this->dieOnAjax();
	}

	/**
	 * Update signature with form data
	 *
	 * @param string $guid
	 * @param int    $isEncrypted
	 *
	 * @return string
	 */
	protected function saveStep2(string $guid, int $isEncrypted): string
	{
		$dbSign = new DbSignatures();
		// Load and sanitize form data
		$birthDateDay    = (string)intval($_REQUEST['birth_date-day']);
		$birthDateMonth  = (string)intval($_REQUEST['birth_date-month']);
		$birthDateYear   = (string)intval($_REQUEST['birth_date-year']);
		$birthDateParsed = date_parse($birthDateDay . '-' . $birthDateMonth . '-' . $birthDateYear);
		$street          = sanitize_text_field($_REQUEST['street']);
		$streetNo        = sanitize_text_field($_REQUEST['street_no']);
		$zip             = sanitize_text_field($_REQUEST['zip']);
		$city            = sanitize_text_field($_REQUEST['city']);
		$gdeId           = (string)intval($_REQUEST['gde_id']);
		$gdeZip          = sanitize_text_field($_REQUEST['gde_zip']);
		$gdeName         = sanitize_text_field($_REQUEST['gde_name']);
		$gdeCanton       = strtolower(sanitize_text_field($_REQUEST['gde_canton']));

		// Validate form data
		if (!isset($_REQUEST['swiss_abroad']) || !$_REQUEST['swiss_abroad']) {
			$country = i18n::$countryDefault;
		} else {
			$country   = isset($_REQUEST['country']) ? strtoupper(sanitize_text_field($_REQUEST['country'])) : null;
			$zip       = sanitize_text_field($_REQUEST['zip_abroad']);
			$city      = sanitize_text_field($_REQUEST['city_abroad']);
			$countries = Strings::getCountries();
			if (!$country || !isset($countries[$country])) {
				Core::errorDie('Invalid country value received: ' . $country, 400);
			}
		}
		if (
			$birthDateParsed['year'] === false
			|| $birthDateParsed['month'] === false
			|| $birthDateParsed['day'] === false
			|| strlen($street) < 4
			|| strlen($street) > 127
			|| strlen($streetNo) < 1
			|| strlen($streetNo) > 5
			|| strlen($zip) < 4
			|| strlen($zip) > 16
			|| strlen($city) < 2
			|| strlen($city) > 64
			|| strlen($gdeId) > 5
			|| strlen($gdeZip) > 4
			|| strlen($gdeName) < 2
			|| strlen($gdeName) > 45
			|| !isset($gdeCanton)
			|| empty($gdeCanton)
			|| !isset(i18n::$cantons[$gdeCanton])
		) {
			Core::errorDie('Invalid form values received.', 400);
		}

		// Prepare update
		$birthDateMysql = sprintf('%s-%s-%s', $birthDateParsed['year'], $birthDateParsed['month'], $birthDateParsed['day']);
		$data           = [
			'birth_date'    => $birthDateMysql,
			'street'        => $street,
			'street_no'     => $streetNo,
			'zip'           => $zip,
			'city'          => $city,
			'country'       => $country,
			'gde_no'        => $gdeId,
			'gde_zip'       => $gdeZip,
			'gde_name'      => $gdeName,
			'gde_canton'    => $gdeCanton,
			'is_step2_done' => 1,
		];
		if (isset($_REQUEST['title'])) {
			$validTitles = ['Mister', 'Miss'];
			if (in_array($_REQUEST['title'], $validTitles)) {
				$data['title'] = sanitize_text_field($_REQUEST['title']);
			}
		}

		// Append additional values to $data
		$data = $this->getPagesUrls($guid, $country, $gdeCanton, $gdeId, $data);

		if ($optinMode = $this->getOptinMode(2)) {
			$optIn            = isset($_REQUEST['is_optin']) && $_REQUEST['is_optin'] ? 1 : 0;
			$optIn            = ($optinMode === 'optOut' || $optinMode === 'optOutChk') ? !$optIn : $optIn;
			$data['is_optin'] = $optIn;
		}

		// Update
		$success = $dbSign->update(
			$data,
			['guid' => $guid,],
			$isEncrypted
		);
		if (!$success) {
			Core::errorDie('DB update failed: ' . Db::getLastError(), 500);
		}
		return $data['link_success'];
	}

	/**
	 * @param             $guid
	 * @param string|null $country
	 * @param string      $gdeCanton
	 * @param string      $gdeId
	 * @param array       $data
	 *
	 * @return array
	 */
	protected function getPagesUrls($guid, string $country, string $gdeCanton, string $gdeId, array $data): array
	{
		$abroadRedirect   = Settings::getCValue('swiss_abroad_redirect');
		$isAbroadRedirect = $abroadRedirect && $country !== i18n::$countryDefault;
		$isOutsideScope   = false;
		if (($localIniMode = Settings::getCValue('local_initiative_mode')) !== 'disabled') {
			$isOutsideScope =
				$localIniMode === 'canton' && Settings::getCValue('local_initiative_canton') !== $gdeCanton
				|| $localIniMode === 'commune' && Settings::getCValue('local_initiative_commune') !== $gdeId;
		}
		if ($isAbroadRedirect) {
			$successPage              = Strings::getPageUrl($guid, $abroadRedirect);
			$data['link_success']     = $successPage;
			$data['link_pdf']         = $successPage;
			$data['is_outside_scope'] = ($localIniMode !== 'disabled') ? 1 : 0;
		} elseif ($isOutsideScope) {
			$successPage              = Strings::getPageUrl($guid, Settings::getCValue('local_initiative_error_redirect'));
			$data['link_success']     = $successPage;
			$data['link_pdf']         = $successPage;
			$data['is_outside_scope'] = 1;
		} else {
			$successPage          = Strings::getPageUrl($guid, Settings::getCValue('use_page_as_success'));
			$data['link_success'] = $successPage;
			$data['link_pdf']     = Strings::getPageUrl($guid, Settings::getCValue('use_page_as_mail_link'));
		}

		return $data;
	}

	/**
	 * Show signature sheet partial or return redirect URL
	 *
	 * @param SignaturesDto $row
	 */
	public function step3(SignaturesDto $row): void
	{
		$guid = $row->getGuid();

		if (!$row->is_step2_done) {
			$this->verifyNonce();
			$url = $this->saveStep2($guid, $row->is_encrypted);
		} else {
			if ($_REQUEST['birth_date'] || isset($_REQUEST['street'])) {
				Core::logMessage('User tried to save step2 a second time', 'warning');
			}
			$url = $row->link_success;
		}

		$successPage = Settings::getCValue('use_page_as_success');
		if ($this->isAjax() && ($successPage || $successPage === '0')) {
			Core::jsonResponse(['action' => 'redirect', 'url' => $url]);
		}

		// Show inline success page
		$this->step3successPage($guid);
		$this->dieOnAjax();
	}

	/**
	 * @param      $guid
	 */
	protected function step3successPage($guid): void
	{
		$dbSign = new DbSignatures();
		// Prepare PDF data
		$row = $dbSign->getRow(
			[
				'ID',
				'first_name',
				'last_name',
				'birth_date',
				'gde_name',
				'gde_zip',
				'gde_canton',
				'street',
				'street_no',
				'is_step2_done',
				'zip',
				'city',
				'country',
			],
			"guid = '" . $guid . "'"
		);
		if (!$row) {
			Core::errorDie('Signature with GUID "' . $guid . '" was not found', 404);
		}
		if (!$row->is_step2_done) {
			Core::errorDie('Signature with GUID "' . $guid . '" needs step2 to be finished', 400);
		}
		$signId    = $row->ID;
		$gdeCanton = $row->gde_canton;
		$gdeName   = $row->gde_name;
		$gdeZip    = $row->gde_zip ?: $row->zip;
		$birthDate = date_parse($row->birth_date);
		$street    = $row->street;
		$streetNo  = $row->street_no;
		$zip       = $row->zip;
		$city      = $row->city;
		$country   = strtoupper($row->country);

		// PDF Fields
		$birthDateDay   = $birthDate ? $birthDate['day'] : '';
		$birthDateMonth = $birthDate ? $birthDate['month'] : '';
		$birthDateYear  = $birthDate ? $birthDate['year'] : '';
		if ($country && $country !== i18n::$countryDefault) {
			$address = [
				'size' => Settings::getCValue('swiss_abroad_fontsize'),
				'text' => $street . ' ' . $streetNo . ', ' . $country . '-' . $zip . ' ' . $city,
			];
		} else {
			$address = $street . ' ' . $streetNo;
		}
		$fields = [
			'field_canton'          => strtoupper($gdeCanton),
			'field_commune'         => $gdeName,
			'field_zip'             => $gdeZip,
			'field_birthdate_day'   => str_pad($birthDateDay, 2, '0', STR_PAD_LEFT),
			'field_birthdate_month' => str_pad($birthDateMonth, 2, '0', STR_PAD_LEFT),
			'field_birthdate_year'  => substr($birthDateYear, -2),
			'field_street'          => $address,
		];
		if (Settings::getCValue('print_names_on_pdf')) {
			$fields['field_first_name'] = $row->first_name;
			$fields['field_last_name']  = $row->last_name;
		}
		$fields = $this->formatFields($fields);

		// PDF QR-code
		if (($qrMode = Settings::getCValue('field_qr_mode')) === 'disabled') {
			$qrData = null;
		} else {
			$shortcode  = Strings::getSerial($signId, $qrMode);
			$qrPosX     = Settings::getCValueByUserlang('field_qr_img', Settings::PART_POS_X);
			$qrPosY     = Settings::getCValueByUserlang('field_qr_img', Settings::PART_POS_Y);
			$qrTextPosX = Settings::getCValueByUserlang('field_qr_text', Settings::PART_POS_X);
			$qrTextPosY = Settings::getCValueByUserlang('field_qr_text', Settings::PART_POS_Y);
			$fontSize   = Settings::getCValue('fontsize');
			$qrData     = [
				'text'       => (string)$shortcode,
				'x'          => intval($qrPosX),
				'y'          => intval($qrPosY),
				'size'       => intval(Settings::getCValueByUserlang('field_qr_img_size')),
				'rotate'     => intval(Settings::getCValueByUserlang('field_qr_img', Settings::PART_ROTATION)),
				'textX'      => intval($qrTextPosX),
				'textY'      => intval($qrTextPosY),
				'textRotate' => intval(Settings::getCValueByUserlang('field_qr_text', Settings::PART_ROTATION)),
				'textSize'   => intval($fontSize),
				'textColor'  => $this->textColor,
			];
		}

		// Prepare view variables
		$title     = __('signature_sheet', 'demovox');
		$permalink = Strings::getPageUrl($guid);
		$pdfUrl    = Settings::getCValueByUserlang('signature_sheet');
		$fields    = json_encode($fields);
		$qrData    = $qrData ? json_encode($qrData) : null;

		// Render view
		include Infos::getPluginDir() . 'public/views/sign-3.php';
	}

	private function verifyNonce(): void
	{
		if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], $this->nonceId)) {
			Core::errorDie('nonce check failed', 401);
		}
	}

	private function formatFields($fields): array
	{
		$fontSize  = Settings::getCValue('fontsize');
		$textColor = $this->textColor;

		$return = [];
		foreach ($fields as $name => $value) {
			$posX   = Settings::getCValueByUserlang($name, Settings::PART_POS_X);
			$posY   = Settings::getCValueByUserlang($name, Settings::PART_POS_Y);
			$rotate = Settings::getCValueByUserlang($name, Settings::PART_ROTATION);
			if ($posX === false || $posY === false || $rotate === false) {
				Core::logMessage('Coordinates for field "' . $name
								 . '" are not defined, please save your Signature sheet settings.', 'warning');
				continue;
			}
			if (is_array($value)) {
				$text = $value['text'];
				if (isset($value['size'])) {
					$fontSize = $value['size'];
				}
			} else {
				$text = $value;
			}
			$return[] = [
				'drawText' => (string)$text,
				'x'        => (int)$posX,
				'y'        => (int)$posY,
				'rotate'   => (int)$rotate,
				'size'     => (int)$fontSize,
				'color'    => (array)$textColor,
			];
		}
		return $return;
	}

	protected function getOptinMode($page): ?string
	{
		$optinMode     = Settings::getCValue('optin_mode');
		$optinPosition = Settings::getCValue('optin_position');
		return ($optinMode !== 'disabled' && $optinPosition == $page) ? $optinMode : null;
	}

	/**
	 * End page output on ajax requests
	 * @return void
	 */
	protected function dieOnAjax(): void
	{
		if ($this->isAjax()) {
			wp_die();
		}
	}

	protected function isAjax(): bool
	{
		return isset($_REQUEST['ajax']) && $_REQUEST['ajax'];
	}
}