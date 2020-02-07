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
		$fields = ConfigVars::getFields();
		foreach ($fields as $field) {
			$fieldType = isset($field['type']) ? $field['type'] : null;
			switch ($fieldType) {
				default:
					Core::delOption($field);
					break;
				case 'pos':
					Core::delOption($field, Config::PART_POS_X);
					Core::delOption($field, Config::PART_POS_Y);
					break;
				case 'pos_rot':
					Core::delOption($field, Config::PART_POS_X);
					Core::delOption($field, Config::PART_POS_Y);
					Core::delOption($field, Config::PART_ROTATION);
					break;
			}
		}
	}

	/**
	 * Set default values to unset fields
	 */
	public static function initDefaults()
	{
		$fields = ConfigVars::getFields();
		foreach ($fields as $field) {
			$id        = $field['uid'];
			$fieldType = isset($field['type']) ? $field['type'] : null;
			switch ($fieldType) {
				default:
					if (isset($field['default'])) {
						self::setDefaultIfUnset($id, $field['default']);
					}
					break;
				case 'pos':
					if (isset($field['defaultX'])) {
						self::setDefaultIfUnset($id, $field['defaultX'], Config::PART_POS_X);
					}
					if (isset($field['defaultY'])) {
						self::setDefaultIfUnset($id, $field['defaultY'], Config::PART_POS_Y);
					}
					break;
				case 'pos_rot':
					if (isset($field['defaultX'])) {
						self::setDefaultIfUnset($id, $field['defaultX'], Config::PART_POS_X);
					}
					if (isset($field['defaultY'])) {
						self::setDefaultIfUnset($id, $field['defaultY'], Config::PART_POS_Y);
					}
					if (isset($field['defaultRot'])) {
						self::setDefaultIfUnset($id, $field['defaultRot'], Config::PART_ROTATION);
					}
					break;
			}
		}
	}

	/**
	 * @param $id
	 * @param $default string
	 * @param $valPart null|string
	 *
	 * @return mixed
	 */
	protected static function setDefaultIfUnset($id, $default, $valPart = null)
	{
		if (self::getValue($id, $valPart) === false) {
			self::setValue($id, $default, $valPart);
		}
	}
}