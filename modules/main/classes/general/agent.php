<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CAllAgent
{
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

		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	public static function Delete($id)
	{
		global $DB;
		$id = intval($id);

		if ($id <= 0)
			return false;

		$DB->Query("DELETE FROM b_agent WHERE ID = ".$id, false, "FILE: ".__FILE__."<br>LINE: ");

		return true;
	}

	public static function RemoveModuleAgents($module)
	{
		global $DB;

		if (strlen($module) > 0)
		{
			$strSql = "DELETE FROM b_agent WHERE MODULE_ID='".$DB->ForSql($module,255)."'";
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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
			$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			return $res;
		}

		return false;
	}

	public static function GetById($ID)
	{
		return CAgent::GetList(Array(), Array("ID"=>IntVal($ID)));
	}

	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = array())
	{
		global $DB;
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";

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

		if(!is_array($arFilter))
			$filter_keys = array();
		else
			$filter_keys = array_keys($arFilter);

		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			$key = strtoupper($filter_keys[$i]);
			if(strlen($val)<=0 || ($key=="USER_ID" && $val!==false && $val!==null))
				continue;

			switch($key)
			{
				case "ID":
					$arSqlSearch[] = "A.ID=".(int)$val;
					break;
				case "ACTIVE":
					$t_val = strtoupper($val);
					if($t_val == "Y" || $t_val == "N")
						$arSqlSearch[] = "A.ACTIVE='".$t_val."'";
					break;
				case "IS_PERIOD":
					$t_val = strtoupper($val);
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
					$arSqlSearch[] = "A.USER_ID ".(IntVal($val)<=0?"IS NULL":"=".IntVal($val));
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
			$by = strtoupper($by);
			$order = strtoupper($order);
			if (isset($arOFields[$by]))
			{
				if ($order != "ASC")
					$order = "DESC".($DB->type=="ORACLE" ? " NULLS LAST" : "");
				else
					$order = "ASC".($DB->type=="ORACLE" ? " NULLS FIRST" : "");
				$arSqlOrder[] = $arOFields[$by]." ".$order;
			}
		}

		$strSql = "SELECT A.ID, A.MODULE_ID, A.USER_ID, B.LOGIN, B.NAME as USER_NAME, B.LAST_NAME, A.SORT, ".
			"A.NAME, A.ACTIVE, A.RUNNING, ".
			$DB->DateToCharFunction("A.LAST_EXEC")." as LAST_EXEC, ".
			$DB->DateToCharFunction("A.NEXT_EXEC")." as NEXT_EXEC, ".
			$DB->DateToCharFunction("A.DATE_CHECK")." as DATE_CHECK, ".
			"A.AGENT_INTERVAL, A.IS_PERIOD ".
			"FROM b_agent A LEFT JOIN b_user B ON(A.USER_ID = B.ID)";
		$strSql .= (count($arSqlSearch)>0) ? " WHERE ".implode(" AND ", $arSqlSearch) : "";
		$strSql .= (count($arSqlOrder)>0) ? " ORDER BY ".implode(", ", $arSqlOrder) : "";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	public static function CheckFields(&$arFields, $ign_name = false)
	{
		global $DB, $APPLICATION;

		$errMsg = array();

		if(!$ign_name && (!is_set($arFields, "NAME") || strlen(trim($arFields["NAME"])) <= 2))
			$errMsg[] = array("id" => "NAME", "text" => Loc::getMessage("MAIN_AGENT_ERROR_NAME"));

		if(
			array_key_exists("NEXT_EXEC", $arFields)
			&& (
				$arFields["NEXT_EXEC"] == ""
				|| !$DB->IsDate($arFields["NEXT_EXEC"], false, LANG, "FULL")
			)
		)
		{
			$errMsg[] = array("id" => "NEXT_EXEC", "text" => Loc::getMessage("MAIN_AGENT_ERROR_NEXT_EXEC"));
		}

		if(
			array_key_exists("DATE_CHECK", $arFields)
			&& $arFields["DATE_CHECK"] <> ""
			&& !$DB->IsDate($arFields["DATE_CHECK"], false, LANG, "FULL")
		)
		{
			$errMsg[] = array("id" => "DATE_CHECK", "text" => Loc::getMessage("MAIN_AGENT_ERROR_DATE_CHECK"));
		}

		if(
			array_key_exists("LAST_EXEC", $arFields)
			&& $arFields["LAST_EXEC"] <> ""
			&& !$DB->IsDate($arFields["LAST_EXEC"], false, LANG, "FULL")
		)
		{
			$errMsg[] = array("id" => "LAST_EXEC", "text" => Loc::getMessage("MAIN_AGENT_ERROR_LAST_EXEC"));
		}

		if($arFields["MODULE_ID"] <> '')
			if(!IsModuleInstalled($arFields["MODULE_ID"]))
				$errMsg[] = array("id" => "MODULE_ID", "text" => Loc::getMessage("MAIN_AGENT_ERROR_MODULE"));

		if(!empty($errMsg))
		{
			$e = new CAdminException($errMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}
}
