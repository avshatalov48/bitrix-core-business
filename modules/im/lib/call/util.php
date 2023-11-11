<?php

namespace Bitrix\Im\Call;

class Util
{
	/**
	 * @param array $idList
	 */
	public static function getUsers(array $idList)
	{
		$result = [];
		foreach ($idList as $userId)
		{
			$result[$userId] = \Bitrix\Im\User::getInstance($userId)->getArray(['JSON' => 'Y', 'HR_PHOTO' => true]);
		}

		return $result;
	}

	public static function generateUUID()
	{
		if (function_exists('random_bytes'))
		{
			$data = random_bytes(16);
		}
		elseif (function_exists('openssl_random_pseudo_bytes'))
		{
			$data = openssl_random_pseudo_bytes(16);
		} else
		{
			$data = uniqid('', true);
		}

		// set version to 4
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);

		// set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}