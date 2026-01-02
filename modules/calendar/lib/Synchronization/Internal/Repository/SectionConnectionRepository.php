<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository;

use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection_Query;
use Bitrix\Calendar\Synchronization\Internal\Repository\Mapper\SectionConnectionMapper;
use Bitrix\Calendar\Synchronization\Internal\Model\SectionConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\EntityCollection;
use Bitrix\Main\Entity\EntityInterface;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Repository\RepositoryInterface;
use Bitrix\Main\Repository\SoftDeletableRepositoryInterface;
use Bitrix\Main\SystemException;
use Exception;

class SectionConnectionRepository implements RepositoryInterface, SoftDeletableRepositoryInterface
{
	public function __construct(private readonly SectionConnectionMapper $mapper)
	{
		Loader::includeModule('dav');
	}

	/**
	 * @param int $id
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getById(mixed $id): ?SectionConnection
	{
		$ormModel = SectionConnectionTable::query()
			->setSelect(
				[
					'*',
					'SECTION',
					'CONNECTION',
				]
			)
			->where('ID', '=', $id)
			->setLimit(1)
			->exec()
			->fetchObject()
		;

		if (!$ormModel)
		{
			return null;
		}

		return $this->mapper->convertFromOrm($ormModel);
	}

	public function findOneBySectionAndConnectionId(int $sectionId, int $connectionId): ?SectionConnection
	{
		$ormModel = SectionConnectionTable::query()
			->setSelect(
				[
					'*',
					'SECTION',
					'CONNECTION',
					'SERVER_PASSWORD' => 'CONNECTION.SERVER_PASSWORD',
					'SERVER_USERNAME' => 'CONNECTION.SERVER_USERNAME'
				]
			)
			->where('SECTION_ID', '=', $sectionId)
			->where('CONNECTION_ID', '=', $connectionId)
			->setLimit(1)
			->exec()
			->fetchObject()
		;

		if (!$ormModel)
		{
			return null;
		}

		return $this->mapper->convertFromOrm($ormModel);
	}

	public function getOneByVendorSectionId(string $vendorSectionId): ?SectionConnection
	{
		$ormModel = SectionConnectionTable::query()
			->setSelect(
				[
					'*',
					'SECTION',
					'CONNECTION',
					'SERVER_PASSWORD' => 'CONNECTION.SERVER_PASSWORD',
					'SERVER_USERNAME' => 'CONNECTION.SERVER_USERNAME'
				]
			)
			->where('VENDOR_SECTION_ID', '=', $vendorSectionId)
			->setLimit(1)
			->exec()
			->fetchObject()
		;

		if (!$ormModel)
		{
			return null;
		}

		return $this->mapper->convertFromOrm($ormModel);
	}

	public function getActiveBySectionAndServices(int $sectionId, array $services): EntityCollection
	{
		$query = SectionConnectionTable::query()
			->setSelect(['*', 'SECTION', 'CONNECTION'])
			->where('SECTION_ID', '=', $sectionId)
			->where('CONNECTION.IS_DELETED', '=', 'N')
			->whereIn('CONNECTION.ACCOUNT_TYPE', $services)
		;

		return $this->buildResultCollection($query);
	}

	public function getByConnection(int $connectionId): EntityCollection
	{
		$query = SectionConnectionTable::query()
			->setSelect(['*', 'SECTION', 'CONNECTION'])
			->where('CONNECTION_ID', '=', $connectionId)
		;

		return $this->buildResultCollection($query);
	}

	/**
	 * @param int $connectionId
	 *
	 * @return EntityCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getActiveByConnection(int $connectionId): EntityCollection
	{
		$query = SectionConnectionTable::query()
			->setSelect(['*', 'SECTION', 'CONNECTION'])
			->where('CONNECTION_ID', '=', $connectionId)
			->where('ACTIVE', '=', 'Y')
		;

		return $this->buildResultCollection($query);
	}

	/**
	 * @param EO_SectionConnection_Query $query
	 *
	 * @return EntityCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function buildResultCollection(Query $query): EntityCollection
	{
		$queryResult = $query->exec();

		$collection = new EntityCollection();

		while ($ormModel = $queryResult->fetchObject())
		{
			$collection->add($this->mapper->convertFromOrm($ormModel));
		}

		return $collection;
	}

	/**
	 * @param SectionConnection $entity
	 *
	 * @throws PersistenceException
	 */
	public function save(EntityInterface $entity): void
	{
		try
		{
			if ($entity->getId())
			{
				$result = SectionConnectionTable::update($entity->getId(), $entity->toArray());
			}
			else
			{
				$result = SectionConnectionTable::add($entity->toArray());
			}
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if ($result->isSuccess() && !$entity->getId())
		{
			$entity->setId($result->getId());
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to save section connection', null, $result->getErrors());
		}
	}

	/**
	 * @param int $id
	 *
	 * @return void
	 *
	 * @throws PersistenceException
	 */
	public function delete(mixed $id): void
	{
		try
		{
			$result = SectionConnectionTable::delete($id);
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to delete section connection', null, $result->getErrors());
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function softDelete(mixed $id): void
	{
		try
		{
			$result = SectionConnectionTable::update($id, ['ACTIVE' => 'N']);
		}
		catch (Exception $e)
		{
			throw new PersistenceException('Unable to delete section connection', $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to delete section connection', null, $result->getErrors());
		}
	}
}
