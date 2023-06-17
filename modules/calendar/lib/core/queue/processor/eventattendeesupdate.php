<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Interfaces\Message;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Core;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class EventAttendeesUpdate implements Interfaces\Processor
{
	/**
	 * @param Message $message
	 * @return string
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Exception
	 */
	public function process(Interfaces\Message $message): string
	{
		$data = $message->getBody();

		if (empty($data['eventId']))
		{
			return self::REJECT;
		}

		$eventId = (int)$data['eventId'];

		$eventArray = EventTable::getList([
			'select' => ['*'],
			'filter' => \Bitrix\Main\ORM\Query\Query::filter()
				->where('DELETED', 'N')
				->where('ID', $eventId)
			,
		])->fetch();

		if (!$eventArray)
		{
			return self::REJECT;
		}

		$event = (new EventBuilderFromArray($eventArray))->build();

		$fields = [
			'ID' => $event->getId(),
			'ATTENDEES' => \CCalendar::GetDestinationUsers($event->getAttendeesCollection()->getAttendeesCodes()),
			'REMIND' => unserialize($eventArray['REMIND'], ['allowed_classes' => false]),
		];

		if (
			$event->getCalendarType() === Core\Event\Tools\Dictionary::CALENDAR_TYPE['group']
			&& Loader::includeModule('socialnetwork')
		)
		{
			$fields = $this->prepareGroupEventData($event, $fields);
		}

		if ($event->getRecurringRule())
		{
			$previousAttendees = $this->getEventPreviousAttendees($eventId);
			$result = $this->saveRecurrentEvent($event, $fields, $eventArray);
		}
		else
		{
			$result = $this->saveNotRecurrentEvent($event, $fields);
		}

		if ($result && $this->wasEventOwnerUpdated($fields))
		{
			$newHostEventData = $this->getNewHostEventData($fields, $result);
		}

		if (
			$result
			&& Loader::includeModule('im')
			&& $event->getMeetingDescription()
			&& !empty($event->getMeetingDescription()->getFields()['CHAT_ID'])
		)
		{
			$currentAttendees = $fields['ATTENDEES'];
			$this->updateEventChat(
				$event->getMeetingDescription()->getFields()['CHAT_ID'],
				$fields['MEETING_HOST'] ?? null,
				array_diff($previousAttendees ?? [], $currentAttendees),
			);
		}

		if (!empty($newHostEventData))
		{
			$this->updateNewMeetingHostStatus($newHostEventData);
			$this->updateCreatedByFieldInGroupEvent($result, $fields['MEETING_HOST']);
			\CCalendar::ClearCache();
		}

		return self::ACK;
	}

	/**
	 * @param Event $event
	 * @param array $fields
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function prepareGroupEventData(Event $event, array $fields): array
	{
		$groupMembers = \Bitrix\Socialnetwork\UserToGroupTable::query()
			->setSelect(['USER_ID', 'ROLE'])
			->addFilter('GROUP_ID', $event->getOwner()->getId())
			->fetchAll()
		;

		foreach ($groupMembers as $groupMember)
		{
			if ($event->getEventHost() && (int)($groupMember['USER_ID'] ?? null) === $event->getEventHost()->getId())
			{
				$eventHostId = (int)$groupMember['USER_ID'];
			}
			if (($groupMember['ROLE'] ?? null) === \Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER)
			{
				$groupOwnerId = (int)$groupMember['USER_ID'];
			}
		}

		$isEventHostGroupMember = !empty($eventHostId);

		if (!$isEventHostGroupMember && !empty($groupOwnerId) && $event->getMeetingDescription())
		{
			$fields = $this->prepareGroupEventWithNewHostFields($event, $groupOwnerId, $fields);
		}

		return $fields;
	}

	/**
	 * @param Event $event
	 * @param int $groupOwnerId
	 * @param array $fields
	 * @return array
	 */
	protected function prepareGroupEventWithNewHostFields(Event $event, int $groupOwnerId, array $fields): array
	{
		$attendeesCodes = $event->getAttendeesCollection()->getAttendeesCodes();
		$fields['ATTENDEES_CODES'] = $this->removePreviousMeetingHostFromAttendeesCodes($attendeesCodes, $event);

		$fields['ATTENDEES'] = \CCalendar::GetDestinationUsers($fields['ATTENDEES_CODES']);

		if (is_array($fields['ATTENDEES']) && !in_array($groupOwnerId, $fields['ATTENDEES'], true))
		{
			$fields['ATTENDEES'][] = $groupOwnerId;
			$fields['ATTENDEES_CODES'] = array_merge(['U'. $groupOwnerId],$fields['ATTENDEES_CODES']);
		}
		$fields['ATTENDEES'] = array_unique($fields['ATTENDEES']);

		$groupOwner = \CUser::GetByID($groupOwnerId)->Fetch();
		$fields['MEETING_HOST'] = $groupOwnerId;

		$meetingFields = $event->getMeetingDescription()->getFields();
		$meetingFields['HOST_NAME'] = ltrim($groupOwner['NAME'] . ' ' . $groupOwner['LAST_NAME']);
		$meetingFields['MEETING_CREATOR'] = $groupOwnerId;

		$fields['MEETING'] = $meetingFields;

		return $fields;
	}

	/**
	 * @param array $attendeesCodes
	 * @param Event $event
	 * @return array
	 */
	protected function removePreviousMeetingHostFromAttendeesCodes(array $attendeesCodes, Event $event): array
	{
		return array_filter(
			$attendeesCodes,
			static function ($attendeeCode) use ($event)
			{
				return $attendeeCode !== 'U' . $event->getEventHost()->getId();
			},
		);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected function getEventPreviousAttendees(int $parentEventId): array
	{
		$events = EventTable::query()
			->addSelect('OWNER_ID')
			->addFilter('DELETED', 'N')
			->addFilter('PARENT_ID', $parentEventId)
			->addFilter('CAL_TYPE', Core\Event\Tools\Dictionary::CALENDAR_TYPE['user'])
			->fetchAll()
		;
		$previousAttendees = [];
		foreach ($events as $event)
		{
			$previousAttendees[] = (int)$event['OWNER_ID'];
		}

		return $previousAttendees;
	}

	/**
	 * @param array $fields
	 * @return bool
	 */
	protected function wasEventOwnerUpdated(array $fields): bool
	{
		return !empty($fields['MEETING_HOST']);
	}

	/**
	 * @param array $fields
	 * @param int $eventId
	 * @return array|false
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getNewHostEventData(array $fields, int $eventId)
	{
		return EventTable::query()
			->setSelect(['ID', 'MEETING_STATUS'])
			->addFilter('DELETED', 'N')
			->addFilter('PARENT_ID', $eventId)
			->addFilter('CAL_TYPE', Core\Event\Tools\Dictionary::CALENDAR_TYPE['user'])
			->addFilter('OWNER_ID', $fields['MEETING_HOST'])
			->fetch()
			;
	}

	/**
	 * @param int|null $chatId
	 * @param int|null $newMeetingHostId
	 * @param array $deletedAttendeesIds
	 * @return void
	 */
	protected function updateEventChat(
		?int $chatId,
		?int $newMeetingHostId,
		array $deletedAttendeesIds
	): void
	{
		if ($chatId > 0)
		{
			$chat = new \CIMChat($newMeetingHostId);
			if ($newMeetingHostId > 0)
			{
				$chat->SetOwner($chatId, $newMeetingHostId, false);
			}
			foreach ($deletedAttendeesIds as $deletedAttendeeId)
			{
				$chat->DeleteUser($chatId, $deletedAttendeeId, false);
			}
		}
	}

	/**
	 * @param array $newHostEventData
	 * @return void
	 * @throws \Exception
	 */
	protected function updateNewMeetingHostStatus(array $newHostEventData): void
	{
		if (!empty($newHostEventData['ID']) && !empty($newHostEventData['MEETING_STATUS']))
		{
			EventTable::update((int)$newHostEventData['ID'], [
				'MEETING_STATUS' => \Bitrix\Calendar\Core\Event\Tools\Dictionary::MEETING_STATUS['Yes'],
			]);
		}
	}

	/**
	 * @param int $eventId
	 * @param int $newOwnerId
	 * @return void
	 * @throws \Exception
	 */
	protected function updateCreatedByFieldInGroupEvent(int $eventId, int $newOwnerId): void
	{
		EventTable::update($eventId, [
			'CREATED_BY' => $newOwnerId,
		]);
	}

	/**
	 * @param Event $event
	 * @param array $fields
	 * @return array|bool|mixed|string|?n|null
	 */
	protected function saveNotRecurrentEvent(Event  $event, array $fields)
	{
		return \CCalendar::SaveEvent([
			'recursionEditMode' => 'skip',
			'overSaving' => true,
			'checkPermission' => false,
			'sendInvitations' => true,
			'arFields' => $fields,
			'userId' => $this->getSaveEventUserId($fields, $event),
		]);
	}

	/**
	 * @param Event $event
	 * @param array $fields
	 * @param array $eventArray
	 * @return bool|int|mixed|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function saveRecurrentEvent(Event $event, array $fields, array $eventArray)
	{
		$entries = [];
		\CCalendarEvent::ParseRecursion($entries, $eventArray, [
			'fromLimitTs' => time() - $eventArray['TZ_OFFSET_FROM'],
			'toLimitTs' => $eventArray['DATE_TO_TS_UTC'],
			'instanceCount' => 1,
			'loadLimit' => false,
			'preciseLimits' => true,
			'checkPermission' => false,
			'userId' => $this->getSaveEventUserId($fields, $event),
		]);

		if (!empty($entries))
		{
			$result = \CCalendar::SaveEventEx([
				'recursionEditMode' => 'next',
				'currentEventDateFrom' => $entries[0]['DATE_FROM'],
				'overSaving' => true,
				'checkPermission' => false,
				'sendInvitations' => true,
				'arFields' => $fields,
				'userId' => $this->getSaveEventUserId($fields, $event),
			]);

			return $result['recEventId'] ?? $result['id'] ?? null;
		}

		return 0;
	}

	/**
	 * @param array $fields
	 * @param Event $event
	 * @return int|mixed|null
	 */
	protected function getSaveEventUserId(array $fields, Event $event)
	{
		return $fields['MEETING_HOST'] ?? ($event->getEventHost() ? $event->getEventHost()->getId() : null);
	}
}