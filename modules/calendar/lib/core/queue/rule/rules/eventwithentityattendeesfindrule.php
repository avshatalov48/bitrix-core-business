<?php

namespace Bitrix\Calendar\Core\Queue\Rule\Rules;

use Bitrix\Calendar\Core\Queue\Queue;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\Dictionary;

class EventWithEntityAttendeesFindRule extends DbRule
{
	private const ROUTING_KEY = 'calendar:find_events_with_entity_attendees';

	/**
	 * @param Interfaces\Message $message
	 * @return Queue\Queue|null
	 */
	protected function getTargetQueue(Interfaces\Message $message): ?Queue\Queue
	{
		$routingKey = $message->getHeaders()[Dictionary::HEADER_KEYS['routingKey']] ?? null;
		if ($routingKey === self::ROUTING_KEY)
		{
			return (new Queue\QueueFactory())->getById(Queue\QueueRegistry::QUEUE_LIST['EventsWithEntityAttendeesFind']);
		}

		return null;
	}

	/**
	 * @param Interfaces\Message $message
	 * @return string
	 */
	protected function getMessageHash(Interfaces\Message $message): string
	{
		$data = $message->getBody();
		$hash = 'calendar_attendee_entity_' . $data['entityType'];
		if ($data['entityId'])
		{
			$hash .= '_' . $data['entityId'];
		}

		return $hash;
	}
}