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
 * @author     SP Schweiz
 */
class InitAdmin extends BaseController
{
	/**
	 * @var AdminGeneral
	 */
	protected $adminGeneral;
	/**
	 * @var AdminGeneralSettings
	 */
	protected $adminGeneralSettings;
	/**
	 * @var AdminCollection
	 */
	protected $adminCollection;
	/**
	 * @var AdminCollectionSettings
	 */
	protected $adminCollectionSettings;

	public function run()
	{
		$this->loadDependencies();
		$this->adminGeneral            = new AdminGeneral($this->getPluginName(), $this->getVersion());
		$this->adminGeneralSettings    = new AdminGeneralSettings($this->getPluginName(), $this->getVersion());
		$this->adminCollection         = new AdminCollection($this->getPluginName(), $this->getVersion());
		$this->adminCollectionSettings = new AdminCollectionSettings($this->getPluginName(), $this->getVersion());

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
		require_once $pluginDir . 'includes/models/DbCollections.php';

		require_once $pluginDir . 'admin/controllers/AdminCollection.php';
		require_once $pluginDir . 'admin/controllers/AdminCollectionSettings.php';
		require_once $pluginDir . 'admin/controllers/AdminGeneral.php';
		require_once $pluginDir . 'admin/controllers/AdminGeneralSettings.php';

		require_once $pluginDir . 'admin/controllers/AdminSettings.php';
		require_once $pluginDir . 'admin/helpers/CollectionStatsDto.php';
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
		Loader::addAction('admin_init', $this->adminCollectionSettings, 'setupFields');
		Loader::addAction('admin_init', $this->adminCollectionSettings, 'setupSections');
	}

	protected function setupAdminAjaxActions()
	{
		$prefix = 'admin_post_demovox_';

		// manage_options
		Loader::addAction($prefix . 'run_cron', $this->adminGeneral, 'runCron');
		Loader::addAction($prefix . 'cancel_cron', $this->adminGeneral, 'cancelCron');
		Loader::addAction($prefix . 'encrypt_test', $this->adminGeneral, 'testEncrypt');
		Loader::addAction($prefix . 'mail_test', $this->adminGeneral, 'testMail');

		// export
		Loader::addAction($prefix . 'get_csv', $this->adminCollection, 'getCsv');

		// demovox_stats
		Loader::addAction($prefix . 'charts_stats', $this->adminCollection, 'statsCharts');
		Loader::addAction($prefix . 'source_stats', $this->adminCollection, 'statsSource');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles()
	{
		wp_enqueue_style($this->getPluginName(), plugin_dir_url(__FILE__) . 'css/demovox-admin.min.css', [], $this->getVersion(), 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts()
	{
		wp_enqueue_script(
			$this->getPluginName() . '_admin',
			plugin_dir_url(__FILE__) . 'js/demovox-admin.min.js',
			['jquery'],
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
		wp_localize_script($this->getPluginName() . '_admin', 'demovoxData', $demovoxJsArr);
	}

	public function setupAdminMenu()
	{
		// Add the menu item and page
		$slug       = 'demovox';
		$icon       = 'dashicons-edit';
		$position   = 30;

		$capabilityOverview = 'demovox';
		$capabilitySysinfo   = 'demovox_sysinfo';
		$capabilityData   = 'demovox_data';
		$capabilityImport   = 'demovox_import';
		$capabilitySettings = 'manage_options';

		$menuTitle = 'demovox';
		$callback  = [$this->adminGeneral, 'pageOverview'];
		add_menu_page($menuTitle, $menuTitle, $capabilityOverview, $slug, $callback, $icon, $position);

		$menuTitle = 'Overview';
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityOverview, $slug, $callback);

		$menuTitle = 'System info';
		$callback = [$this->adminGeneral, 'pageSysinfo'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySysinfo, $slug . 'Sysinfo', $callback);

		$menuTitle = 'General Settings';
		$callback  = [$this->adminGeneralSettings, 'pageGeneralSettings'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySettings, $slug . 'GeneralSettings', $callback);

		$collections = new DbCollections;
		if ($collections->count() < 2) {
			add_submenu_page(
				$slug, '',
				'<span style="display:block; margin:1px 0 1px -5px; padding:0; height:1px; background:#CCC;"></span>',
				"create_users", "#",
			);

			$menuTitle = 'Collection';
			$callback = [$this->adminCollection, 'pageOverview'];
			add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityOverview, $slug . 'Overview', $callback);

			$menuTitle = 'Signatures Data';
			$callback = [$this->adminCollection, 'pageData'];
			add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityData, $slug . 'Data', $callback);

			$menuTitle = 'Import';
			$callback = [$this->adminCollection, 'pageImport'];
			add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityImport, $slug . 'Import', $callback);

			$menuTitle = 'Settings';
			$callback = [$this->adminCollectionSettings, 'pageSettings'];
			add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySettings, $slug . 'Settings', $callback);
		}
	}
}