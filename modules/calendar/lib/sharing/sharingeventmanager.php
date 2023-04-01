<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Sharing;
use Bitrix\Main\PhoneNumber;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class SharingEventManager
{
	public const SHARED_EVENT_TYPE = '#shared#';
	/** @var Event  */
	private Event $event;
	/** @var int|null  */
	private ?int $hostId;
	/** @var int|null  */
	private ?int $ownerId;
	/** @var string|null  */
	private ?string $userLinkHash;

	/**
	 * @param Event $event
	 * @param int|null $hostId
	 * @param int|null $ownerId
	 */
	public function __construct(Event $event, ?int $hostId = null, ?int $ownerId = null, ?string $userLinkHash = null)
	{
		$this->event = $event;
		$this->hostId = $hostId;
		$this->ownerId = $ownerId;
		$this->userLinkHash = $userLinkHash;
	}

	/**
	 * @return Result
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function createEvent(): Result
	{
		$result = new Result();

		if (!$this->checkUserAccessibility())
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_USER_BUSY')));

			return $result;
		}

		$eventId = (new Mappers\Event())->create($this->event)->getId();
		$this->event->setId($eventId);

		if (!$eventId)
		{
			$result->addError(new Error(Loc::getMessage('EC_SHARINGAJAX_EVENT_SAVE_ERROR')));

			return $result;
		}

		$eventLink = (new Sharing\Link\Factory())->createEventLink($eventId, $this->ownerId, $this->hostId, $this->userLinkHash);

		$result->setData([
			'eventLink' => $eventLink,
			'event' => $this->event,
		]);

		return $result;
	}

	/**
	 * @param int $linkId
	 * @return Result
	 * @throws \Exception
	 */
	public function deleteEvent(int $linkId): Result
	{
		$result = new Result();
		(new Mappers\Event())->delete($this->event);
		Sharing\Link\SharingLinkTable::update($linkId, [
			'ACTIVE' => 'N',
		]);

		$this->notifyEventDeleted();
		// Sharing\Link\SharingLinkTable::delete($linkId);

		return $result;
	}

	/**
	 * @param string $userContact
	 * @return bool
	 */
	public static function validateContactData(string $userContact): bool
	{
		return self::isEmailCorrect($userContact)
			|| self::isPhoneNumberCorrect($userContact);
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
			&& PhoneNumber\Parser::getInstance()->parse($userContact)
				->isValid()
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
			'EVENT_TYPE' => self::SHARED_EVENT_TYPE,
			'ACCESSIBILITY' => 'busy',
			'IMPORTANCE' => 'normal',
			'ATTENDEES_CODES' => $attendeesCodes,
			'MEETING_HOST' => $userId,
			'IS_MEETING' => true,
			'MEETING' => $meeting
		];

		return (new EventBuilderFromArray($eventData))->build();
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