<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\SystemException;

define("MODULE_NOT_FOUND", 0);
define("MODULE_INSTALLED", 1);
define("MODULE_DEMO", 2);
define("MODULE_DEMO_EXPIRED", 3);

class CModule
{
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_ID;
	var $MODULE_SORT = 10000;
	var $SHOW_SUPER_ADMIN_GROUP_RIGHTS;
	var $MODULE_GROUP_RIGHTS;
	var $PARTNER_NAME;
	var $PARTNER_URI;

	public static function AddAutoloadClasses($module, $arParams = array())
	{
		if ($module === '')
			$module = null;

		\Bitrix\Main\Loader::registerAutoLoadClasses($module, $arParams);
		return true;
	}

	public static function _GetCache()
	{
		return \Bitrix\Main\ModuleManager::getInstalledModules();
	}

	function InstallDB()
	{
		return false;
	}

	function UnInstallDB()
	{
	}

	function InstallEvents()
	{
	}

	function UnInstallEvents()
	{
	}

	function InstallFiles()
	{
	}

	function UnInstallFiles()
	{
	}

	function DoInstall()
	{
	}

	public function GetModuleTasks()
	{
		return array(
			/*
			"NAME" => array(
				"LETTER" => "",
				"BINDING" => "",
				"OPERATIONS" => array(
					"NAME",
					"NAME",
				),
			),
			*/
		);
	}

	public function InstallTasks()
	{
		global $DB, $CACHE_MANAGER;

		$sqlMODULE_ID = $DB->ForSQL($this->MODULE_ID, 50);

		$arDBOperations = array();
		$rsOperations = $DB->Query("SELECT NAME FROM b_operation WHERE MODULE_ID = '$sqlMODULE_ID'");
		while($ar = $rsOperations->Fetch())
			$arDBOperations[$ar["NAME"]] = $ar["NAME"];

		$arDBTasks = array();
		$rsTasks = $DB->Query("SELECT NAME FROM b_task WHERE MODULE_ID = '$sqlMODULE_ID' AND SYS = 'Y'");
		while($ar = $rsTasks->Fetch())
			$arDBTasks[$ar["NAME"]] = $ar["NAME"];

		$arModuleTasks = $this->GetModuleTasks();
		foreach($arModuleTasks as $task_name => $arTask)
		{
			$sqlBINDING = isset($arTask["BINDING"]) && $arTask["BINDING"] <> ''? $DB->ForSQL($arTask["BINDING"], 50): 'module';
			$sqlTaskOperations = array();

			if(isset($arTask["OPERATIONS"]) && is_array($arTask["OPERATIONS"]))
			{
				foreach($arTask["OPERATIONS"] as $operation_name)
				{
					$operation_name = mb_substr($operation_name, 0, 50);

					if(!isset($arDBOperations[$operation_name]))
					{
						$DB->Query("
							INSERT INTO b_operation
							(NAME, MODULE_ID, BINDING)
							VALUES
							('".$DB->ForSQL($operation_name)."', '$sqlMODULE_ID', '$sqlBINDING')
						", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

						$arDBOperations[$operation_name] = $operation_name;
					}

					$sqlTaskOperations[] = $DB->ForSQL($operation_name);
				}
			}

			$task_name = mb_substr($task_name, 0, 100);
			$sqlTaskName = $DB->ForSQL($task_name);

			if(!isset($arDBTasks[$task_name]) && $task_name <> '')
			{
				$DB->Query("
					INSERT INTO b_task
					(NAME, LETTER, MODULE_ID, SYS, BINDING)
					VALUES
					('$sqlTaskName', '".$DB->ForSQL($arTask["LETTER"], 1)."', '$sqlMODULE_ID', 'Y', '$sqlBINDING')
				", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}

			if(!empty($sqlTaskOperations) && $task_name <> '')
			{
				$DB->Query("
					INSERT INTO b_task_operation
					(TASK_ID,OPERATION_ID)
					SELECT T.ID TASK_ID, O.ID OPERATION_ID
					FROM
						b_task T
						,b_operation O
					WHERE
						T.SYS='Y'
						AND T.NAME='$sqlTaskName'
						AND O.NAME in ('".implode("','", $sqlTaskOperations)."')
						AND O.NAME not in (
							SELECT O2.NAME
							FROM
								b_task T2
								inner join b_task_operation TO2 on TO2.TASK_ID = T2.ID
								inner join b_operation O2 on O2.ID = TO2.OPERATION_ID
							WHERE
								T2.SYS='Y'
								AND T2.NAME='$sqlTaskName'
						)
				", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
		}

		if(is_object($CACHE_MANAGER))
		{
			$CACHE_MANAGER->CleanDir("b_task");
			$CACHE_MANAGER->CleanDir("b_task_operation");
		}
	}

	public function UnInstallTasks()
	{
		global $DB, $CACHE_MANAGER;

		$sqlMODULE_ID = $DB->ForSQL($this->MODULE_ID, 50);

		$DB->Query("
			DELETE FROM b_group_task
			WHERE TASK_ID IN (
				SELECT T.ID
				FROM b_task T
				WHERE T.MODULE_ID = '$sqlMODULE_ID'
			)
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$DB->Query("
			DELETE FROM b_task_operation
			WHERE TASK_ID IN (
				SELECT T.ID
				FROM b_task T
				WHERE T.MODULE_ID = '$sqlMODULE_ID')
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$DB->Query("
			DELETE FROM b_operation
			WHERE MODULE_ID = '$sqlMODULE_ID'
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$DB->Query("
			DELETE FROM b_task
			WHERE MODULE_ID = '$sqlMODULE_ID'
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if(is_object($CACHE_MANAGER))
		{
			$CACHE_MANAGER->CleanDir("b_task");
			$CACHE_MANAGER->CleanDir("b_task_operation");
		}
	}

	function IsInstalled()
	{
		return \Bitrix\Main\ModuleManager::isModuleInstalled($this->MODULE_ID);
	}

	function DoUninstall()
	{
	}

	function Remove()
	{
		\Bitrix\Main\ModuleManager::delete($this->MODULE_ID);
	}

	function Add()
	{
		\Bitrix\Main\ModuleManager::add($this->MODULE_ID);
	}

	public static function GetList()
	{
		$result = new CDBResult;
		$result->InitFromArray(CModule::_GetCache());
		return $result;
	}

	/**
	 * Makes module classes and function available. Returns true on success.
	 *
	 * @param string $module_name
	 * @return bool
	 */
	public static function IncludeModule($module_name)
	{
		return \Bitrix\Main\Loader::includeModule($module_name);
	}

	public static function IncludeModuleEx($module_name)
	{
		return \Bitrix\Main\Loader::includeSharewareModule($module_name);
	}

	public static function err_mess()
	{
		return "<br>Class: CModule;<br>File: ".__FILE__;
	}

	public static function GetDropDownList($strSqlOrder="ORDER BY ID")
	{
		global $DB;
		$err_mess = (CModule::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				ID as REFERENCE
			FROM
				b_module
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	/**
	 * @param string $moduleId
	 * @return CModule|bool
	 */
	public static function CreateModuleObject($moduleId)
	{
		$moduleId = trim($moduleId);
		$moduleId = preg_replace("/[^a-zA-Z0-9_.]+/i", "", $moduleId);
		if ($moduleId == '')
			return false;

		$path = getLocalPath("modules/".$moduleId."/install/index.php");
		if ($path === false)
			return false;

		include_once($_SERVER["DOCUMENT_ROOT"].$path);

		$className = str_replace(".", "_", $moduleId);
		if (!class_exists($className))
			return false;

		return new $className;
	}
}

function RegisterModule($id)
{
	\Bitrix\Main\ModuleManager::registerModule($id);
}

function UnRegisterModule($id)
{
	\Bitrix\Main\ModuleManager::unRegisterModule($id);
}

function AddEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $CALLBACK, $SORT=100, $FULL_PATH = false)
{
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	return $eventManager->addEventHandlerCompatible($FROM_MODULE_ID, $MESSAGE_ID, $CALLBACK, $FULL_PATH, $SORT);
}

function RemoveEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $iEventHandlerKey)
{
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	return $eventManager->removeEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $iEventHandlerKey);
}

function GetModuleEvents($MODULE_ID, $MESSAGE_ID, $bReturnArray = false)
{
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$arrResult = $eventManager->findEventHandlers($MODULE_ID, $MESSAGE_ID);

	foreach($arrResult as $k => $event)
	{
		$arrResult[$k]['FROM_MODULE_ID'] = $MODULE_ID;
		$arrResult[$k]['MESSAGE_ID'] = $MESSAGE_ID;
	}

	if($bReturnArray)
	{
		return $arrResult;
	}
	else
	{
		$resRS = new CDBResult;
		$resRS->InitFromArray($arrResult);
		return $resRS;
	}
}

/**
 * @param $arEvent
 * @param null $param1
 * @param null $param2
 * @param null $param3
 * @param null $param4
 * @param null $param5
 * @param null $param6
 * @param null $param7
 * @param null $param8
 * @param null $param9
 * @param null $param10
 * @return bool|mixed|null
 *
 * @deprecated
 */
function ExecuteModuleEvent($arEvent, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null, $param6=null, $param7=null, $param8=null, $param9=null, $param10=null)
{
	$CNT_PREDEF = 10;
	$r = true;
	if(!empty($arEvent["TO_MODULE_ID"]) && $arEvent["TO_MODULE_ID"] <> 'main')
	{
		if(!CModule::IncludeModule($arEvent["TO_MODULE_ID"]))
			return null;
		$r = include_once($_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".$arEvent["TO_MODULE_ID"]."/include.php"));
	}
	elseif(!empty($arEvent["TO_PATH"]) && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]))
	{
		$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]);
	}
	elseif(!empty($arEvent["FULL_PATH"]) && file_exists($arEvent["FULL_PATH"]))
	{
		$r = include_once($arEvent["FULL_PATH"]);
	}

	if((empty($arEvent["TO_CLASS"]) || empty($arEvent["TO_METHOD"])) && !is_set($arEvent, "CALLBACK"))
		return $r;

	$args = array();
	if (isset($arEvent["TO_METHOD_ARG"]) && is_array($arEvent["TO_METHOD_ARG"]) && !empty($arEvent["TO_METHOD_ARG"]))
	{
		foreach ($arEvent["TO_METHOD_ARG"] as $v)
			$args[] = $v;
	}

	$nArgs = func_num_args();
	for($i = 1; $i <= $CNT_PREDEF; $i++)
	{
		if($i > $nArgs)
			break;
		$args[] = &${"param".$i};
	}

	for($i = $CNT_PREDEF + 1; $i < $nArgs; $i++)
		$args[] = func_get_arg($i);

	//TODO: Возможно заменить на EventManager::getInstance()->getLastEvent();
	global $BX_MODULE_EVENT_LAST;
	$BX_MODULE_EVENT_LAST = $arEvent;

	if(is_set($arEvent, "CALLBACK"))
	{
		$resmod = call_user_func_array($arEvent["CALLBACK"], $args);
	}
	else
	{
		//php bug: http://bugs.php.net/bug.php?id=47948
		class_exists($arEvent["TO_CLASS"]);
		$resmod = call_user_func_array(array($arEvent["TO_CLASS"], $arEvent["TO_METHOD"]), $args);
	}

	return $resmod;
}

function ExecuteModuleEventEx($arEvent, $arParams = array())
{
	$r = true;

	if(
		isset($arEvent["TO_MODULE_ID"])
		&& $arEvent["TO_MODULE_ID"]<>""
		&& $arEvent["TO_MODULE_ID"]<>"main"
	)
	{
		if(!CModule::IncludeModule($arEvent["TO_MODULE_ID"]))
			return null;
	}
	elseif(
		isset($arEvent["TO_PATH"])
		&& $arEvent["TO_PATH"]<>""
		&& file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"])
	)
	{
		$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]);
	}
	elseif(
		isset($arEvent["FULL_PATH"])
		&& $arEvent["FULL_PATH"]<>""
		&& file_exists($arEvent["FULL_PATH"])
	)
	{
		$r = include_once($arEvent["FULL_PATH"]);
	}

	if(array_key_exists("CALLBACK", $arEvent))
	{
		//TODO: Возможно заменить на EventManager::getInstance()->getLastEvent();
		global $BX_MODULE_EVENT_LAST;
		$BX_MODULE_EVENT_LAST = $arEvent;

		if(isset($arEvent["TO_METHOD_ARG"]) && is_array($arEvent["TO_METHOD_ARG"]) && count($arEvent["TO_METHOD_ARG"]))
			$args = array_merge($arEvent["TO_METHOD_ARG"], $arParams);
		else
			$args = $arParams;

		return call_user_func_array($arEvent["CALLBACK"], $args);
	}
	elseif($arEvent["TO_CLASS"] != "" && $arEvent["TO_METHOD"] != "")
	{
		//TODO: Возможно заменить на EventManager::getInstance()->getLastEvent();
		global $BX_MODULE_EVENT_LAST;
		$BX_MODULE_EVENT_LAST = $arEvent;

		if(is_array($arEvent["TO_METHOD_ARG"]) && count($arEvent["TO_METHOD_ARG"]))
			$args = array_merge($arEvent["TO_METHOD_ARG"], $arParams);
		else
			$args = $arParams;

		//php bug: http://bugs.php.net/bug.php?id=47948
		if (class_exists($arEvent["TO_CLASS"]) && is_callable([$arEvent["TO_CLASS"], $arEvent["TO_METHOD"]]))
		{
			return call_user_func_array([$arEvent["TO_CLASS"], $arEvent["TO_METHOD"]], $args);
		}

		$exception = new SystemException("Event handler error: could not invoke {$arEvent["TO_CLASS"]}::{$arEvent["TO_METHOD"]}. Class or method does not exist.");
		$application = \Bitrix\Main\Application::getInstance();
		$exceptionHandler = $application->getExceptionHandler();
		$exceptionHandler->writeToLog($exception);

		return null;
	}
	else
	{
		return $r;
	}
}

function UnRegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $TO_PATH="", $TO_METHOD_ARG = array())
{
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->unRegisterEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS, $TO_METHOD, $TO_PATH, $TO_METHOD_ARG);
}

function RegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $SORT=100, $TO_PATH="", $TO_METHOD_ARG = array())
{
	$eventManager = \Bitrix\Main\EventManager::getInstance();
	$eventManager->registerEventHandlerCompatible($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS, $TO_METHOD, $SORT, $TO_PATH, $TO_METHOD_ARG);
}

function IsModuleInstalled($module_id)
{
	return \Bitrix\Main\ModuleManager::isModuleInstalled($module_id);
}

function GetModuleID($str)
{
	$arr = explode("/",$str);
	$i = array_search("modules",$arr);
	return $arr[$i+1];
}

/**
 * Returns TRUE if version1 >= version2
 * version1 = "XX.XX.XX"
 * version2 = "XX.XX.XX"
 */
function CheckVersion($version1, $version2)
{
	$arr1 = explode(".",$version1);
	$arr2 = explode(".",$version2);
	if (intval($arr2[0])>intval($arr1[0])) return false;
	elseif (intval($arr2[0])<intval($arr1[0])) return true;
	else
	{
		if (intval($arr2[1])>intval($arr1[1])) return false;
		elseif (intval($arr2[1])<intval($arr1[1])) return true;
		else
		{
			if (intval($arr2[2])>intval($arr1[2])) return false;
			elseif (intval($arr2[2])<intval($arr1[2])) return true;
			else return true;
		}
	}
}
