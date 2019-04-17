<?php

namespace Demovox;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/public
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class PublicHandler
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

	/** @var $nonceId string */
	private $nonceId = 'demovox_ajax_submit';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $pluginName The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @since 1.0.0
	 */
	public function __construct($pluginName, $version)
	{
		$this->pluginName = $pluginName;
		$this->version = $version;

		add_action('init', [$this, 'initPlugin']);

		// Shortcodes
		add_shortcode('demovox_form', [$this, 'signShortcode',]);
		add_shortcode('demovox_count', [$this, 'countShortcode',]);
		add_shortcode('demovox_optin', [$this, 'optInShortcode',]);

		// Deprecated
		add_shortcode('demovox_form_shortcode', [$this, 'signShortcode',]);
		add_shortcode('demovox_count_shortcode', [$this, 'countShortcode',]);

		// demovox_form shortcode ajax
		add_action('wp_ajax_demovox_step2', [$this, 'signStep2',]);
		add_action('wp_ajax_nopriv_demovox_step2', [$this, 'signStep2',]);
		add_action('wp_ajax_demovox_step3', [$this, 'signStep3',]);
		add_action('wp_ajax_nopriv_demovox_step3', [$this, 'signStep3',]);
		add_action('wp_ajax_demovox_countries', [$this, 'getCountries',]);
		add_action('wp_ajax_nopriv_demovox_countries', [$this, 'getCountries',]);
		add_action('wp_ajax_demovox_test', [$this, 'getCountries',]);
		add_action('wp_ajax_nopriv_demovox_test', [$this, 'getCountries',]);

		// demovox_optin shortcode ajax
		add_action('wp_ajax_demovox_optin', [$this, 'saveOptIn',]);
		add_action('wp_ajax_nopriv_demovox_optin', [$this, 'saveOptIn',]);
	}

	public function initPlugin()
	{
		if (!session_id() && !headers_sent()) //checking if session already exists
		{
			session_start();
		}
	}

	public function init()
	{
		Core::enforceHttps();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles()
	{
		wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . 'css/demovox-public.min.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts()
	{
		$successPage = Config::getValue('use_page_as_success');
		$demovoxJsArr = [
			'language'          => Infos::getUserLanguage(),
			'ajaxUrl'           => admin_url('admin-ajax.php'),
			'nonce'             => Core::createNonce($this->nonceId),
			'apiAddressEnabled' => '',
			'successPageRedir'  => $successPage || $successPage === '0',
		];
		if ($apiAddressKey = Config::getValue('api_address_key')) {
			$demovoxJsArr['apiAddressEnabled'] = 1;
			$demovoxJsArr['apiAddressKey'] = $apiAddressKey;
			$demovoxJsArr['apiAddressUrl'] = Config::getValue('api_address_url');//'https://test-tel.sp-ps.ch/signatures/commune_suggestion';
			$demovoxJsArr['apiAddressCityInput'] = Config::getValue('api_address_city_input');
			$demovoxJsArr['apiAddressGdeInput'] = Config::getValue('api_address_gde_input');
			$demovoxJsArr['apiAddressGdeSelect'] = Config::getValue('api_address_gde_select');
		}

		wp_enqueue_script(
			$this->pluginName,
			plugin_dir_url(__FILE__) . 'js/demovox-public.min.js',
			['jquery', 'jquery-ui-datepicker'],
			$this->version,
			false
		);
		wp_enqueue_script(
			$this->pluginName . '_pdf',
			plugin_dir_url(__FILE__) . 'js/demovox-public-pdf.min.js',
			['jquery'],
			$this->version,
			false
		);
		wp_localize_script($this->pluginName, 'demovox', $demovoxJsArr);
	}

	/*
	 * Shortcode methods
	 */
	public function countShortcode()
	{
		return DB::countSignatures();
	}

	public function optInShortcode()
	{
		$this->init();

		ob_start();
		$guid = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;
		if (!$guid) {
			return 'request variable "sign" is required';
		}
		$row = DB::getRow(['ID', 'is_optin'], "guid = '" . $guid . "'");
		if (!$row) {
			return 'Signature with GUID "' . $guid . '" was not found';
		}
		$signId = $row->ID;
		$isOptIn = $row->is_optin;
		$textOptin = Config::getValueByUserlang('text_optin');

		// Render view
		include Infos::getPluginDir() . 'public/partials/opt-in.php';

		return ob_get_clean();
	}

	public function saveOptIn()
	{
		$optIn = isset($_REQUEST['is_optin']) && $_REQUEST['is_optin'] ? 1 : 0;
		$guid = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;

		// Save data
		$success = DB::updateStatus(
			[
				'is_optin'  => $optIn,
				'edit_date' => current_time('mysql'),
			],
			['guid' => $guid,]
		);
		if (!$success) {
			Core::showError('DB update failed: ' . DB::getError(), 500);
		}
		wp_die(Strings::wpMessage(__('Your settings were saved', 'demovox'), 'success'));
	}

	public function signShortcode()
	{
		$this->init();

		if (isset($_REQUEST['sign']) && !empty($_REQUEST['sign'])) {
			return $this->signStep(3);
		} else {
			return $this->signStep(1);
		}
	}

	public function signStep2()
	{
		$this->init();
		$this->signStep(2);
	}

	public function signStep3()
	{
		$this->init();
		$this->signStep(3);
	}

	public function getCountries()
	{
		header("Pragma: public");
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24))); // 1 day
		header("Cache-Control: public");
		header("Content-Type: text/json");
		Strings::getCountries('json');
		exit; // exit is required to keep Wordpress from echoing a trailing "0"
		// https://wordpress.stackexchange.com/questions/97502/admin-ajax-is-returning-0
	}

	protected function signStep($nr)
	{
		$pluginDir = Infos::getPluginDir();
		if (Infos::isNoEc6()) {
			ob_start();

			$pdfUrl = Config::getValueByUserlang('signature_sheet');
			include $pluginDir . 'public/partials/fallback.php';

			$output = ob_get_clean();
			return $output;
		}

		require_once $pluginDir . 'public/SignSteps.php';
		$sign = new SignSteps($this->nonceId);

		ob_start();
		switch ($nr) {
			case 1:
			default:
				$sign->step1();
				break;
			case 2:
				$sign->step2();
				break;
			case 3:
				$guid = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;
				$sign->step3($guid);
				break;
		}
		$output = ob_get_clean();
		return $output;
	}
}