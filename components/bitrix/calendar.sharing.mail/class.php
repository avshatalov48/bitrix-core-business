<?php

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sharing;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * mail template for
 * 'EVENT_NAME' => 'CALENDAR_SHARING'
 *
 * @params
 * STATUS
 * LOC_MEETING_STATUS
 * EVENT_NAME
 * DATE_FROM
 * DATE_TO
 * IS_FULL_DAY
 * TZ_FROM
 * TZ_TO
 * RRULE
 * AVATARS
 * DETAIL_LINK
 * ICS_LINK
 * BITRIX24_LINK
 *
 * Sharing only params:
 * WHO_CANCELLED
 * WHEN_CANCELLED
 * VIDEOCONFERENCE_LINK
 * ABUSE_LINK
 * CALENDAR_LINK
 *
 * Event only params:
 * OWNER_STATUS
 * DECISION_YES_LINK
 * DECISION_NO_LINK
 */
class CalendarSharingMailComponent extends CBitrixComponent
{
	protected const MEETING_STATUS_CREATED = 'created';
	protected const MEETING_STATUS_CANCELLED = 'cancelled';

	protected const MEETING_STATUS_EVENT = 'event';
	protected const MEETING_STATUS_EVENT_CANCELLED = 'event_cancelled';
	protected const EVENT_STATUSES = [
		self::MEETING_STATUS_EVENT,
		self::MEETING_STATUS_EVENT_CANCELLED,
	];

	public function executeComponent()
	{
		Sharing\Helper::setSiteLanguage();

		$this->prepareButtons();
		$this->prepareDateParams();
		$this->prepareStatusParams();
		$this->prepareAvatars();
		$this->prepareImages();

		$this->includeComponentTemplate();
	}

	protected function prepareButtons(): void
	{
		$status = $this->arParams['STATUS'];
		$isEvent = $status === self::MEETING_STATUS_EVENT;
		$isCreated = $status === self::MEETING_STATUS_CREATED;
		$isCancelled = $status === self::MEETING_STATUS_CANCELLED;
		$this->arResult['IS_EVENT_STATUS'] = in_array($status, self::EVENT_STATUSES, true);

		$ownerStatus = $this->arParams['OWNER_STATUS'];

		$this->arResult['SHOW_DETAIL_BUTTON'] = !empty($this->arParams['DETAIL_LINK']) && $status !== self::MEETING_STATUS_EVENT_CANCELLED;
		$this->arResult['SHOW_CANCEL_LINK'] = !empty($this->arParams['CANCEL_LINK']);
		$this->arResult['SHOW_WHEN_CANCELLED'] = !empty($this->arParams['WHEN_CANCELLED']) && $isCancelled;
		$this->arResult['SHOW_CALENDAR_BUTTON'] = !empty($this->arParams['CALENDAR_LINK']) && $isCancelled;
		$this->arResult['SHOW_VIDEOCONFERENCE_BUTTON'] = !empty($this->arParams['VIDEOCONFERENCE_LINK']) && $isCreated;
		$this->arResult['SHOW_ICS_BUTTON'] = !empty($this->arParams['ICS_LINK']) && ($isCreated || ($isEvent && $ownerStatus === 'Y'));
		$this->arResult['SHOW_ACCEPT_BUTTON'] = !empty($this->arParams['DECISION_YES_LINK']) && $isEvent && in_array($ownerStatus, ['Q', 'N'], true);
		$this->arResult['SHOW_DECLINE_LINK'] = !empty($this->arParams['DECISION_NO_LINK']) && $isEvent && $ownerStatus === 'Y';
		$this->arResult['SHOW_DECLINE_BUTTON'] = !empty($this->arParams['DECISION_NO_LINK']) && $isEvent && $ownerStatus === 'Q';
	}

	protected function prepareDateParams(): void
	{
		$dateFrom = $this->arParams['DATE_FROM'];
		$dateTo = $this->arParams['DATE_TO'];
		$timezone = Core\Base\DateTimeZone::createByString($this->arParams['TZ_FROM']);
		$isFullDay = $this->arParams['IS_FULL_DAY'];

		$this->arParams['CALENDAR_MONTH_NAME'] = $this->formatMonthName($dateFrom);
		$this->arParams['CALENDAR_DAY'] = $this->formatCalendarDay($dateFrom);

		$isSameDate = $this->formatDate($dateFrom) === $this->formatDate($dateTo);
		if ($isFullDay)
		{
			$this->arParams['CALENDAR_TIME'] = $this->formatWeekDay($dateFrom);
			$this->arParams['EVENT_DATE'] = $this->formatDate($dateFrom) . ' - ' . $this->formatDate($dateTo);
			$this->arParams['EVENT_TIME'] = Loc::getMessage('CALENDAR_SHARING_MAIL_FULL_DAY');
			$this->arParams['TIMEZONE'] = '';

			if ($isSameDate)
			{
				$this->arParams['EVENT_DATE'] = $this->formatWeekDate($dateFrom, '');
			}
		}
		else
		{
			$this->arParams['CALENDAR_TIME'] = $this->formatTime($dateFrom);
			$this->arParams['EVENT_DATE'] = $this->formatWeekDate($dateFrom);
			$this->arParams['EVENT_TIME'] = $this->formatTimeInterval($dateFrom, $dateTo);
			$this->arParams['TIMEZONE'] = Sharing\Helper::formatTimezone($timezone);

			if (!$isSameDate)
			{
				$this->arParams['EVENT_DATE'] = Loc::getMessage('CALENDAR_SHARING_MAIL_FROM_DATE', [
					'#DATE#' => $this->formatDateTime($dateFrom),
				]);
				$this->arParams['EVENT_TIME'] = Loc::getMessage('CALENDAR_SHARING_MAIL_UNTIL_DATE', [
					'#DATE#' => $this->formatDateTime($dateTo),
				]);
			}
		}
	}

	protected function prepareStatusParams(): void
	{
		$status = $this->arParams['STATUS'];

		$this->arParams['ICON'] = $this->getImagesPath() . 'calendar-sharing-email-icon-success.png';

		if ($status === self::MEETING_STATUS_CREATED)
		{
			$this->arParams['LOC_MEETING_STATUS'] = Loc::getMessage('CALENDAR_SHARING_MAIL_TITLE_CREATED');
		}

		if ($status === self::MEETING_STATUS_CANCELLED)
		{
			$this->arParams['LOC_MEETING_STATUS'] = Loc::getMessage('CALENDAR_SHARING_MAIL_TITLE_CANCELLED');
		}

		if (
			$status === self::MEETING_STATUS_CANCELLED
			|| $status === self::MEETING_STATUS_EVENT_CANCELLED
			|| ($status === self::MEETING_STATUS_EVENT && $this->arParams['OWNER_STATUS'] === 'N')
		)
		{
			$this->arParams['ICON'] = $this->getImagesPath() . 'calendar-sharing-email-icon-decline.png';
		}
	}

	protected function prepareImages(): void
	{
		$this->arParams['MAIL_BG_GRAY'] = $this->getImagesPath() . 'calendar-sharing-email-bg-gray.jpg';
		$this->arParams['ICON_BUTTON_CANCEL'] = $this->getImagesPath() . 'calendar-sharing-email-icon-button-cancel-x2.png';
		$this->arParams['ICON_BUTTON_DECLINE'] = $this->getImagesPath() . 'calendar-sharing-email-icon-button-decline.png';
		$this->arParams['ICON_DOTS'] = $this->getImagesPath() . 'calendar-sharing-email-dots.png';
		$this->arParams['ICON_RRULE'] = $this->getImagesPath() . 'calendar-sharing-email-rrule.png';
	}

	protected function prepareAvatars(): void
	{
		if (empty($this->arParams['AVATARS']))
		{
			return;
		}

		$maxAvatarsCount = 4;
		$avatars = explode(',', $this->arParams['AVATARS']);
		if (count($avatars) > $maxAvatarsCount)
		{
			$avatars = array_slice($avatars, 0, $maxAvatarsCount - 1);
			$this->arParams['SHOW_DOTS'] = true;
		}

		$this->arParams['AVATARS'] = array_map([$this, 'prepareAvatar'], $avatars);
	}

	protected function prepareAvatar(string $avatar): string
	{
		if (trim($avatar) === '' || trim($avatar) === '/bitrix/images/1.gif')
		{
			$avatar = $this->getImagesPath() . 'ui-user.png';
		}

		return $avatar;
	}

	protected function getImagesPath(): string
	{
		return $this->getPath() . '/templates/.default/images/';
	}

	protected function formatDate(string $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->getDayMonthFormat());

		return FormatDate($dayMonthFormat, \CCalendar::Timestamp($date));
	}

	protected function formatWeekDay(string $date): string
	{
		return FormatDate('D', \CCalendar::Timestamp($date));
	}

	protected function formatWeekDate(string $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$weekMonthFormat = Main\Type\Date::convertFormatToPhp($culture->getDayOfWeekMonthFormat());

		return FormatDate($weekMonthFormat, \CCalendar::Timestamp($date));
	}

	protected function formatDateTime(string $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->getDayMonthFormat());
		$shortTimeFormat = $culture->getShortTimeFormat();
		$format = "$dayMonthFormat $shortTimeFormat";

		return FormatDate($format, \CCalendar::Timestamp($date));
	}

	protected function formatTimeInterval(string $dateFrom, string $dateTo): string
	{
		$fromFormatted = $this->formatTime($dateFrom);
		$toFormatted = $this->formatTime($dateTo);

		return "$fromFormatted - $toFormatted";
	}

	protected function formatTime(string $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$shortTimeFormat = $culture->getShortTimeFormat();

		return FormatDate($shortTimeFormat, \CCalendar::Timestamp($date));
	}

	protected function formatMonthName(string $date): string
	{
		return FormatDate('f', \CCalendar::Timestamp($date));
	}

	protected function formatCalendarDay(string $date): string
	{
		$day = FormatDate('j', \CCalendar::Timestamp($date));

		return str_pad($day, 2, '0', STR_PAD_LEFT);
	}
}
