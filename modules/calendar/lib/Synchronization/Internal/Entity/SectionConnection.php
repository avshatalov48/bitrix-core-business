<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Main\Entity\EntityInterface;

class SectionConnection implements EntityInterface
{
	private ?int $id = null;

	private ?string $vendorSectionId = null;

	private ?string $syncToken = null;

	private ?string $pageToken = null;

	private ?Section $section = null;

	private ?Connection $connection = null;

	private bool $active = true;

	private ?Date $lastSyncDate = null;

	private ?string $lastSyncStatus = '';

	private ?string $versionId = null;

	private bool $primary = false;

	protected ?Role $owner = null;

	public function getConnection(): ?Connection
	{
		return $this->connection;
	}

	public function getPageToken(): ?string
	{
		return $this->pageToken;
	}

	public function getSyncToken(): ?string
	{
		return $this->syncToken;
	}

	public function setPageToken(?string $token): self
	{
		$this->pageToken = $token;

		return $this;
	}

	public function setSyncToken(?string $token): self
	{
		$this->syncToken = $token;

		return $this;
	}

	public function getVendorSectionId(): ?string
	{
		return $this->vendorSectionId;
	}

	public function setConnection(?Connection $connection): self
	{
		$this->connection = $connection;

		return $this;
	}

	public function setVendorSectionId(?string $vendorSectionId): self
	{
		$this->vendorSectionId = $vendorSectionId;

		return $this;
	}

	public function getSection(): ?Section
	{
		return $this->section;
	}

	public function setSection(?Section $section): self
	{
		$this->section = $section;

		return $this;
	}

	public function setActive(bool $active = true): self
	{
		$this->active = $active;

		return $this;
	}

	public function isActive(): bool
	{
		return $this->active;
	}

	public function setLastSyncDate(?Date $lastSyncDate): self
	{
		$this->lastSyncDate = $lastSyncDate;

		return $this;
	}

	public function getLastSyncDate(): ?Date
	{
		return $this->lastSyncDate;
	}

	public function setLastSyncStatus(?string $lastSyncStatus): self
	{
		$this->lastSyncStatus = $lastSyncStatus;

		return $this;
	}

	public function getLastSyncStatus(): string
	{
		return $this->lastSyncStatus ?? '';
	}

	public function setVersionId(?string $versionId): self
	{
		$this->versionId = $versionId;

		return $this;
	}

	public function getVersionId(): ?string
	{
		return $this->versionId;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function isPrimary(): bool
	{
		return $this->primary;
	}

	public function setPrimary(bool $primary): self
	{
		$this->primary = $primary;

		return $this;
	}

	public function setOwner(?Role $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	public function getOwner(): ?Role
	{
		return $this->owner;
	}

	public function isNew(): bool
	{
		return $this->id === null;
	}

	public function toArray(): array
	{
		$data = [
			'VENDOR_SECTION_ID' => $this->getVendorSectionId(),
			'SYNC_TOKEN' => $this->getSyncToken(),
			'PAGE_TOKEN' => $this->getPageToken(),
			'ACTIVE' => $this->isActive() ? 'Y' : 'N',
			'LAST_SYNC_DATE' => $this->getLastSyncDate()?->getDate(),
			'LAST_SYNC_STATUS' => $this->getLastSyncStatus(),
			'VERSION_ID' => $this->getVersionId(),
			'IS_PRIMARY' => $this->isPrimary() ? 'Y' : 'N',
		];

		if ($this->getSection())
		{
			$data['SECTION_ID'] = $this->getSection()->getId();
		}

		if ($this->getConnection())
		{
			$data['CONNECTION_ID'] = $this->getConnection()->getId();
		}

		return $data;
	}
}
