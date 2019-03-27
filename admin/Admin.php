<?php

namespace Demovox;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/admin
 */

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
class Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $pluginName The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $pluginName The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct($pluginName, $version)
	{
		$this->pluginName = $pluginName;
		$this->version = $version;

		// Hook into the admin menu
		add_action('admin_menu', [$this, 'setupAdminPages']);
		add_action('admin_post_get_csv', [$this, 'getCsv']);
		add_action('admin_post_run_cron', [$this, 'runCron']);
		add_action('admin_post_cancel_cron', [$this, 'cancelCron']);
		add_action('admin_post_charts_stats', [$this, 'statsCharts']);
		add_action('admin_post_source_stats', [$this, 'statsSource']);
		add_action('admin_post_encrypt_test', [$this, 'testEncrypt']);
		add_action('admin_post_mail_test', [$this, 'testMail']);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . 'css/demovox-admin.min.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->pluginName . '_admin', plugin_dir_url(__FILE__) . 'js/demovox-admin.min.js', ['jquery'], $this->version, false);
		wp_enqueue_script($this->pluginName . '_chart', plugin_dir_url(__FILE__) . 'js/Chart.bundle.min.js', [], $this->version, false);
		wp_enqueue_script(
			$this->pluginName . '_pdf',
			plugin_dir_url(__FILE__) . '../public/js/demovox-pdf.min.js',
			['jquery'],
			$this->version,
			false
		);

		wp_enqueue_media();
		$demovoxJsArr = [
			'uploader' => [
				'title' => 'Select signature sheet',
				'text'  => 'Select',
			],
		];
		wp_localize_script($this->pluginName . '_admin', 'demovoxAdmin', $demovoxJsArr);
		//add_action( 'admin_enqueueScripts', [$this, 'loadBloodyTinymce']);
	}

	public function getCsv()
	{
		Core::checkNonce();
		$csvMapper = DB::getExportFields();
		$csv = implode(',', $csvMapper) . "\n";
		$allSignatures = DB::getResults(array_keys($csvMapper));

		foreach ($allSignatures as $signature) {
			$csvSignature = [];

			foreach ($csvMapper as $key => $value) {
				$valueEscaped = str_replace('"', '""', $signature->$key);
				$csvSignature[] = '"' . $valueEscaped . '"';
			}

			$csv .= implode(',', $csvSignature) . "\n";
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"" . $this->pluginName . "-Export.csv\";");
		header("Content-Transfer-Encoding: binary");

		echo $csv;
	}

	/**
	 * todo: remove if it stays unused
	 */
	function loadBloodyTinymce()
	{
		wp_enqueue_script('bloody_tinymce_js_main', includes_url() . 'js/tinymce/tinymce.min.js');
		wp_enqueue_script('bloody_tinymce_js_plugin', includes_url() . 'js/tinymce/plugins/compat3x/plugin.min.js');
	}

	public function setupAdminPages()
	{
		require_once Infos::getPluginDir() . 'admin/AdminSettings.php';
		$adminSettings = new AdminSettings($this->pluginName, $this->version);

		// Add the menu item and page
		$page_title = 'Overview';
		$menu_title = 'demovox';
		$capability = 'manage_options';
		$slug = 'demovox';
		$callback = [$this, 'pageOverview'];
		$icon = 'dashicons-edit';
		$position = 30;
		add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);

		$menu_title = 'Import';
		$callback = [$this, 'pageImport'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Import', $callback);

		$menu_title = 'Signature sheet';
		$callback = [$adminSettings, 'pageSettings1'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Fields1', $callback);

		$menu_title = 'Email';
		$callback = [$adminSettings, 'pageSettings2'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Fields2', $callback);

		$menu_title = 'Opt-in';
		$callback = [$adminSettings, 'pageSettings3'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Fields3', $callback);

		$menu_title = 'Advanced settings';
		$callback = [$adminSettings, 'pageSettings4'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Fields4', $callback);

		$menu_title = 'System info';
		$callback = [$this, 'pageSysinfo'];
		add_submenu_page($slug, $menu_title, $menu_title, $capability, $slug . 'Sysinfo', $callback);
	}

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
		$mailRecipient = $this->getTestMailRecipient();
		$languages = i18n::$languages;

		include Infos::getPluginDir() . 'admin/partials/sysinfo.php';
	}

	public function pageSettings1()
	{
		$page = 'demovoxFields1';
		$languages = i18n::$languages;
		include Infos::getPluginDir() . 'admin/partials/settings-1.php';
	}

	public function pageSettings2()
	{
		$page = 'demovoxFields2';
		include Infos::getPluginDir() . 'admin/partials/settings-2.php';
	}

	public function pageSettings3()
	{
		$page = 'demovoxFields3';
		include Infos::getPluginDir() . 'admin/partials/settings-3.php';
	}

	public function pageSettings4()
	{
		$page = 'demovoxFields4';
		include Infos::getPluginDir() . 'admin/partials/settings-4.php';
	}

	public function statsCharts()
	{
		Core::checkNonce('charts');
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
		Core::checkNonce('encrypt_test');
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
		Core::checkNonce('encrypt_test');
		$mailTo = $this->getTestMailRecipient();
		$langId = (isset($_REQUEST['lang']) && $_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : 'de';
		$mailFrom = Config::getValue('mail_reminder_from_address_' . $langId);
		$nameFrom = Config::getValueByLang('mail_reminder_from_name', $langId);

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

	public function runCron()
	{
		Core::checkNonce();
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You are not allowed to run cron events.', 'wp-control'));
		}
		$hook = sanitize_text_field($_REQUEST['cron']);
		ManageCron::triggerCron($hook);
		echo 'Event triggered at ' . date('d.m.Y I:m:s');
	}

	public function cancelCron()
	{
		Core::checkNonce();
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You are not allowed to run cron events.', 'wp-control'));
		}
		ManageCron::cancelMail();
		echo 'Cron cancelled at ' . date('d.m.Y I:m:s');
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

	protected function getTestMailRecipient()
	{
		return get_option('admin_email');
	}
}