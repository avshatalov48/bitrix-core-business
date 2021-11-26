<?php
namespace Bitrix\Calendar;

use Bitrix\Calendar\Sync\Util\MsTimezoneConverter;
use \Bitrix\Main\Loader;
use Bitrix\Main;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;

class Util
{
	public const USER_SELECTOR_CONTEXT = "CALENDAR";
	public const LIMIT_NUMBER_BANNER_IMPRESSIONS = 3;
	public const DATETIME_PHP_FORMAT = 'Y-m-d H:i:sP';

	private static $requestUid = '';
	private static $userAccessCodes = [];

	/**
	 * @param $managerId
	 * @param $userId
	 * @return bool
	 */
	public static function isManagerForUser($managerId, $userId): bool
	{
		return in_array('IU'.$userId, self::getUserAccessCodes($managerId));
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isSectionStructureConverted(): bool
	{
		return \Bitrix\Main\Config\Option::get('calendar', 'sectionStructureConverted', 'N') === 'Y';
	}

	/**
	 * @param $date
	 * @param bool $round
	 * @param bool $getTime
	 * @return false|float|int
	 */
	public static function getTimestamp($date, $round = true, $getTime = true)
	{
		$timestamp = MakeTimeStamp($date, \CSite::getDateFormat($getTime ? "FULL" : "SHORT"));

		return $round ? (round($timestamp / 60) * 60) : $timestamp;
	}

	/**
	 * @param string|null $timeZone
	 * @return bool
	 */
	public static function isTimezoneValid(?string $timeZone): bool
	{
		return (!is_null($timeZone) && $timeZone !== 'false' && in_array($timeZone, timezone_identifiers_list(), true));
	}

	/**
	 * @param string|null $tz
	 * @return \DateTimeZone
	 */
	public static function prepareTimezone(?string $tz = null): \DateTimeZone
	{
		if (!$tz)
		{
			return new \DateTimeZone("UTC");
		}

		if (self::isTimezoneValid($tz))
		{
			return new \DateTimeZone($tz);
		}

		if ($timezones = MsTimezoneConverter::getValidateTimezones($tz))
		{
			return new \DateTimeZone($timezones[0]);
		}

		return new \DateTimeZone(self::getServerTimezoneName());
	}

	/**
	 * @param string|null $date
	 * @param bool $fullDay
	 * @param string $tz
	 * @return Date
	 * @throws Main\ObjectException
	 */
	public static function getDateObject(string $date = null, $fullDay = true, $tz = 'UTC'): Date
	{
		$preparedDate = $date;
		if ($date)
		{
			$timestamp = \CCalendar::Timestamp($date, false, !$fullDay);
			$preparedDate = \CCalendar::Date($timestamp, !$fullDay);
		}

		return $fullDay
			? new Date($preparedDate, Date::convertFormatToPhp(FORMAT_DATE))
			: new DateTime($preparedDate, Date::convertFormatToPhp(FORMAT_DATETIME), Util::prepareTimezone($tz));
	}

	/**
	 * @return string
	 */
	public static function getUserSelectorContext(): string
	{
		return self::USER_SELECTOR_CONTEXT;
	}

	public static function checkRuZone(): bool
	{
		if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$isRussian = (\CBitrix24::getPortalZone() === 'ru');
		}
		else
		{
			$iterator = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => ['=ID' => 'ru', '=ACTIVE' => 'Y']
			]);

			$row = $iterator->fetch();
			if (empty($row))
			{
				$isRussian = false;
			}
			else
			{
				$iterator = LanguageTable::getList([
					'select' => ['ID'],
					'filter' => ['@ID' => ['ua', 'by', 'kz'], '=ACTIVE' => 'Y'],
					'limit' => 1
				]);
				$row = $iterator->fetch();
				$isRussian = empty($row);
			}
		}

		return $isRussian;
	}

	public static function convertEntitiesToCodes($entityList = [])
	{
		$codeList = [];
		if (is_array($entityList))
		{
			foreach($entityList as $entity)
			{
				if ($entity['entityId'] === 'meta-user' && $entity['id'] === 'all-users')
				{
					$codeList[] = 'UA';
				}
				elseif ($entity['entityId'] === 'user')
				{
					$codeList[] = 'U'.$entity['id'];
				}
				elseif ($entity['entityId'] === 'project')
				{
					$codeList[] = 'SG'.$entity['id'];
				}
				elseif ($entity['entityId'] === 'department')
				{
					$codeList[] = 'DR'.$entity['id'];
				}
			}
		}
		return $codeList;
	}

	public static function convertCodesToEntities($codeList = [])
	{
		$entityList = [];
		if (is_array($codeList))
		{
			foreach($codeList as $code)
			{
				if ($code === 'UA')
				{
					$entityList[] = [
						'entityId' => 'meta-user',
						'id' => 'all-users'
					];
				}
				elseif (mb_substr($code, 0, 1) == 'U')
				{
					$entityList[] = [
						'entityId' => 'user',
						'id' => intval(mb_substr($code, 1))
					];
				}
				if (mb_substr($code, 0, 2) == 'DR')
				{
					$entityList[] = [
						'entityId' => 'department',
						'id' => intval(mb_substr($code, 2))
					];
				}
				elseif (mb_substr($code, 0, 2) == 'SG')
				{
					$entityList[] = [
						'entityId' => 'project',
						'id' => intval(mb_substr($code, 2))
					];
				}
			}
		}

		return $entityList;
	}

	public static function getUsersByEntityList($entityList, $fetchUsers = false)
	{
		if (!Main\Loader::includeModule('socialnetwork'))
		{
			return [];
		}
		$users = \CSocNetLogDestination::getDestinationUsers(self::convertEntitiesToCodes($entityList), $fetchUsers);
		if ($fetchUsers)
		{
			for ($i = 0, $l = count($users); $i < $l; $i++)
			{
				$users[$i]['FORMATTED_NAME'] = \CCalendar::getUserName($users[$i]);
			}
		}
		return $users;
	}


	public static function getDefaultEntityList($userId, $type, $ownerId)
	{
		$entityList = [['entityId' => 'user', 'id' => $userId]];
		if ($type === 'user' && $ownerId !== $userId)
		{
			$entityList[] = ['entityId' => 'user', 'id' => $ownerId];
		}
		else if($type === 'group')
		{
			$entityList[] = ['entityId' => 'project', 'id' => $ownerId];
		}
		return $entityList;
	}

	/**
	 * @param array|null $codeAttendees
	 * @param string $stringWrapper
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getAttendees(array $codeAttendees = null, string $stringWrapper = ''): array
	{
		if (empty($codeAttendees))
		{
			return [];
		}

		$userIdList = [];
		$userList = [];

		foreach ($codeAttendees as $codeAttend)
		{
			if (mb_strpos($codeAttend, 'U') === 0)
			{
				$userId = (int)(mb_substr($codeAttend, 1));
				$userIdList[] = $userId;
			}
		}

		if (!empty($userIdList))
		{
			$res = \Bitrix\Main\UserTable::getList(array(
				'filter' => array(
					'=ID' => $userIdList,
				),
				'select' => array('NAME', 'LAST_NAME'),
			));

			while ($user = $res->fetch())
			{
				$userList[] = addcslashes($stringWrapper . $user['NAME'].' '.$user['LAST_NAME'] . $stringWrapper, "()");
			}
		}

		return $userList;
	}

	/**
	 * @return bool
	 */
	public static function isShowDailyBanner(): bool
	{
		$isInstallMobileApp = (bool)\CUserOptions::GetOption('mobile', 'iOsLastActivityDate', false)
			|| (bool)\CUserOptions::GetOption('mobile', 'AndroidLastActivityDate', false)
		;
		$isSyncCalendar = (bool)\CUserOptions::GetOption('calendar', 'last_sync_iphone', false)
			|| (bool)\CUserOptions::GetOption('calendar', 'last_sync_android', false)
		;
		if ($isInstallMobileApp && $isSyncCalendar)
		{
			return false;
		}

		$dailySyncBanner = \CUserOptions::GetOption('calendar', 'daily_sync_banner', []);
		if (!isset($dailySyncBanner['last_sync_day']) && !isset($dailySyncBanner['count']))
		{
			$dailySyncBanner['last_sync_day'] = '';
			$dailySyncBanner['count'] = 0;
		}
		$today = (new Main\Type\Date())->format('Y-m-d');
		$isShowToday = ($today === $dailySyncBanner['last_sync_day']);
		$isLimitExceeded = ($dailySyncBanner['count'] >= self::LIMIT_NUMBER_BANNER_IMPRESSIONS);

		if ($isLimitExceeded || $isShowToday)
		{
			return false;
		}
		else
		{
			++$dailySyncBanner['count'];
			$dailySyncBanner['last_sync_day'] = (new Main\Type\Date())->format('Y-m-d');
			\CUserOptions::SetOption('calendar', 'daily_sync_banner', $dailySyncBanner);
			return true;
		}

	}

	/**
	 * @param int $userId
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isExtranetUser(int $userId): bool
	{
		if (Loader::includeModule('intranet'))
		{
			$userDb = \Bitrix\Intranet\UserTable::getList([
				'filter' => [
					'ID' => $userId,
				],
				'select' => [
					'USER_TYPE',
				]
			]);

			$user = $userDb->fetch();
			return $user['USER_TYPE'] === 'extranet';
		}

		return false;
	}

	/**
	 * @param int $eventId
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getEventById(int $eventId): ?array
	{
		$eventDb = Internals\EventTable::getList([
			'filter' => [
				'=ID' => $eventId,
			],
		]);

		if ($event = $eventDb->fetch())
		{
			return $event;
		}

		return null;
	}

	/**
	 * @param string $command
	 * @param int $userId
	 * @param array $params
	 * @return bool
	 */
	public static function addPullEvent(string $command, int $userId, array $params = []): bool
	{
		if (Loader::includeModule("pull"))
		{
			if (in_array($command, [
				'edit_event',
				'delete_event',
				'set_meeting_status',
			]))
			{
				\CPullWatch::AddToStack(
					'calendar-planner-'.$userId,
					[
						'module_id' => 'calendar',
						'command' => $command,
						'params' => $params
					]
				);
			}

			if (in_array($command, [
				'edit_event',
				'delete_event',
				'set_meeting_status',
			])
				&& isset($params['fields'])
				&& isset($params['fields']['SECTION_OWNER_ID'])
				&& (int)$params['fields']['SECTION_OWNER_ID'] !== $userId
			)
			{
				\Bitrix\Pull\Event::add(
					(int)$params['fields']['SECTION_OWNER_ID'],
					[
						'module_id' => 'calendar',
						'command' => $command,
						'params' => $params
					]
				);
			}

			return \Bitrix\Pull\Event::add(
				$userId,
				[
					'module_id' => 'calendar',
					'command' => $command,
					'params' => $params
				]
			);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param int $currentUserId
	 * @param array $userIdList
	 *
	 * @return void
	 */
	public static function initPlannerPullWatches(int $currentUserId, array $userIdList = []): void
	{
		if (Loader::includeModule("pull"))
		{
			foreach($userIdList as $userId)
			{
				if ((int)$userId !== $currentUserId)
				{
					\CPullWatch::Add($currentUserId, 'calendar-planner-'.$userId);
				}
			}
		}
	}

	public static function getUserFieldsByEventId(int $eventId): array
	{
		global $DB;
		$result = [];
		$strSql = "SELECT * from b_uts_calendar_event WHERE VALUE_ID=" . $eventId;
		$ufDb = $DB->query($strSql);

		while ($uf = $ufDb->fetch())
		{
			$result[] = [
				'crm' => unserialize($uf['UF_CRM_CAL_EVENT'], ['allowed_classes' => false]),
				'webdav' => unserialize($uf['UF_WEBDAV_CAL_EVENT'], ['allowed_classes' => false]),
			];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getServerTimezoneName(): string
	{
		return (new \DateTime())->getTimezone()->getName();
	}

	/**
	 * @return int
	 */
	public static function getServerOffsetUTC(): int
	{
		return (new \DateTime())->getOffset();
	}

	/**
	 * @param string|null $tz
	 * @param null $date
	 * @return int
	 * @throws \Exception
	 */
	public static function getTimezoneOffsetFromServer(?string $tz = 'UTC', $date = null): int
	{
		if ($date instanceof Date)
		{
			$timestamp = $date->format(self::DATETIME_PHP_FORMAT);
		}
		elseif ($date === null)
		{
			$timestamp = 'now';
		}
		else
		{
			$timestamp = "@".(int)$date;
		}

		$date = new \DateTime($timestamp, self::prepareTimezone($tz));

		return $date->getOffset() - self::getServerOffsetUTC();
	}

	/**
	 * @param string $requestUid
	 */
	public static function setRequestUid(string $requestUid = ''): void
	{
		self::$requestUid = $requestUid;
	}

	/**
	 * @return string
	 */
	public static function getRequestUid(): string
	{
		return self::$requestUid;
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	public static function getUserAccessCodes(int $userId): array
	{
		global $USER;
		$userId = (int)$userId;
		if (!$userId)
		{
			$userId = \CCalendar::GetCurUserId();
		}

		if (!isset(self::$userAccessCodes[$userId]))
		{
			$codes = [];
			$r = \CAccess::GetUserCodes($userId);
			while($code = $r->Fetch())
			{
				$codes[] = $code['ACCESS_CODE'];
			}

			if (!in_array('G2', $codes))
			{
				$codes[] = 'G2';
			}

			if (!in_array('AU', $codes) && $USER && (int)$USER->GetId() === $userId)
			{
				$codes[] = 'AU';
			}

			if(!in_array('UA', $codes) && $USER && (int)$USER->GetId() == $userId)
			{
				$codes[] = 'UA';
			}

			self::$userAccessCodes[$userId] = $codes;
		}

		return self::$userAccessCodes[$userId];
	}
}
