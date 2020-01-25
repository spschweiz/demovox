<?php

namespace Demovox;

class Infos
{

	public static function getLoad($relative = true)
	{
		if (!function_exists('sys_getloadavg')) {
			return 0;
		}

		$load = sys_getloadavg();
		$loadMinute = $load[0];
		if ($relative) {
			$cores = intval(Config::getValue('cron_cores'));
			$loadMinute = $loadMinute / $cores;
		}

		return $loadMinute * 100;
	}

	public static function getUserLanguage($raw = false)
	{
		$lang = get_user_locale();
		if ($raw) {
			return $lang;
		}
		$lang = strtolower(substr($lang, 0, 2));
		$availableLangs = i18n::getLangsEnabled();
		if (!isset($availableLangs[$lang])) {
			$lang = Config::getValue('default_language');
		}

		return $lang;
	}

	public static function getPluginDir()
	{
		return Core::getPluginDir();
	}

	public static function getUserName()
	{
		return wp_get_current_user()->user_login;
	}

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

	public static function countCores()
	{
		try {
			$coreNums = trim(shell_exec("grep -P '^physical id' /proc/cpuinfo|wc -l"));
			return $coreNums;
		} catch (\Exception $e) {
			return 0;
		}
	}

	public static function isHttps()
	{
		return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off";
	}

	public static function isHighLoad($maxLoad = null)
	{
		if ($maxLoad === null) {
			$maxLoad = Config::getValue('cron_max_load');
		}
		$isHigh = intval($maxLoad) < self::getLoad();

		return $isHigh;
	}

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