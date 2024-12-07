<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Main\Application;

trait InsertIgnoreTrait
{
	// TODO: create trait in main
	public static function insertIgnore(array $insertFields): void
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();

		$tableName = $sqlHelper->quote(self::getTableName());
		[$columns, $values] = $sqlHelper->prepareInsert(self::getTableName(), $insertFields);

		$sql = $sqlHelper->getInsertIgnore($tableName, "($columns)", "VALUES ($values)");

		Application::getConnection()->queryExecute($sql);
	}

	public static function insertIgnoreMulti(array $insertRows): void
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();

		$tableName = $sqlHelper->quote(self::getTableName());
		[$columns, $inserts] = self::prepareInsertMulti($insertRows);

		if (!$columns || !$inserts)
		{
			throw new \Exception(sprintf('wrong insert fields for %s', $tableName));
		}

		$sql = $sqlHelper->getInsertIgnore($tableName, "($columns)", "VALUES $inserts");

		Application::getConnection()->queryExecute($sql);
	}

	private static function prepareInsertMulti(array $fields): array
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$inserts = [];
		$columns = null;

		foreach ($fields as $insertField)
		{
			$insert = $sqlHelper->prepareInsert(self::getTableName(), $insertField);
			if (!$columns)
			{
				$columns = $insert[0];
			}
			$inserts[] = "({$insert[1]})";
		}

		return [$columns, implode(',', $inserts)];
	}
}
