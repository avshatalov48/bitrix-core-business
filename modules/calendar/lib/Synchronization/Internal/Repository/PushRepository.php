<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository;

use Bitrix\Calendar\Synchronization\Internal\Entity\Push\EntityType;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\ProcessingStatus;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\PushId;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Model\PushTable;
use Bitrix\Calendar\Synchronization\Internal\Repository\Mapper\PushMapper;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\EntityCollection;
use Bitrix\Main\Entity\EntityInterface;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Repository\RepositoryInterface;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;

class PushRepository implements RepositoryInterface
{
	public function __construct(private readonly PushMapper $mapper)
	{
	}

	/**
	 * @param PushId $id
	 *
	 * @return Push|null
	 *
	 * @throws ArgumentTypeException
	 */
	public function getById(mixed $id): ?EntityInterface
	{
		if (!$id instanceof PushId)
		{
			throw new ArgumentTypeException('id', PushId::class);
		}

		$ormModel = PushTable::query()
			->setSelect(['*'])
			->addFilter('=ENTITY_TYPE', $id->entityType)
			->addFilter('ENTITY_ID', $id->entityId)
			->exec()
			->fetchObject()
		;

		if ($ormModel)
		{
			return $this->mapper->convertFromOrm($ormModel);
		}

		return null;
	}

	/**
	 * @throws RepositoryReadException
	 */
	public function getByConnectionId(int $id): ?Push
	{
		try
		{
			return $this->getById(new PushId(EntityType::Connection->value, $id));
		}
		catch (SystemException $e)
		{
			throw new RepositoryReadException($e->getMessage(), previous: $e);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws ArgumentTypeException
	 */
	public function getBySectionConnectionId(int $id): ?Push
	{
		return $this->getById(new PushId(EntityType::SectionConnection->value, $id));
	}

	public function getByChannelAndResource(string $channelId, string $resourceId): ?Push
	{
		$ormModel = PushTable::query()
			->setSelect(['*'])
			->addFilter('=CHANNEL_ID', $channelId)
			->addFilter('=RESOURCE_ID', $resourceId)
			->exec()
			->fetchObject()
		;

		if ($ormModel)
		{
			return $this->mapper->convertFromOrm($ormModel);
		}

		return null;
	}

	/**
	 * @param string[] $entityTypes An \Bitrix\Calendar\Synchronization\Entity\Push\EntityType values
	 */
	public function getUpdateNeededCollection(array $entityTypes, int $limit = 5): EntityCollection
	{
		$collection = new EntityCollection();

		$queryResult = PushTable::getList([
			'filter' => [
				'ENTITY_TYPE' => $entityTypes,
				'<=EXPIRES' => (new DateTime())->add('+1 day'),
			],
			'order' => [
				'EXPIRES' => 'ASC',
			],
			'limit' => $limit > 0 ? $limit : 5,
		]);

		while ($ormModel = $queryResult->fetchObject())
		{
			$collection->add($this->mapper->convertFromOrm($ormModel));
		}

		return $collection;
	}

	/**
	 * @throws PersistenceException
	 */
	public function add(Push $push): void
	{
		try
		{
			$result = PushTable::add($push->toArray());
		}
		catch (SqlQueryException $e)
		{
			// If there is a race situation
			if ($e->getCode() === 400 && str_starts_with($e->getDatabaseMessage(), '(1062)'))
			{
				return;
			}

			throw new PersistenceException($e->getMessage(), $e);
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to add push', errors: $result->getErrors());
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function update(Push $push): void
	{
		try
		{
			$result = PushTable::update(
				$push->getId()->toArray(),
				$push->toArray()
			);
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to update push', null, $result->getErrors());
		}
	}

	/**
	 * @param Push $entity
	 *
	 * @throws NotSupportedException
	 */
	public function save(EntityInterface $entity): void
	{
		throw new NotSupportedException('Use add or update methods explicitly');
	}

	/**
	 * @throws PersistenceException
	 */
	public function blockPush(Push $push): bool
	{
		try
		{
			return PushTable::update(
				[
					'ENTITY_TYPE' => $push->getEntityType(),
					'ENTITY_ID' => $push->getEntityId(),
				],
				[
					'NOT_PROCESSED' => ProcessingStatus::Blocked->value
				]
			)->isSuccess();
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function unBlockPush(Push $push): void
	{
		try
		{
			PushTable::update(
				[
					'ENTITY_TYPE' => $push->getEntityType(),
					'ENTITY_ID' => $push->getEntityId(),
				],
				[
					'NOT_PROCESSED' => ProcessingStatus::Unblocked->value
				]
			);
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}
	}

	/**
	 * @param PushId $id
	 *
	 * @throws ArgumentTypeException
	 * @throws PersistenceException
	 */
	public function delete(mixed $id): void
	{
		if (!$id instanceof PushId)
		{
			throw new ArgumentTypeException('id', PushId::class);
		}

		try
		{
			$result = PushTable::delete($id->toArray());
		}
		catch (Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to delete push', null, $result->getErrors());
		}
	}
}
