<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor;


use Bitrix\Main\Application;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterTable;

trait CommandTrait
{
	public static function reset(int $userId = 0, array $types = [], array $logIds = [], array $groupIds = [], array $exceptGroups = []): void
	{
		$where = [];

		if ($userId)
		{
			$where[] = 'USER_ID = ' . $userId;
		}

		if (!empty($types))
		{
			$where[] = "TYPE IN ('". implode("','", $types) ."')";
		}

		if (!empty($logIds))
		{
			$where[] = "SONET_LOG_ID IN (". implode(",", $logIds) .")";
		}

		if (!empty($groupIds))
		{
			$where[] = "GROUP_ID IN (". implode(",", $groupIds) .")";
		}

		if (!empty($exceptGroups))
		{
			$where[] = "GROUP_ID NOT IN (". implode(",", $exceptGroups) .")";
		}

		$where = (!empty($where)) ? ('WHERE ' . implode(' AND ', $where)) : '';

		$sql = "
			DELETE
			FROM ". CounterTable::getTableName(). "
			{$where}
		";

		Application::getConnection()->query($sql);
	}

	private function saveFlag(int $userId): void
	{
		$sql = "
			INSERT INTO ". CounterTable::getTableName() ."
			(USER_ID, SONET_LOG_ID, GROUP_ID, TYPE, VALUE)
			VALUES ({$userId}, 0, 0, '". CounterDictionary::COUNTER_FLAG_COUNTED ."', 1)
		";
		Application::getConnection()->query($sql);
	}

	private function batchInsert(array $data): void
	{
		$req = [];
		foreach ($data as $row)
		{
			$row['TYPE'] = "'". $row['TYPE'] ."'";
			$req[] = implode(',', $row);
		}

		if (empty($req))
		{
			return;
		}

		$sql = "
			INSERT INTO ". CounterTable::getTableName(). "
			(USER_ID, SONET_LOG_ID, GROUP_ID, TYPE, VALUE)
			VALUES
			(". implode("),(", $req) .")
		";

		Application::getConnection()->query($sql);
	}
}