<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Connection\Connection;

class EventConnection implements \Bitrix\Main\Entity\EntityInterface
{
	private ?int $id = null;

	private ?Event $event = null;

	private ?Connection $connection = null;

	private string $vendorEventId = '';

	private string $syncStatus = '';

	private int $retryCount = 0;

	private ?string $entityTag = null;

	private int $version = 0;

	private ?string $vendorVersionId = null;

	private ?string $recurrenceId = null;

	private ?array $data = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function isNew(): bool
	{
		return $this->id === null;
	}

	public function getEvent(): Event
	{
		return $this->event;
	}

	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function setConnection(Connection $connection): self
	{
		$this->connection = $connection;

		return $this;
	}

	public function getVendorEventId(): string
	{
		return $this->vendorEventId;
	}

	public function setVendorEventId(string $vendorEventId): self
	{
		$this->vendorEventId = $vendorEventId;

		return $this;
	}

	public function getLastSyncStatus(): string
	{
		return $this->syncStatus;
	}

	public function setLastSyncStatus(string $lastSyncStatus): self
	{
		$this->syncStatus = $lastSyncStatus;

		return $this;
	}

	public function getRetryCount(): int
	{
		return $this->retryCount;
	}

	public function setRetryCount(int $retryCount = 0): EventConnection
	{
		$this->retryCount = $retryCount;

		return $this;
	}

	public function getEntityTag(): ?string
	{
		return $this->entityTag;
	}

	public function setEntityTag(?string $entityTag = null): self
	{
		$this->entityTag = $entityTag;

		return $this;
	}

	public function getVersion(): int
	{
		return $this->version;
	}

	public function setVersion(int $version): self
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @deprecated Use getVersion()
	 */
	public function getEventVersion(): ?string
	{
		return $this->getVersion() === 0 ? null : (string)$this->getVersion();
	}

	public function getVendorVersionId(): ?string
	{
		return $this->vendorVersionId;
	}

	public function setVendorVersionId(?string $versionId): self
	{
		$this->vendorVersionId = $versionId;

		return $this;
	}

	public function getRecurrenceId(): ?string
	{
		return $this->recurrenceId;
	}

	public function setRecurrenceId(?string $id): self
	{
		$this->recurrenceId = $id;

		return $this;
	}

	public function getData(): ?array
	{
		return $this->data;
	}

	public function setData($data): self
	{
		$this->data = $data;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'EVENT_ID' => $this->getEvent()->getId(),
			'CONNECTION_ID' => $this->getConnection()->getId(),
			'VENDOR_EVENT_ID' => $this->getVendorEventId(),
			'SYNC_STATUS' => $this->getLastSyncStatus(),
			'RETRY_COUNT' => $this->getRetryCount(),
			'ENTITY_TAG' => $this->getEntityTag(),
			'VERSION' => $this->getVersion(),
			'VENDOR_VERSION_ID' => $this->getVendorVersionId(),
			'RECURRENCE_ID' => $this->getRecurrenceId(),
			'DATA' => $this->getData(),
		];
	}

	public function hasIdenticalVersion(EventConnection $comparable): bool
	{
		return $comparable->getEntityTag() === $this->entityTag
			|| $comparable->getVendorVersionId() === $this->getVendorVersionId();
	}
}
