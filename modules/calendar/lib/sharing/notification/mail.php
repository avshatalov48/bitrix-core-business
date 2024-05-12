<?php

namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sharing;

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
		if ($this->doSendMeetingCancelled())
		{
			Sharing\SharingEventManager::setCanceledTimeOnSharedLink($this->event->getId());
			Sharing\SharingEventManager::reSaveEventWithoutAttendeesExceptHostAndSharingLinkOwner($this->eventLink);
			return $this->notifyAboutMeetingCancelled($to);
		}

		return false;
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
			'VIDEOCONFERENCE_LINK' => $this->eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE,
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

	public function notifyAboutSharingEventEdit(string $to): bool
	{
		//TODO waiting for mail template
		Sharing\Helper::setSiteLanguage();
		$ownerName = $this->getOwner()['NAME'];

		$gender = $this->getOwner()['GENDER'];
		//TODO add REAL phrases
		$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED', ['#NAME#' => $ownerName]);
		if ($gender === 'M')
		{
			$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED_M', ['#NAME#' => $ownerName]);
		}
		else if ($gender === 'F')
		{
			$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_CANCELLED_F', ['#NAME#' => $ownerName]);
		}
		$mailParams = $this->getBaseMailParams($ownerName);
		$arParams = [
			'STATUS' => self::MEETING_STATUS_CANCELLED,
			'CALENDAR_LINK' => $this->getCalendarLink(),
			'WHO_EDITED' => $ownerName,
//			'WHEN_EDITED' => $this->getWhenCancelled(),
		];
		$arParams = array_merge($arParams, $mailParams);

		//TODO uncomment
		return true;
//		return $this->sendMessage($to, $arParams, $subject);
	}

	protected function sendMessage(string $to, array $arParams, string $subject, ?array $files = null): bool
	{
		$fields = [
			'EMAIL_TO' => $to,
			'SUBJECT' => $subject,
		];

		if (\CCalendar::IsBitrix24())
		{
			$fields['DEFAULT_EMAIL_FROM'] = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_BITRIX24_FROM');
		}

		return \CEvent::SendImmediate(
			'CALENDAR_SHARING',
			SITE_ID,
			array_merge($fields, $arParams),
			'Y',
			'',
			$files,
		) === 'Y';
	}

	protected function getBaseMailParams(string $ownerName): array
	{
		$arParams = [
			'EVENT_NAME' => Sharing\SharingEventManager::getSharingEventNameByUserName($ownerName),
			'IS_FULL_DAY' => $this->event->isFullDayEvent(),
			'DATE_FROM' => $this->event->getStart()->format(),
			'DATE_TO' => $this->event->getEnd()->format(),
			'TZ_FROM' => $this->event->getStartTimeZone()?->getTimeZone()->getName(),
			'TZ_TO' => $this->event->getEndTimeZone()?->getTimeZone()->getName(),
			'ABUSE_LINK' => $this->getAbuseLink(),
			'BITRIX24_LINK' => $this->getBitrix24Link(),
		];

		$parentLink = $this->getParentLink();
		if (!is_null($parentLink))
		{
			$arParams['AVATARS'] = [];
			foreach ($parentLink->getMembers() as $member)
			{
				$arParams['AVATARS'][] = $member->getAvatar();
			}
		}

		return $arParams;
	}

	protected function getParentLink(): ?Sharing\Link\Joint\JointLink
	{
		$link = Sharing\Link\Factory::getInstance()->getLinkByHash($this->eventLink->getParentLinkHash());
		if ($link instanceof Sharing\Link\Joint\JointLink)
		{
			return $link;
		}

		return null;
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
		catch (\Exception)
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