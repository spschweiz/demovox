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
 * The public-facing functionality of the plugin: ajax actions and some of the shortcodes
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Demovox
 * @subpackage Demovox/public
 * @author     SP Schweiz
 */
class PublicHandler extends BaseController
{
	/**
	 * Attributes of the called shortcode
	 * @var null|array
	 */
	protected ?array $shortcodeAttributes;

	/**
	 * The [demovox_form] shortcode.
	 *
	 * Accepts a collection id and will display the sign-up form or the signature sheet.
	 *
	 * @param array|string $atts    Shortcode attributes. Default empty.
	 * @param string|null  $content Shortcode content. Default null.
	 * @param string       $tag     Shortcode tag (name). Default empty.
	 * @return string Shortcode output.
	 */
	public function signShortcode($atts = [], string $content = null, string $tag = ''): string
	{
		$this->requireHttps();
		$this->shortcodeAttributes = $this->getShortcodeAttriutes($atts, $tag);
		if (!isset($this->shortcodeAttributes['collection']) || !is_numeric($this->shortcodeAttributes['collection'])) {
			$this->shortcodeAttributes['collection'] = $this->getDefaultCollection();
		}

		$source = isset($_REQUEST['src']) ? sanitize_text_field($_REQUEST['src']) : '';
		if ($source) {
			Core::setSessionVar('source', $source);
		}

		if (isset($_REQUEST['action'])) {
			if ($_REQUEST['action'] == 'demovox_step2') { // ajax failed
				return $this->signStep(2);
			} elseif ($_REQUEST['action'] == 'demovox_step3') {
				return $this->signStep(3);
			}
		}

		if (isset($_REQUEST['sign']) && !empty($_REQUEST['sign'])) {
			return $this->signStep(3);
		} else {
			return $this->signStep(1);
		}
	}

	/**
	 * Public ajax action "demovox_optin"
	 *
	 * @return void
	 */
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
			Core::errorDie('DB update failed: ' . Db::getLastError(), 500);
		}
		wp_die(Strings::wpMessage(__('Your settings were saved', 'demovox'), 'success'));
	}

	/**
	 * Public ajax action "demovox_step2"
	 *
	 * @return void
	 */
	public function signStep2()
	{
		$this->requireHttps();

		$this->signStep(2);
	}

	/**
	 * Public ajax action "demovox_step3"
	 *
	 * @return void
	 */
	public function signStep3()
	{
		$this->requireHttps();

		$this->signStep(3);
	}

	/**
	 * Public ajax action "demovox_countries"
	 *
	 * @return void
	 */
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

	/**
	 * Return content of signing steps
	 * @param int $nr
	 * @return false|string
	 */
	protected function signStep(int $nr)
	{
		$pluginDir = Infos::getPluginDir();

		if ($this->isRequireFallback($nr)) {
			return $this->showFallback($pluginDir);
		}

		require_once $pluginDir . 'public/SignSteps.php';
		$sign = new SignSteps($this->nonceId);

		ob_start();
		switch ($nr) {
			case 1:
			default:
				$collection = $this->shortcodeAttributes['collection'];
				$sign->step1($collection);
				break;
			case 2:
				$collection = $this->getCollectionFromRequest();
				$sign->step2($collection);
				break;
			case 3:
				$guid = sanitize_key($_REQUEST['sign']);
				$sign->step3($guid);
				break;
		}
		$output = ob_get_clean();
		return $output;
	}

	protected function isRequireFallback($nr)
	{
		return $nr === 3 && Infos::isNoEc6();
	}

	/**
	 * Show fallback page for browsers with incompatible JS version
	 * @param $pluginDir
	 * @return false|string
	 */
	protected function showFallback($pluginDir)
	{
		ob_start();

		$pdfUrl = Settings::getValueByUserlang('signature_sheet');
		include $pluginDir . 'public/views/fallback.php';

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @return int
	 */
	protected function getCollectionFromRequest(): int
	{
		return isset($_REQUEST['collection']) && intval($_REQUEST['collection'])
			? intval($_REQUEST['collection'])
			: $this->getDefaultCollection();
	}
}