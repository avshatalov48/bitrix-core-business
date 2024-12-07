<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Class bizproc extends CModule
{
	var $MODULE_ID = "bizproc";
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

		$this->MODULE_NAME = Loc::getMessage("BIZPROC_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("BIZPROC_INSTALL_DESCRIPTION");
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$errors = null;

		if (!$DB->TableExists('b_bp_workflow_instance'))
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizproc/install/db/' . $connection->getType() . '/install.sql');
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("bizproc");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", "bizproc", "CBPVirtualDocument", "OnAfterIBlockElementDelete");
		RegisterModuleDependences("main", "OnAdminInformerInsertItems", "bizproc", "CBPAllTaskService", "OnAdminInformerInsertItems");
		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestServiceBuildDescription');
		RegisterModuleDependences('rest', 'OnRestAppDelete', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestAppDelete');
		RegisterModuleDependences('rest', 'OnRestAppUpdate', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestAppUpdate');
		RegisterModuleDependences('timeman', 'OnAfterTMDayStart', 'bizproc', 'CBPDocument', 'onAfterTMDayStart');

		COption::SetOptionString("bizproc", "SkipNonPublicCustomTypes", "Y");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationImport', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventImportController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationExport', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventExportController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationClear', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventClearController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationEntity', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'getEntityList');
		$eventManager->registerEventHandlerCompatible('im', 'OnGetNotifySchema', 'bizproc', Bitrix\Bizproc\Integration\NotifySchema::class, 'onGetNotifySchema');

		//Comments
		$commentsListener = \Bitrix\Bizproc\Integration\CommentListener::class;
		$eventManager->registerEventHandler('forum', 'OnAfterCommentAdd', 'bizproc', $commentsListener, 'onAfterCommentAdd');
		//$eventManager->registerEventHandler('forum', 'OnAfterCommentUpdate', 'bizproc', $commentsListener, 'onAfterCommentUpdate');
		$eventManager->registerEventHandler('forum', 'OnCommentDelete', 'bizproc', $commentsListener, 'onCommentDelete');
		$eventManager->registerEventHandler('socialnetwork', 'onContentViewed', 'bizproc', $commentsListener, 'onSocnetContentViewed');

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$errors = null;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/db/".$connection->getType()."/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", "bizproc", "CBPVirtualDocument", "OnAfterIBlockElementDelete");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "bizproc", "CBPAllTaskService", "OnAdminInformerInsertItems");
		UnRegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestServiceBuildDescription');
		UnRegisterModuleDependences('rest', 'OnRestAppDelete', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestAppDelete');
		UnRegisterModuleDependences('rest', 'OnRestAppUpdate', 'bizproc', '\Bitrix\Bizproc\RestService', 'onRestAppUpdate');
		UnRegisterModuleDependences('timeman', 'OnAfterTMDayStart', 'bizproc', 'CBPDocument', 'onAfterTMDayStart');
		UnRegisterModule("bizproc");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationImport', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventImportController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationExport', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventExportController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationClear', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'onEventClearController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationEntity', 'bizproc', '\Bitrix\Bizproc\Integration\Rest\AppConfiguration', 'getEntityList');
		$eventManager->unRegisterEventHandler('im', 'OnGetNotifySchema', 'bizproc', Bitrix\Bizproc\Integration\NotifySchema::class, 'onGetNotifySchema');

		//Comments
		$commentsListener = \Bitrix\Bizproc\Integration\CommentListener::class;
		$eventManager->unRegisterEventHandler('forum', 'OnAfterCommentAdd', 'bizproc', $commentsListener, 'onAfterCommentAdd');
		//$eventManager->unRegisterEventHandler('forum', 'OnAfterCommentUpdate', 'bizproc', $commentsListener, 'onAfterCommentUpdate');
		$eventManager->unRegisterEventHandler('forum', 'OnCommentDelete', 'bizproc', $commentsListener, 'onCommentDelete');
		$eventManager->unRegisterEventHandler('socialnetwork', 'onContentViewed', 'bizproc', $commentsListener, 'onSocnetContentViewed');

		return true;
	}

	function InstallEvents()
	{
		global $DB;

		$dbResult = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME = 'BIZPROC_MAIL_TEMPLATE' ");
		$arResult = $dbResult->Fetch();
		if ($arResult["C"] <= 0)
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/events/set_events.php");

		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/events/del_events.php");
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/activities", $_SERVER["DOCUMENT_ROOT"]."/bitrix/activities", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", false, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/templates/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/bizproc", true, True);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/tools",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, True);
		return true;
	}

	function InstallPublic()
	{
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
		DeleteDirFilesEx("/bitrix/images/bizproc/");
		DeleteDirFilesEx("/bitrix/js/bizproc/");

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->errors = null;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(Loc::getMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/step2.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->errors = array();

		$step = intval($step);
		if($step<2)
		{
			if (IsModuleInstalled("bizprocdesigner"))
				$this->errors[] = Loc::getMessage("BIZPROC_BIZPROCDESIGNER_INSTALLED");

			$GLOBALS["bizproc_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB([
				"savedata" => isset($_REQUEST["savedata"]) ? $_REQUEST["savedata"] : null,
			]);
			$this->UnInstallFiles();

			$this->UnInstallEvents();

			$GLOBALS["bizproc_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", "R", "W"),
			"reference" => array(
					"[D] ".Loc::getMessage("BIZPROC_PERM_D"),
					"[R] ".Loc::getMessage("BIZPROC_PERM_R"),
					"[W] ".Loc::getMessage("BIZPROC_PERM_W")
				)
			);
		return $arr;
	}
}
