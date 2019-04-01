<?php

namespace Demovox;

class Config
{
	const PART_ROTATION = 'rot';
	const PART_LAST_LANG = 'lastLang';
	const GLUE_PART = '_';
	const PART_POS_X = 'x';
	const GLUE_LANG = '_';
	const PART_POS_Y = 'y';

	/**
	 * @param $id string
	 * @param $valPart false|string
	 * @param $retString bool Always return $valPart results as string, used for config outputs
	 * @return mixed Value set for the config.
	 */
	public static function getValue($id, $valPart = false, $retString = false)
	{
		$fullId = $id . ($valPart ? self::GLUE_PART . $valPart : '');
		$value = Core::getOption($fullId);
		if ($value !== false) {
			return self::valueFormat($valPart, $value);
		}

		// No value is set yet, get default value
		$fields = ConfigVars::getFields();
		$key = array_search($id, array_column($fields, 'uid'));
		if ($key === false) {
			Core::logMessage('Option field "' . $id . '" does not exist.');
			return false;
		}
		$field = $fields[$key];
		$value = isset($field['default']) ? $field['default'] : ''; // Set to our default
		if ($field['checkbox']) {
			$value = !!$value;
		}

		return self::valueFormat($valPart, $value, $field);
	}

	protected static function valueFormat($valPart, $value, $field = null)
	{
		if ($valPart == self::PART_POS_X) {
			if (isset($field['defaultX'])) {
				$value = intval($field['defaultX']);
			}
			if (!$value) {
				$value = 0;
			}
		} elseif ($valPart == self::PART_POS_Y) {
			if (isset($field['defaultY'])) {
				$value = intval($field['defaultY']);
			}
			$value = intval($value);
			if (!$value) {
				$value = 0;
			}
		} elseif ($valPart == self::PART_ROTATION) {
			if (isset($field['defaultRot'])) {
				$value = intval($field['defaultRot']);
			}
			$value = intval($value);
			if (!$value) {
				$value = 0;
			}
		}
		return $value;
	}

	/**
	 * @param $id string
	 * @param $valPart string|false
	 * @return mixed Value set for the option.
	 */
	public static function getValueByUserlang($id, $valPart = false)
	{
		$lang = Infos::getUserLanguage();
		return self::getValueByLang($id, $lang, $valPart);
	}

	/**
	 * @param $id string
	 * @param $lang string
	 * @param $valPart string|false
	 * @return mixed Value set for the config value.
	 */
	public static function getValueByLang($id, $lang, $valPart = false)
	{
		$value = self::getValue($id . self::GLUE_LANG . $lang, $valPart);
		if ($value === false) {
			$defaultLang = self::getValue('default_language');
			$value = self::getValue($id . self::GLUE_LANG . $defaultLang, $valPart);
		}
		return $value;
	}

	/**
	 * Delete all fields (plugin settings)
	 */
	public static function deleteAll()
	{
		foreach (ConfigVars::$fields as $field) {
			Core::delOption($field);
		}
	}
}