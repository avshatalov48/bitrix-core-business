<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor\AbstractPreProcessor;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait\AttendeeCodeTrait;

final class CalendarPreProcessor extends AbstractPreProcessor
{
	use AttendeeCodeTrait;

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
		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_CALENDAR_EVENT_UPD:
				$this->onEventUpdate();
				break;
			default:
				break;
		}
	}

	private function onEventUpdate(): void
	{
		$data = $this->event->getData();
		$eventId = (int)($data['ID'] ?? null);

		if ($eventId <= 0)
		{
			return;
		}

		$attendeesCodesBeforeUpdate = $this->getArrayValueFromEventData('ATTENDEES_CODES_BEFORE_UPDATE');
		$attendeesCodesAfterUpdate = $this->getArrayValueFromEventData('ATTENDEES_CODES_AFTER_UPDATE');
		$attendeesBeforeUpdate = $this->getArrayValueFromEventData('ATTENDEES_BEFORE_UPDATE');
		$attendeesAfterUpdate = $this->getArrayValueFromEventData('ATTENDEES_AFTER_UPDATE');

		if (
			!is_array($attendeesCodesBeforeUpdate)
			|| !is_array($attendeesCodesAfterUpdate)
			|| !is_array($attendeesBeforeUpdate)
			|| !is_array($attendeesAfterUpdate)
		)
		{
			return;
		}

		Collection::normalizeArrayValuesByInt($attendeesBeforeUpdate);
		Collection::normalizeArrayValuesByInt($attendeesAfterUpdate);

		$this->processRemovedFromAttendeesSpaces(
			$eventId,
			$attendeesCodesBeforeUpdate,
			$attendeesCodesAfterUpdate,
			$attendeesBeforeUpdate,
		);

		$this->processRemovedFromAttendeesUsers($eventId, $attendeesBeforeUpdate, $attendeesAfterUpdate);
	}

	private function processRemovedFromAttendeesSpaces(
		int $eventId,
		array $attendeesCodesBeforeUpdate,
		array $attendeesCodesAfterUpdate,
		array $attendeesBeforeUpdate,
	): void
	{
		if ($eventId <= 0)
		{
			return;
		}

		sort($attendeesCodesBeforeUpdate);
		sort($attendeesCodesAfterUpdate);

		if ($attendeesCodesBeforeUpdate === $attendeesCodesAfterUpdate)
		{
			return;
		}

		$removedAttendeesCodes = array_diff($attendeesCodesBeforeUpdate, $attendeesCodesAfterUpdate);

		$spaceIdsToReload = [];
		foreach ($removedAttendeesCodes as $attendeeCode)
		{
			$spaceId = $this->getGroupIdFromAttendeeCode($attendeeCode);
			if ($spaceId > 0)
			{
				$this->service->deleteBySpaceId($spaceId, $this->getTypeId(), $eventId);
				$spaceIdsToReload[] = $spaceId;
			}
		}

		$this->pushEvent(
			$attendeesBeforeUpdate,
			PushEventDictionary::EVENT_SPACE_RECENT_ACTIVITY_REMOVE_FROM_SPACE,
			['spaceIdsToReload' => $spaceIdsToReload],
		);
	}

	private function processRemovedFromAttendeesUsers(int $eventId, array $attendeesBeforeUpdate, array $attendeesAfterUpdate): void
	{
		if ($attendeesBeforeUpdate === $attendeesAfterUpdate)
		{
			return;
		}

		$lostAccessUsers = array_values(array_diff($attendeesBeforeUpdate, $attendeesAfterUpdate));

		if (empty($lostAccessUsers))
		{
			return;
		}

		\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
			EventDictionary::EVENT_SPACE_CALENDAR_EVENT_REMOVE_USERS,
			[
				'ID' => $eventId,
				'RECEPIENTS' => $lostAccessUsers,
			]
		);
	}

	private function getArrayValueFromEventData(string $code): ?array
	{
		$data = $this->event->getData();

		return is_array($data[$code]) ? $data[$code] : null;
	}
}
