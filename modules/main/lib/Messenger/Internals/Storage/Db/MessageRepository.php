<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage\Db;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\MessageBoxCollection;
use Bitrix\Main\Messenger\Internals\Exception\Storage\MappingException;
use Bitrix\Main\Messenger\Internals\Exception\Storage\PersistenceException;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessengerMessageTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessageStatus;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class MessageRepository
{
	private MessageMapper $mapper;

	public function __construct(private readonly Entity $tableEntity)
	{
		$this->mapper = new MessageMapper($tableEntity);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getOneByQueue(string $queueId): ?MessageBox
	{
		$query = $this->buildReadyMessageQuery($queueId);

		$query->setLimit(1);

		$item = $query->fetchObject();

		if ($item === null)
		{
			return null;
		}

		return $this->getEntityFromOrmItem($item);
	}

	/**
	 * @throws MappingException
	 * @throws SystemException
	 */
	public function getReadyMessagesOfQueue(string $queueId, int $limit = 50): MessageBoxCollection
	{
		$query = $this->buildReadyMessageQuery($queueId);

		$query->setLimit($limit > 0 ? min($limit, 1000) : 50);

		$items = $query->fetchCollection();

		$collection = new MessageBoxCollection();

		if (!$items || $items->isEmpty())
		{
			return $collection;
		}

		foreach ($items as $ormItem)
		{
			if ($messageBox = $this->getEntityFromOrmItem($ormItem))
			{
				$collection->add($messageBox);
			}
			else
			{
				$ormItem->delete();
			}
		}

		return $collection;
	}

	/**
	 * @throws SystemException
	 */
	private function buildReadyMessageQuery(string $queueId): Query
	{
		/** @var MessengerMessageTable $tableClass */
		$tableClass = $this->tableEntity->getDataClass();

		$query = $tableClass::query();

		$query
			->setSelect(['*'])
			->where('QUEUE_ID', '=', $queueId)
			->where('STATUS', '=', MessageStatus::New->value)
			->where('AVAILABLE_AT', '<=', new DateTime())
			->setOrder(['CREATED_AT' => 'ASC'])
		;

		return $query;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getStaleMessages(): MessageBoxCollection
	{
		$collection = new MessageBoxCollection();

		/** @var MessengerMessageTable $tableClass */
		$tableClass = $this->tableEntity->getDataClass();

		$thresholdDate = DateTime::createFromTimestamp(time() - 2 * 86400);

		$query = $tableClass::query();

		$query
			->setSelect(['*'])
			->where('STATUS', '=', MessageStatus::Processing->value)
			->where('UPDATED_AT', '<', $thresholdDate)
			->setOrder(['CREATED_AT' => 'ASC'])
		;

		$items = $query->fetchCollection();

		if (!$items || $items->isEmpty())
		{
			return $collection;
		}

		foreach ($items as $ormItem)
		{
			if ($messageBox = $this->getEntityFromOrmItem($ormItem))
			{
				$collection->add($messageBox);
			}
			else
			{
				$ormItem->delete();
			}
		}

		return $collection;
	}

	/**
	 * @throws PersistenceException
	 */
	public function save(MessageBox $messageBox): void
	{
		try
		{
			$result = $this->mapper->convertToOrm($messageBox)->save();
		}
		catch (\Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
		}

		if ($result->isSuccess() && !$messageBox->getId())
		{
			$messageBox->setId($result->getId());
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to save message: ' . $result->getError()->getMessage());
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function delete(MessageBox $message): void
	{
		try
		{
			$result = $this->mapper->convertToOrm($message)->delete();
		}
		catch (\Exception $e)
		{
			throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to delete message: ' . $result->getError()->getMessage());
		}
	}

	/**
	 * @throws PersistenceException
	 */
	public function updateStatus(MessageBoxCollection $items, MessageStatus $status): void
	{
		if ($items->isEmpty())
		{
			return;
		}

		$ids = array_map(
			function (MessageBox $item)
			{
				return $item->getId();
			},
			$items->toArray()
		);

		try
		{
			/** @var MessengerMessageTable $tableClass */
			$tableClass = $this->tableEntity->getDataClass();

			$result = $tableClass::updateMulti($ids, ['STATUS' => $status->value], true);
		}
		catch (SystemException $e)
		{
			throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
		}

		if (!$result->isSuccess())
		{
			throw new PersistenceException('Unable to update status: ' . $result->getError()->getMessage());
		}
	}

	private function getEntityFromOrmItem($ormItem): ?MessageBox
	{
		try
		{
			return $this->mapper->convertFromOrm($ormItem);
		}
		catch (ArgumentException)
		{
			return null;
		}
	}
}
