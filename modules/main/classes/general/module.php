<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleTable;

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

	public static function AddAutoloadClasses($module, $arParams = [])
	{
		if ($module === '')
		{
			$module = null;
		}

		Loader::registerAutoLoadClasses($module, $arParams);
		return true;
	}

	public static function _GetCache()
	{
		return ModuleManager::getInstalledModules();
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

	public function InstallEventMessages(string $languageId, array $siteId): void
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
		return [
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
		];
	}

	public function InstallTasks()
	{
		CTask::AddFromArray($this->MODULE_ID, $this->GetModuleTasks());
	}

	public function UnInstallTasks()
	{
		$r = \Bitrix\Main\TaskTable::getList([
			'select' => ['ID'],
			'filter' => ['=MODULE_ID' => $this->MODULE_ID],
		]);

		$arIds = [];
		while ($arR = $r->fetch())
		{
			$arIds[] = $arR['ID'];
		}

		if (!empty($arIds))
		{
			\Bitrix\Main\GroupTaskTable::deleteByFilter(['=TASK_ID' => $arIds]);
			\Bitrix\Main\TaskOperationTable::deleteByFilter(['=TASK_ID' => $arIds]);
			\Bitrix\Main\TaskTable::deleteByFilter(['=MODULE_ID' => $this->MODULE_ID]);
		}
		\Bitrix\Main\OperationTable::deleteByFilter(['=MODULE_ID' => $this->MODULE_ID]);
	}

	function IsInstalled()
	{
		return ModuleManager::isModuleInstalled($this->MODULE_ID);
	}

	function DoUninstall()
	{
	}

	function Remove()
	{
		ModuleManager::delete($this->MODULE_ID);
	}

	function Add()
	{
		ModuleManager::add($this->MODULE_ID);
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
		return Loader::includeModule($module_name);
	}

	public static function IncludeModuleEx($module_name)
	{
		return Loader::includeSharewareModule($module_name);
	}

	public static function GetDropDownList()
	{
		return ModuleTable::getList([
			'select' => ['REFERENCE_ID' => 'ID', 'REFERENCE' => 'ID'],
			'order' => ['ID' => 'ASC'],
			'cache' => ['ttl' => 86400],
		]);
	}

	/**
	 * @param string $moduleId
	 * @return CModule|bool
	 */
	public static function CreateModuleObject($moduleId)
	{
		if (!ModuleManager::isValidModule($moduleId))
		{
			return false;
		}

		$path = getLocalPath("modules/" . $moduleId . "/install/index.php");
		if ($path === false)
		{
			return false;
		}

		include_once($_SERVER["DOCUMENT_ROOT"] . $path);

		$className = str_replace(".", "_", $moduleId);
		if (!class_exists($className))
		{
			return false;
		}

		return new $className;
	}
}
