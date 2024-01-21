<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Util;
use Bitrix\Crm;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\PhoneNumber;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use CUser;

class SharingEventManager
{
	public const SHARED_EVENT_TYPE = Dictionary::EVENT_TYPE['shared'];
	public const SHARED_EVENT_CRM_TYPE = Dictionary::EVENT_TYPE['shared_crm'];
	/** @var Event  */
	private Event $event;
	/** @var int|null  */
	private ?int $hostId;
	/** @var int|null  */
	private ?int $ownerId;
	/** @var Sharing\Link\CrmDealLink|Sharing\Link\UserLink|null $link */
	private ?Sharing\Link\Link $link;

	/**
	 * @param Event $event
	 * @param int|null $hostId
	 * @param int|null $ownerId
	 * @param Sharing\Link\Link|null $link
	 */
	public function __construct(Event $event, ?int $hostId = null, ?int $ownerId = null, ?Sharing\Link\Link $link = null)
	{
		$this->event = $event;
		$this->hostId = $hostId;
		$this->ownerId = $ownerId;
		$this->link = $link;
	}

	/**
	 * @param Event $event
	 * @return $this
	 */
	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	/**
	 * @param bool $sendInvitations
	 * @param string $externalUserName
	 * @return Result
	 * @throws ArgumentException
	 */
	public function createEvent(bool $sendInvitations = true, string $externalUserName = ''): Result
	{
		$result = new Result();

		if (!$this->doesEventHasCorrectTime())
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_USER_BUSY')));

			return $result;
		}

		if (!$this->doesEventSatisfyRule())
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_USER_BUSY')));

			return $result;
		}

		$members = $this->link->getMembers();
		$users = array_merge([$this->link->getOwnerId()], array_map(static function ($member){
			return $member->getId();
		}, $members));

		if (!$this->checkUserAccessibility($users))
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_USER_BUSY')));

			return $result;
		}

		$eventId = (new Mappers\Event())->create($this->event, [
			'sendInvitations' => $sendInvitations
		])->getId();

		$this->event->setId($eventId);

		if (!$eventId)
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_SAVE_ERROR')));

			return $result;
		}

		$eventLinkParams = [
			'eventId' => $eventId,
			'ownerId' => $this->ownerId,
			'hostId' => $this->hostId,
			'parentLinkHash' => $this->link->getHash(),
			'expiryDate' => Helper::createSharingLinkExpireDate(
				DateTime::createFromTimestamp($this->event->getEnd()->getTimestamp()),
				Sharing\Link\Helper::EVENT_SHARING_TYPE
			),
			'externalUserName' => $externalUserName,
		];

		$eventLink = (new Sharing\Link\Factory())->createEventLink($eventLinkParams);

		$result->setData([
			'eventLink' => $eventLink,
			'event' => $this->event,
		]);

		return $result;
	}

	/**
	 * @return Result
	 * @throws \Exception
	 */
	public function deleteEvent(): Result
	{
		$result = new Result();

		(new Mappers\Event())->delete($this->event);
		$this->notifyEventDeleted();

		return $result;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function deactivateEventLink(Sharing\Link\EventLink $eventLink): self
	{
		$eventLink
			->setCanceledTimestamp(time())
			->setActive(false)
		;

		(new Sharing\Link\EventLinkMapper())->update($eventLink);

		return $this;
	}

	/**
	 * @param string $userContact
	 * @return bool
	 */
	public static function validateContactData(string $userContact): bool
	{
		return self::isEmailCorrect($userContact)
			|| self::isPhoneNumberCorrect($userContact)
			;
	}

	/**
	 * @param string $userName
	 * @return bool
	 */
	public static function validateContactName(string $userName): bool
	{
		return self::isUserNameCorrect($userName);
	}

	private static function isUserNameCorrect(string $userName): bool
	{
		return $userName !== '';
	}

	public static function isEmailCorrect(string $userContact): bool
	{
		return check_email($userContact);
	}

	public static function isPhoneNumberCorrect(string $userContact): bool
	{
		return Helper::isPhoneFeatureEnabled()
			&& PhoneNumber\Parser::getInstance()->parse($userContact)->isValid()
			;
	}

	/**
	 * @param $data
	 * @param $userId
	 * @return Event
	 */
	public static function prepareEventForSave($data, $userId, Sharing\Link\Joint\JointLink $link): Event
	{
		$ownerId = (int)($data['ownerId'] ?? null);
		$sectionId = self::getSectionId($userId);

		$attendeesCodes = ['U' . $userId, 'U' . $ownerId];
		$members = $link->getMembers();
		foreach ($members as $member)
		{
			$attendeesCodes[] = 'U' . $member->getId();
		}

		$meeting = [
			'HOST_NAME' => \CCalendar::GetUserName($userId),
			'NOTIFY' => true,
			'REINVITE' => false,
			'ALLOW_INVITE' => true,
			'MEETING_CREATOR' => $userId,
			'HIDE_GUESTS' => false,
		];

		$eventData = [
			'OWNER_ID' => $userId,
			'NAME' => (string)($data['eventName'] ?? ''),
			'DATE_FROM' => (string)($data['dateFrom'] ?? ''),
			'DATE_TO' => (string)($data['dateTo'] ?? ''),
			'TZ_FROM' => (string)($data['timezone'] ?? ''),
			'TZ_TO' => (string)($data['timezone'] ?? ''),
			'SKIP_TIME' => 'N',
			'SECTIONS' => [$sectionId],
			'EVENT_TYPE' => $data['eventType'],
			'ACCESSIBILITY' => 'busy',
			'IMPORTANCE' => 'normal',
			'ATTENDEES_CODES' => $attendeesCodes,
			'MEETING_HOST' => $userId,
			'IS_MEETING' => true,
			'MEETING' => $meeting,
			'DESCRIPTION' => (string)($data['description'] ?? ''),
		];

		return (new EventBuilderFromArray($eventData))->build();
	}

	public static function getEventDataFromRequest($request): array
	{
		return [
			'ownerId' => (int)($request['ownerId'] ?? 0),
			'dateFrom' => (string)($request['dateFrom'] ?? ''),
			'dateTo' => (string)($request['dateTo'] ?? ''),
			'timezone' => (string)($request['timezone'] ?? ''),
			'description' => (string)($request['description'] ?? ''),
			'eventType' => Dictionary::EVENT_TYPE['shared'],
		];
	}

	public static function getSharingEventNameByUserId(int $userId): string
	{
		$user = CUser::GetByID($userId)->Fetch();
		$userName = ($user['NAME'] ?? '') . ' ' . ($user['LAST_NAME'] ?? '');

		return self::getSharingEventNameByUserName($userName);
	}

	public static function getSharingEventNameByUserName(?string $userName): string
	{
		if (!empty($userName))
		{
			$result = Loc::getMessage('CALENDAR_SHARING_EVENT_MANAGER_EVENT_NAME', [
				'#GUEST_NAME#' => trim($userName),
			]);
		}
		else
		{
			$result = Loc::getMessage('CALENDAR_SHARING_EVENT_MANAGER_EVENT_NAME_WITHOUT_GUEST');
		}

		return $result;
	}

	/**
	 * @param $request
	 * @return array
	 */
	public static function getCrmEventDataFromRequest($request): array
	{
		return [
			'ownerId' =>(int)($request['ownerId'] ?? 0),
			'dateFrom' => (string)($request['dateFrom'] ?? ''),
			'dateTo' => (string)($request['dateTo'] ?? ''),
			'timezone' => (string)($request['timezone'] ?? ''),
			'description' => (string)($request['description'] ?? ''),
			'eventType' => Dictionary::EVENT_TYPE['shared_crm'],
		];
	}

	/**
	 * @return string[]
	 */
	public static function getSharingEventTypes(): array
	{
		return [
			self::SHARED_EVENT_CRM_TYPE,
			self::SHARED_EVENT_TYPE,
		];
	}

	/**
	 * @param array $fields
	 * @return void
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onSharingEventEdit(array $fields): void
	{
		$eventId = $fields['ID'];
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
		if ($eventLink instanceof Sharing\Link\EventLink)
		{
			self::updateEventSharingLink($eventLink, $fields);
		}
	}

	/**
	 * @param int $eventId
	 * @return void
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function setCanceledTimeOnSharedLink(int $eventId): void
	{
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
		if ($eventLink instanceof Sharing\Link\EventLink)
		{
			$eventLink->setCanceledTimestamp(time());
			(new Sharing\Link\EventLinkMapper())->update($eventLink);
		}
	}

	/**
	 * @param int $userId
	 * @param string $currentMeetingStatus
	 * @param array $userEventBeforeChange
	 * @param bool|null $isAutoAccept
	 * @return void
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onSharingEventMeetingStatusChange(
		int $userId,
		string $currentMeetingStatus,
		array $userEventBeforeChange,
		bool $isAutoAccept = false
	)
	{
		/** @var Sharing\Link\EventLink $eventLink*/
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId((int)$userEventBeforeChange['PARENT_ID']);

		if (!$eventLink)
		{
			return;
		}

		$ownerId = $eventLink->getOwnerId();
		//if not the link owner's event has changed, send notification to link owner
		if ($ownerId !== $userId && !$isAutoAccept)
		{
			self::onSharingEventGuestStatusChange($currentMeetingStatus, $userEventBeforeChange, $eventLink, $userId);
		}
		else if ($userEventBeforeChange['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared'])
		{
			self::onSharingCommonEventMeetingStatusChange($eventLink);
		}
		else if ($userEventBeforeChange['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared_crm'])
		{
			self::onSharingCrmEventStatusChange($currentMeetingStatus, $userEventBeforeChange, $userId, $ownerId);
		}
	}

	private static function onSharingEventGuestStatusChange(
		string $currentMeetingStatus,
		array $event,
		Sharing\Link\EventLink $eventLink,
		int $userId
	): void
	{
		\CCalendarNotify::Send([
			'mode' => $currentMeetingStatus === "Y" ? 'accept' : 'decline',
			'name' => $event['NAME'],
			'from' => $event["DATE_FROM"],
			'to' => $event["DATE_TO"],
			'location' => \CCalendar::GetTextLocation($userEvent["LOCATION"] ?? null),
			'guestId' => $userId,
			'eventId' => $event['PARENT_ID'],
			'userId' => $eventLink->getOwnerId(),
			'fields' => $event
		]);
	}

	private static function onSharingCommonEventMeetingStatusChange(Sharing\Link\EventLink $eventLink): void
	{
		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventLink->getEventId());

		$host = CUser::GetByID($eventLink->getHostId())->Fetch();
		$email = $host['PERSONAL_MAILBOX'] ?? null;
		$phone = $host['PERSONAL_PHONE'] ?? null;
		$userContact = !empty($email) ? $email : $phone;

		$notificationService = null;
		if ($userContact && self::isEmailCorrect($userContact))
		{
			$notificationService = (new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
			;
		}

		$notificationService?->notifyAboutMeetingStatus($userContact);
	}

	private static function onSharingCrmEventStatusChange(
		string $currentMeetingStatus,
		array $userEventBeforeChange,
		int $userId,
		int $ownerId
	): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$previousMeetingStatus = $userEventBeforeChange['MEETING_STATUS'] ?? null;

		if (
			$currentMeetingStatus === Dictionary::MEETING_STATUS['Yes']
			&& $previousMeetingStatus === Dictionary::MEETING_STATUS['Question']
			&& $userId === $ownerId
		)
		{
			self::onSharingCrmEventConfirmed(
				(int)$userEventBeforeChange['PARENT_ID'],
				$userEventBeforeChange['DATE_FROM'] ?? null,
				$userEventBeforeChange['TZ_FROM'] ?? null,
			);
		}

		if (
			$currentMeetingStatus === Dictionary::MEETING_STATUS['No']
			&& (
				$previousMeetingStatus === Dictionary::MEETING_STATUS['Question']
				|| $previousMeetingStatus === Dictionary::MEETING_STATUS['Yes']
			)
		)
		{
			self::onSharingCrmEventDeclined((int)$userEventBeforeChange['PARENT_ID']);
		}
	}

	/**
	 * @param int $eventId
	 * @param string|null $dateFrom
	 * @param string|null $timezone
	 * @return void
	 * @throws LoaderException
	 */
	private static function onSharingCrmEventConfirmed(int $eventId, ?string $dateFrom, ?string $timezone): void
	{
		$crmDealLink = self::getCrmDealLink($eventId);

		$activity = \CCrmActivity::GetByCalendarEventId($eventId, false);

		if ($crmDealLink && $activity)
		{
			(new Sharing\Crm\NotifyManager($crmDealLink, Sharing\Crm\NotifyManager::NOTIFY_TYPE_EVENT_CONFIRMED))
				->sendSharedCrmActionsEvent(
					Util::getDateTimestamp($dateFrom, $timezone),
					$activity['ID'],
					\CCrmOwnerType::Activity,
				)
			;
		}
	}

	private static function onSharingCrmEventDeclined(int $eventId): void
	{
		$sharingFactory = new Sharing\Link\Factory();

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $sharingFactory->getEventLinkByEventId($eventId);

		/** @var Sharing\Link\CrmDealLink $crmDealLink */
		$crmDealLink = $sharingFactory->getLinkByHash($eventLink->getParentLinkHash());

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventId);

		$completeActivityStatus = Sharing\Crm\ActivityManager::STATUS_CANCELED_BY_MANAGER;

		$userId = \CCalendar::GetUserId();
		if ($userId === 0 || $userId === $event->getEventHost()->getId())
		{
			$completeActivityStatus = Sharing\Crm\ActivityManager::STATUS_CANCELED_BY_CLIENT;
		}

		(new Sharing\Crm\ActivityManager($eventId))
			->completeSharedCrmActivity($completeActivityStatus)
		;
		self::setCanceledTimeOnSharedLink($eventId);
		if ($crmDealLink->getContactId() > 0)
		{
			Crm\Integration\Calendar\Notification\Manager::getSenderInstance($crmDealLink)
				->setCrmDealLink($crmDealLink)
				->setEventLink($eventLink)
				->setEvent($event)
				->sendCrmSharingCancelled()
			;
		}
		else
		{
			$email = CUser::GetByID($eventLink->getHostId())->Fetch()['PERSONAL_MAILBOX'] ?? null;
			if (!is_string($email))
			{
				return;
			}

			$eventLink->setCanceledTimestamp(time());
			(new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
				->notifyAboutMeetingCancelled($email)
			;
		}

		self::reSaveEventWithoutAttendeesExceptHostAndSharingLinkOwner($eventLink);
	}

	public static function onSharingEventEdited(int $eventId, array $previousFields): void
	{
		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventId);
		if ($event instanceof Event)
		{
			$oldEvent = Event::fromBuilder(new EventBuilderFromArray($previousFields));
			if ($event->getSpecialLabel() === Dictionary::EVENT_TYPE['shared'])
			{
				self::onSharingCommonEventEdited($event, $oldEvent);
			}
			else if ($event->getSpecialLabel() === Dictionary::EVENT_TYPE['shared_crm'])
			{
				self::onSharingCrmEventEdited($event, $oldEvent);
			}
		}
	}

	private static function onSharingCommonEventEdited(Event $event, Event $oldEvent): void
	{
		$sharingFactory = new Sharing\Link\Factory();

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $sharingFactory->getEventLinkByEventId($event->getId());

		//TODO remove if not needed
//		/** @var Sharing\Link\UserLink $crmDealLink */
//		$userLink = $sharingFactory->getLinkByHash($eventLink->getParentLinkHash());

		$host = CUser::GetByID($eventLink->getHostId())->Fetch();
		$email = $host['PERSONAL_MAILBOX'] ?? null;
		$phone = $host['PERSONAL_PHONE'] ?? null;
		$userContact = !empty($email) ? $email : $phone;

		$notificationService = null;
		if ($userContact && self::isEmailCorrect($userContact))
		{
			$notificationService = (new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
				->setOldEvent($oldEvent)
			;
		}

		if ($notificationService !== null)
		{
			$notificationService->notifyAboutSharingEventEdit($userContact);
		}
	}

	private static function onSharingCrmEventEdited(Event $event, Event $oldEvent): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		(new Sharing\Crm\ActivityManager($event->getId()))
			->editActivityDeadline(DateTime::createFromUserTime($event->getStart()->toString()))
		;

		$sharingFactory = new Sharing\Link\Factory();

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $sharingFactory->getEventLinkByEventId($event->getId());
		if (!$eventLink instanceof Sharing\Link\EventLink)
		{
			return;
		}

		/** @var Sharing\Link\CrmDealLink $crmDealLink */
		$crmDealLink = $sharingFactory->getLinkByHash($eventLink->getParentLinkHash());
		if (!$crmDealLink instanceof Sharing\Link\CrmDealLink)
		{
			return;
		}

		if ($crmDealLink->getContactId() > 0)
		{
			Crm\Integration\Calendar\Notification\Manager::getSenderInstance($crmDealLink)
				->setCrmDealLink($crmDealLink)
				->setEventLink($eventLink)
				->setEvent($event)
				->setOldEvent($oldEvent)
				->sendCrmSharingEdited()
			;
		}
		else
		{
			$email = CUser::GetByID($eventLink->getHostId())->Fetch()['PERSONAL_MAILBOX'] ?? null;
			if (!is_string($email))
			{
				return;
			}

			(new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
				->setOldEvent($oldEvent)
				->notifyAboutSharingEventEdit($email)
			;
		}
	}

	public static function onSharingEventDeleted(int $eventId, string $eventType): void
	{
		/**@var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
		if ($eventLink)
		{
			self::setDeclinedStatusOnLinkOwnerEvent($eventLink);

			if ($eventType === Dictionary::EVENT_TYPE['shared'])
			{
				self::onSharingCommonEventDeclined($eventLink);
			}
			else if ($eventType === Dictionary::EVENT_TYPE['shared_crm'])
			{
				self::onSharingCrmEventDeclined($eventId);
			}

		}
	}

	public static function onSharingCommonEventDeclined(Sharing\Link\EventLink $eventLink)
	{
		self::setCanceledTimeOnSharedLink($eventLink->getEventId());
		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventLink->getEventId());

		$host = CUser::GetByID($eventLink->getHostId())->Fetch();
		$email = $host['PERSONAL_MAILBOX'] ?? null;
		$phone = $host['PERSONAL_PHONE'] ?? null;
		$userContact = !empty($email) ? $email : $phone;

		$notificationService = null;
		if ($userContact && self::isEmailCorrect($userContact))
		{
			$notificationService = (new Sharing\Notification\Mail())
				->setEventLink($eventLink)
				->setEvent($event)
			;
		}

		if ($notificationService !== null)
		{
			$notificationService->notifyAboutMeetingCancelled($userContact);
		}
	}

	public static function setDeclinedStatusOnLinkOwnerEvent(Sharing\Link\EventLink $eventLink)
	{
		$userId = \CCalendar::GetUserId();
		if ($userId !== 0 && $userId !== $eventLink->getHostId())
		{
			$ownerId = $eventLink->getOwnerId();
			$event = EventTable::query()
				->setSelect(['ID'])
				->where('PARENT_ID', $eventLink->getEventId())
				->whereIn('EVENT_TYPE', self::getSharingEventTypes())
				->where('OWNER_ID', $ownerId)
				->exec()
				->fetch()
			;
			if ($event['ID'] ?? false)
			{
				EventTable::update((int)$event['ID'], ['MEETING_STATUS' => Dictionary::MEETING_STATUS['No']]);
			}
		}
	}

	/**
	 * @param Link\EventLink $eventLink
	 * @param array $fields
	 * @return void
	 */
	private static function updateEventSharingLink(Sharing\Link\EventLink $eventLink, array $fields): void
	{
		if (!empty($fields['DATE_TO']))
		{
			$expireDate = Helper::createSharingLinkExpireDate(
				DateTime::createFromText($fields['DATE_TO']),
				Sharing\Link\Helper::EVENT_SHARING_TYPE
			);
			$eventLink->setDateExpire($expireDate);
		}

		(new Sharing\Link\EventLinkMapper())->update($eventLink);
	}

	/**
	 * @param int $eventId
	 * @return Link\CrmDealLink|null
	 */
	private static function getCrmDealLink(int $eventId): ?Link\CrmDealLink
	{
		$sharingLinkFactory = new Sharing\Link\Factory();
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $sharingLinkFactory->getEventLinkByEventId($eventId);
		if ($eventLink instanceof Sharing\Link\EventLink)
		{
			/** @var Sharing\Link\CrmDealLink $crmDealLink */
			$crmDealLink = $sharingLinkFactory->getLinkByHash($eventLink->getParentLinkHash());
			if ($crmDealLink instanceof Sharing\Link\CrmDealLink)
			{
				return $crmDealLink;
			}
		}

		return null;
	}

	private function doesEventHasCorrectTime(): bool
	{
		$start = new DateTime($this->event->getStart()->toString());
		$end = new DateTime($this->event->getEnd()->toString());

		$offset = Util::getTimezoneOffsetUTC(\CCalendar::GetUserTimezoneName($this->ownerId));
		$fromTs = Util::getDateTimestampUtc($start, $this->event->getStartTimeZone());
		$toTs = Util::getDateTimestampUtc($end, $this->event->getEndTimeZone());

		if ($fromTs < time())
		{
			return false;
		}

		$ownerDate = new \DateTime('now', new \DateTimeZone('UTC'));

		$holidays = $this->getYearHolidays();
		$intersectedHolidays = array_filter($holidays, static fn($holiday) => in_array($holiday, [
			$ownerDate->setTimestamp($fromTs + $offset)->format('j.m'),
			$ownerDate->setTimestamp($toTs + $offset)->format('j.m'),
		], true));

		if (!empty($intersectedHolidays))
		{
			return false;
		}

		return true;
	}

	private function getYearHolidays(): array
	{
		return explode(',', \COption::GetOptionString('calendar', 'year_holidays', Loc::getMessage('EC_YEAR_HOLIDAYS_DEFAULT')));
	}

	private function doesEventSatisfyRule(): bool
	{
		$start = new DateTime($this->event->getStart()->toString());
		$end = new DateTime($this->event->getEnd()->toString());
		$fromTs = Util::getDateTimestampUtc($start, $this->event->getStartTimeZone());
		$toTs = Util::getDateTimestampUtc($end, $this->event->getEndTimeZone());

		$rule = $this->link->getSharingRule();
		$eventDurationMinutes = ($toTs - $fromTs) / 60;
		if ($eventDurationMinutes !== $rule->getSlotSize())
		{
			return false;
		}

		$availableTime = [];
		foreach ($rule->getRanges() as $range)
		{
			foreach ($range->getWeekdays() as $weekday)
			{
				$availableTime[$weekday] ??= [];
				$availableTime[$weekday][] = [
					'from' => $range->getFrom(),
					'to' => $range->getTo(),
				];

				[$intersected, $notIntersected] = $this->separate(fn($interval) => Util::doIntervalsIntersect(
					$interval['from'],
					$interval['to'],
					$range->getFrom(),
					$range->getTo(),
				), $availableTime[$weekday]);

				if (!empty($intersected))
				{
					$from = min(array_column($intersected, 'from'));
					$to = max(array_column($intersected, 'to'));

					$availableTime[$weekday] = [...$notIntersected, [
						'from' => $from,
						'to' => $to,
					]];
				}
			}
		}

		$offset = Util::getTimezoneOffsetUTC(\CCalendar::GetUserTimezoneName($this->ownerId)) / 60;
		$minutesFrom = ($fromTs % 86400) / 60;
		$minutesTo = ($toTs % 86400) / 60;
		$weekday = (int)gmdate('N', $fromTs) % 7;
		foreach ($availableTime[$weekday] as $range)
		{
			if ($minutesFrom >= $range['from'] - $offset && $minutesTo <= $range['to'] - $offset)
			{
				return true;
			}
		}

		return false;
	}

	private function separate($take, $array): array
	{
		return array_reduce($array, fn($s, $e) => $take($e) ? [[...$s[0], $e], $s[1]] : [$s[0], [...$s[1], $e]], [[], []]);
	}

	/**
	 * @return bool
	 */
	private function checkUserAccessibility(array $userIds): bool
	{
		$start = new DateTime($this->event->getStart()->toString());
		$end = new DateTime($this->event->getEnd()->toString());
		$fromTs = Util::getDateTimestampUtc($start, $this->event->getStartTimeZone());
		$toTs = Util::getDateTimestampUtc($end, $this->event->getEndTimeZone());

		return (new SharingAccessibilityManager([
			'userIds' => $userIds,
			'timestampFrom' => $fromTs,
			'timestampTo' => $toTs,
		]))->checkUsersAccessibility();
	}

	/**
	 * @param $userId
	 * @return mixed
	 */
	private static function getSectionId($userId)
	{
		$result = \CCalendarSect::GetList([
			'arFilter' => [
				'OWNER_ID' => $userId,
				'CAL_TYPE' => 'user',
				'ACTIVE' => 'Y'
			]
		]);

		if (!$result)
		{
			$createdSection = \CCalendarSect::CreateDefault([
				'type' => 'user',
				'ownerId' => $userId,
			]);
			$result[] = $createdSection;
		}

		return $result[0]['ID'];
	}

	/**
	 * @return false|null
	 */
	private function notifyEventDeleted()
	{
		return \CCalendarNotify::Send([
			'mode' => 'cancel_sharing',
			'userId' => $this->hostId,
			'guestId' => $this->ownerId,
			'eventId' => $this->event->getId(),
			'from' => $this->event->getStart()->toString(),
			'to' => $this->event->getEnd()->toString(),
			'name' => $this->event->getName(),
			'isSharing' => true,
		]);
	}

	public static function reSaveEventWithoutAttendeesExceptHostAndSharingLinkOwner(Sharing\Link\EventLink $eventLink): void
	{
		$event = (new Mappers\Event)->getById($eventLink->getEventId());
		if ($event)
		{
			$event = \CCalendarEvent::GetList([
				'arFilter' => [
					'ID' => $event->getId(),
				],
				'fetchAttendees' => true,
				'checkPermissions' => false,
				'parseRecursion' => false,
				'setDefaultLimit' => false,
				'limit' => null,
			]);

			$event = $event[0] ?? null;
			if ($event)
			{
				$event['ATTENDEES'] = [$eventLink->getOwnerId(), $eventLink->getHostId()];
				\CCalendar::SaveEvent([
					'arFields' => $event,
					'userId' => $eventLink->getOwnerId(),
					'checkPermission' => false,
					'sendInvitations' => true
				]);
			}
		}
	}
}
