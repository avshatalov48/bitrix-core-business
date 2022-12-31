<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Dav\Internals\EO_DavConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Report\VisualConstructor\Controller\Base;
use Exception;

/**
 * TODO: move it to Sync namespace
 */
class Connection extends Mapper implements BaseMapperInterface
{
	/**
	 * @throws Exception
	 */
	public function patch(Sync\Connection\Connection $connection, array $fields): Sync\Connection\Connection
	{
		Loader::includeModule('dav');
		DavConnectionTable::update($connection->getId(), $fields);

		return $connection;
	}

	/**
	 * @param string $name
	 * @return Sync\Connection\Connection|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByName(string $name): ?Sync\Connection\Connection
	{
		return $this->getOneEntityByFilter(['=NAME' => $name]);
	}

	/**
	 * @param array $filter
	 *
	 * @return Sync\Connection\Connection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$connection = DavConnectionTable::query()
			->setFilter($filter)
			->setSelect(['*'])
			->exec()
			->fetchObject()
		;

		if ($connection)
		{
			return $this->convertToObject($connection);
		}

		return null;
	}

	/**
	 * @param EO_DavConnection $objectEO
	 *
	 * @return Sync\Connection\Connection
	 */
	protected function convertToObject($objectEO): Core\Base\EntityInterface
	{
		return (new Sync\Builders\BuilderConnectionFromDM($objectEO))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'Dav connection';
	}

	/**
	 * @param Sync\Connection\Connection $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Exception
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		Loader::includeModule('dav');
		$data = $this->convertToArray($entity);

		$result = DavConnectionTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new Core\Base\BaseException('Error of create Dav connection', 400);
	}

	/**
	 * @param Sync\Connection\Connection $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		Loader::includeModule('dav');
		$data = $this->convertToArray($entity);
		unset($data['CREATED']);

		$result = DavConnectionTable::update($entity->getId(), $data);
		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new Core\Base\BaseException('Error of update Dav connection', 400);
	}

	private function convertToArray($connection): array
	{
		return [
			'SYNC_TOKEN' => $connection->getToken(),
			'NAME' => $connection->getName(),
			'ENTITY_TYPE' => $connection->getOwner()->getType(),
			'ENTITY_ID' => $connection->getOwner()->getId(),
			'SERVER_SCHEME' => $connection->getServer()->getScheme(),
			'SERVER_HOST' => $connection->getServer()->getHost(),
			'SERVER_PORT' => $connection->getServer()->getPort(),
			'SERVER_USERNAME' => $connection->getServer()->getUserName(),
			'SERVER_PASSWORD' => $connection->getServer()->getPassword(),
			'SERVER_PATH' => $connection->getServer()->getBasePath(),
			'LAST_RESULT' => $connection->getStatus(),
			'IS_DELETED' => $connection->isDeleted() ? 'Y' : 'N',
			'SYNCHRONIZED' => ($lastSyncTime = $connection->getLastSyncTime())
				? $lastSyncTime->getDate()
				: new DateTime()
			,
			'CREATED' => new DateTime(),
			'MODIFIED' => new DateTime(),
			'ACCOUNT_TYPE' => $connection->getAccountType(),
			'NEXT_SYNC_TRY' => ($nextSyncTry = $connection->getNextSyncTry())
				? $nextSyncTry->getDate()
				: new DateTime(),
		];
	}

	/**
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Sync\Connection\ConnectionMap::class;
	}

	/**
	 * @param Sync\Connection\Connection $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 * @throws Core\Base\BaseException
	 */
	protected function deleteEntity(
		Core\Base\EntityInterface $entity,
		array $params = ['softDelete' => true]
	): ?Core\Base\EntityInterface
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		if (!empty($params['softDelete']))
		{
			$entity->setIsActive(true);

			return $this->updateEntity($entity, $params);
		}

		// TODO: change it to SectionTable::delete() after implementation all logic
		$result = DavConnectionTable::delete($entity->getId());
		if ($result->isSuccess())
		{
			$entity->setDeleted(true);

			return null;
		}

		throw new Core\Base\BaseException('Error of delete Dav connection');
	}

	/**
	 * @param array $params
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws LoaderException
	 */
	protected function getDataManagerResult(array $params): Result
	{
		Loader::includeModule('dav');

		return DavConnectionTable::getList($params);
	}

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Sync\Connection\Connection::class;
	}
}
