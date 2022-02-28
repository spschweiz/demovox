<?php

namespace Demovox;
require_once Infos::getPluginDir() . 'admin/controllers/AdminBaseController.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/admin
 * @author     SP Schweiz
 */
class AdminGeneral extends AdminBaseController
{
	public function pageOverview()
	{
		$dbSign = new DbSignatures();
		$count = $dbSign->countSignatures(false);
		$addCount = Config::getValue('add_count');
		$userLang = Infos::getUserLanguage();
		$countOptin = $dbSign->count('is_optin = 1 AND is_step2_done = 1 AND is_deleted = 0');
		$countOptout = $dbSign->count('is_optin = 0 AND is_step2_done = 1 AND is_deleted = 0');
		$countOptNULL = $dbSign->count('is_optin IS NULL AND is_step2_done = 1 AND is_deleted = 0');
		$countUnfinished = $dbSign->count('is_step2_done = 0 AND is_deleted = 0');
		include Infos::getPluginDir() . 'admin/views/general/admin-page.php';
	}

	public function pageSysinfo()
	{
		if (defined('DEMOVOX_ENC_KEY') && defined('DEMOVOX_HASH_KEY')) {
			$encKey = true;
		} else {
			try {
				$key = \Defuse\Crypto\Key::createNewRandomKey();
				$encKey = $key->saveToAsciiSafeString();
			} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
				echo '<span class="error">Crypto error: ' . $e->getMessage() . '</span>';
			}
		}
		if (defined('DEMOVOX_ENC_KEY') && defined('DEMOVOX_HASH_KEY')) {
			$hashKey = true;
		} else {
			try {
				$hashKey = bin2hex(random_bytes(30));
			} catch (\Exception $e) {
				echo '<span class="error">Error on creating random bytes: ' . $e->getMessage() . '</span>';
			}
		}
		$salts = [
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		];
		$saltsFailed = false;
		foreach ($salts as $salt) {
			if (!defined($salt) || constant($salt) == 'put your unique phrase here') {
				$saltsFailed = true;
			}
		}
		$phpDisplayErrors = !!ini_get('display_errors');
		$mailRecipient = $this->getWpMailAddress();
		$languages = i18n::getLangsEnabled();

		include Infos::getPluginDir() . 'admin/views/general/sysinfo.php';
	}

	public function statsCharts()
	{
		Core::checkAccess('demovox_stats');

		$dbSign = new DbSignatures();
		$sqlAppend = ' AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)';
		$source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : null;
		if ($source !== null) {
			$sqlAppend .= ' AND source=\'' . $source . '\'';
		}
		$datasets = [];
		// sheet_received_date
		$datasets[] = [
			'label'           => 'Received signatures',
			'borderColor'     => 'rgba(50, 100, 150, 1)',
			'backgroundColor' => 'rgba(50, 100, 150, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'SUM(is_sheet_received) AS count'],
				'is_sheet_received<>0' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Received sheets',
			'borderColor'     => 'rgba(0, 50, 0, 1)',
			'backgroundColor' => 'rgba(0, 50, 0, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) AS count'],
				'is_sheet_received<>0' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-in',
			'borderColor'     => 'rgba(0, 255, 99, 1)',
			'backgroundColor' => 'rgba(0, 255, 99, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 1 AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Opt-out',
			'borderColor'     => 'rgba(255, 206, 86, 1)',
			'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin = 0 AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'No opt-in info',
			'borderColor'     => 'rgb(68,78,255, 1)',
			'backgroundColor' => 'rgba(68,78,255, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_optin IS NULL AND is_step2_done = 1' . $sqlAppend
			),
		];
		$datasets[] = [
			'label'           => 'Unfinished',
			'borderColor'     => 'rgba(255,99,132,1 )',
			'backgroundColor' => 'rgba(255,99,132, 0.2)',
			'data'            => $dbSign->getResultsRaw(
				['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
				'is_step2_done = 0' . $sqlAppend
			),
		];
		include Infos::getPluginDir() . 'admin/views/general/statsCharts.php';
	}

	public function statsSource()
	{
		Core::checkAccess('demovox_stats');

		$dbSign = new DbSignatures();
		$sourceList = $dbSign->getResultsRaw(
			[
				'source',
				'SUM(is_sheet_received) AS signatures',
				'SUM(is_sheet_received<>0) AS sheetsRec',
				'SUM((is_optin<>0 AND is_step2_done<>0 AND is_deleted = 0)) AS optin',
				'SUM((is_optin=0 AND is_step2_done<>0 AND is_deleted = 0)) AS optout',
				'SUM((is_step2_done=0 AND is_deleted = 0)) AS unfinished',
			],
			'is_deleted = 0',
			'GROUP BY source ORDER BY source'
		);
		include Infos::getPluginDir() . 'admin/views/general/statsSource.php';
	}

	public function testEncrypt()
	{
		Core::checkAccess('manage_options');
		if (!defined('DEMOVOX_ENC_KEY') || !defined('DEMOVOX_HASH_KEY')) {
			echo '<span class="error">Error</span>: Wordpress configs DEMOVOX_ENC_KEY and DEMOVOX_HASH_KEY are required for encryption. Please set them with a random value prior to testing (see examples in System info)';
			return;
		}

		if (isset($_REQUEST['fullLen']) && $_REQUEST['fullLen']) {
			$lengths = [32, 255, 255, 10, 128, 64, 127, 10, 16, 64, 5, 4, 45, 2];
		} else {
			$lengths = [24, 8, 12, 10, 30, 10, 15, 2, 4, 15, 5, 4, 15, 2];
		}
		$iterations = (isset($_REQUEST['i']) && intval($_REQUEST['i'])) ? intval($_REQUEST['i']) : 100;

		$orig = [];
		$enc = [];
		$dec = [];
		foreach ($lengths as $length) {
			for ($i = 1; $i <= $iterations; $i++) {
				$orig[$length][$i] = Strings::generateRandomString($length);
			}
		}
		// Encrypt
		$starttime = microtime(true);
		foreach ($lengths as $length) {
			for ($i = 1; $i <= $iterations; $i++) {
				$enc[$length][$i] = Crypt::encrypt($orig[$length][$i]);
			}
		}
		$endtime = microtime(true);
		$timediffEncrypt = $endtime - $starttime;
		// Decrypt
		$starttime = microtime(true);
		foreach ($lengths as $length) {
			for ($i = 1; $i <= $iterations; $i++) {
				$dec[$length][$i] = Crypt::decrypt($enc[$length][$i]);
			}
		}
		$endtime = microtime(true);
		$timediffDecrypt = $endtime - $starttime;

		$showStrLengths = (isset($_REQUEST['showStrLen']) && $_REQUEST['showStrLen']);

		include Infos::getPluginDir() . 'admin/views/general/sysinfo-encrypt.php';
	}

	public function testMail()
	{
		Core::checkAccess('manage_options');

		$mailTo = $this->getWpMailAddress();
		$langId = (isset($_REQUEST['lang']) && $_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : 'de';
		$mailType = isset($_REQUEST['mailType']) ? intval($_REQUEST['mailType']) : Mail::TYPE_CONFIRM;
		$mailFrom = Config::getValueByLang('mail_from_address', $langId);
		$nameFrom = Config::getValueByLang('mail_from_name', $langId);

		$sign = new SignaturesDto();
		$sign->language = $langId;
		$sign->first_name = $nameFrom;
		$sign->last_name = 'last name';
		$sign->mail = $mailFrom;
		$sign->link_pdf = Strings::getPageUrl('SIGNEE_PERSONAL_CODE');
		$sign->link_optin = Strings::getPageUrl('SIGNEE_PERSONAL_CODE', Config::getValue('use_page_as_optin_link'));

		define('WP_SMTPDEBUG', true);
		Loader::addAction('phpmailer_init', new Mail(), 'config', 10, 1);

		$mailSubject = Mail::getMailSubject($sign, $mailType);
		$mailText = Mail::getMailText($sign, $mailSubject, $mailType);

		Loader::addAction('wp_mail_failed', new Mail(), 'echoMailerErrors', 20, 1);

		ob_start();
		$isSent = Mail::send($mailTo, $mailSubject, $mailText, $mailFrom, $nameFrom);
		$connectionLog = ob_get_contents();
		ob_end_clean();

		include Infos::getPluginDir() . 'admin/views/general/sysinfo-mail.php';
	}

	public function runCron()
	{
		Core::checkAccess('manage_options');

		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::triggerCron($hook);
		echo 'Event triggered at ' . date('d.m.Y G:i:s');
	}

	public function cancelCron()
	{
		Core::checkAccess('manage_options');

		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::cancel($hook);
		echo 'Cron cancelled at ' . date('d.m.Y G:i:s');
	}

	protected function getWpMailAddress()
	{
		return get_bloginfo('admin_email');
	}

	protected function getWpMailName()
	{
		return get_bloginfo('name');
	}

	/**
	 * @param $mail
	 * @return bool success
	 */
	protected function dedupSetSheetReceived($mail)
	{
		$hashedMail = Strings::hashMail($mail);
		$dbMailDd = new DbMails();
		$update = $dbMailDd->updateStatus(['is_sheet_received' => 1], ['mail_md5' => $hashedMail]);
		return $update !== false;
	}

}