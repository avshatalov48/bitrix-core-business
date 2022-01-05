<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main\DB\SqlExpression;

require_once __DIR__."/../general/database.php";

/********************************************************************
*	MySQL database classes
********************************************************************/
abstract class CDatabaseMysql extends CAllDatabase
{
	var $version;

	public $type = "MYSQL";

	public
		$escL = '`',
		$escR = '`';

	public
		$alias_length = 256;

	public function GetVersion()
	{
		if($this->version)
			return $this->version;

		$rs = $this->Query("SELECT VERSION() as R", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($ar = $rs->Fetch())
		{
			$version = trim($ar["R"]);
			preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $arr);
			$version = $arr[0];
			$this->version = $version;
			return $version;
		}
		else
		{
			return false;
		}
	}

	public function StartTransaction()
	{
		$this->Query("START TRANSACTION");
	}

	public function Commit()
	{
		$this->Query("COMMIT", true);
	}

	public function Rollback()
	{
		$this->Query("ROLLBACK", true);
	}

	public function Connect($DBHost, $DBName, $DBLogin, $DBPassword, $connectionName = "")
	{
		$this->DBHost = $DBHost;
		$this->DBName = $DBName;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;

		if (!defined("DBPersistent"))
		{
			define("DBPersistent", true);
		}

		if (defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT === true)
		{
			return true;
		}
		else
		{
			return $this->DoConnect($connectionName);
		}
	}

	abstract protected function QueryInternal($sql);

	abstract protected function GetError();

	public function Query($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		$this->DoConnect();
		$this->db_Error="";

		if($this->DebugToFile || $DB->ShowSqlStat)
			$start_time = microtime(true);

		//We track queries for DML statements
		//and when there is no one we can choose
		//to run query against master connection
		//or replicated one
		$connectionPool = \Bitrix\Main\Application::getInstance()->getConnectionPool();

		if($connectionPool->isMasterOnly())
		{
			//We requested to process all queries
			//by master connection
		}
		elseif($this->bModuleConnection )
		{
			//In case of dedicated module database
			//were is nothing to do
		}
		elseif(isset($arOptions["fixed_connection"]))
		{
			//We requested to process this query
			//by current connection
		}
		elseif($this->bNodeConnection)
		{
			//It is node so nothing to do
		}
		else
		{

			if(isset($arOptions["ignore_dml"]))
			{
				$connectionPool->ignoreDml(true);
			}

			$connection = $connectionPool->getSlaveConnection($strSql);

			if(isset($arOptions["ignore_dml"]))
			{
				$connectionPool->ignoreDml(false);
			}

			if($connection !== null)
			{
				if(!isset($this->obSlave))
				{
					$nodeId = $connection->getNodeId();

					ob_start();
					$conn = CDatabase::GetDBNodeConnection($nodeId, true);
					ob_end_clean();

					if(is_object($conn))
					{
						$this->obSlave = $conn;
					}
					else
					{
						self::$arNodes[$nodeId]["ONHIT_ERROR"] = true;
						CClusterDBNode::SetOffline($nodeId);
					}
				}

				if(is_object($this->obSlave))
				{
					return $this->obSlave->Query($strSql, $bIgnoreErrors, $error_position, $arOptions);
				}
			}
		}

		$result = $this->QueryInternal($strSql);

		if($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if($DB->ShowSqlStat)
				$DB->addDebugQuery($strSql, $exec_time, $connectionPool->isSlavePossible()? $this->node_id: -1);

			if($this->DebugToFile)
				$this->startSqlTracker()->writeFileLog($strSql, $exec_time, "CONN: ".$this->getThreadId());
		}

		if(!$result)
		{
			$this->db_Error = $this->GetError();
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				$application = \Bitrix\Main\Application::getInstance();

				$ex = new \Bitrix\Main\DB\SqlQueryException('Mysql query error', $this->db_Error, $strSql);
				$application->getExceptionHandler()->writeToLog($ex);

				$application->getContext()->getResponse()
					->setStatus('500 Internal Server Error')
					->writeHeaders();

        		if ($this->DebugToFile)
				{
					$this->startSqlTracker()->writeFileLog("ERROR: ".$this->db_Error, 0, "CONN: ".$this->getThreadId());
				}

				if($this->debug)
				{
					echo $error_position."<br><font color=#ff0000>MySQL Query Error: ".htmlspecialcharsbx($strSql)."</font>[".htmlspecialcharsbx($this->db_Error)."]<br>";
				}

				$error_position = preg_replace("#<br[^>]*>#i","\n", $error_position);
				SendError($error_position."\nMySQL Query Error:\n".$strSql." \n [".$this->db_Error."]\n---------------\n\n");

				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php"))
				{
					include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php");
					die();
				}
				else
				{
					die("MySQL Query Error!");
				}

			}
			return false;
		}


		$res = new CDBResult($result);
		$res->DB = $this;
		if($DB->ShowSqlStat)
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		return $res;
	}

	/**
	 * @deprecated Not used.
	 * @param $resource
	 * @return mixed
	 */
	abstract protected function DisconnectInternal($resource);

	/**
	 * Closes database connection.
	 * @deprecated Use D7 connections.
	 */
	public function Disconnect()
	{
		if ($this->connection)
		{
			$this->connection->disconnect();
		}
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
		static $search  = array(
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
		);
		static $replace = array(
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
		);

		$format = str_replace($search, $replace, $format);

		if (mb_strpos($format, '%H') === false)
		{
			$format = str_replace("H", "%h", $format);
		}

		if (mb_strpos($format, '%M') === false)
		{
			$format = str_replace("M", "%b", $format);
		}

		$lowerAmPm = false;
		if(mb_strpos($format, 'T') !== false)
		{
			//lowercase am/pm
			$lowerAmPm = true;
			$format = str_replace("T", "%p", $format);
		}

		if($field === false)
		{
			$field = "#FIELD#";
		}

		if($lowerAmPm)
		{
			return "REPLACE(REPLACE(DATE_FORMAT(".$field.", '".$format."'), 'PM', 'pm'), 'AM', 'am')";
		}

		return "DATE_FORMAT(".$field.", '".$format."')";
	}

	public function DateToCharFunction($strFieldName, $strType="FULL", $lang=false, $bSearchInSitesOnly=false)
	{
		static $CACHE = array();

		$id = $strType.",".$lang.",".$bSearchInSitesOnly;
		if(!isset($CACHE[$id]))
		{
			$CACHE[$id] = $this->DateFormatToDB(CLang::GetDateFormat($strType, $lang, $bSearchInSitesOnly), false);
		}

		$sFieldExpr = $strFieldName;

		//time zone
		if($strType == "FULL" && CTimeZone::Enabled())
		{
			$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr = "DATE_ADD(".$strFieldName.", INTERVAL ".$diff." SECOND)";
		}

		return str_replace("#FIELD#", $sFieldExpr, $CACHE[$id]);
	}

	public function CharToDateFunction($strValue, $strType="FULL", $lang=false)
	{
		// get user time
		if ($strValue instanceof \Bitrix\Main\Type\DateTime && !$strValue->isUserTimeEnabled())
		{
			$strValue = clone $strValue;
			$strValue->toUserTime();
		}

		// format
		$sFieldExpr = "'".CDatabase::FormatDate($strValue, CLang::GetDateFormat($strType, $lang), ($strType=="SHORT"? "YYYY-MM-DD":"YYYY-MM-DD HH:MI:SS"))."'";

		//time zone
		if($strType == "FULL" && CTimeZone::Enabled())
		{
			$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr = "DATE_ADD(".$sFieldExpr.", INTERVAL -(".$diff.") SECOND)";
		}

		return $sFieldExpr;
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
		return "UNIX_TIMESTAMP(".$fieldName.")".$timeZone;
	}

	public function DatetimeToDateFunction($strValue)
	{
		return 'DATE('.$strValue.')';
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
		$z = $this->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$zr = $z->Fetch();
		return $zr["RES"];
	}

	abstract public function LastID();

	public function PrepareFields($strTableName, $strPrefix = "str_", $strSuffix = "")
	{
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $arColumn)
		{
			$column = $arColumn["NAME"];
			$type = $arColumn["TYPE"];
			global $$column;
			$var = $strPrefix.$column.$strSuffix;
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

	public function PrepareInsert($strTableName, $arFields, $strFileDir="", $lang=false)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];

				if($value === false)
				{
					$strInsert1 .= ", `".$strColumnName."`";
					$strInsert2 .= ",  NULL ";
				}
				else
				{
					$strInsert1 .= ", `".$strColumnName."`";
					switch ($type)
					{
						case "datetime":
						case "timestamp":
							if($value == '')
								$strInsert2 .= ", NULL ";
							else
								$strInsert2 .= ", ".CDatabase::CharToDateFunction($value, "FULL", $lang);
							break;
						case "date":
							if($value == '')
								$strInsert2 .= ", NULL ";
							else
								$strInsert2 .= ", ".CDatabase::CharToDateFunction($value, "SHORT", $lang);
							break;
						case "int":
							$strInsert2 .= ", '".intval($value)."'";
							break;
						case "real":
							$value = doubleval($value);
							if(!is_finite($value))
							{
								$value = 0;
							}
							$strInsert2 .= ", '".$value."'";
							break;
						default:
							$strInsert2 .= ", '".$this->ForSql($value)."'";
					}
				}
			}
			elseif(array_key_exists("~".$strColumnName, $arFields))
			{
				$strInsert1 .= ", `".$strColumnName."`";
				$strInsert2 .= ", ".$arFields["~".$strColumnName];
			}
		}

		if($strInsert1!="")
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
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if($value === false)
				{
					$strUpdate .= ", $strTableAlias`".$strColumnName."` = NULL";
				}
				elseif ($value instanceof SqlExpression)
				{
					$strUpdate .= ", $strTableAlias`".$strColumnName."` = ".$value->compile();
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
						default:
							$value = "'".$this->ForSql($value)."'";
					}
					$strUpdate .= ", $strTableAlias`".$strColumnName."` = ".$value;
				}
			}
			elseif(is_set($arFields, "~".$strColumnName))
			{
				$strUpdate .= ", $strTableAlias`".$strColumnName."` = ".$arFields["~".$strColumnName];
			}
		}

		if($strUpdate!="")
			$strUpdate = mb_substr($strUpdate, 2);

		return $strUpdate;
	}

	public function Insert($table, $arFields, $error_position="", $DEBUG=false, $EXIST_ID="", $ignore_errors=false)
	{
		if (!is_array($arFields))
			return false;

		$str1 = "";
		$str2 = "";
		foreach ($arFields as $field => $value)
		{
			$str1 .= ($str1 <> ""? ", ":"")."`".$field."`";
			if ((string)$value == '')
				$str2 .= ($str2 <> ""? ", ":"")."''";
			else
				$str2 .= ($str2 <> ""? ", ":"").$value;
		}

		if ($EXIST_ID <> '')
		{
			$strSql = "INSERT INTO ".$table."(ID,".$str1.") VALUES ('".$this->ForSql($EXIST_ID)."',".$str2.")";
		}
		else
		{
			$strSql = "INSERT INTO ".$table."(".$str1.") VALUES (".$str2.")";
		}

		if ($DEBUG)
			echo "<br>".htmlspecialcharsEx($strSql)."<br>";

		$res = $this->Query($strSql, $ignore_errors, $error_position);

		if ($res === false)
			return false;

		if ($EXIST_ID <> '')
			return $EXIST_ID;
		else
			return $this->LastID();
	}

	public function Update($table, $arFields, $WHERE="", $error_position="", $DEBUG=false, $ignore_errors=false, $additional_check=true)
	{
		$rows = 0;
		if(is_array($arFields))
		{
			$ar = array();
			foreach($arFields as $field => $value)
			{
				if ((string)$value == '')
					$ar[] = "`".$field."` = ''";
				else
					$ar[] = "`".$field."` = ".$value."";
			}

			if (!empty($ar))
			{
				$strSql = "UPDATE ".$table." SET ".implode(", ", $ar)." ".$WHERE;
				if ($DEBUG)
					echo "<br>".htmlspecialcharsEx($strSql)."<br>";
				$w = $this->Query($strSql, $ignore_errors, $error_position);
				if (is_object($w))
				{
					$rows = $w->AffectedRowsCount();
					if ($DEBUG)
						echo "affected_rows = ".$rows."<br>";

					if ($rows <= 0 && $additional_check)
					{
						$w = $this->Query("SELECT 'x' FROM ".$table." ".$WHERE, $ignore_errors, $error_position);
						if (is_object($w))
						{
							if ($w->Fetch())
								$rows = $w->SelectedRowsCount();
							if ($DEBUG)
								echo "num_rows = ".$rows."<br>";
						}
					}
				}
			}
		}
		return $rows;
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
			$strSql =
				"INSERT INTO ".$tablename."(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$this->Query($strSql, $ignore_errors, $error_position, $arOptions);
			return $this->LastID();
		}
	}

	public function TopSql($strSql, $nTopCount)
	{
		$nTopCount = intval($nTopCount);
		if($nTopCount>0)
			return $strSql."\nLIMIT ".$nTopCount;
		else
			return $strSql;
	}

	abstract public function ForSqlLike($strValue, $iMaxLength = 0);

	public function InitTableVarsForEdit($tablename, $strIdentFrom="str_", $strIdentTo="str_", $strSuffixFrom="", $bAlways=false)
	{
		$fields = $this->GetTableFields($tablename);
		foreach($fields as $strColumnName => $field)
		{
			$varnameFrom = $strIdentFrom.$strColumnName.$strSuffixFrom;
			$varnameTo = $strIdentTo.$strColumnName;
			global ${$varnameFrom}, ${$varnameTo};
			if((isset(${$varnameFrom}) || $bAlways))
			{
				if(is_array(${$varnameFrom}))
				{
					${$varnameTo} = array();
					foreach(${$varnameFrom} as $k => $v)
						${$varnameTo}[$k] = htmlspecialcharsbx($v);
				}
				else
					${$varnameTo} = htmlspecialcharsbx(${$varnameFrom});
			}
		}
	}

	public function GetTableFieldsList($table)
	{
		return array_keys($this->GetTableFields($table));
	}

	abstract public function GetTableFields($table);

	public function LockTables($str)
	{
		register_shutdown_function(array(&$this, "UnLockTables"));
		$this->Query("LOCK TABLE ".$str, false, '', array("fixed_connection"=>true));
	}

	public function UnLockTables()
	{
		$this->Query("UNLOCK TABLES", true, '', array("fixed_connection"=>true));
	}

	public function Concat()
	{
		$str = "";
		$ar = func_get_args();
		if (is_array($ar)) $str .= implode(" , ", $ar);
		if ($str <> '') $str = "concat(".$str.")";
		return $str;
	}

	public function IsNull($expression, $result)
	{
		return "ifnull(".$expression.", ".$result.")";
	}

	public function Length($field)
	{
		return "length($field)";
	}

	public function ToChar($expr, $len=0)
	{
		return $expr;
	}

	public function TableExists($tableName)
	{
		$tableName = preg_replace("/[^A-Za-z0-9%_]+/i", "", $tableName);
		$tableName = Trim($tableName);

		if ($tableName == '')
			return False;

		$dbResult = $this->Query("SHOW TABLES LIKE '".$this->ForSql($tableName)."'", false, '', array("fixed_connection"=>true));
		if ($arResult = $dbResult->Fetch())
			return True;
		else
			return False;
	}

	public function GetIndexName($tableName, $arColumns, $bStrict = false)
	{
		if(!is_array($arColumns) || count($arColumns) <= 0)
			return "";

		$rs = $this->Query("SHOW INDEX FROM `".$this->ForSql($tableName)."`", true, '', array("fixed_connection"=>true));
		if(!$rs)
			return "";

		$arIndexes = array();
		while($ar = $rs->Fetch())
			$arIndexes[$ar["Key_name"]][$ar["Seq_in_index"]-1] = $ar["Column_name"];

		$strColumns = implode(",", $arColumns);
		foreach($arIndexes as $Key_name => $arKeyColumns)
		{
			ksort($arKeyColumns);
			$strKeyColumns = implode(",", $arKeyColumns);
			if($bStrict)
			{
				if($strKeyColumns === $strColumns)
					return $Key_name;
			}
			else
			{
				if(mb_substr($strKeyColumns, 0, mb_strlen($strColumns)) === $strColumns)
					return $Key_name;
			}
		}

		return "";
	}

	public function Instr($str, $toFind)
	{
		return "INSTR($str, $toFind)";
	}

	abstract protected function getThreadId();
}

abstract class CDBResultMysql extends CAllDBResult
{
	public function __construct($res = null)
	{
		parent::__construct($res);
	}

	/** @deprecated */
	public function CDBResultMysql($res = null)
	{
		self::__construct($res);
	}

	/**
	 * Returns next row of the select result in form of associated array
	 *
	 * @return array
	 */
	function Fetch()
	{
		global $DB;

		if($this->bNavStart || $this->bFromArray)
		{
			if(!is_array($this->arResult))
			{
				$res = false;
			}
			elseif($res = current($this->arResult))
			{
				next($this->arResult);
			}
		}
		else
		{
			if($this->SqlTraceIndex)
			{
				$start_time = microtime(true);
			}

			$res = $this->FetchInternal();

			if($this->SqlTraceIndex)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$exec_time = round(microtime(true) - $start_time, 10);
				$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
				$DB->timeQuery += $exec_time;
			}
		}

		return $res;
	}

	abstract protected function FetchRow();

	protected function FetchInternal()
	{
		if($this->resultObject !== null)
		{
			$res = $this->resultObject->fetch();
		}
		else
		{
			$res = $this->FetchRow();

			if(!$res)
			{
				return false;
			}

			$this->AfterFetch($res);
		}
		return $res;
	}

	function NavQuery($strSql, $cnt, $arNavStartParams, $bIgnoreErrors = false)
	{
		global $DB;

		if(isset($arNavStartParams["SubstitutionFunction"]))
		{
			$arNavStartParams["SubstitutionFunction"]($this, $strSql, $cnt, $arNavStartParams);
			return null;
		}

		if(isset($arNavStartParams["bDescPageNumbering"]))
			$bDescPageNumbering = $arNavStartParams["bDescPageNumbering"];
		else
			$bDescPageNumbering = false;

		$this->InitNavStartVars($arNavStartParams);
		$this->NavRecordCount = $cnt;

		if($this->NavShowAll)
			$this->NavPageSize = $this->NavRecordCount;

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = ($this->NavPageSize>0 ? floor($this->NavRecordCount/$this->NavPageSize) : 0);
		if($bDescPageNumbering)
		{
			$makeweight = 0;
			if($this->NavPageSize > 0)
				$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			if($this->NavPageCount == 0 && $makeweight > 0)
				$this->NavPageCount = 1;

			//page number to display
			$this->NavPageNomer =
			(
				$this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount
				?
					(\Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN] < 1 || \Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN] > $this->NavPageCount
					?
						$this->NavPageCount
					:
						\Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN]
					)
				:
					$this->PAGEN
			);

			//rows to skip
			$NavFirstRecordShow = 0;
			if($this->NavPageNomer != $this->NavPageCount)
				$NavFirstRecordShow += $makeweight;

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize;
			$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;
		}
		else
		{
			if($this->NavPageSize > 0 && ($this->NavRecordCount % $this->NavPageSize > 0))
				$this->NavPageCount++;

			//calculate total pages depend on rows count. start with 1
			if($this->PAGEN >= 1 && $this->PAGEN <= $this->NavPageCount)
				$this->NavPageNomer = $this->PAGEN;
			elseif(\Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN] >= 1 && \Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN] <= $this->NavPageCount)
				$this->NavPageNomer = \Bitrix\Main\Application::getInstance()->getSession()[$this->SESS_PAGEN];
			elseif($arNavStartParams["checkOutOfRange"] !== true)
				$this->NavPageNomer = 1;
			else
				return null;

			//rows to skip
			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;
		}

		$NavAdditionalRecords = 0;
		if(is_set($arNavStartParams, "iNavAddRecords"))
			$NavAdditionalRecords = $arNavStartParams["iNavAddRecords"];

		if(!$this->NavShowAll)
			$strSql .= " LIMIT ".$NavFirstRecordShow.", ".($NavLastRecordShow - $NavFirstRecordShow + $NavAdditionalRecords);

		if(is_object($this->DB))
			$res_tmp = $this->DB->Query($strSql, $bIgnoreErrors);
		else
			$res_tmp = $DB->Query($strSql, $bIgnoreErrors);

		// Return false on sql errors (if $bIgnoreErrors == true)
		if ($bIgnoreErrors && ($res_tmp === false))
			return false;

		$this->result = $res_tmp->result;
		$this->DB = $res_tmp->DB;

		if($this->SqlTraceIndex)
			$start_time = microtime(true);

		$temp_arrray = array();
		$temp_arrray_add = array();
		$tmp_cnt = 0;

		while($ar = $this->FetchInternal())
		{
			$tmp_cnt++;
			if (intval($NavLastRecordShow - $NavFirstRecordShow) > 0 && $tmp_cnt > ($NavLastRecordShow - $NavFirstRecordShow))
				$temp_arrray_add[] = $ar;
			else
				$temp_arrray[] = $ar;
		}

		if($this->SqlTraceIndex)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);
			$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
			$DB->timeQuery += $exec_time;
		}

		$this->arResult = (!empty($temp_arrray)? $temp_arrray : false);
		$this->arResultAdd = (!empty($temp_arrray_add)? $temp_arrray_add : false);
		$this->nSelectedCount = $cnt;
		$this->bDescPageNumbering = $bDescPageNumbering;
		$this->bFromLimited = true;

		return null;
	}
}

if(defined("BX_USE_MYSQLI") && BX_USE_MYSQLI === true)
{
	require_once __DIR__."/database_mysqli.php";
}
else
{
	require_once __DIR__."/database_mysql.php";
}
