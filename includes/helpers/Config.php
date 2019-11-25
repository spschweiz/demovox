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
		return Core::getOption($fullId);
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