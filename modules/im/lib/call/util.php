<?php

namespace Bitrix\Im\Call;

class Util
{
	/**
	 * @param array $idList
	 */
	public static function getUsers(array $idList): array
	{
		$result = [];
		foreach ($idList as $userId)
		{
			$user = \Bitrix\Im\User::getInstance($userId)->getArray(['JSON' => 'Y', 'HR_PHOTO' => true]);
			$result[$userId] = [
				'id' => $user['id'],
				'first_name' => $user['first_name'],
				'last_name' => $user['last_name'],
				'name' => $user['name'],
				'work_position' => $user['work_position'],
				'extranet' => $user['extranet'],
				'invited' => $user['invited'],
				'last_activity_date' => $user['last_activity_date'],
				'avatar' => $user['avatar'],
				'avatar_hr' => $user['avatar_hr'],
				'gender' => $user['gender'],
				'color' => $user['color'],
				'type' => $user['type'],
			];
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