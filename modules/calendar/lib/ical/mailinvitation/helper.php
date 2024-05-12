<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\ICal\Builder\Attach;
use Bitrix\Calendar\ICal\Builder\AttachCollection;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;
use Bitrix\Calendar\ICal\Builder\Dictionary;
use Bitrix\Calendar\ICal\Parser\ParserPropertyType;
use Bitrix\Calendar\Util;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use COption;
use DateTimeZone;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\UserTable;
use \Bitrix\Disk\AttachedObject;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');

class Helper
{
	public const ICAL_DATETIME_FORMAT_UTC = 'Ymd\THis\Z';
	public const ICAL_DATETIME_FORMAT = 'Ymd\THis';
	public const ICAL_DATETIME_FORMAT_SHORT = 'Ymd\THis';
	public const ICAL_DATE_FORMAT = 'Ymd';
	public const END_OF_TIME = "01.01.2038";

	/**
	 * @param array|null $params
	 * @return string
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getIcalTemplateDate(array $params = null): string
	{
		$from = self::getDateObject($params['DATE_FROM'], false, $params['TZ_FROM']);
		$to = self::getDateObject($params['DATE_TO'], false, $params['TZ_TO']);
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

	/**
	 * @param string|null $date
	 * @param bool $fullDay
	 * @param string $tz
	 * @return Date
	 * @throws \Bitrix\Main\ObjectException
	 */
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

	/**
	 * @param array|null $rrule
	 * @param array|null $params
	 * @return string
	 */
	public static function getIcalTemplateRRule(array $rrule = null, array $params = null): string
	{
		$res = '';
		if ($rrule['BYDAY'] ?? false)
		{
			$rrule['BYDAY'] = \CCalendarEvent::sortByDay($rrule['BYDAY']);
		}
		switch($rrule['FREQ'] ?? null)
		{
			case 'DAILY':
				$res = (int)$rrule['INTERVAL'] === 1
					? Loc::getMessage('EC_RRULE_EVERY_DAY')
					: Loc::getMessage('EC_RRULE_EVERY_DAY_1', ['#DAY#' => $rrule['INTERVAL']])
				;
				break;
			case 'WEEKLY':
				if (!isset($rrule['BYDAY']) || !is_array($rrule['BYDAY']))
				{
					$rrule['BYDAY'] = ['MO'];
				}

				$daysList = implode(', ', array_map(static function($day) {return Loc::getMessage('EC_' . $day);}, $rrule['BYDAY']));
				$res = (int)$rrule['INTERVAL'] === 1
					? Loc::getMessage('EC_RRULE_EVERY_WEEK', ['#DAYS_LIST#' => $daysList])
					: Loc::getMessage('EC_RRULE_EVERY_WEEK_1', ['#WEEK#' => $rrule['INTERVAL'], '#DAYS_LIST#' => $daysList])
				;
				break;
			case 'MONTHLY':
				$res = (int)$rrule['INTERVAL'] === 1
					? Loc::getMessage('EC_RRULE_EVERY_MONTH')
					: Loc::getMessage('EC_RRULE_EVERY_MONTH_1', ['#MONTH#' => $rrule['INTERVAL']])
				;
				break;
			case 'YEARLY':
				$fromTs = \CCalendar::Timestamp($params['DATE_FROM']);
				$res = (int)$rrule['INTERVAL'] === 1
					? Loc::getMessage('EC_RRULE_EVERY_YEAR', [
						'#DAY#' => FormatDate('j', $fromTs),
						'#MONTH#' => FormatDate('n', $fromTs)
					])
					: Loc::getMessage('EC_RRULE_EVERY_YEAR_1', [
						'#YEAR#' => $rrule['INTERVAL'],
						'#DAY#' => FormatDate('j', $fromTs),
						'#MONTH#' => FormatDate('n', $fromTs)
					])
				;
				break;
		}

		if ($rrule['COUNT'] ?? false)
		{
			$res .= ' ' . Loc::getMessage('EC_RRULE_COUNT', ['#COUNT#' => $rrule['COUNT']]);
		}
		elseif (isset($rrule['UNTIL']) && $rrule['UNTIL'] && self::isNotEndOfTime($rrule['UNTIL']))
		{
			$res .= ' ' . Loc::getMessage('EC_RRULE_UNTIL', ['#UNTIL_DATE#' => $rrule['UNTIL']]);
		}

		if (!is_string($res))
		{
			$res = '';
		}

		return $res;
	}

	/**
	 * @return string
	 */
	public static function getUniqId(): string
	{
		return uniqid(self::getServerName(), true);
	}

	/**
	 * @return false|string|null
	 */
	public static function getServerName()
	{
		return COption::getOptionString('main', 'server_name', Application::getInstance()->getContext()->getServer()->getServerName());
	}

	/**
	 * @param string|null $tz
	 * @return DateTimeZone
	 */
	public static function getTimezoneObject(string $tz = null): DateTimeZone
	{
		return !$tz
			? (new \DateTime())->getTimezone()
			: new DateTimeZone(Util::prepareTimezone($tz)->getName());
	}

	/**
	 * @param $userId
	 * @param $uid
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getEventByUId(?string $uid): ?array
	{
		if (is_null($uid))
		{
			return null;
		}

		$event = EventTable::getList([
			'filter' => [
				'=DAV_XML_ID' => $uid,
				'=DELETED' => 'N',
			],
			'limit' => 1,
		])->fetch();

		return (!empty($event) && is_array($event))
			? $event
			: null;
	}

	/**
	 * @param int|null $id
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getUserById(?int $id): ?array
	{
		if ($id === null)
		{
			return null;
		}

		$user = UserTable::getList([
			'filter' => [
				'ID' => $id,
			],
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
				'EMAIL',
			],
			'limit' => 1,
		])->fetch();

		return (!empty($user) && is_array($user))
			? $user
			: null;
	}

	/**
	 * @param array|null $idList
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getIndexUsersByIds(?array $idList): array
	{
		$usersDb = UserTable::getList([
			'filter' => [
				'ID' => $idList,
			],
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
				'EMAIL',
			],
		]);

		$collection = [];
		while ($user = $usersDb->fetch())
		{
			$collection[$user['ID']] = $user;
		}

		return $collection;
	}

	/**
	 * @param $fields
	 * @param $userId
	 * @param $parentId
	 * @param false $isChangeFiles
	 * @return AttachCollection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public static function getMailAttaches($fields, $userId, $parentId, &$isChangeFiles = false): AttachCollection
	{
		//TODO: need refactoring
		global $USER_FIELD_MANAGER;
		$attachCollection = new AttachCollection();
		$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $parentId, LANGUAGE_ID);
		$attachedFilesIds = $UF['UF_WEBDAV_CAL_EVENT']['VALUE'];

		$fields['UF_WEBDAV_CAL_EVENT'] ??= null;
		if (is_array($fields) && is_array($fields['UF_WEBDAV_CAL_EVENT']) && is_array($attachedFilesIds))
		{
			$ufIds = array_unique(array_merge($fields['UF_WEBDAV_CAL_EVENT'], $attachedFilesIds));
		}
		elseif(is_array($fields) && is_array($fields['UF_WEBDAV_CAL_EVENT']))
		{
			$ufIds = $fields['UF_WEBDAV_CAL_EVENT'];
		}
		elseif(is_array($attachedFilesIds))
		{
			$ufIds = $attachedFilesIds;
		}
		else
		{
			return $attachCollection;
		}

		if (!empty($ufIds) && \Bitrix\Main\Loader::includeModule('disk'))
		{
			foreach ($ufIds as $item)
			{
				[$type, $realValue] = \Bitrix\Disk\Uf\FileUserType::detectType($item);

				if ($type == FileUserType::TYPE_ALREADY_ATTACHED)
				{
					$attachedModel = AttachedObject::loadById($realValue);
					if(!$attachedModel
						|| (!empty($fields['UF_WEBDAV_CAL_EVENT'])
							&& $item !== ''
							&& !in_array($item, $fields['UF_WEBDAV_CAL_EVENT'])))
					{
						$isChangeFiles = true;
						continue;
					}
					$file = $attachedModel->getFile();
				}
				elseif ($type == \Bitrix\Disk\Uf\FileUserType::TYPE_NEW_OBJECT)
				{
					$isChangeFiles = true;
					$file = \Bitrix\Disk\File::loadById($realValue, ['STORAGE']);
				}

				if (!$file)
				{
					continue;
				}

				$externalLink = $file->addExternalLink([
					'CREATED_BY' => $userId,
					'TYPE' => \Bitrix\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
				]);
				if (!$externalLink)
				{
					continue;
				}

				$name = $file->getName();
				$size = $file->getSize();
				$link = \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlExternalLink([
						'hash' => $externalLink->getHash(),
						'action' => 'downloadFile',
					],
					true
				);

				$attach = Attach::createInstance($link, $name, $size);

				$attachCollection->add($attach);
			}
		}

		return $attachCollection;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public static function getAttendee(int $userId, int $eventParentId, $isRsvp = true): ?Attendee
	{
		$query = EventTable::query()
			->setSelect([
				'MEETING_STATUS',
				'USER_NAME' => 'USER.NAME',
				'USER_LAST_NAME' => 'USER.LAST_NAME',
				'USER_EMAIL' => 'USER.EMAIL',
			])
			->registerRuntimeField(
				'USER',
				new ReferenceField(
					'USER',
					UserTable::getEntity(),
					Join::on('this.OWNER_ID', 'ref.ID'),
					['join_type' => Join::TYPE_INNER]
				)
			)
			->setFilter(['USER.ID'=>$userId, '=PARENT_ID'=>$eventParentId])
		;

		$attendee = $query->fetch();

		if (is_null($attendee))
		{
			return null;
		}

		return Attendee::createInstance(
			$attendee['USER_EMAIL'],
			$attendee['USER_NAME'],
			$attendee['USER_LAST_NAME'],
			Dictionary::ATTENDEE_STATUS[$attendee['MEETING_STATUS']],
			Dictionary::ATTENDEE_ROLE['REQ_PARTICIPANT'],
			Dictionary::ATTENDEE_CUTYPE['individual'],
			$attendee['USER_EMAIL'],
			$isRsvp
		);
	}

	/**
	 * @param int $parentId
	 * @return AttendeesCollection
	 */
	public static function getAttendeesByEventParentId(int $parentId): AttendeesCollection
	{
		$attendeesCollection = AttendeesCollection::createInstance();

		$query = EventTable::query()
			->setSelect([
				'MEETING_STATUS',
				'USER_NAME' => 'USER.NAME',
				'USER_LAST_NAME' => 'USER.LAST_NAME',
				'USER_EMAIL' => 'USER.EMAIL'
			])
			->where('PARENT_ID', $parentId)
			->registerRuntimeField(
				'USER',
				new ReferenceField(
					'USER',
					UserTable::getEntity(),
					Join::on('this.OWNER_ID', 'ref.ID'),
					['join_type' => Join::TYPE_INNER]
				)
			)
			->exec()
		;

		while ($attendee = $query->fetch())
		{
			$attendeesCollection->add(Attendee::createInstance(
				$attendee['USER_EMAIL'],
				$attendee['USER_NAME'],
				$attendee['USER_LAST_NAME'],
				Dictionary::ATTENDEE_STATUS[$attendee['MEETING_STATUS']],
				Dictionary::ATTENDEE_ROLE['REQ_PARTICIPANT'],
				Dictionary::ATTENDEE_CUTYPE['individual'],
				$attendee['USER_EMAIL']
			));
		}

		return $attendeesCollection;
	}

	/**
	 * @param string|null $dateTime
	 * @param string|null $tz
	 * @return DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getIcalDateTime(string $dateTime = null, string $tz = null): DateTime
	{
		$format = $tz === 'UTC' ? self::ICAL_DATETIME_FORMAT_UTC : self::ICAL_DATETIME_FORMAT;

		return new DateTime($dateTime, $format, Util::prepareTimezone($tz));
	}

	/**
	 * @param string|null $date
	 * @return Date
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getIcalDate(string $date = null): Date
	{
		return new Date($date, self::ICAL_DATE_FORMAT);
	}

	/**
	 * @param array $userInfo
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getUserIdByEmail(array $userInfo): ?int
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
			return (int)$user['ID'];
		}

		return self::getExternalUserByEmail($userInfo, $errorCollection);
	}

	/**
	* Returns id of the 'external' user by email. Creates user if it not exists.
	*
	* @param array $params - incoming params:
	* $params['EMAIL'] - (required) email of the user
	* $params['NAME'] - user's name
	* $params['LAST_NAME'] - user's last name

	* @return int|null
	*/
	public static function getExternalUserByEmail($params, &$errorText): ?int
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
				"!EXTERNAL_AUTH_ID" => \Bitrix\Main\UserTable::getExternalUserTypes(),
			],
			[
				"FIELDS" => [ "ID", "EXTERNAL_AUTH_ID", "ACTIVE" ]
			]
		);

		while (($emailUser = $res->fetch()) && !$userId)
		{
			if (
				(int)$emailUser["ID"] > 0
				&& (
					$emailUser["ACTIVE"] === "Y"
					|| $emailUser["EXTERNAL_AUTH_ID"] === "email"
				)
			)
			{
				if ($emailUser["ACTIVE"] === "N") // email only
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
				'NAME' => $params["NAME"] ?? '',
				'LAST_NAME' => $params["LAST_NAME"] ?? ''
			];

			// create "external" user by email
			$user = \Bitrix\Mail\User::create($userFields);
			$errorMessage = false;
			if (is_object($user) && $user->LAST_ERROR !== '')
			{
				$errorMessage = $user->LAST_ERROR;
			}

			if (!$errorMessage && (int)$user > 0)
			{
				$userId = (int)$user;
			}
			else
			{
				$errorText = $errorMessage;
			}
		}

		if (!is_object($user) && (int)$userId > 0)
		{
			\Bitrix\Main\UI\Selector\Entities::save([
				'context' => Util::getUserSelectorContext(),
				'code' => 'U'.$userId
			]);
		}

		return $userId;
	}

	/**
	 * @param int|null $eventId
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getEventDescriptionById(?int $eventId): ?string
	{
		if (!$eventId)
		{
			return null;
		}

		$event = EventTable::getList([
			'filter' => ['=ID' => $eventId,],
			'select' => ['DESCRIPTION'],
			'limit' => 1,
		])->fetch();

		return is_array($event)
			? $event['DESCRIPTION']
			: null
			;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function getEventById(?int $eventId): ?array
	{
		if (!$eventId)
		{
			return null;
		}

		$event = EventTable::query()
			->setSelect([
				'DATE_FROM',
				'DATE_TO',
				'DATE_CREATE',
				'DT_SKIP_TIME',
				'TZ_FROM',
				'TZ_TO',
				'NAME',
				'DESCRIPTION',
				'COLOR',
				'ACCESSIBILITY',
				'IMPORTANCE',
				'PRIVATE_EVENT',
				'RRULE',
				'LOCATION',
				'REMIND',
				'SECTION_ID',
				'IS_MEETING',
				'MEETING_HOST',
				'MEETING',
				'CAL_TYPE',
				'OWNER_ID',
				'VERSION',
				'PARENT_ID',
				'TIMESTAMP_X',
				'LOCATION',
				'MEETING_STATUS',
				'DAV_XML_ID',
				'ID',
				'ACTIVE',
				'RELATIONS',
				'ATTENDEES_CODES',
			])
			->setFilter(['=ID' => $eventId])
			->setLimit(1)
			->exec()->fetch()
		;

		if ($event)
		{
			return $event;
		}
		
		return null;
	}

	/**
	 * @param string|null $dateTime
	 * @param string $tz
	 * @return DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getIcalDateTimeShort(string $dateTime = null, string $tz = 'UTC'): DateTime
	{
		return new DateTime($dateTime, self::ICAL_DATETIME_FORMAT_SHORT, Util::prepareTimezone($tz));
	}

	/**
	 * @param Date|null $date
	 * @return string
	 */
	public static function getShortMonthName(?Date $date): string
	{
		if ($date === null)
		{
			return \date('M');
		}

		$month = Util::checkRuZone()
			? mb_strtoupper(FormatDate('M', $date->getTimestamp()))
			: mb_strtoupper($date->format('M'))
		;

		return is_string($month)
			? $month
			: $date->format('M')
		;
	}

	/**
	 * @param $until
	 * @return bool
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function isNotEndOfTime($until): bool
	{
		return Util::getDateObject($until)->getTimestamp() !== Util::getDateObject(self::END_OF_TIME)->getTimestamp();
	}

	/**
	 * @param ParserPropertyType|null $date
	 * @return Date|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getDateByParserProperty(?ParserPropertyType $date): ?Date
	{
		if ($date !== null)
		{
			return $date->getParameterValueByName('tzid') !== null
				? self::getIcalDateTime($date->getValue(), $date->getParameterValueByName('tzid'))
				: self::getIcalDate($date->getValue())
			;
		}

		return null;
	}
}
