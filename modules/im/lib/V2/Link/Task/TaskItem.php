<?php

namespace Bitrix\Im\V2\Link\Task;

use Bitrix\Im\Model\LinkTaskTable;
use Bitrix\Im\Model\EO_LinkTask;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Type\DateTime;

class TaskItem extends BaseLinkItem
{
	protected static array $cache = [];

	/**
	 * @param int|array|EO_LinkTask|null $source
	 */
	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public static function getDataClass(): string
	{
		return LinkTaskTable::class;
	}

	public static function getEntityClassName(): string
	{
		return \Bitrix\Im\V2\Entity\Task\TaskItem::class;
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	public static function initByRow(array $row): self
	{
		$entity = \Bitrix\Im\V2\Entity\Task\TaskItem::initByRow($row);
		$link = new static();
		$link
			->setEntity($entity)
			->setId($row['IM_CHAT_ID'])
			->setChatId($row['IM_CHAT_CHAT_ID'])
			->setMessageId($row['IM_CHAT_MESSAGE_ID'])
			->setAuthorId($row['IM_CHAT_AUTHOR_ID'])
			->setDateCreate(new DateTime($row['CREATED_DATE']))
		;

		return $link;
	}

	public static function getByEntity(\Bitrix\Im\V2\Entity\Task\TaskItem $entity): ?self
	{
		$taskItem = self::getByEntityInternal($entity);
		if ($taskItem === null)
		{
			return null;
		}

		$taskItem->setEntity($entity);

		return $taskItem;
	}

	protected static function getByEntityInternal(\Bitrix\Im\V2\Entity\Task\TaskItem $entity): ?self
	{
		if (array_key_exists($entity->getTaskId(), self::$cache))
		{
			return self::$cache[$entity->getTaskId()];
		}

		$chatTask = LinkTaskTable::query()
			->setSelect(['ID', 'MESSAGE_ID', 'CHAT_ID', 'TASK_ID', 'AUTHOR_ID', 'DATE_CREATE'])
			->where('TASK_ID', $entity->getTaskId())
			->setLimit(1)
			->fetchObject()
		;

		if ($chatTask === null)
		{
			self::$cache[$entity->getTaskId()] = null;

			return null;
		}

		self::$cache[$entity->getTaskId()] = new static($chatTask);

		return self::$cache[$entity->getTaskId()];
	}

	public static function getByMessageId(int $messageId): ?self
	{
		$chatTask = LinkTaskTable::query()
			->setSelect(['ID', 'MESSAGE_ID', 'CHAT_ID', 'TASK_ID', 'AUTHOR_ID', 'DATE_CREATE'])
			->where('MESSAGE_ID', $messageId)
			->setLimit(1)
			->fetchObject()
		;

		if ($chatTask === null)
		{
			return null;
		}

		return new static($chatTask);
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'TASK_ID';
	}

	/**
	 * @return \Bitrix\Im\V2\Entity\Task\TaskItem
	 */
	public function getEntity(): RestEntity
	{
		if (isset($this->entity))
		{
			return $this->entity;
		}

		if ($this->getEntityId() !== null)
		{
			$entity = \Bitrix\Im\V2\Entity\Task\TaskItem::getById($this->getEntityId());
			if (isset($entity))
			{
				$this->entity = $entity;
			}
		}

		return $this->entity;
	}

	public static function cleanCache(int $taskId): void
	{
		unset(self::$cache[$taskId]);
	}
}
