<?php

namespace Demovox;

class InitPublic extends BaseController
{
	/**
	 * @var PublicHandler
	 */
	protected $publicHandler;
	public function run()
	{
		$this->loadDependencies();
		$this->publicHandler = new PublicHandler($this->getPluginName(), $this->getVersion());

		$this->defineActions();
		$this->defineShortcodes();
	}

	private function loadDependencies()
	{
		$pluginDir = Infos::getPluginDir();

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $pluginDir . 'public/PublicHandler.php';
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function defineActions()
	{

		Loader::addAction('init', $this, 'startSession');
		Loader::addAction('wp_enqueue_scripts', $this, 'enqueueStyles');
		Loader::addAction('wp_enqueue_scripts', $this, 'enqueueScripts');

		// demovox_form shortcode ajax
		Loader::addAjaxPublic('demovox_step2', $this->publicHandler, 'signStep2');
		Loader::addAjaxPublic('demovox_step3', $this->publicHandler, 'signStep3');
		Loader::addAjaxPublic('demovox_countries', $this->publicHandler, 'getCountries');
		Loader::addAjaxPublic('demovox_test', $this->publicHandler, 'getCountries');

		// demovox_optin shortcode ajax
		Loader::addAjaxPublic('demovox_optin', $this->publicHandler, 'saveOptIn');
	}

	private function defineShortcodes()
	{
		// Shortcodes
		Loader::addShortcode('demovox_form', $this->publicHandler, 'signShortcode');
		Loader::addShortcode('demovox_count', $this->publicHandler, 'countShortcode');
		Loader::addShortcode('demovox_firstname', $this->publicHandler, 'firstNameShortcode');
		Loader::addShortcode('demovox_lastname', $this->publicHandler, 'lastNameShortcode');
		Loader::addShortcode('demovox_optin', $this->publicHandler, 'optInShortcode');

		// Deprecated shortcodes
		Loader::addShortcode('demovox_form_shortcode', $this->publicHandler, 'signShortcode');
		Loader::addShortcode('demovox_count_shortcode', $this->publicHandler, 'countShortcode');
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles()
	{
		wp_enqueue_style($this->getPluginName(), plugin_dir_url(__FILE__) . 'css/demovox-public.min.css', [], $this->getVersion(), 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts()
	{
		$successPage  = Config::getValue('use_page_as_success');
		$demovoxJsArr = [
			'language'          => Infos::getUserLanguage(),
			'ajaxUrl'           => admin_url('admin-ajax.php'),
			'nonce'             => Core::createNonce($this->nonceId),
			'apiAddressEnabled' => '',
			'successPageRedir'  => $successPage || $successPage === '0',
		];
		if ($apiAddressUrl = Config::getValue('api_address_url')) {
			$demovoxJsArr['apiAddressEnabled']   = 1;
			$demovoxJsArr['apiAddressKey']       = Config::getValue('api_address_key');
			$demovoxJsArr['apiAddressUrl']       = $apiAddressUrl;
			$demovoxJsArr['apiAddressCityInput'] = Config::getValue('api_address_city_input');
			$demovoxJsArr['apiAddressGdeInput']  = Config::getValue('api_address_gde_input');
			$demovoxJsArr['apiAddressGdeSelect'] = Config::getValue('api_address_gde_select');
		}

		wp_enqueue_script(
			$this->getPluginName(),
			plugin_dir_url(__FILE__) . 'js/demovox-public.min.js',
			['jquery', 'jquery-ui-datepicker'],
			$this->getVersion(),
			false
		);
		wp_enqueue_script(
			$this->getPluginName() . '_pdf',
			plugin_dir_url(__FILE__) . 'js/demovox-public-pdf.min.js',
			['jquery'],
			$this->getVersion(),
			false
		);
		wp_localize_script($this->getPluginName(), 'demovox', $demovoxJsArr);
	}

	public function startSession()
	{
		if (!session_id() && !headers_sent()) //check if session already exists
		{
			session_start();
		}
	}
}