<?php
namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Core\Base\Date;
use CCalendar;

class Mail extends Service
{
	public const MEETING_STATUS_CREATED = 'created';
	public const MEETING_STATUS_CANCELLED = 'cancelled';

	/**
	 * @param string $to
	 * @return bool
	 */
	public function notifyAboutMeetingStatus(string $to): bool
	{
		if ($this->doSendMeetingCreated())
		{
			return $this->notifyAboutMeetingCreated($to);
		}

		if ($this->doSendMeetingCancelled())
		{
			return $this->notifyAboutMeetingCancelled($to);
		}

		return false;
	}

	protected function doSendMeetingCreated(): bool
	{
		$ownerStatus = $this->getOwner()['STATUS'];
		return $ownerStatus === 'Y' && !$this->event->isDeleted();
	}

	protected function doSendMeetingCancelled(): bool
	{
		$ownerStatus = $this->getOwner()['STATUS'];
		return $ownerStatus === 'N';
	}

	public function notifyAboutMeetingCreated(string $to): bool
	{
		Sharing\Helper::setSiteLanguage();

		$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CREATED');
		$ownerName = $this->getOwner()['NAME'];
		$mailParams = $this->getBaseMailParams($ownerName);
		$arParams = [
			'STATUS' => self::MEETING_STATUS_CREATED,
			'CANCEL_LINK' => $this->eventLink->getUrl() . Sharing\Helper::ACTION_CANCEL,
			'ICS_LINK' => $this->eventLink->getUrl() . Sharing\Helper::ACTION_ICS,
		];
		$arParams = array_merge($arParams, $mailParams);

		$files = null;
		if ($icsFileId = $this->getIcsFileId($ownerName, $arParams['EVENT_NAME']))
		{
			$files = [$icsFileId];
		}

		return $this->sendMessage($to, $arParams, $subject, $files);
	}

	public function notifyAboutMeetingCancelled(string $to): bool
	{
		Sharing\Helper::setSiteLanguage();

		$ownerName = $this->getOwner()['NAME'];

		$gender = $this->getOwner()['GENDER'];
		$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED', ['#NAME#' => $ownerName]);
		if ($gender === 'M')
		{
			$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED_M', ['#NAME#' => $ownerName]);
		}
		if ($gender === 'F')
		{
			$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED_F', ['#NAME#' => $ownerName]);
		}

		$mailParams = $this->getBaseMailParams($ownerName);

		$arParams = [
			'STATUS' => self::MEETING_STATUS_CANCELLED,
			'CALENDAR_LINK' => $this->getCalendarLink(),
			'WHO_CANCELLED' => $ownerName,
			'WHEN_CANCELLED' => $this->getWhenCancelled(),
		];
		$arParams = array_merge($arParams, $mailParams);

		return $this->sendMessage($to, $arParams, $subject);
	}

	protected function sendMessage(string $to, array $arParams, string $subject, ?array $files = null): bool
	{
		$from = '';
		if (CCalendar::IsBitrix24())
		{
			$from = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_BITRIX24_FROM');
		}

		$cFields = [
			'EMAIL_FROM' => $from,
			'EMAIL_TO' => $to,
			'SUBJECT' => $subject,
		];
		$cFields = array_merge($cFields, $arParams);

		$mailEvent = [
			'EVENT_NAME' => 'CALENDAR_SHARING',
			'C_FIELDS' => $cFields,
			'LID' => SITE_ID,
			'DUPLICATE' => 'Y',
			'DATE_INSERT' => (new Main\Type\DateTime())->format('Y-m-d H:i:s'),
			'FILE' => $files,
		];

		return Main\Mail\Event::sendImmediate($mailEvent) === 'Y';
	}

	protected function getBaseMailParams(string $ownerName): array
	{
		return [
			'EVENT_NAME' => Sharing\SharingEventManager::getSharingEventNameByUserName($ownerName),
			'EVENT_DATE' => $this->getFormattedEventDate(),
			'EVENT_TIME' => $this->getFormattedEventTimeInterval(),
			'TIMEZONE' => Sharing\Helper::formatTimezone($this->event->getStartTimeZone()),
			'CALENDAR_MONTH_NAME' => $this->getCalendarMonthName(),
			'CALENDAR_DAY' => $this->getCalendarDay(),
			'CALENDAR_TIME' => $this->getCalendarTime(),
			'ABUSE_LINK' => $this->getAbuseLink(),
			'BITRIX24_LINK' => $this->getBitrix24Link(),
		];
	}

	protected function getFormattedEventDate(): string
	{
		$timestampUTCWithServerOffset = Sharing\Helper::getUserDateTimestamp($this->event->getStart());
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->get('DAY_MONTH_FORMAT'));
		$weekDayFormat = 'l';

		$dayMonth = FormatDate($dayMonthFormat, $timestampUTCWithServerOffset);
		$weekDay = mb_strtolower(FormatDate($weekDayFormat, $timestampUTCWithServerOffset));

		return "$dayMonth, $weekDay";
	}

	protected function getFormattedEventTimeInterval(): string
	{
		$timeStart = $this->formatTime($this->event->getStart());
		$timeEnd = $this->formatTime($this->event->getEnd());
		return "$timeStart - $timeEnd";
	}

	/**
	 * @return string
	 */
	protected function getCalendarMonthName(): string
	{
		$timestampUTCWithServerOffset = Sharing\Helper::getUserDateTimestamp($this->event->getStart());
		return FormatDate('f', $timestampUTCWithServerOffset);
	}

	/**
	 * @return string
	 */
	protected function getCalendarDay(): string
	{
		return $this->event->getStart()->format('j');
	}

	protected function getCalendarTime(): string
	{
		return $this->formatTime($this->event->getStart());
	}

	protected function getWhenCancelled(): string
	{
		$timestamp = $this->eventLink->getCanceledTimestamp();

		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$dayMonthFormat = Main\Type\Date::convertFormatToPhp($culture->get('DAY_MONTH_FORMAT'));
		$timeFormat = $culture->get('SHORT_TIME_FORMAT');
		$formatLong = "$dayMonthFormat $timeFormat";

		return FormatDate($formatLong, $timestamp);
	}

	protected function formatTime(Date $date): string
	{
		$culture = Main\Application::getInstance()->getContext()->getCulture();
		$timeFormat = $culture->get('SHORT_TIME_FORMAT');
		return $date->format($timeFormat);
	}

	/**
	 * @param string $organizerEmail
	 * @return int|null
	 */
	protected function getIcsFileId(string $organizerName, string $eventName): ?int
	{
		try
		{
			$icsManager = IcsManager::getInstance();
			$fileId = $icsManager->createIcsFile($this->event->setName($eventName), [
				'eventUrl' => Sharing\Helper::getShortUrl($this->eventLink->getUrl()),
				'conferenceUrl' => Sharing\Helper::getShortUrl($this->eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE),
				'organizer' => [
					'name' => $organizerName,
					'email' => $this->getOrganizerEmail(),
				],
			]);
		}
		catch (\Exception $e)
		{
			return null;
		}

		return $fileId;
	}

	protected function getOrganizerEmail(): string
	{
		$region = Main\Application::getInstance()->getLicense()->getRegion();
		if ($region === 'ru')
		{
			return 'no-reply@bitrix24.ru';
		}

		return 'no-reply@bitrix24.com';
	}

	protected function getAbuseLink(): ?string
	{
		$ownerId = $this->eventLink->getOwnerId();
		$calendarLink = $this->getCalendarLink() ?? $this->eventLink->getUrl();

		return Sharing\Helper::getEmailAbuseLink($ownerId, $calendarLink);
	}

	protected function getBitrix24Link(): ?string
	{
		return Sharing\Helper::getBitrix24Link();
	}

}