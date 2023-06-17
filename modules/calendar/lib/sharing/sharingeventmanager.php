<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Util;
use Bitrix\Crm;
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
	/** @var string|null  */
	private ?string $parentLinkHash;

	/**
	 * @param Event $event
	 * @param int|null $hostId
	 * @param int|null $ownerId
	 * @param string|null $parentLinkHash
	 */
	public function __construct(Event $event, ?int $hostId = null, ?int $ownerId = null, ?string $parentLinkHash = null)
	{
		$this->event = $event;
		$this->hostId = $hostId;
		$this->ownerId = $ownerId;
		$this->parentLinkHash = $parentLinkHash;
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
	 * @return Result
	 * @throws ArgumentException
	 */
	public function createEvent(bool $sendInvitations = true, string $externalUserName = ''): Result
	{
		$result = new Result();

		if (!$this->checkUserAccessibility())
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
            'parentLinkHash' => $this->parentLinkHash,
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
	public static function prepareEventForSave($data, $userId): Event
	{
		global $DB;
		$ownerId = (int)($data['ownerId'] ?? null);
		$sectionId = self::getSectionId($userId);
		$attendeesCodes = ['U' . $userId, 'U' . $ownerId];
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
			'NAME' => $DB->ForSql(trim($data['eventName'] ?? '')),
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
			'DESCRIPTION' => $DB->ForSql(trim($data['description'] ?? '')),
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

	public static function setCanceledTimeOnSharedLink(int $eventId): void
	{
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
		if ($eventLink instanceof Sharing\Link\EventLink)
		{
			$eventLink->setCanceledTimestamp(time());
			(new Sharing\Link\EventLinkMapper())->update($eventLink);
		}
	}

	public static function onSharingEventMeetingStatusChange(
		int $parentEventId,
		int $userId,
		string $currentMeetingStatus
	)
	{
		$userEvent = \CCalendarEvent::GetList([
			'arFilter' => [
				'PARENT_ID' => $parentEventId,
				'OWNER_ID' => $userId,
				'IS_MEETING' => 1,
				'DELETED' => 'N'
			],
			'checkPermissions' => false,
		]);

		if (empty($userEvent))
		{
			return;
		}

		$userEvent = $userEvent[0];
		$previousMeetingStatus = $userEvent['MEETING_STATUS'];

		if (
			$currentMeetingStatus === Dictionary::MEETING_STATUS['Yes']
			&& $previousMeetingStatus === Dictionary::MEETING_STATUS['Question']
		)
		{
			self::onSharingCrmEventConfirmed(
				(int)$userEvent['PARENT_ID'],
				$userEvent['DATE_FROM'] ?? null,
				$userEvent['TZ_FROM'] ?? null,
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
			self::onSharingCrmEventDeclined((int)$userEvent['PARENT_ID']);
		}
	}

	/**
	 * @param int $eventId
	 * @param string|null $dateFrom
	 * @param string|null $timezone
	 * @return void
	 * @throws LoaderException
	 */
	public static function onSharingCrmEventConfirmed(int $eventId, ?string $dateFrom, ?string $timezone): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$crmDealLink = self::getCrmDealLink($eventId);

		$activity = \CCrmActivity::GetByCalendarEventId($eventId, false);

		if ($crmDealLink && $activity)
		{
			(new Sharing\Crm\NotifyManager($crmDealLink, Sharing\Crm\NotifyManager::NOTIFY_EVENT_CONFIRMED))
				->sendSharedCrmActionsEvent(
					Util::getDateTimestamp($dateFrom, $timezone),
					$activity['ID'],
					\CCrmOwnerType::Activity,
				)
			;
		}
	}

	public static function onSharingCrmEventDeclined(int $eventId): void
	{
		(new Sharing\Crm\ActivityManager($eventId))
			->completeSharedCrmActivity(Sharing\Crm\ActivityManager::STATUS_CANCELED_BY_MANAGER)
		;

		$sharingFactory = new Sharing\Link\Factory();

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = $sharingFactory->getEventLinkByEventId($eventId);

		/** @var Sharing\Link\CrmDealLink $crmDealLink */
		$crmDealLink = $sharingFactory->getLinkByHash($eventLink->getParentLinkHash());

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventId);

		if ($crmDealLink->getContactId() > 0)
		{
			(new Crm\Integration\Calendar\Notification\NotificationService())
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
	}

	public static function onSharingCrmEventClientDelete(int $eventId): void
	{
		(new Sharing\Crm\ActivityManager($eventId))
			->completeSharedCrmActivity(Sharing\Crm\ActivityManager::STATUS_CANCELED_BY_CLIENT);
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

	/**
	 * @return bool
	 */
	private function checkUserAccessibility(): bool
	{
		$timezone = $this->event->getStartTimeZone()->getTimeZone()->getName();
		$userId = $this->ownerId;
		$fromTs = \CCalendar::Timestamp((string)$this->event->getStart(), false);
		$toTs = \CCalendar::Timestamp((string)$this->event->getEnd(), false);

		return (new SharingAccessibilityManager([
			'userId' => $userId,
			'timestampFrom' => $fromTs,
			'timestampTo' => $toTs,
			'timezone' => $timezone
		]))->checkUserAccessibility();
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
}