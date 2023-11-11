<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\ORM\Query\Query;

trait UpdateByFilterTrait
{
	/**
	 * @param array $filter
	 * @param array $fields
	 */
	public static function updateByFilter(array $filter, array $fields)
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$sqlHelper = $entity->getConnection()->getSqlHelper();

		$update = $sqlHelper->prepareUpdate($sqlTableName, $fields);
		$where = Query::buildFilterSql($entity, $filter);
		if ($where !== '' && $update[0] !== '')
		{
			$sql = "UPDATE {$sqlTableName} SET {$update[0]} WHERE {$where}";
			$entity->getConnection()->queryExecute($sql);
		}
	}
}