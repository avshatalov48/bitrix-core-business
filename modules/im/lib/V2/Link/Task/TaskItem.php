<?php

namespace Bitrix\Im\V2\Link\Task;

use Bitrix\Im\Model\LinkTaskTable;
use Bitrix\Im\Model\EO_LinkTask;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Type\DateTime;

class TaskItem extends BaseLinkItem
{

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
		$chatTask = LinkTaskTable::query()
			->setSelect(['ID', 'MESSAGE_ID', 'CHAT_ID', 'TASK_ID', 'AUTHOR_ID', 'DATE_CREATE'])
			->where('TASK_ID', $entity->getTaskId())
			->setLimit(1)
			->fetchObject()
		;

		if ($chatTask === null)
		{
			return null;
		}

		$taskItem = new static($chatTask);
		$taskItem->setEntity($entity);

		return $taskItem;
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
}