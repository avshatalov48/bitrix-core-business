<?
IncludeModuleLangFile(__FILE__);

if(class_exists("lists")) return;
Class lists extends CModule
{
	var $MODULE_ID = "lists";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	var $errors = false;

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("LISTS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("LISTS_MODULE_DESCRIPTION");
	}

	function InstallDB($arParams = array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CDatabase $DB */
		global $DB;

		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_lists_permission WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/db/mysql/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("lists");
			CModule::IncludeModule("lists");
			RegisterModuleDependences("iblock", "OnAfterIBlockUpdate", "lists", "CLists", "OnAfterIBlockUpdate");
			RegisterModuleDependences("iblock", "OnIBlockDelete", "lists", "CLists", "OnIBlockDelete");
			RegisterModuleDependences("iblock", "OnAfterIBlockDelete", "lists", "CLists", "OnAfterIBlockDelete");
			RegisterModuleDependences("iblock", "CIBlockDocument_OnGetDocumentAdminPage", "lists", "CList", "OnGetDocumentAdminPage");
			RegisterModuleDependences("intranet", "OnSharepointCreateProperty", "lists", "CLists", "OnSharepointCreateProperty");
			RegisterModuleDependences("intranet", "OnSharepointCheckAccess", "lists", "CLists", "OnSharepointCheckAccess");
			RegisterModuleDependences("perfmon", "OnGetTableSchema", "lists", "lists", "OnGetTableSchema");
			RegisterModuleDependences("search", "OnSearchGetURL", "lists", "CList", "OnSearchGetURL", 50);
			RegisterModuleDependences('socialnetwork', 'OnFillSocNetLogEvents', 'lists', 'CListsLiveFeed', 'onFillSocNetLogEvents');
			RegisterModuleDependences("socialnetwork", "OnFillSocNetAllowedSubscribeEntityTypes", "lists", "CListsLiveFeed", "onFillSocNetAllowedSubscribeEntityTypes");
			RegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'lists', 'CListsLiveFeed', 'BeforeIndexSocNet');
			RegisterModuleDependences('socialnetwork', 'OnAfterSonetLogEntryAddComment', 'lists', 'CListsLiveFeed', 'OnAfterSonetLogEntryAddComment');
			RegisterModuleDependences('socialnetwork', 'OnForumCommentIMNotify', 'lists', 'CListsLiveFeed', 'OnForumCommentIMNotify');
			RegisterModuleDependences("socialnetwork", "OnSendMentionGetEntityFields", "lists", "CListsLiveFeed", "OnSendMentionGetEntityFields");
			RegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "lists", "CListsLiveFeed", "OnSocNetGroupDelete");
			RegisterModuleDependences('rest', 'onRestServiceBuildDescription', 'lists', '\Bitrix\Lists\Rest\RestService', 'onRestServiceBuildDescription');
			RegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", "lists", "CLists", "OnAfterIBlockElementDelete");
			RegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "lists", "CLists", "OnAfterIBlockPropertyAdd");
			RegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "lists", "CLists", "OnAfterIBlockPropertyUpdate");
			RegisterModuleDependences("iblock", "OnAfterIBlockPropertyDelete", "lists", "CLists", "OnAfterIBlockPropertyDelete");
			RegisterModuleDependences("iblock", "OnBeforeIBlockElementAdd", "lists", "CLists", "OnBeforeIBlockElementAdd");
			RegisterModuleDependences("iblock", "OnBeforeIBlockElementUpdate", "lists", "CLists", "OnBeforeIBlockElementUpdate");
			RegisterModuleDependences("main", "OnGetRatingContentOwner", "lists", "\\Bitrix\\Lists\\Integration\\Main\\RatingVote", "onGetRatingContentOwner");

			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->registerEventHandler('socialnetwork', 'onLogIndexGetContent', 'lists', '\Bitrix\Lists\Integration\Socialnetwork\Log', 'onIndexGetContent');

			if (isset($arParams["INSTALL_DEMO_DATA"]) && $arParams["INSTALL_DEMO_DATA"] == "Y")
			{
				$this->installDemoData();
			}
			return true;
		}
	}

	function installDemoData()
	{
		if(!CModule::IncludeModule("iblock"))
			return;

		$currentPermissions = CLists::GetPermission();
		unset($currentPermissions["bitrix_processes"]);
		unset($currentPermissions["lists"]);

		$socnet_iblock_type_id = COption::GetOptionString("lists", "socnet_iblock_type_id");
		$isSocnetInstalled = IsModuleInstalled('socialnetwork');

		$arTypes = array();
		if (empty($currentPermissions))
		{
			$arTypes[] = array(
				"ID" => "lists",
				"SECTIONS" => "Y",
				"IN_RSS" => "N",
				"SORT" => 80,
				"LANG" => array(),
			);
			$arTypes[] = array(
				"ID" => "bitrix_processes",
				"SECTIONS" => "Y",
				"IN_RSS" => "N",
				"SORT" => 90,
				"LANG" => array(),
			);
		}

		if ($isSocnetInstalled && $socnet_iblock_type_id == '')
		{
			$arTypes[] = array(
				"ID" => "lists_socnet",
				"SECTIONS" => "Y",
				"IN_RSS" => "N",
				"SORT" => 83,
				"LANG" => array(),
			);
		}

		$arLanguages = array();
		if (!empty($arTypes))
		{
			$rsLanguage = CLanguage::GetList();
			while ($arLanguage = $rsLanguage->Fetch())
			{
				$arLanguages[] = $arLanguage["LID"];
			}
		}

		foreach ($arTypes as $arType)
		{
			$dbType = CIBlockType::GetList(array(), array("=ID" => $arType["ID"]));
			if (!$dbType->Fetch())
			{
				foreach($arLanguages as $languageID)
				{
					IncludeModuleLangFile(__FILE__, $languageID);
					$code = mb_strtoupper($arType["ID"]);
					$arType["LANG"][$languageID]["NAME"] = GetMessage($code."_TYPE_NAME");
					$arType["LANG"][$languageID]["ELEMENT_NAME"] = GetMessage($code."_ELEMENT_NAME");
					if ($arType["SECTIONS"] == "Y")
						$arType["LANG"][$languageID]["SECTION_NAME"] = GetMessage($code."_SECTION_NAME");
				}
				$iblockType = new CIBlockType;
				$iblockType->Add($arType);
			}
		}

		if (empty($currentPermissions))
		{
			CLists::SetPermission('lists', array(1));
			CLists::SetPermission('bitrix_processes', array(1));
		}

		$defaultLang = "en";
		if(IsModuleInstalled("bitrix24"))
		{
			$gr = COption::GetOptionString("main", "~controller_group_name", "");
			if($gr != "")
				$defaultLang = mb_substr($gr, 0, 2);
			if($defaultLang == "ua")
				$defaultLang = "ru";
		}
		else
		{
			$defaultSiteId = CSite::GetDefSite();
			$siteObject = CSite::GetByID($defaultSiteId);
			$site = $siteObject->fetch();
			$defaultLang = $site ? $site['LANGUAGE_ID'] : "en";
			if($defaultLang == "ua")
				$defaultLang = "ru";
		}
		\Bitrix\Lists\Importer::installProcesses($defaultLang);
		\Bitrix\Main\Config\Option::set("lists", "livefeed_url", "/bizproc/processes/");

		if ($isSocnetInstalled && $socnet_iblock_type_id == '')
		{
			COption::SetOptionString("lists", "socnet_iblock_type_id", "lists_socnet");
			CLists::EnableSocnet(true);
		}
	}

	function UnInstallDB($arParams = array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CDatabase $DB */
		global $DB;

		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/db/mysql/uninstall.sql");
		}

		UnRegisterModuleDependences("iblock", "OnAfterIBlockUpdate", "lists", "CLists", "OnAfterIBlockUpdate");
		UnRegisterModuleDependences("iblock", "OnIBlockDelete", "lists", "CLists", "OnIBlockDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockDelete", "lists", "CLists", "OnAfterIBlockDelete");
		UnRegisterModuleDependences("iblock", "CIBlockDocument_OnGetDocumentAdminPage", "lists", "CList", "OnGetDocumentAdminPage");
		UnRegisterModuleDependences("intranet", "OnSharepointCreateProperty", "lists", "CLists", "OnSharepointCreateProperty");
		UnRegisterModuleDependences("intranet", "OnSharepointCheckAccess", "lists", "CLists", "OnSharepointCheckAccess");
		UnRegisterModuleDependences("search", "OnSearchGetURL", "lists", "CList", "OnSearchGetURL");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "lists", "lists", "OnGetTableSchema");
		UnRegisterModuleDependences('socialnetwork', 'OnFillSocNetLogEvents', 'lists', 'CListsLiveFeed', 'onFillSocNetLogEvents');
		UnRegisterModuleDependences("socialnetwork", "OnFillSocNetAllowedSubscribeEntityTypes", "lists", "CListsLiveFeed", "onFillSocNetAllowedSubscribeEntityTypes");
		UnRegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'lists', 'CListsLiveFeed', 'BeforeIndexSocNet');
		UnRegisterModuleDependences('socialnetwork', 'OnAfterSonetLogEntryAddComment', 'lists', 'CListsLiveFeed', 'OnAfterSonetLogEntryAddComment');
		UnRegisterModuleDependences('socialnetwork', 'OnForumCommentIMNotify', 'lists', 'CListsLiveFeed', 'OnForumCommentIMNotify');
		UnRegisterModuleDependences("socialnetwork", "OnSendMentionGetEntityFields", "lists", "CListsLiveFeed", "OnSendMentionGetEntityFields");
		UnRegisterModuleDependences("socialnetwork", "OnSocNetGroupDelete", "lists", "CListsLiveFeed", "OnSocNetGroupDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", "lists", "CLists", "OnAfterIBlockElementDelete");
		UnRegisterModuleDependences('rest', 'onRestServiceBuildDescription', 'lists', '\Bitrix\Lists\Rest\RestService', 'onRestServiceBuildDescription');
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyAdd", "lists", "CLists", "OnAfterIBlockPropertyAdd");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyUpdate", "lists", "CLists", "OnAfterIBlockPropertyUpdate");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockPropertyDelete", "lists", "CLists", "OnAfterIBlockPropertyDelete");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementAdd", "lists", "CLists", "OnBeforeIBlockElementAdd");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementUpdate", "lists", "CLists", "OnBeforeIBlockElementUpdate");
		UnRegisterModuleDependences("main", "OnGetRatingContentOwner", "lists", "\\Bitrix\\Lists\\Integration\\Main\\RatingVote", "onGetRatingContentOwner");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler('socialnetwork', 'onLogIndexGetContent', 'lists', '\Bitrix\Lists\Integration\Socialnetwork\Log', 'onIndexGetContent');

		UnRegisterModule("lists");

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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/lists", True, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/activities", $_SERVER["DOCUMENT_ROOT"]."/bitrix/activities", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFilesEx("/bitrix/images/lists/");
			DeleteDirFilesEx("/bitrix/js/lists/");
		}
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $USER, $step;
		$step = intval($step);

		if(!$USER->IsAdmin())
			return;

		if(!CBXFeatures::IsFeatureEditable("Lists"))
		{
			$this->errors = array(GetMessage("MAIN_FEATURE_ERROR_EDITABLE"));
			$APPLICATION->ThrowException(implode("<br>", $this->errors));

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LISTS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/step2.php");
		}
		elseif($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("LISTS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/step1.php");
		}
		elseif($step==2)
		{
			$this->InstallDB(array());
			$this->InstallFiles(array());
			CBXFeatures::SetFeatureEnabled("Lists", true);

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LISTS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $USER, $step;
		if($USER->IsAdmin())
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("LISTS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				$this->UnInstallFiles();
				CBXFeatures::SetFeatureEnabled("Lists", false);
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("LISTS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/lists/install/unstep2.php");
			}
		}
	}

	public static function OnGetTableSchema()
	{
		return array(
			"iblock" => array(
				"b_iblock_type" => array(
					"ID" => array(
						"b_lists_permission" => "IBLOCK_TYPE_ID",
					)
				),
				"b_iblock" => array(
					"ID" => array(
						"b_lists_field" => "IBLOCK_ID",
						"b_lists_socnet_group" => "IBLOCK_ID",
						"b_lists_url" => "IBLOCK_ID",
					)
				),
			),
			"main" => array(
				"b_group" => array(
					"ID" => array(
						"b_lists_permission" => "GROUP_ID",
					)
				),
			),
		);
	}
}
?>