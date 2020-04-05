<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Data\ConnectionPool;

abstract class CAllDatabase
{
	var $DBName;
	var $DBHost;
	var $DBLogin;
	var $DBPassword;
	var $bConnected;

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

	function StartUsingMasterOnly()
	{
		Main\Application::getInstance()->getConnectionPool()->useMasterOnly(true);
	}

	function StopUsingMasterOnly()
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
	function GetDBNodeConnection($node_id, $bIgnoreErrors = false, $bCheckStatus = true)
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
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php"))
				include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php");
			else
				include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/dbconn_error.php");
			die();
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

	abstract function Connect($DBHost, $DBName, $DBLogin, $DBPassword);

	abstract function ConnectInternal();

	function DoConnect($connectionName = "")
	{
		if($this->bConnected)
			return true;

		$app = Main\Application::getInstance();
		if ($app != null)
		{
			$con = $app->getConnection($connectionName);
			if (
				$con
				&& $con->isConnected()
				&& ($con instanceof Bitrix\Main\DB\Connection)
				&& ($this->DBHost == $con->getHost())
				&& ($this->DBLogin == $con->getLogin())
				&& ($this->DBName == $con->getDatabase())
			)
			{
				$this->db_Conn = $con->getResource();
				$this->bConnected = true;
				$this->sqlTracker = null;
				$this->cntQuery = 0;
				$this->timeQuery = 0;
				$this->arQueryDebug = array();

				return true;
			}
		}

		if(!$this->ConnectInternal())
		{
			return false;
		}

		$this->bConnected = true;
		$this->sqlTracker = null;
		$this->cntQuery = 0;
		$this->timeQuery = 0;
		$this->arQueryDebug = array();

		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DB, $USER, $APPLICATION;
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php"))
			include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php");

		if ($app != null)
		{
			$con = $app->getConnection($connectionName);
			if(!$con && $this->bNodeConnection)
			{
				//create a node connection in the new kernel
				$pool = $app->getConnectionPool();
				$parameters = array(
					'host' => $this->DBHost,
					'database' => $this->DBName,
					'login' => $this->DBLogin,
					'password' => $this->DBPassword,
				);
				$con = $pool->cloneConnection(ConnectionPool::DEFAULT_CONNECTION_NAME, $connectionName, $parameters);
				$con->setNodeId($this->node_id);
			}
			if (
				$con
				&& !$con->isConnected()
				&& ($con instanceof Bitrix\Main\DB\Connection)
				&& ($this->DBHost == $con->getHost())
				&& ($this->DBLogin == $con->getLogin())
				&& ($this->DBName == $con->getDatabase())
			)
			{
				$con->setConnectionResourceNoDemand($this->db_Conn);
			}
		}

		return true;
	}

	function startSqlTracker()
	{
		if (!$this->sqlTracker)
		{
			$app = Main\Application::getInstance();
			$this->sqlTracker = $app->getConnection()->startTracker();
		}
		return $this->sqlTracker;
	}

	function GetNowFunction()
	{
		return CDatabase::CurrentTimeFunction();
	}

	function GetNowDate()
	{
		return CDatabase::CurrentDateFunction();
	}

	abstract function DateToCharFunction($strFieldName, $strType="FULL");

	abstract function CharToDateFunction($strValue, $strType="FULL");

	abstract function Concat();

	function Substr($str, $from, $length = null)
	{
		// works for mysql and oracle, redefined for mssql
		$sql = 'SUBSTR('.$str.', '.$from;

		if (!is_null($length))
		{
			$sql .= ', '.$length;
		}

		return $sql.')';
	}

	abstract function IsNull($expression, $result);

	abstract function Length($field);

	function ToChar($expr, $len=0)
	{
		return "CAST(".$expr." AS CHAR".($len > 0? "(".$len.")":"").")";
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
				strtoupper($new_format)
			);
			$toPhpFormat = Main\Type\Date::convertFormatToPhp($fixed_format);

			return $time->format($toPhpFormat);
		}

		return false;
	}

	/**
	 * @param string $strSql
	 * @param bool $bIgnoreErrors
	 * @param string $error_position
	 * @param array $arOptions
	 * @return CDBResult
	 */
	abstract function Query($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array());

	//query with CLOB
	function QueryBind($strSql, $arBinds, $bIgnoreErrors=false)
	{
		return $this->Query($strSql, $bIgnoreErrors);
	}

	function QueryLong($strSql, $bIgnoreErrors = false)
	{
		return $this->Query($strSql, $bIgnoreErrors);
	}

	abstract function ForSql($strValue, $iMaxLength=0);

	abstract function PrepareInsert($strTableName, $arFields);

	abstract function PrepareUpdate($strTableName, $arFields);

	function ParseSqlBatch($strSql, $bIncremental = False)
	{
		if(strtolower($this->type)=="mysql")
			$delimiter = ";";
		elseif(strtolower($this->type)=="mssql")
			$delimiter = "\nGO";
		else
			$delimiter = "(?<!\\*)/(?!\\*)";

		$strSql = trim($strSql);

		$ret = array();
		$str = "";

		do
		{
			if(preg_match("%^(.*?)(['\"`#]|--|".$delimiter.")%is", $strSql, $match))
			{
				//Found string start
				if($match[2] == "\"" || $match[2] == "'" || $match[2] == "`")
				{
					$strSql = substr($strSql, strlen($match[0]));
					$str .= $match[0];
					//find a qoute not preceeded by \
					if(preg_match("%^(.*?)(?<!\\\\)".$match[2]."%s", $strSql, $string_match))
					{
						$strSql = substr($strSql, strlen($string_match[0]));
						$str .= $string_match[0];
					}
					else
					{
						//String falled beyong end of file
						$str .= $strSql;
						$strSql = "";
					}
				}
				//Comment found
				elseif($match[2] == "#" || $match[2] == "--")
				{
					//Take that was before comment as part of sql
					$strSql = substr($strSql, strlen($match[1]));
					$str .= $match[1];
					//And cut the rest
					$p = strpos($strSql, "\n");
					if($p === false)
					{
						$p1 = strpos($strSql, "\r");
						if($p1 === false)
							$strSql = "";
						elseif($p < $p1)
							$strSql = substr($strSql, $p);
						else
							$strSql = substr($strSql, $p1);
					}
					else
						$strSql = substr($strSql, $p);
				}
				//Delimiter!
				else
				{
					//Take that was before delimiter as part of sql
					$strSql = substr($strSql, strlen($match[0]));
					$str .= $match[1];
					//Delimiter must be followed by whitespace
					if(preg_match("%^[\n\r\t ]%", $strSql))
					{
						$str = trim($str);
						if(strlen($str))
						{
							if ($bIncremental)
							{
								$strSql1 = str_replace("\r\n", "\n", $str);
								if (!$this->QueryLong($strSql1, true))
									$ret[] = $this->GetErrorMessage();
							}
							else
							{
								$ret[] = $str;
								$str = "";
							}
						}
					}
					//It was not delimiter!
					elseif(strlen($strSql))
					{
						$str .= $match[2];
					}
				}
			}
			else //End of file is our delimiter
			{
				$str .= $strSql;
				$strSql = "";
			}
		} while (strlen($strSql));

		$str = trim($str);
		if(strlen($str))
		{
			if ($bIncremental)
			{
				$strSql1 = str_replace("\r\n", "\n", $str);
				if (!$this->QueryLong($strSql1, true))
					$ret[] = $this->GetErrorMessage();
			}
			else
			{
				$ret[] = $str;
			}
		}
		return $ret;
	}

	function RunSQLBatch($filepath, $bIncremental = False)
	{
		if(!file_exists($filepath) || !is_file($filepath))
			return array("File $filepath is not found.");

		$arErr = array();
		$contents = file_get_contents($filepath);

		$arSql = $this->ParseSqlBatch($contents, $bIncremental);
		foreach($arSql as $strSql)
		{
			if ($bIncremental)
			{
				$arErr[] = $strSql;
			}
			else
			{
				$strSql = str_replace("\r\n", "\n", $strSql);
				if(!$this->Query($strSql, true))
					$arErr[] = "<hr><pre>Query:\n".$strSql."\n\nError:\n<font color=red>".$this->GetErrorMessage()."</font></pre>";
			}
		}

		if(!empty($arErr))
			return $arErr;

		return false;
	}

	function IsDate($value, $format=false, $lang=false, $format_type="SHORT")
	{
		if ($format===false) $format = CLang::GetDateFormat($format_type, $lang);
		return CheckDateTime($value, $format);
	}

	function GetErrorMessage()
	{
		if(is_object($this->obSlave) && strlen($this->obSlave->db_Error))
			return $this->obSlave->db_Error;
		elseif(strlen($this->db_Error))
			return $this->db_Error."!";
		else
			return '';
	}

	function GetErrorSQL()
	{
		if(is_object($this->obSlave) && strlen($this->obSlave->db_ErrorSQL))
			return $this->obSlave->db_ErrorSQL;
		elseif(strlen($this->db_ErrorSQL))
			return $this->db_ErrorSQL;
		else
			return '';
	}

	function DDL($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		$res = $this->Query($strSql, $bIgnoreErrors, $error_position, $arOptions);

		//Reset metadata cache
		$this->column_cache = array();

		return $res;
	}

	function addDebugQuery($strSql, $exec_time, $node_id = 0)
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

	function addDebugTime($index, $exec_time)
	{
		if ($this->arQueryDebug[$index])
		{
			$this->arQueryDebug[$index]->addTime($exec_time);
		}
	}

	abstract public function GetIndexName($tableName, $arColumns, $bStrict = false);

	public function IndexExists($tableName, $arColumns, $bStrict = false)
	{
		return $this->GetIndexName($tableName, $arColumns, $bStrict) !== "";
	}
}

abstract class CAllDBResult
{
	var $result;
	var $arResult;
	var $arReplacedAliases; // replace tech. aliases in Fetch to human aliases
	var $arResultAdd;
	var $bNavStart = false;
	var $bShowAll = false;
	var $NavNum, $NavPageCount, $NavPageNomer, $NavPageSize, $NavShowAll, $NavRecordCount;
	var $bFirstPrintNav = true;
	var $PAGEN, $SIZEN;
	var $SESS_SIZEN, $SESS_ALL, $SESS_PAGEN;
	var $add_anchor = "";
	var $bPostNavigation = false;
	var $bFromArray = false;
	var $bFromLimited = false;
	var $sSessInitAdd = "";
	var $nPageWindow = 5;
	var $nSelectedCount = false;
	var $arGetNextCache = false;
	var $bDescPageNumbering = false;
	/** @var array */
	var $arUserFields = false;
	var $usedUserFields = false;
	/** @var array */
	var $SqlTraceIndex = false;
	/** @var CDatabase */
	var $DB;
	var $NavRecordCountChangeDisable = false;
	var $is_filtered = false;
	var $nStartPage = 0;
	var $nEndPage = 0;
	/** @var Main\DB\Result */
	var $resultObject = null;

	/** @param CDBResult $res */
	public function __construct($res = null)
	{
		$obj = is_object($res);
		if($obj && is_subclass_of($res, "CAllDBResult"))
		{
			$this->result = $res->result;
			$this->nSelectedCount = $res->nSelectedCount;
			$this->arResult = $res->arResult;
			$this->arResultAdd = $res->arResultAdd;
			$this->bNavStart = $res->bNavStart;
			$this->NavPageNomer = $res->NavPageNomer;
			$this->bShowAll = $res->bShowAll;
			$this->NavNum = $res->NavNum;
			$this->NavPageCount = $res->NavPageCount;
			$this->NavPageSize = $res->NavPageSize;
			$this->NavShowAll = $res->NavShowAll;
			$this->NavRecordCount = $res->NavRecordCount;
			$this->bFirstPrintNav = $res->bFirstPrintNav;
			$this->PAGEN = $res->PAGEN;
			$this->SIZEN = $res->SIZEN;
			$this->bFromArray = $res->bFromArray;
			$this->bFromLimited = $res->bFromLimited;
			$this->nPageWindow = $res->nPageWindow;
			$this->bDescPageNumbering = $res->bDescPageNumbering;
			$this->SqlTraceIndex = $res->SqlTraceIndex;
			$this->DB = $res->DB;
			$this->arUserFields = $res->arUserFields;
		}
		elseif($obj && $res instanceof Main\DB\ArrayResult)
		{
			$this->InitFromArray($res->getResource());
		}
		elseif($obj && $res instanceof Main\DB\Result)
		{
			$this->result = $res->getResource();
			$this->resultObject = $res;
		}
		elseif(is_array($res))
		{
			$this->arResult = $res;
		}
		else
		{
			$this->result = $res;
		}
	}

	/** @deprecated */
	public function CAllDBResult($res = null)
	{
		self::__construct($res);
	}

	public function __sleep()
	{
		return array(
			'result',
			'arResult',
			'arReplacedAliases',
			'arResultAdd',
			'bNavStart',
			'bShowAll',
			'NavNum',
			'NavPageCount',
			'NavPageNomer',
			'NavPageSize',
			'NavShowAll',
			'NavRecordCount',
			'bFirstPrintNav',
			'PAGEN',
			'SIZEN',
			'add_anchor',
			'bPostNavigation',
			'bFromArray',
			'bFromLimited',
			'sSessInitAdd',
			'nPageWindow',
			'nSelectedCount',
			'arGetNextCache',
			'bDescPageNumbering',
			'arUserMultyFields',
		);
	}

	/**
	 * @return array
	 */
	abstract public function Fetch();

	/**
	 * @return array
	 */
	abstract protected function FetchInternal();

	abstract public function SelectedRowsCount();

	abstract public function AffectedRowsCount();

	abstract public function FieldsCount();

	abstract public function FieldName($iCol);

	public function NavContinue()
	{
		if (count($this->arResultAdd) > 0)
		{
			$this->arResult = $this->arResultAdd;
			return true;
		}
		else
			return false;
	}

	public function IsNavPrint()
	{
		if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && $this->NavShowAll == false))
			return false;

		return true;
	}

	public function NavPrint($title, $show_allways=false, $StyleText="text", $template_path=false)
	{
		echo $this->GetNavPrint($title, $show_allways, $StyleText, $template_path);
	}

	public function GetNavPrint($title, $show_allways=false, $StyleText="text", $template_path=false, $arDeleteParam=false)
	{
		$res = '';
		$add_anchor = $this->add_anchor;

		$sBegin = GetMessage("nav_begin");
		$sEnd = GetMessage("nav_end");
		$sNext = GetMessage("nav_next");
		$sPrev = GetMessage("nav_prev");
		$sAll = GetMessage("nav_all");
		$sPaged = GetMessage("nav_paged");

		$nPageWindow = $this->nPageWindow;

		if(!$show_allways)
		{
			if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && $this->NavShowAll == false))
				return '';
		}

		$sUrlPath = GetPagePath();

		$arDel = array("PAGEN_".$this->NavNum, "SIZEN_".$this->NavNum, "SHOWALL_".$this->NavNum, "PHPSESSID");
		if(is_array($arDeleteParam))
			$arDel = array_merge($arDel, $arDeleteParam);
		$strNavQueryString = DeleteParam($arDel);
		if($strNavQueryString <> "")
			$strNavQueryString = htmlspecialcharsbx("&".$strNavQueryString);

		if($template_path!==false && !file_exists($template_path) && file_exists($_SERVER["DOCUMENT_ROOT"].$template_path))
			$template_path = $_SERVER["DOCUMENT_ROOT"].$template_path;

		if($this->bDescPageNumbering === true)
		{
			if($this->NavPageNomer + floor($nPageWindow/2) >= $this->NavPageCount)
				$nStartPage = $this->NavPageCount;
			else
			{
				if($this->NavPageNomer + floor($nPageWindow/2) >= $nPageWindow)
					$nStartPage = $this->NavPageNomer + floor($nPageWindow/2);
				else
				{
					if($this->NavPageCount >= $nPageWindow)
						$nStartPage = $nPageWindow;
					else
						$nStartPage = $this->NavPageCount;
				}
			}

			if($nStartPage - $nPageWindow >= 0)
				$nEndPage = $nStartPage - $nPageWindow + 1;
			else
				$nEndPage = 1;
			//echo "nEndPage = $nEndPage; nStartPage = $nStartPage;";
		}
		else
		{
			if($this->NavPageNomer > floor($nPageWindow/2) + 1 && $this->NavPageCount > $nPageWindow)
				$nStartPage = $this->NavPageNomer - floor($nPageWindow/2);
			else
				$nStartPage = 1;

			if($this->NavPageNomer <= $this->NavPageCount - floor($nPageWindow/2) && $nStartPage + $nPageWindow-1 <= $this->NavPageCount)
				$nEndPage = $nStartPage + $nPageWindow - 1;
			else
			{
				$nEndPage = $this->NavPageCount;
				if($nEndPage - $nPageWindow + 1 >= 1)
					$nStartPage = $nEndPage - $nPageWindow + 1;
			}
		}

		$this->nStartPage = $nStartPage;
		$this->nEndPage = $nEndPage;

		if($template_path!==false && file_exists($template_path))
		{
/*
			$this->bFirstPrintNav - is first tiem call
			$this->NavPageNomer - number of current page
			$this->NavPageCount - total page count
			$this->NavPageSize - page size
			$this->NavRecordCount - records count
			$this->bShowAll - show "all" link
			$this->NavShowAll - is all shown
			$this->NavNum - number of navigation
			$this->bDescPageNumbering - reverse paging

			$this->nStartPage - first page in chain
			$this->nEndPage - last page in chain

			$strNavQueryString - query string
			$sUrlPath - current url

			Url for link to the page #PAGE_NUMBER#:
			$sUrlPath.'?PAGEN_'.$this->NavNum.'='.#PAGE_NUMBER#.$strNavQueryString.'#nav_start"'.$add_anchor
*/

			ob_start();
			include($template_path);
			$res = ob_get_contents();
			ob_end_clean();
			$this->bFirstPrintNav = false;
			return $res;
		}

		if($this->bFirstPrintNav)
		{
			$res .= '<a name="nav_start'.$add_anchor.'"></a>';
			$this->bFirstPrintNav = false;
		}

		$res .= '<font class="'.$StyleText.'">'.$title.' ';
		if($this->bDescPageNumbering === true)
		{
			$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			$NavFirstRecordShow = 0;
			if($this->NavPageNomer != $this->NavPageCount)
				$NavFirstRecordShow += $makeweight;

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize + 1;

			if ($this->NavPageCount == 1)
				$NavLastRecordShow = $this->NavRecordCount;
			else
				$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;

			$res .= $NavFirstRecordShow;
			$res .= ' - '.$NavLastRecordShow;
			$res .= ' '.GetMessage("nav_of").' ';
			$res .= $this->NavRecordCount;
			$res .= "\n<br>\n</font>";

			$res .= '<font class="'.$StyleText.'">';

			if($this->NavPageNomer < $this->NavPageCount)
				$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.$this->NavPageCount.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sBegin.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer+1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPrev.'</a>';
			else
				$res .= $sBegin.'&nbsp;|&nbsp;'.$sPrev;

			$res .= '&nbsp;|&nbsp;';

			$NavRecordGroup = $nStartPage;
			while($NavRecordGroup >= $nEndPage)
			{
				$NavRecordGroupPrint = $this->NavPageCount - $NavRecordGroup + 1;
				if($NavRecordGroup == $this->NavPageNomer)
					$res .= '<b>'.$NavRecordGroupPrint.'</b>&nbsp';
				else
					$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.$NavRecordGroup.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$NavRecordGroupPrint.'</a>&nbsp;';
				$NavRecordGroup--;
			}
			$res .= '|&nbsp;';
			if($this->NavPageNomer > 1)
				$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer-1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sNext.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sEnd.'</a>&nbsp;';
			else
				$res .= $sNext.'&nbsp;|&nbsp;'.$sEnd.'&nbsp;';
		}
		else
		{
			$res .= ($this->NavPageNomer-1)*$this->NavPageSize+1;
			$res .= ' - ';
			if($this->NavPageNomer != $this->NavPageCount)
				$res .= $this->NavPageNomer * $this->NavPageSize;
			else
				$res .= $this->NavRecordCount;
			$res .= ' '.GetMessage("nav_of").' ';
			$res .= $this->NavRecordCount;
			$res .= "\n<br>\n</font>";

			$res .= '<font class="'.$StyleText.'">';

			if($this->NavPageNomer > 1)
				$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sBegin.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer-1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPrev.'</a>';
			else
				$res .= $sBegin.'&nbsp;|&nbsp;'.$sPrev;

			$res .= '&nbsp;|&nbsp;';

			$NavRecordGroup = $nStartPage;
			while($NavRecordGroup <= $nEndPage)
			{
				if($NavRecordGroup == $this->NavPageNomer)
					$res .= '<b>'.$NavRecordGroup.'</b>&nbsp';
				else
					$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.$NavRecordGroup.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$NavRecordGroup.'</a>&nbsp;';
				$NavRecordGroup++;
			}
			$res .= '|&nbsp;';
			if($this->NavPageNomer < $this->NavPageCount)
				$res .= '<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer+1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sNext.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.$this->NavPageCount.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sEnd.'</a>&nbsp;';
			else
				$res .= $sNext.'&nbsp;|&nbsp;'.$sEnd.'&nbsp;';
		}

		if($this->bShowAll)
			$res .= $this->NavShowAll? '|&nbsp;<a href="'.$sUrlPath.'?SHOWALL_'.$this->NavNum.'=0'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPaged.'</a>&nbsp;' : '|&nbsp;<a href="'.$sUrlPath.'?SHOWALL_'.$this->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sAll.'</a>&nbsp;';

		$res .= '</font>';
		return $res;
	}

	public function ExtractFields($strPrefix="str_", $bDoEncode=true)
	{
		return $this->NavNext(true, $strPrefix, $bDoEncode);
	}

	public function ExtractEditFields($strPrefix="str_")
	{
		return $this->NavNext(true, $strPrefix, true, false);
	}

	public function GetNext($bTextHtmlAuto=true, $use_tilda=true)
	{
		if($arRes = $this->Fetch())
		{
			if($this->arGetNextCache==false)
			{
				$this->arGetNextCache = array();
				foreach($arRes as $FName=>$arFValue)
					$this->arGetNextCache[$FName] = array_key_exists($FName."_TYPE", $arRes);
			}
			if($use_tilda)
			{
				$arTilda = array();
				foreach($arRes as $FName=>$arFValue)
				{
					if($this->arGetNextCache[$FName] && $bTextHtmlAuto)
						$arTilda[$FName] = FormatText($arFValue, $arRes[$FName."_TYPE"]);
					elseif(is_array($arFValue))
						$arTilda[$FName] = htmlspecialcharsEx($arFValue);
					elseif(preg_match("/[;&<>\"]/", $arFValue))
						$arTilda[$FName] = htmlspecialcharsEx($arFValue);
					else
						$arTilda[$FName] = $arFValue;
					$arTilda["~".$FName] = $arFValue;
				}
				return $arTilda;
			}
			else
			{
				foreach($arRes as $FName=>$arFValue)
				{
					if($this->arGetNextCache[$FName] && $bTextHtmlAuto)
						$arRes[$FName] = FormatText($arFValue, $arRes[$FName."_TYPE"]);
					elseif(is_array($arFValue))
						$arRes[$FName] = htmlspecialcharsEx($arFValue);
					elseif(preg_match("/[;&<>\"]/", $arFValue))
						$arRes[$FName] = htmlspecialcharsEx($arFValue);
				}
			}
		}
		return $arRes;
	}

	public static function NavStringForCache($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		$NavParams = CDBResult::GetNavParams($nPageSize, $bShowAll, $iNumPage);
		return "|".($NavParams["SHOW_ALL"]?"":$NavParams["PAGEN"])."|".$NavParams["SHOW_ALL"]."|";
	}

	public static function GetNavParams($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		/** @global CMain $APPLICATION */
		global $NavNum, $APPLICATION;

		$bDescPageNumbering = false; //it can be extracted from $nPageSize

		if(is_array($nPageSize))
		{
			$params = $nPageSize;
			if(isset($params["iNumPage"]))
				$iNumPage = $params["iNumPage"];
			if(isset($params["nPageSize"]))
				$nPageSize = $params["nPageSize"];
			if(isset($params["bDescPageNumbering"]))
				$bDescPageNumbering = $params["bDescPageNumbering"];
			if(isset($params["bShowAll"]))
				$bShowAll = $params["bShowAll"];
			if(isset($params["NavShowAll"]))
				$NavShowAll = $params["NavShowAll"];
			if(isset($params["sNavID"]))
				$sNavID = $params["sNavID"];
		}

		$nPageSize = intval($nPageSize);
		$NavNum = intval($NavNum);

		$PAGEN_NAME = "PAGEN_".($NavNum+1);
		$SHOWALL_NAME = "SHOWALL_".($NavNum+1);

		global ${$PAGEN_NAME}, ${$SHOWALL_NAME};
		$md5Path = md5((isset($sNavID)? $sNavID: $APPLICATION->GetCurPage()));

		if($iNumPage === false)
			$PAGEN = ${$PAGEN_NAME};
		else
			$PAGEN = $iNumPage;

		$SHOWALL = ${$SHOWALL_NAME};

		$SESS_PAGEN = $md5Path."SESS_PAGEN_".($NavNum+1);
		$SESS_ALL = $md5Path."SESS_ALL_".($NavNum+1);
		if(intval($PAGEN) <= 0)
		{
			if(CPageOption::GetOptionString("main", "nav_page_in_session", "Y")=="Y" && intval($_SESSION[$SESS_PAGEN])>0)
				$PAGEN = $_SESSION[$SESS_PAGEN];
			elseif($bDescPageNumbering === true)
				$PAGEN = 0;
			else
				$PAGEN = 1;
		}

		//Number of records on a page
		$SIZEN = $nPageSize;
		if(intval($SIZEN) < 1)
			$SIZEN = 10;

		//Show all records
		$SHOW_ALL = ($bShowAll? (isset($SHOWALL) ? ($SHOWALL == 1) : (CPageOption::GetOptionString("main", "nav_page_in_session", "Y")=="Y" && $_SESSION[$SESS_ALL] == 1)) : false);

		//$NavShowAll comes from $nPageSize array
		$res = array(
			"PAGEN"=>$PAGEN,
			"SIZEN"=>$SIZEN,
			"SHOW_ALL"=>(isset($NavShowAll)? $NavShowAll : $SHOW_ALL),
		);

		if(CPageOption::GetOptionString("main", "nav_page_in_session", "Y")=="Y")
		{
			$_SESSION[$SESS_PAGEN] = $PAGEN;
			$_SESSION[$SESS_ALL] = $SHOW_ALL;
			$res["SESS_PAGEN"] = $SESS_PAGEN;
			$res["SESS_ALL"] = $SESS_ALL;
		}

		return $res;
	}

	public function InitNavStartVars($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		if(is_array($nPageSize) && isset($nPageSize["bShowAll"]))
			$this->bShowAll = $nPageSize["bShowAll"];
		else
			$this->bShowAll = $bShowAll;

		$this->bNavStart = true;

		$arParams = self::GetNavParams($nPageSize, $bShowAll, $iNumPage);

		$this->PAGEN = $arParams["PAGEN"];
		$this->SIZEN = $arParams["SIZEN"];
		$this->NavShowAll = $arParams["SHOW_ALL"];
		$this->NavPageSize = $arParams["SIZEN"];
		$this->SESS_SIZEN = $arParams["SESS_SIZEN"];
		$this->SESS_PAGEN = $arParams["SESS_PAGEN"];
		$this->SESS_ALL = $arParams["SESS_ALL"];

		global $NavNum;

		$NavNum++;
		$this->NavNum = $NavNum;

		if($this->NavNum>1)
			$add_anchor = "_".$this->NavNum;
		else
			$add_anchor = "";

		$this->add_anchor = $add_anchor;
	}

	public function NavStart($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		if($this->bFromLimited)
			return;

		if(is_array($nPageSize))
			$this->InitNavStartVars($nPageSize);
		else
			$this->InitNavStartVars(intval($nPageSize), $bShowAll, $iNumPage);

		if($this->bFromArray)
		{
			$this->NavRecordCount = count($this->arResult);
			if($this->NavRecordCount < 1)
				return;

			if($this->NavShowAll)
				$this->NavPageSize = $this->NavRecordCount;

			$this->NavPageCount = floor($this->NavRecordCount/$this->NavPageSize);
			if($this->NavRecordCount % $this->NavPageSize > 0)
				$this->NavPageCount++;

			$this->NavPageNomer =
				($this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount
				?
					(CPageOption::GetOptionString("main", "nav_page_in_session", "Y")!="Y"
						|| $_SESSION[$this->SESS_PAGEN] < 1
						|| $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount
					?
						1
					:
						$_SESSION[$this->SESS_PAGEN]
					)
				:
					$this->PAGEN
				);

			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;

			$this->arResult = array_slice($this->arResult, $NavFirstRecordShow, $NavLastRecordShow - $NavFirstRecordShow);
		}
		else
		{
			$this->DBNavStart();
		}
	}

	abstract public function DBNavStart();

	public function InitFromArray($arr)
	{
		if(is_array($arr))
			reset($arr);
		$this->arResult = $arr;
		$this->nSelectedCount = count($arr);
		$this->bFromArray = true;
	}

	public function NavNext($bSetGlobalVars=true, $strPrefix="str_", $bDoEncode=true, $bSkipEntities=true)
	{
		$arr = $this->Fetch();
		if($arr && $bSetGlobalVars)
		{
			foreach($arr as $key=>$val)
			{
				$varname = $strPrefix.$key;
				global $$varname;

				if($bDoEncode && !is_array($val) && !is_object($val))
				{
					if($bSkipEntities)
						$$varname = htmlspecialcharsEx($val);
					else
						$$varname = htmlspecialcharsbx($val);
				}
				else
				{
					$$varname = $val;
				}
			}
		}
		return $arr;
	}

	public function GetPageNavString($navigationTitle, $templateName = "", $showAlways=false, $parentComponent=null)
	{
		return $this->GetPageNavStringEx($dummy, $navigationTitle, $templateName, $showAlways, $parentComponent);
	}

	public function GetPageNavStringEx(&$navComponentObject, $navigationTitle, $templateName = "", $showAlways=false, $parentComponent=null, $componentParams = array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		ob_start();

		$params = array_merge(
			array(
				"NAV_TITLE"=> $navigationTitle,
				"NAV_RESULT" => $this,
				"SHOW_ALWAYS" => $showAlways
			),
			$componentParams
		);

		$navComponentObject = $APPLICATION->IncludeComponent(
			"bitrix:system.pagenavigation",
			$templateName,
			$params,
			$parentComponent,
			array(
				"HIDE_ICONS" => "Y"
			)
		);

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	public function SetUserFields($arUserFields)
	{
		if (is_array($arUserFields))
		{
			$this->arUserFields = $arUserFields;
			$this->usedUserFields = false;
		}
		else
		{
			$this->arUserFields = false;
			$this->usedUserFields = false;
		}
	}

	protected function AfterFetch(&$res)
	{
		global $USER_FIELD_MANAGER;

		if($this->arUserFields)
		{
			//Cache actual user fields on first fetch
			if ($this->usedUserFields === false)
			{
				$this->usedUserFields = array();
				foreach($this->arUserFields as $userField)
				{
					if (array_key_exists($userField['FIELD_NAME'], $res))
						$this->usedUserFields[] = $userField;
				}
			}
			// We need to call OnAfterFetch for each user field
			foreach($this->usedUserFields as $userField)
			{
				$name = $userField['FIELD_NAME'];
				if ($userField['MULTIPLE'] === 'Y')
				{
					if (substr($res[$name], 0, 1) !== 'a' && $res[$name] > 0)
					{
						$res[$name] = $USER_FIELD_MANAGER->LoadMultipleValues($userField, $res[$name]);
					}
					else
					{
						$res[$name] = unserialize($res[$name]);
					}
					$res[$name] = $USER_FIELD_MANAGER->OnAfterFetch($userField, $res[$name]);
				}
				else
				{
					$res[$name] = $USER_FIELD_MANAGER->OnAfterFetch($userField, $res[$name]);
				}
			}
		}

		if ($this->arReplacedAliases)
		{
			foreach($this->arReplacedAliases as $tech => $human)
			{
				$res[$human] = $res[$tech];
				unset($res[$tech]);
			}
		}
	}
}
