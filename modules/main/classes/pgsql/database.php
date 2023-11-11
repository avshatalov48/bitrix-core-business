<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main\DB\SqlExpression;

class CDatabase extends CAllDatabase
{
	/** @var resource */
	var $db_Conn;

	public
		$escL = '"',
		$escR = '"';

	public $type = "PGSQL";

	protected function ConnectInternal()
	{
		throw new \Bitrix\Main\NotImplementedException("Use d7 connection.");
	}

	public function ToNumber($expr)
	{
		return "CASE WHEN " . $expr . "~E'^\\d+$' THEN " . $expr . "::integer ELSE 0 END";
	}

	public function DateFormatToDB($format, $field = false)
	{
		$this->DoConnect();
		if ($field === false)
		{
			$field = "#FIELD#";
		}
		return "to_char(".$field.", '".$this->connection->getSqlHelper()->formatDate($format)."')";
	}

	public static function CurrentTimeFunction()
	{
		return "CURRENT_TIMESTAMP";
	}

	public static function CurrentDateFunction()
	{
		return "CURRENT_DATE";
	}

	public function DatetimeToTimestampFunction($fieldName)
	{
		$timeZone = "";
		if (CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$timeZone = $diff > 0? "+".$diff: $diff;
		}
		return "extract(epoch FROM $fieldName)".$timeZone;
	}

	public function DatetimeToDateFunction($strValue)
	{
		return 'cast(' . $strValue . ' as date)';
	}

	public function ForSqlLike($strValue, $iMaxLength = 0)
	{
		if ($iMaxLength > 0)
			$strValue = mb_substr($strValue ?? '', 0, $iMaxLength);

		$this->DoConnect();
		return pg_escape_string($this->db_Conn, str_replace("\\", "\\\\", $strValue ?? ''));
	}

	protected function QueryInternal($strSql)
	{
		return pg_query($this->db_Conn, $strSql);
	}

	protected function GetError()
	{
		return pg_last_error($this->db_Conn);
	}

	public function GetTableFields($table)
	{
		if (!isset($this->column_cache[$table]))
		{
			$this->column_cache[$table] = array();
			$this->DoConnect();

			$dbResult = $this->query("SELECT * FROM ".$this->connection->getSqlHelper()->quote($table)." limit 0");

			$fields = pg_num_fields($dbResult->result);
			for ($i = 0; $i < $fields; $i++)
			{
				$fieldName = mb_strtoupper(pg_field_name($dbResult->result, $i));
				$fieldType = pg_field_type($dbResult->result, $i);
				switch ($fieldType)
				{
					case 'bigint':
					case 'int8':
					case 'bigserial':
					case 'serial8':
					case 'integer':
					case 'int':
					case 'int4':
					case 'smallint':
					case 'int2':
					case 'smallserial':
					case 'serial2':
					case 'serial':
					case 'serial4':
						$type = "int";
						break;
					case 'double precision':
					case 'float8':
					case 'numeric':
					case 'real':
					case 'float4':
						$type = "real";
						break;
					case 'time':
					case 'timestamp':
					case 'timestamptz':
						$type = "datetime";
						break;
					case 'date':
						$type = "date";
						break;
					case 'bytea':
						$type = "bytes";
						break;
					default:
						$type = "string";
						break;
				}

				$this->column_cache[$table][$fieldName] = array(
					"NAME" => $fieldName,
					"TYPE" => $type,
				);
			}
		}
		return $this->column_cache[$table];
	}

	public function PrepareInsert($strTableName, $arFields)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$this->DoConnect();
		$sqlHelper = $this->connection->getSqlHelper();
		$arColumns = $this->GetTableFields($strTableName);
		foreach ($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if (isset($arFields[$strColumnName]))
			{
				$strInsert1 .= ", ".$sqlHelper->quote($strColumnName);
				$value = $arFields[$strColumnName];

				if ($value === false)
				{
					$strInsert2 .= ",  NULL ";
				}
				else
				{
					switch ($type)
					{
						case "datetime":
						case "timestamp":
							if ($value == '')
								$strInsert2 .= ", NULL ";
							else
								$strInsert2 .= ", ".CDatabase::CharToDateFunction($value);
							break;
						case "date":
							if ($value == '')
								$strInsert2 .= ", NULL ";
							else
								$strInsert2 .= ", ".CDatabase::CharToDateFunction($value, "SHORT");
							break;
						case "int":
							$strInsert2 .= ", '".intval($value)."'";
							break;
						case "real":
							$value = doubleval($value);
							if (!is_finite($value))
							{
								$value = 0;
							}
							$strInsert2 .= ", '".$value."'";
							break;
						case "bytes":
							$strInsert2 .= ", decode('".bin2hex($value)."', 'hex')";
							break;
						default:
							$strInsert2 .= ", '".$sqlHelper->forSql($value)."'";
					}
				}
			}
			elseif (array_key_exists("~".$strColumnName, $arFields))
			{
				$strInsert1 .= ", ".$sqlHelper->quote($strColumnName);
				$strInsert2 .= ", ".$arFields["~".$strColumnName];
			}
		}

		if ($strInsert1 != "")
		{
			$strInsert1 = mb_substr($strInsert1, 2);
			$strInsert2 = mb_substr($strInsert2, 2);
		}

		return array($strInsert1, $strInsert2);
	}

	public function PrepareUpdate($strTableName, $arFields, $strFileDir="", $lang = false, $strTableAlias = "")
	{
		$arBinds = array();
		return $this->PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, $arBinds, $strTableAlias);
	}

	public function PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, &$arBinds, $strTableAlias = "")
	{
		$arBinds = array();
		if ($strTableAlias != "")
			$strTableAlias .= ".";
		$strUpdate = "";

		$this->DoConnect();
		$sqlHelper = $this->connection->getSqlHelper();
		$arColumns = $this->GetTableFields($strTableName);
		foreach ($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if (isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if ($value === false)
				{
					$strUpdate .= ", $strTableAlias".$sqlHelper->quote($strColumnName)." = NULL";
				}
				elseif ($value instanceof SqlExpression)
				{
					$strUpdate .= ", $strTableAlias".$sqlHelper->quote($strColumnName)." = ".$value->compile();
				}
				else
				{
					switch ($type)
					{
						case "int":
							$value = intval($value);
							break;
						case "real":
							$value = doubleval($value);
							if(!is_finite($value))
							{
								$value = 0;
							}
							break;
						case "datetime":
						case "timestamp":
							if($value == '')
								$value = "NULL";
							else
								$value = CDatabase::CharToDateFunction($value, "FULL", $lang);
							break;
						case "date":
							if($value == '')
								$value = "NULL";
							else
								$value = CDatabase::CharToDateFunction($value, "SHORT", $lang);
							break;
						case "bytes":
							$value = "decode('".bin2hex($value)."', 'hex')";
							break;
						default:
							$value = "'".$sqlHelper->ForSql($value)."'";
					}
					$strUpdate .= ", $strTableAlias".$sqlHelper->quote($strColumnName)." = ".$value;
				}
			}
			elseif (is_set($arFields, "~".$strColumnName))
			{
				$strUpdate .= ", $strTableAlias".$sqlHelper->quote($strColumnName)." = ".$arFields["~".$strColumnName];
			}
		}

		if ($strUpdate != "")
		{
			$strUpdate = mb_substr($strUpdate, 2);
		}

		return $strUpdate;
	}

	public function PrepareUpdateJoin($strTableName, $arFields, $from, $where)
	{
		$tables = '';
		foreach ($from as $join)
		{
			$tables .= ($tables ? ",\n  " : "FROM\n  ") . $join[0];
			$where .= ($where ? "\nAND " : "\n") . $join[1];
		}
		$fields = '';
		foreach ($arFields as $fieldName => $fieldValue)
		{
			$fields .= ($fields ? ",\n  " : "") . $fieldName . '=' . $fieldValue;
		}
		$update = 'UPDATE ' . $strTableName . "\n"
			. "SET\n  " . $fields . "\n"
			. $tables
			. ($where ? "\nWHERE" . $where : "")
		;
		return $update;
	}

	public function Insert($table, $arFields, $error_position="", $DEBUG=false, $EXIST_ID="", $ignore_errors=false)
	{
		if (!is_array($arFields))
			return false;

		$str1 = "";
		$str2 = "";
		foreach ($arFields as $field => $value)
		{
			$str1 .= ($str1 <> ""? ", ":"") . $this->quote($field);
			if ((string)$value == '')
				$str2 .= ($str2 <> ""? ", ":"")."''";
			else
				$str2 .= ($str2 <> ""? ", ":"").$value;
		}

		if ($EXIST_ID <> '')
		{
			$strSql = "INSERT INTO ".$table."(ID,".$str1.") VALUES ('".$this->ForSql($EXIST_ID)."',".$str2.") RETURNING ID";
		}
		else
		{
			$strSql = "INSERT INTO ".$table."(".$str1.") VALUES (".$str2.") RETURNING ID";
		}

		if ($DEBUG)
			echo "<br>".htmlspecialcharsEx($strSql)."<br>";

		$res = $this->Query($strSql, $ignore_errors, $error_position);

		if ($res === false)
			return false;

		$row = $res->Fetch();

		return array_shift($row);
	}

	public function Add($tablename, $arFields, $arCLOBFields = Array(), $strFileDir="", $ignore_errors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		if(!isset($this) || !is_object($this) || !isset($this->type))
		{
			return $DB->Add($tablename, $arFields, $arCLOBFields, $strFileDir, $ignore_errors, $error_position, $arOptions);
		}
		else
		{
			$arInsert = $this->PrepareInsert($tablename, $arFields, $strFileDir);
			if (intval($arFields["ID"]) <= 0)
			{
				$strSql = "INSERT INTO ".$tablename."(".$arInsert[0].") VALUES (".$arInsert[1].") RETURNING ID";
				$row = $this->Query($strSql, $ignore_errors, $error_position, $arOptions)->Fetch();
				return array_shift($row);
			}
			else
			{
				$strSql ="INSERT INTO ".$tablename."(".$arInsert[0].") VALUES(".$arInsert[1].")";
				$this->Query($strSql, $ignore_errors, $error_position, $arOptions);
				return intval($arFields["ID"]);
			}
		}
	}

	public function CreateIndex($indexName, $tableName, $columns, $unique = false, $fulltext = false)
	{
		foreach ($columns as $i => $columnName)
		{
			$columns[$i] = $this->quote($columnName);
		}

		if ($unique)
		{
			return $this->Query('CREATE UNIQUE INDEX ' . $this->quote($indexName) . ' ON ' . $this->quote($tableName) . '(' . implode(',', $columns) . ')', true);
		}
		elseif ($fulltext)
		{
			return $this->Query('CREATE INDEX ' . $this->quote($indexName) . ' ON ' . $this->quote($tableName) . '(' . implode(',', $columns) . ')', true);
		}
		else
		{
			return $this->Query('CREATE INDEX ' . $this->quote($indexName) . ' ON ' . $this->quote($tableName) . ' USING GIN (to_tsvector(\'english\', ' . implode(',', $columns) . '))', true);
		}
	}

	protected function getThreadId()
	{
		return pg_get_pid($this->db_Conn);
	}
}
