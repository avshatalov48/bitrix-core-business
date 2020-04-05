<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/********************************************************************
*	MySQL database classes
********************************************************************/
class CDatabase extends CDatabaseMysql
{
	function ConnectInternal()
	{
		if (DBPersistent && !$this->bNodeConnection)
			$this->db_Conn = @mysql_pconnect($this->DBHost, $this->DBLogin, $this->DBPassword);
		else
			$this->db_Conn = @mysql_connect($this->DBHost, $this->DBLogin, $this->DBPassword, true);

		if(!$this->db_Conn)
		{
			$s = (DBPersistent && !$this->bNodeConnection? "mysql_pconnect" : "mysql_connect");
			if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
				echo "<br><font color=#ff0000>Error! ".$s."()</font><br>".mysql_error()."<br>";

			SendError("Error! ".$s."()\n".mysql_error()."\n");

			return false;
		}

		if(!mysql_select_db($this->DBName, $this->db_Conn))
		{
			if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
				echo "<br><font color=#ff0000>Error! mysql_select_db(".$this->DBName.")</font><br>".mysql_error($this->db_Conn)."<br>";

			SendError("Error! mysql_select_db(".$this->DBName.")\n".mysql_error($this->db_Conn)."\n");

			return false;
		}

		return true;
	}

	protected function QueryInternal($strSql)
	{
		return mysql_query($strSql, $this->db_Conn);
	}

	protected function GetError()
	{
		return mysql_error($this->db_Conn);
	}

	protected function DisconnectInternal($resource)
	{
		mysql_close($resource);
	}

	function LastID()
	{
		$this->DoConnect();
		return mysql_insert_id($this->db_Conn);
	}

	function ForSql($strValue, $iMaxLength = 0)
	{
		if ($iMaxLength > 0)
			$strValue = substr($strValue, 0, $iMaxLength);

		if (!isset($this) || !is_object($this) || !$this->db_Conn)
		{
			global $DB;
			$DB->DoConnect();
			return mysql_real_escape_string($strValue, $DB->db_Conn);
		}
		else
		{
			$this->DoConnect();
			return mysql_real_escape_string($strValue, $this->db_Conn);
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
			return mysql_real_escape_string(str_replace("\\", "\\\\", $strValue), $DB->db_Conn);
		}
		else
		{
			$this->DoConnect();
			return mysql_real_escape_string(str_replace("\\", "\\\\", $strValue), $this->db_Conn);
		}
	}

	function GetTableFields($table)
	{
		if(!array_key_exists($table, $this->column_cache))
		{
			$this->column_cache[$table] = array();
			$this->DoConnect();
			$rs = @mysql_list_fields($this->DBName, $table, $this->db_Conn);
			if($rs > 0)
			{
				$intNumFields = mysql_num_fields($rs);
				while(--$intNumFields >= 0)
				{
					$ar = array(
						"NAME" => mysql_field_name($rs, $intNumFields),
						"TYPE" => mysql_field_type($rs, $intNumFields),
					);
					$this->column_cache[$table][$ar["NAME"]] = $ar;
				}
			}
		}
		return $this->column_cache[$table];
	}

	protected function getThreadId()
	{
		return mysql_thread_id($this->db_Conn);
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
		return mysql_fetch_array($this->result, MYSQL_ASSOC);
	}

	function SelectedRowsCount()
	{
		if($this->nSelectedCount !== false)
			return $this->nSelectedCount;

		if(is_resource($this->result))
			return mysql_num_rows($this->result);
		else
			return 0;
	}

	function AffectedRowsCount()
	{
		if(isset($this) && is_object($this) && is_object($this->DB))
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$this->DB->DoConnect();
			return mysql_affected_rows($this->DB->db_Conn);
		}
		else
		{
			global $DB;
			$DB->DoConnect();
			return mysql_affected_rows($DB->db_Conn);
		}
	}

	function FieldsCount()
	{
		if(is_resource($this->result))
			return mysql_num_fields($this->result);
		else
			return 0;
	}

	function FieldName($iCol)
	{
		return mysql_field_name($this->result, $iCol);
	}

	function DBNavStart()
	{
		global $DB;

		//total rows count
		if(is_resource($this->result))
			$this->NavRecordCount = mysql_num_rows($this->result);
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

		mysql_data_seek($this->result, $NavFirstRecordShow);

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
