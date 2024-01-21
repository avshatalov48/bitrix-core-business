<?php

namespace Bitrix\Calendar\Core\Managers;

use Bitrix\Calendar\Internals\EventOriginalRecursionTable;

final class EventOriginalRecursion
{
	public function add(int $eventId, int $originalRecursionEventId): void
	{
		if ($eventId > 0 && $originalRecursionEventId > 0)
		{
			$insertFields = [
				'PARENT_EVENT_ID' => $eventId,
				'ORIGINAL_RECURSION_EVENT_ID' => $originalRecursionEventId,
			];
			$updateFields = [
				'ORIGINAL_RECURSION_EVENT_ID' => $originalRecursionEventId,
			];

			EventOriginalRecursionTable::merge($insertFields, $updateFields);
		}
	}
}