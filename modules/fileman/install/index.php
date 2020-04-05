<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Class fileman extends CModule
{
	var $MODULE_ID = "fileman";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

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
			$this->MODULE_VERSION = FILEMAN_VERSION;
			$this->MODULE_VERSION_DATE = FILEMAN_VERSION_DATE;
		}

		$this->MODULE_NAME = Loc::getMessage("FILEMAN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("FILEMAN_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		if (!$DB->Query("SELECT 'x' FROM b_medialib_collection", true))
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/db/".$DBType."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("fileman");
		RegisterModuleDependences("main", "OnGroupDelete", "fileman", "CFileman", "OnGroupDelete");
		RegisterModuleDependences("main", "OnPanelCreate", "fileman", "CFileman", "OnPanelCreate");
		RegisterModuleDependences("main", "OnModuleUpdate", "fileman", "CFileman", "OnModuleUpdate");
		RegisterModuleDependences("main", "OnModuleInstalled", "fileman", "CFileman", "ClearComponentsListCache");

		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyMapGoogle', 'GetUserTypeDescription');
		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyMapYandex', 'GetUserTypeDescription');
		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyVideo', 'GetUserTypeDescription');
		RegisterModuleDependences("main", "OnUserTypeBuildList", "fileman", "CUserTypeVideo", "GetUserTypeDescription");
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "fileman", "CEventFileman", "GetAuditTypes");
		RegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "fileman", "CEventFileman", "MakeFilemanObject");
		RegisterModuleDependences("main", "OnUserTypeBuildList", "fileman", "\\Bitrix\\Fileman\\UserField\\Address", "getUserTypeDescription", 154);

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tasks/install.php");

		COption::SetOptionString('fileman', "use_editor_3", "Y");
		// // Add hotkeys
		// $hkc = new CHotKeysCode;
		// $id = $hkc->Add(array(
			// CLASS_NAME => "admin_file_edit_apply",
			// CODE => "if(top.AjaxApply && typeof top.AjaxApply == 'function'){top.AjaxApply();}",
			// NAME => Loc::getMessage("FILEMAN_HOTKEY_TITLE"),
			// IS_CUSTOM => "0"
		// ));
		// CHotKeys::getInstance()->Add(array("KEYS_STRING"=>"Ctrl+83", "CODE_ID"=>$id, "USER_ID" => 0)); //S

		return true;
	}

	function UnInstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		//if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		//{
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/db/".$DBType."/uninstall.sql");
		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}
		//}


		UnRegisterModuleDependences("main", "OnGroupDelete", "fileman", "CFileman", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnPanelCreate", "fileman", "CFileman", "OnPanelCreate");
		UnRegisterModuleDependences("main", "OnModuleUpdate", "fileman", "CFileman", "OnModuleUpdate");
		UnRegisterModuleDependences("main", "OnModuleInstalled", "fileman", "CFileman", "ClearComponentsListCache");
		UnRegisterModule("fileman");

		UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyMapGoogle', 'GetUserTypeDescription');
		UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyMapYandex', 'GetUserTypeDescription');
		UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'fileman', 'CIBlockPropertyVideo', 'GetUserTypeDescription');
		UnRegisterModuleDependences("main", "OnUserTypeBuildList", "fileman", "CUserTypeVideo", "GetUserTypeDescription");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "fileman", "CEventFileman", "GetAuditTypes");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "fileman", "CEventFileman", "MakeFilemanObject");
		UnRegisterModuleDependences("main", "OnUserTypeBuildList", "fileman", "\\Bitrix\\Fileman\\UserField\\Address", "getUserTypeDescription");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tasks/uninstall.php");

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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/images/1.gif", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		}

		if(\Bitrix\Main\Loader::includeModule('fileman'))
		{
			\CFileMan::decodePdfViewerLangFiles();
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/fileman/");//icons
		DeleteDirFilesEx("/bitrix/images/fileman/");//images
		DeleteDirFilesEx("/bitrix/js/fileman"); // JS
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools"); // tools
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");

		if ($FM_RIGHT!="D")
		{
			$this->InstallDB();
			$this->InstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage("FILEMAN_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/step1.php");
		}
	}
	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		if ($FM_RIGHT!="D")
		{
			$this->UnInstallDB();
			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage("FILEMAN_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/unstep1.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","F","R"),
			"reference" => array(
				"[D] ".Loc::getMessage("FILEMAN_DENIED"),
				"[F] ".Loc::getMessage("FILEMAN_ACCESSABLE_FOLDERS"),
				"[R] ".Loc::getMessage("FILEMAN_VIEW"))
			);
		return $arr;
	}
}
?>