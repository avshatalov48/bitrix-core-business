<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

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

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage('APP_PLATFORM_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('APP_PLATFORM_MODULE_DESCRIPTION');
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		
		if (!$DB->Query("SELECT 'x' FROM b_mobileapp_app", true))
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/db/".mb_strtolower($DB->type) . "/install.sql");
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
		$DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/db/".mb_strtolower($DB->type) . "/uninstall.sql");
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

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/services/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/services/",
				true, true
			);

		$siteId = \CSite::GetDefSite();
		if($siteId)
		{
			\Bitrix\Main\UrlRewriter::add($siteId, [
				"CONDITION" => "#^\/?\/mobileapp/jn\/(.*)\/.*#",
				"RULE" => "componentName=$1",
				"PATH" => "/bitrix/services/mobileapp/jn.php",
			]);
		}


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

		$APPLICATION->IncludeAdminFile(Loc::getMessage("APP_PLATFORM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/step.php");
	}

	function DoUninstall()
	{
		global $USER, $DB, $APPLICATION, $step;
		if($USER->IsAdmin())
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(Loc::getMessage("APP_PLATFORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB();
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(Loc::getMessage("APP_PLATFORM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp/install/unstep.php");
			}
		}
	}
}
?>