<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway;

use Bitrix\Calendar\Core\Builders\EventCloner;
use Bitrix\Calendar\Core\Event\Event;

class EventPreparer
{
	public function prepare(Event $event, string $vendorEventId): Event
	{
		$event = (new EventCloner($event))->build();
		$event->setUid($vendorEventId);

		return $event;
	}
}
