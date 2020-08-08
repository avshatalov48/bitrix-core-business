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
}