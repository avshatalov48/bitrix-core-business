<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;

trait DeleteTrait
{
	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter does not look like filter in getList. It depends by current implementation.
	 * @return void
	 */
	public static function deleteBatch(array $filter)
	{
		$whereSql = Query::buildFilterSql(static::getEntity(), $filter);

		if ($whereSql !== '')
		{
			$tableName = static::getTableName();
			$connection = Application::getConnection();
			$connection->queryExecute("DELETE FROM {$tableName} WHERE {$whereSql}");
		}
	}
}
