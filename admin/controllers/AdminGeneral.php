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
 * @author     SP Schweiz
 */
class AdminGeneral extends BaseController
{
	use AdminScriptsTrait;

	public function pageOverview()
	{
		$dbSign = new DbSignatures();
		$count = $dbSign->countSignatures(null, false);
		$userLang = Infos::getAdminLanguage();

		if ($count) {
			$stats = new CollectionStatsDto();
			$stats->countOptin = $dbSign->count(DbSignatures::WHERE_OPTIN);
			$stats->countOptout = $dbSign->count(DbSignatures::WHERE_OPTOUT);
			$stats->countOptNULL = $dbSign->count(DbSignatures::WHERE_OPTNULL);
			$stats->countUnfinished = $dbSign->count(DbSignatures::WHERE_UNFINISHED);
		}

		require_once Infos::getPluginDir() . 'admin/helpers/CollectionList.php';
		$collectionList = new CollectionList();

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

		include Infos::getPluginDir() . 'admin/views/general/sysinfo.php';
	}

	/**
	 * ajax action "encrypt_test"
	 * @return void
	 */
	public function testEncrypt()
	{
		Core::requireAccess('demovox_sysinfo');

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

	public function pageImport()
	{
		$statusMsg = '';
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
			$statusMsg = $this->doCsvImport();
		}
		$csvFormat = Core::getOption('importCsvFormat');
		$delimiter = Core::getOption('importCsvDelimiter') ?: ';';
		include Infos::getPluginDir() . 'admin/views/general/import.php';
	}

	protected function doCsvImport()
	{
		Core::requireAccess('demovox_import');

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
		$dbSign = new DbSignatures();

		$dbMailDd = new DbMails();
		$hasDedup = !!$dbMailDd->count();

		// Handle csv
		$ok = $okSign = 0;
		$receivedAgain = $fail = [];
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
				Core::logMessage('doCsvImport: missing signature count for serial = "' . $serial);
				$fail[] = $serial;
				continue;
			}

			$row = $dbSign->getRow(['ID', 'is_sheet_received', 'mail'], "serial = '" . $serial . "'");
			if (!$row) {
				$fail[] = $serial;
				Core::logMessage('doCsvImport: Could not find serial = "' . $serial . '"');
				continue;
			}

			$save = $dbSign->updateStatus(
				[
					'is_sheet_received'   => $signCount,
					'sheet_received_date' => $deliveryDateMysql,
				],
				['ID' => $row->ID]
			);
			if ($save === false) {
				$fail[] = $serial;
				Core::logMessage(
					'doCsvImport: Could not update signature ID = "' . $row->ID . '" (serial = "' . $serial . '): '
					. Db::getLastError()
				);
				continue;
			}

			if ($hasDedup) {
				$saveDedup = $this->dedupSetSheetReceived($row->mail);
				if (!$saveDedup) {
					$fail[] = $serial;
					Core::logMessage(
						'doCsvImport: Could not update mail dedup ID = "' . $row->ID . '" mail = "' . $row->mail
						. '" (serial = "' . $serial . '): ' . Db::getLastError()
					);
					continue;
				}
			}

			if ($row->is_sheet_received && $row->is_sheet_received != $signCount) {
				$receivedAgain[] = $serial . ' (' . $row->is_sheet_received . ')';
			}
			$ok++;
			$okSign += $signCount;
		}

		// Return
		if ($ok) {
			$return .= Strings::wpMessage('Imported ' . $ok . ' sheets (total of ' . $okSign . ' signatures)', 'success');
		}
		if ($count = count($receivedAgain)) {
			$return .= Strings::wpMessage(
				$count . ' sheets were already marked as received before with another number of signatures.'
				. ' Affected serials with their old number of signatures:<br/>'
				. implode(', ', $receivedAgain),
				'warning '
			);
		}
		if ($count = count($fail)) {
			$return .= Strings::wpMessage($count . ' failed sheet(s):<br/>' . implode(', ', $fail), 'error');
		}
		return $return;
	}

	public function generateTranslations()
	{
		Core::requireAccess('demovox_sysinfo');

		$pluginDir = Infos::getPluginDir();
		$filename = $pluginDir . '/languages/demovox.adminsettings.po';
		$filenameTpl = $pluginDir . '/languages/demovox.adminsettings.template.po';

		$fp = fopen($filename, "w");
		$fpTpl = fopen($filenameTpl, "r");

		$template = fread($fpTpl, filesize($filenameTpl));
		$template = strtr($template, [
			'{version}' => DEMOVOX_VERSION,
			'{year}'    => date('Y'),
			'{date}'    => date(DATE_ATOM),
		]);
		fwrite($fp, $template . "\n");
		fclose($fpTpl);

		$title = "includes/docker/SettingsVars/ConfigSections.php";
		$contents = SettingsVars::getSections();
		$searchIns = ['title', 'sub'];
		$this->generateTranslationContent($contents, $searchIns, $title, $fp);

		$title = "includes/docker/SettingsVars/ConfigFields.php";
		$contents = SettingsVars::getFields();
		$searchIns = ['supplemental', 'label', 'options'];
		$this->generateTranslationContent($contents, $searchIns, $title, $fp);

		$title = "includes/docker/SettingsVarsCollection/ConfigSections.php";
		$contents = SettingsVarsCollection::getSections();
		$searchIns = ['title', 'sub'];
		$this->generateTranslationContent($contents, $searchIns, $title, $fp);

		$title = "includes/docker/SettingsVarsCollection/ConfigFields.php";
		$contents = SettingsVarsCollection::getFields();
		$searchIns = ['supplemental', 'label', 'options'];
		$this->generateTranslationContent($contents, $searchIns, $title, $fp);

		fclose($fp);

		echo 'Done';
	}

	/**
	 * @param array|null $contents
	 * @param array $searchIns
	 * @param string $title
	 * @param resource $fp
	 * @return void
	 */
	protected function generateTranslationContent(?array $contents, array $searchIns, string $title, $fp): void
	{
		$messages = [];
		$stack = "### $title ###\n\n";
		foreach ($contents as $content) {
			foreach ($searchIns as $searchIn)
				if (isset($content[$searchIn])) {
					if (is_array($content[$searchIn])) {
						foreach ($content[$searchIn] as $message) {
							if(is_numeric($message)){
								continue;
							}
							$message = str_replace('"', '\"', $message);
							if (!in_array($message, $messages)) {
								$messages[] = $message;
							}
						}
						continue;
					}
					$message = $content[$searchIn];
					if(is_numeric($message)){
						continue;
					}
					$message = str_replace('"', '\"', $message);
					if (!in_array($message, $messages)) {
						$messages[] = $message;
					}
				}
		}

		foreach ($messages as $message) {
			$stack .= "msgid \"$message\"\nmsgstr \"\"\n\n";
		}

		fwrite($fp, "\n$stack\n");
	}
}