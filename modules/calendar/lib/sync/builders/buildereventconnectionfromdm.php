<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Internals\EO_EventConnection;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;

class BuilderEventConnectionFromDM implements Builder
{
	/**
	 * @var EO_EventConnection
	 */
	private EO_EventConnection $entity;

	/**
	 * @param EO_EventConnection $link
	 */
	public function __construct(EO_EventConnection $link)
	{
		$this->entity = $link;
	}

	public function build(): EventConnection
	{
		return (new EventConnection())
			->setId($this->getId())
			->setEntityTag($this->getEntityTag())
			->setVendorVersionId($this->entity->getVendorVersionId())
			->setRecurrenceId($this->entity->getRecurrenceId())
			->setLastSyncStatus($this->getLastSyncStatus())
			->setVendorEventId($this->getVendorEventId())
			->setVersion($this->getVersion())
			->setData($this->entity->getData())
			->setConnection($this->getConnection())
			->setEvent($this->getEvent())
		;
	}

	private function getId(): int
	{
		return $this->entity->getId();
	}

	private function getEntityTag(): ?string
	{
		return $this->entity->getEntityTag();
	}

	private function getLastSyncStatus(): ?string
	{
		return $this->entity->getSyncStatus();
	}

	private function getVendorEventId(): ?string
	{
		return $this->entity->getVendorEventId();
	}

	private function getVersion(): ?string
	{
		return $this->entity->getVersion();
	}

	private function getConnection(): Connection
	{
		return (new BuilderConnectionFromDM($this->entity->getConnection()))->build();
	}

	private function getEvent(): Event
	{
		return (new EventBuilderFromEntityObject($this->entity->getEvent()))->build();
	}
}
