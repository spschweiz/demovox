<?php

namespace Demovox;

class Config
{
	const GLUE_LANG = '_';
	const GLUE_PART = '_';
	const PART_ROTATION = 'rot';
	const PART_POS_X = 'x';
	const PART_POS_Y = 'y';
	const PART_PREVIOUS_LANG = 'prevLang';

	/**
	 * @param $id string
	 * @param $valPart null|string
	 * @return mixed Value set for the config.
	 */
	public static function getValue($id, $valPart = null)
	{
		$fullId = $id . ($valPart ? self::GLUE_PART . $valPart : '');
		$field = ConfigVars::getField($id);
		if ($field === null) {
			return null;
		}
		$value = Core::getOption($fullId);

		return self::valueFormat($valPart, $value, $field);
	}

	/**
	 * @param $id string
	 * @param $value mixed Option value to set
	 * @param $valPart null|string
	 * @return mixed Value set for the config.
	 */
	public static function setValue($id, $value, $valPart = null)
	{
		$fullId = $id . ($valPart ? self::GLUE_PART . $valPart : '');
		return Core::setOption($fullId, $value);
	}

	protected static function valueFormat($valPart, $value, $field = null)
	{
		if (isset($field['type']) && $field['type'] === 'checkbox') {
			if ($value === false) {
				$value = isset($field['default']) ? $field['default'] : false; // Set to our default
			} else {
				$value = !!$value;
			}
		} elseif ($value === false && $valPart == self::PART_POS_X) {
			if (isset($field['defaultX'])) {
				$value = intval($field['defaultX']);
			}
		} elseif ($valPart == self::PART_POS_Y) {
			if ($value === false && isset($field['defaultY'])) {
				$value = intval($field['defaultY']);
			}
			$value = intval($value);
		} elseif ($valPart == self::PART_ROTATION) {
			if ($value === false && isset($field['defaultRot'])) {
				$value = intval($field['defaultRot']);
			}
			$value = intval($value);
		} else {
			if ($value === false) {
				$value = isset($field['default']) ? $field['default'] : false; // Set to our default
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