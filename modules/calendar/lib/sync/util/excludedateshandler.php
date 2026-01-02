<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\Map;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Entities\SyncEvent;

class ExcludeDatesHandler
{
	/**
	 * @param Event $event
	 * @param Map|null $exceptionEvents
	 *
	 * @return void
	 *
	 * @deprecated Use \Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection::removeDateFromCollection
	 */
	public function prepareEventExcludeDates(Event $event, ?Core\Base\Map $exceptionEvents)
	{
		if (
			$exceptionEvents === null
			|| $exceptionEvents->count() === 0
			|| !$event->getExcludedDateCollection()
			|| $event->getExcludedDateCollection()->count() === 0
		)
		{
			return;
		}

		/** @var Core\Base\Date $date */
		foreach ($event->getExcludedDateCollection() as $key => $date)
		{
			$formattedDate = $date->format('Ymd');

			if (!$exceptionEvents->has($formattedDate))
			{
				continue;
			}

			$event->getExcludedDateCollection()->remove($key);

			/** @var SyncEvent|int $instance */
			$instance = $exceptionEvents->getItem($formattedDate);

			$untilDate = $event->getRecurringRule()?->getUntil();

			if ($untilDate && $instance instanceof SyncEvent)
			{
				$instanceEvent = $instance->getEvent();

				$dateFrom = $instanceEvent->getOriginalDateFrom() ?: $instanceEvent->getStart();

				// Office 365 does not support instances later than until date of recurrence. Simulated this behavior
				if ($untilDate->getTimestamp() < $dateFrom->getTimestamp())
				{
					$untilDate = clone $dateFrom;
				}

				$event->getRecurringRule()?->setUntil($untilDate);
			}
		}
	}
}
