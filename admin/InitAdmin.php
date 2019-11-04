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
 * @author     Fabian Horlacher / SP Schweiz
 */
class InitAdmin extends Base
{
	/**
	 * @var AdminPages
	 */
	protected $adminPages;
	/**
	 * @var AdminSettings
	 */
	protected $adminSettings;

	public function run()
	{
		$this->loadDependencies();
		$this->adminPages    = new AdminPages($this->getPluginName(), $this->getVersion());
		$this->adminSettings = new AdminSettings($this->getPluginName(), $this->getVersion());

		$this->defineHooks();
		$this->setupAdminSettingsActions();
		$this->setupAdminAjaxActions();
	}

	private function loadDependencies()
	{
		$pluginDir = Infos::getPluginDir();
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $pluginDir . 'admin/AdminPages.php';
		require_once $pluginDir . 'admin/AdminSettings.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function defineHooks()
	{
		Loader::addAction('admin_enqueue_scripts', $this, 'enqueueStyles');
		Loader::addAction('admin_enqueue_scripts', $this, 'enqueueScripts');
		Loader::addAction('admin_menu', $this, 'setupAdminMenu');
	}

	protected function setupAdminSettingsActions()
	{
		// Hook into the admin menu
		Loader::addAction('admin_init', $this->adminSettings, 'setupFields');
		Loader::addAction('admin_init', $this->adminSettings, 'setupSections');
	}

	protected function setupAdminAjaxActions()
	{
		$prefix = 'admin_post_demovox_';

		// export
		Loader::addAction($prefix . 'get_csv', $this->adminPages, 'getCsv');

		// manage_options
		Loader::addAction($prefix . 'run_cron', $this->adminPages, 'runCron');
		Loader::addAction($prefix . 'cancel_cron', $this->adminPages, 'cancelCron');
		Loader::addAction($prefix . 'encrypt_test', $this->adminPages, 'testEncrypt');
		Loader::addAction($prefix . 'mail_test', $this->adminPages, 'testMail');

		// demovox_stats
		Loader::addAction($prefix . 'charts_stats', $this->adminPages, 'statsCharts');
		Loader::addAction($prefix . 'source_stats', $this->adminPages, 'statsSource');
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

		wp_enqueue_style($this->getPluginName(), plugin_dir_url(__FILE__) . 'css/demovox-admin.min.css', [], $this->getVersion(), 'all');
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

		wp_enqueue_script(
			$this->getPluginName() . '_admin',
			plugin_dir_url(__FILE__) . 'js/demovox-admin.min.js',
			['jquery'],
			$this->getVersion(),
			false
		);
		wp_enqueue_script(
			$this->getPluginName() . '_chart',
			plugin_dir_url(__FILE__) . 'js/Chart.bundle.min.js',
			[],
			$this->getVersion(),
			false
		);
		wp_enqueue_script(
			$this->getPluginName() . '_pdf',
			plugin_dir_url(__FILE__) . '../public/js/demovox-public-pdf.min.js',
			['jquery'],
			$this->getVersion(),
			false
		);

		wp_enqueue_media();
		$demovoxJsArr = [
			'uploader' => [
				'title' => 'Select signature sheet',
				'text'  => 'Select',
			],
		];
		wp_localize_script($this->getPluginName() . '_admin', 'demovoxAdmin', $demovoxJsArr);
	}

	public function setupAdminMenu()
	{
		// Add the menu item and page
		$page_title = 'Overview';
		$slug       = 'demovox';
		$icon       = 'dashicons-edit';
		$position   = 30;

		$capabilityOverview = 'demovox_overview';
		$capabilityExport   = 'export';
		$capabilityImport   = 'demovox_import';
		$capabilitySettings = 'manage_options';

		$menuTitle = 'demovox';
		$callback  = [$this->adminPages, 'pageOverview'];
		add_menu_page($page_title, $menuTitle, $capabilityOverview, $slug, $callback, $icon, $position);

		$menuTitle = 'Signatures Data';
		$callback  = [$this->adminPages, 'pageData'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityExport, $slug . 'Data', $callback);

		$menuTitle = 'Import';
		$callback  = [$this->adminPages, 'pageImport'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityImport, $slug . 'Import', $callback);

		$menuTitle = 'Settings';
		$callback  = [$this->adminSettings, 'pageSettings'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySettings, $slug . 'Settings', $callback);

		$menuTitle = 'System info';
		$callback  = [$this->adminPages, 'pageSysinfo'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySettings, $slug . 'Sysinfo', $callback);
	}
}