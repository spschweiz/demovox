<?php

namespace Demovox;

/**
 * General demovox config (opposed to collection specific @ConfigVars)
 */
class ConfigVarsGlobal
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

		$fields = include('ConfigVarsGlobal/ConfigFields.php');

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

		$sections = include('ConfigVarsGlobal/ConfigSections.php');

		self::$sectionsCache = $sections;
		return $sections;
	}
}