<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

namespace Demovox;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     Fabian Horlacher / SP Schweiz
 */
class Loader
{
	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress action that is being registered.
	 * @param object $component     A reference to the instance of the object on which the action is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @since    1.0.0
	 */
	public static function addAction($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		add_action(
			$hook,
			[$component, $callback],
			$priority,
			$accepted_args
		);
	}

	public static function addAjaxPublic($actionName, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$hook = 'wp_ajax_' . $actionName;
		add_action(
			$hook,
			[$component, $callback],
			$priority,
			$accepted_args
		);
		$hook = 'wp_ajax_nopriv_' . $actionName;
		add_action(
			$hook,
			[$component, $callback],
			$priority,
			$accepted_args
		);
	}

	public static function addShortcode($tag, $component, $callback)
	{
		add_shortcode($tag, [$component, $callback]);
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress filter that is being registered.
	 * @param object $component     A reference to the instance of the object on which the filter is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
	 *
	 * @since    1.0.0
	 */
	public static function addFilter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		add_filter(
			$hook,
			[$component, $callback],
			$priority,
			$accepted_args
		);
	}

}
