<?php


namespace Bitrix\Calendar\ICal\Basic;


use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Calendar\Internals;

class ICalUtil
{
	const ICAL_DATETIME_FORMAT = 'Ymd\THis\Z';
	const ICAL_DATETIME_FORMAT_SHORT = 'Ymd\THis';
	const ICAL_DATE_FORMAT = 'Ymd';

	public static function getServerName()
	{
		return \COption::getOptionString('main', 'server_name');
	}

	public static function getUniqId()
	{
		return uniqid(self::getServerName(), true);
	}

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

	/*
 * Returns id of the 'external' user by email. Creates user if it not exists.
 *
 * @param array $params - incomoning params:
 * $params['EMAIL'] - (required) email of the user
 * $params['NAME'] - user's name
 * $params['LAST_NAME'] - user's last name

 * @return 'userId' - id of the user, or null
 */
	public static function getExternalUserByEmail($params, &$errorText)
	{
		$userId = null;
		$user = null;

		if (
			!is_array($params)
			|| empty($params['EMAIL'])
			|| !check_email($params['EMAIL'])
			|| !Loader::includeModule('mail')
		)
		{
			return $userId;
		}

		$userEmail = $params['EMAIL'];

		if (
			empty($userEmail)
			|| !check_email($userEmail)
		)
		{
			return $userId;
		}

		$res = \CUser::getList(
			$o = "ID",
			$b = "ASC",
			[
				"=EMAIL" => $userEmail,
				"!EXTERNAL_AUTH_ID" => [ "bot", "controller", "replica", "shop", "imconnector", "sale", "saleanonymous" ]
			],
			[
				"FIELDS" => [ "ID", "EXTERNAL_AUTH_ID", "ACTIVE" ]
			]
		);

		while (($emailUser = $res->fetch()) && !$userId)
		{
			if (
				intval($emailUser["ID"]) > 0
				&& (
					$emailUser["ACTIVE"] == "Y"
					|| $emailUser["EXTERNAL_AUTH_ID"] == "email"
				)
			)
			{
				if ($emailUser["ACTIVE"] == "N") // email only
				{
					$user = new \CUser;
					$user->update($emailUser["ID"], [
						'ACTIVE' => 'Y'
					]);
				}

				$userId = $emailUser['ID'];
			}
		}

		if (!$userId)
		{
			$userFields = [
				'EMAIL' => $userEmail,
				'NAME' => isset($params["NAME"])  ? $params["NAME"] : '',
				'LAST_NAME' => isset($params["LAST_NAME"]) ? $params["LAST_NAME"] : ''
			];

			// create "external" user by email
			$user = \Bitrix\Mail\User::create($userFields);
			$errorMessage = false;
			if (is_object($user) && $user->LAST_ERROR <> '')
			{
				$errorMessage = $user->LAST_ERROR;
			}

			if (!$errorMessage && intval($user) > 0)
			{
				$userId = intval($user);
			}
			else
			{
				$errorText = $errorMessage;
			}
		}

		if (!is_object($user) && intval($userId) > 0)
		{
			\Bitrix\Main\UI\Selector\Entities::save([
				'context' => Util::getUserSelectorContext(),
				'code' => 'U'.$userId
			]);
		}

		return $userId;
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

	public static function getEventByUId($userId, $uid): ?array
	{
		$parameters = [
			'filter' => [
				'=DAV_XML_ID' => $uid,
				'OWNER_ID' => $userId,
			],
			'limit' => 1,
		];

		if ($event = Internals\EventTable::getList($parameters)->fetch())
		{
			return $event;
		}

		return null;
	}

	public static function getUserById(?string $id)
	{
		$parameters = [
			'filter' => [
				'ID' => $id,
			],
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
			],
			'limit' => 1,
		];

		$userDd = UserTable::getList($parameters);

		if ($user = $userDd->fetch())
		{
			return $user;
		}

		return null;
	}

	public static function getIcalDateTime(string $dateTime = null, string $tz = null): DateTime
	{
		return new DateTime($dateTime, self::ICAL_DATETIME_FORMAT, Util::prepareTimezone($tz));
	}

	public static function getIcalDate(string $date = null): Date
	{
		return new Date($date, self::ICAL_DATE_FORMAT);
	}

	public static function prepareAttendeesToCancel($attendees)
	{
		foreach ($attendees as $attendee)
		{
			$usersId[] = $attendee['id'];
		}

		return !empty($usersId) ? self::getUsersById($usersId) : null;
	}

	public static function getUsersById(array $usersId)
	{
		$parameters = [
			'filter' => [
				'ID' => $usersId,
			],
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
				'EMAIL',
			],
		];

		$usersDd = UserTable::getList($parameters);

		while ($user = $usersDd->fetch())
		{
			$users[$user['ID']] = $user;
		}

		return $users;
	}

	public static function getUsersByCode(array $attendeesCodeList = null)
	{
		foreach ($attendeesCodeList as $code)
		{
			if(mb_substr($code, 0, 1) == 'U')
			{
				$userIdsList[] = intVal(mb_substr($code, 1));
			}
		}

		return self::getUsersById($userIdsList);
	}

	public static function getIcalDateTimeShort(string $dateTime = null, string $tz = 'UTC')
	{
		return new DateTime($dateTime, self::ICAL_DATETIME_FORMAT_SHORT, Util::prepareTimezone($tz));
	}
}