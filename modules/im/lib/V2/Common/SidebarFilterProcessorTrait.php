<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\ORM\Query\Query;

trait SidebarFilterProcessorTrait
{
	protected static function processSidebarFilters(Query $query, array $filter, array $order, $fieldsMap = []): void
	{
		if (isset($filter['USER_ID']))
		{
			$query->whereIn($fieldsMap['AUTHOR_ID'] ?? 'AUTHOR_ID', $filter['USER_ID']);
		}

		if (isset($filter['LAST_ID']))
		{
			$operator = $order[$fieldsMap['ID'] ?? 'ID'] === 'DESC' ? '<' : '>';
			$query->where($fieldsMap['ID'] ?? 'ID', $operator, $filter['LAST_ID']);
		}

		if (isset($filter['CHAT_ID']))
		{
			$query->where($fieldsMap['CHAT_ID'] ?? 'CHAT_ID', $filter['CHAT_ID']);
		}

		if (isset($filter['DATE_FROM']))
		{
			$query->where($fieldsMap['DATE_CREATE'] ?? 'DATE_CREATE', '>=', $filter['DATE_FROM']);
		}

		if (isset($filter['DATE_TO']))
		{
			$query->where($fieldsMap['DATE_CREATE'] ?? 'DATE_CREATE', '<=', $filter['DATE_TO']);
		}

		if (isset($filter['START_ID']) && (int)$filter['START_ID'] > 0)
		{
			$query->where($fieldsMap['MESSAGE_ID'] ?? 'MESSAGE_ID', '>=', $filter['START_ID']);
		}
	}
}