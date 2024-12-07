<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\OpenEvent;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateSetEventsWatched extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();

		$eventIds = $request->get('eventIds');
		if (!$eventIds)
		{
			$this->addError(new Error(
				message: 'eventIds is required',
				code: 'event_ids_required',
				customData: ['field_name' => 'eventIds'],
			));
		}

		$uniqueIntegerEventIds = array_unique(array_filter(array_map('intval', $eventIds)));
		if (count($eventIds) !== count($uniqueIntegerEventIds))
		{
			$this->addError(new Error(
				message: 'eventIds invalid',
				code: 'event_ids_invalid',
				customData: ['field_name' => 'eventIds']
			));
		}

		if ($this->getErrors())
		{
			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}
}
