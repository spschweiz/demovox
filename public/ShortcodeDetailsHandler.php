<?php

namespace Demovox;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/public
 */

/**
 * Shortcode methods for signature detail information used on the opt-in page and success page.
 *
 * @package    Demovox
 * @subpackage Demovox/public
 * @author     SP Schweiz
 */
class ShortcodeDetailsHandler extends BaseController
{
	/*
	 * Shortcode methods
	 */
	/**
	 * The [demovox_count] shortcode to show the number of signatures.
	 *
	 * @param array|string $atts    Shortcode attributes. Default empty.
	 * @return string Shortcode output.
	 */
	public function countShortcode(array $atts = [])
	{
		$collectionId = $this->getShortcodeCollectionId($atts);
		$dbSign = new DbSignatures();
		if ($sep = Config::getValue('count_thousands_sep')) {
			$count = number_format($dbSign->countSignatures($collectionId), 0, '', $sep);
		} else {
			$count = $dbSign->countSignatures($collectionId);
		}
		return $count;
	}

	public function firstNameShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['first_name']);
		if (!$row) {
			return '';
		}
		return $row->first_name;
	}

	public function lastNameShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['last_name']);
		if (!$row) {
			return '';
		}
		return $row->last_name;
	}

	public function streetShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['street']);
		if (!$row) {
			return '';
		}
		return $row->street;
	}

	public function street_noShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['street_no']);
		if (!$row) {
			return '';
		}
		return $row->street_no;
	}

	public function zipShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['zip']);
		if (!$row) {
			return '';
		}
		return $row->zip;
	}

	public function cityShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['city']);
		if (!$row) {
			return '';
		}
		return $row->city;
	}

	public function mailShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['mail']);
		if (!$row) {
			return '';
		}
		return $row->mail;
	}

	public function optInShortcode()
	{
		$this->requireHttps();

		$row = $this->getRow(['first_name']);
		if (!$row) {
			return '- Record not found -';
		}

		$signId    = $row->ID;
		$isOptIn   = $row->is_optin;
		$textOptin = Config::getValueByUserlang('text_optin');

		// Render view
		include Infos::getPluginDir() . 'public/views/opt-in.php';

		return ob_get_clean();
	}

	protected function getRow($select)
	{
		$guid = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;
		if (!$guid) {
			Core::logMessage(400 . ' - equest variable "sign" is required', 'info');
			return null;
		}
		$dbSign = new DbSignatures();
		$row    = $dbSign->getRow($select, "guid = '" . $guid . "'");
		if (!$row) {
			Core::logMessage(404 . ' - Signature with GUID "' . $guid . '" was not found', 'error');
		}
		return $row;
	}

	/**
	 * Get collection id out of shortcode params
	 *
	 * @param $atts
	 * @return int $collection
	 */
	protected function getShortcodeCollectionId($atts): int
	{
		$shortcodeAttributes = $this->getShortcodeAttriutes($atts);
		$collection = $shortcodeAttributes['collection'];
		if (!isset($shortcodeAttributes['collection']) || !is_numeric($shortcodeAttributes['collection'])) {
			$collection = $this->getDefaultCollection();
		}
		return $collection;
	}
}