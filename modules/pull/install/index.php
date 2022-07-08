<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("pull")) return;

class pull extends CModule
{
	var $MODULE_ID = "pull";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = PULL_VERSION;
			$this->MODULE_VERSION_DATE = PULL_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("PULL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("PULL_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();
		$GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage("PULL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/step1.php");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;

		$this->errors = false;
		if(!$DB->Query("SELECT 'x' FROM b_pull_stack", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/pull/install/db/mysql/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("pull");
		RegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", 50, "/modules/pull/ajax_hit_before.php");
		RegisterModuleDependences("main", "OnProlog", "main", "", "", 3, "/modules/pull/ajax_hit.php");
		RegisterModuleDependences("main", "OnProlog", "pull", "CPullOptions", "OnProlog");
		RegisterModuleDependences("main", "OnEpilog", "pull", "CPullOptions", "OnEpilog");
		RegisterModuleDependences("main", "OnAfterEpilog", "pull", "\Bitrix\Pull\Event", "onAfterEpilog");
		RegisterModuleDependences("main", "OnAfterEpilog", "pull", "CPullWatch", "DeferredSql");

		RegisterModuleDependences("perfmon", "OnGetTableSchema", "pull", "CPullTableSchema", "OnGetTableSchema");
		RegisterModuleDependences("main", "OnAfterRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		RegisterModuleDependences("main", "OnAfterUnRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		RegisterModuleDependences("socialnetwork", "OnSonetLogCounterClear", "pull", "\Bitrix\Pull\MobileCounter", "onSonetLogCounterClear");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'pull', '\Bitrix\Pull\Rest', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'onRestCheckAuth', 'pull', '\Bitrix\Pull\Rest\GuestAuth', 'onRestCheckAuth');

		CAgent::AddAgent("CPullOptions::ClearAgent();", "pull", "N", 30, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+30, "FULL"));

		return true;
	}

	function InstallFiles()
	{
		if($_ENV['COMPUTERNAME']!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function InstallEvents(){ return true; }

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = intval($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("PULL_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/pull/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array("savedata" => $_REQUEST["savedata"]));
			$this->UnInstallFiles();
			$APPLICATION->IncludeAdminFile(GetMessage("PULL_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/pull/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/pull/install/db/mysql/uninstall.sql");

		$arSQLErrors = Array();
		if(is_array($this->errors))
			$arSQLErrors = array_merge($arSQLErrors, $this->errors);

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		UnRegisterModuleDependences("main", "OnAfterRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		UnRegisterModuleDependences("main", "OnAfterUnRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "pull", "CPullTableSchema", "OnGetTableSchema");
		UnRegisterModuleDependences("main", "OnProlog", "main", "", "", "/modules/pull/ajax_hit.php");
		UnRegisterModuleDependences("main", "OnProlog", "pull", "CPullOptions", "OnProlog");
		UnRegisterModuleDependences("main", "OnEpilog", "pull", "CPullOptions", "OnEpilog");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "pull", "\Bitrix\Pull\Event", "onAfterEpilog");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "pull", "CPullWatch", "DeferredSql");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", "/modules/pull/ajax_hit_before.php");
		UnRegisterModuleDependences("socialnetwork", "OnSonetLogCounterClear", "pull", "\Bitrix\Pull\MobileCounter", "onSonetLogCounterClear");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'pull', '\Bitrix\Pull\Rest', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'onRestCheckAuth', 'pull', '\Bitrix\Pull\Rest\GuestAuth', 'onRestCheckAuth');

		UnRegisterModule("pull");

		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		return true;
	}

	function UnInstallEvents(){ return true; }

	public function migrateToBox()
	{
		COption::RemoveOption("pull");
	}

}
