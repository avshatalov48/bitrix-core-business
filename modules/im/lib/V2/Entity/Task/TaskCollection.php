<?php

namespace Bitrix\Im\V2\Entity\Task;

use Bitrix\Im\V2\Entity\EntityCollection;

/**
 * @method TaskItem next()
 * @method TaskItem current()
 * @method TaskItem offsetGet($offset)
 */
class TaskCollection extends EntityCollection
{
	public static function getRestEntityName(): string
	{
		return 'tasks';
	}

	public static function initByDBResult(\CDBResult $result): self
	{
		$taskCollection = new static();

		while ($row = $result->Fetch())
		{
			$taskCollection[] = TaskItem::initByRow($row);
		}

		return $taskCollection;
	}
}