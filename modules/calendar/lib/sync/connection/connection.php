<?php

namespace Bitrix\Calendar\Sync\Connection;

use Bitrix\Main\Type;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\SectionCollection;
use Bitrix\Calendar\Sync\Vendor\VendorInterface;

class Connection implements EntityInterface
{
	/** @var string */
	private string $serviceName;
	/**
	 * @var integer|null
	 */
	private ?int $id = null;
	/**
	 * @var ?string
	 */
	private ?string $name = null;
	/**
	 * @var Role|null
	 */
	private ?Role $owner = null;
	/**
	 * @var VendorInterface|null
	 */
	private ?VendorInterface $vendor = null;
	/**
	 * @var bool
	 */
	private bool $deleted = false;
	/**
	 * @var Date|null
	 */
	protected ?Date $lastSyncTime = null;
	/**
	 * @var string|null
	 */
	protected ?string $token = null;
	/**
	 * @var string
	 */
	protected string $status = '[200] Not synced';
	/**
	 * @var SectionCollection|null
	 */
	protected ?SectionCollection $sectionCollection = null;
	/**
	 * @var Role|null
	 */
	protected ?Role $creator = null;
	/**
	 * @var string|null
	 */
	protected ?string $syncStatus = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $nextSyncTryTime = null;

	/**
	 * @return Server
	 */
	public function getServer(): Server
	{
		return $this->getVendor()->getServer();
	}

	/**
	 * @param Server $server
	 * @return $this
	 */
	public function setServer(Server $server): Connection
	{
		$this->getVendor()->setServer($server);

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
	 * @return VendorInterface
	 */
	public function getVendor(): VendorInterface
	{
		return $this->vendor;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string|null $status
	 *
	 * @return $this
	 */
	public function setStatus(?string $status): Connection
	{
		if ($status !== null)
		{
			$this->status = $status;
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getToken(): ?string
	{
		return $this->token;
	}

	/**
	 * @param string|null $token
	 *
	 * @return $this
	 */
	public function setToken(?string $token): self
	{
		$this->token = $token;

		return $this;
	}

	/**
	 * @return Date|null
	 */
	public function getLastSyncTime(): ?Date
	{
		return $this->lastSyncTime;
	}

	/**
	 * @param Date|null $time
	 * @return Connection
	 */
	public function setLastSyncTime(?Date $time): self
	{
		$this->lastSyncTime = $time;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->deleted;
	}

	/**
	 * @param bool $deleted
	 *
	 * @return $this
	 */
	public function setDeleted(bool $deleted): self
	{
		$this->deleted = $deleted;

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return Connection
	 */
	public function setName(string $name): Connection
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param VendorInterface $vendor
	 *
	 * @return $this
	 */
	public function setVendor(VendorInterface $vendor): self
	{
		$this->vendor = $vendor;

		return $this;
	}

	/**
	 * @param Role $owner
	 *
	 * @return Connection
	 */
	public function setOwner(?Role $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * @param int|null $id
	 *
	 * @return Connection
	 */
	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @param SectionCollection $sectionCollection
	 *
	 * @return Connection
	 */
	public function setSectionCollection(SectionCollection $sectionCollection): self
	{
		$this->sectionCollection = $sectionCollection;

		return $this;
	}

	/**
	 * @return SectionCollection
	 */
	public function getSectionCollection(): SectionCollection
	{
		// if ($this->sectionCollection === null)
		// {
		// 	// TODO: implement it
		// 	$this->sectionCollection = Conn
		// }
		return $this->sectionCollection;
	}

	/**
	 * @return bool
	 */
	public function hasName(): bool
	{
		return $this->name && $this->name !== '';
	}

	/**
	 * @return string
	 */
	public function getAccountType(): string
	{
		return $this->getVendor()->getCode();
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setServiceName(string $name): self
	{
		$this->serviceName = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getServiceName(): string
	{
		return $this->serviceName;
	}

	public function setCreator(?Role $creator): self
	{
		$this->creator = $creator;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSyncStatus(): ?string
	{
		return $this->syncStatus;
	}

	/**
	 * @param string|null $status
	 *
	 * @return $this
	 */
	public function setSyncStatus(?string $syncStatus): Connection
	{
		if ($syncStatus !== null)
		{
			$this->syncStatus = $syncStatus;
		}

		return $this;
	}

	/**
	 * @return Date|null
	 */
	public function getNextSyncTry(): ?Date
	{
		return $this->nextSyncTryTime;
	}

	/**
	 * @param Date|null $nextSyncTryTime
	 *
	 * @return Connection
	 */
	public function setNextSyncTry(?Date $nextSyncTryTime): self
	{
		$this->nextSyncTryTime = $nextSyncTryTime;
		return $this;
	}
}
