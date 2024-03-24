<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;

trait MultiplyInsertTrait
{
	use DeadlockResolver;

	public static function multiplyInsertWithoutDuplicate(array $insertFields, array $params = []): void
	{
		if (empty($insertFields))
		{
			return;
		}

		if (($params['DEADLOCK_SAFE'] ?? false) && !empty($params['UNIQUE_FIELDS'] ?? []))
		{
			$insertFields = self::prepareFieldsToMinimizeDeadlocks($insertFields, $params['UNIQUE_FIELDS']);
		}
		$sqlHelper = Application::getConnection()->getSqlHelper();
		[$fields, $insertStatement] = static::prepareInsertMultiple($insertFields);
		$table = $sqlHelper->quote(static::getTableName());

		$sql = $sqlHelper->getInsertIgnore($table, "({$fields})", "VALUES {$insertStatement}");

		self::execute($sql, $params);
	}

	public static function multiplyMerge(array $insertFields, array $updateFields, array $uniqueFields = null, array $params = []): void
	{
		if (empty($insertFields))
		{
			return;
		}

		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$table = static::getTableName();
		if ($uniqueFields === null)
		{
			$entity = static::getEntity();
			$uniqueFields = $entity->getPrimaryArray();
		}

		if ($params['DEADLOCK_SAFE'] ?? false)
		{
			$insertFields = self::prepareFieldsToMinimizeDeadlocks($insertFields, $uniqueFields);
		}

		[, $insert] = static::prepareInsertMultiple($insertFields);
		$fields = array_keys(array_values($insertFields)[0]);

		$sql = $sqlHelper->prepareMergeSelect($table, $uniqueFields, $fields, " VALUES {$insert}", $updateFields);

		self::execute($sql, $params);
	}

	private static function prepareInsertMultiple(array $insertFields): array
	{
		$tableName = static::getTableName();
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$inserts = [];
		$fields = '';
		foreach ($insertFields as $insertField)
		{
			$insert = $sqlHelper->prepareInsert($tableName, $insertField);
			$fields = $insert[0];
			$inserts[] = "({$insert[1]})";
		}
		$insertStatement = implode(',', $inserts);

		return [$fields, $insertStatement];
	}

	private static function execute(string $sql, array $params): void
	{
		if (isset($params['DEADLOCK_SAFE']) && $params['DEADLOCK_SAFE'])
		{
			self::executeDeadlockSafeQuery($sql, $params['MAX_RETRY_COUNT'] ?? null);
		}
		else
		{
			Application::getConnection()->queryExecute($sql);
		}
	}
}