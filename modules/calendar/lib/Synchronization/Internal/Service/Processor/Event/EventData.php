<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\InstanceMap;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;

class EventData
{
	/**
	 * @var array<string, static>
	 */
	protected array $instances = [];

	/**
	 * Map: vendorEventId => key from the "instances" field
	 *
	 * @var array<string, string>
	 */
	protected array $instanceKeyMap = [];

	public function __construct(public readonly EventConnection $eventConnection)
	{
	}

	public function addInstance(EventData $instance): void
	{
		$event = $this->getEvent();

		$date = $instance->getEvent()->getOriginalDateFrom() ?: $instance->getEvent()->getStart();

		$excludedDate = clone $date;
		$excludedDate->setDateTimeFormat(ExcludedDatesCollection::EXCLUDED_DATE_FORMAT);

		$event->addExcludedDate($excludedDate);

		$key = InstanceMap::getKeyByDate($date);

		$this->instances[$key] = $instance;

		if ($instance->eventConnection->getVendorEventId())
		{
			$this->instanceKeyMap[$instance->eventConnection->getVendorEventId()] = $key;
		}
	}

	/**
	 * @return array<string, static>
	 */
	public function getInstances(): array
	{
		return $this->instances;
	}

	/**
	 * @param array<string, static> $instances
	 */
	public function setInstances(array $instances): self
	{
		$this->instances = [];
		$this->instanceKeyMap = [];

		foreach ($instances as $instance)
		{
			$this->addInstance($instance);
		}

		return $this;
	}

	public function getSameInstance(EventData $instance): ?static
	{
		if ($key = $this->getInstanceKey($instance))
		{
			return $this->instances[$key] ?? null;
		}

		return null;
	}

	private function getInstanceKey(EventData $instance): ?string
	{
		if (empty($this->instances))
		{
			return null;
		}

		if ($instance->getEvent()->getOriginalDateFrom())
		{
			return InstanceMap::getKeyByDate($instance->getEvent()->getOriginalDateFrom());
		}

		// For Office 365. Because MS API not returns the originalStart field
		return $this->getInstanceKeyFromEventConnection($instance->eventConnection);
	}

	public function getInstanceKeyFromEventConnection(EventConnection $eventConnection): ?string
	{
		if ($eventConnection->getRecurrenceId())
		{
			$vendorEventId = $eventConnection->getVendorEventId();

			return $this->instanceKeyMap[$vendorEventId] ?? null;
		}

		return null;
	}

	public function getEvent(): Event
	{
		return $this->eventConnection->getEvent();
	}

	public function getAction(): ?string
	{
		return $this->eventConnection->getLastSyncStatus();
	}

	public function isDeleted(): bool
	{
		return $this->getAction() === Dictionary::SYNC_EVENT_ACTION['delete'];
	}
}
