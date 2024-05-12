<?php
namespace Bitrix\Calendar;

use Bitrix\Calendar\Sync\Util\MsTimezoneConverter;
use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use COption;

class Util
{
	public const USER_SELECTOR_CONTEXT = "CALENDAR";
	public const USER_FIELD_ENTITY_ID = 'CALENDAR_EVENT';
	public const LIMIT_NUMBER_BANNER_IMPRESSIONS = 3;
	public const DATETIME_PHP_FORMAT = 'Y-m-d H:i:sP';
	public const VERSION_DIFFERENCE = 1;
	public const DEFAULT_TIMEZONE = "UTC";

	private static $requestUid = '';
	private static $userAccessCodes = [];
	private static $pathCache = [];
	private static $isRussian = null;

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
			return new \DateTimeZone(self::DEFAULT_TIMEZONE);
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
	 * @param string|null $tz
	 * @return Date
	 * @throws Main\ObjectException
	 */
	public static function getDateObject(string $date = null, ?bool $fullDay = true, ?string $tz = 'UTC'): Date
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
		if (!is_null(self::$isRussian))
		{
			return self::$isRussian;
		}

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			self::$isRussian = (\CBitrix24::getPortalZone() === 'ru');
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
				self::$isRussian = false;
			}
			else
			{
				$iterator = LanguageTable::getList([
					'select' => ['ID'],
					'filter' => ['@ID' => ['ua', 'by', 'kz'], '=ACTIVE' => 'Y'],
					'limit' => 1
				]);
				$row = $iterator->fetch();
				self::$isRussian = empty($row);
			}
		}

		return self::$isRussian;
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
				elseif ($entity['entityId'] === 'project' || $entity['entityId'] === 'project-roles')
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
				elseif (preg_match('/^SG([0-9]+)_?([AEKMO])?$/', $code, $match) && isset($match[2]))
				{
					// todo May need to be removed/rewrite after creating new roles in projects.
					$entityList[] = [
						'entityId' => 'project-roles',
						'id' => mb_substr($code, 2)
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
		return \CCalendar::GetDestinationUsers(self::convertEntitiesToCodes($entityList), $fetchUsers);
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
				'filter' => [
					'=ID' => $userIdList,
				],
				'select' => ['NAME', 'LAST_NAME'],
			));

			while ($user = $res->fetch())
			{
				$userList[] = addcslashes($stringWrapper . $user['NAME'].' '.$user['LAST_NAME'] . $stringWrapper, "()");
			}
		}

		return $userList;
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
			if (!empty($event['NAME']))
			{
				$event['NAME'] = Emoji::decode($event['NAME']);
			}
			if (!empty($event['DESCRIPTION']))
			{
				$event['DESCRIPTION'] = Emoji::decode($event['DESCRIPTION']);
			}
			if (!empty($event['LOCATION']))
			{
				$event['LOCATION'] = Emoji::decode($event['LOCATION']);
			}
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
		if (!Loader::includeModule("pull"))
		{
			return false;
		}

		if (
			in_array($command, [
				'edit_event',
				'delete_event',
				'set_meeting_status',
			])
		)
		{
			\CPullWatch::AddToStack(
				'calendar-planner-'.$userId,
				[
					'module_id' => 'calendar',
					'command' => $command,
					'params' => $params
				]
			);

			if (isset($params['fields']['CAL_TYPE']) && $params['fields']['CAL_TYPE'] === 'location')
			{
				\CPullWatch::AddToStack(
					'calendar-location',
					[
						'module_id' => 'calendar',
						'command' => "{$command}_location",
						'params' => [
							'fields' => [
								'DATE_FROM' => $params['fields']['DATE_FROM'],
								'DATE_TO' => $params['fields']['DATE_TO'],
								'EXDATE' => $params['fields']['EXDATE'],
							],
						],
					],
				);
			}
		}

		if (
			in_array($command, [
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


	/**
	 * @param int $ownerId
	 * @param string $type
	 * @return string
	 */
	public static function getPathToCalendar(?int $ownerId, ?string $type): string
	{
		$key = $type . $ownerId;
		if (!isset(self::$pathCache[$key]) || !is_string(self::$pathCache[$key]))
		{
			if ($type === 'user')
			{
				$path = \COption::GetOptionString(
					'calendar',
					'path_to_user_calendar',
					\COption::getOptionString('socialnetwork', 'user_page', "/company/personal/")
					. "user/#user_id#/calendar/"
				);
			}
			elseif ($type === 'group')
			{
				$path = \COption::GetOptionString(
					'calendar',
					'path_to_group_calendar',
					\COption::getOptionString('socialnetwork', 'workgroups_page', "/workgroups/")
					. "group/#group_id#/calendar/"
				);
			}
			else
			{
				$settings = \CCalendar::GetSettings();
				$path = $settings['path_to_type_' . $type] ?? null;
			}

			if (!\COption::GetOptionString('calendar', 'pathes_for_sites', true))
			{
				$siteId = \CCalendar::GetSiteId();
				$pathList = \CCalendar::GetPathes();
				if (isset($pathList[$siteId]))
				{
					if ($type === 'user' && isset($pathList[$siteId]['path_to_user_calendar']))
					{
						$path = $pathList[$siteId]['path_to_user_calendar'];
					}
					elseif ($type === 'group' && isset($pathList[$siteId]['path_to_group_calendar']))
					{
						$path = $pathList[$siteId]['path_to_group_calendar'];
					}
					else if (!empty($pathList[$siteId]['path_to_type_' . $type]))
					{
						$path = $pathList[$siteId]['path_to_type_' . $type];
					}
				}
			}

			if (!is_string($path))
			{
				$path =  '';
			}

			if (!empty($path) && $ownerId > 0)
			{
				if ($type === 'user')
				{
					$path = str_replace(['#user_id#', '#USER_ID#'], $ownerId, $path);
				}
				elseif ($type === 'group')
				{
					$path = str_replace(['#group_id#', '#GROUP_ID#'], $ownerId, $path);
				}
			}
			self::$pathCache[$key] = $path;
		}

		return self::$pathCache[$key];
	}

	/**
	 * @return string
	 */
	public static function getServerName(): string
	{
		return COption::getOptionString('main', 'server_name', Application::getInstance()->getContext()->getServer()->getServerName());
	}

	/**
	 * @param int $second
	 *
	 * @return int[]
	 */
	public static function secondsToDayHoursMinutes(int $second): array
	{
		$day = $second / 24 / 3600;
        $hours = $second / 3600 - (int)$day * 24;
        $min = $second / 60 - (int)$day * 24 * 60 - (int)$hours * 60;

		return [
			'days' => (int)$day,
			'hours' => (int)$hours,
			'minutes' => (int)$min
		];
	}

	/**
	 * @param int $minutes
	 *
	 * @return int[]
	 */
	public static function minutesToDayHoursMinutes(int $minutes): array
	{
		$day = $minutes / 24 / 60;
		$hours = $minutes / 60 - (int)$day * 24;
		$min = $minutes - (int)$day * 24 * 60 - (int)$hours * 60;

		return [
			'days' => (int)$day,
			'hours' => (int)$hours,
			'minutes' => (int)$min
		];
	}

	public static function getDateTimestamp(?string $dateFrom, ?string $timezone): ?int
	{
		if (!$dateFrom || !$timezone)
		{
			return null;
		}

		$date =  new \Bitrix\Calendar\Core\Base\Date(
			Util::getDateObject(
				$dateFrom,
				false,
				$timezone
			)
		);

		return $date->getTimestamp();
	}

	public static function formatDateTimeTimestamp(int $timestamp, string $timezoneName): string
	{
		$timezone = new \DateTimeZone($timezoneName);
		$dateTimeFormat = Date::convertFormatToPhp(FORMAT_DATETIME);

		return (new \DateTime('now', $timezone))
			->setTimestamp($timestamp)
			->format($dateTimeFormat)
		;
	}

	public static function formatDateTimeTimestampUTC(int $timestamp): string
	{
		$dateTimeFormat = Date::convertFormatToPhp(FORMAT_DATETIME);

		return gmdate($dateTimeFormat, $timestamp);
	}

	public static function formatDateTimestampUTC(int $timestamp): string
	{
		$dateFormat = Date::convertFormatToPhp(FORMAT_DATE);

		return gmdate($dateFormat, $timestamp);
	}

	public static function getTimezoneOffsetUTC(string $timezoneName): int
	{
		$utc = new \DateTimeZone('UTC');

		return (new \DateTimeZone($timezoneName))->getOffset(new \DateTime('now', $utc));
	}

	public static function getDateTimestampUtc(DateTime $date, ?string $eventTimezone = null): int
	{
		$dateTimezone = $date->getTimeZone()->getName();
		$dateTimestampUTC = $date->getTimestamp() + \CCalendar::GetTimezoneOffset($dateTimezone);
		$eventOffsetUTC = \CCalendar::GetTimezoneOffset($eventTimezone);

		return $dateTimestampUTC - $eventOffsetUTC;
	}

	public static function formatEventDateTime(DateTime $dateTime): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->getDateFormat());
		$timeFormat = $culture->get('SHORT_TIME_FORMAT');

		$eventDate = FormatDate($dayMonthFormat, $dateTime->getTimestamp());
		$eventTime = FormatDate($timeFormat, $dateTime->getTimestamp());

		return "$eventDate $eventTime";
	}

	public static function getTimezoneHint(int $userId, array $event): string
	{
		$skipTime = $event['DT_SKIP_TIME'] === "Y";
		$timezoneHint = '';
		if (
			!$skipTime
			&& (
				(int)$event['~USER_OFFSET_FROM'] !== 0
				|| (int)$event['~USER_OFFSET_TO'] !== 0
				|| $event['TZ_FROM'] !== $event['TZ_TO']
				|| $event['TZ_FROM'] !== \CCalendar::GetUserTimezoneName($userId)
			)
		)
		{
			if ($event['TZ_FROM'] === $event['TZ_TO'])
			{
				$timezoneHint = \CCalendar::GetFromToHtml(
					\CCalendar::Timestamp($event['DATE_FROM']),
					\CCalendar::Timestamp($event['DATE_TO']),
					false,
					$event['DT_LENGTH']
				);
				if ($event['TZ_FROM'])
				{
					$timezoneHint .= ' (' . $event['TZ_FROM'] . ')';
				}
			}
			else
			{
				$timezoneHint = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $event['DATE_FROM'].' ('.$event['TZ_FROM'].')', '#DATE_TO#' => $event['DATE_TO'].' ('.$event['TZ_TO'].')'));
			}
		}

		return $timezoneHint;
	}

	public static function formatEventDate(DateTime $dateTime): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->getDateFormat());

		return FormatDate($dayMonthFormat, $dateTime->getTimestamp());
	}

	public static function doIntervalsIntersect($from1, $to1, $from2, $to2): bool
	{
		return self::oneIntervalIntersectsAnother($from1, $to1, $from2, $to2)
			|| self::oneIntervalIntersectsAnother($from2, $to2, $from1, $to1);
	}

	public static function oneIntervalIntersectsAnother($from1, $to1, $from2, $to2): bool
	{
		$startsInside = $from2 <= $from1 && $from1 < $to2;
		$endsInside = $from2 < $to1 && $to1 <= $to2;
		$startsBeforeEndsAfter = $from1 <= $from2 && $to1 >= $to2;

		return $startsInside || $endsInside || $startsBeforeEndsAfter;
	}
}
