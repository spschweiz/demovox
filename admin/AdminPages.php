<?php

namespace Demovox;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/admin
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class AdminPages
{
	public function pageOverview()
	{
		$count = DB::countSignatures(false);
		$addCount = Config::getValue('add_count');
		$userLang = Infos::getUserLanguage();
		$countOptin = intval(DB::getRow(['COUNT(*) as count'], 'is_optin = 1 AND is_step2_done = 1 AND is_deleted = 0')->count);
		$countOptout = intval(DB::getRow(['COUNT(*) as count'], 'is_optin = 0 AND is_step2_done = 1 AND is_deleted = 0')->count);
		$countUnfinished = intval(DB::getRow(['COUNT(*) as count'], 'is_step2_done = 0 AND is_deleted = 0')->count);
		include Infos::getPluginDir() . 'admin/partials/admin-page.php';
	}

	public function pageImport()
	{
		$statusMsg = '';
		$page = 'demovoxImport';
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
			$statusMsg = $this->doCsvImport();
		}
		$csvFormat = Core::getOption('importCsvFormat');
		$delimiter = Core::getOption('importCsvDelimiter') ?: ';';
		include Infos::getPluginDir() . 'admin/partials/import.php';
	}

	public function pageData()
	{
		$page = 'demovoxData';
		include Infos::getPluginDir() . 'admin/partials/data.php';
	}

	public function pageSysinfo()
	{
		if (defined('DEMOVOX_ENC_KEY')) {
			$encKey = true;
		} else {
			try {
				$key = \Defuse\Crypto\Key::createNewRandomKey();
				$encKey = $key->saveToAsciiSafeString();
			} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
				echo '<span style="color:red">Crypto error: ' . $e->getMessage() . '</span>';
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
		$configPath = dirname(ABSPATH) . '/wp-config.php';
		$phpShowErrors = !!ini_get('display_errors');
		list($httpsEnforced, $httpStatus, $httpRedirect) = Infos::checkHttp2Https();
		$mailRecipient = $this->getWpMailAddress();
		$languages = i18n::getLangsEnabled();

		include Infos::getPluginDir() . 'admin/partials/sysinfo.php';
	}

	public function statsCharts()
	{
		$this->checkPermission('demovox_stats');

		$countDategroupedOi = DB::getResults(['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
			'is_optin = 1 AND is_step2_done = 1 AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)');
		$countDategroupedOo = DB::getResults(['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
			'is_optin = 0 AND is_step2_done = 1 AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)');
		$countDategroupedC = DB::getResults(['DATE_FORMAT(creation_date, "%Y,%m,%d") as date', 'COUNT(*) as count'],
			'is_step2_done = 0 AND is_deleted = 0 GROUP BY YEAR(creation_date), MONTH(creation_date), DAY(creation_date)');
		$datesetsOi = $datesetsOo = $datesetsC = '';
		foreach ($countDategroupedOi as $row) {
			$datesetsOi .= '{t:nDate(' . $row->date . '),y:' . intval($row->count) . '},';
		}
		foreach ($countDategroupedOo as $row) {
			$datesetsOo .= '{t:nDate(' . $row->date . '),y:' . intval($row->count) . '},';
		}
		foreach ($countDategroupedC as $row) {
			$datesetsC .= '{t:nDate(' . $row->date . '),y:' . intval($row->count) . '},';
		}
		include Infos::getPluginDir() . 'admin/partials/charts.php';
	}

	public function statsSource()
	{
		$this->checkPermission('demovox_stats');

		$sourceList = DB::getResults(
			[
				'source',
				'SUM(is_sheet_received) AS signatures',
				'SUM(is_sheet_received<>0) AS sheetsRec',
				'SUM((is_optin<>0 AND is_step2_done<>0 AND is_deleted = 0)) AS optin',
				'SUM((is_optin=0 AND is_step2_done<>0 AND is_deleted = 0)) AS optout',
				'SUM((is_step2_done=0 AND is_deleted = 0)) AS unfinished',
			],
			'',
			'GROUP BY source ORDER BY source');
		include Infos::getPluginDir() . 'admin/partials/statsSource.php';
	}

	public function testEncrypt()
	{
		$this->checkPermission('manage_options');

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
				$enc[$length][$i] = DB::encrypt($orig[$length][$i]);
			}
		}
		$endtime = microtime(true);
		$timediffEncrypt = $endtime - $starttime;
		// Decrypt
		$starttime = microtime(true);
		foreach ($lengths as $length) {
			for ($i = 1; $i <= $iterations; $i++) {
				$dec[$length][$i] = DB::decrypt($enc[$length][$i]);
			}
		}
		$endtime = microtime(true);
		$timediffDecrypt = $endtime - $starttime;

		$showStrLengths = (isset($_REQUEST['showStrLen']) && $_REQUEST['showStrLen']);

		include Infos::getPluginDir() . 'admin/partials/sysinfo-encrypt.php';
	}

	public function testMail()
	{
		$this->checkPermission('manage_options');

		$mailTo = $this->getWpMailAddress();
		$langId = (isset($_REQUEST['lang']) && $_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : 'de';
		$mailFrom = Config::getValueByLang('mail_confirm_from_address', $langId);
		$nameFrom = Config::getValueByLang('mail_confirm_from_name', $langId);

		$sign = new signObject($langId, $nameFrom, 'last name', $mailFrom);

		define('WP_SMTPDEBUG', true);
		add_action('phpmailer_init', [new Mail(), 'config'], 10, 1);

		$mailSubject = Mail::getMailSubject($sign);
		$mailText = Mail::getMailText($sign, $mailSubject);

		ob_start();
		$isSent = Mail::send($mailTo, $mailSubject, $mailText, $mailFrom, $nameFrom);
		$connectionLog = ob_get_contents();
		ob_end_clean();

		include Infos::getPluginDir() . 'admin/partials/sysinfo-mail.php';
	}

	public function runCron()
	{
		$this->checkPermission('manage_options');

		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::triggerCron($hook);
		echo 'Event triggered at ' . date('d.m.Y I:m:s');
	}

	public function cancelCron()
	{
		$this->checkPermission('manage_options');

		ManageCron::cancelMail();
		echo 'Cron cancelled at ' . date('d.m.Y I:m:s');
	}

	protected function checkPermission($capability = null)
	{
		Core::checkNonce();
		if (!current_user_can($capability)) {
			wp_die(esc_html__('You are not allowed to access this page.', 'wp-control'));
		}
	}

	protected function getWpMailAddress()
	{
		return get_bloginfo('admin_email');
	}

	protected function getWpMailName()
	{
		return get_bloginfo('name');
	}

	protected function doCsvImport()
	{
		// Validate request & prepare data
		$deliveryDate = sanitize_text_field($_REQUEST['deliveryDate']);
		$format = intval($_REQUEST['csvFormat']);
		$delimiter = sanitize_text_field($_REQUEST['delimiter']);
		$signCount = intval($_REQUEST['signCount']);
		$csvArr = Strings::parseCsv($_REQUEST['csv'], $delimiter);
		$deliveryDateMysql = date('Y-m-d', strtotime($deliveryDate));
		Core::setOption('importCsvFormat', $format);
		Core::setOption('importCsvDelimiter', $delimiter);
		if (!$deliveryDate) {
			return Strings::wpMessage('Error: Delivery date not defined or invalid', 'error');
		}

		// Handle csv
		$ok = 0;
		$fail = [];
		$totalSignCount = 0;
		$return = '';
		foreach ($csvArr as $line) {
			$serial = sanitize_text_field($line[0]);
			if (!$serial) {
				continue;
			}
			switch ($format) {
				case 1:
					if (!$signCount) {
						return Strings::wpMessage('Error: Signature count is required', 'error');
					}
					break;
				case 2:
					$signCount = isset($line[1]) ? intval($line[1]) : null;
					break;
				case 3:
					$signCount = isset($line[2]) ? intval($line[2]) : null;
					break;
				default:
					return Strings::wpMessage('Error: Format is required', 'error');
			}
			if (!$signCount) {
				$fail[] = $serial;
				continue;
			}

			$row = DB::getRow(['ID'], "serial = '" . $serial . "'");
			if (!$row) {
				$fail[] = $serial;
				Core::logMessage('doCsvImport: Could not find serial = "' . $serial . '"');
				continue;
			}

			$save = DB::updateStatus(
				[
					'is_sheet_received'   => $signCount,
					'sheet_received_date' => $deliveryDateMysql,
				],
				['ID' => $row->ID]
			);
			if ($save) {
				$ok++;
				$totalSignCount += $signCount;
			} else {
				$fail[] = $serial;
				Core::logMessage(
					'doCsvImport: Could not update ID = "' . $row->ID . '" (serial = "' . $serial . '): '
					. DB::getError()
				);
			}
		}

		// Return
		if ($ok) {
			$return .= Strings::wpMessage('Imported ' . $ok . ' sheets (' . $totalSignCount . ' signatures)', 'success');
		}
		if ($count = count($fail)) {
			$return .= Strings::wpMessage($count . ' failed sheet(s): ' . implode(', ', $fail), 'error');
		}
		return $return;
	}
}