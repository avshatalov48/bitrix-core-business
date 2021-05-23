<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

use Bitrix\Main;

/**
 * @deprecated Use CTask
 */
class CAllTask
{
	protected static $TASK_OPERATIONS_CACHE = array();

	public static function err_mess()
	{
		return "<br>Class: CTask<br>File: ".__FILE__;
	}

	public static function CheckFields(&$arFields, $ID = false)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		if($ID>0)
			unset($arFields["ID"]);

		$arMsg = array();

		if(($ID===false || is_set($arFields, "NAME")) && $arFields["NAME"] == '')
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_EMPTY'));

		$sql_str = "SELECT T.ID
			FROM b_task T
			WHERE T.NAME='".$DB->ForSQL($arFields['NAME'])."'";
		if ($ID !== false)
			$sql_str .= " AND T.ID <> ".intval($ID);

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if ($r = $z->Fetch())
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_DOUBLE'));

		if (isset($arFields['LETTER']))
		{
			if (preg_match("/[^A-Z]/i", $arFields['LETTER']) || strlen($arFields['LETTER']) > 1)
				$arMsg[] = array("id"=>"LETTER", "text"=> GetMessage('MAIN_TASK_WRONG_LETTER'));
			$arFields['LETTER'] = strtoupper($arFields['LETTER']);
		}
		else
		{
			$arFields['LETTER'] = '';
		}

		if(count($arMsg)>0)
		{
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		if (!isset($arFields['SYS']) || $arFields['SYS'] != "Y")
			$arFields['SYS'] = "N";
		if (!isset($arFields['BINDING']))
			$arFields['BINDING'] = 'module';

		return true;
	}

	public static function Add($arFields)
	{
		global $CACHE_MANAGER, $DB;

		if(!static::CheckFields($arFields))
			return false;

		if(CACHED_b_task !== false)
			$CACHE_MANAGER->CleanDir("b_task");

		$ID = $DB->Add("b_task", $arFields);
		return $ID;
	}

	public static function Update($arFields,$ID)
	{
		global $DB, $CACHE_MANAGER;

		if(!static::CheckFields($arFields,$ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_task", $arFields);

		if($strUpdate)
		{
			if(CACHED_b_task !== false)
				$CACHE_MANAGER->CleanDir("b_task");
			$strSql =
				"UPDATE b_task SET ".
					$strUpdate.
				" WHERE ID=".intval($ID);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

	public static function UpdateModuleRights($id, $moduleId, $letter, $site_id = false)
	{
		global $DB;

		if (!isset($id, $moduleId))
			return false;

		$sql = "SELECT GT.GROUP_ID
				FROM b_group_task GT
				WHERE GT.TASK_ID=".intval($id);
		$z = $DB->Query($sql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$arGroups = array();
		while($r = $z->Fetch())
		{
			$g = intval($r['GROUP_ID']);
			if ($g > 0)
				$arGroups[] = $g;
		}
		if (count($arGroups) == 0)
			return false;

		$str_groups = implode(',', $arGroups);
		$moduleId = $DB->ForSQL($moduleId);
		$DB->Query(
			"DELETE FROM b_module_group
			WHERE
				MODULE_ID = '".$moduleId."' AND
				SITE_ID ".($site_id ? "='".$site_id."'" : "IS NULL")." AND
				GROUP_ID IN (".$str_groups.")",
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__
		);

		if ($letter == '')
			return false;

		$letter = $DB->ForSQL($letter);
		$DB->Query(
			"INSERT INTO b_module_group (MODULE_ID, GROUP_ID, G_ACCESS, SITE_ID) ".
			"SELECT '".$moduleId."', G.ID, '".$letter."', ".($site_id ? "'".$site_id."'" : "NULL")." ".
			"FROM b_group G ".
			"WHERE G.ID IN (".$str_groups.")"
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
		return true;
	}

	public static function Delete($ID, $protect = true)
	{
		global $DB, $CACHE_MANAGER;

		$ID = intval($ID);

		if(CACHED_b_task !== false)
			$CACHE_MANAGER->CleanDir("b_task");

		$sql_str = "DELETE FROM b_task WHERE ID=".$ID;
		if ($protect)
			$sql_str .= " AND SYS='N'";
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (!$protect)
		{
			if(CACHED_b_task_operation !== false)
				$CACHE_MANAGER->CleanDir("b_task_operation");

			$DB->Query("DELETE FROM b_task_operation WHERE TASK_ID=".$ID, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
	}

	public static function GetList($arOrder = array('MODULE_ID'=>'asc','LETTER'=>'asc'), $arFilter = array())
	{
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_task !== false)
		{
			$context = Main\Context::getCurrent();
			$cacheId = "b_task".md5(serialize($arOrder).".".serialize($arFilter).".".$context->getLanguage());
			if($CACHE_MANAGER->Read(CACHED_b_task, $cacheId, "b_task"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				return $res;
			}
		}

		static $arFields = array(
			"ID" => array("FIELD_NAME" => "T.ID", "FIELD_TYPE" => "int"),
			"NAME" => array("FIELD_NAME" => "T.NAME", "FIELD_TYPE" => "string"),
			"LETTER" => array("FIELD_NAME" => "T.LETTER", "FIELD_TYPE" => "string"),
			"MODULE_ID" => array("FIELD_NAME" => "T.MODULE_ID", "FIELD_TYPE" => "string"),
			"SYS" => array("FIELD_NAME" => "T.SYS", "FIELD_TYPE" => "string"),
			"BINDING" => array("FIELD_NAME" => "T.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (static::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		if(is_array($arFilter))
		{
			foreach($arFilter as $n => $val)
			{
				$n = strtoupper($n);
				if((string)$val == '' || strval($val) == "NOT_REF")
					continue;

				if(isset($arFields[$n]))
				{
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, ($n == 'NAME'? "Y" : "N"));
				}
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order) == 'desc'?'desc':'asc').',';

		if($strOrderBy <> '')
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				T.ID, T.NAME, T.DESCRIPTION, T.MODULE_ID, T.LETTER, T.SYS, T.BINDING
			FROM
				b_task T
			WHERE
				".$strSqlSearch."
			".$strOrderBy;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$arResult = array();
		while($arRes = $res->Fetch())
		{
			$arRes['TITLE'] = static::GetLangTitle($arRes['NAME'], $arRes['MODULE_ID']);
			$arRes['DESC'] = static::GetLangDescription($arRes['NAME'], $arRes['DESCRIPTION'], $arRes['MODULE_ID']);
			$arResult[] = $arRes;
		}
		$res->InitFromArray($arResult);

		if(CACHED_b_task !== false)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$CACHE_MANAGER->Set($cacheId, $arResult);
		}

		return $res;
	}


	public static function GetOperations($ID, $return_names = false)
	{
		global $DB, $CACHE_MANAGER;

		$ID = intval($ID);

		if (!isset(static::$TASK_OPERATIONS_CACHE[$ID]))
		{
			if(CACHED_b_task_operation !== false)
			{
				$cacheId = "b_task_operation_".$ID;
				if($CACHE_MANAGER->Read(CACHED_b_task_operation, $cacheId, "b_task_operation"))
				{
					static::$TASK_OPERATIONS_CACHE[$ID] = $CACHE_MANAGER->Get($cacheId);
				}
			}
		}

		if (!isset(static::$TASK_OPERATIONS_CACHE[$ID]))
		{
			$sql_str = '
				SELECT T_O.OPERATION_ID, O.NAME
				FROM b_task_operation T_O
				INNER JOIN b_operation O ON T_O.OPERATION_ID = O.ID
				WHERE T_O.TASK_ID = '.$ID.'
			';

			static::$TASK_OPERATIONS_CACHE[$ID] = array(
				'names' => array(),
				'ids' => array(),
			);
			$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($r = $z->Fetch())
			{
				static::$TASK_OPERATIONS_CACHE[$ID]['names'][] = $r['NAME'];
				static::$TASK_OPERATIONS_CACHE[$ID]['ids'][] = $r['OPERATION_ID'];
			}

			if(CACHED_b_task_operation !== false)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$CACHE_MANAGER->Set($cacheId, static::$TASK_OPERATIONS_CACHE[$ID]);
			}
		}

		return static::$TASK_OPERATIONS_CACHE[$ID][$return_names ? 'names' : 'ids'];
	}

	public static function SetOperations($ID, $arr, $bOpNames=false)
	{
		global $DB, $CACHE_MANAGER;

		$ID = intval($ID);

		//get old operations
		$aPrevOp = array();
		$res = $DB->Query("
			SELECT O.NAME
			FROM b_operation O
			INNER JOIN b_task_operation T_OP ON O.ID = T_OP.OPERATION_ID
			WHERE T_OP.TASK_ID = ".$ID."
			ORDER BY O.ID
		");
		while(($res_arr = $res->Fetch()))
			$aPrevOp[] = $res_arr["NAME"];

		$sql_str = 'DELETE FROM b_task_operation WHERE TASK_ID='.$ID;
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if(is_array($arr) && count($arr)>0)
		{
			if($bOpNames)
			{
				$sID = "";
				foreach($arr as $op_id)
					$sID .= ",'".$DB->ForSQL($op_id)."'";
				$sID = LTrim($sID, ",");

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', O.ID ".
					"FROM b_operation O, b_task T ".
					"WHERE O.NAME IN (".$sID.") AND T.MODULE_ID=O.MODULE_ID AND T.ID=".$ID." "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
			else
			{
				$sID = "0";
				foreach($arr as $op_id)
					$sID .= ",".intval($op_id);

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', ID ".
					"FROM b_operation ".
					"WHERE ID IN (".$sID.") "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
		}

		unset(static::$TASK_OPERATIONS_CACHE[$ID]);

		if(CACHED_b_task_operation !== false)
			$CACHE_MANAGER->CleanDir("b_task_operation");

		//get new operations
		$aNewOp = array();
		$res = $DB->Query("
			SELECT O.NAME
			FROM b_operation O
			INNER JOIN b_task_operation T_OP ON O.ID = T_OP.OPERATION_ID
			WHERE T_OP.TASK_ID = ".$ID."
			ORDER BY O.ID
		");
		while(($res_arr = $res->Fetch()))
			$aNewOp[] = $res_arr["NAME"];

		//compare with old one
		$aDiff = array_diff($aNewOp, $aPrevOp);
		if(empty($aDiff))
			$aDiff = array_diff($aPrevOp, $aNewOp);
		if(!empty($aDiff))
		{
			if(COption::GetOptionString("main", "event_log_task", "N") === "Y")
				CEventLog::Log("SECURITY", "TASK_CHANGED", "main", $ID, "(".implode(", ", $aPrevOp).") => (".implode(", ", $aNewOp).")");
			foreach(GetModuleEvents("main", "OnTaskOperationsChanged", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $aPrevOp, $aNewOp));
		}
	}

	public static function GetTasksInModules($mode=false, $module_id=false, $binding = false)
	{
		$arFilter = array();
		if ($module_id !== false)
			$arFilter["MODULE_ID"] = $module_id;
		if ($binding !== false)
			$arFilter["BINDING"] = $binding;

		$z = static::GetList(
			array(
				"MODULE_ID" => "asc",
				"LETTER" => "asc"
			),
			$arFilter
		);

		$arr = array();
		if ($mode)
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = array('reference_id'=>array(),'reference'=>array());

				$arr[$r['MODULE_ID']]['reference_id'][] = $r['ID'];
				$arr[$r['MODULE_ID']]['reference'][] = '['.($r['LETTER'] ? $r['LETTER'] : '..').'] '.static::GetLangTitle($r['NAME'], $r['MODULE_ID']);
			}
		}
		else
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = array();

				$arr[$r['MODULE_ID']][] = $r;
			}
		}
		return $arr;
	}

	public static function GetByID($ID)
	{
		return static::GetList(array(), array("ID" => intval($ID)));
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = array();

		if(preg_match("/[^a-z0-9._]/i", $module))
		{
			return array();
		}

		if(!isset($descriptions[$module]))
		{
			if(($path = getLocalPath("modules/".$module."/admin/task_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"].$path);
			}
			else
			{
				$descriptions[$module] = array();
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}

	public static function GetLetter($ID)
	{
		$z = static::GetById($ID);
		if ($r = $z->Fetch())
			if ($r['LETTER'])
				return $r['LETTER'];
		return false;
	}

	public static function GetIdByLetter($letter, $module, $binding='module')
	{
		static $TASK_LETTER_CACHE = array();
		if (!$letter)
			return false;

		if (!isset($TASK_LETTER_CACHE))
			$TASK_LETTER_CACHE = array();

		$k = strtoupper($letter.'_'.$module.'_'.$binding);
		if (isset($TASK_LETTER_CACHE[$k]))
			return $TASK_LETTER_CACHE[$k];

		$z = static::GetList(
			array(),
			array(
				"LETTER" => $letter,
				"MODULE_ID" => $module,
				"BINDING" => $binding,
				"SYS"=>"Y"
			)
		);

		if ($r = $z->Fetch())
		{
			$TASK_LETTER_CACHE[$k] = $r['ID'];
			if ($r['ID'])
				return $r['ID'];
		}

		return false;
	}
}

class CTask extends CAllTask
{
}
