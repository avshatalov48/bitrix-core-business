<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event;

use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Main\Entity\EntityCollection;

class LocalEventMap
{
	/**
	 * @var EventData[]
	 */
	protected array $items = [];

	public function __construct(EntityCollection $eventConnections)
	{
		$this->buildMap($eventConnections);
	}

	private function buildMap(EntityCollection $eventConnections): void
	{
		$orphans = [];

		/** @var EventConnection $eventConnection */
		foreach ($eventConnections as $eventConnection)
		{
			$data = new EventData($eventConnection);

			$id = $eventConnection->getVendorEventId();
			$recurrenceId = $eventConnection->getRecurrenceId();

			if ($eventConnection->getEvent()->isInstance())
			{
				if ($recurrenceId)
				{
					if ($this->has($recurrenceId))
					{
						$this->addInstance($data, $recurrenceId);
					}
					else
					{
						$orphans[$recurrenceId][$id] = $data;
					}
				}

				continue;
			}

			if (!empty($orphans[$id]) && $eventConnection->getEvent()->isRecurrence())
			{
				$data->setInstances($orphans[$id]);
			}

			$this->add($id, $data);
		}

		$this->addOrphans($orphans);
	}

	private function addOrphans(array $orphans): void
	{
		foreach ($orphans as $collection)
		{
			/** @var EventData $eventData */
			foreach ($collection as $eventData)
			{
				$this->add($eventData->eventConnection->getVendorEventId(), $eventData);
			}
		}
	}

	public function add(string $id, EventData $data): void
	{
		$this->items[$id] = $data;
	}

	public function has(string $key): bool
	{
		return isset($this->items[$key]);
	}

	public function get(string $key): ?EventData
	{
		return $this->items[$key] ?? null;
	}

	private function addInstance(EventData $instance, string $masterId): void
	{
		if (!$this->has($masterId))
		{
			// @todo Add correct exception
			throw new \LogicException();
		}

		$this->items[$masterId]->addInstance($instance);
	}

	public function remove(string $key): void
	{
		if ($this->has($key))
		{
			unset($this->items[$key]);
		}
	}
}
