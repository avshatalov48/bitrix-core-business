<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Event;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;

class Factory
{
	public static function buildEvent(string $hitId, string $type, array $data = [], int|null $eventId = null): Event
	{
		switch ($type)
		{
			case EventDictionary::EVENT_WORKGROUP_ADD:
			case EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_DELETE:
				$event = new WorkgroupEvent($hitId, $type);
				break;
			default:
				$event = new Event($hitId, $type);
		}

		$event->setData($data);

		if ($eventId)
		{
			$event->setId($eventId);
		}

		return $event;
	}
}