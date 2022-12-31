<?php
namespace Bitrix\Calendar\Sync\Entities;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;

class SyncEvent implements Core\Base\EntityInterface
{
	/**
	 * @var Core\Event\Event
	 */
	protected Core\Event\Event $event;
	/**
	 * @var Sync\Connection\EventConnection|null
	 */
	protected ?Sync\Connection\EventConnection $eventConnection = null;
	/**
	 * @var InstanceMap|null
	 */
	protected ?InstanceMap $instanceMap = null;
	/**
	 * @var string|null
	 */
	protected ?string $action = null;

	/**
	 * @return Core\Event\Event
	 */
	public function getEvent(): Core\Event\Event
	{
		return $this->event;
	}

	/**
	 * @param Core\Event\Event $event
	 * @return SyncEvent
	 */
	public function setEvent(Core\Event\Event $event): SyncEvent
	{
		$this->event = $event;

		return $this;
	}

	/**
	 * @return Sync\Connection\EventConnection
	 */
	public function getEventConnection(): ?Sync\Connection\EventConnection
	{
		return $this->eventConnection;
	}

	/**
	 * @param Sync\Connection\EventConnection|null $eventConnection
	 *
	 * @return SyncEvent
	 */
	public function setEventConnection(?Sync\Connection\EventConnection $eventConnection): SyncEvent
	{
		$this->eventConnection = $eventConnection;

		return $this;
	}

	/**
	 * @param string|null $action
	 *
	 * @return $this
	 */
	public function setAction(?string $action): SyncEvent
	{
		$this->action = $action ?? '';

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction(): ?string
	{
		return $this->action;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->getEvent() ? $this->getEvent()->getId() : null;
	}

	/**
	 * @return int|null
	 */
	public function getEventId(): ?int
	{
		return $this->event->getId();
	}

	/**
	 * @return int|null
	 */
	public function getParentId(): ?int
	{
		return $this->event->getParentId();
	}

	/**
	 * @return string|null
	 */
	public function getEntityTag(): ?string
	{
		return $this->eventConnection->getEntityTag();
	}

	/**
	 * @return string
	 */
	public function getVendorEventId(): string
	{
		return $this->eventConnection->getVendorEventId();
	}

	/**
	 * @return string|null
	 */
	public function getUid(): ?string
	{
		return $this->event->getUid();
	}

	/**
	 * @return bool
	 */
	public function isRecurrence(): bool
	{
		return $this->event->isRecurrence();
	}

	/**
	 * @param InstanceMap|null $instanceMap
	 * @return SyncEvent
	 */
	public function setInstanceMap(?InstanceMap $instanceMap): SyncEvent
	{
		$this->instanceMap = $instanceMap;

		return $this;
	}

	/**
	 * @return InstanceMap|null
	 */
	public function getInstanceMap(): ?InstanceMap
	{
		return $this->instanceMap;
	}

	/**
	 * @return bool
	 */
	public function isInstance(): bool
	{
		return $this->event->isInstance();
	}

	/**
	 * @param SyncEvent $instance
	 *
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function addInstance(SyncEvent $instance): self
	{
		if ($this->instanceMap === null)
		{
			$this->instanceMap = new InstanceMap();
		}

		if (!$this->event->getExcludedDateCollection())
		{
			$this->event->setExcludedDateCollection(new Core\Event\Properties\ExcludedDatesCollection());
		}

		$instance->getEvent()->getOriginalDateFrom()
			? $this->updateExcludedDatesMasterEvent($instance->getEvent()->getOriginalDateFrom())
			: $this->updateExcludedDatesMasterEvent($instance->getEvent()->getStart())
		;

		$this->instanceMap->add($instance);

		return $this;
	}

	/**
	 * @param array $list
	 *
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function addInstanceList(array $list): SyncEvent
	{
		if ($this->instanceMap === null)
		{
			$this->instanceMap = new InstanceMap();
		}

		/** @var SyncEvent $item */
		foreach ($list as $item)
		{
			$this->addInstance($item);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasInstances(): bool
	{
		return $this->instanceMap !== null && $this->instanceMap->count();
	}

	/**
	 * @return string|null
	 */
	public function getVendorRecurrenceId(): ?string
	{
		if (!$this->eventConnection)
		{
			return null;
		}

		return $this->eventConnection->getRecurrenceId();
	}

	/**
	 * @return bool
	 */
	public function isSuccessAction(): bool
	{
		return $this->action === Sync\Dictionary::SYNC_SECTION_ACTION['success'];
	}

	/**
	 * @param Core\Base\Date $excludedDate
	 *
	 * @return void
	 */
	private function updateExcludedDatesMasterEvent(Core\Base\Date $excludedDate): void
	{
		$date = clone $excludedDate;
		$date->setDateTimeFormat(Core\Event\Properties\ExcludedDatesCollection::EXCLUDED_DATE_FORMAT);

		if ($this->event->getExcludedDateCollection())
		{
			$this->addExcludeDate($date);
		}
		else
		{
			$this->event->setExcludedDateCollection(new Core\Event\Properties\ExcludedDatesCollection([$date]));
		}
	}

	/**
	 * @return bool
	 */
	public function isBaseEvent(): bool
	{
		return $this->event->isBaseEvent();
	}

	/**
	 * @return bool
	 */
	public function isSingle(): bool
	{
		return $this->event->isSingle();
	}

	/**
	 * @param Core\Base\Date $newExDate
	 *
	 * @return void
	 */
	private function addExcludeDate(Core\Base\Date $newExDate)
	{
		/** @var Core\Base\Date $exDate */
		foreach ($this->event->getExcludedDateCollection() as $exDate)
		{
			if ($exDate->format('Ymd') === $newExDate->format('Ymd'))
			{
				return;
			}
		}

		$this->event->getExcludedDateCollection()->add($newExDate);
	}
}
