<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("im")) return;

class im extends CModule
{
	var $MODULE_ID = "im";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = IM_VERSION;
			$this->MODULE_VERSION_DATE = IM_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("IM_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IM_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();

		$GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage("IM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/step1.php");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;

		if(!$DB->Query("SELECT 'x' FROM b_im_chat", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/im/install/db/mysql/install.sql");

		if(!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("im");
		RegisterModuleDependences('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		RegisterModuleDependences('main', 'OnChangeRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		RegisterModuleDependences('main', 'OnCancelRatingVote', 'im', 'CIMEvent', 'OnCancelRatingVote');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'im', 'CIMEvent', 'OnAfterUserAdd');
		RegisterModuleDependences('main', 'OnAfterUserUpdate', 'im', 'CIMEvent', 'OnAfterUserUpdate');
		RegisterModuleDependences('main', 'OnUserDelete', 'im', 'CIMEvent', 'OnUserDelete');
		RegisterModuleDependences("main", "OnBeforeUserSendPassword", "im", "CIMEvent", "OnBeforeUserSendPassword");
		RegisterModuleDependences("pull", "OnGetDependentModule", "im", "CIMEvent", "OnGetDependentModule");
		RegisterModuleDependences("main", "OnProlog", "main", "", "", 3, "/modules/im/ajax_hit.php");
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "im", "im", "OnGetTableSchema");
		RegisterModuleDependences("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		RegisterModuleDependences("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		RegisterModuleDependences("main", "OnApplicationsBuildList", "im", "DesktopApplication", "OnApplicationsBuildList");
		RegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOnlineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		RegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOfflineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'im', 'CIMRestService', 'OnRestServiceBuildDescription');
		RegisterModuleDependences('rest', 'OnRestAppDelete', 'im', 'CIMRestService', 'OnRestAppDelete');
		RegisterModuleDependences('main', 'OnAuthProvidersBuildList', 'im', '\Bitrix\Im\Access\ChatAuthProvider', 'getProviders');
		RegisterModuleDependences('main', 'OnAfterUserUpdate', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserUpdate');
		RegisterModuleDependences( 'main', 'OnAfterUserDelete', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserDelete');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserAdd');

		CAgent::AddAgent("CIMMail::MailNotifyAgent();", "im", "N", 600);
		CAgent::AddAgent("CIMMail::MailMessageAgent();", "im", "N", 600);
		CAgent::AddAgent("CIMDisk::RemoveTmpFileAgent();", "im", "N", 43200);
		CAgent::AddAgent("\\Bitrix\\Im\\Notify::cleanNotifyAgent();", "im", "N", 7200);
		CAgent::AddAgent("\\Bitrix\\Im\\Bot::deleteExpiredTokenAgent();", "im", "N", 86400);
		CAgent::AddAgent("\\Bitrix\\Im\\Disk\\NoRelationPermission::cleaningAgent();", "im", "N", 3600);
		CAgent::AddAgent("\\Bitrix\\Im\\Call\\Conference::removeTemporaryAliases();", "im", "N", 86400);
		CAgent::AddAgent('\Bitrix\Im\Message\Uuid::cleanOldRecords();', 'im', 'N', 86400);/** @see \Bitrix\Im\Message\Uuid::cleanOldRecords */
		CAgent::AddAgent('\Bitrix\Im\V2\Link\Reminder\ReminderService::remindAgent();', 'im', 'N', 60);
		CAgent::AddAgent('\Bitrix\Im\V2\Link\File\TemporaryFileService::cleanAgent();', 'im', 'N', 3600);

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->registerEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');
		$eventManager->registerEventHandler('voximplant', 'onConferenceFinished', 'im', '\Bitrix\Im\Call\Call', 'onVoximplantConferenceFinished');

		$eventManager->registerEventHandler('rest', 'onRestCheckAuth', 'im', '\Bitrix\Im\Call\Auth', 'onRestCheckAuth');

		$eventManager->registerEventHandler('calendar', 'OnAfterCalendarEntryUpdate', 'im', '\Bitrix\Im\V2\Service\Messenger', 'updateCalendar');
		$eventManager->registerEventHandler('calendar', 'OnAfterCalendarEventDelete', 'im', '\Bitrix\Im\V2\Service\Messenger', 'unregisterCalendar');

		//marketplace
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'im','\Bitrix\Im\V2\Marketplace\Placement', 'onRestServiceBuildDescription');

		$solution = COption::GetOptionString("main", "wizard_solution", false);
		if ($solution == 'community')
		{
			COption::SetOptionString("im", "path_to_user_profile",'/people/user/#user_id#/');
		}

		CModule::IncludeModule("im");

		if(\Bitrix\Main\Entity\CryptoField::cryptoAvailable())
		{
			\Bitrix\Im\Model\ConferenceTable::enableCrypto("PASSWORD");
		}

		\Bitrix\Im\Integration\Intranet\User::registerEventHandler();

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/db/mysql/install_ft.sql");
		if ($errors === false)
		{
			\Bitrix\Im\Model\MessageIndexTable::getEntity()->enableFullTextIndex("SEARCH_CONTENT");
			\Bitrix\Im\Model\ChatIndexTable::getEntity()->enableFullTextIndex("SEARCH_CONTENT");
		}

		if (CIMConvert::ConvertCount() > 0)
		{
			CAdminNotify::Add(Array(
				"MESSAGE" => GetMessage("IM_CONVERT_MESSAGE", Array("#A_TAG_START#" => '<a href="/bitrix/admin/im_convert.php?lang='.LANGUAGE_ID.'">', "#A_TAG_END#" => "</a>")),
				"TAG" => "IM_CONVERT",
				"MODULE_ID" => "IM",
				"ENABLE_CLOSE" => "Y"
			));
			CAgent::AddAgent("CIMConvert::UndeliveredMessageAgent();", "im", "N", 20, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+20, "FULL"));
		}

		$this->InstallTemplateRules();
		$this->InstallEvents();
		$this->InstallUserFields();
		$this->installDefaultConfigurationPreset();

		CAgent::AddAgent("CIMChat::InstallGeneralChat(true);", "im", "N", 900, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+900, "FULL"));

		return true;
	}

	function InstallFiles()
	{
		if($_ENV['COMPUTERNAME']!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/install/activities', $_SERVER['DOCUMENT_ROOT'].'/bitrix/activities', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/templates", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public", $_SERVER["DOCUMENT_ROOT"]."/", true, true);

			if (!IsModuleInstalled('bitrix24'))
			{
				$siteId = \CSite::GetDefSite();
				if ($siteId)
				{
					\Bitrix\Main\UrlRewriter::add($siteId, array(
						"CONDITION" => "#^/video([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
						"RULE" => "alias=\$1&videoconf",
						"PATH" => "/desktop_app/router.php",
					));
					\Bitrix\Main\UrlRewriter::add($siteId, array(
						"CONDITION" => "#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
						"RULE" => "alias=\$1",
						"PATH" => "/desktop_app/router.php",
					));
					\Bitrix\Main\UrlRewriter::add($siteId, array(
						"CONDITION" => "#^/online/(/?)([^/]*)#",
						"RULE" => "",
						"PATH" => "/desktop_app/router.php",
					));
				}
			}

			$GLOBALS["APPLICATION"]->SetFileAccessPermission('/desktop_app/', array("*" => "R"));
			$GLOBALS["APPLICATION"]->SetFileAccessPermission('/online/', array("*" => "R"));
			$GLOBALS["APPLICATION"]->SetFileAccessPermission('/video/', array("*" => "R"));
		}
		return true;
	}

	function InstallEvents()
	{
		global $DB;

		$rs = $DB->Query("SELECT count(*) CNT FROM b_event_type WHERE EVENT_NAME IN ('IM_NEW_NOTIFY', 'IM_NEW_NOTIFY_GROUP', 'IM_NEW_MESSAGE', 'IM_NEW_MESSAGE_GROUP') ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["CNT"] <= 0)
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/events/set_events.php");

		return true;
	}

	function InstallTemplateRules()
	{
		if (
			file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/templates/pub/")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/pub/")
		)
		{
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/templates/pub/",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/pub/",
				$rewrite = true,
				$recursive = true,
				$delete_after_copy = false
			);
		}

		$default_site_id = CSite::GetDefSite();
		if ($default_site_id)
		{
			$desktopAppFound = false;
			$arAppTempalate = Array(
				"SORT" => 1,
				"CONDITION" => "CSite::InDir('/desktop_app/')",
				"TEMPLATE" => "desktop_app"
			);

			$callAppFound = false;
			$arCallTempalate = Array(
				"SORT" => 50,
				"CONDITION" => 'preg_match("#^/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "call_app"
			);

			$callDesktopAppFound = false;
			$arCallTempalateForDesktop = [
				"SORT" => 60,
				"CONDITION" => 'preg_match("#^/desktop_app/router.php\?alias=([\.\-0-9a-zA-Z]+)&videoconf#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "call_app"
			];

			$pubAppFound = false;
			$arPubTempalate = Array(
				"SORT" => 100,
				"CONDITION" => 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
				"TEMPLATE" => "pub"
			);

			$arFields = Array("TEMPLATE"=>Array());
			$dbTemplates = CSite::GetTemplateList($default_site_id);
			while($template = $dbTemplates->Fetch())
			{
				if ($template["CONDITION"] == "CSite::InDir('/desktop_app/')")
				{
					$desktopAppFound = true;
					$template = $arAppTempalate;
				}
				else if ($template["CONDITION"] == 'preg_match("#^/video/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$callAppFound = true;
					$template = $arCallTempalate;
				}
				else if ($template["CONDITION"] == 'preg_match("#^/desktop_app/router.php\?alias=([\.\-0-9a-zA-Z]+)&videoconf#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$callDesktopAppFound = true;
					$template = $arCallTempalateForDesktop;
				}
				else if ($template["CONDITION"] == 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
				{
					$pubAppFound = true;
					$template = $arPubTempalate;
				}
				$arFields["TEMPLATE"][] = array(
					"SORT" => $template['SORT'],
					"CONDITION" => $template['CONDITION'],
					"TEMPLATE" => $template['TEMPLATE'],
				);
			}
			if (!$desktopAppFound)
				$arFields["TEMPLATE"][] = $arAppTempalate;
			if (!$pubAppFound)
				$arFields["TEMPLATE"][] = $arPubTempalate;
			if (!$callDesktopAppFound)
				$arFields["TEMPLATE"][] = $arCallTempalateForDesktop;
			if (!$callAppFound)
				$arFields["TEMPLATE"][] = $arCallTempalate;

			$obSite = new CSite;
			$arFields["LID"] = $default_site_id;
			$obSite->Update($default_site_id, $arFields);
		}

		return true;
	}

	function InstallUserFields()
	{
		$arFields = array();
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_IM_SEARCH';

		$rs = CUserTypeEntity::GetList(array(), array(
			"ENTITY_ID" => $arFields["ENTITY_ID"],
			"FIELD_NAME" => $arFields["FIELD_NAME"],
		));
		if(!$rs->Fetch())
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

			$CUserTypeEntity = new CUserTypeEntity();
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

		$defaultGroupId =
			\Bitrix\Im\Model\OptionGroupTable::add([
				'NAME' => \Bitrix\Im\Configuration\Configuration::DEFAULT_PRESET_NAME,
				'SORT' => 0,
				'CREATE_BY_ID' => 0,
			])
				->getId()
		;
		$generalDefaultSettings = \Bitrix\Im\Configuration\General::getDefaultSettings();
		\Bitrix\Im\Configuration\General::setSettings($defaultGroupId, $generalDefaultSettings);

		$notifySettings = \Bitrix\Im\Configuration\Notification::getSimpleNotifySettings($generalDefaultSettings);
		\Bitrix\Im\Configuration\Notification::setSettings($defaultGroupId, $notifySettings);


		if (\Bitrix\Main\Loader::includeModule('intranet'))
		{
			$topDepartmentId = \Bitrix\Im\Configuration\Department::getTopDepartmentId();
			\Bitrix\Im\Model\OptionAccessTable::add([
				'GROUP_ID' => $defaultGroupId,
				'ACCESS_CODE' => $topDepartmentId ? 'DR' . $topDepartmentId : 'AU'
			]);
		}

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

		\Bitrix\Main\Config\Option::set('im', \Bitrix\Im\Configuration\Configuration::DEFAULT_PRESET_SETTING_NAME, (int)$defaultGroupId);

		return $defaultGroupId;
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = intval($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("IM_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/im/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array("savedata" => $_REQUEST["savedata"]));

			if(!isset($_REQUEST["saveemails"]) || $_REQUEST["saveemails"] != "Y")
				$this->UnInstallEvents();

			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(GetMessage("IM_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/im/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		CModule::IncludeModule('im');

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/im/install/db/mysql/uninstall.sql");
			COption::RemoveOption("im", "general_chat_id");
			\Bitrix\Main\Config\Option::delete('im', ['name' => \Bitrix\Im\Configuration\Configuration::DEFAULT_PRESET_SETTING_NAME]);
		}

		if(is_array($this->errors))
			$arSQLErrors = $this->errors;

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		\Bitrix\Im\Integration\Intranet\User::unRegisterEventHandler();

		CAdminNotify::DeleteByTag("IM_CONVERT");

		CAgent::RemoveAgent("CIMMail::MailNotifyAgent();", "im");
		CAgent::RemoveAgent("CIMMail::MailMessageAgent();", "im");
		CAgent::RemoveAgent("CIMDisk::RemoveTmpFileAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Notify::cleanNotifyAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Bot::deleteExpiredTokenAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Disk\\NoRelationPermission::cleaningAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Call\\Conference::removeTemporaryAliases();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Message\\Uuid::cleanOldRecords();", "im");
		CAgent::RemoveAgent('\Bitrix\Im\V2\Link\Reminder\ReminderService::remindAgent();', 'im');
		CAgent::RemoveAgent('\Bitrix\Im\V2\Link\File\TemporaryFileService::cleanAgent();', 'im');
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		UnRegisterModuleDependences("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "im", "im", "OnGetTableSchema");
		UnRegisterModuleDependences('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		UnRegisterModuleDependences('main', 'OnChangeRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		UnRegisterModuleDependences('main', 'OnAfterUserAdd', 'im', 'CIMEvent', 'OnAfterUserAdd');
		UnRegisterModuleDependences('main', 'OnUserDelete', 'im', 'CIMEvent', 'OnUserDelete');
		UnRegisterModuleDependences("main", "OnBeforeUserSendPassword", "im", "CIMEvent", "OnBeforeUserSendPassword");
		UnRegisterModuleDependences('main', 'OnCancelRatingVote', 'im', 'CIMEvent', 'OnCancelRatingVote');
		UnRegisterModuleDependences('main', 'OnAfterUserUpdate', 'im', 'CIMEvent', 'OnAfterUserUpdate');
		UnRegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOnlineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		UnRegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOfflineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		UnRegisterModuleDependences("pull", "OnGetDependentModule", "im", "CIMEvent", "OnGetDependentModule");
		UnRegisterModuleDependences("main", "OnProlog", "main", "", "", "/modules/im/ajax_hit.php");
		UnRegisterModuleDependences("main", "OnApplicationsBuildList", "im", "DesktopApplication", "OnApplicationsBuildList");
		UnRegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'im', 'CIMRestService', 'OnRestServiceBuildDescription');
		UnRegisterModuleDependences('rest', 'OnRestAppDelete', 'im', 'CIMRestService', 'OnRestAppDelete');
		UnRegisterModuleDependences('main', 'OnAuthProvidersBuildList', 'im', '\Bitrix\Im\Access\ChatAuthProvider', 'getProviders');
		UnRegisterModuleDependences('main', 'OnAfterUserUpdate', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserUpdate');
		UnRegisterModuleDependences( 'main', 'OnAfterUserDelete', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserDelete');
		UnRegisterModuleDependences('main', 'OnAfterUserAdd', 'im', '\Bitrix\Im\Configuration\EventHandler', 'onAfterUserAdd');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');
		$eventManager->unRegisterEventHandler('voximplant', 'onConferenceFinished', 'im', '\Bitrix\Im\Call\Call', 'onVoximplantConferenceFinished');

		$eventManager->unregisterEventHandler('calendar', 'OnAfterCalendarEntryUpdate', 'im', '\Bitrix\Im\V2\Service\Messenger', 'updateCalendar');
		$eventManager->unregisterEventHandler('calendar', 'OnAfterCalendarEventDelete', 'im', '\Bitrix\Im\V2\Service\Messenger', 'unregisterCalendar');
		$eventManager->unregisterEventHandler('rest', 'OnRestServiceBuildDescription', 'im','\Bitrix\Im\V2\Marketplace\Placement', 'onRestServiceBuildDescription');

		$this->UnInstallUserFields($arParams);



		UnRegisterModule("im");

		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFilesEx('/desktop_app/');
			DeleteDirFilesEx('/bitrix/templates/desktop_app/');
			DeleteDirFilesEx('/bitrix/templates/call_app/');
		}
		$GLOBALS["APPLICATION"]->SetFileAccessPermission('/desktop_app/', array("*" => "D"));
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/events/del_events.php");

		return true;
	}

	function UnInstallUserFields($arParams = Array())
	{
		if (!$arParams['savedata'])
		{
			$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_IM_SEARCH'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new CUserTypeEntity();
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
