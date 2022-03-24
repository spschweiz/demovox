<?php

namespace Demovox;

class Strings
{

	/**
	 * Get permalink to page where to find a visitor specific demovox page
	 *
	 * @param string $signGuid
	 * @param null|int $pageId By default: success page (probably with pdf link)
	 * @param $baseUrl null|string
	 * @return string
	 */
	public static function getPageUrl($signGuid, $pageId = null, $baseUrl = null)
	{
		$pageId = $pageId ?: Settings::getCValue('use_page_as_success');
		$url = get_permalink($pageId);
		if ($baseUrl) {
			$lengthCut = strlen(home_url());
			$url = $baseUrl . substr($url, $lengthCut);
		}
		if($signGuid) {
			if (strpos($url, '?') === false) {
				$url .= '?';
			} else {
				$url .= '&';
			}
		}
		$url .= 'sign=' . $signGuid;
		return $url;
	}

	/**
	 * Generate hashed value from $signId
	 * https://stackoverflow.com/questions/959957/php-short-hash-like-url-shortening-websites
	 *
	 * @param $qrMode
	 * @param $signId
	 * @return string
	 */
	public static function getSerial($signId, $qrMode = null)
	{
		if ($qrMode === null) {
			$qrMode = Settings::getCValue('field_qr_mode');
		}
		switch ($qrMode) {
			case 'id':
			default:
				$serial = $signId;
				break;
			case 'hashids':
				// Hashids requires either the PHP extension GMP or BC Math in order to work. (GMP should be faster)
				$hashids = new \Hashids\Hashids('salt', 5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
				$serial = $hashids->encode($signId);
				break;
			case 'PseudoCrypt':
				require __DIR__ . '/../libs/php/PseudoCrypt.php';
				$serial = \DemovoxLibs\PseudoCrypt::hash($signId);
				break;
			case 'BaseIntEncoder':
				require __DIR__ . '/../libs/php/BaseIntEncoder.php';
				$serial = \DemovoxLibs\BaseIntEncoder::encode($signId);
				break;
		}
		return $serial;
	}

	public static function getAdminUrl(string $url, ?string $action = null)
	{
		$url = str_replace('&amp;', '&', $url);
		if ($action !== null) {
			$url = add_query_arg('action', $action, $url);
			$url = wp_nonce_url($url, $action);
		}
		return admin_url($url);
	}

	public static function getAdminLink(string $url, string $label)
	{
		$url = self::getAdminUrl($url);
		$label = Strings::__($label);
		return '<a href="' . $url . '">' . $label . '</a>';
	}

	public static function getCountries($format = 'php', $locale = null, $echo = null)
	{
		$locale = $locale ?: Infos::getUserLanguage(true);
		$availableFormats = [
			'csv',
			'html',
			'json',
			'mysql.sql',
			'php',
			'postgresql.sql',
			'sqlite.sql',
			'txt',
			'xliff',
			'xml',
			'yaml',
		];
		if (!in_array($format, $availableFormats)) {
			Core::errorDie('getCountries: invalid format ' . $format . ' was requested', 500);
		}
		if ($echo === null) {
			$echo = ($format === 'json');
		}
		$ds = DIRECTORY_SEPARATOR;
		$dirBase = Infos::getPluginDir() . 'libs' . $ds . 'composer' . $ds . 'umpirsky' . $ds . 'country-list' . $ds . 'data' . $ds;
		$dir = $dirBase . $locale;
		if (!is_dir($dir)) {
			$dir = $dirBase . Settings::getCValue('default_language');
		}
		$path = $dir . $ds . 'country.' . $format;
		if ($echo) {
			readfile($path);
		} else {
			return include $path;
		}
	}

	public static function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZäöüÜÄÖç&/ÉÀÈéàèê';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public static function parseCsv($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
	{
		$enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
		$enc = preg_replace_callback(
			'/\\\"(.*?)\\\"/s',
			function ($field) {
				return urlencode(utf8_encode($field[1]));
			},
			$enc
		);
		$lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *(\r\n|\r|\n))+/s' : '/(\r\n|\r|\n)+/s') : '/(\r\n|\r|\n)/s', $enc);
		return array_map(
			function ($line) use ($delimiter, $trim_fields) {
				$fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
				return array_map(
					function ($field) {
						return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
					},
					$fields
				);
			},
			$lines
		);
	}

	/**
	 * @param $message
	 * @param null|string $status success|warning|error|info
	 * @return string
	 */
	public static function wpMessage($message, $status = null)
	{
		$status = $status ?: 'info';
		$string = '<div class="notice ' . $status . ' inline notice-' . $status . ' notice-alt"><p>'
			. $message . '</p></div>';

		return $string;
	}

	/**
	 *
	 * @param array $options
	 * @param string $value
	 * @param string $name
	 * @param string $id
	 * @return string select id
	 */
	public static function createSelect($options, $value, $name, $id = null, $attributes = [])
	{
		$id = $id === null ? $name : $id;
		$optionsMarkup = '’';
		foreach ($options as $key => $label) {
			$optionsMarkup .= sprintf(
				'<option value="%s" %s>%s</option>',
				$key,
				selected($value, $key, false),
				$label
			);
		}
		$addAttribs = '';
		foreach ($attributes as $name => $value) {
			$addAttribs .= ' ' . $name . '="' . $value . '"';
		}
		printf('<select name="%1$s" id="%2$s"%4$s>%3$s</select>', $name, $id, $optionsMarkup, $addAttribs);

		return $id;
	}

	/**
	 * Convert newline to html <br/>
	 * @param string $text
	 * @return string
	 */
	public static function nl2br($text)
	{
		return str_replace(["\r\n", "\r", "\n"], "<br/>", $text);
	}

	/**
	 * @param string $email
	 * @return false|string
	 */
	public static function hashMail($email)
	{
		if (!defined('DEMOVOX_HASH_KEY')) {
			Core::errorDie('Mail hashing failed: Constant DEMOVOX_HASH_KEY is not defined in wp-config.php', 500);
		}
		return hash_hmac('md5', $email, DEMOVOX_HASH_KEY);
	}

	/**
	 * Create a GUID
	 * @return string
	 */
	public static function createGuid()
	{
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		$data    = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	 * @param string $text
	 * @param string $domain
	 * @return string
	 */
	public static function __(string $text, string $domain = 'demovox'): string
	{
		return __($text, $domain);
	}
}