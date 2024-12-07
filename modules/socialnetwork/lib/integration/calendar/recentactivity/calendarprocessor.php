<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\AbstractProcessor;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\AccessCodeTrait;

final class CalendarProcessor extends AbstractProcessor
{
	use AccessCodeTrait;

	public function isAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['calendar'];
	}

	public function process(): void
	{
		$eventId = (int)($this->event->getData()['ID'] ?? null);

		if ($eventId <= 0)
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_CALENDAR_EVENT_DEL:
				$this->onEventDelete($eventId);
				break;
			case EventDictionary::EVENT_SPACE_CALENDAR_EVENT_REMOVE_USERS:
				$this->onEventRemoveUsers($eventId);
				break;
			default:
				$this->onDefaultEvent($eventId);
				break;
		}
	}

	private function onDefaultEvent(int $eventId): void
	{
		$attendeeCodes = $this->event->getData()['ATTENDEES_CODES'] ?? null;

		if (is_string($attendeeCodes))
		{
			$attendeeCodes = explode(',', $attendeeCodes);
		}
		else
		{
			$attendeeCodes = [];
		}

		$spaceIds = $this->getSpaceIdsFromCodes($attendeeCodes, $this->recipient);

		$spaceIds = array_unique($spaceIds);
		foreach ($spaceIds as $spaceId)
		{
			$this->saveRecentActivityData($spaceId, $eventId);
		}
	}

	private function onEventDelete(int $eventId): void
	{
		$this->deleteRecentActivityData($eventId);
	}

	private function onEventRemoveUsers(int $eventId): void
	{
		$this->deleteRecentActivityData($eventId);
	}
}
