<?php

if (class_exists("im"))
{
	return;
}

use Bitrix\Main\Localization\Loc;

class im extends \CModule
{
	public $MODULE_ID = 'im';
	public $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = [];

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("IM_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IM_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();

		$GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("IM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/step1.php");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();

		if (!$DB->TableExists('b_im_chat'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/im/install/db/' . $connection->getType() . '/install.sql');
		}

		if (!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		\Bitrix\Main\ModuleManager::registerModule("im");

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->registerEventHandlerCompatible('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		$eventManager->registerEventHandlerCompatible('main', 'OnChangeRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		$eventManager->registerEventHandlerCompatible('main', 'OnCancelRatingVote', 'im', 'CIMEvent', 'OnCancelRatingVote');
		$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserAdd', 'im', 'CIMEvent', 'OnAfterUserAdd');
		$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserUpdate', 'im', 'CIMEvent', 'OnAfterUserUpdate');
		$eventManager->registerEventHandlerCompatible('main', 'OnUserDelete', 'im', 'CIMEvent', 'OnUserDelete');
		$eventManager->registerEventHandlerCompatible("main", "OnBeforeUserSendPassword", "im", "CIMEvent", "OnBeforeUserSendPassword");
		$eventManager->registerEventHandlerCompatible("pull", "OnGetDependentModule", "im", "CIMEvent", "OnGetDependentModule");
		$eventManager->registerEventHandlerCompatible("main", "OnProlog", "main", "", "", 3, "/modules/im/ajax_hit.php");
		$eventManager->registerEventHandlerCompatible("perfmon", "OnGetTableSchema", "im", "im", "OnGetTableSchema");
		$eventManager->registerEventHandlerCompatible("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		$eventManager->registerEventHandlerCompatible("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		$eventManager->registerEventHandlerCompatible("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		$eventManager->registerEventHandlerCompatible("main", "OnApplicationsBuildList", "im", "DesktopApplication", "OnApplicationsBuildList");
		$eventManager->registerEventHandlerCompatible("main", "OnUserOnlineStatusGetCustomOnlineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		$eventManager->registerEventHandlerCompatible("main", "OnUserOnlineStatusGetCustomOfflineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		$eventManager->registerEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', 'im', 'CIMRestService', 'OnRestServiceBuildDescription');
		$eventManager->registerEventHandlerCompatible('rest', 'OnRestAppDelete', 'im', 'CIMRestService', 'OnRestAppDelete');
		$eventManager->registerEventHandlerCompatible('main', 'OnAuthProvidersBuildList', 'im', '\Bitrix\Im\Access\ChatAuthProvider', 'getProviders');
		$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserUpdate', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserUpdate');
		$eventManager->registerEventHandlerCompatible( 'main', 'OnAfterUserDelete', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserDelete');
		$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserAdd', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserAdd');

		\CAgent::AddAgent('CIMMail::MailNotifyAgent();', "im", "N", 600); /** @see \CIMMail::MailNotifyAgent */
		\CAgent::AddAgent('CIMMail::MailMessageAgent();', "im", "N", 600); /** @see \CIMMail::MailMessageAgent */
		\CAgent::AddAgent('CIMDisk::RemoveTmpFileAgent();', "im", "N", 43200); /** @see \CIMDisk::RemoveTmpFileAgent */
		\CAgent::AddAgent('Bitrix\Im\Notify::cleanNotifyAgent();', "im", "N", 7200); /** @see \Bitrix\Im\Notify::cleanNotifyAgent */
		\CAgent::AddAgent('Bitrix\Im\Bot::deleteExpiredTokenAgent();', "im", "N", 86400); /** @see \Bitrix\Im\Bot::deleteExpiredTokenAgent */
		\CAgent::AddAgent('Bitrix\Im\Disk\NoRelationPermission::cleaningAgent();', "im", "N", 3600); /** @see \Bitrix\Im\Disk\NoRelationPermission::cleaningAgent */
		\CAgent::AddAgent('Bitrix\Im\Call\Conference::removeTemporaryAliases();', "im", "N", 86400); /** @see \Bitrix\Im\Call\Conference::removeTemporaryAliases */
		\CAgent::AddAgent('Bitrix\Im\Message\Uuid::cleanOldRecords();', 'im', 'N', 86400); /** @see \Bitrix\Im\Message\Uuid::cleanOldRecords */
		\CAgent::AddAgent('Bitrix\Im\V2\Link\Reminder\ReminderService::remindAgent();', 'im', 'N', 60); /** @see \Bitrix\Im\V2\Link\Reminder\ReminderService::remindAgent */
		\CAgent::AddAgent('Bitrix\Im\V2\Link\File\TemporaryFileService::cleanAgent();', 'im', 'N', 3600); /** @see \Bitrix\Im\V2\Link\File\TemporaryFileService::cleanAgent */
		\CAgent::AddAgent('Bitrix\Im\Update\MessageDisappearing::disappearMessagesAgent();', 'im', 'N', 60); /** @see \Bitrix\Im\Update\MessageDisappearing::disappearMessagesAgent */
		\CAgent::AddAgent('\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncRelationAgent();', 'im', 'N', 300); /** @see \Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncRelationAgent() */
		\CAgent::AddAgent('\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncMemberAgent();', 'im', 'N', 300); /** @see \Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncMemberAgent() */
		\CAgent::AddAgent('\Bitrix\Im\V2\Recent\Initializer::executeAgent();', 'im', 'N', 300); /** @see \Bitrix\Im\V2\Recent\Initializer::executeAgent() */

		$eventManager->registerEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->registerEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');
		$eventManager->registerEventHandler('voximplant', 'onConferenceFinished', 'im', '\Bitrix\Im\Call\Call', 'onVoximplantConferenceFinished');

		$eventManager->registerEventHandler('rest', 'onRestCheckAuth', 'im', '\Bitrix\Im\Call\Auth', 'onRestCheckAuth');

		$eventManager->registerEventHandler('calendar', 'OnAfterCalendarEntryUpdate', 'im', '\Bitrix\Im\V2\Service\Messenger', 'updateCalendar');
		$eventManager->registerEventHandler('calendar', 'OnAfterCalendarEventDelete', 'im', '\Bitrix\Im\V2\Service\Messenger', 'unregisterCalendar');
		$eventManager->registerEventHandler('im', 'OnAfterMessagesAdd', 'im', '\Bitrix\Im\V2\Message\Delete\DisappearService', 'checkDisappearing');
		$eventManager->registerEventHandler('ai', 'onTuningLoad', 'im', '\Bitrix\Im\V2\Integration\AI\Restriction', 'onTuningLoad');
		$eventManager->registerEventHandler('humanresources', 'RELATION_ADDED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onRelationAdded');
		$eventManager->registerEventHandler('humanresources', 'RELATION_DELETED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onRelationDeleted');
		$eventManager->registerEventHandler('humanresources', 'MEMBER_ADDED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberAdded');
		$eventManager->registerEventHandler('humanresources', 'MEMBER_DELETED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberDeleted');
		$eventManager->registerEventHandler('intranet', 'onLicenseHasChanged', 'im', '\Bitrix\Im\V2\TariffLimit\Limit', 'onLicenseHasChanged');
		$eventManager->registerEventHandler('humanresources', 'MEMBER_UPDATED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberUpdated');

		//marketplace
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'im','\Bitrix\Im\V2\Marketplace\Placement', 'onRestServiceBuildDescription');

		$solution = \Bitrix\Main\Config\Option::get("main", "wizard_solution", false);
		if ($solution == 'community')
		{
			\Bitrix\Main\Config\Option::set("im", "path_to_user_profile",'/people/user/#user_id#/');
		}

		\Bitrix\Main\Loader::includeModule("im");

		if(\Bitrix\Main\Entity\CryptoField::cryptoAvailable())
		{
			\Bitrix\Im\Model\ConferenceTable::enableCrypto("PASSWORD");
		}

		\Bitrix\Im\Integration\Intranet\User::registerEventHandler();

		$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/im/install/db/' . $connection->getType() . '/install_ft.sql');
		if ($errors === false)
		{
			\Bitrix\Im\Model\MessageIndexTable::getEntity()->enableFullTextIndex("SEARCH_CONTENT");
			\Bitrix\Im\Model\ChatIndexTable::getEntity()->enableFullTextIndex("SEARCH_CONTENT");
		}

		if (\CIMConvert::ConvertCount() > 0)
		{
			\CAdminNotify::Add([
				"MESSAGE" => Loc::getMessage("IM_CONVERT_MESSAGE", Array("#A_TAG_START#" => '<a href="/bitrix/admin/im_convert.php?lang='.LANGUAGE_ID.'">', "#A_TAG_END#" => "</a>")),
				"TAG" => "IM_CONVERT",
				"MODULE_ID" => "IM",
				"ENABLE_CLOSE" => "Y"
			]);
			\CAgent::AddAgent("CIMConvert::UndeliveredMessageAgent();", "im", "N", 20, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+20, "FULL"));
		}

		$this->InstallTemplateRules();
		$this->InstallEvents();
		$this->InstallUserFields();
		$this->installDefaultConfigurationPreset();
		\Bitrix\Main\Config\Option::set('im', 'im_link_url_migration', 'Y'); /** @see \Bitrix\Im\V2\Link\Url\UrlItem::$migrationOptionName */
		\Bitrix\Main\Config\Option::set('im', 'im_link_file_migration', 'Y'); /** @see \Bitrix\Im\V2\Link\File\FileItem::$migrationOptionName */

		\CAgent::AddAgent("CIMChat::InstallGeneralChat(true);", "im", "N", 900, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+900, "FULL"));
		\CAgent::AddAgent('\Bitrix\Im\V2\Chat\GeneralChannel::installAgent();', "im", "N", 3600, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+600, "FULL"));

		return true;
	}

	function InstallFiles()
	{
		global $APPLICATION;

		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/install/activities', $_SERVER['DOCUMENT_ROOT'].'/bitrix/activities', true, true);
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/templates", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", true, true);
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public", $_SERVER["DOCUMENT_ROOT"]."/", true, true);

		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$siteId = \CSite::GetDefSite();
			if ($siteId)
			{
				\Bitrix\Main\UrlRewriter::add($siteId, [
					"CONDITION" => "#^/video([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
					"RULE" => "alias=\$1&videoconf",
					"PATH" => "/desktop_app/router.php",
				]);
				\Bitrix\Main\UrlRewriter::add($siteId, [
					"CONDITION" => "#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
					"RULE" => "alias=\$1",
					"PATH" => "/desktop_app/router.php",
				]);
				\Bitrix\Main\UrlRewriter::add($siteId, [
					"CONDITION" => "#^/online/(/?)([^/]*)#",
					"RULE" => "",
					"PATH" => "/desktop_app/router.php",
				]);
			}
		}

		$APPLICATION->setFileAccessPermission('/desktop_app/', ["*" => "R"]);
		$APPLICATION->setFileAccessPermission('/online/', ["*" => "R"]);
		$APPLICATION->setFileAccessPermission('/video/', ["*" => "R"]);

		return true;
	}

	function InstallEvents()
	{
		global $DB;

		$rs = $DB->Query("SELECT count(*) as CNT FROM b_event_type WHERE EVENT_NAME IN ('IM_NEW_NOTIFY', 'IM_NEW_NOTIFY_GROUP', 'IM_NEW_MESSAGE', 'IM_NEW_MESSAGE_GROUP') ");
		$ar = $rs->Fetch();
		if ($ar["CNT"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/events/set_events.php");
		}

		return true;
	}

	function InstallTemplateRules()
	{
		if (
			file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/templates/pub/")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/pub/")
		)
		{
			\CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/templates/pub/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/pub/",
				$rewrite = true,
				$recursive = true,
				$delete_after_copy = false
			);
		}

		$default_site_id = \CSite::GetDefSite();
		if ($default_site_id)
		{
			$desktopAppFound = false;
			$arAppTempalate = [
				"SORT" => 1,
				"CONDITION" => "CSite::InDir('/desktop_app/')",
				"TEMPLATE" => "desktop_app"
			];

			$callAppFound = false;
			$arCallTempalate = [
				"SORT" => 50,
				"CONDITION" => 'preg_match("#^/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "call_app"
			];

			$callExtranetAppFound = false;
			$arCallExtranetTempalate = [
				"SORT" => 55,
				"CONDITION" => 'preg_match("#^/extranet/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "call_app"
			];

			$callDesktopAppFound = false;
			$arCallTempalateForDesktop = [
				"SORT" => 60,
				"CONDITION" => 'preg_match("#^/desktop_app/router.php\?alias=([\.\-0-9a-zA-Z]+)&videoconf#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "call_app"
			];

			$pubAppFound = false;
			$arPubTempalate = [
				"SORT" => 100,
				"CONDITION" => 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "pub"
			];

			$arFields = ["TEMPLATE" => []];
			$dbTemplates = \CSite::GetTemplateList($default_site_id);
			while ($template = $dbTemplates->Fetch())
			{
				if ($template["CONDITION"] == "CSite::InDir('/desktop_app/')")
				{
					$desktopAppFound = true;
					$template = $arAppTempalate;
				}
				elseif ($template["CONDITION"] == 'preg_match("#^/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$callAppFound = true;
					$template = $arCallTempalate;
				}
				elseif ($template["CONDITION"] == 'preg_match("#^/extranet/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$callExtranetAppFound = true;
					$template = $arCallExtranetTempalate;
				}
				elseif ($template["CONDITION"] == 'preg_match("#^/desktop_app/router.php\?alias=([\.\-0-9a-zA-Z]+)&videoconf#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$callDesktopAppFound = true;
					$template = $arCallTempalateForDesktop;
				}
				elseif ($template["CONDITION"] == 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$pubAppFound = true;
					$template = $arPubTempalate;
				}
				$arFields["TEMPLATE"][] = [
					"SORT" => $template['SORT'],
					"CONDITION" => $template['CONDITION'],
					"TEMPLATE" => $template['TEMPLATE'],
				];
			}
			if (!$desktopAppFound)
			{
				$arFields["TEMPLATE"][] = $arAppTempalate;
			}
			if (!$pubAppFound)
			{
				$arFields["TEMPLATE"][] = $arPubTempalate;
			}
			if (!$callDesktopAppFound)
			{
				$arFields["TEMPLATE"][] = $arCallTempalateForDesktop;
			}
			if (!$callAppFound)
			{
				$arFields["TEMPLATE"][] = $arCallTempalate;
			}
			if (!$callExtranetAppFound)
			{
				$arFields["TEMPLATE"][] = $arCallExtranetTempalate;
			}

			$obSite = new \CSite;
			$arFields["LID"] = $default_site_id;
			$obSite->Update($default_site_id, $arFields);
		}

		return true;
	}

	function InstallUserFields()
	{
		$arFields = [];
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_IM_SEARCH';

		$rs = \CUserTypeEntity::GetList([], [
			"ENTITY_ID" => $arFields["ENTITY_ID"],
			"FIELD_NAME" => $arFields["FIELD_NAME"],
		]);
		if (!$rs->Fetch())
		{
			$arMess['IM_UF_NAME_SEARCH'] = 'IM: users can find';

			$arFields['USER_TYPE_ID'] = 'string';
			$arFields['EDIT_IN_LIST'] = 'N';
			$arFields['SHOW_IN_LIST'] = 'N';
			$arFields['MULTIPLE'] = 'N';

			$arFields['EDIT_FORM_LABEL'][LANGUAGE_ID] = $arMess['IM_UF_NAME_SEARCH'];
			$arFields['LIST_COLUMN_LABEL'][LANGUAGE_ID] = $arMess['IM_UF_NAME_SEARCH'];
			$arFields['LIST_FILTER_LABEL'][LANGUAGE_ID] = $arMess['IM_UF_NAME_SEARCH'];
			if (LANGUAGE_ID != 'en')
			{
				$arFields['EDIT_FORM_LABEL']['en'] = $arMess['IM_UF_NAME_SEARCH'];
				$arFields['LIST_COLUMN_LABEL']['en'] = $arMess['IM_UF_NAME_SEARCH'];
				$arFields['LIST_FILTER_LABEL']['en'] = $arMess['IM_UF_NAME_SEARCH'];
			}

			$CUserTypeEntity = new \CUserTypeEntity();
			$CUserTypeEntity->Add($arFields);
		}
	}

	function installDefaultConfigurationPreset()
	{
		$defaultGroupId = \Bitrix\Main\Config\Option::get('im', \Bitrix\Im\Configuration\Configuration::DEFAULT_PRESET_SETTING_NAME, null);
		if ($defaultGroupId !== null)
		{
			return $defaultGroupId;
		}

		$defaultGroupId = Bitrix\Im\Configuration\Configuration::createDefaultPreset();

		$usersQuery =
			\Bitrix\Main\UserTable::query()
				->addSelect('ID')
				->where('IS_REAL_USER', 'Y')
		;

		$userBindings = [];
		foreach ($usersQuery->exec() as $row)
		{
			$userBindings[] = [
				'USER_ID' => $row['ID'],
				'GENERAL_GROUP_ID' => $defaultGroupId,
				'NOTIFY_GROUP_ID' => $defaultGroupId,
			];
		}
		if (!empty($userBindings))
		{
			\Bitrix\Im\Model\OptionUserTable::addMulti($userBindings, true);
		}

		return $defaultGroupId;
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$step = (int)($_REQUEST['step'] ?? 1);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/unstep1.php");
		}
		elseif ($step == 2)
		{
			$this->UnInstallDB(["savedata" => $_REQUEST["savedata"]]);

			if (!isset($_REQUEST["saveemails"]) || $_REQUEST["saveemails"] != "Y")
			{
				$this->UnInstallEvents();
			}

			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(Loc::getMessage("IM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = [])
	{
		global $APPLICATION, $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		\Bitrix\Main\Loader::includeModule('im');

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/im/install/db/' . $connection->getType() . '/uninstall.sql');
			\Bitrix\Main\Config\Option::delete("im", ['name' => "general_chat_id"]);
			\Bitrix\Im\V2\Chat\GeneralChat::cleanGeneralChatCache(\Bitrix\Im\V2\Chat\GeneralChat::ID_CACHE_ID);
			\Bitrix\Im\V2\Chat\GeneralChat::cleanGeneralChatCache(\Bitrix\Im\V2\Chat\GeneralChat::MANAGERS_CACHE_ID);
			\Bitrix\Main\Config\Option::delete('im', ['name' => \Bitrix\Im\Configuration\Configuration::DEFAULT_PRESET_SETTING_NAME]);
		}

		if (is_array($this->errors))
		{
			$arSQLErrors = $this->errors;
		}

		if (!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		\Bitrix\Im\Integration\Intranet\User::unRegisterEventHandler();

		\CAdminNotify::DeleteByTag("IM_CONVERT");

		\CAgent::RemoveAgent('CIMMail::MailNotifyAgent();', "im");
		\CAgent::RemoveAgent('CIMMail::MailMessageAgent();', "im");
		\CAgent::RemoveAgent('CIMDisk::RemoveTmpFileAgent();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\Notify::cleanNotifyAgent();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\Bot::deleteExpiredTokenAgent();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\Disk\NoRelationPermission::cleaningAgent();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\Call\Conference::removeTemporaryAliases();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\Message\Uuid::cleanOldRecords();', "im");
		\CAgent::RemoveAgent('Bitrix\Im\V2\Link\Reminder\ReminderService::remindAgent();', 'im');
		\CAgent::RemoveAgent('Bitrix\Im\V2\Link\File\TemporaryFileService::cleanAgent();', 'im');
		\CAgent::RemoveAgent('Bitrix\Im\Update\MessageDisappearing::disappearMessagesAgent();', 'im');
		\CAgent::RemoveAgent('\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncRelationAgent();', 'im');
		\CAgent::RemoveAgent('\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService::syncMemberAgent();', 'im');
		\CAgent::RemoveAgent('\Bitrix\Im\V2\Recent\Initializer::executeAgent();', 'im');

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		$eventManager->unRegisterEventHandler("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		$eventManager->unRegisterEventHandler("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		$eventManager->unRegisterEventHandler("perfmon", "OnGetTableSchema", "im", "im", "OnGetTableSchema");
		$eventManager->unRegisterEventHandler('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		$eventManager->unRegisterEventHandler('main', 'OnChangeRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserAdd', 'im', 'CIMEvent', 'OnAfterUserAdd');
		$eventManager->unRegisterEventHandler('main', 'OnUserDelete', 'im', 'CIMEvent', 'OnUserDelete');
		$eventManager->unRegisterEventHandler("main", "OnBeforeUserSendPassword", "im", "CIMEvent", "OnBeforeUserSendPassword");
		$eventManager->unRegisterEventHandler('main', 'OnCancelRatingVote', 'im', 'CIMEvent', 'OnCancelRatingVote');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserUpdate', 'im', 'CIMEvent', 'OnAfterUserUpdate');
		$eventManager->unRegisterEventHandler("main", "OnUserOnlineStatusGetCustomOnlineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		$eventManager->unRegisterEventHandler("main", "OnUserOnlineStatusGetCustomOfflineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		$eventManager->unRegisterEventHandler("pull", "OnGetDependentModule", "im", "CIMEvent", "OnGetDependentModule");
		$eventManager->unRegisterEventHandler("main", "OnProlog", "main", "", "", "/modules/im/ajax_hit.php");
		$eventManager->unRegisterEventHandler("main", "OnApplicationsBuildList", "im", "DesktopApplication", "OnApplicationsBuildList");
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'im', 'CIMRestService', 'OnRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestAppDelete', 'im', 'CIMRestService', 'OnRestAppDelete');
		$eventManager->unRegisterEventHandler('main', 'OnAuthProvidersBuildList', 'im', '\Bitrix\Im\Access\ChatAuthProvider', 'getProviders');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserUpdate', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserUpdate');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserDelete', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserDelete');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserAdd', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserAdd');

		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');
		$eventManager->unRegisterEventHandler('voximplant', 'onConferenceFinished', 'im', '\Bitrix\Im\Call\Call', 'onVoximplantConferenceFinished');

		$eventManager->unregisterEventHandler('calendar', 'OnAfterCalendarEntryUpdate', 'im', '\Bitrix\Im\V2\Service\Messenger', 'updateCalendar');
		$eventManager->unregisterEventHandler('calendar', 'OnAfterCalendarEventDelete', 'im', '\Bitrix\Im\V2\Service\Messenger', 'unregisterCalendar');
		$eventManager->unregisterEventHandler('rest', 'OnRestServiceBuildDescription', 'im','\Bitrix\Im\V2\Marketplace\Placement', 'onRestServiceBuildDescription');
		$eventManager->unregisterEventHandler('im', 'OnAfterMessagesAdd', 'im', '\Bitrix\Im\V2\Message\Delete\DisappearService', 'checkDisappearing');
		$eventManager->unRegisterEventHandler('ai', 'onTuningLoad', 'im', '\Bitrix\Im\V2\Integration\AI\Restriction', 'onTuningLoad');
		$eventManager->unRegisterEventHandler('humanresources', 'RELATION_ADDED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onRelationAdded');
		$eventManager->unRegisterEventHandler('humanresources', 'RELATION_DELETED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onRelationDeleted');
		$eventManager->unRegisterEventHandler('humanresources', 'MEMBER_ADDED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberAdded');
		$eventManager->unRegisterEventHandler('humanresources', 'MEMBER_DELETED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberDeleted');
		$eventManager->unRegisterEventHandler('intranet', 'onLicenseHasChanged', 'im', '\Bitrix\Im\V2\TariffLimit\Limit', 'onLicenseHasChanged');
		$eventManager->unRegisterEventHandler('humanresources', 'MEMBER_UPDATED', 'im', '\Bitrix\Im\V2\Integration\HumanResources\Sync\SyncService', 'onMemberUpdated');

		$this->UnInstallUserFields($arParams);

		\Bitrix\Main\ModuleManager::unRegisterModule("im");

		return true;
	}

	function UnInstallFiles($arParams = [])
	{
		global $APPLICATION;

		\DeleteDirFilesEx('/desktop_app/');
		\DeleteDirFilesEx('/bitrix/templates/desktop_app/');
		\DeleteDirFilesEx('/bitrix/templates/call_app/');

		$APPLICATION->SetFileAccessPermission('/desktop_app/', array("*" => "D"));

		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/events/del_events.php");

		return true;
	}

	function UnInstallUserFields($arParams = [])
	{
		if (!$arParams['savedata'])
		{
			$res = \CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_IM_SEARCH'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new \CUserTypeEntity();
				$CUserTypeEntity->Delete($arFieldData['ID']);
			}
		}

		return true;
	}

	public static function OnGetTableSchema()
	{
		return array(
			"im" => array(
				"b_im_message" => array(
					"ID" => array(
						"b_im_relation" => "LAST_ID",
						"b_im_relation^" => "LAST_SEND_ID",
						"b_im_relation^^" => "START_ID",
						"b_im_relation^^^" => "UNREAD_ID",
						"b_disk_object" => "LAST_FILE_ID",
						"b_im_chat" => "LAST_MESSAGE_ID",
						"b_im_message_param" => "MESSAGE_ID",
						"b_im_recent" => "ITEM_MID",
					),
					"CHAT_ID" => array(
						"b_im_chat" => "ID",
					),
				),
				"b_im_chat" => array(
					"ID" => array(
						"b_im_message" => "CHAT_ID",
						"b_im_relation" => "CHAT_ID",
						"b_im_recent" => "ITEM_CID",
					),
				),
				"b_im_relation" => array(
					"ID" => array(
						"b_im_recent" => "ITEM_RID",
					),
					"CHAT_ID" => array(
						"b_im_chat" => "ID",
					),
				),
			),
			"main" => array(
				"b_user" => array(
					"ID" => array(
						"b_im_relation" => "USER_ID",
						"b_im_message" => "AUTHOR_ID",
						"b_im_chat" => "AUTHOR_ID",
					),
				),
				"b_module" => array(
					"ID" => array(
						"b_im_message" => "NOTIFY_MODULE",
					),
				),
			),
			"imopelines" => array(
				"b_imopenlines_session" => array(
					"ID" => array(
						"b_im_recent" => "ITEM_OLID",
					),
				),
			),
		);
	}
}
