<?php

namespace Demovox;
require_once Infos::getPluginDir() . 'admin/controllers/AdminSettings.php';
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
	public function pageSettings()
	{
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
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-tabs.php';
	}

	public function pageSettings0()
	{
		$page = 'demovoxFields0';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-0.php';
	}

	public function pageSettings1()
	{
		$page = 'demovoxFields1';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-1.php';
	}

	public function pageSettings2()
	{
		$page = 'demovoxFields2';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-2.php';
	}

	public function pageSettings3()
	{
		$page = 'demovoxFields3';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-3.php';
	}

	public function pageSettings4()
	{
		$page = 'demovoxFields4';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-4.php';
	}

	public function pageSettings5()
	{
		$page = 'demovoxFields5';
		include Infos::getPluginDir() . 'admin/views/collection-settings/settings-5.php';
	}
}