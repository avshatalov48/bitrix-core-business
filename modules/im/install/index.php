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

	function im()
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

		if(strtolower($DB->type) !== 'mysql')
		{
			$this->errors = array(
				GetMessage('IM_DB_NOT_SUPPORTED'),
			);
		}
		else
		{
			$this->errors = false;
			if(!$DB->Query("SELECT 'x' FROM b_im_chat", true))
				$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/im/install/db/".strtolower($DB->type)."/install.sql");
		}


		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("im");
		RegisterModuleDependences('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
		RegisterModuleDependences('main', 'OnCancelRatingVote', 'im', 'CIMEvent', 'OnCancelRatingVote');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'im', 'CIMEvent', 'OnAfterUserAdd');
		RegisterModuleDependences('main', 'OnAfterUserUpdate', 'im', 'CIMEvent', 'OnAfterUserUpdate');
		RegisterModuleDependences('main', 'OnUserDelete', 'im', 'CIMEvent', 'OnUserDelete');
		RegisterModuleDependences("main", "OnBeforeUserSendPassword", "im", "CIMEvent", "OnBeforeUserSendPassword");
		RegisterModuleDependences("pull", "OnGetDependentModule", "im", "CIMEvent", "OnGetDependentModule");
		RegisterModuleDependences("main", "OnProlog", "main", "", "", 3, "/modules/im/ajax_hit.php");
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "im", "CIMTableSchema", "OnGetTableSchema");
		RegisterModuleDependences("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		RegisterModuleDependences("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		RegisterModuleDependences("main", "OnApplicationsBuildList", "im", "DesktopApplication", "OnApplicationsBuildList");
		RegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOnlineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		RegisterModuleDependences("main", "OnUserOnlineStatusGetCustomOfflineStatus", "im", "CIMStatus", "OnUserOnlineStatusGetCustomStatus");
		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'im', 'CIMRestService', 'OnRestServiceBuildDescription');
		RegisterModuleDependences('rest', 'OnRestAppDelete', 'im', 'CIMRestService', 'OnRestAppDelete');

		CAgent::AddAgent("CIMMail::MailNotifyAgent();", "im", "N", 600);
		CAgent::AddAgent("CIMMail::MailMessageAgent();", "im", "N", 600);
		CAgent::AddAgent("CIMDisk::RemoveTmpFileAgent();", "im", "N", 43200);
		CAgent::AddAgent("\\Bitrix\\Im\\Bot::deleteExpiredTokenAgent();", "im", "N", 86400);
		CAgent::AddAgent("\\Bitrix\\Im\\Disk\\NoRelationPermission::cleaningAgent();", "im", "N", 3600);

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->registerEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');

		$solution = COption::GetOptionString("main", "wizard_solution", false);
		if ($solution == 'community')
		{
			COption::SetOptionString("im", "path_to_user_profile",'/people/user/#user_id#/');
		}

		CModule::IncludeModule("im");

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/mysql/install_ft.sql");
		if ($errors === false)
		{
			\Bitrix\Im\Model\MessageTable::getEntity()->enableFullTextIndex("MESSAGE");
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

		CIMChat::InstallGeneralChat();

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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/templates", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", True, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public", $_SERVER["DOCUMENT_ROOT"]."/", True, True);

			CUrlRewriter::add(array(
				"CONDITION" => "#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
				"RULE" => "alias=\$1",
				"PATH" => "/desktop_app/router.php",
			));
			CUrlRewriter::add(array(
				"CONDITION" => "#^/online/(/?)([^/]*)#",
				"RULE" => "",
				"PATH" => "/desktop_app/router.php",
			));

			$GLOBALS["APPLICATION"]->SetFileAccessPermission('/desktop_app/', array("*" => "R"));
			$GLOBALS["APPLICATION"]->SetFileAccessPermission('/online/', array("*" => "R"));
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

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
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

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/im/install/db/".strtolower($DB->type)."/uninstall.sql");
			COption::RemoveOption("im", "general_chat_id");
		}

		if(is_array($this->errors))
			$arSQLErrors = $this->errors;

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}
		CAdminNotify::DeleteByTag("IM_CONVERT");

		CAgent::RemoveAgent("CIMMail::MailNotifyAgent();", "im");
		CAgent::RemoveAgent("CIMMail::MailMessageAgent();", "im");
		CAgent::RemoveAgent("CIMDisk::RemoveTmpFileAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Bot::deleteExpiredTokenAgent();", "im");
		CAgent::RemoveAgent("\\Bitrix\\Im\\Disk\\NoRelationPermission::cleaningAgent();", "im");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "im", "CIMNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("main", "OnFileDelete", "im", "CIMEvent", "OnFileDelete");
		UnRegisterModuleDependences("disk", "onAfterDeleteFile", "im", "CIMDisk", "OnAfterDeleteFile");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "im", "CIMTableSchema", "OnGetTableSchema");
		UnRegisterModuleDependences('main', 'OnAddRatingVote', 'im', 'CIMEvent', 'OnAddRatingVote');
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

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounter', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounter');
		$eventManager->unRegisterEventHandler('pull', 'onGetMobileCounterTypes', 'im', '\Bitrix\Im\Counter', 'onGetMobileCounterTypes');

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
}
