<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;

abstract class Mapper implements BaseMapperInterface
{
	public const POSITIVE_ANSWER = 'Y';
	public const NEGATIVE_ANSWER = 'N';

	protected const DEFAULT_SELECT = ['*'];

	/** @var Core\Base\Map[]  */
	protected static array $cache = [];

	/**
	 * @param int $id
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws ArgumentException
	 */
	public function getById(int $id): ?object
	{
		if ($this->getCacheMap()->has($id))
		{
			return $this->getCacheMap()->getItem($id);
		}

		$entity = $this->getOneEntityByFilter([
			'=ID' => $id,
		]);

		if (!is_null($entity))
		{
			$this->getCacheMap()->add($entity, $id);
		}

		return $entity;
	}

	/**
	 * @throws ArgumentException
	 */
	public function getByEntityObject($entityObject): ?Core\Base\EntityInterface
	{
		if ($this->getCacheMap()->has($entityObject->getId()))
		{
			return $this->getCacheMap()->getItem($entityObject->getId());
		}

		$entity = $this->convertToObject($entityObject);
		if ($entity !== null)
		{
			$this->getCacheMap()->add($entity, $entity->getId());
		}

		return $entity;
	}

	/**
	 * @param Core\Base\EntityInterface $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|Core\Event\Event|\Bitrix\Calendar\Sync\Connection\Connection|Core\Section\Section|\Bitrix\Calendar\Sync\Connection\SectionConnection|\Bitrix\Calendar\Sync\Connection\EventConnection
	 *
	 * @throws ArgumentException
	 */
	public function create(
		Core\Base\EntityInterface $entity,
		array $params = []
	): ?Core\Base\EntityInterface
	{
		if ($entity->getId())
		{
			return null;
		}

		$resultEntity = $this->createEntity($entity, $params);
		if ($resultEntity && $resultEntity->getId())
		{
			$this->getCacheMap()->add($resultEntity, $resultEntity->getId());
			return $resultEntity;
		}

		return null;
	}

	/**
	 * @param Core\Base\EntityInterface $entity
	 * @param array $params
	 * @return Core\Event\Event
	 */
	public function update(
		Core\Base\EntityInterface $entity,
		array $params = []
	): ?Core\Base\EntityInterface
	{
		$resultEntity = $this->updateEntity($entity, $params);

		if ($resultEntity && $resultEntity->getId())
		{
			$this->getCacheMap()->updateItem($resultEntity, $resultEntity->getId());

			return $resultEntity;
		}

		return null;
	}

	/**
	 * @param Core\Base\EntityInterface $entity
	 * @param array $params
	 *
	 * @return object|null
	 */
	public function delete(
		Core\Base\EntityInterface $entity,
		array $params = ['softDelete' => true]
	): ?Core\Base\EntityInterface
	{
		$resultEntity = $this->deleteEntity($entity, $params);
		if ($resultEntity === null)
		{
			$this->getCacheMap()->remove($entity->getId());

			return null;
		}

		if (!empty($resultEntity->getId()))
		{
			$this->getCacheMap()->updateItem($resultEntity, $resultEntity->getId());

			return $resultEntity;
		}

		return null;
	}

	/**
	 * @param Core\Base\EntityMap $map
	 * @param array $params
	 *
	 * @return void
	 *
	 * @throws Core\Base\BaseException
	 */
	public function deleteMap(Core\Base\EntityMap $map, array $params = ['softDelete' => true])
	{
		while ($entity = $map->fetch())
		{
			$this->delete($entity, $params);
		}
	}

	/**
	 * @param $filter
	 * @param array $params
	 *
	 * @return $this
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function deleteByFilter($filter, array $params = ['softDelete' => true]): self
	{
		$className = $this->getMapClass();
		$result = new $className();
		$paramsForSelect = [
			'filter' => $filter,
			'select' => ['ID']
		];

		$managerResult = $this->getDataManagerResult($paramsForSelect);
		while ($row = $managerResult->fetchObject())
		{
			if ($this->getCacheMap()->has($row->getId()))
			{
				$this->delete($this->getCacheMap()->getItem($row->getId()), $params);
			}
			else
			{
				// TODO: change to more smarty way. Without build entity object and select database.
				$entity = $this->getById($row->getId());
				$this->delete($entity, $params);
			}
		}

		return $result;
	}

	/**
	 * @param array $filter
	 * @param int|null $limit
	 * @param array|null $order
	 *
	 * @return Core\Base\Map
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getMap($filter, int $limit = null, array $order = null): Core\Base\Map
	{
		$className = $this->getMapClass();
		$result = new $className();
		$params = ['filter' => $filter];
		if ($limit)
		{
			$params['limit'] = $limit;
		}
		if ($order)
		{
			$params['order'] = $order;
		}
		$params['select'] = self::DEFAULT_SELECT;

		$managerResult = $this->getDataManagerResult($params);
		while ($row = $managerResult->fetchObject())
		{
			$link = $this->getByEntityObject($row);
			if ($link !== null)
			{
				$result->add($link, $link->getId());
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function resetCacheById(int $id): self
	{
		$map = $this->getCacheMap();
		if ($map->has($id))
		{
			$map->remove($id);
		}

		return $this;
	}

	/**
	 * @return Core\Base\Map
	 */
	final protected function getCacheMap(): Core\Base\Map
	{
		if (empty(self::$cache[static::class]))
		{
			$this->initCacheMap(static::class);
		}

		return self::$cache[static::class];
	}

	/**
	 * @param string $class
	 *
	 * @return void
	 */
	protected function initCacheMap(string $class)
	{
		$object = (get_class($this) === $class)
			? $this
			: new $class()
			;
		$mapClassName = $object->getMapClass();

		self::$cache[$class] = new $mapClassName();
	}

	/**
	 * redefine this method if you need using custom Map class
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Core\Base\EntityMap::class;
	}

	/**
	 * @return string
	 */
	abstract protected function getEntityClass(): string;

	/**
	 * @param array $params
	 *
	 * @return Result
	 */
	abstract protected function getDataManagerResult(array $params): Result;

	/**
	 * @param array $filter
	 *
	 * @return object|null
	 */
	abstract protected function getOneEntityByFilter(array $filter): ?object;

	abstract protected function convertToObject($objectEO): ?Core\Base\EntityInterface;

	/**
	 * @return string
	 */
	abstract protected function getEntityName(): string;

	/**
	 * @param $entity
	 * @param array $params
	 * @return Core\Base\EntityInterface|null
	 */
	abstract protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface;

	/**
	 * @param $entity
	 * @param array $params
	 * @return Core\Base\EntityInterface|null
	 */
	abstract protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface;

	/**
	 * @param Core\Base\EntityInterface $entity
	 * @param array $params
	 * @return Core\Base\EntityInterface|null
	 */
	abstract protected function deleteEntity(Core\Base\EntityInterface $entity, array $params): ?Core\Base\EntityInterface;
}
