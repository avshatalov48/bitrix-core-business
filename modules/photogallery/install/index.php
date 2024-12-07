<?php

if (class_exists("photogallery"))
{
	return;
}

IncludeModuleLangFile(__FILE__);

class photogallery extends CModule
{
	var $MODULE_ID = "photogallery";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("P_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("P_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		RegisterModule("photogallery");
		RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "photogallery", "CPhotogalleryElement", "OnBeforeIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "photogallery", "CPhotogalleryElement", "OnAfterIBlockElementAdd");
		RegisterModuleDependences("search", "BeforeIndex", "photogallery", "CRatingsComponentsPhotogallery", "BeforeIndex");
		RegisterModuleDependences("im", "OnGetNotifySchema", "photogallery", "CPhotogalleryNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "photogallery", "\\Bitrix\\Photogallery\\Integration\\Socialnetwork\\Group", "onSocNetGroupDelete");
		return true;
	}

	function UnInstallDB()
	{
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "photogallery", "CPhotogalleryElement", "OnBeforeIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "photogallery", "CPhotogalleryElement", "OnAfterIBlockElementAdd");
		UnRegisterModuleDependences("search", "BeforeIndex", "photogallery", "CRatingsComponentsPhotogallery", "BeforeIndex");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "photogallery", "CPhotogalleryNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "photogallery", "\\Bitrix\\Photogallery\\Integration\\Socialnetwork\\Group", "onSocNetGroupDelete");
		UnRegisterModule("photogallery");
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;

		if (!check_bitrix_sessid())
		{
			return;
		}

		if (IsModuleInstalled("iblock"))
		{
			$step = intval($_REQUEST["step"] ?? null);

			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("PHOTO_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/step1.php");
			}
			elseif($step == 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("PHOTO_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/photogallery/install/step2.php");
			}
			elseif ($step == 3)
			{
				$this->InstallDB();
				$this->InstallFiles();
				LocalRedirect("module_admin.php?lang=".LANGUAGE_ID);
			}
		}
		elseif (!IsModuleInstalled("photogallery"))
		{
			$this->InstallDB();
			$this->InstallFiles();
		}
	}

	function DoUninstall()
	{
		if (!check_bitrix_sessid())
		{
			return;
		}

		$this->UnInstallDB();
	}
}
