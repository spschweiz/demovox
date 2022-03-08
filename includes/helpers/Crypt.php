<?php

namespace Demovox;

class Crypt
{

	/**
	 * @return bool
	 */
	public static function isEncryptionEnabled()
	{
		return self::getEncryptionMode() !== 'disabled';
	}

	/**
	 * @param string $value
	 *
	 * @return string|null
	 */
	public static function encrypt($value)
	{
		if (!defined('DEMOVOX_ENC_KEY')) {
			Core::errorDie('Encryption failed: Constant DEMOVOX_ENC_KEY is not defined in wp-config.php', 500);
		}
		try {
			$key = \Defuse\Crypto\Key::loadFromAsciiSafeString(DEMOVOX_ENC_KEY);
			return $encyrpted = \Defuse\Crypto\Crypto::encrypt($value, $key);
		} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
			Core::errorDie('Encryption failed: EnvironmentIsBrokenException (' . $e->getMessage() . ')', 500);
		} catch (\TypeError $e) {
			Core::errorDie('Encryption failed: TypeError (' . $e->getMessage() . ' Value:' . $value . ')', 500);
		} catch (\Defuse\Crypto\Exception\BadFormatException $e) {
			Core::errorDie('Decryption failed: BadFormatException (' . $e->getMessage() . ')', 500);
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public static function getEncryptionMode()
	{
		return Settings::getValue('encrypt_signees');
	}

	/**
	 * @param string $value
	 *
	 * @return string|null
	 */
	public static function decrypt($value)
	{
		if (!defined('DEMOVOX_ENC_KEY')) {
			Core::errorDie('Decryption failed: Constant DEMOVOX_ENC_KEY is not defined in wp-config.php', 500);
		}
		try {
			$key = \Defuse\Crypto\Key::loadFromAsciiSafeString(DEMOVOX_ENC_KEY);
			return $decyrpted = \Defuse\Crypto\Crypto::decrypt($value, $key);
		} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
			Core::errorDie(
				'Decryption failed: WrongKeyOrModifiedCiphertextException (' . $e->getMessage() . ' Value:' . $value . ')',
				500
			);
		} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
			Core::errorDie('Decryption failed: EnvironmentIsBrokenException (' . $e->getMessage() . ')', 500);
		} catch (\TypeError $e) {
			Core::errorDie('Decryption failed: TypeError (' . $e->getMessage() . ')', 500);
		} catch (\Defuse\Crypto\Exception\BadFormatException $e) {
			Core::errorDie('Decryption failed: BadFormatException (' . $e->getMessage() . ')', 500);
		}
		return null;
	}
}