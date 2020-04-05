<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class mobileapp extends CModule
{
	var $MODULE_ID = "mobileapp";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $errors;

	function mobileapp()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage('APP_PLATFORM_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('APP_PLATFORM_MODULE_DESCRIPTION');
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		
		if (!$DB->Query("SELECT 'x' FROM b_mobileapp_app", true))
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/mobileapp/install/db/" . strtolower($DB->type) . "/install.sql");
		$APPLICATION->ResetException();
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));

			return false;
		}

		RegisterModule("mobileapp");
		RegisterModuleDependences("pull", "OnGetDependentModule", "mobileapp", "CMobileAppPullSchema", "OnGetDependentModule");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB;
		$DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/mobileapp/install/db/" . strtolower($DB->type) . "/uninstall.sql");
		UnRegisterModuleDependences("pull", "OnGetDependentModule", "mobileapp", "CMobileAppPullSchema", "OnGetDependentModule");
		UnRegisterModule("mobileapp");
		return true;
	}

	function InstallFiles()
	{
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/",
				true, true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/",
				true, true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/images/",
				true, true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/components/",
				true, true
			);

			return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION;
		if(!$USER->IsAdmin())
			return;

		$this->InstallDB();
		$this->InstallFiles();

		$APPLICATION->IncludeAdminFile(GetMessage("APP_PLATFORM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/step.php");
	}

	function DoUninstall()
	{
		global $USER, $DB, $APPLICATION, $step;
		if($USER->IsAdmin())
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("APP_PLATFORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB();
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("APP_PLATFORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/unstep.php");
			}
		}
	}
}
?>