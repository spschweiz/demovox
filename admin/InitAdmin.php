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
		$this->registerSettings();
		$this->setupAdminAjaxActions();
	}

	private function loadDependencies()
	{
		$pluginDir = Infos::getPluginDir();
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $pluginDir . 'includes/models/DbCollections.php';

		require_once $pluginDir . 'admin/controllers/base/AdminBaseController.php';
		require_once $pluginDir . 'admin/controllers/base/AdminSettings.php';

		require_once $pluginDir . 'admin/controllers/AdminCollection.php';
		require_once $pluginDir . 'admin/controllers/AdminCollectionSettings.php';
		require_once $pluginDir . 'admin/controllers/AdminGeneral.php';
		require_once $pluginDir . 'admin/controllers/AdminGeneralSettings.php';

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

	protected function registerSettings()
	{
		$registerOnlyOnSave = Core::getOption('settings_no_register');
		if ($registerOnlyOnSave) {
			$isPageDemovoxSettings = isset($_REQUEST['option_page']) && substr($_REQUEST['option_page'], 0, 7) == 'demovox';
			$actionUpdate = isset($_REQUEST['action']) && $_REQUEST['action'] == 'update';
			if (!$isPageDemovoxSettings || !$actionUpdate) {
				return;
			}
		}
		// Hook into the admin menu
		Loader::addAction('admin_init', $this->adminCollectionSettings, 'registerSettings');
		Loader::addAction('admin_init', $this->adminGeneralSettings, 'registerSettings');
	}

	protected function setupAdminAjaxActions()
	{
		$prefix = 'admin_post_demovox_';

		// sysinfo
		Loader::addAction($prefix . 'run_cron', $this->adminGeneral, 'runCron');
		Loader::addAction($prefix . 'cancel_cron', $this->adminGeneral, 'cancelCron');
		Loader::addAction($prefix . 'encrypt_test', $this->adminGeneral, 'testEncrypt');

		// collection
		// - export
		Loader::addAction($prefix . 'get_csv', $this->adminCollection, 'getCsv');
		Loader::addAction($prefix . 'mail_test', $this->adminCollection, 'testMail');

		// - create new collection
		Loader::addAction($prefix . 'collection_create', $this->adminCollection, 'createNew');

		// - stats
		Loader::addAction($prefix . 'charts_stats', $this->adminCollection, 'statsCharts');
		Loader::addAction($prefix . 'source_stats', $this->adminCollection, 'statsSource');
		Loader::addAction($prefix . 'cron', $this->adminCollection, 'pageCron');

		// Cron
		Loader::addAction($prefix . 'run_cron', $this->adminCollection, 'runCron');
		Loader::addAction($prefix . 'cancel_cron', $this->adminCollection, 'cancelCron');
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
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityOverview, 'demovox', $callback);

		$menuTitle = 'Import';
		$callback = [$this->adminGeneral, 'pageImport'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilityImport, 'demovoxImport', $callback);

		$menuTitle = 'General Settings';
		$callback  = [$this->adminGeneralSettings, 'pageGeneralSettings'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySettings, 'demovoxGeneralSettings', $callback);

		$menuTitle = 'System info';
		$callback = [$this->adminGeneral, 'pageSysinfo'];
		add_submenu_page($slug, $menuTitle, $menuTitle, $capabilitySysinfo, 'demovoxSysinfo', $callback);

		// collection
		$collections = new DbCollections;
		$multipleCollections = $collections->count() > 1;
		$clnSlug = $multipleCollections ? null : $slug;

		add_submenu_page(
			$clnSlug, '',
			'<span style="display:block; margin:1px 0 1px -5px; padding:0; height:1px; background:#CCC;"></span>',
			$capabilityOverview, '#',
		);

		$menuTitle = 'Collection';
		$callback = [$this->adminCollection, 'pageOverview'];
		add_submenu_page($clnSlug, $menuTitle, $menuTitle, $capabilityOverview, 'demovoxOverview', $callback);


		$menuTitle = 'Signatures Data';
		$callback = [$this->adminCollection, 'pageData'];
		add_submenu_page($clnSlug, $menuTitle, $menuTitle, $capabilityData, 'demovoxData', $callback);

		$menuTitle = 'Settings';
		$callback = [$this->adminCollectionSettings, 'pageSettings'];
		add_submenu_page($clnSlug, $menuTitle, $menuTitle, $capabilitySettings, 'demovoxSettings', $callback);

		$menuTitle = 'Cron';
		$callback = [$this->adminCollection, 'pageCron'];
		add_submenu_page($clnSlug, $menuTitle, $menuTitle, $capabilitySettings, 'demovoxCron', $callback);
	}
}