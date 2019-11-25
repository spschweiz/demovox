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
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/public
 * @author     Fabian Horlacher / SP Schweiz
 */
class PublicHandler extends BaseController
{
	public function requireHttps()
	{
		Core::enforceHttps();
	}

	/*
	 * Shortcode methods
	 */
	public function countShortcode()
	{
		$dbSign = new DbSignatures();
		if ($sep = Config::getValue('count_thousands_sep')) {
			$count = number_format($dbSign->countSignatures(), 0, '', $sep);
		} else {
			$count = $dbSign->countSignatures();
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
		include Infos::getPluginDir() . 'public/partials/opt-in.php';

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

	public function saveOptIn()
	{
		$dbSign = new DbSignatures();
		$this->requireHttps();

		$optIn = isset($_REQUEST['is_optin']) && $_REQUEST['is_optin'] ? 1 : 0;
		$guid  = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;

		// Save data
		$success = $dbSign->updateStatus(
			[
				'is_optin'  => $optIn,
				'edit_date' => current_time('mysql'),
			],
			['guid' => $guid,]
		);
		if (!$success) {
			Core::showError('DB update failed: ' . Db::getError(), 500);
		}
		wp_die(Strings::wpMessage(__('Your settings were saved', 'demovox'), 'success'));
	}

	public function signShortcode()
	{
		$this->requireHttps();

		if (isset($_REQUEST['sign']) && !empty($_REQUEST['sign'])) {
			return $this->signStep(3);
		} else {
			return $this->signStep(1);
		}
	}

	public function signStep2()
	{
		$this->requireHttps();

		$this->signStep(2);
	}

	public function signStep3()
	{
		$this->requireHttps();

		$this->signStep(3);
	}

	public function getCountries()
	{
		header("Pragma: public");
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24))); // 1 day
		header("Cache-Control: public");
		header("Content-Type: text/json");
		Strings::getCountries('json');
		exit; // exit is required to keep Wordpress from echoing a trailing "0"
		// https://wordpress.stackexchange.com/questions/97502/admin-ajax-is-returning-0
	}

	protected function signStep($nr)
	{
		$pluginDir = Infos::getPluginDir();
		if (Infos::isNoEc6()) {
			ob_start();

			$pdfUrl = Config::getValueByUserlang('signature_sheet');
			include $pluginDir . 'public/partials/fallback.php';

			$output = ob_get_clean();
			return $output;
		}

		require_once $pluginDir . 'public/SignSteps.php';
		$sign = new SignSteps($this->nonceId);

		ob_start();
		switch ($nr) {
			case 1:
			default:
				$sign->step1();
				break;
			case 2:
				$sign->step2();
				break;
			case 3:
				$guid = isset($_REQUEST['sign']) ? sanitize_key($_REQUEST['sign']) : null;
				$sign->step3($guid);
				break;
		}
		$output = ob_get_clean();
		return $output;
	}
}