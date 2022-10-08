<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Vendor\VendorInterface;

abstract class BuilderConnection implements Builder
{
	/**
	 * @var mixed
	 */
	protected $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * @return Connection
	 *
	 */
	public function build(): Connection
	{
		return (new Connection())
			->setId($this->getId())
			->setName($this->getName())
			->setLastSyncTime($this->getLastSyncTime())
			->setVendor($this->getVendor())
			->setDeleted($this->isDeleted())
			->setLastSyncTime($this->getLastSyncTime())
			->setToken($this->getToken())
			->setStatus($this->getStatus())
			->setOwner($this->getOwner())
			->setNextSyncTry($this->getNextSyncTry())
		;
	}

	abstract protected function getId(): int;
	abstract protected function getName(): string;
	abstract protected function getLastSyncTime(): ?Date;
	abstract protected function getToken(): ?string;
	abstract protected function getStatus(): ?string;
	abstract protected function getVendor(): ?VendorInterface;
	abstract protected function getOwner(): ?Role;
	abstract protected function isDeleted(): bool;
	abstract protected function getNextSyncTry(): ?Date;
}
