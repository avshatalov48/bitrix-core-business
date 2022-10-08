<?php

namespace Bitrix\Calendar\Core\Section;

use Bitrix\Calendar\Core\Base\Date;
/** @deprecated  */
class SectionSyncData
{
	/**
	 * @var int
	 */
	protected $connectionId;
	/**
	 * @var int
	 */
	protected $sectionId;
	/**
	 * @var string
	 */
	protected $vendorSectionId;
	/**
	 * @var string
	 */
	protected $syncToken;
	/**
	 * @var string
	 */
	protected $pageToken;
	/**
	 * @var bool
	 */
	protected $isActive;
	/**
	 * @var Date
	 */
	protected $lastSync;
	/**
	 * @var string
	 */
	protected $lastSyncStatus;
	/**
	 * @var int
	 */
	protected $version;
	protected $connectionType;

	/**
	 * @param int $connectionId
	 * @return SectionSyncData
	 */
	public function setConnectionId(int $connectionId): SectionSyncData
	{
		$this->connectionId = $connectionId;

		return $this;
	}

	/**
	 * @param int $sectionId
	 * @return SectionSyncData
	 */
	public function setSectionId(int $sectionId): SectionSyncData
	{
		$this->sectionId = $sectionId;

		return $this;
	}

	/**
	 * @param string $vendorSectionId
	 * @return SectionSyncData
	 */
	public function setVendorSectionId(string $vendorSectionId): SectionSyncData
	{
		$this->vendorSectionId = $vendorSectionId;

		return $this;
	}

	/**
	 * @param string $syncToken
	 * @return SectionSyncData
	 */
	public function setSyncToken(string $syncToken): SectionSyncData
	{
		$this->syncToken = $syncToken;

		return $this;
	}

	/**
	 * @param string $pageToken
	 * @return SectionSyncData
	 */
	public function setPageToken(string $pageToken): SectionSyncData
	{
		$this->pageToken = $pageToken;

		return $this;
	}

	/**
	 * @param bool $isActive
	 * @return SectionSyncData
	 */
	public function setIsActive(bool $isActive): SectionSyncData
	{
		$this->isActive = $isActive;

		return $this;
	}

	/**
	 * @param Date $lastSync
	 * @return SectionSyncData
	 */
	public function setLastSync(Date $lastSync): SectionSyncData
	{
		$this->lastSync = $lastSync;

		return $this;
	}

	/**
	 * @param string $lastSyncStatus
	 * @return SectionSyncData
	 */
	public function setLastSyncStatus(string $lastSyncStatus): SectionSyncData
	{
		$this->lastSyncStatus = $lastSyncStatus;

		return $this;
	}

	/**
	 * @param int $version
	 * @return SectionSyncData
	 */
	public function setVersion(int $version): SectionSyncData
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getConnectionId(): int
	{
		return $this->connectionId;
	}

	/**
	 * @return int
	 */
	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	/**
	 * @return string
	 */
	public function getVendorSectionId(): string
	{
		return $this->vendorSectionId;
	}

	/**
	 * @return string
	 */
	public function getSyncToken(): string
	{
		return $this->syncToken;
	}

	/**
	 * @return string
	 */
	public function getPageToken(): string
	{
		return $this->pageToken;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->isActive;
	}

	/**
	 * @return Date
	 */
	public function getLastSync(): Date
	{
		return $this->lastSync;
	}

	/**
	 * @return string
	 */
	public function getLastSyncStatus(): string
	{
		return $this->lastSyncStatus;
	}

	/**
	 * @return int
	 */
	public function getVersion(): int
	{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getConnectionType(): string
	{
		// ToDo return connection type
		return $this->connectionType;
	}

	/**
	 * @param string $connectionType
	 * @return SectionSyncData
	 */
	public function setConnectionType(string $connectionType): SectionSyncData
	{
		$this->connectionType = $connectionType;

		return $this;
	}
}
