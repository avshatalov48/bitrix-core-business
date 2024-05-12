<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Public\PublicEvent;
use Bitrix\Calendar\Sharing;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class CalendarPubEventComponent extends CBitrixComponent
{
	protected array $allowedActions = [PublicEvent::ACCEPT, PublicEvent::DECLINE, PublicEvent::ICS];

	public function executeComponent()
	{
		Loc::loadMessages(__DIR__.'/templates/.default/alert.php');
		$this->arResult['PAGE_TITLE'] = Loc::getMessage('EC_CALENDAR_ALERT_TITLE_NOT_ATTENDEES');

		$this->arResult['BITRIX24_LINK'] = Sharing\Helper::getBitrix24Link();
		$this->arResult['CURRENT_LANG'] = Loc::getCurrentLang();

		/** @var Event $event */
		$event = (new Mappers\Event())->getById((int)$this->arParams['EVENT_ID']);

		if (!$event || !PublicEvent::isHashValid($event, $this->arParams['HASH']))
		{
			return $this->includeComponentTemplate('template-new');
		}

		$this->arResult['EVENT'] = $this->prepareEvent($event);
		if ($this->arResult['EVENT'] === null)
		{
			return $this->includeComponentTemplate('template-new');
		}

		$this->arResult['PAGE_TITLE'] = $event->getName();
		$this->arResult['ACTION'] = $this->getAction();

		return $this->includeComponentTemplate('template-new');
	}

	protected function prepareEvent(Event $event): ?array
	{
		$attendees = \CCalendarEvent::GetAttendees([$event->getParentId()])[$event->getParentId()] ?? [];

		$owner = current(array_filter(
			$attendees,
			static fn($attendee) => (int)$event->getOwner()->getId() === (int)$attendee['USER_ID'],
		));

		if (empty($owner))
		{
			return null;
		}

		$hideGuests = $event->getMeetingDescription()?->getHideGuests();
		if ($hideGuests)
		{
			$attendees = [$owner];
		}

		$timestampFrom = $event->getStart()->getTimestamp();
		$timestampTo = $event->getEnd()->getTimestamp();
		$eventTimezone = $event->getStartTimeZone()?->toString();
		if (is_string($eventTimezone) && $event->isFullDayEvent())
		{
			$offset = Util::getTimezoneOffsetUTC($eventTimezone);
			$timestampFrom += $offset;
			$timestampTo += $offset;
		}

		return [
			'id' => $event->getId(),
			'hash' => $this->arParams['HASH'],
			'isDeleted' => $event->isDeleted(),
			'name' => $event->getName(),
			'timestampFrom' => $timestampFrom,
			'timestampTo' => $timestampTo,
			'timezone' => $event->isFullDayEvent() ? null : $eventTimezone,
			'isFullDay' => $event->isFullDayEvent(),
			'location' => \CCalendar::GetTextLocation($event->getLocation()),
			'description' => \CCalendarEvent::ParseText($event->getDescription(), $event->getParentId()),
			'files' => $this->prepareFiles($event),
			'rruleDescription' => $this->prepareRruleDescription($event),
			'members' => array_map(fn($attendee) => $this->prepareAttendee($attendee, $owner), $attendees),
		];
	}

	protected function prepareRruleDescription(Event $event): string
	{
		if (!$event->getRecurringRule())
		{
			return '';
		}

		return \CCalendarEvent::GetRRULEDescription([
			'RRULE' => $event->getRecurringRule()->toArray(),
			'DATE_FROM' => $event->getStart()->toString(),
			'DT_SKIP_TIME' => $event->isFullDayEvent() ? 'Y' : 'N',
		]);
	}

	protected function prepareAttendee(array $attendee, array $owner): array
	{
		$name = $attendee['NAME'];
		$lastName = $attendee['LAST_NAME'];
		$avatar = $attendee['AVATAR'];
		$status = $attendee['STATUS'];
		$isOwner = $attendee['USER_ID'] === $owner['USER_ID'] && $owner['STATUS'] !== 'H';

		if ($isOwner && empty(trim("$name $lastName")))
		{
			$name = $owner['EMAIL'];
		}

		return [
			'name' => $name,
			'lastName' => $lastName,
			'avatar' => $avatar,
			'status' => $status,
			'isOwner' => $isOwner,
		];
	}

	protected function prepareFiles(Event $event): array
	{
		$filesCollection = \Bitrix\Calendar\ICal\MailInvitation\Helper::getMailAttaches(
			null,
			$event->getEventHost()?->getId(),
			$event->getParentId()
		);

		$files = [];
		foreach ($filesCollection as $file)
		{
			$files[] = [
				'link' => $file->getLink(),
				'name' => $file->getName(),
				'size' => $file->getFormatSize(),
			];
		}

		return $files;
	}

	/**
	 * @return string
	 */
	protected function getAction(): string
	{
		$request = Main\Context::getCurrent()->getRequest();
		$action = (string)$request->getQueryList()->get(PublicEvent::ACTION);

		if (in_array($action, $this->allowedActions, true))
		{
			return $action;
		}

		return '';
	}
}
