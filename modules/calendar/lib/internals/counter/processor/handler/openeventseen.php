<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\CounterTable;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;

class OpenEventSeen
{
	public function __invoke(array $categories, int $userId): void
	{
		$eventIds = [];
		$categoryIds = [];

		if (!$userId)
		{
			return;
		}

		if (empty($categories))
		{
			return;
		}

		foreach ($categories as $categoryId => $events)
		{
			$categoryIds[] = $categoryId;
			$eventIds = array_merge($eventIds, $events);
		}

		if (empty($eventIds))
		{
			return;
		}

		CounterTable::deleteByFilter([
			'EVENT_ID' => $eventIds,
			'USER_ID' => $userId,
		]);
		// this will run the Counter\Processor\Total which will update users' counters
		CounterService::addEvent(EventDictionary::OPEN_EVENT_SCORER_UPDATED, [
			'user_ids' => [$userId],
		]);

		(new OpenEventPushScorer())([$userId], $categoryIds);
	}
}
