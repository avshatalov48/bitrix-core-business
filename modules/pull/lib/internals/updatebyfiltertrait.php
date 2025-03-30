<?php

namespace Bitrix\Pull\Internals;

use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Query\Query;

trait UpdateByFilterTrait
{
	public static function updateByFilter(array $filter, array $fields): UpdateResult
	{
		$result = new UpdateResult();

		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$sqlHelper = $entity->getConnection()->getSqlHelper();

		$update = $sqlHelper->prepareUpdate($sqlTableName, $fields);
		try
		{
			$where = Query::buildFilterSql($entity, $filter);
		} catch (\Throwable $e)
		{
			return $result->addError(new Error("Could not construct the update query: {$e->getMessage()}"));
		}

		if ($where !== '' && $update[0] !== '')
		{
			$sql = "UPDATE {$sqlTableName} SET {$update[0]} WHERE {$where}";
			$entity->getConnection()->queryExecute($sql);
			$result->setAffectedRowsCount($entity->getConnection());

			static::cleanCache();
		}
		else
		{
			$result->addError(new Error("Could not execute the update query"));
		}

		return $result;
	}
}