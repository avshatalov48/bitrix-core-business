<?php

namespace Bitrix\Perfmon;

class PgsqlDatabase extends BaseDatabase
{
	/**
	 * @inheritDoc
	 */
	public function getTables($full = true)
	{
		$tables = [];

		if ($full)
		{
			$tableList = $this->connection->query("
				select
					table_name
					,pg_table_size(quote_ident(table_name)) data_length
					,pg_indexes_size(quote_ident(table_name)) index_length
					,pg_catalog.pg_class.reltuples row_count
				from
					information_schema.tables
					left join pg_catalog.pg_class on pg_catalog.pg_class.oid = quote_ident(table_name)::regclass::oid
				where
					table_schema = 'public'
			");
			while ($table = $tableList->fetch())
			{
				$tables[] = [
					'TABLE_NAME' => $table['TABLE_NAME'],
					'ENGINE_TYPE' => '',
					'NUM_ROWS' => $table['ROW_COUNT'],
					'BYTES' => $table['DATA_LENGTH'],
					'BYTES_INDEX' => $table['INDEX_LENGTH'],
				];
			}
		}
		else
		{
			$tableList = $this->connection->query("
				select
					table_name
				from
					information_schema.tables
				where
					table_schema = 'public'
			");
			while ($table = $tableList->fetch())
			{
				$tables[] = [
					'TABLE_NAME' => $table['TABLE_NAME'],
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

	/**
	 * @inheritDoc
	 */
	protected function fillTableIndexes($tableName)
	{
		$table = $this->schema->tables->search($tableName);
		if (!$table)
		{
			$table = new \Bitrix\Perfmon\Sql\Table($tableName);

			$sqlHelper = $this->connection->getSqlHelper();
			$indexColumns = [];
			$unique = [];
			$fulltext = [];
			try
			{
				$tableColumns = [];
				$columnList = $this->connection->query("
					SELECT a.attnum, a.attname
					FROM pg_class t
					LEFT JOIN pg_attribute a ON a.attrelid = t.oid
					WHERE t.relname = '" . $sqlHelper->forSql($tableName) . "'
				");
				while ($column = $columnList->fetch())
				{
					if ($column['ATTNUM'] > 0)
					{
						$tableColumns[$column['ATTNUM']] = $column['ATTNAME'];
					}
				}

				$indexList = $this->connection->query("
					SELECT relname, indkey, indisprimary, indisunique, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
					FROM pg_class, pg_index
					WHERE pg_class.oid = pg_index.indexrelid
					AND pg_class.oid IN (
						SELECT indexrelid
						FROM pg_index, pg_class
						WHERE pg_class.relname = '" . $sqlHelper->forSql($tableName) . "'
						AND pg_class.oid = pg_index.indrelid
					)
					ORDER BY indisprimary desc
				");

				while ($indexColumn = $indexList->fetch())
				{
					$indexColumns[$indexColumn['RELNAME']] = [];
					$unique[$indexColumn['RELNAME']] = $indexColumn['INDISPRIMARY'] === 't' || $indexColumn['INDISUNIQUE'] === 't';
					$fulltext[$indexColumn['RELNAME']] = !empty($indexColumn['FULL_TEXT']);
					if ($indexColumn['FULL_TEXT'])
					{
						$match = [];
						if (preg_match_all('/,\s*([a-z0-9_]+)/i', $indexColumn['FULL_TEXT'], $match))
						{
							foreach ($match[1] as $i => $colName)
							{
								$indexColumns[$indexColumn['RELNAME']][$i] = mb_strtoupper($colName);
							}
						}
					}
					else
					{
						foreach (explode(' ', $indexColumn['INDKEY']) as $i => $indkey)
						{
							$indexColumns[$indexColumn['RELNAME']][$i] = mb_strtoupper($tableColumns[$indkey]);
						}
					}
				}
			}
			catch (\Bitrix\Main\DB\SqlQueryException $_)
			{
			}

			foreach ($indexColumns as $indexName => $columns)
			{
				$index = new \Bitrix\Perfmon\Sql\Index($indexName, $unique[$indexName], $fulltext[$indexName]);
				$index->columns = array_values($columns);
				$table->indexes->add($index);
			}
			$this->schema->tables->add($table);
		}

		return $table;
	}

	/**
	 * @inheritDoc
	 */
	public function getTableFields($tableName = false)
	{
		$sqlHelper = $this->connection->getSqlHelper();

		$strSql = "
			SELECT *
			FROM information_schema.columns
			WHERE table_schema = 'public'
			AND table_name = '" . $sqlHelper->forSql($tableName) . "'
		";
		$columnList = $this->connection->query($strSql);
		$result = [];
		$resultExt = [];
		while ($column = $columnList->fetch())
		{
			$canSort = true;
			$match = [];
			switch ($column['DATA_TYPE'])
			{
				case 'character varying':
					$dataType = 'string';
					$ormDataType = 'string';
					break;
				case 'character':
					$dataType = 'string';
					if (
						$column['CHARACTER_MAXIMUM_LENGTH'] == 1
						&& (
							substr($column['COLUMN_DEFAULT'], 0, 3) === "'N'"
							|| substr($column['COLUMN_DEFAULT'], 0, 3) === "'Y'"
						)
					)
					{
						$column['COLUMN_DEFAULT'] = $column['COLUMN_DEFAULT'][1];
						$ormDataType = 'boolean';
					}
					else
					{
						$ormDataType = 'string';
					}
					break;
				case 'text':
				case 'bytea':
					$canSort = false;
					$dataType = 'string';
					$ormDataType = 'string';
					break;
				case 'bigint':
				case 'bigserial':
				case 'int':
				case 'int2':
				case 'int4':
				case 'int8':
				case 'integer':
				case 'serial':
				case 'serial2':
				case 'serial4':
				case 'serial8':
				case 'smallint':
				case 'smallserial':
					$dataType = 'int';
					$ormDataType = 'integer';
					break;
				case 'double precision':
				case 'float4':
				case 'float8':
				case 'numeric':
				case 'real':
					$dataType = 'double';
					$ormDataType = 'float';
					break;
				case 'timestamp without time zone':
					$dataType = 'datetime';
					$ormDataType = 'datetime';
					break;
				case 'date':
					$dataType = 'date';
					$ormDataType = 'date';
					break;
				default:
					$canSort = false;
					$dataType = 'unknown';
					$ormDataType = 'UNKNOWN';
					break;
			}

			$result[mb_strtoupper($column['COLUMN_NAME'])] = $dataType;
			$resultExt[mb_strtoupper($column['COLUMN_NAME'])] = [
				'type' => $dataType,
				'length' => $column['CHARACTER_MAXIMUM_LENGTH'],
				'nullable' => $column['IS_NULLABLE'] !== 'NO',
				'default' => preg_match('/^\'(.*)\'::/', $column['COLUMN_DEFAULT'], $match) ? $match[1] : $column['COLUMN_DEFAULT'],
				'sortable' => $canSort,
				'orm_type' => $ormDataType,
				'increment' => strpos($column['COLUMN_DEFAULT'], 'nextval(') !== false || $column['IS_IDENTITY'] === 'YES',
				'info' => $column,
			];
		}

		return [$result, $resultExt];
	}
}
