<?php

namespace Bitrix\Location\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Common\RepositoryTrait;
use	Bitrix\Location\Entity;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Repository\AddressRepository;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class AddressService
 *
 * Service to work with addresses
 *
 * @package Bitrix\Location\Service
 */
final class AddressService extends BaseService
{
	use RepositoryTrait;

	/** @var AddressService */
	protected static $instance;

	/** @var AddressRepository  */
	protected $repository;

	/**
	 * Find Address by addressId.
	 *
	 * @param int $addressId
	 * @return Entity\Address|bool|null
	 */
	public function findById(int $addressId)
	{
		$result = false;

		try
		{
			$result = $this->repository->findById($addressId);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find Address by linked entity
	 *
	 * @param string $entityId
	 * @param string $entityType
	 * @return Entity\Address\AddressCollection
	 */
	public function findByLinkedEntity(string $entityId, string $entityType): Entity\Address\AddressCollection
	{
		$result = false;

		try
		{
			$result = $this->repository->findByLinkedEntity($entityId, $entityType);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Save Address
	 *
	 * @param Entity\Address $address
	 * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\Result|\Bitrix\Main\ORM\Data\UpdateResult
	 */
	public function save(Entity\Address $address)
	{
		return $this->repository->save($address);
	}

	/**
	 * Delete Address
	 *
	 * @param int $addressId
	 * @return \Bitrix\Main\ORM\Data\DeleteResult
	 */
	public function delete(int $addressId): \Bitrix\Main\ORM\Data\DeleteResult
	{
		return $this->repository->delete($addressId);
	}

	/**
	 * Check if Address count limit is reached
	 *
	 * @return bool
	 * @internal
	 */
	public function isLimitReached(): bool
	{
		return false;
	}

	/**
	 * AddressService constructor.
	 * @param Container $config
	 */
	protected function __construct(Container $config)
	{
		$this->setRepository($config->get('repository'));
		parent::__construct($config);
	}
}
