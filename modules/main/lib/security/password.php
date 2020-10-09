<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\Security;

class Password
{
	/**
	 * Compares a hash and a(n) (original) password.
	 * @param string $hash Hash of the password.
	 * @param string $password User supplied password.
	 * @param bool $original
	 * @return bool
	 */
	public static function equals($hash, $password, $original = true): bool
	{
		if($original)
		{
			$salt = "";
			$hashLength = strlen($hash);

			if($hashLength > 100)
			{
				//new SHA-512 method, format is $6${salt}${hash}
				$salt = substr($hash, 3, 16);

				$password = static::hash($password, $salt);
			}
			else
			{
				if($hashLength > 32)
				{
					//old salt+md5 method, format is {salt}{hash}
					$salt = substr($hash, 0, $hashLength - 32);
				}
				//else very old format {hash} without a salt

				$password = $salt.md5($salt.$password);
			}
		}

		return hash_equals($hash, $password);
	}

	/**
	 * Determines if a password needs to be rehashed.
	 * @param string $hash Hash of the password.
	 * @return bool
	 */
	public static function needRehash($hash): bool
	{
		if(CRYPT_SHA512 == 1)
		{
			if(strlen($hash) > 100)
			{
				//new SHA-512 hash usually 106 bytes long
				return false;
			}
		}
		else
		{
			if(strlen($hash) > 32)
			{
				//old md5+salt method
				return false;
			}
		}
		return true;
	}

	/**
	 * Hashes a password using SHA-512 by default.
	 * @param string $password
	 * @param null|string $salt If null, will be generated
	 * @return string
	 */
	public static function hash($password, $salt = null): string
	{
		if(CRYPT_SHA512 == 1)
		{
			//new SHA-512 method
			if($salt === null)
			{
				$salt = Random::getString(16, true);
			}
			//by default rounds=5000
			return crypt($password, '$6$'.$salt.'$');
		}
		else
		{
			//old md5 method
			if($salt === null)
			{
				$salt = Random::getStringByAlphabet(8, Random::ALPHABET_ALL);
			}
			return $salt.md5($salt.$password);
		}
	}
}
