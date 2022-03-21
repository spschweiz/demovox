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

	/** @var int */
	protected int $collectionId;

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
	public function __construct(string $pluginName, string $version)
	{
		$this->pluginName = $pluginName;
		$this->version    = $version;
	}

	public function requireHttps()
	{
		Core::enforceHttps();
	}

	protected function getDefaultCollection(): int
	{
		return Infos::getDefaultCollectionId();
	}

	/**
	 * Get attributes of a shortcode and use defaults
	 * @param array|null  $atts
	 * @param string|null $tag
	 * @param null|array  $default
	 * @return array
	 */
	protected function getShortcodeAttriutes($atts, ?string $tag = null, ?array $default = null): array
	{
		// normalize attribute keys, lowercase
		$atts = array_change_key_case((array)$atts, CASE_LOWER);

		// override default attributes with user attributes
		if ($default) {
			$atts = shortcode_atts(
				$default, $atts, $tag
			);
		}

		return $atts;
	}

	protected function setCollectionId(int $collectionId): void
	{
		$this->collectionId = $collectionId;
		Infos::setCollectionId($collectionId);
	}

	protected function getCollectionId(): int
	{
		return $this->collectionId;
	}

}