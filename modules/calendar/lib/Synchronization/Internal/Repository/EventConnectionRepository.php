<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository;

use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Model\EventConnectionTable;
use Bitrix\Calendar\Synchronization\Internal\Repository\Mapper\EventConnectionMapper;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\EntityCollection;
use Bitrix\Main\Entity\EntityInterface;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Repository\RepositoryInterface;
use Bitrix\Main\SystemException;
use Exception;

class EventConnectionRepository implements RepositoryInterface
{
	public function __construct(private readonly EventConnectionMapper $mapper)
	{
	}

	/**
	 * @param int $id
	 *
	 * @return EntityInterface|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getById(mixed $id): ?EntityInterface
	{
		$ormModel = EventConnectionTable::getById($id)->fetchObject();

		if (!$ormModel)
		{
			return null;
		}

		return $this->mapper->convertFromOrm($ormModel);
	}

	/**
	 * @throws RepositoryReadException
	 */
	public function getOneByEventAndConnectionId(int $eventId, int $connectionId): ?EventConnection
	{
		try
		{
			$ormModel = EventConnectionTable::query()
				->setSelect(['*', 'EVENT', 'CONNECTION'])
				->where('EVENT_ID', '=', $eventId)
				->where('CONNECTION_ID', '=', $connectionId)
				->whereNotNull('EVENT.ID')
				->setLimit(1)
				->exec()
				->fetchObject()
			;
		}
		catch (SystemException $e)
		{
			throw new RepositoryReadException($e->getMessage(), $e->getCode(), $e);
		}

		if (!$ormModel)
		{
			return null;
		}

		return $this->mapper->convertFromOrm($ormModel);
	}

	/**
	 * @param int[] $ids
	 * @param int $connectionId
	 *
	 * @return EntityCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByIdsAndConnectionId(array $ids, int $connectionId): EntityCollection
	{
		$collection = new EntityCollection();

		if (empty($ids))
		{
			return $collection;
		}

		$query = EventConnectionTable::query()
			->setSelect(['*', 'EVENT', 'CONNECTION'])
			->where('CONNECTION_ID', '=', $connectionId)
			->whereNotNull('EVENT.ID')
			->where(
				(new ConditionTree())
					->logic(ConditionTree::LOGIC_OR)
					->whereIn('VENDOR_EVENT_ID', $ids)
					->whereIn('RECURRENCE_ID', $ids),
			)
		;

		$queryResult = $query->exec();

		while ($ormModel = $queryResult->fetchObject())
		{
			$collection->add($this->mapper->convertFromOrm($ormModel));
		}

		return $collection;
	}

	/**
	 * @param string $recurrenceId
	 * @param int $connectionId
	 *
	 * @return EntityCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getByRecurrenceAndConnectionId(string $recurrenceId, int $connectionId): EntityCollection
	{
		$collection = new EntityCollection();

		$query =
			EventConnectionTable::query()
				->setSelect(['*', 'EVENT', 'CONNECTION'])
				->where('RECURRENCE_ID', '=', $recurrenceId)
				->where('CONNECTION_ID', '=', $connectionId)
				->whereNotNull('EVENT.ID')
		;

		$queryResult = $query->exec();

		while ($ormModel = $queryResult->fetchObject())
		{
			$collection->add($this->mapper->convertFromOrm($ormModel));
		}

		return $collection;
	}

	/**
	 * @param int $eventId
	 * @param array $services
	 *
	 * @return EntityCollection
	 *
	 * @return EntityCollection
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getActiveByEventAndServices(int $eventId, array $services): EntityCollection
	{
		$query = EventConnectionTable::query()
			->setSelect(['*', 'CONNECTION'])
			->where('EVENT_ID', '=', $eventId)
			->where('CONNECTION.IS_DELETED', '=', 'N')
			->whereIn('CONNECTION.ACCOUNT_TYPE', $services)
		;

		$queryResult = $query->exec();

		$collection = new EntityCollection();

		while ($ormModel = $queryResult->fetchObject())
		{
			$collection->add($this->mapper->convertFromOrm($ormModel));
		}

		return $collection;
	}

	/**
	 * @param EventConnection $entity
	 *
	 * @throws PersistenceException
	 */
	public function save(EntityInterface $entity): void
	{
		try
		{
			if ($entity->getId())
			{
				$result = EventConnectionTable::update($entity->getId(), $entity->toArray());
			}
			else
			{
				$result = EventConnectionTable::add($entity->toArray());
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
			throw new PersistenceException('Unable to save event connection', null, $result->getErrors());
		}
	}

	/**
	 * @param EventConnection[] $eventConnections
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function saveAll(array $eventConnections): void
	{
		if (empty($eventConnections))
		{
			return;
		}

		$ormCollection = EventConnectionTable::createCollection();

		foreach ($eventConnections as $eventConnection)
		{
			$ormEventConnection = $this->mapper->convertToOrm($eventConnection);
			$ormCollection->add($ormEventConnection);
		}

		$ormCollection->save(true);
	}

	/**
	 * @throws PersistenceException
	 */
	public function deleteBrokenEventConnections(int $sectionId, int $connectionId): void
	{
		try
		{
			EventConnectionTable::getEntity()->getConnection()->queryExecute(
				"DELETE ec FROM b_calendar_event_connection ec
					INNER JOIN b_calendar_event AS event ON event.ID=ec.EVENT_ID
				WHERE event.SECTION_ID = '$sectionId' AND ec.CONNECTION_ID = '$connectionId'"
			);
		}
		catch (SystemException $e)
		{
			throw new PersistenceException(
				sprintf(
					'Unable to delete event connections for section "%s" and connection "%s"',
					$sectionId,
					$connectionId
				),
				$e
			);
		}
	}

	/**
	 * @param int $id
	 *
	 * @throws PersistenceException
	 */
	public function delete(mixed $id): void
	{
		try
		{
			$result = EventConnectionTable::delete($id);
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to delete event connection', null, $result->getErrors());
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function deleteByVendorId(string $vendorId): void
	{
		try
		{
			$queryResult = EventConnectionTable::query()
				->setSelect(['ID'])
				->where('VENDOR_EVENT_ID', $vendorId)
				->exec()
			;

			while ($ormModel = $queryResult->fetchObject())
			{
				$id = $ormModel->getId();

				$result = EventConnectionTable::delete($id);

				if (!$result->isSuccess())
				{
					throw new PersistenceException(
						sprintf('Unable to delete event connection "%s"', $id),
						errors: $result->getErrors()
					);
				}
			}
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}
	}
}
