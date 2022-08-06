<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = mb_substr($strPath2Lang, 0, mb_strlen($strPath2Lang) - mb_strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class bitrix_sitecorporate extends CModule
{
	var $MODULE_ID = "bitrix.sitecorporate";
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

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SCOM_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("SPER_PARTNER");
		$this->PARTNER_URI = GetMessage("PARTNER_URI");
	}


	function InstallDB($install_wizard = true)
	{
		RegisterModule("bitrix.sitecorporate");
		RegisterModuleDependences("main", "OnBeforeProlog", "bitrix.sitecorporate", "CSiteCorporate", "ShowPanel");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		UnRegisterModuleDependences("main", "OnBeforeProlog", "bitrix.sitecorporate", "CSiteCorporate", "ShowPanel");
		UnRegisterModule("bitrix.sitecorporate");

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
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.sitecorporate/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
	
		return true;
	}

	function InstallPublic()
	{
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		$APPLICATION->IncludeAdminFile(GetMessage("SCOM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.sitecorporate/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage("SCOM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.sitecorporate/install/unstep.php");
	}
}
?>