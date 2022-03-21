<?php

namespace Demovox;

class Settings
{
	const GLUE_LANG = '_';
	const GLUE_PART = '_';
	const PART_ROTATION = 'rot';
	const PART_POS_X = 'x';
	const PART_POS_Y = 'y';
	const PART_PREVIOUS_LANG = 'prevLang';

	/**
	 * @param $id           string
	 * @param $valPart      string|null
	 * @return mixed Value set for the config.
	 */
	public static function getValue(string $id, ?string $valPart = null)
	{
		$fullId = self::getFullId($id, null, $valPart);
		return Core::getOption($fullId);
	}

	/**
	 * Get collection specific value
	 * @param $id           string
	 * @param $collectionId int|null
	 * @param $valPart      string|null
	 * @return mixed Value set for the config.
	 */
	public static function getCValue(string $id, ?int $collectionId = null, ?string $valPart = null)
	{
		$collectionId = self::getCollectionId($collectionId);
		$fullId = self::getFullId($id, $collectionId, $valPart);
		return Core::getOption($fullId);
	}

	/**
	 * @param $id           string
	 * @param $value        mixed Option value to set
	 * @param $valPart      string|null
	 * @return bool Value set for the config.
	 */
	public static function setValue(string $id, $value, ?string $valPart = null) : bool
	{
		$fullId = self::getFullId($id, null, $valPart);
		return Core::setOption($fullId, $value);
	}

	/**
	 * Set collection specific value
	 * @param $id           string
	 * @param $value        mixed Option value to set
	 * @param $collectionId int|null
	 * @param $valPart      string|null
	 * @return bool Value set for the config.
	 */
	public static function setCValue(string $id, $value, ?int $collectionId = null, ?string $valPart = null) : bool
	{
		$collectionId = self::getCollectionId($collectionId);
		$fullId = self::getFullId($id, $collectionId, $valPart);
		return Core::setOption($fullId, $value);
	}

	/**
	 * Delete setting
	 * @param $id           string
	 * @param $valPart      string|null
	 * @return bool Value set for the config.
	 */
	public static function delValue(string $id, ?string $valPart = null) : bool
	{
		$fullId = self::getFullId($id, null, $valPart);
		return Core::delOption($fullId);
	}

	/**
	 * Delete  collection specific setting
	 * @param $id           string
	 * @param $collectionId int|null
	 * @param $valPart      string|null
	 * @return bool Value set for the config.
	 */
	public static function delCValue(string $id, ?int $collectionId = null, ?string $valPart = null) : bool
	{
		$collectionId = self::getCollectionId($collectionId);
		$fullId = self::getFullId($id, $collectionId, $valPart);
		return Core::delOption($fullId);
	}

	/**
	 * @param $id           string
	 * @param $collectionId int|null
	 * @param $valPart      string|false
	 * @return mixed Value set for the option.
	 */
	public static function getCValueByUserlang(string $id, ?int $collectionId = null, $valPart = null)
	{
		$lang = Infos::getUserLanguage();
		return self::getCValueByLang($id, $lang, $collectionId, $valPart);
	}

	/**
	 * @param $id           string
	 * @param $lang         string
	 * @param $collectionId int|null
	 * @param $valPart      string|null
	 * @return mixed Value set for the config value.
	 */
	public static function getCValueByLang(string $id, string $lang, ?int $collectionId = null, ?string $valPart = null)
	{
		$value = self::getCValue($id . self::GLUE_LANG . $lang, $collectionId, $valPart);
		if ($value === false) {
			$defaultLang = self::getValue('default_language');
			$value = self::getCValue($id . self::GLUE_LANG . $defaultLang, $collectionId, $valPart);
		}
		return $value;
	}

	/**
	 * Delete all fields (plugin settings)
	 */
	public static function deleteAll() : void
	{
		require_once Infos::getPluginDir() . 'includes/helpers/SettingsVars.php';
		require_once Infos::getPluginDir() . 'includes/helpers/SettingsVarsCollection.php';

		$fieldsGlobal = SettingsVars::getFields();
		foreach ($fieldsGlobal as $field) {
			$id        = $field['uid'];
			self::delValue($id);
		}

		$collections = new DbCollections();
		$allCollections = $collections->getResults(['ID']);
		$fieldsCollection = SettingsVarsCollection::getFields();
		foreach ($fieldsCollection as $field) {
			$id        = $field['uid'];
			$fieldType = $field['type'] ?? null;
			foreach($allCollections as $collection) {
				$collectionId = $collection->ID;
				switch ($fieldType) {
					default:
						self::delCValue($id, $collectionId);
						break;
					case 'pos':
						self::delCValue($id, $collectionId, Settings::PART_POS_X);
						self::delCValue($id, $collectionId, Settings::PART_POS_Y);
						break;
					case 'pos_rot':
						self::delCValue($id, $collectionId, Settings::PART_POS_X);
						self::delCValue($id, $collectionId, Settings::PART_POS_Y);
						self::delCValue($id, $collectionId, Settings::PART_ROTATION);
						break;
				}
			}
		}
	}

	/**
	 * Set default values to undefined field settings for a specific or the default	 collection
	 *
	 * @param int|null $collectionId
	 * @return void
	 */
	public static function initDefaults(int $collectionId = null) : void
	{
		require_once Infos::getPluginDir() . 'includes/helpers/SettingsVars.php';
		require_once Infos::getPluginDir() . 'includes/helpers/SettingsVarsCollection.php';
		$fieldsGlobal = SettingsVars::getFields();

		foreach ($fieldsGlobal as $field) {
			$id        = $field['uid'];
			if (isset($field['default'])) {
				if (self::getValue($id) === false) {
					self::setValue($id, $field['default']);
				}
			}
		}

		$collectionId = $collectionId === null ? Infos::getDefaultCollectionId() : $collectionId;
		$fieldsCollection = SettingsVarsCollection::getFields();

		foreach ($fieldsCollection as $field) {
			$id        = $field['uid'];
			$fieldType = $field['type'] ?? null;
			switch ($fieldType) {
				default:
					if (isset($field['default'])) {
						self::setCDefaultIfUnset($id, $field['default'], $collectionId);
					}
					break;
				case 'pos':
					if (isset($field['defaultX'])) {
						self::setCDefaultIfUnset($id, $field['defaultX'], $collectionId, Settings::PART_POS_X);
					}
					if (isset($field['defaultY'])) {
						self::setCDefaultIfUnset($id, $field['defaultY'], $collectionId, Settings::PART_POS_Y);
					}
					break;
				case 'pos_rot':
					if (isset($field['defaultX'])) {
						self::setCDefaultIfUnset($id, $field['defaultX'], $collectionId, Settings::PART_POS_X);
					}
					if (isset($field['defaultY'])) {
						self::setCDefaultIfUnset($id, $field['defaultY'], $collectionId, Settings::PART_POS_Y);
					}
					if (isset($field['defaultRot'])) {
						self::setCDefaultIfUnset($id, $field['defaultRot'], $collectionId, Settings::PART_ROTATION);
					}
					break;
			}
		}
	}

	/**
	 * @param $id           string
	 * @param $default      string
	 * @param $collectionId null|int
	 * @param $valPart      null|string
	 * @return void
	 */
	protected static function setCDefaultIfUnset(string $id, string $default, ?int $collectionId = null, string $valPart = null) : void
	{
		if (self::getCValue($id, $collectionId, $valPart) === false) {
			self::setCValue($id, $default, $collectionId, $valPart);
		}
	}

	/**
	 * @param string      $id
	 * @param int|null    $collectionId
	 * @param string|null $valPart
	 * @return string
	 */
	protected static function getFullId(string $id, ?int $collectionId, ?string $valPart): string
	{
		return ($collectionId ? $collectionId . self::GLUE_PART : '')
			. $id . ($valPart ? self::GLUE_PART . $valPart : '');
	}

	/**
	 * @param int|null $collectionId
	 * @return int|null
	 */
	protected static function getCollectionId(?int $collectionId): ?int
	{
		if ($collectionId === null) {
			$collectionId = Infos::getCollectionId();
		}
		if ($collectionId === null) {
			Core::errorDie('CollectionId undefined');
		}
		return $collectionId;
	}
}