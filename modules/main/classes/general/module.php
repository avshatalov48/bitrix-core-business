<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

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

