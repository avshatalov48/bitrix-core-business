<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Sync\Vendor\Vendor;
use Bitrix\Calendar\Sync\Vendor\VendorInterface;
use Bitrix\Dav\Internals\EO_DavConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class BuilderConnectionFromDM extends BuilderConnection
{
	/** @var EO_DavConnection */
	protected $data;


	/**
	 * @param EO_DavConnection $davConnection
	 */
	public function __construct(EO_DavConnection $davConnection)
	{
		parent::__construct($davConnection);
	}


	protected function getId(): int
	{
		return $this->data->getId();
	}

	protected function getName(): string
	{
		return $this->data->getName();
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getLastSyncTime(): ?Date
	{
		return $this->data->getSynchronized()
			? new Date($this->data->getSynchronized())
			: null;
	}

	/**
	 * @return string|null
	 */
	protected function getToken(): ?string
	{
		return $this->data->getSyncToken() ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getStatus(): ?string
	{
		return $this->data->getLastResult() ?? null;
	}

	/**
	 * @return VendorInterface|null
	 *
	 * @throws ObjectException
	 */
	protected function getVendor(): ?VendorInterface
	{
		return new Vendor([
			'ACCOUNT_TYPE' => $this->data->getAccountType(),
			'SERVER_SCHEME' => $this->data->getServerScheme(),
			'SERVER_HOST' => $this->data->getServerHost(),
			'SERVER_PORT' => $this->data->getServerPort(),
			'SERVER_USERNAME' => $this->data->getServerUsername(),
			'SERVER_PASSWORD' => $this->data->getServerPassword(),
			'SERVER_PATH' => $this->data->getServerPath(),
		]);
	}

	/**
	 * @return Role|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOwner(): ?Role
	{
		try
		{
			return Helper::getRole(
				$this->data->getEntityId(),
				$this->data->getEntityType()
			);
		}
		catch (BaseException $e)
		{
			return null;
		}
	}

	/**
	 * @return bool
	 */
	protected function isDeleted(): bool
	{
		return $this->data['IS_DELETED'] ?? false;
	}

	/**
	 * @return string|null
	 */
	protected function getSyncStatus(): ?string
	{
		return $this->data->getSyncStatus();
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getNextSyncTry(): ?Date
	{
		return $this->data->getNextSyncTry()
			? new Date($this->data->getSynchronized())
			: null;
	}
}
