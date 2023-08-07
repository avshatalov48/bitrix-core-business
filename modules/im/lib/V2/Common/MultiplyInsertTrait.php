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

		$sqlHelper = Application::getConnection()->getSqlHelper();
		$inserts = [];
		$fields = '';
		foreach ($insertFields as $insertField)
		{
			$insert = $sqlHelper->prepareInsert(static::getTableName(), $insertField);
			$fields = $insert[0];
			$inserts[] = "({$insert[1]})";
		}
		$insertStatement = implode(',', $inserts);
		$table = $sqlHelper->quote(static::getTableName());

		$sql = "
			INSERT IGNORE INTO {$table} ({$fields})
			VALUES {$insertStatement};
		";

		if (isset($params['DEADLOCK_SAFE']) && $params['DEADLOCK_SAFE'])
		{
			self::executeDeadlockSafeQuery($sql, $params['MAX_RETRY_COUNT'] ?? null);
		}
		else
		{
			Application::getConnection()->queryExecute($sql);
		}
	}

	public static function multiplyMerge(array $insertFields, array $updateFields): void
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$inserts = [];
		$fields = '';
		foreach ($insertFields as $insertField)
		{
			$insert = $sqlHelper->prepareInsert(static::getTableName(), $insertField);
			$fields = $insert[0];
			$inserts[] = "({$insert[1]})";
		}
		$insertStatement = implode(',', $inserts);
		$table = $sqlHelper->quote(static::getTableName());

		$updateStatement = $sqlHelper->prepareUpdate(static::getTableName(), $updateFields)[0];

		$sql = "
			INSERT INTO {$table} ({$fields})
			VALUES {$insertStatement}
			ON DUPLICATE KEY UPDATE {$updateStatement};
		";

		Application::getConnection()->queryExecute($sql);
	}
}