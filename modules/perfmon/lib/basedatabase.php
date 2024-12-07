<?php

namespace Bitrix\Perfmon;

class BaseDatabase
{
	protected $connection = null;
	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	public static function createFromConnection($connection)
	{
		if (is_a($connection, '\Bitrix\Main\DB\MysqliConnection'))
		{
			return new MysqliDatabase($connection);
		}
		elseif (is_a($connection, '\Bitrix\Main\DB\PgsqlConnection'))
		{
			return new PgsqlDatabase($connection);
		}
		else
		{
			throw new \Bitrix\Main\DB\ConnectionException('Unsupported connection type.');
		}
	}

	public function getTables($full = true)
	{
		$result = new \CDBResult();
		$result->InitFromArray([]);

		return $result;
	}

	public function getIndexes($tableName)
	{
		return [];
	}

	public function getUniqueIndexes($tableName)
	{
		return [];
	}

	public function getTableFields($tableName = false)
	{
		return [];
	}
}