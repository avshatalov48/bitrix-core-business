<?php

if(class_exists("sender"))
{
	return;
}

IncludeModuleLangFile(__FILE__);

class sender extends CModule
{
	var $MODULE_ID = "sender";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	var $errors;

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("SENDER_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SENDER_MODULE_DESC");
		$this->MODULE_CSS = "/bitrix/modules/sender/styles.css";
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		if (!$DB->TableExists('b_sender_contact'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sender/install/db/' . $connection->getType() . '/install.sql');
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("sender");
			CModule::IncludeModule("sender");

			$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sender/install/db/' . $connection->getType() . '/install_ft.sql');

			// read and click notifications
			RegisterModuleDependences("main", "OnMailEventMailRead", "sender", "bitrix\\sender\\postingmanager", "onMailEventMailRead");
			RegisterModuleDependences("main", "OnMailEventMailClick", "sender", "bitrix\\sender\\postingmanager", "onMailEventMailClick");

			// unsubscription notifications
			RegisterModuleDependences("main", "OnMailEventSubscriptionDisable", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionDisable");
			RegisterModuleDependences("main", "OnMailEventSubscriptionEnable", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionEnable");
			RegisterModuleDependences("main", "OnMailEventSubscriptionList", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionList");
			RegisterModuleDependences(
				"main", \Bitrix\Main\Mail\Tracking::onChangeStatus,
				"sender", \Bitrix\Sender\Integration\EventHandler::class, "onMailEventMailChangeStatus"
			);

			// connectors of module sender
			RegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorListContact");
			RegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorListRecipient");
			RegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorList");

			// mail templates and blocks
			RegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\Preset\\TemplateBase", "onPresetTemplateList");
			RegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\TemplateTable", "onPresetTemplateList");
			RegisterModuleDependences("sender", "OnPresetMailBlockList", "sender", "Bitrix\\Sender\\Preset\\MailBlockBase", "OnPresetMailBlockList");
			RegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\Preset\\TemplateBase", "onPresetTemplateListSite");

			// triggers
			RegisterModuleDependences("sender", "OnTriggerList", "sender", "bitrix\\sender\\triggermanager", "onTriggerList");
			RegisterModuleDependences("sender", "OnAfterRecipientUnsub", "sender", "Bitrix\\Sender\\TriggerManager", "onAfterRecipientUnsub");

			// conversion
			RegisterModuleDependences("sender", "OnAfterRecipientClick", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onAfterRecipientClick");
			RegisterModuleDependences("conversion", "OnSetDayContextAttributes", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onSetDayContextAttributes");
			RegisterModuleDependences("main", "OnBeforeProlog", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onBeforeProlog");
			RegisterModuleDependences("conversion", "OnGetAttributeTypes", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onGetAttributeTypes");

			// voximplant
			RegisterModuleDependences("voximplant", "OnInfoCallResult", "sender", "Bitrix\\Sender\\Integration\\VoxImplant\\Service", "onInfoCallResult");

			RegisterModuleDependences("pull", "OnGetDependentModule", "sender", "Bitrix\\Sender\\SenderPullSchema", "OnGetDependentModule" );
			RegisterModuleDependences("im", "OnGetNotifySchema", "sender", "Bitrix\\Sender\\SenderNotifySchema", "OnGetNotifySchema" );

			RegisterModuleDependences("main", "OnAfterFileSave", "sender", "Bitrix\\Sender\\Integration\\Main\\FileManager", "OnAfterFileSave" );

			CTimeZone::Disable();

			\Bitrix\Sender\Runtime\Job::actualizeAll();
			\Bitrix\Sender\Trigger\Manager::activateAllHandlers();
			CAgent::AddAgent(
				'Bitrix\\Sender\\Access\\Install\\AccessInstaller::installAgent();',
				"sender", "N", 60, "", "Y",
				ConvertTimeStamp(time()+CTimeZone::GetOffset()+450, "FULL")
			);

			CTimeZone::Enable();

			\Bitrix\Main\Update\Stepper::bindClass(
				'\Bitrix\Sender\Install\SetFileInfoStepper',
				'sender',
				600
			);

			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;

		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		CModule::IncludeModule("sender");
		\Bitrix\Sender\Trigger\Manager::activateAllHandlers(false);

		if(!array_key_exists("save_tables", $arParams) || ($arParams["save_tables"] != "Y"))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/db/".$connection->getType()."/uninstall.sql");
		}

		CAgent::RemoveModuleAgents('sender');

		UnRegisterModuleDependences("main", "OnMailEventMailRead", "sender", "bitrix\\sender\\postingmanager", "onMailEventMailRead");
		UnRegisterModuleDependences("main", "OnMailEventMailClick", "sender", "bitrix\\sender\\postingmanager", "onMailEventMailClick");

		UnRegisterModuleDependences("main", "OnMailEventSubscriptionDisable", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionDisable");
		UnRegisterModuleDependences("main", "OnMailEventSubscriptionEnable", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionEnable");
		UnRegisterModuleDependences("main", "OnMailEventSubscriptionList", "sender", "Bitrix\\Sender\\Subscription", "onMailEventSubscriptionList");
		UnRegisterModuleDependences(
			"main", \Bitrix\Main\Mail\Tracking::onChangeStatus,
			"sender", \Bitrix\Sender\Integration\EventHandler::class, "onMailEventMailChangeStatus"
		);

		UnRegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorListContact");
		UnRegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorListRecipient");
		UnRegisterModuleDependences("sender", "OnConnectorList", "sender", "bitrix\\sender\\connectormanager", "onConnectorList");

		UnRegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\Preset\\TemplateBase", "onPresetTemplateList");
		UnRegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\TemplateTable", "onPresetTemplateList");
		UnRegisterModuleDependences("sender", "OnPresetMailBlockList", "sender", "Bitrix\\Sender\\Preset\\MailBlockBase", "OnPresetMailBlockList");
		UnRegisterModuleDependences("sender", "OnPresetTemplateList", "sender", "Bitrix\\Sender\\Preset\\TemplateBase", "onPresetTemplateListSite");

		UnRegisterModuleDependences("sender", "OnTriggerList", "sender", "bitrix\\sender\\triggermanager", "onTriggerList");
		UnRegisterModuleDependences("sender", "OnAfterRecipientUnsub", "sender", "Bitrix\\Sender\\TriggerManager", "onAfterRecipientUnsub");

		UnRegisterModuleDependences("sender", "OnAfterRecipientClick", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onAfterRecipientClick");
		UnRegisterModuleDependences("conversion", "OnSetDayContextAttributes", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onSetDayContextAttributes");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onBeforeProlog");
		UnRegisterModuleDependences("conversion", "OnGetAttributeTypes", "sender", "Bitrix\\Sender\\Internals\\ConversionHandler", "onGetAttributeTypes");

		// voximplant
		UnRegisterModuleDependences("voximplant", "OnInfoCallResult", "sender", "Bitrix\\Sender\\Integration\\VoxImplant\\Service", "onInfoCallResult");

		UnRegisterModuleDependences("pull", "OnGetDependentModule", "sender", "Bitrix\\Sender\\SenderPullSchema", "OnGetDependentModule" );
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "sender", "Bitrix\\Sender\\SenderNotifySchema", "OnGetNotifySchema" );

		UnRegisterModuleDependences("main", "OnAfterFileSave", "sender", "Bitrix\\Sender\\Integration\\Main\\FileManager", "OnAfterFileSave" );

		UnRegisterModule("sender");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function GetEventCountByName($eventName)
	{
		global $DB;
		$result = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN ('".$DB->ForSql($eventName)."') "
		);
		$array = $result->Fetch();
		return $array['C'];
	}

	function InstallEvents()
	{
		$senderSubscribeEventCount = $this->getEventCountByName("SENDER_SUBSCRIBE_CONFIRM") ?? 0;
		$senderSubscribeEvent = $senderSubscribeEventCount <= 0;

		$senderConsentEventCount = $this->getEventCountByName("SENDER_CONSENT") ?? 0;
		$senderConsentEvent = $senderConsentEventCount <= 0;

		if($senderSubscribeEvent || $senderConsentEvent)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/events.php");
		}
		return true;
	}

	function DeleteEventByName($name)
	{
		global $DB;
		$realEscapeName = $DB->ForSql($name);
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN ('".$realEscapeName."') ");
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN ('".$realEscapeName."') ");
	}

	function UnInstallEvents()
	{
		$this->DeleteEventByName("SENDER_SUBSCRIBE_CONFIRM");
		$this->DeleteEventByName("SENDER_CONSENT");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		//admin files
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
		//css
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js");

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
		if($POST_RIGHT == "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("SENDER_MODULE_INST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/inst1.php");
			}
			elseif($step==2)
			{
				if($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("SENDER_MODULE_INST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/inst2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
		if($POST_RIGHT == "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("SENDER_MODULE_UNINST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/uninst1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"save_tables" => $_REQUEST["save_tables"],
				));
				//message types and templates
				if($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("SENDER_MODULE_UNINST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sender/install/uninst2.php");
			}
		}
	}

}
