<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\Map;
use Bitrix\Calendar\Core\Event\Event;

class ExcludeDatesHandler
{
	/**
	 * @param Event $event
	 * @param Map|null $exceptionEvents
	 *
	 * @return void
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
			if ($exceptionEvents->has($date->format('Ymd')))
			{
				$event->getExcludedDateCollection()->remove($key);
			}
		}
	}
}