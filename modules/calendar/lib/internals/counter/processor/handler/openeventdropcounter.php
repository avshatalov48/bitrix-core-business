<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\CounterTable;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;

class OpenEventDropCounter
{
	public function __invoke(array $userIds, int $eventId, int $categoryId): void
	{
		if (empty($userIds) || !$eventId)
		{
			return;
		}

		CounterTable::deleteByFilter([
			'EVENT_ID' => $eventId,
			'USER_ID' => $userIds,
		]);
		// this will run the Counter\Processor\Total which will update users' counters
		CounterService::addEvent(EventDictionary::OPEN_EVENT_SCORER_UPDATED, [
			'user_ids' => $userIds,
		]);

		(new OpenEventPushScorer())($userIds, [$categoryId]);
	}
}
