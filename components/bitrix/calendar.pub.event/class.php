<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}


use Bitrix\Calendar\ICal\Builder\AttachCollection;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;
use Bitrix\Calendar\ICal\Builder\Dictionary;
use Bitrix\Calendar\ICal\MailInvitation\AttachmentEditManager;
use Bitrix\Calendar\ICal\MailInvitation\Helper;
use Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationReplyHandler;
use Bitrix\Calendar\ICal\MailInvitation\SenderEditInvitation;
use Bitrix\Calendar\ICal\MailInvitation\SenderInvitation;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;
use \Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Web\Uri;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/lib/ical/mailinvitation/senderinvitation.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/lib/ical/mailinvitation/senderrequestinvitation.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');


class CalendarPubEventComponent extends CBitrixComponent implements Controllerable
{
	protected $event;

	/**
	 * CalendarPubEventComponent constructor.
	 * @param null $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	/**
	 * @return array
	 */
	public function configureActions(): array
	{
		return [
			'handleDecision' => [
				'-prefilters' => [
					Authentication::class
				],
			],
			'downloadInvitation' => [
				'-prefilters' => [
					Authentication::class
				],
			],
		];
	}

	/**
	 * @return mixed|void|null
	 * @throws LoaderException
	 */
	public function executeComponent()
	{
		if (
			!Loader::includeModule("calendar")
			|| !$this->checkEventId()
		)
		{
			$this->includeComponentTemplate('alert');
			return;
		}

		$event = $this->getEventFromDb();
		if (empty($event)
			|| !$this->checkHash(
				(int)$event['ID'],
				(int)$event['OWNER_ID'],
				(int)Util::getTimestamp($event['DATE_CREATE']->format(Date::convertFormatToPhp(FORMAT_DATETIME))),
				$this->arParams['HASH']
			)
			|| !$this->checkDecision($event)
			|| !$this->checkEventActivity($event)
		)
		{
			$this->includeComponentTemplate('alert');
			return;
		}

		$this->arResult['HASH'] = $this->arParams['HASH'];
		$this->event = $event;

		if ($this->arParams['DOWNLOAD'] === 'Y')
		{
			$this->downloadInvitation();
		}

		$this->prepareParams();

		$this->includeComponentTemplate();
	}

	/**
	 * @return int
	 */
	public function getEventId(): int
	{
		if (isset($this->event['ID']))
		{
			return (int)$this->event['ID'];
		}

		return (int) $this->arParams['EVENT_ID'];
	}

	/**
	 * @param int $eventId
	 * @param int $userId
	 * @param int $dateCreateTimestamp
	 * @param string|null $verifiableHash
	 * @return bool
	 */
	protected function checkHash(int $eventId, int $userId, int $dateCreateTimestamp, ?string $verifiableHash): bool
	{
		if (empty($verifiableHash))
		{
			return false;
		}

		$hash = Helper::getHashForPubEvent($eventId, $userId, $dateCreateTimestamp);

		return $hash === $verifiableHash;
	}

	/**
	 * @return bool
	 */
	private function checkEventId(): bool
	{
		return isset($this->arParams['EVENT_ID']);
	}

	/**
	 * @param $event
	 * @return bool
	 */
	protected function checkEventActivity($event): bool
	{
		return $event['ACTIVE'] === 'Y' && $event['DELETED'] === 'N';
	}

	/**
	 * @return array|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getEventFromDb(): ?array
	{
		return Util::getEventById((int)$this->arParams['EVENT_ID']);
	}

	/**
	 * @param array $event
	 * @return bool
	 */
	private function checkDecision(array $event): bool
	{
		$this->arResult['IS_SHOW_CHOOSE_BUTTON'] = false;
		if (empty($this->arParams['DECISION']))
		{
			return true;
		}

		if (
			in_array(
				$this->arParams['DECISION'],
				[
					SenderInvitation::DECISION_YES,
					SenderInvitation::DECISION_NO
				],
				true
			)
		)
		{
			IncomingInvitationReplyHandler::createInstance()
				->sendNotificationGuestReaction($event, $this->arParams['DECISION']);
			return true;
		}
		elseif ($this->arParams['DECISION'] === SenderEditInvitation::DECISION_CHANGE)
		{
			$this->arResult['IS_SHOW_CHOOSE_BUTTON'] = true;
			return true;
		}

		return false;
	}

	/**
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function handleDecisionAction(): array
	{
		$attendeesList = [];
		$values =  $this->request->getValues();

		if (!Loader::includeModule('calendar'))
		{
			return [];
		}

		$event = Util::getEventById((int)$values['eventId']);
		if ($event && is_array($event))
		{
			$isCheckHash = $this->checkHash(
				$values['eventId'],
				(int)$event['OWNER_ID'],
				(int)Util::getTimestamp($event['DATE_CREATE']->format(Date::convertFormatToPhp(FORMAT_DATETIME))),
				$values['hash']
			);

			if ($isCheckHash)
			{
				CCalendarEvent::SetMeetingStatus(
					[
						'userId' => $event['OWNER_ID'],
						'eventId' => $values['eventId'],
						'status' => $values['decision'] === 'Y'
							? 'Y'
							: 'N',
						'personalNotification' => true
					]
				);

				$meetingInfo = unserialize($event['MEETING'], ['allowed_classes' => false]);
				if (isset($meetingInfo['HIDE_GUESTS']) && !$meetingInfo['HIDE_GUESTS'])
				{
					foreach (Helper::getAttendeesByEventParentId((int)$event['PARENT_ID']) as $attendee)
					{
						/** @var Bitrix\Calendar\ICal\Builder\Attendee $attendee */
						$attendeesList[] = [
							'name' => $attendee->getFullName(),
							'status' => $attendee->getStatus(),
						];
					}
				}
			}
		}

		return ['attendeesList' => $attendeesList];
	}

	/**
	 * @param string $status
	 * @return string
	 */
	public function getStyleClassAttendeeStatus(?string $status): string
	{
		switch ($status)
		{
			case 'ACCEPTED':
				return 'calendar-pub-event-user--accept';
			case 'DECLINED':
				return 'calendar-pub-event-user--cancel';
			default:
				return 'calendar-pub-event-user--waiting';
		}
	}

	/**
	 * @throws ArgumentNullException
	 */
	public function downloadInvitation(): void
	{
		if ($this->event && is_array($this->event))
		{
			$isCheckHash = $this->checkHash(
				$this->getEventId(),
				(int)$this->event['OWNER_ID'],
				(int)Util::getTimestamp($this->event['DATE_CREATE']->format(Date::convertFormatToPhp(FORMAT_DATETIME))),
				$this->arParams['HASH']
			);
			if ($isCheckHash)
			{
				\CTimeZone::Disable();
				$content = $this->getContentInvitation($this->event);
				if ($content)
				{
					$response = $this->getResponseForFileTransfer(mb_strlen($content));
					$response->flush($content);
				}
				\CTimeZone::Enable();
			}
		}

		(\Bitrix\Main\Application::getInstance())->end();
	}

	/**
	 * @param int $contentLength
	 * @return \Bitrix\Main\HttpResponse
	 * @throws ArgumentNullException
	 */
	protected function getResponseForFileTransfer(int $contentLength): \Bitrix\Main\HttpResponse
	{
		return
			(new \Bitrix\Main\HttpResponse())
				->setStatus('200 OK')
				->addHeader('Content-Description', 'File Transfer')
				->addHeader('Content-Type', 'application/force-download; name="invite.ics"')
				->addHeader('Content-Disposition', 'attachment; filename="invite.ics"')
				->addHeader('Expires', '0')
				->addHeader('Cache-Control', 'must-revalidate')
				->addHeader('Pragma', 'public')
				->addHeader('Content-Length', $contentLength)
		;
	}

	/**
	 * @param array $event
	 * @return string|null
	 */
	protected function getContentInvitation(array $event): ?string
	{
		$meeting = unserialize($event['MEETING'], ['allowed_classes' => false]);

		$event['ICAL_ORGANIZER'] = $this->getIcalOrganizer((int)$event['MEETING_HOST']);
		$event['ICAL_ATTENDEES'] = $this->getIcalAttendees($meeting['HIDE_GUESTS']);
		$event['ICAL_ATTACHES'] = Helper::getMailAttaches(
			null,
			(int) $event['MEETING_HOST'],
			(int) $event['PARENT_ID']
		);
		if (is_array($event['LOCATION'])
			&& isset($event['LOCATION']['NEW'])
			&& is_string($event['LOCATION']['NEW'])
		)
		{
			$event['TEXT_LOCATION'] = CCalendar::GetTextLocation($event["LOCATION"]['NEW']);
		}
		elseif (is_string($event['LOCATION']))
		{
			$event['TEXT_LOCATION'] = CCalendar::GetTextLocation($event['LOCATION']);
		}

		return Encoding::convertEncoding(
			AttachmentEditManager::createInstance($event)->getContent(),
			SITE_CHARSET,
			"utf-8"
		);
	}

	/**
	 * @param int $organizerId
	 * @return Attendee
	 */
	protected function getIcalOrganizer(int $organizerId): Attendee
	{
		$organizer = Helper::getUserById($organizerId);
		return Attendee::createInstance(
			$organizer['EMAIL'],
			$organizer['NAME'],
			$organizer['LAST_NAME'],
			null,
			null,
			null,
			$organizer['EMAIL']
		);
	}

	/**
	 * @param bool $hideGuests
	 * @return AttendeesCollection
	 */
	protected function getIcalAttendees(bool $hideGuests): AttendeesCollection
	{
		if ($hideGuests)
		{
			$attendee = Helper::getUserById((int)$this->event['OWNER_ID']);

			return AttendeesCollection::createInstance([
				Attendee::createInstance(
				$attendee['EMAIL'],
				$attendee['NAME'],
				$attendee['LAST_NAME'],
				Dictionary::ATTENDEE_STATUS[$this->event['MEETING_STATUS']],
				Dictionary::ATTENDEE_ROLE['REQ_PARTICIPANT'],
				Dictionary::ATTENDEE_CUTYPE['individual'],
				$attendee['EMAIL']
			)]);
		}

		return Helper::getAttendeesByEventParentId((int)$this->event['PARENT_ID']);
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function prepareParams(): void
	{
		$this->arResult['TOP_TITLE'] = COption::GetOptionString("main", "site_name", Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_REQUEST') , '-');
		if (!empty($this->event['NAME']))
		{
			$this->arResult['NAME'] = Emoji::decode($this->event['NAME']);
		}
		$this->arResult['IS_SHOW_LIST_BOX'] = false;

		$this->prepareEventDurationParams();
		$this->prepareDecisionParams();
		$this->prepareAttendeesParams();
		$this->prepareDescriptionParams();
		$this->prepareAttachmentsParams();
		$this->prepareLocationParams();
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function prepareEventDurationParams(): void
	{
		$this->arResult['FULL_DAY'] = $this->event['DT_SKIP_TIME'] === 'Y';
		$this->arResult['IS_LONG_DATETIME_FORMAT'] = false;
		$this->arResult['IS_SHOW_RRULE'] = false;
		$dateFrom = Util::getDateObject(
			\CCalendar::Date($this->event['DATE_FROM']->getTimestamp()),
			$this->arResult['FULL_DAY'],
			$this->event['TZ_FROM']
		);
		$dateTo = Util::getDateObject(
			\CCalendar::Date($this->event['DATE_TO']->getTimestamp()),
			$this->arResult['FULL_DAY'],
			$this->event['TZ_TO']
		);

		if ($dateFrom instanceof Date && $dateTo instanceof Date)
		{
			$this->prepareDateParamsForDateBox($dateFrom);
			$this->arResult['IS_SHOW_TIME_OFFSET'] = false;
			$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
			$this->arResult['DATE_FROM'] = FormatDate($culture->getFullDateFormat(), $dateFrom->getTimestamp());

			if (
				$dateTo->getDiff($dateFrom)->format('%a') > 0
				|| $dateTo->format('j') !== $dateFrom->format('j')
				|| $dateTo->format('Y') !== $dateFrom->format('Y')
				|| $dateTo->format('n') !== $dateFrom->format('n')
			)
			{
				$this->arResult['IS_LONG_DATETIME_FORMAT'] = true;
				$this->arResult['DATE_TO'] = FormatDate($culture->getFullDateFormat(), $dateTo->getTimestamp());
			}

			if ($this->arResult['FULL_DAY'])
			{
				if (!isset($this->arResult['DATE_TO']))
				{
					$this->arResult['DATE_TO'] = FormatDate($culture->getFullDateFormat(), $dateTo->getTimestamp());
				}
			}
			else
			{
				$this->arResult['TIME_FROM'] = FormatDate(
					$culture->getShortTimeFormat(),
					$dateFrom->getTimestamp() + Util::getTimezoneOffsetFromServer($this->event['TZ_FROM'], $dateFrom)
				);
				$this->arResult['TIME_TO'] = FormatDate(
					$culture->getShortTimeFormat(),
					$dateTo->getTimestamp() + Util::getTimezoneOffsetFromServer($this->event['TZ_TO'], $dateTo)
				);
				$this->arResult['OFFSET_FROM'] = $dateFrom->format('P');
				$this->arResult['TIMEZONE_NAME_FROM'] = $dateFrom->format('e');
				if ($dateFrom->format('e') !== 'UTC')
				{
					$this->arResult['IS_SHOW_TIME_OFFSET'] = true;
				}
			}

			$rrule = CCalendarEvent::ParseRRULE($this->event['RRULE']);
			if (is_array($rrule))
			{
				$this->arResult['IS_SHOW_RRULE'] = true;
				$this->arResult['RRULE'] = Helper::getIcalTemplateRRule(
					$rrule,
					[
						'DATE_FROM' => $this->event['DATE_FROM'],
					]
				);
			}
		}
	}

	/**
	 * @param Date $date
	 */
	protected function prepareDateParamsForDateBox(Date $date): void
	{
		$this->arResult['SHORT_NAME_MONTH'] = Helper::getShortMonthName($date);
		$this->arResult['DATE_FROM_NUMBER'] = $date->format('j');
	}

	/**
	 *
	 */
	protected function prepareDecisionParams(): void
	{
		$decision = $this->arParams['DECISION'] ?? $this->event['MEETING_STATUS'];
		if (isset($this->arParams['DECISION']))
		{
			$server = $this->request->getServer();
			$uri = strstr($server->getRequestUri(), '?', true);
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
			header("Location: {$scheme}://{$server->getServerName()}{$uri}");
		}
		$this->arResult['HAS_DECISION'] = in_array(
			$decision,
			[
				SenderInvitation::DECISION_YES,
				SenderInvitation::DECISION_NO,
			],
			true
		);

		$this->arResult['IS_POSITIVE_DECISION'] = $decision === 'Y';
		$this->arResult['DOWNLOAD_INVITATION_LINK'] = $this->getLinkForDownloadInvitation();
	}

	/**
	 * @return string
	 */
	protected function getLinkForDownloadInvitation(): string
	{
		$server = $this->request->getServer();
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$path = "{$scheme}://{$server->getServerName()}{$server->getRequestUri()}";
		$path .= mb_strpos($path, "?") === false
			? "?download=Y"
			: "&download=Y"
		;

		return $path;
	}

	/**
	 *
	 */
	protected function prepareAttendeesParams(): void
	{
		$this->arResult['IS_SHOW_ATTENDEES_BOX'] = false;
		$this->arResult['ATTENDEES_COLLECTION'] = Helper::getAttendeesByEventParentId($this->event['PARENT_ID']);
		$meeting = unserialize($this->event['MEETING'], ['allowed_classes' => false]);

		if (
			!$meeting['HIDE_GUESTS']
			&& $this->arResult['ATTENDEES_COLLECTION'] instanceof AttendeesCollection
			&& $this->arResult['ATTENDEES_COLLECTION']->getCount() > 0
		)
		{
			$this->arResult['IS_SHOW_LIST_BOX'] = true;
			$this->arResult['IS_SHOW_ATTENDEES_BOX'] = true;
			$this->arResult['ATTENDEES_COUNT'] = $this->arResult['ATTENDEES_COLLECTION']->getCount();
		}
	}

	/**
	 *
	 */
	protected function prepareDescriptionParams(): void
	{
		$this->arResult['IS_SHOW_DESCRIPTION_BOX'] = false;
		if ($this->event['DESCRIPTION'] !== '')
		{
			$this->arResult['EVENT_DESCRIPTION'] = nl2br(
				CCalendarEvent::ParseText(
					Emoji::decode($this->event['DESCRIPTION']),
					$this->event['PARENT_ID']
				)
			);

			$this->arResult['IS_SHOW_DESCRIPTION_BOX'] = true;
			$this->arResult['IS_SHOW_LIST_BOX'] = true;
		}
	}

	/**
	 *
	 */
	protected function prepareAttachmentsParams(): void
	{
		$this->arResult['IS_SHOW_ATTACHMENTS_BOX'] = false;
		$this->arResult['ATTACHMENTS_COLLECTION'] = Helper::getMailAttaches(
			null,
			$this->event['MEETING_HOST'],
			$this->event['PARENT_ID']
		);

		if (
			$this->arResult['ATTACHMENTS_COLLECTION'] instanceof AttachCollection
			&& $this->arResult['ATTACHMENTS_COLLECTION']->getCount() > 0
		)
		{
			$this->arResult['IS_SHOW_ATTACHMENTS_BOX'] = true;
			$this->arResult['IS_SHOW_LIST_BOX'] = true;
		}
	}

	/**
	 *
	 */
	protected function prepareLocationParams(): void
	{
		$this->arResult['IS_SHOW_LOCATION_BOX'] = false;
		if ($this->event['LOCATION'] !== '')
		{
			$this->arResult['EVENT_LOCATION'] = CCalendar::GetTextLocation($this->event['LOCATION']);
			if(!empty($this->arResult['EVENT_LOCATION']))
			{
				$this->arResult['IS_SHOW_LOCATION_BOX'] = true;
				$this->arResult['IS_SHOW_LIST_BOX'] = true;
			}
		}
	}
}