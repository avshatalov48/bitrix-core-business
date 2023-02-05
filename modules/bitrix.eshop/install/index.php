<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


Class bitrix_eshop extends CModule
{
	var $MODULE_ID = "bitrix.eshop";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("SCOM_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("SPER_PARTNER");
		$this->PARTNER_URI = Loc::getMessage("PARTNER_URI");
	}


	function InstallDB($install_wizard = true)
	{
		RegisterModule("bitrix.eshop");
		RegisterModuleDependences("main", "OnBeforeProlog", "bitrix.eshop", "CEShop", "ShowPanel");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		UnRegisterModule("bitrix.eshop");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "bitrix.eshop", "CEShop", "ShowPanel");

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
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.eshop/install/wizards/bitrix/eshop", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/eshop", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.eshop/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.eshop/install/wizards/bitrix/eshop.mobile", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/eshop.mobile", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix.eshop/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/bitrix.eshop", true, true);

		return true;
	}

	function InstallPublic()
	{
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/wizards/bitrix/eshop");
		DeleteDirFilesEx("/bitrix/images/bitrix.eshop/");//images

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();
		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		return true;
	}
}
?>