<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Exception;

class SectionConnection extends Complex
{
	/**
	 * @param Sync\Connection\SectionConnection $sectionConnection
	 * @param array $fields
	 *
	 * @return Sync\Connection\SectionConnection
	 *
	 * @throws BaseException
	 * @throws Exception
	 *
	 * @deprecated Refactor this method to use cache. And need to find places of usage.
	 */
	public function patch(
		Sync\Connection\SectionConnection $sectionConnection,
		array $fields
	): Sync\Connection\SectionConnection
	{
		$sectionConnectionFields = $this->convertToArray($sectionConnection);

		$result = SectionConnectionTable::update(
			$sectionConnection->getId(),
			array_intersect_key($sectionConnectionFields, array_flip($fields))
		);

		if ($result->isSuccess())
		{
			return $sectionConnection;
		}

		throw new BaseException('Error of delete SectionConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param Sync\Connection\SectionConnection $sectionConnection
	 *
	 * @return array
	 * @throws BaseException
	 */
	public function convertToArray(Sync\Connection\SectionConnection $sectionConnection): array
	{
		if (!$sectionConnection->getConnection())
		{
			throw new BaseException('The sectionConnection must have an connection');
		}

		return [
			'SECTION_ID'        => $sectionConnection->getSection()->getId(),
			'CONNECTION_ID'     => $sectionConnection->getConnection()->getId(),
			'VENDOR_SECTION_ID' => $sectionConnection->getVendorSectionId(),
			'SYNC_TOKEN'        => $sectionConnection->getSyncToken(),
			'PAGE_TOKEN'        => $sectionConnection->getPageToken(),
			'ACTIVE'            => $sectionConnection->isActive() ? self::POSITIVE_ANSWER : self::NEGATIVE_ANSWER,
			'LAST_SYNC_DATE'    => $sectionConnection->getLastSyncDate()
				? $sectionConnection->getLastSyncDate()->getDate()
				: null,
			'LAST_SYNC_STATUS'  => $sectionConnection->getLastSyncStatus(),
			'VERSION_ID'        => $sectionConnection->getVersionId(),
			'IS_PRIMARY'        => $sectionConnection->isPrimary() ? self::POSITIVE_ANSWER : self::NEGATIVE_ANSWER,
		];
	}

    /**
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Sync\Connection\SectionConnectionMap::class;
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'section connection link';
	}

	/**
	 * @param array $filter
	 *
	 * @return object|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException|LoaderException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}
		/** @var EO_SectionConnection $data */
		$link = SectionConnectionTable::query()
			->setFilter($filter)
			->setSelect([
				'*',
				'SECTION',
				'CONNECTION',
				'SERVER_PASSWORD' => 'CONNECTION.SERVER_PASSWORD',
				'SERVER_USERNAME' => 'CONNECTION.SERVER_USERNAME'
			])
			->exec()->fetchObject();

		if ($link !== null)
		{
			return $this->convertToObject($link);
		}

		return null;
	}

	/**
	 * @param EO_SectionConnection $objectEO
	 *
	 * @return Sync\Connection\SectionConnection|null
	 *
	 * @throws ObjectException
	 */
	protected function convertToObject($objectEO): ?Sync\Connection\SectionConnection
	{
		$section = $objectEO->getSection();
		if ($section !== null)
		{
			$section = $this->prepareSection($section);
		}
		else
		{
			$objectEO->delete();

			return null;
		}

		$connection = $objectEO->getConnection();
		if ($connection !== null)
		{
			$connection = $this->prepareConnection($connection);
		}
		else
		{
			$objectEO->delete();

			return null;
		}

		$item = new Sync\Connection\SectionConnection();
		$item
			->setId($objectEO->getId())
			->setSection($section)
			->setConnection($connection)
			->setVendorSectionId($objectEO->getVendorSectionId())
			->setSyncToken($objectEO->getSyncToken())
			->setPageToken($objectEO->getPageToken())
			->setActive($objectEO->getActive())
			->setLastSyncDate(new Core\Base\Date($objectEO->getLastSyncDate()))
			->setLastSyncStatus($objectEO->getLastSyncStatus())
			->setVersionId($objectEO->get('VERSION_ID'))
			->setPrimary($objectEO->getIsPrimary())
		;

		return $item;
	}

	/**
	 * @param Sync\Connection\SectionConnection $entity
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

		$result = SectionConnectionTable::add($data);

		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new BaseException('Error of create SectionConnection: '
			. implode('; ', $result->getErrorMessages()),
		400);
	}

	/**
	 * @param Sync\Connection\SectionConnection $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws BaseException
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);

		$result = SectionConnectionTable::update($entity->getId(), $data);

		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new BaseException('Error of update SectionConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param Sync\Connection\SectionConnection $entity
	 *
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 * @throws BaseException
	 * @throws Exception
	 */
	protected function deleteEntity(
		Core\Base\EntityInterface $entity,
		array $params = ['softDelete' => true]
	): ?Core\Base\EntityInterface
	{
		if (!empty($params['softDelete']))
		{
			$entity->setActive(false);
			return $this->updateEntity($entity, $params);
		}

		$result = SectionConnectionTable::delete($entity->getId());

		if ($result->isSuccess())
		{
			return null;
		}

		throw new BaseException('Error of delete SectionConnection: '
			. implode('; ', $result->getErrorMessages()),
			400);
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
			$params['select'] = [
				"*",
				'SECTION',
				'CONNECTION',
				'SERVER_PASSWORD' => 'CONNECTION.SERVER_PASSWORD',
				'SERVER_USERNAME' => 'CONNECTION.SERVER_USERNAME'
			];
		}

		return SectionConnectionTable::getList($params);
	}

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Sync\Connection\SectionConnection::class;
	}
}
