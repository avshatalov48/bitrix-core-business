<?php

namespace Bitrix\Im\V2\Link\Task;

use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Tasks\Internals\SearchIndex;
use Bitrix\Tasks\Provider\TaskList;
use Bitrix\Tasks\Provider\TaskQuery;

/**
 * @implements \IteratorAggregate<int,TaskItem>
 * @method TaskItem offsetGet($key)
 */
class TaskCollection extends BaseLinkCollection
{
	public const SELECT_FIELDS = [
		'ID',
		'TITLE',
		'REAL_STATUS',
		'DEADLINE',
		'CREATED_BY',
		'RESPONSIBLE_ID',
		'CREATED_DATE',
		'IM_CHAT_ID',
		'IM_CHAT_MESSAGE_ID',
		'IM_CHAT_CHAT_ID',
		'IM_CHAT_AUTHOR_ID',
	];

	public static function getCollectionElementClass(): string
	{
		return TaskItem::class;
	}

	public static function initByTaskQuery(TaskQuery $taskQuery): self
	{
		$tasksArray = (new TaskList())->getList($taskQuery);

		$linkCollection = new static();

		foreach ($tasksArray as $row)
		{
			$linkCollection->add(TaskItem::initByRow($row));
		}

		return $linkCollection;
	}

	public static function find(
		array $filter,
		array $order = ['ID' => 'DESC'],
		?int $limit = null,
		?Context $context = null
	): self
	{
		$context = $context ?? Locator::getContext();

		$taskQuery = new TaskQuery($context->getUserId());

		$taskOrder = [];
		if (isset($order['ID']))
		{
			$taskOrder['IM_CHAT_ID'] = $order['ID'];
		}

		$taskFilter = static::processFilters($filter, $taskOrder);

		$taskQuery
			->setSelect(static::SELECT_FIELDS)
			->setOrder($taskOrder)
			->setWhere($taskFilter)
		;

		if (isset($limit))
		{
			$taskQuery->setLimit($limit);
		}

		return static::initByTaskQuery($taskQuery);
	}

	protected static function processFilters(array $filter, array $order): array
	{
		$result = [];

		if (isset($filter['CHAT_ID']))
		{
			$result['IM_CHAT_CHAT_ID'] = (int)$filter['CHAT_ID'];
		}
		if (isset($filter['USER_ID']))
		{
			$usersIds = $filter['USER_ID'];
			if (!empty($usersIds))
			{
				$result['::SUBFILTER-MEMBER'] = [
					'::LOGIC' => 'OR',
					'CREATED_BY' => $usersIds,
					'RESPONSIBLE_ID' => $usersIds,
					'ACCOMPLICE' => $usersIds,
					'AUDITOR' => $usersIds,
				];
			}
		}
		if (isset($filter['DATE_FROM']))
		{
			$result['>=CREATED_DATE'] = $filter['DATE_FROM'];
		}
		if (isset($filter['DATE_TO']))
		{
			$result['<=CREATED_DATE'] = $filter['DATE_TO'];
		}
		if (isset($filter['SEARCH_TASK_NAME']))
		{
			$result['::SUBFILTER-FULL_SEARCH_INDEX'] = [
				'*FULL_SEARCH_INDEX' => SearchIndex::prepareStringToSearch($filter['SEARCH_TASK_NAME'])
			];
		}
		if (isset($filter['LAST_ID']))
		{
			$operator = '<';
			if (isset($order['IM_CHAT_ID']) && $order['IM_CHAT_ID'] === 'ASC')
			{
				$operator = '>';
			}
			$result["{$operator}IM_CHAT_ID"] = (int)$filter['LAST_ID'];
		}

		return $result;
	}
}