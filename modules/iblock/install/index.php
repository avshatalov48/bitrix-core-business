<?php

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class iblock extends CModule
{
	var $MODULE_ID = "iblock";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	var $errors;

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("IBLOCK_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IBLOCK_INSTALL_DESCRIPTION");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		if(!$DB->Query("SELECT 'x' FROM b_iblock_type", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/db/mysql/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		ModuleManager::registerModule("iblock");
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible("main", "OnGroupDelete", "iblock", "CIBlock", "OnGroupDelete");
		$eventManager->registerEventHandlerCompatible("main", "OnBeforeLangDelete", "iblock", "CIBlock", "OnBeforeLangDelete");
		$eventManager->registerEventHandlerCompatible("main", "OnLangDelete", "iblock", "CIBlock", "OnLangDelete");
		$eventManager->registerEventHandlerCompatible("main", "OnUserTypeRightsCheck", "iblock", "CIBlockSection", "UserTypeRightsCheck");
		$eventManager->registerEventHandlerCompatible("search", "OnReindex", "iblock", "CIBlock", "OnSearchReindex");
		$eventManager->registerEventHandlerCompatible("search", "OnSearchGetURL", "iblock", "CIBlock", "OnSearchGetURL");
		$eventManager->registerEventHandlerCompatible("main", "OnEventLogGetAuditTypes", "iblock", "CIBlock", "GetAuditTypes");
		$eventManager->registerEventHandlerCompatible("main", "OnEventLogGetAuditHandlers", "iblock", "CEventIBlock", "MakeIBlockObject");
		$eventManager->registerEventHandlerCompatible("main", "OnGetRatingContentOwner", "iblock", "CRatingsComponentsIBlock", "OnGetRatingContentOwner", 200);
		$eventManager->registerEventHandlerCompatible("main", "OnTaskOperationsChanged", "iblock", "CIBlockRightsStorage", "OnTaskOperationsChanged");
		$eventManager->registerEventHandlerCompatible("main", "OnGroupDelete", "iblock", "CIBlockRightsStorage", "OnGroupDelete");
		$eventManager->registerEventHandlerCompatible("main", "OnUserDelete", "iblock", "CIBlockRightsStorage", "OnUserDelete");
		$eventManager->registerEventHandlerCompatible("perfmon", "OnGetTableSchema", "iblock", "iblock", "OnGetTableSchema");
		$eventManager->registerEventHandlerCompatible("sender", "OnConnectorList", "iblock", "\\Bitrix\\Iblock\\SenderEventHandler", "onConnectorListIblock");

		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyDate", "GetUserTypeDescription", 10);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyDateTime", "GetUserTypeDescription", 20);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyXmlID", "GetUserTypeDescription", 30);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyFileMan", "GetUserTypeDescription", 40);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyHTML", "GetUserTypeDescription", 50);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyElementList", "GetUserTypeDescription", 60);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySequence", "GetUserTypeDescription", 70);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyElementAutoComplete", "GetUserTypeDescription", 80);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySKU", "GetUserTypeDescription", 90);
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySectionAutoComplete", "GetUserTypeDescription", 100);

		$eventManager->registerEventHandler("main", "onVirtualClassBuildList", "iblock", \Bitrix\Iblock\IblockTable::class, "compileAllEntities");
		//$eventManager->registerEventHandler("landing", "OnBuildSourceList", "iblock", "\\Bitrix\\Iblock\\LandingSource\\Element", "onBuildSourceListHandler");

		unset($eventManager);

		$this->InstallTasks();

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		$arSQLErrors = array();

		if(Loader::includeModule("search"))
			CSearch::DeleteIndex("iblock");
		if(!Loader::includeModule("iblock"))
			return false;

		$arSql = $arErr = array();
		if(!array_key_exists("savedata", $arParams) || ($arParams["savedata"] != "Y"))
		{
			$rsIBlock = CIBlock::GetList(array("ID"=>"ASC"), array(), false);
			while ($arIBlock = $rsIBlock->Fetch())
			{
				if($arIBlock["VERSION"] == 2)
				{
					$arSql[] = "DROP TABLE b_iblock_element_prop_s".$arIBlock["ID"];
					$arSql[] = "DROP TABLE b_iblock_element_prop_m".$arIBlock["ID"];
				}
				$GLOBALS["USER_FIELD_MANAGER"]->OnEntityDelete("IBLOCK_".$arIBlock["ID"]."._SECTION");
			}

			foreach($arSql as $strSql)
			{
				if(!$DB->Query($strSql, true))
					$arSQLErrors[] = "<hr><pre>Query:\n".$strSql."\n\nError:\n<span style=\"color: red;\">".$DB->db_Error."</span></pre>";
			}

			$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'iblock'");
			while($arRes = $db_res->Fetch())
				CFile::Delete($arRes["ID"]);

			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/db/mysql/uninstall.sql");

			$this->UnInstallTasks();
		}

		if(is_array($this->errors))
			$arSQLErrors = array_merge($arSQLErrors, $this->errors);

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("main", "OnGroupDelete", "iblock", "CIBlock", "OnGroupDelete");
		$eventManager->unRegisterEventHandler("main", "OnBeforeLangDelete", "iblock", "CIBlock", "OnBeforeLangDelete");
		$eventManager->unRegisterEventHandler("main", "OnLangDelete", "iblock", "CIBlock", "OnLangDelete");
		$eventManager->unRegisterEventHandler("main", "OnUserTypeRightsCheck", "iblock", "CIBlockSection", "UserTypeRightsCheck");
		$eventManager->unRegisterEventHandler("search", "OnReindex", "iblock", "CIBlock", "OnSearchReindex");
		$eventManager->unRegisterEventHandler("search", "OnSearchGetURL", "iblock", "CIBlock", "OnSearchGetURL");
		$eventManager->unRegisterEventHandler("main", "OnEventLogGetAuditTypes", "iblock", "CIBlock", "GetAuditTypes");
		$eventManager->unRegisterEventHandler("main", "OnEventLogGetAuditHandlers", "iblock", "CEventIBlock", "MakeIBlockObject");
		$eventManager->unRegisterEventHandler("main", "OnGetRatingContentOwner", "iblock", "CRatingsComponentsIBlock", "OnGetRatingContentOwner");
		$eventManager->unRegisterEventHandler("main", "OnTaskOperationsChanged", "iblock", "CIBlockRightsStorage", "OnTaskOperationsChanged");
		$eventManager->unRegisterEventHandler("main", "OnGroupDelete", "iblock", "CIBlockRightsStorage", "OnGroupDelete");
		$eventManager->unRegisterEventHandler("main", "OnUserDelete", "iblock", "CIBlockRightsStorage", "OnUserDelete");
		$eventManager->unRegisterEventHandler("perfmon", "OnGetTableSchema", "iblock", "iblock", "OnGetTableSchema");
		$eventManager->unRegisterEventHandler("sender", "OnConnectorList", "iblock", "\\Bitrix\\Iblock\\SenderEventHandler", "onConnectorListIblock");

		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyDate", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyDateTime", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyXmlID", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyFileMan", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyHTML", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyElementList", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySequence", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertyElementAutoComplete", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySKU", "GetUserTypeDescription");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "iblock", "CIBlockPropertySectionAutoComplete", "GetUserTypeDescription");

		$eventManager->unregisterEventHandler("main", "onVirtualClassBuildList", "iblock", \Bitrix\Iblock\IblockTable::class, "compileAllEntities");
		//$eventManager->unRegisterEventHandler("landing", "OnBuildSourceList", "iblock", "\\Bitrix\\Iblock\\LandingSource\\Element", "onBuildSourceListHandler");
		unset($eventManager);

		ModuleManager::unRegisterModule("iblock");

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

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/install/admin', $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/iblock", true, true);
			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php"))
				@copy($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/rss.php");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFilesEx("/bitrix/images/iblock/");//images
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/panel/iblock/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel/iblock/");//css sku
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/tools/iblock/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/iblock/");
			DeleteDirFilesEx("/bitrix/themes/.default/icons/iblock/");//icons
			DeleteDirFilesEx("/bitrix/js/iblock/");//javascript
		}
		return true;
	}


	function DoInstall()
	{
		global $APPLICATION, $step, $obModule;
		$step = intval($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/step1.php");
		elseif($step==2)
		{
			if($this->InstallDB())
			{
				$this->InstallFiles();
			}
			$obModule = $this;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step, $obModule;
		$step = intval($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/unstep1.php");
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$GLOBALS["CACHE_MANAGER"]->CleanAll();
			$this->UnInstallFiles();
			$obModule = $this;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/unstep2.php");
		}
	}

	function GetModuleTasks()
	{
		return array(
			'iblock_deny' => array(
				'LETTER' => 'D',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
				)
			),
			'iblock_read' => array(
				'LETTER' => 'R',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'section_read',
					'element_read'
				)
			),
			'iblock_element_add' => array(
				'LETTER' => 'E',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'section_element_bind'
				)
			),
			'iblock_admin_read' => array(
				'LETTER' => 'S',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'iblock_admin_display',
					'section_read',
					'element_read'
				)
			),
			'iblock_admin_add' => array(
				'LETTER' => 'T',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'iblock_admin_display',
					'section_read',
					'section_element_bind',
					'element_read',
				)
			),
			'iblock_limited_edit' => array(
				'LETTER' => 'U',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'iblock_admin_display',
					'section_read',
					'section_element_bind',
					'element_read',
					'element_edit',
					'element_edit_price',
					'element_delete',
					'element_bizproc_start'
				)
			),
			'iblock_full_edit' => array(
				'LETTER' => 'W',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'iblock_admin_display',
					'section_read',
					'section_edit',
					'section_delete',
					'section_element_bind',
					'section_section_bind',
					'element_read',
					'element_edit',
					'element_edit_price',
					'element_delete',
					'element_edit_any_wf_status',
					'element_bizproc_start'
				)
			),
			'iblock_full' => array(
				'LETTER' => 'X',
				'BINDING' => 'iblock',
				'OPERATIONS' => array(
					'iblock_admin_display',
					'iblock_edit',
					'iblock_delete',
					'iblock_rights_edit',
					'iblock_export',
					'section_read',
					'section_edit',
					'section_delete',
					'section_element_bind',
					'section_section_bind',
					'section_rights_edit',
					'element_read',
					'element_edit',
					'element_edit_price',
					'element_delete',
					'element_edit_any_wf_status',
					'element_bizproc_start',
					'element_rights_edit'
				)
			)
		);
	}

	public static function OnGetTableSchema()
	{
		return array(
			"iblock" => array(
				"b_iblock_type" => array(
					"ID" => array(
						"b_iblock_type_lang" => "IBLOCK_TYPE_ID",
						"b_iblock" => "IBLOCK_TYPE_ID",
					)
				),
				"b_iblock" => array(
					"ID" => array(
						"b_iblock_site" => "IBLOCK_ID",
						"b_iblock_messages" => "IBLOCK_ID",
						"b_iblock_fields" => "IBLOCK_ID",
						"b_iblock_property" => "IBLOCK_ID",
						"b_iblock_property^" => "LINK_IBLOCK_ID",
						"b_iblock_section" => "IBLOCK_ID",
						"b_iblock_element" => "IBLOCK_ID",
						"b_iblock_group" => "IBLOCK_ID",
						"b_iblock_right" => "IBLOCK_ID",
						"b_iblock_section_right" => "IBLOCK_ID",
						"b_iblock_element_right" => "IBLOCK_ID",
						"b_iblock_rss" => "IBLOCK_ID",
						"b_iblock_sequence" => "IBLOCK_ID",
						"b_iblock_offers_tmp" => "PRODUCT_IBLOCK_ID",
						"b_iblock_offers_tmp^" => "OFFERS_IBLOCK_ID",
						"b_iblock_right^" => "ENTITY_ID",
						"b_iblock_section_property" => "IBLOCK_ID",
						"b_iblock_iblock_iprop" => "IBLOCK_ID",
						"b_iblock_section_iprop" => "IBLOCK_ID",
						"b_iblock_element_iprop" => "IBLOCK_ID",
					)
				),
				"b_iblock_section" => array(
					"ID" => array(
						"b_iblock_section" => "IBLOCK_SECTION_ID",
						"b_iblock_element" => "IBLOCK_SECTION_ID",
						"b_iblock_right" => "ENTITY_ID",
						"b_iblock_section_right" => "SECTION_ID",
						"b_iblock_element_right" => "SECTION_ID",
						"b_iblock_section_element" => "IBLOCK_SECTION_ID",
						"b_iblock_section_property" => "SECTION_ID",
						"b_iblock_section_iprop" => "SECTION_ID",
						"b_iblock_element_iprop" => "SECTION_ID",
					)
				),
				"b_iblock_element" => array(
					"ID" => array(
						"b_iblock_element" => "WF_PARENT_ELEMENT_ID",
						"b_iblock_element_property" => "IBLOCK_ELEMENT_ID",
						"b_iblock_right" => "ENTITY_ID",
						"b_iblock_element_right" => "ELEMENT_ID",
						"b_iblock_section_element" => "IBLOCK_ELEMENT_ID",
						"b_iblock_element_iprop" => "ELEMENT_ID",
					)
				),
				"b_iblock_property" => array(
					"ID" => array(
						"b_iblock_element_property" => "IBLOCK_PROPERTY_ID",
						"b_iblock_property_enum" => "PROPERTY_ID",
						"b_iblock_section_element" => "ADDITIONAL_PROPERTY_ID",
						"b_iblock_section_property" => "PROPERTY_ID",
					)
				),
				"b_iblock_right" => array(
					"ID" => array(
						"b_iblock_section_right" => "RIGHT_ID",
						"b_iblock_element_right" => "RIGHT_ID",
					)
				),
				"b_iblock_iproperty" => array(
					"ID" => array(
						"b_iblock_iblock_iprop" => "IPROP_ID",
						"b_iblock_section_iprop" => "IPROP_ID",
						"b_iblock_element_iprop" => "IPROP_ID",
					)
				),
			),
			"main" => array(
				"b_file" => array(
					"ID" => array(
						"b_iblock" => "PICTURE",
						"b_iblock_section" => "PICTURE",
						"b_iblock_section^" => "DETAIL_PICTURE",
						"b_iblock_element" => "PREVIEW_PICTURE",
						"b_iblock_element^" => "DETAIL_PICTURE",
					)
				),
				"b_lang" => array(
					"LID" => array(
						"b_iblock" => "LID",
						"b_iblock_site" => "SITE_ID",
					)
				),
				"b_user" => array(
					"ID" => array(
						"b_iblock_section" => "MODIFIED_BY",
						"b_iblock_section^" => "CREATED_BY",
						"b_iblock_element" => "MODIFIED_BY",
						"b_iblock_element^" => "CREATED_BY",
						"b_iblock_element^^" => "WF_LOCKED_BY",
						"b_iblock_element_lock" => "LOCKED_BY",
					)
				),
				"b_group" => array(
					"ID" => array(
						"b_iblock_group" => "GROUP_ID",
					)
				),
				"b_task" => array(
					"ID" => array(
						"b_iblock_right" => "TASK_ID",
						"b_task_operation" => "TASK_ID",
					)
				),
				"b_operation" => array(
					"ID" => array(
						"b_task_operation" => "OPERATION_ID",
					)
				),
			),
			"socialnetwork" => array(
				"b_sonet_group" => array(
					"ID" => array(
						"b_iblock" => "SOCNET_GROUP_ID",
						"b_iblock_section" => "SOCNET_GROUP_ID",
					)
				),
			),
		);
	}
}