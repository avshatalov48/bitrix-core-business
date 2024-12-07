<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\OpenEvent;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateSetEventAttendeeStatus extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();

		$eventId = $request->get('eventId');
		if (!$eventId)
		{
			$this->addError(new Error(
				message: 'eventId is required',
				code: 'event_id_required',
				customData: ['field_name' => 'eventId'],
			));
		}

		$eventId = (int)$eventId;
		if (!$eventId)
		{
			$this->addError(new Error(
				message: 'eventId invalid',
				code: 'event_id_invalid',
				customData: ['field_name' => 'eventId']
			));
		}

		$attendeeStatus = $request->get('attendeeStatus');
		if ($attendeeStatus === null)
		{
			$this->addError(new Error(
				message: 'attendeeStatus is required',
				code: 'attendee_status_required',
				customData: ['field_name' => 'attendeeStatus'],
			));
		}

		if ($this->getErrors())
		{
			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}
}
