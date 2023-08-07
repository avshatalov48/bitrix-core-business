<?php
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if(class_exists("webservice"))
{
	return;
}

class webservice extends \CModule
{
	var $MODULE_ID = "webservice";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "N";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("WEBS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("WEBS_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		
		$this->InstallFiles();
		$this->InstallDB();
		
		$APPLICATION->IncludeAdminFile(GetMessage("WEBS_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/webservice/install/step.php");
	}

	function InstallFiles()
	{
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/webservice/install/components",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/components",
			true, true
		);

		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/webservice/install/tools",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/tools",
			true, true
		);

		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/webservice/install/js",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js",
			true, true
		);

		return true;
	}
	
	function InstallDB()
	{
		RegisterModule("webservice");
		
		return true;
	}
	
	function InstallEvents()
	{
		return true;
	}
	
	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		
		$this->UnInstallFiles();
		$this->UnInstallDB();
		
		$APPLICATION->IncludeAdminFile(GetMessage("WEBS_UNINSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/webservice/install/unstep.php");
	}
	
	function UnInstallDB()
	{
		UnRegisterModule("webservice");

		return true;
	}
	
	function UnInstallFiles()
	{
		return true;
	}
	
	function UnInstallEvents()
	{
		return true;
	}
}
?>