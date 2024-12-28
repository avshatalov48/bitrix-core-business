<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\Model\RecentInitQueueTable;
use Bitrix\Im\V2\Recent\Initializer\Queue\QueueItem;
use Bitrix\Main\Application;

class QueueService
{
	protected const LOCK_TTL = 1;
	protected const LOCK_NAME = 'recent_init_dequeue';

	protected static self $instance;

	private function __construct()
	{
	}

	public static function getInstance(): static
	{
		self::$instance ??= new static();

		return self::$instance;
	}

	public function save(?QueueItem $queueItem): ?QueueItem
	{
		if ($queueItem === null)
		{
			return null;
		}

		if ($queueItem->id === 0)
		{
			return $this->add($queueItem);
		}

		return $this->update($queueItem);
	}

	protected function add(QueueItem $queueItem): ?QueueItem
	{
		if ($queueItem->id !== 0)
		{
			return $queueItem;
		}

		$result = RecentInitQueueTable::add($this->getFieldsForAddOrUpdate($queueItem));

		if (!$result->isSuccess())
		{
			return null;
		}

		$id = $result->getId();

		return $queueItem->setId($id);
	}

	public function addMulti(array $items): void
	{
		RecentInitQueueTable::addMulti($this->getFieldsForMultiAdd($items), true);
	}

	protected function getFieldsForMultiAdd(array $items): array
	{
		return array_map(fn (QueueItem $item) => $this->getFieldsForAddOrUpdate($item), $items);
	}

	protected function update(QueueItem $queueItem): ?QueueItem
	{
		if ($queueItem->id === 0)
		{
			return null;
		}

		RecentInitQueueTable::update($queueItem->id, $this->getFieldsForAddOrUpdate($queueItem));

		return $queueItem;
	}

	protected function getFieldsForAddOrUpdate(QueueItem $queueItem): array
	{
		$fields = $queueItem->getFields();
		unset($fields['ID']);

		return $fields;
	}

	public function getFirst(): ?QueueItem
	{
		$lock = $this->lock();
		if (!$lock)
		{
			return null;
		}

		try
		{
			return $this->getFirstInternal()?->lock();
		}
		finally
		{
			$this->unlock();
		}
	}

	public function isQueueEmpty(): bool
	{
		$row = RecentInitQueueTable::query()
			->setSelect(['ID'])
			->setLimit(1)
			->fetch()
		;

		return $row === false;
	}

	public function delete(QueueItem $queueItem): void
	{
		if ($queueItem->id)
		{
			RecentInitQueueTable::delete($queueItem->id);
		}
	}

	protected function getFirstInternal(): ?QueueItem
	{
		$row = RecentInitQueueTable::query()
			->setSelect(['*'])
			->where('IS_LOCKED', false)
			->setLimit(1)
			->setOrder(['ID' => 'ASC'])
			->fetch()
		;

		if (!$row)
		{
			return null;
		}

		return QueueItem::createFromRow($row);
	}

	protected function lock(): bool
	{
		return Application::getConnection(RecentInitQueueTable::getConnectionName())
			->lock(self::LOCK_NAME, self::LOCK_TTL)
		;
	}

	protected function unlock(): bool
	{
		return Application::getConnection(RecentInitQueueTable::getConnectionName())->unlock(self::LOCK_NAME);
	}
}
