<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use CFile;
use CUser;
use Bitrix\Main\Context;

class Helper
{
	private const PHONE_FEATURE_ENABLED = true;
	private const IS_SHARING_ENABLED_OPTION_NAME = 'isSharingEnabled';

	public const ACTION = 'action';
	public const ICS = 'ics';
	public const CANCEL = 'cancel';
	public const CONFERENCE = 'videoconference';
	public const ACTION_ICS = '?'.self::ACTION.'='.self::ICS;
	public const ACTION_CANCEL = '?'.self::ACTION.'='.self::CANCEL;
	public const ACTION_CONFERENCE = '?'.self::ACTION.'='.self::CONFERENCE;

	public static function isSharingFeatureEnabled(): bool
	{
		return Option::get('calendar', self::IS_SHARING_ENABLED_OPTION_NAME, 'N', '-') === 'Y';
	}

	public static function isPhoneFeatureEnabled(): bool
	{
		//TODO add REAL check
		return self::PHONE_FEATURE_ENABLED;
	}

	public static function getShortUrl(string $url): string
	{
		return \CCalendar::GetServerPath() . \CBXShortUri::getShortUri($url);
	}

	public static function getOwnerInfo(int $id): array
	{
		$user = CUser::GetByID($id)->Fetch();
		$arFileTmp = CFile::ResizeImageGet(
			$user["PERSONAL_PHOTO"],
			array('width' => 100, 'height' => 100),
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true,
		);

		return [
			'id' => $user['ID'],
			'name' => $user['NAME'],
			'lastName' => $user['LAST_NAME'],
			'photo' => $arFileTmp['src'] ?? null,
		];
	}

	public static function getStatusLoc(string $statusLetter): string
	{
		$key = 'CALENDAR_SHARING_EVENT_ATTENDEE_STATUS_' . strtoupper($statusLetter);

		return Loc::getMessage($key);
	}

	public static function formatTimeInterval(Date $from, Date $to, bool $isFullDay): string
	{
		$isLongDateTimeFormat = false;

		$culture = Context::getCurrent()->getCulture();
		$formattedDateFrom = FormatDate($culture->getFullDateFormat(), self::getUserDateTimestamp($from));
		$formattedDateTo = '';
		$formattedTimeFrom = '';
		$formattedTimeTo = '';

		if ($to->format('j') !== $from->format('j')
			|| $to->format('Y') !== $from->format('Y')
			|| $to->format('n') !== $from->format('n')
		)
		{
			$isLongDateTimeFormat = true;
			$formattedDateTo = FormatDate($culture->getFullDateFormat(), self::getUserDateTimestamp($to));
		}

		if ($isFullDay)
		{
			if (!isset($formattedDateTo))
			{
				$formattedDateTo = FormatDate($culture->getFullDateFormat(), self::getUserDateTimestamp($to));
			}
		}
		else
		{
			$formattedTimeFrom = FormatDate($culture->getShortTimeFormat(), self::getUserDateTimestamp($from));
			$formattedTimeTo = FormatDate($culture->getShortTimeFormat(), self::getUserDateTimestamp($to));
		}

		if ($isFullDay)
		{
			if ($isLongDateTimeFormat)
			{
				return $formattedDateFrom . " - " . $formattedDateTo;
			}

			return $formattedDateFrom . Loc::getMessage('EC_VIEW_FULL_DAY');
		}

		if ($isLongDateTimeFormat)
		{
			return $formattedDateFrom . ' ' . $formattedTimeFrom . ' - ' . $formattedDateTo . ' ' . $formattedTimeTo;
		}

		return $formattedDateFrom . ' ' . $formattedTimeFrom . ' - ' . $formattedTimeTo;
	}

	public static function formatTimezone(DateTimeZone $timezone): string
	{
		$utcOffset = "UTC";
		if ($timezone->getTimeZone()->getOffset(new \DateTime('now')) !== 0)
		{
			$time = new \DateTime('now', $timezone->getTimeZone());
			$utcOffset .= " " . $time->format('P');
		}

		return "($utcOffset) " . $timezone->toString();
	}

	protected static function getUserDateTimestamp(Date $date): int
	{
		$dateTimezone = new \DateTimeZone($date->getFields()['timezone']);
		$serverTimezone = (new \DateTime())->getTimezone();

		$dateOffset = $dateTimezone->getOffset(new \DateTime());
		$serverOffset = $serverTimezone->getOffset(new \DateTime());
		$offset = - $serverOffset + $dateOffset;

		$userDate = clone $date;
		$userDate->setTime($date->getHour(), $date->getMinutes(), $date->getSeconds() + $offset);

		return $userDate->getTimestamp();
	}

	public static function getEventTimestampUTC(DateTime $date, ?string $eventTimezone = null): int
	{
		$dateTimezone = $date->getTimeZone()->getName();
		$dateTimestampUTC = $date->getTimestamp() + \CCalendar::GetTimezoneOffset($dateTimezone);
		$eventOffsetUTC = \CCalendar::GetTimezoneOffset($eventTimezone);

		return $dateTimestampUTC - $eventOffsetUTC;
	}

}