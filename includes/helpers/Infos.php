<?php

namespace Demovox;

class Infos
{

	/**
	 * Check if clients are redirected from HTTP to HTTPS
	 * @return array
	 */
	public static function checkHttp2Https()
	{
		$url = 'http://' . $_SERVER['HTTP_HOST'];
		$url_expect = 'https://' . $_SERVER['HTTP_HOST'];
		if (function_exists('curl_version')) {
			$ch = \curl_init($url);
			\curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
			\curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
			\curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			\curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
			\curl_exec($ch);

			if (\curl_errno($ch)) {
				// no listener on port 80
				$success = true;
				$httpStatus = $httpRedirect = null;
			} else {
				$httpStatus = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$httpRedirect = \curl_getinfo($ch, CURLINFO_REDIRECT_URL);
				curl_close($ch);
				$isRedirect = ($httpStatus >= 300 && $httpStatus < 400);
				$success = ($isRedirect && strpos($httpRedirect, $url_expect) !== false);
			}
		} else {
			$headers = get_headers($url);
			if ($headers === false || !isset($headers[0])) {
				// no listener on port 80
				$success = true;
				$httpStatus = $httpRedirect = null;
			} else {
				$httpStatus = $headers[0];
				$isRedirect = preg_match('/^HTTP\/\d+.\d+ 30\d /i', $httpStatus) !== false;
				$success = false;
				$httpRedirect = null;
				foreach ($headers as $key => $val) {
					if (strpos($val, 'Location: ') !== false
						&& strpos($val, $url_expect) !== false) {
						$httpRedirect = $val;
						$success = $isRedirect;
						break;
					}
				}
			}
		}

		return [$success, $httpStatus, $httpRedirect];
	}

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
		$availableLangs = i18n::$languages;
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
}