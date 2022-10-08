<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Entities\SyncEvent;

class BuidlerSyncEventFromExternalEvent implements Builder
{
	private array $externalEvent;
	private Event $event;
	private Connection $connection;

	public function __construct(array $externalEvent, Event $event, Connection $connection)
	{
		$this->externalEvent = $externalEvent;
		$this->event = $event;
		$this->connection = $connection;
	}

	/**
	 * @return SyncEvent
	 */
	public function build(): SyncEvent
	{
		$syncEvent = new SyncEvent();
		$event = (new BuilderEventWithLocalEvent($this->externalEvent, $this->event))->build();
		$eventConnection = (new BuilderEventConnectionFromExternalEvent(
			$this->externalEvent,
			$this->event,
			$this->connection
		))->build();

		$syncEvent->setEvent($event);
		$syncEvent->setEventConnection($eventConnection);

		return $syncEvent;
	}
}
