<?php

namespace Demovox;

/**
 * Collection specific config
 */
class SettingsVarsCollection
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

		$fieldsDef = include(Infos::getPluginDir() . 'includes/helpers/SettingsVarsCollection/ConfigFields.php');
		$fields = [];
		$collections = new DbCollections;
		foreach ($collections->getResults(['ID']) as $collection) {
			foreach ($fieldsDef as $field) {
				$field['uid'] = $collection->ID . Settings::GLUE_PART . $field['uid'];
				$field['section'] = $collection->ID . Settings::GLUE_PART . $field['section'];
				$fields[] = $field;
			}
		}
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

		$sectionsDef = include('SettingsVarsCollection/ConfigSections.php');
		$sections = [];
		$collections = new DbCollections;
		foreach ($collections->getResults(['ID']) as $collection) {
			foreach ($sectionsDef as $id => $section) {
				$section['page'] = $collection->ID . Settings::GLUE_PART . $section['page'];
				$id = $collection->ID . Settings::GLUE_PART . $id;
				$sections[$id] = $section;
			}
		}
		self::$sectionsCache = $sections;
		return $sections;
	}
}