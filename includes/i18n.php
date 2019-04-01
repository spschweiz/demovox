<?php

namespace Demovox;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/spschweiz/demovox
 * @since      1.0.0
 *
 * @package    Demovox
 * @subpackage Demovox/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Demovox
 * @subpackage Demovox/includes
 * @author     Fabian Horlacher / SP Schweiz <nospam@nospam.com>
 */
class i18n
{
	public static $cantons = [
		''   => '',
		'ag' => 'Aargau',
		'ai' => 'Appenzell Innerrhoden',
		'ar' => 'Appenzell Ausserrhoden',
		'be' => 'Bern',
		'bl' => 'Basel-Landschaft',
		'bs' => 'Basel-Stadt',
		'fr' => 'Fribourg',
		'ge' => 'Genève',
		'gl' => 'Glarus',
		'gr' => 'Graubünden',
		'ju' => 'Jura',
		'lu' => 'Luzern',
		'ne' => 'Neuchâtel',
		'nw' => 'Nidwalden',
		'ow' => 'Obwalden',
		'sg' => 'St. Gallen',
		'sh' => 'Schaffhausen',
		'so' => 'Solothurn',
		'sz' => 'Schwyz',
		'tg' => 'Thurgau',
		'ti' => 'Ticino',
		'ur' => 'Uri',
		'vd' => 'Vaud',
		'vs' => 'Valais',
		'zg' => 'Zug',
		'zh' => 'Zürich',
	];

	public static $languages = [
		'de' => 'German',
		'fr' => 'French',
		'it' => 'Italian',
		'rm' => 'Romansh',
		'en' => 'English',
	];

	public static function getLangsEnabled()
	{
		$languages = [];
		$glueLang = Config::GLUE_LANG;
		foreach (self::$languages as $langId => $lang) {
			if (ConfigVars::getFields('is_language_enabled' . $glueLang . $langId)) {
				$languages[$langId] = $lang;
			}
		}
		return $languages;
	}

	public static function getLangs()
	{
		return self::$languages;
	}

	public static $defaultCountry = 'CH';

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function loadPluginTextdomain()
	{
		load_plugin_textdomain(
			'demovox',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}

}
