<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Class compression extends CModule
{
	var $MODULE_ID = "compression";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function __construct()
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
		else
		{
			$this->MODULE_VERSION = COMPRESSION_VERSION;
			$this->MODULE_VERSION_DATE = COMPRESSION_VERSION_DATE;
		}

		$this->MODULE_NAME = Loc::getMessage("COMPRESSION_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("COMPRESSION_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		RegisterModule("compression");
		RegisterModuleDependences("main", "OnPageStart", "compression", "CCompress", "OnPageStart", 1);
		RegisterModuleDependences("main", "OnAfterEpilog", "compression", "CCompress", "OnAfterEpilog", 10000);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences("main", "OnPageStart", "compression", "CCompress", "OnPageStart");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "compression", "CCompress", "OnAfterEpilog");
		UnRegisterModule("compression");

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

	function InstallFiles($arParams = array())
	{
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallDB();
		$APPLICATION->IncludeAdminFile(Loc::getMessage("COMPRESS_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/compression/install/step.php");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallDB();
		$APPLICATION->IncludeAdminFile(Loc::getMessage("COMPRESS_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/compression/install/unstep.php");
	}
}
?>