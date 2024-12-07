<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CAllAgent
{
	protected const LOCK_TIME = 600;

	public static function AddAgent(
		$name, // PHP function name
		$module = "", // module
		$period = "N", // check for agent execution count in period of time
		$interval = 86400, // time interval between execution
		$datecheck = "", // first check for execution time
		$active = "Y", // is the agent active or not
		$next_exec = "", // first execution time
		$sort = 100, // order
		$user_id = false, // user
		$existError = true // return error, if agent already exist
	)
	{
		global $DB, $APPLICATION;

		$z = $DB->Query("
			SELECT ID
			FROM b_agent
			WHERE NAME = '".$DB->ForSql($name)."'
			AND USER_ID".($user_id? " = ".(int)$user_id: " IS NULL")
		);
		if (!($agent = $z->Fetch()))
		{
			$arFields = array(
				"MODULE_ID" => $module,
				"SORT" => $sort,
				"NAME" => $name,
				"ACTIVE" => $active,
				"AGENT_INTERVAL" => $interval,
				"IS_PERIOD" => $period,
				"USER_ID" => $user_id,
			);
			$next_exec = (string)$next_exec;
			if ($next_exec != '')
				$arFields["NEXT_EXEC"] = $next_exec;

			$ID = CAgent::Add($arFields);
			return $ID;
		}
		else
		{
			if (!$existError)
				return $agent['ID'];

			if ($APPLICATION instanceof CMain)
			{
				$e = new CAdminException(array(
					array(
						"id" => "agent_exist",
						"text" => ($user_id
							? Loc::getMessage("MAIN_AGENT_ERROR_EXIST_FOR_USER", array('#AGENT#' => $name, '#USER_ID#' => $user_id))
							: Loc::getMessage("MAIN_AGENT_ERROR_EXIST_EXT", array('#AGENT#' => $name))
						)
					)
				));
				$APPLICATION->throwException($e);
			}
			return false;
		}
	}

	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (CAgent::CheckFields($arFields))
		{
			if (!is_set($arFields, "NEXT_EXEC"))
				$arFields["~NEXT_EXEC"] = $DB->GetNowDate();

			if (CACHED_b_agent !== false)
				$CACHE_MANAGER->CleanDir("agents");

			$ID = $DB->Add("b_agent", $arFields);
			foreach (GetModuleEvents("main", "OnAfterAgentAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(
					$arFields,
				));

			return $ID;
		}
		return false;
	}

	public static function RemoveAgent($name, $module = "", $user_id = false)
	{
		global $DB;

		if (trim($module) == '')
			$module = "AND (MODULE_ID is null or ".$DB->Length("MODULE_ID")." = 0)";
		else
			$module = "AND MODULE_ID = '".$DB->ForSql($module, 50)."'";

		$strSql = "
				DELETE FROM b_agent
				WHERE NAME = '".$DB->ForSql($name)."'
				".$module."
				AND  USER_ID".($user_id ? " = ".(int)$user_id : " IS NULL");

		$DB->Query($strSql);
	}

	public static function Delete($id)
	{
		global $DB;
		$id = intval($id);

		if ($id <= 0)
			return false;

		$DB->Query("DELETE FROM b_agent WHERE ID = ".$id);

		return true;
	}

	public static function RemoveModuleAgents($module)
	{
		global $DB;

		if ($module <> '')
		{
			$strSql = "DELETE FROM b_agent WHERE MODULE_ID='".$DB->ForSql($module,255)."'";
			$DB->Query($strSql);
		}
	}

	public static function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;
		$ign_name = false;

		$ID = intval($ID);

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if(is_set($arFields, "IS_PERIOD") && $arFields["IS_PERIOD"]!="Y")
			$arFields["IS_PERIOD"]="N";
		if(!is_set($arFields, "NAME"))
			$ign_name = true;

		if(CAgent::CheckFields($arFields, $ign_name))
		{
			if(CACHED_b_agent !== false)
				$CACHE_MANAGER->CleanDir("agents");

			$strUpdate = $DB->PrepareUpdate("b_agent", $arFields);
			$strSql = "UPDATE b_agent SET ".$strUpdate." WHERE ID=".$ID;
			$res = $DB->Query($strSql);
			return $res;
		}

		return false;
	}

	public static function GetById($ID)
	{
		return CAgent::GetList(Array(), Array("ID"=>intval($ID)));
	}

	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = array())
	{
		global $DB;

		$arSqlSearch = array();
		$arSqlOrder = array();

		$arOFields = array(
			"ID" => "A.ID",
			"ACTIVE" => "A.ACTIVE",
			"IS_PERIOD" => "A.IS_PERIOD",
			"NAME" => "A.NAME",
			"MODULE_ID" => "A.MODULE_ID",
			"USER_ID" => "A.USER_ID",
			"LAST_EXEC" => "A.LAST_EXEC",
			"AGENT_INTERVAL" => "A.AGENT_INTERVAL",
			"NEXT_EXEC" => "A.NEXT_EXEC",
			"DATE_CHECK" => "A.DATE_CHECK",
			"SORT" => "A.SORT"
		);

		if (!is_array($arFilter))
		{
			$arFilter = [];
		}
		foreach ($arFilter as $key => $val)
		{
			if ((string)$val == '')
			{
				continue;
			}

			switch(strtoupper($key))
			{
				case "ID":
					$arSqlSearch[] = "A.ID=".(int)$val;
					break;
				case "ACTIVE":
					$t_val = mb_strtoupper($val);
					if($t_val == "Y" || $t_val == "N")
						$arSqlSearch[] = "A.ACTIVE='".$t_val."'";
					break;
				case "IS_PERIOD":
					$t_val = mb_strtoupper($val);
					if($t_val=="Y" || $t_val=="N")
						$arSqlSearch[] = "A.IS_PERIOD='".$t_val."'";
					break;
				case "NAME":
					$arSqlSearch[] = "A.NAME LIKE '".$DB->ForSQLLike($val)."'";
					break;
				case "=NAME":
					$arSqlSearch[] = "A.NAME = '".$DB->ForSQL($val)."'";
					break;
				case "MODULE_ID":
					$arSqlSearch[] = "A.MODULE_ID = '".$DB->ForSQL($val)."'";
					break;
				case "USER_ID":
					$arSqlSearch[] = "A.USER_ID ".(intval($val)<=0?"IS NULL":"=".intval($val));
					break;
				case "LAST_EXEC":
					$arr = ParseDateTime($val, CLang::GetDateFormat());
					if($arr)
					{
						$date2 = mktime(0, 0, 0, $arr["MM"], $arr["DD"]+1, $arr["YYYY"]);
						$arSqlSearch[] = "A.LAST_EXEC>=".$DB->CharToDateFunction($DB->ForSql($val), "SHORT")." AND A.LAST_EXEC<".$DB->CharToDateFunction(ConvertTimeStamp($date2), "SHORT");
					}
					break;
				case "NEXT_EXEC":
					$arr = ParseDateTime($val);
					if($arr)
					{
						$date2 = mktime(0, 0, 0, $arr["MM"], $arr["DD"]+1, $arr["YYYY"]);
						$arSqlSearch[] = "A.NEXT_EXEC>=".$DB->CharToDateFunction($DB->ForSql($val), "SHORT")." AND A.NEXT_EXEC<".$DB->CharToDateFunction(ConvertTimeStamp($date2), "SHORT");
					}
					break;
			}
		}

		foreach($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);
			if (isset($arOFields[$by]))
			{
				if ($order != "ASC")
				{
					$order = "DESC";
				}
				$arSqlOrder[] = $arOFields[$by]." ".$order;
			}
		}

		$strSql = "SELECT A.ID, A.MODULE_ID, A.USER_ID, B.LOGIN, B.NAME as USER_NAME, B.LAST_NAME, A.SORT, ".
			"A.NAME, A.ACTIVE, A.RUNNING, ".
			$DB->DateToCharFunction("A.LAST_EXEC")." as LAST_EXEC, ".
			$DB->DateToCharFunction("A.NEXT_EXEC")." as NEXT_EXEC, ".
			$DB->DateToCharFunction("A.DATE_CHECK")." as DATE_CHECK, ".
			"A.AGENT_INTERVAL, A.IS_PERIOD, A.RETRY_COUNT ".
			"FROM b_agent A LEFT JOIN b_user B ON(A.USER_ID = B.ID)";
		$strSql .= !empty($arSqlSearch) ? " WHERE ".implode(" AND ", $arSqlSearch) : "";
		$strSql .= !empty($arSqlOrder) ? " ORDER BY ".implode(", ", $arSqlOrder) : "";

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function CheckFields($arFields, $ign_name = false)
	{
		global $DB, $APPLICATION;

		$errMsg = array();

		if(!$ign_name && (!is_set($arFields, "NAME") || mb_strlen(trim($arFields["NAME"])) <= 2))
			$errMsg[] = array("id" => "NAME", "text" => Loc::getMessage("MAIN_AGENT_ERROR_NAME"));

		if(
			array_key_exists("NEXT_EXEC", $arFields)
			&& (
				$arFields["NEXT_EXEC"] == ""
				|| !$DB->IsDate($arFields["NEXT_EXEC"], false, false, "FULL")
			)
		)
		{
			$errMsg[] = array("id" => "NEXT_EXEC", "text" => Loc::getMessage("MAIN_AGENT_ERROR_NEXT_EXEC"));
		}

		if(
			array_key_exists("DATE_CHECK", $arFields)
			&& $arFields["DATE_CHECK"] <> ""
			&& !$DB->IsDate($arFields["DATE_CHECK"], false, false, "FULL")
		)
		{
			$errMsg[] = array("id" => "DATE_CHECK", "text" => Loc::getMessage("MAIN_AGENT_ERROR_DATE_CHECK"));
		}

		if(
			array_key_exists("LAST_EXEC", $arFields)
			&& $arFields["LAST_EXEC"] <> ""
			&& !$DB->IsDate($arFields["LAST_EXEC"], false, false, "FULL")
		)
		{
			$errMsg[] = array("id" => "LAST_EXEC", "text" => Loc::getMessage("MAIN_AGENT_ERROR_LAST_EXEC"));
		}

		if(!empty($errMsg))
		{
			if ($APPLICATION instanceof CMain)
			{
				$e = new CAdminException($errMsg);
				$APPLICATION->ThrowException($e);
			}
			return false;
		}
		return true;
	}

	/**
	 * Three states: no cron (null), on cron (true), on hit (false).
	 * @return bool|null
	 */
	protected static function OnCron()
	{
		if (COption::GetOptionString('main', 'agents_use_crontab', 'N') == 'Y' || (defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true))
		{
			return (defined('BX_CRONTAB') && BX_CRONTAB === true);
		}
		return null;
	}

	public static function CheckAgents()
	{
		define("START_EXEC_AGENTS_1", microtime(true));

		define("BX_CHECK_AGENT_START", true);

		//For a while agents will execute only on primary cluster group
		if((defined("NO_AGENT_CHECK") && NO_AGENT_CHECK===true) || (defined("BX_CLUSTER_GROUP") && BX_CLUSTER_GROUP !== 1))
			return null;

		$res = CAgent::ExecuteAgents();

		define("START_EXEC_AGENTS_2", microtime(true));

		return $res;
	}

	public static function ExecuteAgents()
	{
		global $DB, $CACHE_MANAGER, $pPERIOD, $USER;

		$cron = static::OnCron();

		if ($cron !== null)
		{
			$str_crontab = ($cron ? " AND IS_PERIOD='N' " : " AND IS_PERIOD='Y' ");
		}
		else
		{
			$str_crontab = "";
		}

		$saved_time = 0;
		$cache_id = "agents".$str_crontab;
		if (CACHED_b_agent !== false && $CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents"))
		{
			$saved_time = $CACHE_MANAGER->Get($cache_id);
			if (time() < $saved_time)
			{
				return "";
			}
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$strSql = "
			SELECT 'x'
			FROM b_agent
			WHERE
				ACTIVE = 'Y'
				AND NEXT_EXEC <= " . $helper->getCurrentDateTimeFunction() . "
				AND (DATE_CHECK IS NULL OR DATE_CHECK <= " . $helper->getCurrentDateTimeFunction() . ")
				".$str_crontab."
			LIMIT 1
		";

		$db_result_agents = $DB->Query($strSql);
		if ($db_result_agents->Fetch())
		{
			if(!$connection->lock('agent'))
			{
				return "";
			}
		}
		else
		{
			if (CACHED_b_agent !== false)
			{
				$rs = $DB->Query("SELECT UNIX_TIMESTAMP(NEXT_EXEC)-UNIX_TIMESTAMP(" . $helper->getCurrentDateTimeFunction() . ") DATE_DIFF FROM b_agent WHERE ACTIVE = 'Y' " . $str_crontab . " ORDER BY NEXT_EXEC LIMIT 1");
				$ar = $rs->Fetch();

				if (!$ar || $ar["DATE_DIFF"] < 0)
					$date_diff = 0;
				elseif ($ar["DATE_DIFF"] > CACHED_b_agent)
					$date_diff = CACHED_b_agent;
				else
					$date_diff = $ar["DATE_DIFF"];

				if ($saved_time > 0)
				{
					$CACHE_MANAGER->Clean($cache_id, "agents");
					$CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents");
				}
				$CACHE_MANAGER->Set($cache_id, intval(time()+$date_diff));
			}

			return "";
		}

		$strSql =
			"SELECT ID, NAME, AGENT_INTERVAL, IS_PERIOD, MODULE_ID, RETRY_COUNT ".
			"FROM b_agent ".
			"WHERE ACTIVE = 'Y' ".
			"	AND NEXT_EXEC <= " . $helper->getCurrentDateTimeFunction() . " ".
			"	AND (DATE_CHECK IS NULL OR DATE_CHECK <= " . $helper->getCurrentDateTimeFunction() . ") ".
			$str_crontab.
			" ORDER BY RUNNING ASC, SORT desc ";

		// limit selection to prevent excessive UPDATE
		$limit = ($cron ? COption::GetOptionInt("main", "agents_cron_limit", 1000) : COption::GetOptionInt("main", "agents_limit", 100));
		if ($limit > 0)
		{
			$strSql .= ' LIMIT ' . $limit;
		}

		$db_result_agents = $DB->Query($strSql);
		$ids = '';
		$agents_array = array();
		while ($db_result_agents_array = $db_result_agents->Fetch())
		{
			$agents_array[] = $db_result_agents_array;
			$ids .= ($ids <> ''? ', ':'').$db_result_agents_array["ID"];
		}
		if ($ids <> '')
		{
			$strSql = "UPDATE b_agent SET DATE_CHECK = " . $helper->addSecondsToDateTime(self::LOCK_TIME) . " WHERE ID IN (".$ids.")";
			$DB->Query($strSql);
		}

		$connection->unlock('agent');

		/** @var callable|false $logFunction */
		$logFunction = (defined("BX_AGENTS_LOG_FUNCTION") && function_exists(BX_AGENTS_LOG_FUNCTION)? BX_AGENTS_LOG_FUNCTION : false);

		ignore_user_abort(true);
		$startTime = time();

		foreach ($agents_array as $arAgent)
		{
			if (time() - $startTime > self::LOCK_TIME - 30)
			{
				// locking time control; 30 seconds delta is for the possibly last agent
				break;
			}

			if ($logFunction)
			{
				$logFunction($arAgent, "start");
			}

			if ($arAgent["MODULE_ID"] <> '' && $arAgent["MODULE_ID"]!="main")
			{
				if (!CModule::IncludeModule($arAgent["MODULE_ID"]))
					continue;
			}

			//update the agent to the running state - if it fails it'll go to the end of the list on the next try
			$DB->Query("UPDATE b_agent SET RUNNING = 'Y', RETRY_COUNT = RETRY_COUNT+1 WHERE ID = ".$arAgent["ID"]);

			//these vars can be assigned within agent code
			$pPERIOD = $arAgent["AGENT_INTERVAL"];

			CTimeZone::Disable();

			// global $USER should not be available here
			$USER = null;

			try
			{
				$eval_result = "";
				$e = eval("\$eval_result=".$arAgent["NAME"]);
			}
			catch (Throwable $exception)
			{
				CTimeZone::Enable();

				$application = \Bitrix\Main\Application::getInstance();
				$exceptionHandler = $application->getExceptionHandler();
				$exceptionHandler->writeToLog(new \Bitrix\Main\SystemException("Error in agent {$arAgent["NAME"]}.", 0, '', 0, $exception));

				continue;
			}

			CTimeZone::Enable();

			if ($logFunction)
			{
				$logFunction($arAgent, "finish", $eval_result, $e);
			}

			if ($e === false)
			{
				continue;
			}
			elseif ($eval_result == '')
			{
				$strSql = "DELETE FROM b_agent WHERE ID = ".$arAgent["ID"];
			}
			else
			{
				if ($logFunction && function_exists('token_get_all'))
				{
					if (count(token_get_all("<?php ".$eval_result)) < 3)
					{
						//probably there is an error in the result
						$logFunction($arAgent, "not_callable", $eval_result, $e);
					}
				}

				$strSql = "
					UPDATE b_agent SET
						NAME = '".$DB->ForSQL($eval_result)."',
						LAST_EXEC = " . $helper->getCurrentDateTimeFunction() . ",
						NEXT_EXEC = " . $helper->addSecondsToDateTime($pPERIOD, $arAgent["IS_PERIOD"]=="Y"? "NEXT_EXEC" : null) . ",
						DATE_CHECK = NULL,
						RUNNING = 'N',
						RETRY_COUNT = 0
					WHERE ID = ".$arAgent["ID"];
			}
			$DB->Query($strSql);
		}

		return null;
	}
}

class CAgent extends CAllAgent
{
}
