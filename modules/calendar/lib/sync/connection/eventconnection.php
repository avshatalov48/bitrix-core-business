<?php

namespace Bitrix\Calendar\Sync\Connection;

use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Event\Event;

class EventConnection implements EntityInterface
{
	/** @var int */
	private int $version = 0;
	/** @var Event|null */
	private ?Event $event = null;
	/** @var string */
	private string $vendorEventId = '';
	/** @var Connection|null */
	private ?Connection $connection = null;
	/** @var string */
	private string $lastSyncStatus = '';
	/** @var int  */
	private int $retryCount = 0;
	/** @var string | null */
	private ?string $entityTag = null;
	/** @var array | null */
	private ?array $data = null;
	/** @var string|null */
	private ?string $recurrenceId = null;
	/** @var int|null  */
	private ?int $id = null;
	/** @var string|null  */
	private ?string $vendorVersionId = null;

	/**
	 * @param int|null $id
	 *
	 * @return $this
	 */
	public function setId(?int $id): EventConnection
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return Event
	 */
	public function getEvent(): Event
	{
		return $this->event;
	}

	/**
	 * @param Event $event
	 *
	 * @return EventConnection
	 */
	public function setEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVendorEventId(): string
	{
		return $this->vendorEventId;
	}

	/**
	 * @param string $vendorEventId
	 * @return EventConnection
	 */
	public function setVendorEventId(string $vendorEventId): self
	{
		$this->vendorEventId = $vendorEventId;

		return $this;
	}

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * @param Connection $connection
	 *
	 * @return EventConnection
	 */
	public function setConnection(Connection $connection): self
	{
		$this->connection = $connection;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastSyncStatus(): string
	{
		return $this->lastSyncStatus;
	}

	/**
	 * @param string $lastSyncStatus
	 *
	 * @return EventConnection
	 */
	public function setLastSyncStatus(string $lastSyncStatus): self
	{
		$this->lastSyncStatus = $lastSyncStatus;

		return $this;
	}

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public function setRetryCount(int $retryCount = 0): EventConnection
	{
		$this->retryCount = $retryCount;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getRetryCount(): int
	{
		return $this->retryCount;
	}

	/**
	 * @return string
	 */
	public function getEntityTag(): ?string
	{
		return $this->entityTag;
	}

	/**
	 * @return string
	 */
	public function getVendorVersionId(): ?string
	{
		return $this->vendorVersionId;
	}

	/**
	 * @param string|null $versionId
	 *
	 * @return EventConnection
	 */
	public function setVendorVersionId(?string $versionId): self
	{
		$this->vendorVersionId = $versionId;

		return $this;
	}

	/**
	 * @param string|null $entityTag
	 *
	 * @return EventConnection
	 */
	public function setEntityTag(?string $entityTag = null): self
	{
		$this->entityTag = $entityTag;

		return $this;
	}

	/**
	 * @param int $version
	 * @return $this
	 */
	public function setVersion(int $version): self
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getVersion(): int
	{
		return $this->version;
	}

	public function getData(): ?array
	{
		return $this->data;
	}

	public function setData($data): EventConnection
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEventVersion(): ?string
	{
		return $this->getVersion();
	}

	/**
	 * @param string|null $id
	 * @return $this
	 */
	public function setRecurrenceId(?string $id): EventConnection
	{
		$this->recurrenceId = $id;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getRecurrenceId(): ?string
	{
		return $this->recurrenceId;
	}

	public function upVersion()
	{
		$this->version++;
	}
}
