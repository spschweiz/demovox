<?php

namespace Demovox;

/**
 * General demovox config (opposed to collection specific @SettingsVars)
 */
class SettingsVarsGlobal
{
	static private $fieldsCache = null;
	static private $sectionsCache = null;

	/**
	 * @return array|null
	 */
	public static function getFields()
	{
		if (self::$fieldsCache !== null) {
			return self::$fieldsCache;
		}

		$fields = include('SettingsVarsGlobal/ConfigFields.php');

		self::$fieldsCache = $fields;
		return $fields;
	}

	/**
	 * @return array
	 */
	public static function getSections()
	{
		if (self::$sectionsCache !== null) {
			return self::$sectionsCache;
		}

		$sections = include('SettingsVarsGlobal/ConfigSections.php');

		self::$sectionsCache = $sections;
		return $sections;
	}
}