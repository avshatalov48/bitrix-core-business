<?php

class CPerfomanceTableList extends CDBResult
{
	public static function GetList($bFull = true)
	{
		global $DB;

		$rsTables = $DB->Query("
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
				" . ($bFull ? '' : "and table_catalog = '" . $DB->ForSql($DB->DBName) . "'") . '
		');

		return new CPerfomanceTableList($rsTables);
	}

	public function Fetch()
	{
		$ar = parent::Fetch();
		if ($ar)
		{
				$ar = [
					'TABLE_NAME' => $ar['TABLE_NAME'],
					'ENGINE_TYPE' => '',
					'NUM_ROWS' => $ar['ROW_COUNT'],
					'BYTES' => $ar['DATA_LENGTH'],
					'BYTES_INDEX' => $ar['INDEX_LENGTH'],
				];
		}
		return $ar;
	}
}

class CPerfomanceTable extends CAllPerfomanceTable
{
	public function Init($TABLE_NAME)
	{
		$TABLE_NAME = trim($TABLE_NAME, '`');
		$this->TABLE_NAME = $TABLE_NAME;
	}

	public function IsExists($TABLE_NAME = false)
	{
		global $DB;

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}
		if ($TABLE_NAME == '')
		{
			return false;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');

		return $DB->TableExists($TABLE_NAME);
	}

	public function GetIndexes($TABLE_NAME = false)
	{
		global $DB;
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}
		if ($TABLE_NAME == '')
		{
			return [];
		}

		$TABLE_NAME = trim($TABLE_NAME, '`"');

		if (!array_key_exists($TABLE_NAME, $cache))
		{
			$tableColumns = [];
			$r = $DB->Query("
				SELECT a.attnum, a.attname
				FROM pg_class t
				LEFT JOIN pg_attribute a ON a.attrelid = t.oid
				WHERE t.relname = '" . $DB->ForSql($TABLE_NAME) . "'
			");
			while ($a = $r->fetch())
			{
				if ($a['ATTNUM'] > 0)
				{
					$tableColumns[$a['ATTNUM']] = $a['ATTNAME'];
				}
			}

			$r = $DB->Query("
				SELECT relname, indkey, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
				FROM pg_class, pg_index
				WHERE pg_class.oid = pg_index.indexrelid
				AND pg_class.oid IN (
					SELECT indexrelid
					FROM pg_index, pg_class
					WHERE pg_class.relname = '" . $DB->ForSql($TABLE_NAME) . "'
					AND pg_class.oid = pg_index.indrelid
				)
			");
			$arResult = [];
			while ($a = $r->fetch())
			{
				$arResult[$a['RELNAME']] = [];
				if ($a['FULL_TEXT'])
				{
					$match = [];
					if (preg_match_all('/,\s*([a-z0-9_]+)/i', $a['FULL_TEXT'], $match))
					{
						foreach ($match[1] as $i => $colName)
						{
							$arResult[$a['RELNAME']][$i] = mb_strtoupper($colName);
						}
					}
				}
				else
				{
					foreach (explode(' ', $a['INDKEY']) as $i => $indkey)
					{
						$arResult[$a['RELNAME']][$i] = mb_strtoupper($tableColumns[$indkey]);
					}
				}
			}

			$cache[$TABLE_NAME] = $arResult;
		}

		return $cache[$TABLE_NAME];
	}

	public function GetUniqueIndexes($TABLE_NAME = false)
	{
		global $DB;
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}
		if ($TABLE_NAME == '')
		{
			return [];
		}

		$TABLE_NAME = trim($TABLE_NAME, '`"');

		if (!array_key_exists($TABLE_NAME, $cache))
		{
			$tableColumns = [];
			$r = $DB->Query("
				SELECT a.attnum, a.attname
				FROM pg_class t
				LEFT JOIN pg_attribute a ON a.attrelid = t.oid
				WHERE t.relname = '" . $DB->ForSql($TABLE_NAME) . "'
			");
			while ($a = $r->fetch())
			{
				if ($a['ATTNUM'] > 0)
				{
					$tableColumns[$a['ATTNUM']] = $a['ATTNAME'];
				}
			}

			$r = $DB->Query("
				SELECT relname, indkey, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
				FROM pg_class, pg_index
				WHERE pg_class.oid = pg_index.indexrelid
				AND pg_class.oid IN (
					SELECT indexrelid
					FROM pg_index, pg_class
					WHERE pg_class.relname = '" . $DB->ForSql($TABLE_NAME) . "'
					AND pg_class.oid = pg_index.indrelid
				)
				AND (indisprimary OR indisunique)
			");
			$arResult = [];
			while ($a = $r->fetch())
			{
				$arResult[$a['RELNAME']] = [];
				if ($a['FULL_TEXT'])
				{
					$match = [];
					if (preg_match_all('/,\s*([a-z0-9_]+)/i', $a['FULL_TEXT'], $match))
					{
						foreach ($match[1] as $i => $colName)
						{
							$arResult[$a['RELNAME']][$i] = mb_strtoupper($colName);
						}
					}
				}
				else
				{
					foreach (explode(' ', $a['INDKEY']) as $i => $indkey)
					{
						$arResult[$a['RELNAME']][$i] = mb_strtoupper($tableColumns[$indkey]);
					}
				}
			}

			$cache[$TABLE_NAME] = $arResult;
		}

		return $cache[$TABLE_NAME];
	}

	public function GetTableFields($TABLE_NAME = false, $bExtended = false)
	{
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}
		if ($TABLE_NAME == '')
		{
			return false;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');

		if (!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SELECT *
				FROM information_schema.columns
				WHERE table_schema = 'public'
				AND table_name = '" . $DB->ForSql($TABLE_NAME) . "'
			";
			$rs = $DB->Query($strSql);
			$arResult = [];
			$arResultExt = [];
			while ($ar = $rs->Fetch())
			{
				$canSort = true;
				$match = [];
				switch ($ar['DATA_TYPE'])
				{
					case 'character varying':
						$DATA_TYPE = 'string';
						$ORM_DATA_TYPE = 'string';
						break;
					case 'character':
						$DATA_TYPE = 'string';
						if (
							$ar['CHARACTER_MAXIMUM_LENGTH'] == 1
							&& (
								substr($ar['COLUMN_DEFAULT'], 0, 3) === "'N'"
								|| substr($ar['COLUMN_DEFAULT'], 0, 3) === "'Y'"
							)
						)
						{
							$ar['COLUMN_DEFAULT'] = $ar['COLUMN_DEFAULT'][1];
							$ORM_DATA_TYPE = 'boolean';
						}
						else
						{
							$ORM_DATA_TYPE = 'string';
						}
						break;
					case 'text':
					case 'bytea':
						$canSort = false;
						$DATA_TYPE = 'string';
						$ORM_DATA_TYPE = 'string';
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
						$DATA_TYPE = 'int';
						$ORM_DATA_TYPE = 'integer';
						break;
					case 'double precision':
					case 'float4':
					case 'float8':
					case 'numeric':
					case 'real':
						$DATA_TYPE = 'double';
						$ORM_DATA_TYPE = 'float';
						break;
					case 'timestamp without time zone':
						$DATA_TYPE = 'datetime';
						$ORM_DATA_TYPE = 'datetime';
						break;
					case 'date':
						$DATA_TYPE = 'date';
						$ORM_DATA_TYPE = 'date';
						break;
					default:
						$canSort = false;
						$DATA_TYPE = 'unknown';
						$ORM_DATA_TYPE = 'UNKNOWN';
						break;
				}

				$arResult[mb_strtoupper($ar['COLUMN_NAME'])] = $DATA_TYPE;
				$arResultExt[mb_strtoupper($ar['COLUMN_NAME'])] = [
					'type' => $DATA_TYPE,
					'length' => $ar['CHARACTER_MAXIMUM_LENGTH'],
					'nullable' => $ar['IS_NULLABLE'] !== 'NO',
					'default' => preg_match('/^\'(.*)\'::/', $ar['COLUMN_DEFAULT'], $match) ? $match[1] : $ar['COLUMN_DEFAULT'],
					'sortable' => $canSort,
					'orm_type' => $ORM_DATA_TYPE,
					'increment' => strpos($ar['COLUMN_DEFAULT'], 'nextval(') !== false || $ar['IS_IDENTITY'] === 'YES',
					'info' => $ar,
				];
			}
			$cache[$TABLE_NAME] = [$arResult, $arResultExt];
		}

		if ($bExtended)
		{
			return $cache[$TABLE_NAME][1];
		}
		else
		{
			return $cache[$TABLE_NAME][0];
		}
	}

	public static function escapeColumn($column)
	{
		return '"' . str_replace('"', '""', mb_strtolower($column)) . '"';
	}

	public static function escapeTable($tableName)
	{
		return '"' . str_replace('"', '""', mb_strtolower($tableName)) . '"';
	}

	public function getCreateIndexDDL($TABLE_NAME, $INDEX_NAME, $INDEX_COLUMNS)
	{
		$tableFields = $this->GetTableFields($TABLE_NAME, true);
		foreach ($INDEX_COLUMNS as $i => $field)
		{
			if ($tableFields[trim($field, '`[]"')]['orm_type'] === 'text')
			{
				$INDEX_COLUMNS[$i] = $field . '(100)';
			}
		}
		return parent::getCreateIndexDDL($TABLE_NAME, $INDEX_NAME, $INDEX_COLUMNS);
	}
}
