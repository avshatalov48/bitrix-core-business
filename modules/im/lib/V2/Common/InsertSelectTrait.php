<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;

trait InsertSelectTrait
{
	public static function insertSelect(\Bitrix\Main\ORM\Query\Query $query, bool $isIgnore = true): void
	{
		$tableName = static::getTableName();
		$ignore = $isIgnore ? 'IGNORE' : '';

		$sql = "INSERT {$ignore} INTO {$tableName} {$query->getQuery()}";

		Application::getConnection()->queryExecute($sql);
	}
}