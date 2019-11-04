<?php

namespace Demovox;

class BaseController
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $pluginName The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/** @var $nonceId string */
	protected $nonceId = 'demovox_ajax_submit';

	/**
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}
	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $pluginName The name of the plugin.
	 * @param string $version    The version of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct($pluginName, $version)
	{
		$this->pluginName = $pluginName;
		$this->version    = $version;
	}

}