<?php

namespace Bitrix\Calendar\Sync\Handlers;

use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Event\Event;

class SyncEventMergeHandler extends Core\Handlers\HandlerBase
{
	/**
	 * @param SyncEvent $savedSyncEvent
	 * @param SyncEvent $externalSyncEvent
	 * @param int|null $id
	 * @param int|null $eventConnectionId
	 *
	 * @return SyncEvent
	 *
	 * @throws Core\Base\BaseException
	 */
	public function __invoke(
		SyncEvent $savedSyncEvent,
		SyncEvent $externalSyncEvent,
		?int $id = null,
		?int $eventConnectionId = null
	): SyncEvent
	{
		$localEvent = $savedSyncEvent->getEvent();
		$externalEvent = $externalSyncEvent->getEvent();

		$externalEvent
			->setOwner($localEvent->getOwner())
			->setSection($localEvent->getSection())
			->setId($id)
			->setCreator($localEvent->getCreator())
			->setCalendarType($localEvent->getCalendarType())
			->setSpecialLabel($localEvent->getSpecialLabel())
			->setMeetingDescription($localEvent->getMeetingDescription())
			->setEventHost($localEvent->getEventHost())
			->setAttendeesCollection($localEvent->getAttendeesCollection())
			->setIsMeeting($localEvent->isMeeting())
			->setMeetingStatus($localEvent->getMeetingStatus())
			->setRelations($localEvent->getRelations())
			->setVersion($localEvent->getVersion())
			->setRemindCollection($this->prepareReminders($localEvent, $externalEvent))
		;

		$externalEventConnection = $externalSyncEvent->getEventConnection();

		$externalEventConnection?->setId($eventConnectionId)
			->setVersion($localEvent->getVersion())
			->setRetryCount(0)
		;

		return $externalSyncEvent;
	}

	/**
	 * @param Event $localEvent
	 * @param Event $externalEvent
	 *
	 * @return Core\Event\Properties\RemindCollection|null
	 *
	 * @throws Core\Base\BaseException
	 */
	private function prepareReminders(Event $localEvent, Event $externalEvent): ?Core\Event\Properties\RemindCollection
	{
		$localRemindCollection = $localEvent->getRemindCollection();
		$externalRemindCollection = $externalEvent->getRemindCollection();
		if (!$externalRemindCollection || $externalRemindCollection->count() === 0)
		{
			if ($localRemindCollection)
			{
				// if reminders count more than one, we don't know which reminder was deleted,
				// and leave all of them
				return ($localRemindCollection->count() > 1)
					? $localEvent->getRemindCollection()
					: null
					;
			}
			else
			{
				return null;
			}
		}

		if ((!$localRemindCollection || $localRemindCollection->count() < 2))
		{
			return $externalEvent->getRemindCollection()->setSingle(false);
		}
		else
		{
			if ($externalRemindCollection->isSingle())
			{
				$this->removeClosestRemind($localRemindCollection);
				$localRemindCollection->add($externalRemindCollection->fetch());
				return $localRemindCollection;
			}
			else
			{
				return $this->mergeRemindCollections(
					$externalRemindCollection,
					$localRemindCollection,
				);
			}
		}
	}

	/**
	 * @param Core\Event\Properties\RemindCollection|null $remindCollection
	 *
	 * @return void
	 */
	private function removeClosestRemind(?Core\Event\Properties\RemindCollection $remindCollection)
	{
		$minValue = null;
		$minIndex = null;

		/** @var Core\Event\Properties\Remind $item */
		foreach ($remindCollection->getCollection() as $index => $item)
		{
			$offset = $item->getTimeBeforeStartInMinutes();
			if ($offset < 0)
			{
				continue;
			}

			if ($minValue === null || $offset < $minValue)
			{
				$minValue = $offset;
				$minIndex = $index;
			}
		}
		if ($minIndex !== null)
		{
			$remindCollection->remove($minIndex);
		}
	}

	/**
	 * @param Core\Event\Properties\RemindCollection $externalRemindCollection
	 * @param Core\Event\Properties\RemindCollection $localRemindCollection
	 *
	 * @return Core\Event\Properties\RemindCollection
	 */
	private function mergeRemindCollections(
		Core\Event\Properties\RemindCollection $externalRemindCollection,
		Core\Event\Properties\RemindCollection $localRemindCollection
	): Core\Event\Properties\RemindCollection
	{
		$prepareMap = function (?Core\Event\Properties\RemindCollection $collection)
		{
			$result = [];
			/** @var Core\Event\Properties\Remind $item */
			foreach ($collection as $index => $item)
			{
				$result[$item->getTimeBeforeStartInMinutes()] = $index;
			}
			return $result;
		};

		$externalMap = $prepareMap($externalRemindCollection);
		$localMap = $prepareMap($localRemindCollection);

		foreach ($localMap as $key => $index)
		{
			if (!array_key_exists($key, $externalMap))
			{
				$localRemindCollection->remove($index);
			}
		}

		return $localRemindCollection->addItems($externalRemindCollection->getCollection());
	}
}
