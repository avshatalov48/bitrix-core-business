<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Entities\SyncEvent;

class BuilderEventConnectionFromExternalEvent implements Builder
{
	private array $externalEvent;
	private SyncEvent $syncEvent;
	private Connection $connection;

	public function __construct(array $externalEvent, SyncEvent $syncEvent, Connection $connection)
	{
		$this->externalEvent = $externalEvent;
		$this->syncEvent = $syncEvent;
		$this->connection = $connection;
	}

	/**
	 * @return EventConnection
	 */
	public function build(): EventConnection
	{
		$id = null;
		if ($eventConnection = $this->syncEvent->getEventConnection())
		{
			$id = $eventConnection->getId();
		}

		return (new EventConnection())
			->setEntityTag($this->externalEvent['etag'] ?? null)
			->setVendorEventId($this->externalEvent['id'] ?? null)
			->setVendorVersionId($this->externalEvent['sequence'] ?? null)
			->setVersion($this->syncEvent->getEvent()->getVersion())
			->setRecurrenceId($this->externalEvent['recurringEventId'] ?? null)
			->setRetryCount(0)
			->setLastSyncStatus('success')
			->setEvent($this->syncEvent->getEvent())
			->setConnection($this->connection)
			->setId($id)
		;
	}
}
