<?php


namespace Bitrix\Calendar\ICal\Basic;


use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class ICalUtil
{
	public static function isMailUser($userId): bool
	{
		$parameters = [
			'filter' => [
				'ID' => $userId,
			],
			'select' => [
				'ID', 'EXTERNAL_AUTH_ID',
			],
			'limit' => 1,
		];

		$userDd = UserTable::getList($parameters);

		if ($user = $userDd->fetch())
		{
			return $user['EXTERNAL_AUTH_ID'] === 'email';
		}

		return false;
	}

	public static function processDestinationUserEmail($params, &$errorText)
	{
		$userId = static::getExternalUserByEmail($params, $errorText);
		return $userId ? ['U'.$userId] : [];
	}

	public static function getUserIdByEmail(array $userInfo): ?string
	{
		$parameters = [
			'filter' => [
				'EMAIL' => $userInfo['EMAIL'],
			],
			'select' => ['ID',],
			'limit' => 1,
		];

		$userDd = UserTable::getList($parameters);
		if ($user = $userDd->fetch())
		{
			return $user['ID'];
		}

		return self::getExternalUserByEmail($userInfo, $errorCollection);
	}

	public static function prepareAttendeesToCancel($attendees)
	{
		foreach ($attendees as $attendee)
		{
			$usersId[] = $attendee['id'];
		}

		return !empty($usersId) ? self::getIndexUsersById($usersId) : null;
	}

	/**
	 * @param int[] $usersId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getIndexUsersById(array $usersId): array
	{
		$users = [];
		$usersDd = UserTable::getList([
			'filter' => [
				'ID' => $usersId,
			],
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
				'EMAIL',
			],
		]);

		while ($user = $usersDd->fetch())
		{
			$users[$user['ID']] = $user;
		}

		return $users;
	}

	/**
	 * @param array|null $attendeesCodeList
	 * @return array
	 */
	public static function getUsersByCode(array $attendeesCodeList = null): array
	{
		$userIdsList = [];
		foreach ($attendeesCodeList as $code)
		{
			if(mb_strpos($code, 'U') === 0)
			{
				$userIdsList[] = (int)mb_substr($code, 1);
			}
		}

		return self::getIndexUsersById($userIdsList);
	}
}
