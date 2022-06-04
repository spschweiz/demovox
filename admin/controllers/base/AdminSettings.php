<?php

namespace Demovox;

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
abstract class AdminSettings extends BaseController
{
	use AdminScriptsTrait;

	protected function getSettingsSections(): array
	{
		return SettingsVarsCollection::getSections();
	}

	protected function getSettingsFields(): array
	{
		return SettingsVarsCollection::getFields();
	}

	protected function languageChangeWarning($uid): void
	{
		$wpid = Core::getWpId($uid);

		$currUserLang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '-';
		$lastUserLang = Core::getOption($uid . Settings::GLUE_PART . Settings::PART_PREVIOUS_LANG);
		if ($currUserLang != $lastUserLang && $lastUserLang !== false) {
			echo Strings::wpMessage(
				'<b>Error: Previously selected page can not be loaded.</b> Don\'t click on "Save" or you might lose this setting.<br/>'
				. 'Client Wordpress language has changed since this config was last set. (probably with a 3rd party plugin like WPML)<br/>'
				. 'Please change the language back to "' . $lastUserLang
				. '" and reload this page or select the page again. '
				. '(Current language: "' . $currUserLang . '")',
				'error'
			);
		}
		printf(
			'<input name="%1$s" id="%1$s" type="hidden" value="%2$s" />',
			$wpid . Settings::GLUE_PART . Settings::PART_PREVIOUS_LANG,
			$currUserLang
		);
	}

	/**
	 * Replacement for do_settings_sections()
	 * Supports to add HTML in 'addPre', 'addPost', and 'sub'
	 *
	 * @param string $page
	 */
	protected function doSettingsSections(string $page): void
	{
		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_sections[$page])) {
			return;
		}

		$sections = $this->getSettingsSections();

		foreach ((array)$wp_settings_sections[$page] as $wpSection) {
			$sectionId      = $wpSection['id'];
			$sectionDetails = $sections[$wpSection['id']];

			if (isset($sectionDetails['addPre'])) {
				echo Strings::__a($sectionDetails['addPre']);
			}

			if ($wpSection['title']) {
				$title = Strings::__a($wpSection['title']);
				echo "<h2>{$title}</h2>\n";
			}

			if (isset($sectionDetails['sub'])) {
				echo Strings::__a($sectionDetails['sub']);
			}
			if ($wpSection['callback']) {
				call_user_func($wpSection['callback'], $wpSection);
			}

			if (!isset($wp_settings_fields[$page][$sectionId])) {
				Core::logMessage('No fields registered for section: ' . $sectionId);
				continue;
			}

			echo '<table class="form-table">';
			do_settings_fields($page, $sectionId);
			echo '</table>';

			if (isset($sectionDetails['addPost'])) {
				echo Strings::__a($sectionDetails['addPost']);
			}
		}
	}

	public function registerSettings(): void
	{
		require_once Core::getPluginDir() . 'admin/helpers/RegisterSettings.php';

		$settings = new RegisterSettings($this);
		$settings->register();
	}

	public function fieldCallback($arguments): void
	{
		$uid  = $arguments['uid'];
		$wpid = Core::getWpId($uid);
		$type = $arguments['type'];

		$placeholder = (isset($arguments['placeholder']) && $arguments['placeholder'] !== false
						&& $arguments['placeholder'] !== 0)
			? $arguments['placeholder'] : '';

		// Check which type of field we want
		switch ($type) {
			case 'text': // If it is a text field
			case 'input':
			case 'file':
			case 'number':
			default:
				$value = str_replace('"', '&quot;', Settings::getValue($uid));
				printf(
					'<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" size="40" />',
					$wpid,
					$type,
					$placeholder,
					$value
				);
				break;
			case 'checkbox':
				$value = Settings::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="%2$s" value="1" %3$s/>',
					$wpid,
					$type,
					$value ? 'checked="checked"' : ''
				);
				break;
			case 'pos':
				$valuePosX = Settings::getValue($uid, Settings::PART_POS_X);
				$valuePosY = Settings::getValue($uid, Settings::PART_POS_Y);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Settings::GLUE_PART . Settings::PART_POS_X,
					'x',
					$valuePosX
				);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Settings::GLUE_PART . Settings::PART_POS_Y,
					'y',
					$valuePosY
				);
				break;
			case 'rotate':
				$value = Settings::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" min="0" max="359" />',
					$wpid,
					$placeholder,
					$value
				);
				break;
			case 'pos_rot':
				$valuePosX = Settings::getValue($uid, Settings::PART_POS_X);
				$valuePosY = Settings::getValue($uid, Settings::PART_POS_Y);
				$valueRot  = Settings::getValue($uid, Settings::PART_ROTATION);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Settings::GLUE_PART . Settings::PART_POS_X,
					'x',
					$valuePosX
				);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Settings::GLUE_PART . Settings::PART_POS_Y,
					'y',
					$valuePosY
				);
				$options = [
					0 => 'Normal orientation',
					90 => 'Rotate 90° clockwise',
					180 => 'Rotate 180°',
					270 => 'Rotate 90° counter-clockwise',
				];
				Strings::createSelect($options, $valueRot, $wpid . Settings::GLUE_PART . Settings::PART_ROTATION);
				break;
			case 'textarea': // If it is a textarea
				$value = str_replace('"', '&quot;', Settings::getValue($uid));
				printf(
					'<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="20" cols="180">%3$s</textarea>',
					$wpid,
					$placeholder,
					$value
				);
				break;
			case 'wysiwyg': // If it is a wysiwyg editor
				// https://developer.wordpress.org/reference/functions/wp_editor/
				$value = Settings::getValue($uid);
				wp_editor($value, $wpid);
				break;
			case 'select': // If it is a select dropdown
				if (!empty ($arguments['options']) && is_array($arguments['options'])) {
					Strings::createSelect($arguments['options'], Settings::getValue($uid), $wpid);
				}
				break;
			case 'wpMedia':
				$value = Settings::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="text" placeholder="%2$s" value="%3$s" size="72" />',
					$wpid,
					$placeholder,
					$value
				);
				$select = Strings::__a('Select');
				echo '<button class="uploadButton" data-input-id="' . $wpid . '">' . $select . '</button>';
				break;
			case 'wpPage': // If it is a select dropdown
				$value = Settings::getValue($uid);
				$args  = [
					'name' => $wpid,
					'selected' => $value,
					'suppress_filters' => true, // disable WPML language filtering
				];
				if (isset($arguments['optionNone']) && $arguments['optionNone']) {
					$args['show_option_none'] = $arguments['optionNone'];
				}
				$this->languageChangeWarning($uid);
				wp_dropdown_pages($args);
				break;
		}

		// If there is help text
		if (isset($arguments['helper']) && $helper = $arguments['helper']) {
			printf('<span class="helper"> %s</span>', $helper); // Show it
		}

		// If there is supplemental text
		if (isset($arguments['supplemental']) && $supplemental = $arguments['supplemental']) {
			printf('<p class="description">%s</p>', $supplemental); // Show it
		}
	}
}