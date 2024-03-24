<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;

trait InsertSelectTrait
{
	public static function insertSelect(Query $query, array $fields, bool $isIgnore = true): void
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$tableName = $helper->quote(static::getTableName());
		$fields = implode(', ', array_map([$helper, 'quote'], $fields));

		if ($isIgnore)
		{
			$sql = $helper->getInsertIgnore(
				$tableName,
				" ({$fields}) ",
				$query->getQuery()
			);
		}
		else
		{
			$sql = "INSERT INTO {$tableName} ({$fields}) \n {$query->getQuery()} ";
		}

		$connection->queryExecute($sql);
	}
}