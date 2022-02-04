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
		if (Config::getValue('drop_tables_on_uninstall')) {
			$success = true;
			$dbs = ModelInfo::getDbServices();
			foreach ($dbs as $db) {
				$success = $success && $db->dropTable();
			}
		}

		if (Config::getValue('drop_config_on_uninstall')) {
			Config::deleteAll();
			ManageCron::deleteOptions();
		}
	}

}
