<?php

namespace Bitrix\Perfmon;

class MysqliDatabase extends BaseDatabase
{
	public function getTables($full = true)
	{
		$sqlHelper = $this->connection->getSqlHelper();
		$dbName = $this->connection->getDatabase();
		$tables = [];

		if ($full)
		{
			$tableList = $this->connection->query('show table status');
			while ($table = $tableList->fetch())
			{
				$tables[] = [
					'TABLE_NAME' => $table['Name'],
					'ENGINE_TYPE' => $table['Comment'] === 'VIEW' ? 'VIEW' : $table['Engine'],
					'NUM_ROWS' => $table['Rows'],
					'BYTES' => $table['Data_length'],
					'BYTES_INDEX' => $table['Index_length'],
				];
			}
		}
		else
		{
			$tableList = $this->connection->query('show tables from ' . $sqlHelper->quote($dbName));
			while ($table = $tableList->fetch())
			{
				$tables[] = [
					'TABLE_NAME' => $table['Tables_in_' . $dbName],
					'ENGINE_TYPE' => '',
					'NUM_ROWS' => '',
					'BYTES' => '',
					'BYTES_INDEX' => '',
				];
			}
		}

		$result = new \CDBResult();
		$result->InitFromArray($tables);

		return $result;
	}

	public function getIndexes($tableName = false)
	{
		$sqlHelper = $this->connection->getSqlHelper();
		$strSql = 'SHOW INDEXES FROM ' . $sqlHelper->quote($tableName);
		$result = [];
		try
		{
			$indexList = $this->connection->query($strSql);
			while ($indexColumn = $indexList->fetch())
			{
				$result[$indexColumn['Key_name']][$indexColumn['Seq_in_index']] = $indexColumn['Column_name'];
			}
		}
		catch (\Bitrix\Main\DB\SqlQueryException $_)
		{
		}

		return $result;
	}

	public function getUniqueIndexes($tableName = false)
	{
		$sqlHelper = $this->connection->getSqlHelper();
		$strSql = 'SHOW INDEXES FROM ' . $sqlHelper->quote($tableName);
		$result = [];
		try
		{
			$indexList = $this->connection->query($strSql);
			while ($indexColumn = $indexList->fetch())
			{
				if (!$indexColumn['Non_unique'])
				{
					$result[$indexColumn['Key_name']][$indexColumn['Seq_in_index']] = $indexColumn['Column_name'];
				}
			}
		}
		catch (\Bitrix\Main\DB\SqlQueryException $_)
		{
		}

		return $result;
	}

	public function getTableFields($tableName = false)
	{
		$sqlHelper = $this->connection->getSqlHelper();
		$strSql = 'SHOW COLUMNS FROM ' . $sqlHelper->quote($tableName);
		$columnList = $this->connection->query($strSql);
		$result = [];
		$resultExt = [];
		while ($column = $columnList->fetch())
		{
			$canSort = true;
			$match = [];
			if (preg_match('/^(varchar|char|varbinary)\\((\\d+)\\)/', $column['Type'], $match))
			{
				$column['DATA_TYPE'] = 'string';
				$column['DATA_LENGTH'] = $match[2];
				if ($match[2] == 1 && ($column['Default'] === 'N' || $column['Default'] === 'Y'))
				{
					$column['ORM_DATA_TYPE'] = 'boolean';
				}
				else
				{
					$column['ORM_DATA_TYPE'] = 'string';
				}
			}
			elseif (preg_match('/^(varchar|char)/', $column['Type']))
			{
				$column['DATA_TYPE'] = 'string';
				$column['ORM_DATA_TYPE'] = 'string';
			}
			elseif (preg_match('/^(text|longtext|mediumtext|longblob|mediumblob|blob)/', $column['Type']))
			{
				$canSort = false;
				$column['DATA_TYPE'] = 'string';
				$column['ORM_DATA_TYPE'] = 'text';
			}
			elseif (preg_match('/^(datetime|timestamp)/', $column['Type']))
			{
				$column['DATA_TYPE'] = 'datetime';
				$column['ORM_DATA_TYPE'] = 'datetime';
			}
			elseif (preg_match('/^(date)/', $column['Type']))
			{
				$column['DATA_TYPE'] = 'date';
				$column['ORM_DATA_TYPE'] = 'date';
			}
			elseif (preg_match('/^(int|smallint|bigint|tinyint|mediumint)/', $column['Type']))
			{
				$column['DATA_TYPE'] = 'int';
				$column['ORM_DATA_TYPE'] = 'integer';
			}
			elseif (preg_match('/^(float|double|decimal)/', $column['Type']))
			{
				$column['DATA_TYPE'] = 'double';
				$column['ORM_DATA_TYPE'] = 'float';
			}
			else
			{
				$canSort = false;
				$column['DATA_TYPE'] = 'unknown';
				$column['ORM_DATA_TYPE'] = 'UNKNOWN';
			}
			$result[$column['Field']] = $column['DATA_TYPE'];
			$resultExt[$column['Field']] = [
				'type' => $column['DATA_TYPE'],
				'length' => $column['DATA_LENGTH'] ?? null,
				'nullable' => $column['Null'] !== 'NO',
				'default' => $column['Default'],
				'sortable' => $canSort,
				'orm_type' => $column['ORM_DATA_TYPE'],
				'increment' => ($column['Extra'] === 'auto_increment'),
				'type~' => $column['Type'],
			];
		}

		return [$result, $resultExt];
	}
}