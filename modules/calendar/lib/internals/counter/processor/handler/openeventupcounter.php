<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\CounterTable;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;

class OpenEventUpCounter
{
	public function __invoke(array $userIds, CalendarEvent $event): void
	{
		$rows = [];

		if (empty($userIds))
		{
			return;
		}

		foreach ($userIds as $userId)
		{
			if (!$userId || $userId === $event->getCreator()?->getId())
			{
				continue;
			}

			$rows[] = [
				'PARENT_ID' => $event->getEventOption()->getCategoryId(),
				'EVENT_ID' => $event->getId(),
				'USER_ID' => $userId,
				'TYPE' => CounterDictionary::SCORER_OPEN_EVENT,
				'VALUE' => 1,
			];
		}

		if (empty($rows))
		{
			return;
		}

		CounterTable::addMulti($rows, true);
		// this will run the Counter\Processor\Total which will update users' counters
		CounterService::addEvent(EventDictionary::OPEN_EVENT_SCORER_UPDATED, [
			'user_ids' => $userIds,
		]);

		(new OpenEventPushScorer())($userIds, [$event->getEventOption()->getCategoryId()]);
	}
}
