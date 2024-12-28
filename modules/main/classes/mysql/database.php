<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\DB\SqlExpression;

class CDatabaseMysql extends CAllDatabase
{
	/** @var mysqli */
	var $db_Conn;
	public $type = "MYSQL";
	public
		$escL = '`',
		$escR = '`';

	/**
	 * Closes database connection.
	 * @deprecated Use D7 connections.
	 */
	public function Disconnect()
	{
		$this->connection?->disconnect();
	}

	public static function CurrentTimeFunction()
	{
		return "now()";
	}

	public static function CurrentDateFunction()
	{
		return "CURRENT_DATE";
	}

	public function DateFormatToDB($format, $field = false)
	{
		static $search = [
			"YYYY",
			"MMMM",
			"MM",
			"MI",
			"DD",
			"HH",
			"GG",
			"G",
			"SS",
			"TT",
		];
		static $replace = [
			"%Y",
			"%M",
			"%m",
			"%i",
			"%d",
			"%H",
			"%h",
			"%l",
			"%s",
			"%p",
		];

		$format = str_replace($search, $replace, $format);

		if (!str_contains($format, '%H'))
		{
			$format = str_replace("H", "%h", $format);
		}

		if (!str_contains($format, '%M'))
		{
			$format = str_replace("M", "%b", $format);
		}

		$lowerAmPm = false;
		if (str_contains($format, 'T'))
		{
			//lowercase am/pm
			$lowerAmPm = true;
			$format = str_replace("T", "%p", $format);
		}

		if ($field === false)
		{
			$field = "#FIELD#";
		}

		if ($lowerAmPm)
		{
			return "REPLACE(REPLACE(DATE_FORMAT(" . $field . ", '" . $format . "'), 'PM', 'pm'), 'AM', 'am')";
		}

		return "DATE_FORMAT(" . $field . ", '" . $format . "')";
	}

	public function DatetimeToTimestampFunction($fieldName)
	{
		$timeZone = "";
		if (CTimeZone::Enabled())
		{
			static $diff = false;
			if ($diff === false)
			{
				$diff = CTimeZone::GetOffset();
			}

			if ($diff <> 0)
			{
				$timeZone = $diff > 0 ? "+" . $diff : $diff;
			}
		}
		return "UNIX_TIMESTAMP(" . $fieldName . ")" . $timeZone;
	}

	public function DatetimeToDateFunction($strValue)
	{
		return 'DATE(' . $strValue . ')';
	}

	//  1 if date1 > date2
	//  0 if date1 = date2
	// -1 if date1 < date2
	public function CompareDates($date1, $date2)
	{
		$s_date1 = $this->CharToDateFunction($date1);
		$s_date2 = $this->CharToDateFunction($date2);
		$strSql = "
			SELECT
				if($s_date1 > $s_date2, 1,
					if ($s_date1 < $s_date2, -1,
						if ($s_date1 = $s_date2, 0, 'x')
				)) as RES
			";
		$z = $this->Query($strSql);
		$zr = $z->Fetch();
		return $zr["RES"];
	}

	public function PrepareFields($strTableName, $strPrefix = "str_", $strSuffix = "")
	{
		$arColumns = $this->GetTableFields($strTableName);
		foreach ($arColumns as $arColumn)
		{
			$column = $arColumn["NAME"];
			$type = $arColumn["TYPE"];
			global $$column;
			$var = $strPrefix . $column . $strSuffix;
			global $$var;
			switch ($type)
			{
				case "int":
					$$var = intval($$column);
					break;
				case "real":
					$$var = doubleval($$column);
					break;
				default:
					$$var = $this->ForSql($$column);
			}
		}
	}

	public function PrepareInsert($strTableName, $arFields)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$arColumns = $this->GetTableFields($strTableName);
		foreach ($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if (isset($arFields[$strColumnName]))
			{
				if ($strInsert1 != '')
				{
					$strInsert1 .= ', ';
					$strInsert2 .= ', ';
				}

				$value = $arFields[$strColumnName];

				$strInsert1 .= "`" . $strColumnName . "`";

				if ($value === false)
				{
					$strInsert2 .= "NULL";
				}
				else
				{
					switch ($type)
					{
						case "datetime":
						case "timestamp":
							if ($value == '')
							{
								$strInsert2 .= "NULL";
							}
							else
							{
								$strInsert2 .= CDatabase::CharToDateFunction($value);
							}
							break;
						case "date":
							if ($value == '')
							{
								$strInsert2 .= "NULL";
							}
							else
							{
								$strInsert2 .= CDatabase::CharToDateFunction($value, "SHORT");
							}
							break;
						case "int":
							$strInsert2 .= "'" . intval($value) . "'";
							break;
						case "real":
							$value = doubleval($value);
							if (!is_finite($value))
							{
								$value = 0;
							}
							$strInsert2 .= "'" . $value . "'";
							break;
						default:
							$strInsert2 .= "'" . $this->ForSql($value) . "'";
					}
				}
			}
			elseif (array_key_exists("~" . $strColumnName, $arFields))
			{
				if ($strInsert1 != '')
				{
					$strInsert1 .= ', ';
					$strInsert2 .= ', ';
				}
				$strInsert1 .= "`" . $strColumnName . "`";
				$strInsert2 .= $arFields["~" . $strColumnName];
			}
		}

		return [$strInsert1, $strInsert2];
	}

	public function PrepareUpdate($strTableName, $arFields, $strFileDir = "", $lang = false, $strTableAlias = "")
	{
		$arBinds = [];
		return $this->PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, $arBinds, $strTableAlias);
	}

	public function PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, &$arBinds, $strTableAlias = "")
	{
		$arBinds = [];
		if ($strTableAlias != "")
		{
			$strTableAlias .= ".";
		}
		$strUpdate = "";
		$arColumns = $this->GetTableFields($strTableName);
		foreach ($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if (isset($arFields[$strColumnName]))
			{
				if ($strUpdate != '')
				{
					$strUpdate .= ', ';
				}

				$value = $arFields[$strColumnName];

				if ($value === false)
				{
					$strUpdate .= $strTableAlias . "`" . $strColumnName . "` = NULL";
				}
				elseif ($value instanceof SqlExpression)
				{
					$strUpdate .= $strTableAlias . "`" . $strColumnName . "` = " . $value->compile();
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
							if (!is_finite($value))
							{
								$value = 0;
							}
							break;
						case "datetime":
						case "timestamp":
							if ($value == '')
							{
								$value = "NULL";
							}
							else
							{
								$value = CDatabase::CharToDateFunction($value, "FULL", $lang);
							}
							break;
						case "date":
							if ($value == '')
							{
								$value = "NULL";
							}
							else
							{
								$value = CDatabase::CharToDateFunction($value, "SHORT", $lang);
							}
							break;
						default:
							$value = "'" . $this->ForSql($value) . "'";
					}
					$strUpdate .= $strTableAlias . "`" . $strColumnName . "` = " . $value;
				}
			}
			elseif (is_set($arFields, "~" . $strColumnName))
			{
				if ($strUpdate != '')
				{
					$strUpdate .= ', ';
				}
				$strUpdate .= $strTableAlias . "`" . $strColumnName . "` = " . $arFields["~" . $strColumnName];
			}
		}

		return $strUpdate;
	}

	public function PrepareUpdateJoin($strTableName, $arFields, $from, $where)
	{
		$tables = $strTableName;
		foreach ($from as $join)
		{
			$tables .= "\n  INNER JOIN " . $join[0] . ' ON ' . $join[1];
		}
		$fields = '';
		foreach ($arFields as $fieldName => $fieldValue)
		{
			$fields .= ($fields ? ",\n  " : "") . $strTableName . '.' . $fieldName . '=' . $fieldValue;
		}
		$update = 'UPDATE ' . $tables . "\n"
			. "SET\n  " . $fields . "\n"
			. ($where ? "\nWHERE" . $where : "");
		return $update;
	}

	public function Insert($table, $arFields, $error_position = "", $DEBUG = false, $EXIST_ID = "", $ignore_errors = false)
	{
		if (!is_array($arFields))
		{
			return false;
		}

		$str1 = "";
		$str2 = "";
		foreach ($arFields as $field => $value)
		{
			$str1 .= ($str1 <> "" ? ", " : "") . $this->quote($field);
			if ((string)$value == '')
			{
				$str2 .= ($str2 <> "" ? ", " : "") . "''";
			}
			else
			{
				$str2 .= ($str2 <> "" ? ", " : "") . $value;
			}
		}

		if ($EXIST_ID <> '')
		{
			$strSql = "INSERT INTO " . $table . "(ID," . $str1 . ") VALUES ('" . $this->ForSql($EXIST_ID) . "'," . $str2 . ")";
		}
		else
		{
			$strSql = "INSERT INTO " . $table . "(" . $str1 . ") VALUES (" . $str2 . ")";
		}

		if ($DEBUG)
		{
			echo "<br>" . htmlspecialcharsEx($strSql) . "<br>";
		}

		$res = $this->Query($strSql, $ignore_errors, $error_position);

		if ($res === false)
		{
			return false;
		}

		if ($EXIST_ID <> '')
		{
			return $EXIST_ID;
		}
		else
		{
			return $this->LastID();
		}
	}

	public function Add($tablename, $arFields, $arCLOBFields = [], $strFileDir = "", $ignore_errors = false, $error_position = "", $arOptions = [])
	{
		global $DB;

		if (!isset($this) || !is_object($this) || !isset($this->type))
		{
			return $DB->Add($tablename, $arFields, $arCLOBFields, $strFileDir, $ignore_errors, $error_position, $arOptions);
		}
		else
		{
			$arInsert = $this->PrepareInsert($tablename, $arFields);
			$strSql =
				"INSERT INTO " . $tablename . "(" . $arInsert[0] . ") " .
				"VALUES(" . $arInsert[1] . ")";
			$this->Query($strSql, $ignore_errors, $error_position, $arOptions);
			return $this->LastID();
		}
	}

	public function LockTables($str)
	{
		register_shutdown_function([&$this, "UnLockTables"]);
		$this->Query("LOCK TABLE " . $str, false, '', ["fixed_connection" => true]);
	}

	public function UnLockTables()
	{
		$this->Query("UNLOCK TABLES", true, '', ["fixed_connection" => true]);
	}

	public function ToChar($expr, $len = 0)
	{
		return $expr;
	}

	public function Instr($str, $toFind)
	{
		return "INSTR($str, $toFind)";
	}

	public function CreateIndex($indexName, $tableName, $columns, $unique = false, $fulltext = false)
	{
		foreach ($columns as $i => $columnName)
		{
			$columns[$i] = $this->quote($columnName);
		}

		if ($unique)
		{
			$indexType = 'UNIQUE';
		}
		elseif ($fulltext)
		{
			$indexType = 'FULLTEXT';
		}
		else
		{
			$indexType = '';
		}

		return $this->Query('CREATE ' . $indexType . ' INDEX ' . $this->quote($indexName) . ' ON ' . $this->quote($tableName) . '(' . implode(',', $columns) . ')', true);
	}

	protected function QueryInternal($strSql)
	{
		return mysqli_query($this->db_Conn, $strSql);
	}

	protected function GetError()
	{
		return "(" . $this->GetErrorCode() . ") " . mysqli_error($this->db_Conn);
	}

	protected function GetErrorCode()
	{
		return mysqli_errno($this->db_Conn);
	}

	public function ForSqlLike($strValue, $iMaxLength = 0)
	{
		if ($iMaxLength > 0)
		{
			$strValue = mb_substr($strValue ?? '', 0, $iMaxLength);
		}

		$this->DoConnect();
		return mysqli_real_escape_string($this->db_Conn, str_replace("\\", "\\\\", $strValue ?? ''));
	}

	public function GetTableFields($table)
	{
		if (!isset($this->column_cache[$table]))
		{
			$this->column_cache[$table] = [];
			$this->DoConnect();

			$dbResult = $this->query("SELECT * FROM " . $this->quote($table) . " LIMIT 0");

			$resultFields = mysqli_fetch_fields($dbResult->result);
			foreach ($resultFields as $field)
			{
				switch ($field->type)
				{
					case MYSQLI_TYPE_TINY:
					case MYSQLI_TYPE_SHORT:
					case MYSQLI_TYPE_LONG:
					case MYSQLI_TYPE_INT24:
					case MYSQLI_TYPE_CHAR:
						$type = "int";
						break;

					case MYSQLI_TYPE_DECIMAL:
					case MYSQLI_TYPE_NEWDECIMAL:
					case MYSQLI_TYPE_FLOAT:
					case MYSQLI_TYPE_DOUBLE:
						$type = "real";
						break;

					case MYSQLI_TYPE_DATETIME:
					case MYSQLI_TYPE_TIMESTAMP:
						$type = "datetime";
						break;

					case MYSQLI_TYPE_DATE:
					case MYSQLI_TYPE_NEWDATE:
						$type = "date";
						break;

					default:
						$type = "string";
						break;
				}

				$this->column_cache[$table][$field->name] = [
					"NAME" => $field->name,
					"TYPE" => $type,
				];
			}
		}
		return $this->column_cache[$table];
	}

	protected function getThreadId()
	{
		return mysqli_thread_id($this->db_Conn);
	}
}

class CDatabase extends CDatabaseMysql
{
}
