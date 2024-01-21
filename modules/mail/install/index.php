<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Class mail extends CModule
{
	var $MODULE_ID = "mail";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("MAIL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("MAIL_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		if (!$DB->TableExists('b_mail_mailbox'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mail/install/db/' . $connection->getType() . '/install.sql');

			if (\Bitrix\Main\Entity\CryptoField::cryptoAvailable())
			{
				\Bitrix\Main\ORM\Data\DataManager::enableCrypto('TOKENS', 'b_mail_oauth', true);
			}
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			$eventManager = \Bitrix\Main\EventManager::getInstance();

			$eventManager->registerEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', 'mail', 'CMailRestService', 'OnRestServiceBuildDescription');

			$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserUpdate', 'mail', 'CMail', 'onUserUpdate');
			$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserDelete', 'mail', 'CMail', 'onUserDelete');

			$eventManager->registerEventHandlerCompatible('main', 'OnBeforeSiteUpdate', 'mail', 'Bitrix\Mail\User', 'handleSiteUpdate');
			$eventManager->registerEventHandler('main', 'OnAfterSetOption_server_name', 'mail', 'Bitrix\Mail\User', 'handleServerNameUpdate');

			$eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'mail', 'Bitrix\Mail\MessageUserType', 'getUserTypeDescription');
			$eventManager->registerEventHandlerCompatible('main', 'OnMailEventMailRead', 'mail', 'Bitrix\Mail\Helper\MessageEventManager', 'onMailEventMailRead');

			$eventManager->registerEventHandler('main', 'OnUISelectorGetProviderByEntityType', 'mail', '\Bitrix\Mail\Integration\Main\UISelector\Handler', 'OnUISelectorGetProviderByEntityType');
			$eventManager->registerEventHandler('main', 'OnUISelectorFillLastDestination', 'mail', '\Bitrix\Mail\Integration\Main\UISelector\Handler', 'OnUISelectorFillLastDestination');

			$eventManager->registerEventHandler('mail', 'onMailMessageNew', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onMailMessageNew');
			$eventManager->registerEventHandlerCompatible('im', 'OnGetNotifySchema', 'mail', '\Bitrix\Mail\Integration\Im\Notification', 'getSchema');

			$eventManager->registerEventHandler('mail', 'onMailMessageNew', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onMailMessageNew');

			$eventManager->registerEventHandler('mobile', 'onRequestSyncMail', 'mail', '\Bitrix\Mail\Integration\SyncRequest', 'onRequestSyncMail');

			$eventManager->registerEventHandler('calendar', 'OnAfterCalendarEventDelete', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onUnbindEvent');

			$eventManager->registerEventHandler('ai', 'onTuningLoad', 'mail', '\Bitrix\Mail\Integration\AI\EventHandler', 'onTuningLoad');
			$eventManager->registerEventHandler('ai', 'onContextGetMessages', 'mail', '\Bitrix\Mail\Integration\AI\Controller', 'onContextGetMessages');

			RegisterModule("mail");

			if (CModule::IncludeModule("mail"))
			{
				$errors = $DB->runSqlBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mail/install/db/' . $connection->getType() . '/install_ft.sql');
				if ($errors === false)
				{
					\Bitrix\Mail\MailMessageTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
				}

				$result = \Bitrix\Main\SiteTable::getList();
				while (($site = $result->fetch()) !== false)
				{
					$this->installMailService($site["LID"]);
				}

				// create group and give it rights
				$arGroup = array(
					"ACTIVE" => "Y",
					"C_SORT" => 201,
					"NAME" => Loc::getMessage("MAIL_GROUP_NAME"),
					"DESCRIPTION" => Loc::getMessage("MAIL_GROUP_DESC"),
					"STRING_ID" => "MAIL_INVITED",
					"TASKS_MODULE" => array("main_change_profile"),
					"TASKS_FILE" => array(
						Array("fm_folder_access_read", "/bitrix/components/bitrix/"),
						Array("fm_folder_access_read", "/bitrix/tools/"),
						Array("fm_folder_access_read", "/upload/"),
						Array("fm_folder_access_read", "/pub/")
					),
				);

				$group = new CGroup;

				$dbResult = CGroup::GetList(
					'id',
					'asc',
					array(
						"STRING_ID" => $arGroup["STRING_ID"],
						"STRING_ID_EXACT_MATCH" => "Y"
					)
				);
				if ($arExistsGroup = $dbResult->Fetch())
				{
					$groupID = $arExistsGroup["ID"];
				}
				else
				{
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24"))
					{
						$arGroup["~ID"] = 17;
					}
					$groupID = $group->Add($arGroup);
				}

				if ($groupID > 0)
				{
					COption::SetOptionString("mail", "mail_invited_group", $groupID);

					$arTasksID = Array();
					foreach ($arGroup["TASKS_MODULE"] as $taskName)
					{
						$dbResult = CTask::GetList(array(), array("NAME" => $taskName));
						if ($arTask = $dbResult->Fetch())
						{
							$arTasksID[] = $arTask["ID"];
						}
					}
					if (!empty($arTasksID))
					{
						CGroup::SetTasks($groupID, $arTasksID);
					}

					if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24"))
					{
						foreach ($arGroup["TASKS_FILE"] as $arFile)
						{
							$taskName = $arFile[0];
							$originalPath = $filePath = $arFile[1];

							$dbResult = CTask::GetList(Array(), Array("NAME" => $taskName));
							if ($arTask = $dbResult->Fetch())
							{
								$permissions = array(
									$groupID => "T_".$arTask["ID"]
								);

								$documentRoot = $_SERVER["DOCUMENT_ROOT"];
								$filePath = rtrim($filePath, "/");
								$position = mb_strrpos($filePath, "/");

								$pathFile = mb_substr($filePath, $position + 1);
								$pathDir = mb_substr($filePath, 0, $position);

								$PERM = array();
								if (file_exists($documentRoot.$pathDir."/.access.php"))
								{
									@include($documentRoot.$pathDir."/.access.php");
								}

								$arPermisson = (
								!isset($PERM[$pathFile])
								|| !is_array($PERM[$pathFile])
									? $permissions
									: $permissions + $PERM[$pathFile]
								);

								$GLOBALS["APPLICATION"]->SetFileAccessPermission($originalPath, $arPermisson);
							}
						}
					}
				}
			}

			RegisterModuleDependences("pull", "OnGetDependentModule", "mail", "\\Bitrix\\Mail\\MailPullSchema", "OnGetDependentModule" );
			RegisterModuleDependences('tasks', 'OnTaskDelete', 'mail', '\\Bitrix\\Mail\\Integration\\Intranet\\Secretary', 'onTaskDelete');

			CAgent::AddAgent("CMailbox::CleanUp();", "mail", "N", 60*60*24);

			return true;
		}
	}

	/**
	 * @param $siteId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function installMailService($siteId)
	{
		$filter = [
			'ACTIVE' => 'Y',
			'SITE_ID' => $siteId
		];
		$result = Bitrix\Mail\MailServicesTable::getList(array('filter' => $filter));
		if ($result->fetch() === false)
		{
			\Bitrix\Mail\Internals\MailServiceInstaller::installServices($siteId);
		}
	}

	public function installBitrix24MailService()
	{
		if (CModule::IncludeModule("mail"))
		{
			$result = \Bitrix\Main\SiteTable::getList();
			while (($site = $result->fetch()) !== false)
			{
				if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite($site['LID']))
					continue;

				\Bitrix\Mail\MailServicesTable::add(array(
					'SITE_ID'      => $site['LID'],
					'ACTIVE'       => 'Y',
					'NAME'         => 'bitrix24',
					'SERVICE_TYPE' => 'controller'
				));
			}
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/db/".$connection->getType()."/uninstall.sql");

			if (\Bitrix\Main\Loader::includeModule('mail'))
			{
				\Bitrix\Mail\MailMessageTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT', false);
			}
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'mail', 'CMailRestService', 'OnRestServiceBuildDescription');

		$eventManager->unRegisterEventHandler('main', 'OnAfterUserUpdate', 'mail', 'CMail', 'onUserUpdate');
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserDelete', 'mail', 'CMail', 'onUserDelete');

		$eventManager->unRegisterEventHandler('main', 'OnBeforeSiteUpdate', 'mail', 'Bitrix\\Mail\\User', 'handleSiteUpdate');
		$eventManager->unRegisterEventHandler('main', 'OnAfterSetOption_server_name', 'mail', 'Bitrix\\Mail\\User', 'handleServerNameUpdate');

		$eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'mail', 'Bitrix\\Mail\\MessageUserType', 'getUserTypeDescription');
		$eventManager->unRegisterEventHandler('main', 'OnMailEventMailRead', 'mail', 'Bitrix\\Mail\\Helper\\MessageEventManager', 'onMailEventMailRead');

		$eventManager->unRegisterEventHandler('main', 'OnUISelectorGetProviderByEntityType', 'mail', '\Bitrix\Mail\Integration\Main\UISelector\Handler', 'OnUISelectorGetProviderByEntityType');
		$eventManager->unRegisterEventHandler('main', 'OnUISelectorFillLastDestination', 'mail', '\Bitrix\Mail\Integration\Main\UISelector\Handler', 'OnUISelectorFillLastDestination');

		$eventManager->unRegisterEventHandler('mail', 'onMailMessageNew', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onMailMessageNew');

		$eventManager->unRegisterEventHandler('im', 'OnGetNotifySchema', 'mail', '\Bitrix\Mail\Integration\Im\Notification', 'getSchema');

		$eventManager->unRegisterEventHandler('mail', 'onMailMessageNew', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onMailMessageNew');
		$eventManager->unRegisterEventHandler('calendar', 'OnAfterCalendarEventDelete', 'mail', '\Bitrix\Mail\Integration\Calendar\ICal\ICalMailEventManager', 'onUnbindEvent');

		$eventManager->unRegisterEventHandler('mobile', 'onRequestSyncMail', 'mail', '\Bitrix\Mail\Integration\SyncRequest', 'onRequestSyncMail');

		$eventManager->unRegisterEventHandler('ai', 'onTuningLoad', 'mail', '\Bitrix\Mail\Integration\AI\EventHandler', 'onTuningLoad');
		$eventManager->unRegisterEventHandler('ai', 'onContextGetMessages', 'mail', '\Bitrix\Mail\Integration\AI\Controller', 'onContextGetMessages');

		//delete agents
		CAgent::RemoveModuleAgents("mail");
		
		UnRegisterModule("mail");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/mail", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/templates", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/mail/");//icons
		DeleteDirFilesEx("/bitrix/images/mail/");//images
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;

		if(!CBXFeatures::IsFeatureEditable("SMTP"))
		{
			$APPLICATION->ThrowException(Loc::getMessage("MAIN_FEATURE_ERROR_EDITABLE"));
		}
		else
		{
			$this->InstallFiles();
			$this->InstallDB();
			CBXFeatures::SetFeatureEnabled("SMTP", true);
		}
		$APPLICATION->IncludeAdminFile(Loc::getMessage("MAIL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/step1.php");
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step;
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("MAIL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			CBXFeatures::SetFeatureEnabled("SMTP", false);
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("MAIL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/unstep2.php");
		}

		UnRegisterModuleDependences("pull", "OnGetDependentModule", "mail", "\\Bitrix\\Mail\\MailPullSchema", "OnGetDependentModule" );
		UnRegisterModuleDependences('tasks', 'OnTaskDelete', 'mail', '\\Bitrix\\Mail\\Integration\\Intranet\\Secretary', 'onTaskDelete');
	}

	public function migrateToBox()
	{
		COption::SetOptionString('mail', 'disable_log', 'N');
	}
}
