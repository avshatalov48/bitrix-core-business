<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Sync\Vendor\Vendor;
use Bitrix\Calendar\Sync\Vendor\VendorInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class BuilderConnectionFromArray extends BuilderConnection
{

	public function __construct(array $data)
	{
		parent::__construct($data);
	}

	protected function getId(): int
	{
		return $this->data['ID'];
	}

	protected function getName(): string
	{
		return $this->data['NAME'] ?? '';
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getLastSyncTime(): ?Date
	{
		// TODO: move date format to helper
		return $this->data['SYNCHRONIZED']
			? new Date(new DateTime($this->data['SYNCHRONIZED'], 'Y-m-d H:i:s'))
			: null;
	}

	/**
	 * @return string|null
	 */
	protected function getToken(): ?string
	{
		return $this->data['SYNC_TOKEN'] ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getStatus(): ?string
	{
		return $this->data['LAST_RESULT'];
	}

	/**
	 * @return VendorInterface|null
	 *
	 * @throws ObjectException
	 */
	protected function getVendor(): ?VendorInterface
	{
		return new Vendor($this->data);
	}

	/**
	 * @return Role|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOwner(): ?Role
	{
		try
		{
			return Helper::getRole(
				$this->data['ENTITY_ID'],
				$this->data['ENTITY_TYPE']
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
		return $this->data['IS_DELETED'] === 'Y';
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getNextSyncTry(): ?Date
	{
		// TODO: move date format to helper
		return $this->data['NEXT_SYNC_TRY']
			? new Date(new DateTime($this->data['NEXT_SYNC_TRY'], 'Y-m-d H:i:s'))
			: null;
	}
}
