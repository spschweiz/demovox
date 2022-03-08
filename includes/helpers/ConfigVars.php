<?php

namespace Demovox;

/**
 * Collection specific config
 */
class ConfigVars
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

		$fields = include('ConfigVars/ConfigFields.php');

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

		$sections = include('ConfigVars/ConfigSections.php');

		self::$sectionsCache = $sections;
		return $sections;
	}
}