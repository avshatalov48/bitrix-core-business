<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Bitrix24\Form\AbuseZoneMap;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Form\FeedbackForm;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use CFile;
use CUser;
use Bitrix\Main\Context;
use CUserOptions;
use Bitrix\Calendar\Sharing;

class Helper
{
	private const PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME = 'payAttentionToNewSharingFeature';
	private const PAY_ATTENTION_TO_NEW_FEATURE_FIRST = 'first-feature';
	private const PAY_ATTENTION_TO_NEW_FEATURE_NEW = 'new-feature';
	private const PAY_ATTENTION_TO_NEW_FEATURE_REMIND = 'remind-feature';
	private const WEEK_TIMESTAMP = 604800; // 86400 * 7

	public const ACTION = 'action';
	public const ICS = 'ics';
	public const CANCEL = 'cancel';
	public const CONFERENCE = 'videoconference';
	public const OWNER_CREATED = 'ownerCreated';
	public const ACTION_ICS = '?'.self::ACTION.'='.self::ICS;
	public const ACTION_CANCEL = '?'.self::ACTION.'='.self::CANCEL;
	public const ACTION_CONFERENCE = '?'.self::ACTION.'='.self::CONFERENCE;

	protected const ABUSE_SENDER_PAGE = 'page';
	protected const ABUSE_SENDER_EMAIL = 'email';

	/**
	 * returns true if user didn't view sharing tour in calendar, false otherwise
	 * @return ?string
	 */
	public static function payAttentionToNewSharingFeature(): ?string
	{
		$defaultValue = 'unset';
		$optionValue = CUserOptions::getOption(
			"calendar",
			self::PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME,
			$defaultValue
		);

		if ($optionValue === $defaultValue && (new Sharing\Sharing(\CCalendar::GetUserId()))->isEnabled())
		{
			CUserOptions::setOption(
				"calendar",
				self::PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME,
				'Y'
			);
			$optionValue = 'Y';
		}

		if ($optionValue === $defaultValue)
		{
			return self::PAY_ATTENTION_TO_NEW_FEATURE_FIRST;
		}

		if ($optionValue === 'Y')
		{
			return self::PAY_ATTENTION_TO_NEW_FEATURE_NEW;
		}

		if (!is_string($optionValue) || $optionValue === 'N')
		{
			return null;
		}

		$values = explode(',', $optionValue);
		$valuesCount = count($values);
		if ($valuesCount >= 4)
		{
			return null;
		}

		$now = time();
		$lastValue = (int)$values[$valuesCount - 1];
		if ($lastValue && $now > $lastValue + self::WEEK_TIMESTAMP)
		{
			if (self::hasSharingEvent())
			{
				return self::PAY_ATTENTION_TO_NEW_FEATURE_REMIND;
			}

			return self::PAY_ATTENTION_TO_NEW_FEATURE_NEW;
		}

		return null;
	}

	/**
	 * disabling sharing tour in calendar for user
	 * @return void
	 */
	public static function disableOptionPayAttentionToNewSharingFeature(): void
	{
		$defaultValue = 'unset';
		$optionValue = CUserOptions::getOption(
			"calendar",
			self::PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME,
			$defaultValue
		);

		if ($optionValue === $defaultValue)
		{
			CUserOptions::setOption(
				"calendar",
				self::PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME,
				'Y'
			);
			return;
		}

		$timestamps = [];
		if ($optionValue !== 'Y')
		{
			$timestamps = explode(',', $optionValue);
		}
		$timestamps[] = time();

		if (count($timestamps) >= 4)
		{
			$optionResult = 'N';
		}
		else
		{
			$optionResult = implode(',', $timestamps);
		}

		CUserOptions::setOption(
			"calendar",
			self::PAY_ATTENTION_TO_NEW_SHARING_FEATURE_OPTION_NAME,
			$optionResult
		);
	}

	private static function hasSharingEvent(): bool
	{
		$result = false;
		$userId = \CCalendar::GetUserId();

		if ($userId)
		{
			$queryResult = EventTable::query()
				->setSelect(['ID'])
				->where('EVENT_TYPE', Dictionary::EVENT_TYPE['shared'])
				->where('OWNER_ID', $userId)
				->setLimit(1)
			;

			if ($queryResult->fetch())
			{
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * returns true if we can send calendar sharing messages using phone number, false otherwise
	 * @return bool
	 */
	public static function isPhoneFeatureEnabled(): bool
	{
		return false;
	}

	/**
	 * returns true if we can send calendar sharing messages using email, false otherwise
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function isMailFeatureEnabled(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::isEmailConfirmed();
		}

		return true;
	}

	/**
	 * generates short url
	 * @param string $url
	 * @return string
	 */
	public static function getShortUrl(string $url): string
	{
		return \CCalendar::GetServerPath() . \CBXShortUri::getShortUri($url);
	}

	/**
	 * @param string $name
	 * @param string $lastName
	 * @return string
	 */
	public static function getPersonFullNameLoc(string $name, string $lastName): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$nameFormat = is_null($culture) ? '#NAME# #LAST_NAME#' : $culture->getNameFormat();

		return trim(str_replace(
			['#NAME#', '#LAST_NAME#'],
			[$name, $lastName],
			$nameFormat,
		));
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public static function getOwnerInfo(int $id): array
	{
		$user = CUser::GetByID($id)->Fetch();
		$arFileTmp = CFile::ResizeImageGet(
			$user["PERSONAL_PHOTO"],
			array('width' => 512, 'height' => 512),
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
			'gender' => $user['PERSONAL_GENDER'] ?? null,
		];
	}

	/**
	 * examples:
	 * DAY_MONTH_FORMAT "December 31"
	 * SHORT_TIME_FORMAT "2:05 pm"
	 * @param Date $date
	 * @return string
	 */
	public static function formatDate(Date $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->get('DAY_MONTH_FORMAT'));
		$timeFormat = $culture->get('SHORT_TIME_FORMAT');
		$weekDayFormat = 'l';

		$timestampUTCWithServerOffset = self::getUserDateTimestamp($date);
		$dayMonth = FormatDate($dayMonthFormat, $timestampUTCWithServerOffset);
		$time = FormatDate($timeFormat, $timestampUTCWithServerOffset);
		$weekDay = mb_strtolower(FormatDate($weekDayFormat, $timestampUTCWithServerOffset));

		return "$dayMonth $time, $weekDay";
	}

	/**
	 * example:
	 * FORMAT_DATE "12/31/2019"
	 * @param Date $date
	 * @return string
	 */
	public static function formatDateShort(Date $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$formatShort = Main\Type\Date::convertFormatToPhp($culture->get('FORMAT_DATE'));

		return FormatDate($formatShort, $date->getTimestamp());
	}

	/**
	 * @param Date $from
	 * @param Date $to
	 * @param bool $isFullDay
	 * @return string
	 */
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

	/**
	 * @param DateTimeZone $timezone
	 * @return string
	 * @throws \Exception
	 */
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

	/**
	 * @param Date $date
	 * @return int
	 */
	public static function getUserDateTimestamp(Date $date): int
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

	/**
	 * @param DateTime $date
	 * @param string|null $eventTimezone
	 * @return int
	 */
	public static function getEventTimestampUTC(DateTime $date, ?string $eventTimezone = null): int
	{
		$dateTimezone = $date->getTimeZone()->getName();
		$dateTimestampUTC = $date->getTimestamp() + \CCalendar::GetTimezoneOffset($dateTimezone);
		$eventOffsetUTC = \CCalendar::GetTimezoneOffset($eventTimezone);

		return $dateTimestampUTC - $eventOffsetUTC;
	}

	/**
	 * returns the expiration date of sharing link according to the date of need and the type of link
	 * @param DateTime $dateTime
	 * @param string $linkType
	 * @return DateTime|null
	 */
	public static function createSharingLinkExpireDate(DateTime $dateTime, string $linkType): ?DateTime
	{
		$result = null;
		if (key_exists($linkType, Sharing\Link\Helper::LIFETIME_AFTER_NEED))
		{
			$result = $dateTime->add(Sharing\Link\Helper::LIFETIME_AFTER_NEED[$linkType]);
		}

		return $result;
	}

	public static function getPageAbuseLink(int $ownerId, string $calendarLink): ?string
	{
		return self::getAbuseLink($ownerId, $calendarLink, self::ABUSE_SENDER_PAGE);
	}

	public static function getEmailAbuseLink(int $ownerId, string $calendarLink): ?string
	{
		return self::getAbuseLink($ownerId, $calendarLink, self::ABUSE_SENDER_EMAIL);
	}

	private static function getAbuseLink(int $ownerId, string $calendarLink, string $senderPage): ?string
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return null;
		}

		$owner = self::getOwnerInfo($ownerId);

		$feedbackForm = new FeedbackForm('calendar_sharing_abuse');
		$presets = $feedbackForm->getPresets();
		$formParams = [
			'hostname' => $presets['hostname'],
			'b24_plan' => $presets['b24_plan'],
			'sender_page' => $senderPage,
			'admin_data' => \COption::GetOptionString('main', 'email_from', ''),
			'user_data' => "id: {$owner['id']}, name: {$owner['name']} {$owner['lastName']}",
			'calendar_link' => $calendarLink,
		];

		$formParamsQuery = http_build_query($formParams);

		$region = Main\Application::getInstance()->getLicense()->getRegion();
		return AbuseZoneMap::getLink($region) . "?$formParamsQuery";
	}

	public static function getBitrix24Link(): ?string
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return null;
		}

		$region = Main\Application::getInstance()->getLicense()->getRegion();
		$abuseLink = AbuseZoneMap::getLink($region);

		$parsedUrl = parse_url($abuseLink);
		$protocol = $parsedUrl['scheme'];
		$host = $parsedUrl['host'];
		$parsedUri = new Uri($protocol . '://' . $host);

		return rtrim($parsedUri->getLocator(), '/');
	}

	public static function setSiteLanguage(): void
	{
		$siteDb = Main\SiteTable::getById(SITE_ID);
		if ($site = $siteDb->fetchObject())
		{
			Loc::setCurrentLang($site->getLanguageId());
		}
	}
}