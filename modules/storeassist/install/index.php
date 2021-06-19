<?
IncludeModuleLangFile(__FILE__);

Class storeassist extends CModule
{
	var $MODULE_ID = "storeassist";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
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

		$this->MODULE_NAME = GetMessage('STOREASSIST_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('STOREASSIST_MODULE_DESCRIPTION');
	}

	function DoInstall()
	{
		global $USER, $APPLICATION;
		if(!$USER->IsAdmin())
			return;

		$this->InstallDB();
		$this->InstallFiles();
		$this->InstallEvents();

		$APPLICATION->IncludeAdminFile(GetMessage("STOREASSIST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/step.php");
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION;
		if($USER->IsAdmin())
		{
			$this->UnInstallEvents();
			$this->UnInstallFiles();
			$this->UnInstallDB();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("STOREASSIST_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/unstep.php");
		}
	}

	function InstallDB()
	{
		RegisterModule("storeassist");
		RegisterModuleDependences("main", "OnPrologAdminTitle", "storeassist", "CStoreAssist", "onPrologAdminTitle");
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', "storeassist", "CStoreAssist", "onBuildGlobalMenu");

		$dateCheckTs = MakeTimeStamp(date("d", time()+3600*24).".".date("m", time()+3600*24).".".date("Y", time()+3600*24)." 00:00:00", "DD.MM.YYYY HH:MI:SS");
		$dateCheck = ConvertTimeStamp($dateCheckTs, "FULL");
		CAgent::AddAgent("CStoreAssist::AgentCountDayOrders();", "storeassist", "N", 86400, $dateCheck, "Y", $dateCheck);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences("main", "OnPrologAdminTitle", "storeassist", "CStoreAssist", "onPrologAdminTitle");
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', "storeassist", "CStoreAssist", "onBuildGlobalMenu");
		CAgent::RemoveModuleAgents("storeassist");

		UnRegisterModule("storeassist");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/js/storeassist/");//javascript
		DeleteDirFilesEx("/bitrix/panel/storeassist/");//javascript
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");

		return true;
	}
}
?>