<?php

namespace Bitrix\Im\V2\Entity\Task;

use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Provider\TaskList;
use Bitrix\Tasks\Provider\TaskQuery;

/**
 * @implements \IteratorAggregate<int,TaskItem>
 * @method TaskItem offsetGet($key)
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

	public static function getByIds(array $ids, ?Context $context = null): self
	{
		$taskCollection = new static();

		if (empty($ids) || !Loader::includeModule('tasks'))
		{
			return $taskCollection->setContext($context);
		}

		$context = $context ?? Locator::getContext();
		$taskQuery = new TaskQuery($context->getUserId());
		$taskQuery
			->setSelect(\Bitrix\Im\V2\Link\Task\TaskCollection::SELECT_FIELDS)
			->setWhere(['=ID' => $ids])
		;
		$rows = (new TaskList())->getList($taskQuery);

		foreach ($rows as $row)
		{
			$taskCollection[] = TaskItem::initByRow($row);
		}

		return $taskCollection;
	}
}