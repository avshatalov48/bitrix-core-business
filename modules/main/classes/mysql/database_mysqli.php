<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/********************************************************************
*	MySQLi database classes
********************************************************************/
class CDatabase extends CDatabaseMysql
{
	/** @var mysqli */
	var $db_Conn;

	function ConnectInternal()
	{
		$dbHost = $this->DBHost;
		$dbPort = null;
		if (($pos = strpos($dbHost, ":")) !== false)
		{
			$dbPort = intval(substr($dbHost, $pos + 1));
			$dbHost = substr($dbHost, 0, $pos);
		}

		$persistentPrefix = (DBPersistent && !$this->bNodeConnection? "p:" : "");

		$this->db_Conn = mysqli_connect($persistentPrefix.$dbHost, $this->DBLogin, $this->DBPassword, $this->DBName, $dbPort);

		if(!$this->db_Conn)
		{
			$error = "[".mysqli_connect_errno()."] ".mysqli_connect_error();
			if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
				echo "<br><font color=#ff0000>Error! mysqli_connect()</font><br>".$error."<br>";

			SendError("Error! mysqli_connect()\n".$error."\n");

			return false;
		}

		return true;
	}

	protected function QueryInternal($strSql)
	{
		return mysqli_query($this->db_Conn, $strSql, MYSQLI_STORE_RESULT);
	}

	protected function GetError()
	{
		return "[".mysqli_errno($this->db_Conn)."] ".mysqli_error($this->db_Conn);
	}

	protected function DisconnectInternal($resource)
	{
		mysqli_close($resource);
	}

	function LastID()
	{
		$this->DoConnect();
		return mysqli_insert_id($this->db_Conn);
	}

	function ForSql($strValue, $iMaxLength = 0)
	{
		if ($iMaxLength > 0)
			$strValue = substr($strValue, 0, $iMaxLength);

		if (!isset($this) || !is_object($this) || !$this->db_Conn)
		{
			global $DB;
			$DB->DoConnect();
			return mysqli_real_escape_string($DB->db_Conn, $strValue);
		}
		else
		{
			$this->DoConnect();
			return mysqli_real_escape_string($this->db_Conn, $strValue);
		}
	}

	function ForSqlLike($strValue, $iMaxLength = 0)
	{
		if ($iMaxLength > 0)
			$strValue = substr($strValue, 0, $iMaxLength);

		if(!isset($this) || !is_object($this) || !$this->db_Conn)
		{
			global $DB;
			$DB->DoConnect();
			return mysqli_real_escape_string($DB->db_Conn, str_replace("\\", "\\\\", $strValue));
		}
		else
		{
			$this->DoConnect();
			return mysqli_real_escape_string($this->db_Conn, str_replace("\\", "\\\\", $strValue));
		}
	}

	function GetTableFields($table)
	{
		if(!isset($this->column_cache[$table]))
		{
			$this->column_cache[$table] = array();
			$this->DoConnect();

			$dbResult = $this->query("SELECT * FROM `".$this->ForSql($table)."` LIMIT 0");

			$resultFields = mysqli_fetch_fields($dbResult->result);
			foreach ($resultFields as $field)
			{
				switch($field->type)
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

				$this->column_cache[$table][$field->name] = array(
					"NAME" => $field->name,
					"TYPE" => $type,
				);
			}
		}
		return $this->column_cache[$table];
	}

	protected function getThreadId()
	{
		return mysqli_thread_id($this->db_Conn);
	}
}

class CDBResult extends CDBResultMysql
{
	public function __construct($res = null)
	{
		parent::__construct($res);
	}

	/** @deprecated */
	public function CDBResult($res = null)
	{
		self::__construct($res);
	}

	protected function FetchRow()
	{
		return mysqli_fetch_assoc($this->result);
	}

	function SelectedRowsCount()
	{
		if($this->nSelectedCount !== false)
			return $this->nSelectedCount;

		if(is_object($this->result))
			return mysqli_num_rows($this->result);
		else
			return 0;
	}

	function AffectedRowsCount()
	{
		if(isset($this) && is_object($this) && is_object($this->DB))
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$this->DB->DoConnect();
			return mysqli_affected_rows($this->DB->db_Conn);
		}
		else
		{
			global $DB;
			$DB->DoConnect();
			return mysqli_affected_rows($DB->db_Conn);
		}
	}

	function FieldsCount()
	{
		if(is_object($this->result))
			return mysqli_num_fields($this->result);
		else
			return 0;
	}

	function FieldName($iCol)
	{
		$fieldInfo = mysqli_fetch_field_direct($this->result, $iCol);
		return $fieldInfo->name;
	}

	function DBNavStart()
	{
		global $DB;

		//total rows count
		if(is_object($this->result))
			$this->NavRecordCount = mysqli_num_rows($this->result);
		else
			return;

		if($this->NavRecordCount < 1)
			return;

		if($this->NavShowAll)
			$this->NavPageSize = $this->NavRecordCount;

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = floor($this->NavRecordCount/$this->NavPageSize);
		if($this->NavRecordCount % $this->NavPageSize > 0)
			$this->NavPageCount++;

		//page number to display. start with 1
		$this->NavPageNomer = ($this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount? ($_SESSION[$this->SESS_PAGEN] < 1 || $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount? 1:$_SESSION[$this->SESS_PAGEN]):$this->PAGEN);

		//rows to skip
		$NavFirstRecordShow = $this->NavPageSize * ($this->NavPageNomer-1);
		$NavLastRecordShow = $this->NavPageSize * $this->NavPageNomer;

		if($this->SqlTraceIndex)
			$start_time = microtime(true);

		mysqli_data_seek($this->result, $NavFirstRecordShow);

		$temp_arrray = array();
		for($i=$NavFirstRecordShow; $i<$NavLastRecordShow; $i++)
		{
			if(($res = $this->FetchInternal()))
			{
				$temp_arrray[] = $res;
			}
			else
			{
				break;
			}
		}

		if($this->SqlTraceIndex)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);
			$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
			$DB->timeQuery += $exec_time;
		}

		$this->arResult = $temp_arrray;
	}
}
