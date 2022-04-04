<?php

namespace Demovox;

/**
 * Fired before plugin uninstall
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

/**
 * Fired during plugin Uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
 * More about when to use deactivation or uninstall hook:
 * https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     SP Schweiz
 */
class Uninstaller
{

	/**
	 * Short Description. (use period)
	 *
	 * Removes database table.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall()
	{
		if (Settings::getValue('drop_tables_on_uninstall')) {
			$success = true;
			$dbs = ModelInfo::getDbServices();
			foreach ($dbs as $db) {
				$success = $success && $db->dropTable();
			}
		}

		if (Settings::getValue('drop_config_on_uninstall')) {
			self::removeCap();
			Settings::deleteAll();
			ManageCron::deleteOptions();
		}
	}

	/**
	 * remove capabilities
	 * @return void
	 */
	protected static function removeCap() {
		Core::delOption('init_capabilities_version');
		$role = get_role('super admin');
		if ($role) {
			$role->remove_cap('demovox');
			$role->remove_cap('demovox_stats');
			$role->remove_cap('demovox_import');
			$role->remove_cap('demovox_export');
			$role->remove_cap('demovox_data');
			$role->remove_cap('demovox_edit_collection');
			$role->remove_cap('demovox_sysinfo');
		}

		$role = get_role('administrator');
		$role->remove_cap('demovox');
		$role->remove_cap('demovox_stats');
		$role->remove_cap('demovox_import');
		$role->remove_cap('demovox_export');
		$role->remove_cap('demovox_data');
		$role->remove_cap('demovox_edit_collection');
		$role->remove_cap('demovox_sysinfo');

		$role = get_role('editor');
		$role->remove_cap('demovox');
		$role->remove_cap('demovox_stats');
		$role->remove_cap('demovox_import');
		$role->remove_cap('demovox_export');
		$role->remove_cap('demovox_data');
		$role->remove_cap('demovox_edit_collection');
		$role->remove_cap('demovox_sysinfo');

		$role = get_role('author');
		$role->remove_cap('demovox');
		$role->remove_cap('demovox_stats');
		$role->remove_cap('demovox_import');
	}
}
