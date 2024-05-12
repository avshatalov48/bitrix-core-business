<?php

namespace Bitrix\Calendar\Public;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\ICal\IcsManager;
use Bitrix\Calendar\Util;
use Bitrix\Main\Type\Date;

class PublicEvent
{
	public const PUBLIC_EVENT_PATH = 'pub/calendar-event';

	public const ACTION = 'action';
	public const ACCEPT = 'accept';
	public const DECLINE = 'decline';
	public const ICS = 'ics';
	public const ACTION_ACCEPT = '?'.self::ACTION.'='.self::ACCEPT;
	public const ACTION_DECLINE = '?'.self::ACTION.'='.self::DECLINE;
	public const ACTION_ICS = '?'.self::ACTION.'='.self::ICS;

	public static function isHashValid(Event $event, ?string $hash): bool
	{
		$eventId = $event->getId();
		$userId = $event->getOwner()?->getId();
		$dateCreate = $event->getDateCreate();

		if ($userId === null || $dateCreate === null)
		{
			return false;
		}

		$dateCreateTimestamp = (int)Util::getTimestamp($dateCreate->toString());

		return self::getHashForPubEvent($eventId, $userId, $dateCreateTimestamp) === $hash;
	}

	public static function getDetailLinkFromEvent(Event $event): ?string
	{
		$eventId = $event->getId();
		$ownerId = $event->getOwner()?->getId();
		$dateCreate = $event->getDateCreate();

		if ($ownerId === null || $dateCreate === null)
		{
			return null;
		}

		$dateCreateTimestamp = (int)Util::getTimestamp(
			$dateCreate->format(Date::convertFormatToPhp(FORMAT_DATETIME)),
		);

		return self::getDetailLink($eventId, $ownerId, $dateCreateTimestamp);
	}

	public static function getDetailLink(int $eventId, int $userId, int $dateCreateTimestamp): string
	{
		$serverPath = \CCalendar::GetServerPath();
		$publicPath = self::PUBLIC_EVENT_PATH;

		return "$serverPath/$publicPath/$eventId/".self::getHashForPubEvent($eventId, $userId, $dateCreateTimestamp)."/";
	}

	protected static function getHashForPubEvent(int $eventId, int $userId, int $dateCreateTimestamp): string
	{
		return md5($eventId.self::getSaltForPubLink().$dateCreateTimestamp.$userId);
	}

	protected static function getSaltForPubLink(): string
	{
		$salt = \COption::GetOptionString('calendar', 'pub_event_salt', '');

		if (empty($salt))
		{
			$salt = uniqid('', true);
			\COption::SetOptionString('calendar', 'pub_event_salt', $salt);
		}

		return $salt;
	}

	public static function prepareEventDescriptionForIcs(Event $event): string
	{
		if ($event->getMeetingDescription()?->getHideGuests())
		{
			$event->setAttendeesCollection(null);
		}

		$detailLink = self::getDetailLinkFromEvent($event);
		$description = IcsManager::getInstance()->prepareEventDescription($event, [
			'eventUrl' => $detailLink,
		]);

		return str_replace("\\n", "\n", $description);
	}
}