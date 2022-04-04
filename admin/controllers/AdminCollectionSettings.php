<?php

namespace Demovox;
require_once Infos::getPluginDir() . 'admin/controllers/base/AdminSettings.php';
require_once Infos::getPluginDir() . 'admin/helpers/SignatureList.php';

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
class AdminCollectionSettings extends AdminSettings
{
	use CollectionTrait;

	public function __construct(string $pluginName, string $version)
	{
		parent::__construct($pluginName, $version);
		$this->setCollectionIdByReq();
	}

	public function pageSettings()
	{
		$this->registerSettings();

		$tabs = [
			'General',
			'Sign-up form',
			'Success page',
			'Signature sheet',
			'Email',
			'API',
		];
		$firstTab = array_keys($tabs);
		$currentTab = !empty($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? sanitize_title($_GET['tab']) : $firstTab[0];
		$page = 'demovoxSettings';
		$languages = i18n::getLangs();
		$collectionId = $this->getCollectionId();
		$collectionName = $this->getCollectionName();

		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-tabs.php';
	}

	protected function pageSettings0()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields0';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-0.php';
	}

	protected function pageSettings1()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields1';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-1.php';
	}

	protected function pageSettings2()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields2';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-2.php';
	}

	protected function pageSettings3()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields3';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-3.php';
	}

	protected function pageSettings4()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields4';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-4.php';
	}

	protected function pageSettings5()
	{
		$collectionId = $this->getCollectionId();
		$page = $this->getCollectionId() . Settings::GLUE_PART . 'demovoxFields5';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-5.php';
	}

	public function registerSettings(): void
	{
		require_once Core::getPluginDir() . 'admin/helpers/RegisterSettings.php';
		require_once Core::getPluginDir() . 'admin/helpers/RegisterSettingsCollection.php';

		$settings = new RegisterSettingsCollection($this);
		$settings->register();
	}
}