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
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class AdminSettings
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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $pluginName The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct($pluginName, $version)
	{
		$this->pluginName = $pluginName;
		$this->version    = $version;

		// Hook into the admin menu
		add_action('admin_init', [$this, 'setupFields']);
		add_action('admin_init', [$this, 'setupSections']);
	}

	public function pageSettings1()
	{
		$page      = 'demovoxFields1';
		$languages = i18n::$languages;
		include Infos::getPluginDir() . 'admin/partials/settings-1.php';
	}

	public function pageSettings2()
	{
		$page = 'demovoxFields2';
		include Infos::getPluginDir() . 'admin/partials/settings-2.php';
	}

	public function pageSettings3()
	{
		$page = 'demovoxFields3';
		include Infos::getPluginDir() . 'admin/partials/settings-3.php';
	}

	public function pageSettings4()
	{
		$page = 'demovoxFields4';
		include Infos::getPluginDir() . 'admin/partials/settings-4.php';
	}

	public function setupSections()
	{
		$areas = Config::getSections();
		foreach ($areas as $name => $section) {
			add_settings_section($name, $section['title'], null, $section['page']);
		}
	}

	protected function languageChangeWarning($uid)
	{
		$wpid         = Core::getWpId($uid);
		$currUserLang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '-';
		$lastUserLang = Core::getOption($uid . Config::GLUE_PART . Config::PART_LAST_LANG);
		if ($currUserLang != $lastUserLang && $lastUserLang !== false) {
			echo Strings::wpMessage(
				'<b>Error: Previously selected page can not be loaded.</b> Don\'t click on "Save" or you might lose this setting.<br/>'
				. 'Client Wordpress language has changed since this config was last set. (probably with a 3rd party plugin like WPML)<br/>'
				. 'Please change the language back to "' . $lastUserLang . '" and reload this page or select the page again. '
				. '(Current language: "' . $currUserLang . '")',
				'error'
			);
		}
		printf(
			'<input name="%1$s" id="%1$s" type="hidden" value="%2$s" />',
			$wpid . Config::GLUE_PART . Config::PART_LAST_LANG,
			$currUserLang
		);
	}

	/**
	 * Replacement for do_settings_sections()
	 * Supports to add HTML in 'addPre', 'addPost', and 'sub'
	 *
	 * @param $page
	 */
	protected function doSettingsSections($page)
	{
		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_sections[$page])) {
			return;
		}

		$sections = Config::getSections();

		foreach ((array)$wp_settings_sections[$page] as $section) {
			if (isset($sections[$section['id']]['addPre'])) {
				echo $sections[$section['id']]['addPre'];
			}

			if ($section['title']) {
				echo "<h2>{$section['title']}</h2>\n";
			}

			if (isset($sections[$section['id']]['sub'])) {
				echo $sections[$section['id']]['sub'];
			}
			if ($section['callback']) {
				call_user_func($section['callback'], $section);
			}

			if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
				continue;
			}
			echo '<table class="form-table">';
			do_settings_fields($page, $section['id']);
			echo '</table>';

			if (isset($sections[$section['id']]['addPost'])) {
				echo $sections[$section['id']]['addPost'];
			}
		}
	}

	public function setupFields()
	{
		$sections = Config::getSections();
		$fields   = ConfigVars::getFields();
		$callback = [$this, 'fieldCallback',];
		foreach ($fields as $field) {
			$page      = $sections[$field['section']]['page'];
			$id        = Core::getWpId($field['uid']);
			$fieldType = isset($field['type']) ? $field['type'] : null;
			switch ($fieldType) {
				default:
					add_settings_field($id, $field['label'], $callback, $page, $field['section'], $field);
					register_setting($page, $id);
					break;
				case'pos_rot':
					add_settings_field(
						$id . Config::GLUE_PART . Config::PART_POS_X,
						$field['label'],
						$callback,
						$page,
						$field['section'],
						$field
					);
					register_setting($page, $id . Config::GLUE_PART . Config::PART_POS_X);
					register_setting($page, $id . Config::GLUE_PART . Config::PART_POS_Y);
					register_setting($page, $id . Config::GLUE_PART . Config::PART_ROTATION);
					break;
				case'wpPage':
					add_settings_field($id, $field['label'], $callback, $page, $field['section'], $field);
					register_setting($page, $id);
					register_setting($page, $id . Config::GLUE_PART . Config::PART_LAST_LANG);
					break;
			}
		}
	}

	public function fieldCallback($arguments)
	{
		$uid         = $arguments['uid'];
		$wpid        = Core::getWpId($uid);
		$type        = $arguments['type'];
		$placeholder = (isset($arguments['placeholder']) && $arguments['placeholder'] !== false && $arguments['placeholder'] !== 0)
			? $arguments['placeholder'] : '';

		// Check which type of field we want
		switch ($type) {
			case 'text': // If it is a text field
			case 'input':
			case 'file':
			case 'number':
				$value = str_replace('"', '&quot;', Config::getValue($uid));
				printf(
					'<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" size="40" />',
					$wpid,
					$type,
					$placeholder,
					$value
				);
				break;
			case 'checkbox':
				$value = Config::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="%2$s" value="true" %3$s/>',
					$wpid,
					$type,
					checked($value, 'true', false)
				);
				break;
			case 'pos':
				$valuePosX = Config::getValue($uid, Config::PART_POS_X, true);
				$valuePosY = Config::getValue($uid, Config::PART_POS_Y, true);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Config::GLUE_PART . Config::PART_POS_X,
					'x',
					$valuePosX
				);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Config::GLUE_PART . Config::PART_POS_Y,
					'y',
					$valuePosY
				);
				break;
			case 'rotate':
				$value = Config::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" min="0" max="359" />',
					$wpid,
					$placeholder,
					$value
				);
				break;
			case 'pos_rot':
				$valuePosX = Config::getValue($uid, Config::PART_POS_X, true);
				$valuePosY = Config::getValue($uid, Config::PART_POS_Y, true);
				$valueRot  = Config::getValue($uid, Config::PART_ROTATION, true);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Config::GLUE_PART . Config::PART_POS_X,
					'x',
					$valuePosX
				);
				printf(
					'<input name="%1$s" id="%1$s" type="number" placeholder="%2$s" value="%3$s" size="5" />',
					$wpid . Config::GLUE_PART . Config::PART_POS_Y,
					'y',
					$valuePosY
				);
				$options = [
					0   => 'Normal orientation',
					90  => 'Rotate 90° clockwise',
					180 => 'Rotate 180°',
					270 => 'Rotate 90° counter-clockwise',
				];
				Strings::createSelect($options, $valueRot, $wpid . Config::GLUE_PART . Config::PART_ROTATION);
				break;
			case 'textarea': // If it is a textarea
				$value = str_replace('"', '&quot;', Config::getValue($uid));
				printf(
					'<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="20" cols="180">%3$s</textarea>',
					$wpid,
					$placeholder,
					$value
				);
				break;
			case 'wysiwyg': // If it is a wysiwyg editor
				// https://developer.wordpress.org/reference/functions/wp_editor/
				$value = Config::getValue($uid);
				wp_editor($value, $wpid);
				break;
			case 'select': // If it is a select dropdown
				if (!empty ($arguments['options']) && is_array($arguments['options'])) {
					Strings::createSelect($arguments['options'], Config::getValue($uid), $wpid);
				}
				break;
			case 'wpMedia':
				$value = Config::getValue($uid);
				printf(
					'<input name="%1$s" id="%1$s" type="text" placeholder="%2$s" value="%3$s" size="35" />',
					$wpid,
					$placeholder,
					$value
				);
				echo '<button class="uploadButton" data-input-id="' . $wpid . '">Select</button>';
				break;
			case 'wpPage': // If it is a select dropdown
				$value = Config::getValue($uid);
				$args  = [
					'name'             => $wpid,
					'selected'         => $value,
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