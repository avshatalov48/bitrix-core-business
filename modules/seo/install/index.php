<?php

if(class_exists("seo"))
{
	return;
}

IncludeModuleLangFile(__FILE__);

class seo extends CModule
{
	var $MODULE_ID = "seo";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
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

		$this->MODULE_NAME = GetMessage("SEO_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SEO_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();
		$GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage("SEO_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/step1.php");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_seo_search_engine'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/seo/install/db/' . $connection->getType() . '/install.sql');
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("seo");

		$this->InstallTasks();

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->registerEventHandler('main', 'OnPanelCreate', 'seo', 'CSeoEventHandlers', 'SeoOnPanelCreate');

		if (COption::GetOptionString('main', 'vendor', '') == '1c_bitrix')
		{
			$eventManager->registerEventHandler("fileman", "OnIncludeHTMLEditorScript", "seo", "CSeoEventHandlers", "OnIncludeHTMLEditorScript");
			$eventManager->registerEventHandler("fileman", "OnBeforeHTMLEditorScriptRuns", "seo", "CSeoEventHandlers", "OnBeforeHTMLEditorScriptRuns");
		}

		$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionAdd", "seo", "\\Bitrix\\Seo\\SitemapIblock", "addSection");
		$eventManager->registerEventHandler("iblock", "OnAfterIBlockElementAdd", "seo", "\\Bitrix\\Seo\\SitemapIblock", "addElement");

		$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "deleteSection");
		$eventManager->registerEventHandler("iblock", "OnAfterIBlockElementDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "deleteElement");
		$eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "updateSection");
		$eventManager->registerEventHandler("iblock", "OnAfterIBlockElementUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "updateElement");

		$eventManager->registerEventHandler("forum", "onAfterTopicAdd", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "addTopic");
		$eventManager->registerEventHandler("forum", "onAfterTopicUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "updateTopic");
		$eventManager->registerEventHandler("forum", "onAfterTopicDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "deleteTopic");

		$eventManager->registerEventHandler("main", "OnAdminIBlockElementEdit", "seo", "\\Bitrix\\Seo\\AdvTabEngine", "eventHandler");
		$eventManager->registerEventHandler("main", "OnBeforeProlog", "seo", "\\Bitrix\\Seo\\AdvSession", "checkSession");

		$eventManager->registerEventHandler("sale", "OnOrderSave", "seo", "\\Bitrix\\Seo\\AdvSession", "onOrderSave");
		$eventManager->registerEventHandler("sale", "OnBasketOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onBasketOrder");
		$eventManager->registerEventHandler("sale", "onSalePayOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSalePayOrder");
		$eventManager->registerEventHandler("sale", "onSaleDeductOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleDeductOrder");
		$eventManager->registerEventHandler("sale", "onSaleDeliveryOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleDeliveryOrder");
		$eventManager->registerEventHandler("sale", "onSaleStatusOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleStatusOrder");

		$eventManager->registerEventHandler("conversion", "OnSetDayContextAttributes", "seo", "\\Bitrix\\Seo\\ConversionHandler", "onSetDayContextAttributes");
		$eventManager->registerEventHandler("conversion", "OnGetAttributeTypes", "seo", "\\Bitrix\\Seo\\ConversionHandler", "onGetAttributeTypes");

		$eventManager->registerEventHandler("catalog", "OnProductUpdate", "seo", "\\Bitrix\\Seo\\Adv\\Auto", "checkQuantity");
		$eventManager->registerEventHandler("catalog", "OnProductSetAvailableUpdate", "seo", "\\Bitrix\\Seo\\Adv\\Auto", "checkQuantity");

		$eventManager->registerEventHandler("bitrix24", "onDomainChange", "seo", "\\Bitrix\\Seo\\Service", "changeRegisteredDomain");

		if (COption::GetOptionString('seo', 'searchers_list', '') == '' && CModule::IncludeModule('statistic'))
		{
			$arFilter = array('ACTIVE' => 'Y', 'NAME' => 'Google|MSN|Bing', 'NAME_EXACT_MATCH' => 'Y');
			if (COption::GetOptionString('main', 'vendor') == '1c_bitrix')
				$arFilter['NAME'] .= '|Yandex';

			$strSearchers = '';
			$dbRes = CSearcher::GetList('s_id', 'asc', $arFilter);
			while ($arRes = $dbRes->Fetch())
			{
				$strSearchers .= ($strSearchers == '' ? '' : ',').$arRes['ID'];
			}

			COption::SetOptionString('seo', 'searchers_list', $strSearchers);
		}

		CAgent::AddAgent("Bitrix\\Seo\\Engine\\YandexDirect::updateAgent();","seo", "N", 3600);
		CAgent::AddAgent("Bitrix\\Seo\\Adv\\LogTable::clean();","seo", "N", 86400);
		CAgent::AddAgent("Bitrix\\Seo\\Adv\\Auto::checkQuantityAgent();","seo", "N", 3600);

		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);

		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$step = intval($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("SEO_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			CAgent::RemoveModuleAgents('seo');

			$APPLICATION->IncludeAdminFile(GetMessage("SEO_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB;

		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/seo/install/db/".$connection->getType()."/uninstall.sql");

			if(empty($this->errors))
			{
				\Bitrix\Seo\Adv\YandexRegionTable::setLastUpdate(0);
			}
		}

		if(!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		$this->UnInstallTasks();

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnPanelCreate', 'seo');
		$eventManager->unRegisterEventHandler("fileman", "OnIncludeHTMLEditorScript", "seo");
		$eventManager->unRegisterEventHandler("fileman", "OnBeforeHTMLEditorScriptRuns", "seo", "CSeoEventHandlers", "OnBeforeHTMLEditorScriptRuns");

		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockSectionAdd", "seo", "\\Bitrix\\Seo\\SitemapIblock", "addSection");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementAdd", "seo", "\\Bitrix\\Seo\\SitemapIblock", "addElement");

		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockSectionDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "deleteSection");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "deleteElement");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockSectionUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "updateSection");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Iblock", "updateElement");
		// compatibility
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockSectionDelete", "seo", "\\Bitrix\\Seo\\SitemapIblock", "beforeDeleteSection");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockElementDelete", "seo", "\\Bitrix\\Seo\\SitemapIblock", "beforeDeleteElement");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockSectionDelete", "seo", "\\Bitrix\\Seo\\SitemapIblock", "deleteSection");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementDelete", "seo", "\\Bitrix\\Seo\\SitemapIblock", "deleteElement");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockSectionUpdate", "seo", "\\Bitrix\\Seo\\SitemapIblock", "beforeUpdateSection");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockElementUpdate", "seo", "\\Bitrix\\Seo\\SitemapIblock", "beforeUpdateElement");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockSectionUpdate", "seo", "\\Bitrix\\Seo\\SitemapIblock", "updateSection");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementUpdate", "seo", "\\Bitrix\\Seo\\SitemapIblock", "updateElement");

		$eventManager->unRegisterEventHandler("forum", "onAfterTopicAdd", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "addTopic");
		$eventManager->unRegisterEventHandler("forum", "onAfterTopicUpdate", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "updateTopic");
		$eventManager->unRegisterEventHandler("forum", "onAfterTopicDelete", "seo", "\\Bitrix\\Seo\\Sitemap\\Source\\Forum", "deleteTopic");
		// compatibility
		$eventManager->unRegisterEventHandler("forum", "onAfterTopicAdd", "seo", "\\Bitrix\\Seo\\SitemapForum", "addTopic");
		$eventManager->unRegisterEventHandler("forum", "onAfterTopicUpdate", "seo", "\\Bitrix\\Seo\\SitemapForum", "updateTopic");
		$eventManager->unRegisterEventHandler("forum", "onAfterTopicDelete", "seo", "\\Bitrix\\Seo\\SitemapForum", "deleteTopic");

		$eventManager->unRegisterEventHandler("main", "OnAdminIBlockElementEdit", "seo", "\\Bitrix\\Seo\\AdvTabEngine", "eventHandler");
		$eventManager->unRegisterEventHandler("main", "OnBeforeProlog", "seo", "\\Bitrix\\Seo\\AdvSession", "checkSession");

		$eventManager->unRegisterEventHandler("sale", "OnOrderSave", "seo", "\\Bitrix\\Seo\\AdvSession", "onOrderSave");
		$eventManager->unRegisterEventHandler("sale", "OnBasketOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onBasketOrder");
		$eventManager->unRegisterEventHandler("sale", "onSalePayOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSalePayOrder");
		$eventManager->unRegisterEventHandler("sale", "onSaleDeductOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleDeductOrder");
		$eventManager->unRegisterEventHandler("sale", "onSaleDeliveryOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleDeliveryOrder");
		$eventManager->unRegisterEventHandler("sale", "onSaleStatusOrder", "seo", "\\Bitrix\\Seo\\AdvSession", "onSaleStatusOrder");

		$eventManager->unRegisterEventHandler("conversion", "OnSetDayContextAttributes", "seo", "\\Bitrix\\Seo\\ConversionHandler", "onSetDayContextAttributes");
		$eventManager->unRegisterEventHandler("conversion", "OnGetAttributeTypes", "seo", "\\Bitrix\\Seo\\ConversionHandler", "onGetAttributeTypes");

		$eventManager->unRegisterEventHandler("catalog", "OnProductUpdate", "seo", "\\Bitrix\\Seo\\Adv\\Auto", "checkQuantity");
		$eventManager->unRegisterEventHandler("catalog", "OnProductSetAvailableUpdate", "seo", "\\Bitrix\\Seo\\Adv\\Auto", "checkQuantity");

		$eventManager->unregisterEventHandler("bitrix24", "onDomainChange", "seo", "\\Bitrix\\Seo\\Service", "changeRegisteredDomain");

		UnRegisterModule("seo");

		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/images/seo", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/seo");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/seo/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components");

		return true;
	}

	public function GetModuleTasks()
	{
		return [
			'seo_denied' => [
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => [],
			],
			'seo_edit' => [
				'LETTER' => 'F',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'seo_tools',
				],
			],
			'seo_full_access' => [
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => [
					'seo_tools',
					'seo_settings',
				],
			],
		];
	}
}
