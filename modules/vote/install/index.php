<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = mb_substr($PathInstall, 0, mb_strlen($PathInstall) - mb_strlen("/index.php"));
IncludeModuleLangFile($PathInstall."/install.php");
IncludeModuleLangFile(__FILE__);

if(class_exists("vote")) return;
Class vote extends CModule
{
	var $MODULE_ID = "vote";
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
		else
		{
			$this->MODULE_VERSION = VOTE_VERSION;
			$this->MODULE_VERSION_DATE = VOTE_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("VOTE_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("VOTE_MODULE_DESCRIPTION");
		$this->MODULE_CSS = "/bitrix/modules/vote/vote.css";
	}

	function InstallUserFields($moduleId = "all")
	{}

	function UnInstallUserFields()
	{
		$ent = new CUserTypeEntity;
		foreach(array("vote") as $type)
		{
			$rsData = CUserTypeEntity::GetList(array("ID" => "ASC"), array("USER_TYPE_ID" => $type));
			if ($rsData && ($arRes = $rsData->Fetch()))
			{
				do {
					$ent->Delete($arRes['ID']);
				} while ($arRes = $rsData->Fetch());
			}
		}
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_vote WHERE 1=0", true))
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/db/mysql/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{

			COption::SetOptionString("vote", "VOTE_DIR", "");
			COption::SetOptionString("vote", "VOTE_COMPATIBLE_OLD_TEMPLATE", "N");

			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->registerEventHandlerCompatible("main", "OnBeforeProlog", "main", "", "", 10, "/modules/vote/keepvoting.php");
			$eventManager->registerEventHandlerCompatible("main", "OnUserTypeBuildList", "vote", "Bitrix\\Vote\\Uf\\VoteUserType", "getUserTypeDescription", 200);
			$eventManager->registerEventHandlerCompatible("main", "OnUserLogin", "vote", "Bitrix\\Vote\\User", "onUserLogin", 200);
			$eventManager->registerEventHandlerCompatible("im", "OnGetNotifySchema", "vote", "CVoteNotifySchema", "OnGetNotifySchema");

			RegisterModule("vote");
			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->UnInstallUserFields();
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/db/mysql/uninstall.sql");
		}

		//delete agents
		CAgent::RemoveModuleAgents("vote");

		$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'vote'");
		while($arRes = $db_res->Fetch())
			CFile::Delete($arRes["ID"]);

		// Events
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/events/del_events.php");

		COption::RemoveOption("vote");

		UnRegisterModuleDependences("im", "OnGetNotifySchema", "vote", "CVoteNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences("main", "OnUserLogin", "vote", "Bitrix\\Vote\\User", "onUserLogin");
		UnRegisterModuleDependences("main", "OnUserTypeBuildList", "vote", "Bitrix\\Vote\\Uf\\VoteUserType", "getUserTypeDescription");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", "/modules/vote/keepvoting.php");
		UnRegisterModule("vote");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{
		global $DB;
		$sIn = "'VOTE_FOR'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'VOTE_NEW', 'VOTE_FOR'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles($arParams = array())
	{
		global $DB;

		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/vote");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/vote", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/vote", true, true);
		}

		$bReWriteAdditionalFiles = (($GLOBALS["public_rewrite"] == "Y") ? True : False);

		if($GLOBALS["install_public"] == "Y" && !empty($GLOBALS["public_dir"]))
		{
			$sites = CLang::GetList('', '', Array("ACTIVE"=>"Y"));
			while($site = $sites->Fetch())
			{
				if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/public/".$site["LANGUAGE_ID"]))
					CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/public/".$site["LANGUAGE_ID"], $site['ABS_DOC_ROOT'].$site["DIR"].$GLOBALS["public_dir"], $bReWriteAdditionalFiles, true);
			}
		}

		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
			DeleteDirFilesEx("/bitrix/themes/.default/icons/vote/");//icons
			DeleteDirFilesEx("/bitrix/themes/.default/start_menu/vote/");
			DeleteDirFilesEx("/bitrix/images/vote/");//images
			DeleteDirFilesEx("/bitrix/js/vote/");//js
			DeleteDirFilesEx("/bitrix/tools/vote");
			$children = (new \Bitrix\Main\IO\Directory($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/components/bitrix/"))->getChildren();
			foreach ($children as $componentDir)
			{
				DeleteDirFilesEx("/bitrix/component/bitrix/".$componentDir->getName());
			}
		}
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
		if ($VOTE_RIGHT=="W")
		{
			$step = intval($step);
			if($step<2)
			{
				$GLOBALS["install_step"] = 1;
				$APPLICATION->IncludeAdminFile(GetMessage("VOTE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/step.php");
			}
			elseif($step==2)
			{
				if($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$GLOBALS["install_step"] = 2;
				$APPLICATION->IncludeAdminFile(GetMessage("VOTE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/step.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step;
		$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
		if ($VOTE_RIGHT=="W")
		{
			if($step < 2)
			{
				$GLOBALS["uninstall_step"] = 1;
				$APPLICATION->IncludeAdminFile(GetMessage("VOTE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/unstep.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				//message types and templates
				if($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$GLOBALS["uninstall_step"] = 2;
				$APPLICATION->IncludeAdminFile(GetMessage("VOTE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/unstep.php");
			}
		}
	}
}
?>