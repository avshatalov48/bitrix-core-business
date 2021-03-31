<?php
namespace Bitrix\Calendar;


use \Bitrix\Main\Loader;
use Bitrix\Main;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;


class Util
{
	const USER_SELECTOR_CONTEXT = "CALENDAR";
	const LIMIT_NUMBER_BANNER_IMPRESSIONS = 3;

	private static $userAccessCodes = array();

	public static function isManagerForUser($managerId, $userId)
	{
		if (!isset(self::$userAccessCodes[$managerId]))
		{
			$codes = array();
			$r = \CAccess::getUserCodes($managerId);
			while($code = $r->fetch())
			{
				$codes[] = $code['ACCESS_CODE'];
			}
			self::$userAccessCodes[$managerId] = $codes;
		}

		return in_array('IU'.$userId, self::$userAccessCodes[$managerId]);
	}

	public static function isSectionStructureConverted()
	{
		return \Bitrix\Main\Config\Option::get('calendar', 'sectionStructureConverted', 'N') === 'Y';
	}

	public static function getTimestamp($date, $round = true, $getTime = true)
	{
		$timestamp = MakeTimeStamp($date, \CSite::getDateFormat($getTime ? "FULL" : "SHORT"));
		// Get rid of seconds
		if ($round)
		{
			$timestamp = round($timestamp / 60) * 60;
		}
		return $timestamp;
	}

	public static function isTimezoneValid(string $timeZone = null): bool
	{
		return (!is_null($timeZone) && $timeZone !== 'false' && in_array($timeZone, timezone_identifiers_list(), true));
	}

	public static function prepareTimezone($tz = null): \DateTimeZone
	{
		return (self::isTimezoneValid($tz))
			? new \DateTimeZone($tz)
			: new \DateTimeZone("UTC")
		;
	}

	public static function getDateObject(string $date = null, $fullDay = true, $tz = 'UTC'): Date
	{
		$preparedDate = $date;
		if ($date)
		{
			$timestamp = \CCalendar::Timestamp($date, false, !$fullDay);
			$preparedDate = \CCalendar::Date($timestamp);
		}

		return $fullDay
			? new Date($preparedDate, Date::convertFormatToPhp(FORMAT_DATE))
			: new DateTime($preparedDate, Date::convertFormatToPhp(FORMAT_DATETIME), Util::prepareTimezone($tz));
	}

	public static function getUserSelectorContext()
	{
		return self::USER_SELECTOR_CONTEXT;
	}

	public static function getIcalTemplateDate(array $params = null): string
	{
		$from = Util::getDateObject($params['DATE_FROM'], false, $params['TZ_FROM']);
		$to = Util::getDateObject($params['DATE_TO'], false, $params['TZ_TO']);
		if ($from->format('dmY') !== $to->format('dmY'))
		{
			$res = $params['FULL_DAY']
				? $from->format('d.m.Y') . ' - ' . $to->format('d.m.Y')
				: $from->format('d.m.Y H:i') . ' - ' . $to->format('d.m.Y H:i');
		}
		else
		{
			$res = $params['FULL_DAY']
				? $from->format('d.m.Y')
				: $from->format('d.m.Y H:i') . ' - ' . $to->format('H:i');
		}

		return $res;
	}

	public static function getIcalTemplateRRule(array $rrule = null, array $params = null): string
	{
		$res = '';
		Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].'bitrix/modules/calendar/general/classes/calendar.php'
		);

		switch($rrule['FREQ'])
		{
			case 'DAILY':
				if($rrule['INTERVAL'] == 1)
					$res = GetMessage('EC_RRULE_EVERY_DAY');
				else
					$res = GetMessage('EC_RRULE_EVERY_DAY_1', array('#DAY#' => $rrule['INTERVAL']));
				break;
			case 'WEEKLY':
				$daysList = array();
				foreach($rrule['BYDAY'] as $day)
					$daysList[] = GetMessage('EC_'.$day);
				$daysList = implode(', ', $daysList);
				if($rrule['INTERVAL'] == 1)
					$res = GetMessage('EC_RRULE_EVERY_WEEK', array('#DAYS_LIST#' => $daysList));
				else
					$res = GetMessage('EC_RRULE_EVERY_WEEK_1', array('#WEEK#' => $rrule['INTERVAL'], '#DAYS_LIST#' => $daysList));
				break;
			case 'MONTHLY':
				if($rrule['INTERVAL'] == 1)
					$res = GetMessage('EC_RRULE_EVERY_MONTH');
				else
					$res = GetMessage('EC_RRULE_EVERY_MONTH_1', array('#MONTH#' => $rrule['INTERVAL']));
				break;
			case 'YEARLY':
				$fromTs = \CCalendar::Timestamp($params['DATE_FROM']);
//					if ($params['FULL_DAY'])
//					{
//						$fromTs -= $event['~USER_OFFSET_FROM'];
//					}

				if($rrule['INTERVAL'] == 1)
				{
					$res = GetMessage('EC_RRULE_EVERY_YEAR', [
						'#DAY#' => FormatDate('j', $fromTs),
						'#MONTH#' => FormatDate('n', $fromTs)
					]);
				}
				else
				{
					$res = GetMessage('EC_RRULE_EVERY_YEAR_1', [
						'#YEAR#' => $rrule['INTERVAL'],
						'#DAY#' => FormatDate('j', $fromTs),
						'#MONTH#' => FormatDate('n', $fromTs)
					]);
				}
				break;
		}

//		$from = Util::getDateObject($params['DATE_FROM'], false, $params['TZ_FROM']);
//		$to = Util::getDateObject($params['DATE_TO'], false, $params['TZ_TO']);
//		$res .= ' ' . $from->format('H:i'). ' - ' . $to->format('H:i');

		if ($rrule['COUNT'])
		{
			$res .= ' ' . Loc::getMessage('EC_RRULE_COUNT', ['#COUNT#' => $rrule['COUNT']]);
		}
		elseif ($rrule['UNTIL'])
		{
			$res .= ' ' . Loc::getMessage('EC_RRULE_UNTIL', ['#UNTIL_DATE#' => $rrule['UNTIL']]);
		}

		return $res;
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
			if (mb_substr($codeAttend, 0, 1) === 'U')
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
				$userList[] = $stringWrapper . $user['NAME'].' '.$user['LAST_NAME'] . $stringWrapper;
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
			|| (bool)\CUserOptions::GetOption('mobile', 'AndroidLastActivityDate', false);
		$isSyncCalendar = (bool)\CUserOptions::GetOption('calendar', 'last_sync_iphone', false)
			|| (bool)\CUserOptions::GetOption('calendar', 'last_sync_android', false);
		if ($isInstallMobileApp && $isSyncCalendar)
		{
			return false;
		}

		$dailySyncBanner = \CUserOptions::GetOption('calendar', 'daily_sync_banner', false);
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

	public static function isExtranetUser(int $userId)
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
}
