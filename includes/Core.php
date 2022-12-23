<?php

namespace Demovox;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     SP Schweiz
 */
class Core
{

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $pluginName The string used to uniquely identify this plugin.
	 */
	protected $pluginName;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected static $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('DEMOVOX_VERSION')) {
			self::$version = DEMOVOX_VERSION;
		} else {
			self::$version = '1.0.0';
		}
		$this->pluginName = 'Demovox';
	}

	/**
	 * Only needed for ajax actions, as capability of normal requests are checked on initializing their callback
	 * @param string $capability
	 * @return void
	 */
	public static function requireAccess(string $capability)
	{
		Core::checkNonce();
		if (!Core::hasAccess($capability)) {
			Core::errorDie('Capability ' . $capability . ' is missing', 403);
		}
	}

	public static function hasAccess(string $capability)
	{
		return current_user_can($capability);
	}

	public function run()
	{
		self::loadDependencies();
		$this->setLocale();

		if (is_admin()) {
			require_once Infos::getPluginDir() . 'admin/InitAdmin.php';
			$admin = new InitAdmin($this->pluginName, self::$version);
			$admin->run();
		}
		require_once Infos::getPluginDir() . 'public/InitPublic.php';
		$public = new InitPublic($this->pluginName, self::$version);
		$public->run();

		ManageCron::registerHooks();

		$this->storeSource();
		$this->hardening();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Loader. Orchestrates the hooks of the plugin.
	 * - i18n. Defines internationalization functionality.
	 * - Admin. Defines all hooks for the admin area.
	 * - PublicHandler. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public static function loadDependencies()
	{
		$pluginDir = self::getPluginDir();

		require_once $pluginDir . 'includes/CollectionTrait.php';
		require_once $pluginDir . 'includes/BaseController.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $pluginDir . 'includes/helpers/Loader.php';

		/**
		 * The helper classes
		 */
		// The class responsible for DB access and encryption.
		require_once $pluginDir . 'includes/helpers/Crypt.php';
		require_once $pluginDir . 'includes/dto/Dto.php';
		require_once $pluginDir . 'includes/dto/MailsDto.php';
		require_once $pluginDir . 'includes/dto/SignaturesDto.php';
		require_once $pluginDir . 'includes/dto/CollectionsDto.php';
		require_once $pluginDir . 'includes/models/Db.php';
		require_once $pluginDir . 'includes/models/DbMails.php';
		require_once $pluginDir . 'includes/models/DbSignatures.php';
		require_once $pluginDir . 'includes/helpers/ModelInfo.php';
		require_once $pluginDir . 'libs/php/RemoteAddress.php';
		require_once $pluginDir . 'includes/helpers/Infos.php';
		require_once $pluginDir . 'includes/helpers/Strings.php';
		require_once $pluginDir . 'includes/helpers/Settings.php';
		// The class responsible for sending mails.
		require_once $pluginDir . 'includes/helpers/Mail.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $pluginDir . 'includes/i18n.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $pluginDir . 'includes/cron/ManageCron.php';
		ManageCron::loadDependencies();

		require __DIR__ . '/../libs/composer/autoload.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the demovox_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function setLocale()
	{
		$plugin_i18n = new i18n();

		Loader::addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');
	}

	public function hardening()
	{
		if (WP_DEBUG) {
			return;
		}
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
	}

	/**
	 * Check for signature source "demovox_src" param globally
	 */
	public function storeSource()
	{
		$source = isset($_REQUEST['demovox_src']) ? sanitize_text_field($_REQUEST['demovox_src']) : '';
		if ($source) {
			Core::setSessionVar('source', $source);
		}
	}

	private static $optionPrefix = 'demovox_';

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function getWpId($id)
	{
		$fieldUidPrefix = self::$optionPrefix;
		return $fieldUidPrefix . $id;
	}

	/**
	 * @param string $id
	 *
	 * @return mixed Value set for the option. False if not set.
	 */
	public static function getOption($id)
	{
		$wpId = self::getWpId($id);
		global $wpdb;
                $query = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE " . $wpdb->options . ".option_name = '$wpId'");
                $val = $query[ 0 ]->option_value;
                return $val;
	}

	/**
	 * Update or set option
	 *
	 * @param string      $id       Option name. Expected to not be SQL-escaped.
	 * @param mixed       $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public static function setOption($id, $value, $autoload = null)
	{
		$wpId = self::getWpId($id);
		return update_option($wpId, $value, $autoload);
	}

	/**
	 * @param string $id
	 *
	 * @return bool True, if option is successfully deleted. False on failure.
	 * @param $valPart null|string
	 */
	public static function delOption($id, $valPart = null)
	{
		$fullId = $id . ($valPart ? Settings::GLUE_PART . $valPart : '');
		return delete_option(Core::getWpId($fullId));
	}

	public static function setSessionVar($name, $value) : void
	{
		$_SESSION['demovox_' . $name] = $value;
	}

	public static function getSessionVar($name)
	{
		return $_SESSION['demovox_' . $name] ?? null;
	}

	public static function createNonce($action = -1)
	{
		return wp_create_nonce($action);
	}

	protected static function checkNonce()
	{
		$actionName = isset($_REQUEST['action'])
			? sanitize_text_field($_REQUEST['action'])
			: sanitize_text_field($_REQUEST['page']);
		if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], $actionName)) {
			wp_die('Sorry, your nonce did not verify.');
		}
	}

	static function enforceHttps()
	{
		if (Infos::isHttps()) {
			return;
		}
		if (WP_DEBUG && !Settings::getValue('redirect_http_to_https')) {
			return;
		}
		wp_die(
			'<div class="demovox"><h3 class="errorTitle">Error: HTTPS required</h3><p>demovox does not allow access through an unencrypted connection</p></div>',
			'Error: HTTPS required'
		);
	}

	static function jsonResponse(array $data)
	{
		header('Content-Type: text/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	static function errorDie($error, $statusCode)
	{
		$isError = !(substr($statusCode, 0, 1) == 2 || substr($statusCode, 0, 1) == 3);
		self::logMessage($statusCode . ' - ' . $error, $isError ? 'error' : 'info');
		http_response_code($statusCode);
		switch ($statusCode) {
			default:
				$msg = __('Unknown error');
				break;
			case 400:
				$msg = __('Invalid form values received');
				break;
			case 401:
				$msg = __('Unauthorized');
				break;
			case 403:
				$msg = __('You are not allowed to access this page');
				break;
			case 404:
				$msg = __('Requested entry not found');
				break;
			case 405:
				$msg = __('Requested resource does not support this operation');
				break;
			case 500:
				$msg = __('Internal server error');
				break;
		}
		$msg = Strings::wpMessage($msg, 'error');
		wp_die($msg, $statusCode);
	}

	static function logMessage($message, $level = 'error', $type = null)
	{
		if (!WP_DEBUG_LOG && !WP_DEBUG) {
			return;
		}
		$trace  = debug_backtrace();
		$source = $trace[1];
		if ($source['function'] == 'showError' && $source['function'] == 'Demovox\Core') {
			$source = $trace[2];
		}
		$date   = date('Y-m-d G:i:s', time());
		$string = $date . ' [' . $level . '] ' . $source['file'] . ':' . $source['line'] . "\n" . $message . "\n";

		$fn = Infos::getPluginDir() . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'debug.demovox' . ($type ? '.' . $type : '')
			  . '.php';
		if (!file_exists($fn)) {
			$string = '<?php die(\'silenzio\') ?>' . "\n" . $string;
		}
		$fp = fopen($fn, 'a');
		fputs($fp, $string);
		fclose($fp);
		if (WP_DEBUG_DISPLAY) {
			if (self::isPluginPage() && $level !== 'error') {
				return;
			}
			echo $string;
		}
	}

	public static function getPluginDir()
	{
		return plugin_dir_path(dirname(__FILE__));
	}

	public static function isPluginPage()
	{
		global $pagenow;
		return $pagenow === 'plugins.php' && is_admin();
	}

	public static function isPluginEnabled()
	{
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		return is_plugin_active('demovox/demovox.php');
	}

	/**
	 * @param string $src
	 * @return string
	 */
	public static function prependPluginUri(string $src): string
	{
		return $src ? self::getPluginUri() . $src : '';
	}

	/**
	 * Public uri to the plugin's base directory
	 * @return string
	 */
	protected static function getPluginUri(): string
	{
		return plugin_dir_url(dirname(__FILE__));
	}

	public static function addScript($handle, string $src = '', array $deps = [], bool $in_footer = false, $ver = 'demovox'): void
	{
		$src = self::prependPluginUri($src);
		$ver = $ver === 'demovox' ? self::$version : $ver;
		wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
	}

	public static function addStyle($handle, string $src = '', array $deps = [], string $media = 'all', $ver = 'demovox'): void
	{
		$src = self::prependPluginUri($src);
		$ver = $ver === 'demovox' ? self::$version : $ver;
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}

}
