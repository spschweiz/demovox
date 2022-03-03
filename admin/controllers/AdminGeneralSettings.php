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
class AdminGeneralSettings extends AdminSettings
{
	public function pageGeneralSettings()
	{
		$page = 'demovoxFieldsGlobal';
		$languages = i18n::getLangs();
		include Infos::getPluginDir() . 'admin/views/general-settings/settings.php';
	}
}