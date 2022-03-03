<?php

namespace Demovox;
/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 * More about when to use deactivation or uninstall hook:
 * https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     SP Schweiz
 */
class Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		ManageCron::deactivate();
	}

}
