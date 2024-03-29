<?php

namespace Demovox;

class Infos
{
	protected static int $collectionId;

	public static function getCollectionId()
	{
		return self::$collectionId;
	}

	public static function setCollectionId(int $collectionId)
	{
		self::$collectionId = $collectionId;
	}

	/**
	 * Get server load (only works on Linux servers)
	 *
	 * @param $relative
	 * @return float|int
	 */
	public static function getLoad($relative = true)
	{
		if (!function_exists('sys_getloadavg')) {
			return 0;
		}

		$load = sys_getloadavg();
		$loadMinute = $load[0];
		if ($relative) {
			$cores = intval(Settings::getValue('cron_cores'));
			$loadMinute = $loadMinute / $cores;
		}

		return $loadMinute * 100;
	}

	public static function getUserLanguage($raw = false): string
	{
		$lang = get_user_locale();
		if ($raw) {
			return $lang;
		}
		$lang = strtolower(substr($lang, 0, 2));
		$availableLangs = i18n::getLangsEnabled();
		if (!isset($availableLangs[$lang])) {
			$lang = Settings::getCValue('default_language');
		}

		return $lang;
	}

	public static function getAdminLanguage($raw = false): string
	{
		$lang = get_user_locale();
		if ($raw) {
			return $lang;
		}
		$lang = strtolower(substr($lang, 0, 2));
		if (!isset(i18n::$languages[$lang])) {
			$lang = i18n::$languageDefault;
		}

		return $lang;
	}

	/**
	 * Filesystem directory path to plugin
	 * @return string
	 */
	public static function getPluginDir()
	{
		return Core::getPluginDir();
	}

	public static function getDefaultCollectionId(){
		return 1;
	}

	/**
	 * Wordpress username
	 * @return string
	 */
	public static function getUserName()
	{
		return wp_get_current_user()->user_login;
	}

	/**
	 * Check if client has no ECMAScript 6 compatibility by user agent (imprecise)
	 * @return bool
	 */
	public static function isNoEc6()
	{
		if (isset($_SERVER['HTTP_USER_AGENT'])
			&& (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false
				|| strpos($_SERVER['HTTP_USER_AGENT'], 'Konqueror') !== false)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Count server cores (only works on Linux servers)
	 * @return int|string
	 */
	public static function countCores()
	{
		try {
			$coreNums = trim(shell_exec("grep -P '^physical id' /proc/cpuinfo|wc -l"));
			return $coreNums;
		} catch (\Exception $e) {
			return 0;
		}
	}

	/**
	 * SSL protocol enabled?
	 * @return bool
	 */
	public static function isHttps()
	{
		return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off";
	}

	/**
	 * Check if server load is high (only works on Linux servers)
	 * @param $maxLoad
	 * @return bool
	 */
	public static function isHighLoad($maxLoad = null)
	{
		if ($maxLoad === null) {
			$maxLoad = Settings::getValue('cron_max_load');
		}
		$isHigh = intval($maxLoad) < self::getLoad();

		return $isHigh;
	}

	/**
	 * Get client IP (imprecise)
	 * @return string
	 */
	public static function getClientIp()
	{
		$address = new \DemovoxLibs\RemoteAddress();
		return $address->getIpAddress();
	}

	/**
	 * Use this method for forms
	 * @return string
	 */
	public static function getRequestUri()
	{
		return htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'ISO-8859-1');
	}
}