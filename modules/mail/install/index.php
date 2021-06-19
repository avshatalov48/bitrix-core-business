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
		else
		{
			$this->MODULE_VERSION = MAIL_VERSION;
			$this->MODULE_VERSION_DATE = MAIL_VERSION_DATE;
		}

		$this->MODULE_NAME = Loc::getMessage("MAIL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("MAIL_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_mail_mailbox WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/db/".mb_strtolower($DB->type)."/install.sql");

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

			RegisterModule("mail");

			if (CModule::IncludeModule("mail"))
			{
				if (mb_strtolower($DB->type) == 'mysql')
				{
					$errors = $DB->runSqlBatch(sprintf(
						'%s/bitrix/modules/mail/install/db/%s/install_ft.sql',
						$_SERVER['DOCUMENT_ROOT'],
						mb_strtolower($DB->type)
					));
					if ($errors === false)
					{
						\Bitrix\Mail\MailMessageTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
					}
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
			$mailServices = array(
				'gmail' => array(
					'SERVER' => 'imap.gmail.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'https://mail.google.com/',
					'SMTP_SERVER' => 'smtp.gmail.com',
					'SMTP_PORT' => 465,
					'SMTP_ENCRYPTION' => 'Y',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'N',
				),
				'icloud' => array(
					'SERVER' => 'imap.mail.me.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'https://www.icloud.com/#mail',
					'SMTP_SERVER' => 'smtp.mail.me.com',
					'SMTP_PORT' => 587,
					'SMTP_ENCRYPTION' => 'N',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'Y',
				),
				'outlook.com' => array(
					'SERVER' => 'imap-mail.outlook.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'https://www.outlook.com/owa',
					'SMTP_SERVER' => 'smtp-mail.outlook.com',
					'SMTP_PORT' => 587,
					'SMTP_ENCRYPTION' => 'N',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'Y',
				),
				'office365' => array(
					'SERVER' => 'outlook.office365.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'http://mail.office365.com/',
					'SMTP_SERVER' => 'smtp.office365.com',
					'SMTP_PORT' => 587,
					'SMTP_ENCRYPTION' => 'N',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'N',
				),
				'yahoo' => array(
					'SERVER' => 'imap.mail.yahoo.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'http://mail.yahoo.com/',
					'SMTP_SERVER' => 'smtp.mail.yahoo.com',
					'SMTP_PORT' => 465,
					'SMTP_ENCRYPTION' => 'Y',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'N',
				),
				'aol' => array(
					'SERVER' => 'imap.aol.com',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'http://mail.aol.com/',
					'SMTP_SERVER' => 'smtp.aol.com',
					'SMTP_PORT' => 465,
					'SMTP_ENCRYPTION' => 'Y',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'Y',
				),
				'yandex' => array(
					'SERVER' => 'imap.yandex.ru',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'https://mail.yandex.ru/',
					'SMTP_SERVER' => 'smtp.yandex.ru',
					'SMTP_PORT' => 465,
					'SMTP_ENCRYPTION' => 'Y',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'Y',
				),
				'mail.ru' => array(
					'SERVER' => 'imap.mail.ru',
					'PORT' => 993,
					'ENCRYPTION' => 'Y',
					'LINK' => 'http://e.mail.ru/',
					'SMTP_SERVER' => 'smtp.mail.ru',
					'SMTP_PORT' => 465,
					'SMTP_ENCRYPTION' => 'Y',
					'SMTP_LOGIN_AS_IMAP' => 'Y',
					'SMTP_PASSWORD_AS_IMAP' => 'Y',
					'UPLOAD_OUTGOING' => 'Y',
				),
				'exchange' => array(),
				'other' => array(),
			);

			$mailServicesByLang = array(
				'ru' => array(
					100  => 'gmail',
					200  => 'outlook.com',
					300  => 'icloud',
					400  => 'office365',
					500  => 'exchange',
					600  => 'yahoo',
					700  => 'aol',
					800  => 'yandex',
					900  => 'mail.ru',
					1000 => 'other',
				),
				'ua' => array(
					100  => 'gmail',
					200  => 'outlook.com',
					300  => 'icloud',
					400  => 'office365',
					500  => 'exchange',
					600  => 'yahoo',
					700  => 'aol',
					800  => 'other',
				),
				'en' => array(
					100 => 'gmail',
					200 => 'outlook.com',
					300 => 'icloud',
					400 => 'office365',
					500 => 'exchange',
					600 => 'yahoo',
					700 => 'aol',
					800 => 'other'
				),
				'de' => array(
					100 => 'gmail',
					200 => 'outlook.com',
					300 => 'icloud',
					400 => 'office365',
					500 => 'exchange',
					600 => 'yahoo',
					700 => 'aol',
					800 => 'other'
				)
			);

			$site = \Bitrix\Main\SiteTable::getList(array('filter' => ["=LID" => $siteId]))
				->fetch();

			if (!$site)
				return;

			if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite($site['LID']))
				return;

			$portalZone = \Bitrix\Main\Loader::includeModule('bitrix24') ? \CBitrix24::getPortalZone() : $site['LANGUAGE_ID'];

			$mailServicesList = isset($mailServicesByLang[$portalZone])
				? $mailServicesByLang[$portalZone]
				: $mailServicesByLang['en'];
			foreach ($mailServicesList as $serviceSort => $serviceName)
			{
				$serviceSettings = $mailServices[$serviceName];

				$serviceSettings['SITE_ID']      = $site['LID'];
				$serviceSettings['ACTIVE']       = 'Y';
				$serviceSettings['SERVICE_TYPE'] = 'imap';
				$serviceSettings['NAME']         = $serviceName;
				$serviceSettings['SORT']         = $serviceSort;

				Bitrix\Mail\MailServicesTable::add($serviceSettings);
			}
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
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/install/db/".mb_strtolower($DB->type)."/uninstall.sql");

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
	}

	public function migrateToBox()
	{
		COption::SetOptionString('mail', 'disable_log', 'N');
	}
}
