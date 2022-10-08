<?php

namespace Bitrix\Calendar\Sync\Connection;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;

class SectionConnection implements EntityInterface
{
	/** @var int|null */
	private ?int $id = null;
	/** @var string|null */
	private ?string $vendorSectionId = null;
	/** @var string|null */
	private ?string $syncToken = null;
	/** @var string|null */
	private ?string $pageToken = null;
	/** @var Core\Section\Section|null */
	private ?Core\Section\Section $section = null;
	/** @var Sync\Connection\Connection|null */
	private ?Connection $connection = null;
	/** @var bool */
	private bool $active = true;
	/** @var Date|null */
	private ?Date $lastSyncDate = null;
	/** @var string|null */
	private ?string $lastSyncStatus = '';
	/** @var string|null */
	private ?string $versionId = null;
	/** @var bool  */
	private bool $primary = false;
	/**
	 * @var Role|null
	 */
	protected ?Role $owner = null;

	/**
	 * @return Connection|null
	 */
	public function getConnection(): ?Sync\Connection\Connection
	{
		return $this->connection;
	}

	/**
	 * @return string|null
	 */
	public function getPageToken(): ?string
	{
		return $this->pageToken;
	}

	/**
	 * @return string|null
	 */
	public function getSyncToken(): ?string
	{
		return $this->syncToken;
	}

	/**
	 * @param string|null $token
	 * @return $this
	 */
	public function setPageToken(?string $token): self
	{
		$this->pageToken = $token;

		return $this;
	}

	/**
	 * @param string|null $token
	 * @return $this
	 */
	public function setSyncToken(?string $token): self
	{
		$this->syncToken = $token;

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @throws BaseException
	 * @throws ArgumentException
	 * @deprecated you should not use this method
	 */
	public function save(): self
	{
		if ($this->getId())
		{
			(new Core\Mappers\SectionConnection())->update($this);
		}
		else
		{
			(new Core\Mappers\SectionConnection())->create($this);
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getVendorSectionId(): ?string
	{
		return $this->vendorSectionId;
	}

	/**
	 * @param Connection|null $connection
	 * @return $this
	 */
	public function setConnection(?Connection $connection): self
	{
		$this->connection = $connection;

		return $this;
	}

	/**
	 * @param string|null $vendorSectionId
	 *
	 * @return SectionConnection
	 */
	public function setVendorSectionId(?string $vendorSectionId): self
	{
		$this->vendorSectionId = $vendorSectionId;

		return $this;
	}

	/**
	 * @return Section
	 */
	public function getSection(): ?Section
	{
		return $this->section;
	}

	/**
	 * @param Section|null $section
	 *
	 * @return SectionConnection
	 */
	public function setSection(?Section $section): self
	{
		$this->section = $section;

		return $this;
	}

	/**
	 * @param bool $active
	 *
	 * @return SectionConnection
	 */
	public function setActive(bool $active): self
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * @param Core\Base\Date|null $lastSyncDate
	 *
	 * @return $this
	 */
	public function setLastSyncDate(?Core\Base\Date $lastSyncDate): self
	{
		$this->lastSyncDate = $lastSyncDate;

		return $this;
	}

	/**
	 * @return ?DateTime
	 */
	public function getLastSyncDate(): ?Core\Base\Date
	{
		return $this->lastSyncDate;
	}

	/**
	 * @param string|null $lastSyncStatus
	 *
	 * @return SectionConnection
	 */
	public function setLastSyncStatus(?string $lastSyncStatus): self
	{
		$this->lastSyncStatus = $lastSyncStatus;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastSyncStatus(): string
	{
		return $this->lastSyncStatus ?? '';
	}

	/**
	 * @param string|null $versionId
	 *
	 * @return SectionConnection
	 */
	public function setVersionId(?string $versionId): self
	{
		$this->versionId = $versionId;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getVersionId(): ?string
	{
		return $this->versionId;
	}

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public function setId(int $id): self
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
	 * @return bool
	 */
	public function isPrimary(): bool
	{
		return $this->primary;
	}

	/**
	 * @param bool $primary
	 *
	 * @return SectionConnection
	 */
	public function setPrimary(bool $primary): self
	{
		$this->primary = $primary;

		return $this;
	}

	/**
	 * @param Role|null $owner
	 * @return $this
	 */
	public function setOwner(?Role $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * @return Role|null
	 */
	public function getOwner(): ?Role
	{
		return $this->owner;
	}

	/**
	 * @return bool
	 */
	public function isNew(): bool
	{
		return $this->id === null;
	}
}
