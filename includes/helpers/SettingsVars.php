<?php

namespace Demovox;

/**
 * General demovox config (opposed to collection specific @SettingsVarsCollection)
 */
class SettingsVars
{
	static private $fieldsCache = null;
	static private $sectionsCache = null;

	/**
	 * @return array|null
	 */
	public static function getFields(): array
	{
		if (self::$fieldsCache !== null) {
			return self::$fieldsCache;
		}

		$fields = include(Infos::getPluginDir() . 'includes/helpers/SettingsVarsGlobal/ConfigFields.php');

		self::$fieldsCache = $fields;
		return $fields;
	}

	/**
	 * @return array
	 */
	public static function getSections(): array
	{
		if (self::$sectionsCache !== null) {
			return self::$sectionsCache;
		}

		$sections = include(Infos::getPluginDir() . 'includes/helpers/SettingsVarsGlobal/ConfigSections.php');

		self::$sectionsCache = $sections;
		return $sections;
	}
}