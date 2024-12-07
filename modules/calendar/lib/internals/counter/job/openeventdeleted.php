<?php

namespace Bitrix\Calendar\Internals\Counter\Job;

use Bitrix\Calendar\Internals\Counter\CounterTable;
use Bitrix\Calendar\Internals\Counter\Processor;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Main\Update\Stepper;

/**
 * OpenEventDeleted is the stepper that runs right after an open event is deleted
 * It is supposed to drop counters for all affected users
 */
final class OpenEventDeleted extends Stepper
{
	private const LIMIT = 50;
	protected static $moduleId = 'calendar';

	public function execute(array &$option): bool
	{
		$outerParams = $this->getOuterParams();
		$offset = (int)($option['offset'] ?? 0);
		$eventId = (int)($outerParams[0] ?? null);
		$categoryId = (int)($outerParams[1] ?? null);

		$userIds = $this->getAffectedUserIds(eventId: $eventId, offset: $offset);

		if (empty($userIds))
		{
			return self::FINISH_EXECUTION;
		}

		$this
			->dropCounter(eventId: $eventId, categoryId: $categoryId, userIds: $userIds)
			->setOptions(options: $option, offset: $offset)
		;

		return self::CONTINUE_EXECUTION;
	}

	private function getAffectedUserIds(int $eventId, int $offset): array
	{
		$userIds = [];

		try
		{
			$query = CounterTable::query()
				->setSelect(['USER_ID'])
				->where('EVENT_ID', '=', $eventId)
				->setLimit($this->getLimit())
				->setOffset($offset)
				->exec()
			;

			foreach ($query->fetchAll() as $counter)
			{
				$userIds[] = (int)$counter['USER_ID'];
			}
		}
		catch (\Exception $exception)
		{
			(new Logger())->log($exception);
		}

		return $userIds;
	}

	private function dropCounter(int $eventId, int $categoryId, array $userIds): self
	{
		(new Processor\OpenEvent())->dropCounter(userIds: $userIds, eventId: $eventId, categoryId: $categoryId);

		return $this;
	}

	private function setOptions(array &$options, int $offset): self
	{
		$options['offset'] = $offset + $this->getLimit();

		return $this;
	}

	private function getLimit(): int
	{
		$limit = \COption::GetOptionString('calendar', 'calendarCounterStepperLimit', '');

		return $limit === ''
			? self::LIMIT
			: (int)$limit
			;
	}
}
