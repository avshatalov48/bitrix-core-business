<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Data\ConnectionPool;
use Bitrix\Main\Context;

abstract class CAllDatabase
{
	var $DBName;
	var $DBHost;
	var $DBLogin;
	var $DBPassword;

	var $db_Conn;
	var $debug;
	var $DebugToFile;
	var $ShowSqlStat;
	var $db_Error;
	var $db_ErrorSQL;
	var $result;
	var $type;

	static $arNodes = array();
	var $column_cache = array();
	var $bModuleConnection;
	var $bNodeConnection;
	var $node_id;
	/** @var CDatabase */
	var $obSlave = null;

	/**
	 * @var Main\DB\Connection
	 */
	protected $connection; // d7 connection
	protected $connectionName = null;

	/**
	 * @var integer
	 * @deprecated Use \Bitrix\Main\Application::getConnection()->getTracker()->getCounter();
	 **/
	var $cntQuery = 0;
	/**
	 * @var float
	 * @deprecated Use \Bitrix\Main\Application::getConnection()->getTracker()->getTime();
	 **/
	var $timeQuery = 0.0;
	/**
	 * @var \Bitrix\Main\Diag\SqlTrackerQuery[]
	 * @deprecated Use \Bitrix\Main\Application::getConnection()->getTracker()->getQueries();
	 **/
	var $arQueryDebug = array();
	/**
	 * @var \Bitrix\Main\Diag\SqlTracker
	 */
	public $sqlTracker = null;

	public function StartUsingMasterOnly()
	{
		Main\Application::getInstance()->getConnectionPool()->useMasterOnly(true);
	}

	public function StopUsingMasterOnly()
	{
		Main\Application::getInstance()->getConnectionPool()->useMasterOnly(false);
	}

	/**
	 * @param string $node_id
	 * @param boolean $bIgnoreErrors
	 * @param boolean $bCheckStatus
	 *
	 * @return boolean|CDatabase
	 */
	public static function GetDBNodeConnection($node_id, $bIgnoreErrors = false, $bCheckStatus = true)
	{
		global $DB;

		if(!array_key_exists($node_id, self::$arNodes))
		{
			if(CModule::IncludeModule('cluster'))
				self::$arNodes[$node_id] = CClusterDBNode::GetByID($node_id);
			else
				self::$arNodes[$node_id] = false;
		}
		$node = &self::$arNodes[$node_id];

		if(
			is_array($node)
			&& (
				!$bCheckStatus
				|| (
					$node["ACTIVE"] == "Y"
					&& ($node["STATUS"] == "ONLINE" || $node["STATUS"] == "READY")
				)
			)
			&& !isset($node["ONHIT_ERROR"])
		)
		{
			if(!array_key_exists("DB", $node))
			{
				$node_DB = new CDatabase;
				$node_DB->type = $DB->type;
				$node_DB->debug = $DB->debug;
				$node_DB->DebugToFile = $DB->DebugToFile;
				$node_DB->bNodeConnection = true;
				$node_DB->node_id = $node_id;

				if($node_DB->Connect($node["DB_HOST"], $node["DB_NAME"], $node["DB_LOGIN"], $node["DB_PASSWORD"], "node".$node_id))
				{
					if(defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT===true)
					{
						if($node_DB->DoConnect("node".$node_id))
							$node["DB"] = $node_DB;
					}
					else
					{
						$node["DB"] = $node_DB;
					}
				}
			}

			if(array_key_exists("DB", $node))
				return $node["DB"];
		}

		if($bIgnoreErrors)
		{
			return false;
		}
		else
		{
			static::showConnectionError();
			die();
		}
	}

	public static function showConnectionError()
	{
		$response = new Main\HttpResponse();
		$response->setStatus('500 Internal Server Error');
		$response->writeHeaders();

		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php"))
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php");
		}
		else
		{
			echo "Error connecting to database. Please try again later.";
		}
	}

	/**
	 * Returns module database connection.
	 * Can be used only if module supports sharding.
	 *
	 * @param string $module_id
	 * @param bool $bModuleInclude
	 * @return bool|CDatabase
	 */
	public static function GetModuleConnection($module_id, $bModuleInclude = false)
	{
		$node_id = COption::GetOptionString($module_id, "dbnode_id", "N");
		if(is_numeric($node_id))
		{
			if($bModuleInclude)
			{
				$status = COption::GetOptionString($module_id, "dbnode_status", "ok");
				if($status === "move")
					return false;
			}

			$moduleDB = CDatabase::GetDBNodeConnection($node_id, $bModuleInclude);

			if(is_object($moduleDB))
			{
				$moduleDB->bModuleConnection = true;
				return $moduleDB;
			}

			//There was an connection error
			if($bModuleInclude && CModule::IncludeModule('cluster'))
				CClusterDBNode::SetOffline($node_id);

			//TODO: unclear what to return when node went offline
			//in the middle of the hit.
			return false;
		}
		else
		{
			return $GLOBALS["DB"];
		}
	}

	/**
	 * @deprecated Use D7 connections.
	 */
	public function Connect($DBHost, $DBName, $DBLogin, $DBPassword, $connectionName = "")
	{
		$this->DBHost = $DBHost;
		$this->DBName = $DBName;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;
		$this->connectionName = $connectionName;

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

	/**
	 * @deprecated Not used.
	 */
	abstract protected function ConnectInternal();

	public function DoConnect($connectionName = '')
	{
		if ($this->connection && $this->connection->isConnected())
		{
			// the connection can reconnect outside
			$this->db_Conn = $this->connection->getResource();
			return true;
		}

		$application = Main\Application::getInstance();

		$found = false;

		// try to get a connection by its name
		$connection = $application->getConnection($connectionName ?: (string)$this->connectionName);

		if ($connection instanceof Main\DB\Connection)
		{
			// empty connection data, using the default connection
			if ((string)$this->DBHost === '')
			{
				$found = true;

				$this->DBHost = $connection->getHost();
				$this->DBName = $connection->getDatabase();
				$this->DBLogin = $connection->getLogin();
				$this->DBPassword = $connection->getPassword();
			}

			// or specific connection data
			if (!$found)
			{
				$found = (
					$this->DBHost == $connection->getHost()
					&& $this->DBName == $connection->getDatabase()
					&& $this->DBLogin == $connection->getLogin()
				);
			}
		}

		// connection not found, adding the new connection to the pool
		if (!$found)
		{
			if ((string)$connectionName === '')
			{
				$connectionName = "{$this->DBHost}.{$this->DBName}.{$this->DBLogin}";
			}

			$parameters = [
				'host' => $this->DBHost,
				'database' => $this->DBName,
				'login' => $this->DBLogin,
				'password' => $this->DBPassword,
			];

			$connection = $application->getConnectionPool()->cloneConnection(
				ConnectionPool::DEFAULT_CONNECTION_NAME,
				$connectionName,
				$parameters
			);

			if ($this->bNodeConnection && ($connection instanceof Main\DB\Connection))
			{
				$connection->setNodeId($this->node_id);
			}

			$found = true;
		}

		if ($found)
		{
			// real connection establishes here
			$this->db_Conn = $connection->getResource();

			$this->connection = $connection;
			$this->sqlTracker = null;
			$this->cntQuery = 0;
			$this->timeQuery = 0;
			$this->arQueryDebug = [];

			return true;
		}

		return false;
	}

	public function startSqlTracker()
	{
		if (!$this->sqlTracker)
		{
			$app = Main\Application::getInstance();
			$this->sqlTracker = $app->getConnection()->startTracker();
		}
		return $this->sqlTracker;
	}

	public function GetVersion()
	{
		if (!$this->version)
		{
			$this->version = $this->connection->getVersion()[0];
		}

		return $this->version;
	}

	public function GetNowFunction()
	{
		return $this->CurrentTimeFunction();
	}

	public function GetNowDate()
	{
		return $this->CurrentDateFunction();
	}

	public function DateToCharFunction($strFieldName, $strType="FULL", $lang=false, $bSearchInSitesOnly=false)
	{
		static $CACHE = array();

		$id = $strType . ',' . $lang . ',' . $bSearchInSitesOnly;
		if (!isset($CACHE[$id]))
		{
			if ($lang === false && ($context = Context::getCurrent()) && ($culture = $context->getCulture()) !== null)
			{
				$format = ($strType == "FULL" ? $culture->getFormatDatetime() : $culture->getFormatDate());
			}
			else
			{
				$format = CLang::GetDateFormat($strType, $lang, $bSearchInSitesOnly);
			}
			$CACHE[$id] = $this->DateFormatToDB($format);
		}

		$sFieldExpr = $strFieldName;

		//time zone
		if ($strType == "FULL" && CTimeZone::Enabled())
		{
			$diff = CTimeZone::GetOffset();

			if ($diff <> 0)
			{
				$sFieldExpr = $this->connection->getSqlHelper()->addSecondsToDateTime($diff, $strFieldName);
			}
		}

		return str_replace("#FIELD#", $sFieldExpr, $CACHE[$id]);
	}

	public function CharToDateFunction($strValue, $strType="FULL", $lang=false)
	{
		// get user time
		if ($strValue instanceof Main\Type\DateTime && !$strValue->isUserTimeEnabled())
		{
			$strValue = clone $strValue;
			$strValue->toUserTime();
		}

		// format
		if ($lang === false && ($context = Context::getCurrent()) && ($culture = $context->getCulture()) !== null)
		{
			$format = ($strType == "FULL" ? $culture->getFormatDatetime() : $culture->getFormatDate());
		}
		else
		{
			$format = CLang::GetDateFormat($strType, $lang);
		}

		$sFieldExpr = "'".CDatabase::FormatDate($strValue, $format, ($strType=="SHORT"? "YYYY-MM-DD":"YYYY-MM-DD HH:MI:SS"))."'";

		//time zone
		if($strType == "FULL" && CTimeZone::Enabled())
		{
			$diff = CTimeZone::GetOffset();

			if ($diff <> 0)
			{
				$this->Doconnect();
				$sFieldExpr = $this->connection->getSqlHelper()->addSecondsToDateTime(-$diff, $sFieldExpr);
			}
		}

		return $sFieldExpr;
	}

	public function Concat()
	{
		$this->Doconnect();
		return call_user_func_array([$this->connection->getSqlHelper(), 'getConcatFunction'], func_get_args());
	}

	public function Substr($str, $from, $length = null)
	{
		// works for mysql and oracle, redefined for mssql
		$sql = 'SUBSTR('.$str.', '.$from;

		if (!is_null($length))
		{
			$sql .= ', '.$length;
		}

		return $sql.')';
	}

	public function IsNull($expression, $result)
	{
		$this->Doconnect();
		return $this->connection->getSqlHelper()->getIsNullFunction($expression, $result);
	}

	public function Length($field)
	{
		$this->Doconnect();
		return $this->connection->getSqlHelper()->getLengthFunction($field);
	}

	public function ToChar($expr, $len=0)
	{
		return "CAST(".$expr." AS CHAR".($len > 0? "(".$len.")":"").")";
	}

	public function ToNumber($expr)
	{
		return "CAST(".$expr." AS SIGNED)";
	}

	public static function DateFormatToPHP($format)
	{
		static $cache = array();
		if (!isset($cache[$format]))
		{
			$cache[$format] = Main\Type\Date::convertFormatToPhp($format);
		}
		return $cache[$format];
	}

	public static function FormatDate($strDate, $format="DD.MM.YYYY HH:MI:SS", $new_format="DD.MM.YYYY HH:MI:SS")
	{
		if (empty($strDate))
			return false;

		if ($format===false && defined("FORMAT_DATETIME"))
			$format = FORMAT_DATETIME;

		$fromPhpFormat = Main\Type\Date::convertFormatToPhp($format);

		$time = false;
		try
		{
			$time = new Main\Type\DateTime($strDate, $fromPhpFormat);
		}
		catch(Main\ObjectException $e)
		{
		}

		if ($time !== false)
		{
			//Compatibility issue
			$fixed_format = preg_replace(
				array(
					"/(?<!Y)Y(?!Y)/i",
					"/(?<!M)M(?!M|I)/i",
					"/(?<!D)D(?!D)/i",
					"/(?<!H)H:I:S/i",
				),
				array(
					"YYYY",
					"MM",
					"DD",
					"HH:MI:SS",
				),
				mb_strtoupper($new_format)
			);
			$toPhpFormat = Main\Type\Date::convertFormatToPhp($fixed_format);

			return $time->format($toPhpFormat);
		}

		return false;
	}

	public function TopSql($strSql, $nTopCount)
	{
		$nTopCount = intval($nTopCount);
		if($nTopCount>0)
			return $strSql."\nLIMIT ".$nTopCount;
		else
			return $strSql;
	}

	public function LastID()
	{
		$this->DoConnect();
		return $this->connection->getInsertedId();
	}

	public function GetTableFieldsList($table)
	{
		return array_keys($this->GetTableFields($table));
	}

	/**
	 * @param string $strSql
	 * @param bool $bIgnoreErrors
	 * @param string $error_position
	 * @param array $arOptions
	 * @return CDBResult | false
	 */
	public function Query($strSql, $bIgnoreErrors = false, $error_position = "", $arOptions = [])
	{
		global $DB;

		$this->DoConnect();
		$this->db_Error = "";

		if ($this->DebugToFile || $DB->ShowSqlStat)
		{
			$start_time = microtime(true);
		}

		//We track queries for DML statements
		//and when there is no one we can choose
		//to run query against master connection
		//or replicated one
		$connectionPool = Main\Application::getInstance()->getConnectionPool();

		if ($connectionPool->isMasterOnly())
		{
			//We requested to process all queries
			//by master connection
		}
		elseif ($this->bModuleConnection)
		{
			//In case of dedicated module database
			//were is nothing to do
		}
		elseif (isset($arOptions["fixed_connection"]))
		{
			//We requested to process this query
			//by current connection
		}
		elseif ($this->bNodeConnection)
		{
			//It is node so nothing to do
		}
		else
		{
			if (isset($arOptions["ignore_dml"]))
			{
				$connectionPool->ignoreDml(true);
			}

			$connection = $connectionPool->getSlaveConnection($strSql);

			if (isset($arOptions["ignore_dml"]))
			{
				$connectionPool->ignoreDml(false);
			}

			if ($connection !== null)
			{
				if (!isset($this->obSlave))
				{
					$nodeId = $connection->getNodeId();

					ob_start();
					$conn = CDatabase::GetDBNodeConnection($nodeId, true);
					ob_end_clean();

					if (is_object($conn))
					{
						$this->obSlave = $conn;
					}
					else
					{
						self::$arNodes[$nodeId]["ONHIT_ERROR"] = true;
						CClusterDBNode::SetOffline($nodeId);
					}
				}

				if (is_object($this->obSlave))
				{
					return $this->obSlave->Query($strSql, $bIgnoreErrors, $error_position, $arOptions);
				}
			}
		}

		$result = $this->QueryInternal($strSql);

		if ($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if ($DB->ShowSqlStat)
			{
				$DB->addDebugQuery($strSql, $exec_time, $connectionPool->isSlavePossible() ? $this->node_id : -1);
			}

			if ($this->DebugToFile)
			{
				$this->startSqlTracker()->writeFileLog($strSql, $exec_time, "CONN: " . $this->getThreadId());
			}
		}

		if (!$result)
		{
			$this->db_Error = $this->GetError();
			$this->db_ErrorSQL = $strSql;
			if (!$bIgnoreErrors)
			{
				$application = Main\Application::getInstance();

				$ex = new Main\DB\SqlQueryException('Query error', $this->db_Error, $strSql);
				$application->getExceptionHandler()->writeToLog($ex);

				(new Main\HttpResponse())
					->setStatus('500 Internal Server Error')
					->writeHeaders()
				;

				if ($this->DebugToFile)
				{
					$this->startSqlTracker()->writeFileLog("ERROR: " . $this->db_Error, 0, "CONN: " . $this->getThreadId());
				}

				if ($this->debug)
				{
					echo $error_position . "<br><font color=#ff0000>Query Error: " . htmlspecialcharsbx($strSql) . "</font>[" . htmlspecialcharsbx($this->db_Error) . "]<br>";
				}

				$error_position = preg_replace("#<br[^>]*>#i", "\n", $error_position);
				SendError($error_position . "\nQuery Error:\n" . $strSql . " \n [" . $this->db_Error . "]\n---------------\n\n");

				if (file_exists($_SERVER["DOCUMENT_ROOT"] . BX_PERSONAL_ROOT . "/php_interface/dbquery_error.php"))
				{
					include($_SERVER["DOCUMENT_ROOT"] . BX_PERSONAL_ROOT . "/php_interface/dbquery_error.php");
					die();
				}
				else
				{
					die("Query Error!");
				}
			}
			return false;
		}

		$res = new CDBResult($result);
		$res->DB = $this;
		if ($DB->ShowSqlStat)
		{
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		}
		return $res;
	}

	//query with CLOB
	public function QueryBind($strSql, $arBinds, $bIgnoreErrors=false)
	{
		return $this->Query($strSql, $bIgnoreErrors);
	}

	public function QueryLong($strSql, $bIgnoreErrors = false)
	{
		return $this->Query($strSql, $bIgnoreErrors);
	}

	public function ForSql($strValue, $iMaxLength=0)
	{
		$this->Doconnect();
		return $this->connection->getSqlHelper()->forSql($strValue, $iMaxLength);
	}

	public function TableExists($tableName)
	{
		$this->DoConnect();
		return $this->connection->isTableExists($tableName);
	}

	public function quote($identifier)
	{
		$this->Doconnect();
		return $this->connection->getSqlHelper()->quote($identifier);
	}

	abstract public function PrepareInsert($strTableName, $arFields);

	abstract public function PrepareUpdate($strTableName, $arFields);

	public function PrepareUpdateJoin($strTableName, $arFields, $from, $where)
	{
		return '';
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
					$ar[] = $this->quote($field) . " = ''";
				else
					$ar[] = $this->quote($field) . " = ".$value."";
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

	/**
	 * @deprecated Use \Bitrix\Main\DB\Connection::parseSqlBatch()
	 * @param string $strSql
	 * @return array
	 */
	public function ParseSqlBatch($strSql)
	{
		$this->Doconnect();
		return $this->connection->parseSqlBatch($strSql);
	}

	public function RunSQLBatch($filepath)
	{
		if(!file_exists($filepath) || !is_file($filepath))
		{
			return array("File $filepath is not found.");
		}

		$arErr = array();
		$contents = file_get_contents($filepath);

		$this->Doconnect();
		foreach($this->connection->parseSqlBatch($contents) as $strSql)
		{
			if(!$this->Query($strSql, true))
			{
				$arErr[] = "<hr><pre>Query:\n".$strSql."\n\nError:\n<font color=red>".$this->GetErrorMessage()."</font></pre>";
			}
		}

		if(!empty($arErr))
		{
			return $arErr;
		}

		return false;
	}

	public function IsDate($value, $format=false, $lang=false, $format_type="SHORT")
	{
		if ($format===false) $format = CLang::GetDateFormat($format_type, $lang);
		return CheckDateTime($value, $format);
	}

	public function GetErrorMessage()
	{
		if(is_object($this->obSlave) && $this->obSlave->db_Error <> '')
			return $this->obSlave->db_Error;
		elseif($this->db_Error <> '')
		{
			return $this->db_Error."!";
		}
		else
			return '';
	}

	public function GetErrorSQL()
	{
		if(is_object($this->obSlave) && $this->obSlave->db_ErrorSQL <> '')
			return $this->obSlave->db_ErrorSQL;
		elseif($this->db_ErrorSQL <> '')
		{
			return $this->db_ErrorSQL;
		}
		else
			return '';
	}

	public function StartTransaction()
	{
		$this->DoConnect();
		$this->connection->startTransaction();
	}

	public function Commit()
	{
		$this->DoConnect();
		$this->connection->commitTransaction();
	}

	public function Rollback()
	{
		$this->DoConnect();
		$this->connection->rollbackTransaction();
	}

	public function DDL($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		$res = $this->Query($strSql, $bIgnoreErrors, $error_position, $arOptions);

		//Reset metadata cache
		$this->column_cache = array();

		return $res;
	}

	public function addDebugQuery($strSql, $exec_time, $node_id = 0)
	{
		$this->cntQuery++;
		$this->timeQuery += $exec_time;
		$this->arQueryDebug[] = $this->startSqlTracker()->getNewTrackerQuery()
			->setSql($strSql)
			->setTime($exec_time)
			->setTrace(defined("BX_NO_SQL_BACKTRACE")? null: Main\Diag\Helper::getBackTrace(8, null, 2))
			->setState($GLOBALS["BX_STATE"])
			->setNode($node_id)
		;
	}

	public function addDebugTime($index, $exec_time)
	{
		if ($this->arQueryDebug[$index])
		{
			$this->arQueryDebug[$index]->addTime($exec_time);
		}
	}

	public function GetIndexName($tableName, $arColumns, $bStrict = false)
	{
		$this->Doconnect();
		return $this->connection->getIndexName($tableName, $arColumns, $bStrict) ?? '';
	}

	public function IndexExists($tableName, $arColumns, $bStrict = false)
	{
		return $this->GetIndexName($tableName, $arColumns, $bStrict) !== "";
	}

	public function CreateIndex($indexName, $tableName, $columns, $unique = false, $fulltext = false)
	{
		return false;
	}

	/**
	 * Registers database-dependent classes for autoload.
	 *
	 * @param string|null $connectionType
	 * @return void
	 */
	public static function registerAutoload(?string $connectionType = null): void
	{
		if ($connectionType === null)
		{
			$application = Main\HttpApplication::getInstance();
			$connectionType = $application->getConnection()->getType();
		}

		Main\Loader::registerAutoLoadClasses(
			'main',
			[
				'CDatabase' => 'classes/' . $connectionType . '/database.php',
				'CDBResult' => 'classes/' . $connectionType . '/dbresult.php',
			]
		);
	}
}
