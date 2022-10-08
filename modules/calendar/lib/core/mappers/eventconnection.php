<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Internals\EO_EventConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Exception;

class EventConnection extends Complex
{
	/**
	 * @param array $filter
	 *
	 * @return object|null
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$link = EventConnectionTable::query()
			->setFilter($filter)
			->setSelect(['*', 'EVENT', 'CONNECTION'])
			->exec()->fetchObject();

		if ($link === null)
		{
			return null;
		}

		return $this->convertToObject($link);
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'event connection link';
	}

	/**
	 * @param $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws BaseException
	 * @throws Exception
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);

		$result = EventConnectionTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new BaseException('Error of create EventConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param $entity
	 * @param array $params [
	 *      not params
	 * ]
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws BaseException
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);

		$result = EventConnectionTable::update($entity->getId(), $data);

		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new BaseException('Error of update EventConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param Sync\Connection\EventConnection $entity
	 * @param array $params [
	 *      not params
	 * ]
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws BaseException
	 * @throws Exception
	 */
	protected function deleteEntity(
		Core\Base\EntityInterface $entity,
		array $params = ['softDelete' => true]
	): ?Core\Base\EntityInterface
	{
//		if (!empty($params['softDelete']))
//		{
//			$entity->setActive(false);
//			return $this->updateEntity($entity, $params);
//		}

		$result = EventConnectionTable::delete($entity->getId());

		if ($result->isSuccess())
		{
			return null;
		}

		throw new BaseException('Error of delete EventConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Sync\Connection\EventConnectionMap::class;
	}

	/**
	 * @param EO_EventConnection $objectEO
	 *
	 * @return Sync\Connection\EventConnection
	 *
	 */
	protected function convertToObject($objectEO): ?Sync\Connection\EventConnection
	{
		if ($objectEO->getEvent() === null || $objectEO->getConnection() === null)
		{
			$objectEO->delete();

			return null;
		}

		$event = $this->prepareEvent($objectEO->getEvent());
		$connection = $this->prepareConnection($objectEO->getConnection());

		return (new Sync\Connection\EventConnection())
			->setLastSyncStatus($objectEO->getSyncStatus())
			->setRetryCount($objectEO->getRetryCount())
			->setVersion((int)$objectEO->getVersion())
			->setVendorEventId($objectEO->getVendorEventId())
			->setEntityTag($objectEO->getEntityTag())
			->setRecurrenceId($objectEO->getRecurrenceId())
			->setVendorVersionId($objectEO->getVendorVersionId())
			->setId($objectEO->getId())
			->setData($objectEO->getData())
			->setEvent($event)
			->setConnection($connection)
			;
	}

	/**
	 * @param Sync\Connection\EventConnection $entity
	 *
	 * @return array
	 */
	private function convertToArray(Sync\Connection\EventConnection $entity): array
	{
		return [
			'EVENT_ID' => $entity->getEvent()->getId(),
			'CONNECTION_ID' => $entity->getConnection()->getId(),
			'VENDOR_EVENT_ID' => $entity->getVendorEventId(),
			'SYNC_STATUS' => $entity->getLastSyncStatus(),
			'RETRY_COUNT' => $entity->getRetryCount(),
			'ENTITY_TAG' => $entity->getEntityTag(),
			'VENDOR_VERSION_ID' => $entity->getVendorVersionId(),
			'VERSION' => $entity->getVersion(),
			'DATA' => $entity->getData(),
			'RECURRENCE_ID' => $entity->getRecurrenceId(),
		];
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
		if ($params['select'] === self::DEFAULT_SELECT)
		{
			$params['select'] = ["*", 'EVENT', 'CONNECTION'];
		}

		return EventConnectionTable::getList($params);
	}

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Sync\Connection\EventConnection::class;
	}
}
