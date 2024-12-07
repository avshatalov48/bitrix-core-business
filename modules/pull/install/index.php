<?php

if (class_exists("pull"))
{
	return;
}

use Bitrix\Main\Localization\Loc;

class pull extends \CModule
{
	public $MODULE_ID = 'pull';
	public $MODULE_GROUP_RIGHTS = 'Y';

	private $errors = [];

	public function __construct()
	{
		$arModuleVersion = [];

		include (__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('PULL_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('PULL_MODULE_DESCRIPTION');
	}

	public function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$APPLICATION->IncludeAdminFile(Loc::getMessage('PULL_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pull/install/step1.php');
	}

	public function InstallDB()
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$connection->isTableExists('b_pull_stack'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/pull/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('', $this->errors));
			return false;
		}

		\Bitrix\Main\ModuleManager::registerModule("pull");

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->registerEventHandlerCompatible("main", "OnBeforeProlog", "main", "", "", 50, "/modules/pull/ajax_hit_before.php");
		$eventManager->registerEventHandlerCompatible("main", "OnProlog", "main", "", "", 3, "/modules/pull/ajax_hit.php");
		$eventManager->registerEventHandlerCompatible("main", "OnProlog", "pull", "CPullOptions", "OnProlog");
		$eventManager->registerEventHandlerCompatible("main", "OnEpilog", "pull", "CPullOptions", "OnEpilog");
		$eventManager->registerEventHandlerCompatible("main", "OnAfterEpilog", "pull", "\Bitrix\Pull\Event", "onAfterEpilog");
		$eventManager->registerEventHandlerCompatible("main", "OnAfterEpilog", "pull", "CPullWatch", "DeferredSql");

		$eventManager->registerEventHandlerCompatible("perfmon", "OnGetTableSchema", "pull", "CPullTableSchema", "OnGetTableSchema");
		$eventManager->registerEventHandlerCompatible("main", "OnAfterRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		$eventManager->registerEventHandlerCompatible("main", "OnAfterUnRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		$eventManager->registerEventHandlerCompatible("socialnetwork", "OnSonetLogCounterClear", "pull", "\Bitrix\Pull\MobileCounter", "onSonetLogCounterClear");

		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'pull', '\Bitrix\Pull\Rest', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'onRestCheckAuth', 'pull', '\Bitrix\Pull\Rest\GuestAuth', 'onRestCheckAuth');

		\CAgent::AddAgent("CPullOptions::ClearAgent();", "pull", "N", 30, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+30, "FULL"));

		return true;
	}

	public function InstallFiles()
	{
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);

		return true;
	}

	public function DoUninstall()
	{
		global $APPLICATION;

		$step = (int)($_REQUEST['step'] ?? 1);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("PULL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/unstep1.php");
		}
		elseif ($step == 2)
		{
			$this->UnInstallDB(["savedata" => $_REQUEST["savedata"]]);
			$this->UnInstallFiles();
			$APPLICATION->IncludeAdminFile(Loc::getMessage("PULL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/pull/install/unstep2.php");
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $APPLICATION, $DB;

		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/pull/install/db/".$connection->getType()."/uninstall.sql");
		}

		$arSQLErrors = [];
		if (is_array($this->errors))
		{
			$arSQLErrors = array_merge($arSQLErrors, $this->errors);
		}

		if (!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler("main", "OnAfterRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		$eventManager->unRegisterEventHandler("main", "OnAfterUnRegisterModule", "pull", "CPullOptions", "ClearCheckCache");
		$eventManager->unRegisterEventHandler("perfmon", "OnGetTableSchema", "pull", "CPullTableSchema", "OnGetTableSchema");
		$eventManager->unRegisterEventHandler("main", "OnProlog", "main", "", "", "/modules/pull/ajax_hit.php");
		$eventManager->unRegisterEventHandler("main", "OnProlog", "pull", "CPullOptions", "OnProlog");
		$eventManager->unRegisterEventHandler("main", "OnEpilog", "pull", "CPullOptions", "OnEpilog");
		$eventManager->unRegisterEventHandler("main", "OnAfterEpilog", "pull", "\Bitrix\Pull\Event", "onAfterEpilog");
		$eventManager->unRegisterEventHandler("main", "OnAfterEpilog", "pull", "CPullWatch", "DeferredSql");
		$eventManager->unRegisterEventHandler("main", "OnBeforeProlog", "main", "", "", "/modules/pull/ajax_hit_before.php");
		$eventManager->unRegisterEventHandler("socialnetwork", "OnSonetLogCounterClear", "pull", "\Bitrix\Pull\MobileCounter", "onSonetLogCounterClear");

		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'pull', '\Bitrix\Pull\Rest', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'onRestCheckAuth', 'pull', '\Bitrix\Pull\Rest\GuestAuth', 'onRestCheckAuth');

		\Bitrix\Main\ModuleManager::unRegisterModule('pull');

		return true;
	}
}
