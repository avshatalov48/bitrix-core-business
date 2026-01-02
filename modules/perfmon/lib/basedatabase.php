<?php

namespace Bitrix\Perfmon;

class BaseDatabase
{
	protected $connection = null;
	protected $schema = null;

	public function __construct($connection)
	{
		$this->connection = $connection;
		$this->schema = new \Bitrix\Perfmon\Sql\Schema;
	}

	/**
	 * Creates BaseDatabase object instance depending on type of the $connection.
	 *
	 * @param \Bitrix\Main\DB\Connection $connection The connection.
	 *
	 * @return BaseDatabase
	 */
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

	/**
	 * Returns the database tables list.
	 *
	 * @param bool $full If extended info is needed.
	 *
	 * @return \CDBResult
	 */
	public function getTables($full = true)
	{
		$result = new \CDBResult();
		$result->InitFromArray([]);

		return $result;
	}

	/**
	 * Reads meta information from the database about table indexes into $this->schema object.
	 *
	 * @param string $tableName Table name.
	 *
	 * @return \Bitrix\Perfmon\Sql\Table
	 */
	protected function fillTableIndexes($tableName)
	{
		$table = $this->schema->tables->search($tableName);
		if (!$table)
		{
			$table = new \Bitrix\Perfmon\Sql\Table($tableName);
			$this->schema->tables->add($table);
		}

		return $table;
	}

	/**
	 * Returns list of all table indexes.
	 *
	 * @param string $tableName Table name.
	 *
	 * @return array
	 */
	public function getIndexes($tableName)
	{
		$result = [];

		$table = $this->fillTableIndexes($tableName);
		/** @var $index \Bitrix\Perfmon\Sql\Index */
		foreach ($table->indexes->getList() as $index)
		{
			$result[$index->name] = $index->columns;
		}

		return $result;
	}

	/**
	 * Returns list of unique table indexes.
	 *
	 * @param string $tableName Table name.
	 *
	 * @return array
	 */
	public function getUniqueIndexes($tableName)
	{
		$result = [];

		$table = $this->fillTableIndexes($tableName);
		/** @var $index \Bitrix\Perfmon\Sql\Index */
		foreach ($table->indexes->getList() as $index)
		{
			if ($index->unique)
			{
				$result[$index->name] = $index->columns;
			}
		}

		return $result;
	}

	/**
	 * Returns list of full text table indexes.
	 *
	 * @param string $tableName Table name.
	 *
	 * @return array
	 */
	public function getFullTextIndexes($tableName)
	{
		$result = [];

		$table = $this->fillTableIndexes($tableName);
		/** @var $index \Bitrix\Perfmon\Sql\Index */
		foreach ($table->indexes->getList() as $index)
		{
			if ($index->fulltext)
			{
				$result[$index->name] = $index->columns;
			}
		}

		return $result;
	}

	/**
	 * Returns list of all table fields.
	 *
	 * @param string $tableName Table name.
	 *
	 * @return array
	 */
	public function getTableFields($tableName = false)
	{
		return [];
	}
}
