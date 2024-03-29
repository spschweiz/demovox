<?php

namespace Demovox;

class InitPublic extends BaseController
{
	/**
	 * @var PublicHandler
	 */
	protected $publicHandler;
	/**
	 * @var ShortcodeDetailsHandler
	 */
	protected $shortcodeDetailsHandler;

	public function run()
	{
		$this->loadDependencies();
		$this->publicHandler = new PublicHandler($this->getPluginName(), $this->getVersion());
		$this->shortcodeDetailsHandler = new ShortcodeDetailsHandler($this->getPluginName(), $this->getVersion());

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
		require_once $pluginDir . 'public/ShortcodeDetailsHandler.php';
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

		// demovox_form shortcode ajax
		Loader::addAjaxPublic('demovox_step2', $this->publicHandler, 'signStep2');
		Loader::addAjaxPublic('demovox_step3', $this->publicHandler, 'signStep3');
		Loader::addAjaxPublic('demovox_countries', $this->publicHandler, 'getCountries');

		// demovox_optin shortcode ajax
		Loader::addAjaxPublic('demovox_optin', $this->publicHandler, 'saveOptIn');
	}

	private function defineShortcodes()
	{
		// Shortcodes
		Loader::addShortcode('demovox_form', $this->publicHandler, 'signShortcode');
		Loader::addShortcode('demovox_count', $this->shortcodeDetailsHandler, 'countShortcode');
		Loader::addShortcode('demovox_firstname', $this->shortcodeDetailsHandler, 'firstNameShortcode');
		Loader::addShortcode('demovox_lastname', $this->shortcodeDetailsHandler, 'lastNameShortcode');
		Loader::addShortcode('demovox_street', $this->shortcodeDetailsHandler, 'streetShortcode');
		Loader::addShortcode('demovox_street_no', $this->shortcodeDetailsHandler, 'street_noShortcode');
		Loader::addShortcode('demovox_zip', $this->shortcodeDetailsHandler, 'zipShortcode');
		Loader::addShortcode('demovox_city', $this->shortcodeDetailsHandler, 'cityShortcode');
		Loader::addShortcode('demovox_mail', $this->shortcodeDetailsHandler, 'mailShortcode');
		Loader::addShortcode('demovox_optin', $this->shortcodeDetailsHandler, 'optInShortcode');

		// Deprecated shortcodes
		Loader::addShortcode('demovox_form_shortcode', $this->publicHandler, 'signShortcode');
		Loader::addShortcode('demovox_count_shortcode', $this->shortcodeDetailsHandler, 'countShortcode');
	}

	public function startSession()
	{
		if (!session_id() && !headers_sent()) //check if session already exists
		{
			session_start();
		}
	}
}