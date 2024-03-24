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

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("FILEMAN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("FILEMAN_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;

		$connection = \Bitrix\Main\Application::getConnection();
		$errors = null;

		if (!$DB->TableExists('b_medialib_collection'))
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/fileman/install/db/' . $connection->getType() . '/install.sql');
		}

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

		$this->InstallTasks();

		// probably deprecated
		$DB->Query("
			INSERT INTO b_group_task (GROUP_ID,TASK_ID)
			SELECT MG.GROUP_ID, T.ID
			FROM b_task T 
				INNER JOIN b_module_group MG ON MG.G_ACCESS = T.LETTER
			WHERE T.SYS = 'Y'
				AND T.BINDING = 'module'
				AND MG.MODULE_ID = 'fileman'
				AND T.MODULE_ID = MG.MODULE_ID
		");

		COption::SetOptionString('fileman', "use_editor_3", "Y");

		return true;
	}

	function UnInstallDB()
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();

		$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/fileman/install/db/' . $connection->getType() . '/uninstall.sql');
		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

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

		$this->UnInstallTasks();

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
			CFileMan::decodePdfViewerLangFiles();
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
		/** @noinspection PhpUnusedLocalVariableInspection */
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
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DOCUMENT_ROOT, $APPLICATION, $step;

		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		if ($FM_RIGHT!="D")
		{
			$this->UnInstallDB();
			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage("FILEMAN_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/unstep1.php");
		}
	}

	public function GetModuleTasks()
	{
		return [
			//FILEMAN: module
			'fileman_denied' => [
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => [],
			],
			'fileman_allowed_folders' => [
				'LETTER' => 'F',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'fileman_view_file_structure',
					'fileman_add_element_to_menu',
					'fileman_edit_menu_elements',
					'fileman_edit_existent_files',
					'fileman_edit_existent_folders',
					'fileman_admin_files',
					'fileman_admin_folders',
					'fileman_view_permissions',
					'fileman_upload_files',
				],
			],
			'fileman_full_access' => [
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'fileman_view_file_structure',
					'fileman_view_all_settings',
					'fileman_edit_menu_types',
					'fileman_add_element_to_menu',
					'fileman_edit_menu_elements',
					'fileman_edit_existent_files',
					'fileman_edit_existent_folders',
					'fileman_admin_files',
					'fileman_admin_folders',
					'fileman_view_permissions',
					'fileman_edit_all_settings',
					'fileman_upload_files',
					'fileman_install_control',
				],
			],

			// MEDIALIBRARY OPERATIONS IN TASKS
			'medialib_denied' => [
				'LETTER' => 'D',
				'BINDING' => 'medialib',
				'OPERATIONS' => [],
			],
			'medialib_view' => [
				'LETTER' => 'F',
				'BINDING' => 'medialib',
				'OPERATIONS' => [
					'medialib_view_collection',
				],
			],
			'medialib_only_new' => [
				'LETTER' => 'R',
				'BINDING' => 'medialib',
				'OPERATIONS' => [
					'medialib_view_collection',
					'medialib_new_collection',
					'medialib_new_item',
				]
			],
			'medialib_edit_items' => [
				'LETTER' => 'V',
				'BINDING' => 'medialib',
				'OPERATIONS' => [
					'medialib_view_collection',
					'medialib_new_item',
					'medialib_edit_item',
					'medialib_del_item',
				],
			],
			'medialib_editor' => [
				'LETTER' => 'W',
				'BINDING' => 'medialib',
				'OPERATIONS' => [
					'medialib_view_collection',
					'medialib_new_collection',
					'medialib_edit_collection',
					'medialib_del_collection',
					'medialib_new_item',
					'medialib_edit_item',
					'medialib_del_item',
				],
			],
			'medialib_full' => [
				'LETTER' => 'X',
				'BINDING' => 'medialib',
				'OPERATIONS' => [
					'medialib_view_collection',
					'medialib_new_collection',
					'medialib_edit_collection',
					'medialib_del_collection',
					'medialib_access',
					'medialib_new_item',
					'medialib_edit_item',
					'medialib_del_item',
				],
			],

			// STICKERS OPERATIONS IN TASKS
			'stickers_denied' => [
				'LETTER' => 'D',
				'BINDING' => 'stickers',
				'OPERATIONS' => [],
			],
			'stickers_read' => [
				'LETTER' => 'R',
				'BINDING' => 'stickers',
				'OPERATIONS' => [
					'sticker_view',
				],
			],
			'stickers_edit' => [
				'LETTER' => 'W',
				'BINDING' => 'stickers',
				'OPERATIONS' => [
					'sticker_view',
					'sticker_edit',
					'sticker_new',
					'sticker_del',
				],
			],
		];
	}
}
