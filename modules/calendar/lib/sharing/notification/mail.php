<?php
namespace Bitrix\Calendar\Sharing\Notification;

use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Core\Mappers;
use CCalendar;

class Mail extends Service
{
	public function notifyAboutMeetingStatus(string $to): void
	{
		$from = '';
		if (CCalendar::IsBitrix24())
		{
			$from = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_BITRIX24_FROM');
		}

		$owner = $this->getOwner();
		$mailEvent = [
			'EVENT_NAME' => 'CALENDAR_SHARING',
			'C_FIELDS' => [
				'EMAIL_FROM' => $from,
				'EMAIL_TO' => $to,
				'SUBJECT' => $this->getEmailSubjectLoc($owner['STATUS'], $this->event->getName()),

				//arParams below
				'EVENT_NAME' => $this->event->getName(),
				'EVENT_DATETIME' => $this->getEventFormattedDateTime(),
				'TIMEZONE' => Sharing\Helper::formatTimezone($this->event->getStartTimeZone()),
				'STATUS' => $owner['STATUS'],
				'OWNER_NAME' => $owner['NAME'],
				'OWNER_PHOTO' => $owner['PHOTO'],
				'CALENDAR_WEEKDAY' => $this->getCalendarWeekDay(),
				'CALENDAR_DAY' => $this->getCalendarDay(),
			],
			'LID' => SITE_ID,
			'DUPLICATE' => 'Y',
			'DATE_INSERT' => (new Main\Type\DateTime())->format('Y-m-d H:i:s'),
		];

		if ($owner['STATUS'] === 'N')
		{
			$calendarLink = $this->getCalendarLink();
			if (!is_null($calendarLink))
			{
				$mailEvent['C_FIELDS']['NEW_EVENT_LINK'] = $calendarLink;
			}
		}
		else
		{
			$mailEvent['C_FIELDS']['ICS_FILE'] = $this->eventLink->getUrl() . Sharing\Helper::ACTION_ICS;
			$mailEvent['C_FIELDS']['CANCEL_LINK'] = $this->eventLink->getUrl() . Sharing\Helper::ACTION_CANCEL;
			$mailEvent['C_FIELDS']['VIDEOCONFERENCE_LINK'] = $this->eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE;

			if ($icsFileId = $this->getIcsFileId($to))
			{
				$mailEvent['FILE']  = [$icsFileId];
			}
		}

		Main\Mail\Event::sendImmediate($mailEvent);
	}

	protected function getEmailSubjectLoc(string $ownerStatus, string $eventName): string
	{
		$subject = Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_Q');

		if ($ownerStatus === 'Y')
		{
			$subject =  Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_Y', ['#EVENT_NAME#' => $eventName]);
		}

		if ($ownerStatus === 'N')
		{
			$subject =  Loc::getMessage('EC_CALENDAR_SHARING_MAIL_SUBJECT_N', ['#EVENT_NAME#' => $eventName]);
		}

		return $subject;
	}

	protected function getCalendarWeekDay(): string
	{
		return strtoupper(FormatDate('D', $this->event->getStart()->getTimestamp()));
	}

	protected function getCalendarDay(): string
	{
		return $this->event->getStart()->format('j');
	}

	protected function getIcsFileId(string $organizerEmail): ?int
	{
		try
		{
			$event = (new Mappers\Event())->getById($this->event->getId());
			$icsManager = IcsManager::getInstance();
			$fileId = $icsManager->createIcsFile($event, [
				'eventUrl' => Sharing\Helper::getShortUrl($this->eventLink->getUrl()),
				'conferenceUrl' => Sharing\Helper::getShortUrl($this->eventLink->getUrl() . Sharing\Helper::ACTION_CONFERENCE),
				'organizer' => [
					'name' => $this->event->getMeetingDescription()->getFields()['HOST_NAME'],
					'email' => $organizerEmail,
				],
			]);
		}
		catch (\Exception $e)
		{
			return null;
		}

		return $fileId;
	}

}