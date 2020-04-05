<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\SiteTable;

Loc::loadMessages(__FILE__);

class catalog extends CModule
{
	var $MODULE_ID = "catalog";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function catalog()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && isset($arModuleVersion["VERSION"]))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("CATALOG_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("CATALOG_INSTALL_DESCRIPTION2");
	}

	function DoInstall()
	{
		global $APPLICATION, $step, $errors;

		$step = (int)$step;
		$errors = false;

		if(!ModuleManager::isModuleInstalled("currency"))
			$errors = Loc::getMessage("CATALOG_UNINS_CURRENCY");
		elseif(!ModuleManager::isModuleInstalled("iblock"))
			$errors = Loc::getMessage("CATALOG_UNINS_IBLOCK");
		else
		{
			$this->InstallFiles();
			$this->InstallDB();
			$this->InstallEvents();
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("CATALOG_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/step1.php");
	}

	function InstallFiles()
	{
		if ($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/admin", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/components", $_SERVER['DOCUMENT_ROOT']."/bitrix/components", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/images", $_SERVER['DOCUMENT_ROOT']."/bitrix/images/catalog", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/themes", $_SERVER['DOCUMENT_ROOT']."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/tools", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools", true, true);

			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/public/catalog_import", $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/catalog_import");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/load_import/cron_frame.php", $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/catalog_import/cron_frame.php");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/public/catalog_export", $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/catalog_export");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/load/cron_frame.php", $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/include/catalog_export/cron_frame.php");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/public/catalog_export/froogle_util.php", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools/catalog_export/froogle_util.php");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/public/catalog_export/yandex_util.php", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools/catalog_export/yandex_util.php");
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/public/catalog_export/yandex_detail.php", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools/catalog_export/yandex_detail.php");

			CheckDirPath($_SERVER['DOCUMENT_ROOT']."/bitrix/catalog_export/");
		}

		return true;
	}

	function InstallDB()
	{
		global $APPLICATION;
		global $DB;
		global $errors;

		$bitrix24 = ModuleManager::isModuleInstalled('bitrix24');

		if(!$DB->Query("SELECT 'x' FROM b_catalog_group", true))
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/db/".strtolower($DB->type)."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode('. ', $errors));
			return false;
		}

		ModuleManager::registerModule('catalog');

		\Bitrix\Main\Config\Option::set('catalog', 'subscribe_repeated_notify', 'Y');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('sale', 'onBuildCouponProviders', 'catalog', '\Bitrix\Catalog\DiscountCouponTable', 'couponManager');
		$eventManager->registerEventHandler('sale', 'onBuildDiscountProviders', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'catalogDiscountManager');
		$eventManager->registerEventHandler('sale', 'onExtendOrderData', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'extendOrderData');
		$eventManager->registerEventHandler('currency', 'onAfterUpdateCurrencyBaseRate', 'catalog', '\Bitrix\Catalog\Product\Price', 'handlerAfterUpdateCurrencyBaseRate');

		$eventManager->registerEventHandlerCompatible('main', 'onUserDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onUserDelete');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onIblockElementDelete');
		$eventManager->registerEventHandlerCompatible('catalog', 'OnProductUpdate', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onProductUpdate');
		$eventManager->registerEventHandlerCompatible('catalog', 'OnProductSetAvailableUpdate', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onProductSetAvailableUpdate');
		$eventManager->registerEventHandlerCompatible('catalog', 'onAddContactType', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onAddContactType');
		$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onSaleOrderSaved');

		RegisterModuleDependences("iblock", "OnBeforeIBlockUpdate", "catalog", "CCatalog", "OnBeforeIBlockUpdate");
		RegisterModuleDependences("iblock", "OnAfterIBlockUpdate", "catalog", "CCatalog", "OnAfterIBlockUpdate");
		RegisterModuleDependences("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogProduct", "OnIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CPrice", "OnIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogStoreProduct", "OnIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogDocs", "OnIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalogDocs", "OnBeforeIBlockElementDelete");
		RegisterModuleDependences("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		RegisterModuleDependences("main", "OnGroupDelete", "catalog", "CCatalogProductGroups", "OnGroupDelete");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "catalog", "CCatalogProduct", "OnAfterIBlockElementUpdate");
		RegisterModuleDependences("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");
		RegisterModuleDependences("iblock", "OnBeforeIBlockDelete", "catalog", "CCatalog", "OnBeforeCatalogDelete", 300);
		RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalog", "OnBeforeIBlockElementDelete", 10000);
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "catalog", "CCatalogEvent", "GetAuditTypes");
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildGlobalMenu');
		RegisterModuleDependences('main', 'OnAdminListDisplay', 'catalog', 'CCatalogAdmin', 'OnAdminListDisplay');
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildSaleMenu');
		RegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlGroup", "GetControlDescr", 100);
		RegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockFields", "GetControlDescr", 200);
		RegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockProps", "GetControlDescr", 300);
		RegisterModuleDependences("catalog", "OnDocumentBarcodeDelete", "catalog", "CCatalogStoreDocsElement", "OnDocumentBarcodeDelete");
		RegisterModuleDependences("catalog", "OnBeforeDocumentDelete", "catalog", "CCatalogStoreDocsBarcode", "OnBeforeDocumentDelete");
		RegisterModuleDependences("catalog", "OnCatalogStoreDelete", "catalog", "CCatalogDocs", "OnCatalogStoreDelete");
		RegisterModuleDependences("iblock", "OnBeforeIBlockPropertyDelete", "catalog", "CCatalog", "OnBeforeIBlockPropertyDelete");

		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductFields", "GetControlDescr", 1100);
		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductProps", "GetControlDescr", 1200);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductFields", "GetControlDescr", 1200);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductProps", "GetControlDescr", 1300);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogGifterProduct", "GetControlDescr", 200);
		RegisterModuleDependences("sale", "OnExtendBasketItems", "catalog", "CCatalogDiscount", "ExtendBasketItems", 100);

		RegisterModuleDependences('iblock', 'OnModuleUnInstall', 'catalog', 'CCatalog', 'OnIBlockModuleUnInstall');

		RegisterModuleDependences('iblock', 'OnIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementAdd');
		RegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementAdd');
		RegisterModuleDependences('iblock', 'OnIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementUpdate');
		RegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementUpdate');
		RegisterModuleDependences('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementDelete');
		RegisterModuleDependences('iblock', 'OnAfterIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementDelete');
		RegisterModuleDependences('iblock', 'OnIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValues');
		RegisterModuleDependences('iblock', 'OnAfterIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIBlockElementSetPropertyValues');

		RegisterModuleDependences('perfmon', 'OnGetTableSchema', 'catalog', 'catalog', 'getTableSchema');

		if (!$bitrix24)
		{
			CAgent::AddAgent('\Bitrix\Catalog\CatalogViewedProductTable::clearAgent();', 'catalog', 'N', (int)COption::GetOptionString("catalog", "viewed_period") * 24 * 3600);
		}

		$this->InstallTasks();

		$arCount = $DB->Query("select count(ID) as CNT from b_catalog_measure", true)->Fetch();
		if(is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0)
		{
			$DB->Query("insert into b_catalog_measure (CODE, SYMBOL_INTL, SYMBOL_LETTER_INTL, IS_DEFAULT) values(6, 'm', 'MTR', 'N')", true);
			$DB->Query("insert into b_catalog_measure (CODE, SYMBOL_INTL, SYMBOL_LETTER_INTL, IS_DEFAULT) values(112, 'l', 'LTR', 'N')", true);
			$DB->Query("insert into b_catalog_measure (CODE, SYMBOL_INTL, SYMBOL_LETTER_INTL, IS_DEFAULT) values(163, 'g', 'GRM', 'N')", true);
			$DB->Query("insert into b_catalog_measure (CODE, SYMBOL_INTL, SYMBOL_LETTER_INTL, IS_DEFAULT) values(166, 'kg', 'KGM', 'N')", true);
			$DB->Query("insert into b_catalog_measure (CODE, SYMBOL_INTL, SYMBOL_LETTER_INTL, IS_DEFAULT) values(796, 'pc. 1', 'PCE. NMB', 'Y')", true);
		}

		if (!$bitrix24)
		{
			$languageID = '';
			$siteIterator = SiteTable::getList(array(
				'select' => array('LID', 'LANGUAGE_ID'),
				'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
			));
			if ($site = $siteIterator->fetch())
			{
				$languageID = (string)$site['LANGUAGE_ID'];
			}
			if ($languageID == '')
				$languageID = 'en';

			if ($languageID == 'ru')
			{
				$mess = Loc::getMessage('CATALOG_INSTALL_PROFILE_IRR2', null, 'ru');
				if ($mess == '')
					$mess = 'irr.ru';
				$strQuery = "select COUNT(CE.ID) as CNT from b_catalog_export CE where CE.IS_EXPORT = 'Y' and CE.FILE_NAME ='yandex' and CE.NAME = '".$DB->ForSql($mess)."'";
				$rsProfiles = $DB->Query($strQuery, true);
				if (false !== $rsProfiles)
				{
					$arProfile = $rsProfiles->Fetch();
					if ((int)$arProfile['CNT'] == 0)
					{
						$arFields = array(
							'FILE_NAME' => 'yandex',
							'NAME' => $mess,
							'DEFAULT_PROFILE' => 'N',
							'IN_MENU' => 'N',
							'IN_AGENT' => 'N',
							'IN_CRON' => 'N',
							'NEED_EDIT' => 'Y',
							'IS_EXPORT' => 'Y'
						);
						$arInsert = $DB->PrepareInsert("b_catalog_export", $arFields);
						$strQuery = "INSERT INTO b_catalog_export(".$arInsert[0].") VALUES(".$arInsert[1].")";
						$DB->Query($strQuery, true);
					}
				}
			}
		}

		return true;
	}

	function InstallEvents()
	{
		global $DB;
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/events/set_events.php");
		return true;
	}

	function DoUnInstall()
	{
		global $APPLICATION, $step, $errors;
		$step = (int)$step;
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("CATALOG_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/unstep1.php");
		}
		elseif ($step == 2)
		{
			$errors = false;

			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));

			$this->UnInstallFiles();
			$this->UnInstallEvents();

			$APPLICATION->IncludeAdminFile(Loc::getMessage("CATALOG_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/unstep2.php");
		}
	}

	function UnInstallFiles()
	{
		if ($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/admin", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
			DeleteDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/themes/.default/", $_SERVER['DOCUMENT_ROOT']."/bitrix/themes/.default");//css
			DeleteDirFilesEx("/bitrix/themes/.default/icons/catalog/");//icons
			DeleteDirFilesEx("/bitrix/tools/catalog/"); // scripts
			DeleteDirFilesEx("/bitrix/js/catalog/");//javascript
			DeleteDirFilesEx("/bitrix/panel/catalog/");//panel
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $errors;

		if (!defined('BX_CATALOG_UNINSTALLED'))
			define('BX_CATALOG_UNINSTALLED', true);

		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/catalog/install/db/".strtolower($DB->type)."/uninstall.sql");
			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
			$this->UnInstallTasks();
			COption::RemoveOption("catalog");
		}

		UnRegisterModuleDependences("iblock", "OnBeforeIBlockUpdate", "catalog", "CCatalog", "OnBeforeIBlockUpdate");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockUpdate", "catalog", "CCatalog", "OnAfterIBlockUpdate");
		UnRegisterModuleDependences("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CProduct", "OnIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CPrice", "OnIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogStoreProduct", "OnIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogDocs", "OnIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalogDocs", "OnBeforeIBlockElementDelete");
		UnRegisterModuleDependences("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "catalog", "CCatalogProduct", "OnAfterIBlockElementUpdate");
		UnRegisterModuleDependences("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockDelete", "catalog", "CCatalog", "OnBeforeCatalogDelete");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalog", "OnBeforeIBlockElementDelete");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "catalog", "CCatalogEvent", "GetAuditTypes");
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildGlobalMenu');
		UnRegisterModuleDependences('main', 'OnAdminListDisplay', 'catalog', 'CCatalogAdmin', 'OnAdminListDisplay');
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildSaleMenu');
		UnRegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlGroup", "GetControlDescr");
		UnRegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockFields", "GetControlDescr");
		UnRegisterModuleDependences("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockProps", "GetControlDescr");
		UnRegisterModuleDependences("catalog", "OnDocumentBarcodeDelete", "catalog", "CCatalogStoreDocsElement", "OnDocumentBarcodeDelete");
		UnRegisterModuleDependences("catalog", "OnBeforeDocumentDelete", "catalog", "CCatalogStoreDocsBarcode", "OnBeforeDocumentDelete");
		UnRegisterModuleDependences("catalog", "OnCatalogStoreDelete", "catalog", "CCatalogDocs", "OnCatalogStoreDelete");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockPropertyDelete", "catalog", "CCatalog", "OnBeforeIBlockPropertyDelete");

		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductFields", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductProps", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductFields", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductProps", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogGifterProduct", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnExtendBasketItems", "catalog", "CCatalogDiscount", "ExtendBasketItems");

		UnRegisterModuleDependences('iblock', 'OnModuleUnInstall', 'catalog', 'CCatalog', 'OnIBlockModuleUnInstall');

		UnRegisterModuleDependences('iblock', 'OnIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementAdd');
		UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementAdd');
		UnRegisterModuleDependences('iblock', 'OnIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementUpdate');
		UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementUpdate');
		UnRegisterModuleDependences('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementDelete');
		UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementDelete');
		UnRegisterModuleDependences('iblock', 'OnIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValues');
		UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIBlockElementSetPropertyValues');

		UnRegisterModuleDependences('perfmon', 'OnGetTableSchema', 'catalog', 'catalog', 'getTableSchema');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('sale', 'onBuildCouponProviders', 'catalog', '\Bitrix\Catalog\DiscountCouponTable', 'couponManager');
		$eventManager->unRegisterEventHandler('sale', 'onBuildDiscountProviders', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'catalogDiscountManager');
		$eventManager->unRegisterEventHandler('sale', 'onExtendOrderData', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'extendOrderData');
		$eventManager->unRegisterEventHandler('currency', 'onAfterUpdateCurrencyBaseRate', 'catalog', '\Bitrix\Catalog\Product\Price', 'handlerAfterUpdateCurrencyBaseRate');

		$eventManager->unRegisterEventHandler('main', 'onUserDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onUserDelete');
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onIblockElementDelete');
		$eventManager->unRegisterEventHandler('catalog', 'OnProductUpdate', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onProductUpdate');
		$eventManager->unRegisterEventHandler('catalog', 'OnProductSetAvailableUpdate', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onProductSetAvailableUpdate');
		$eventManager->unRegisterEventHandler('catalog', 'onAddContactType', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onAddContactType');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onSaleOrderSaved');

		CAgent::RemoveModuleAgents('catalog');

		ModuleManager::unRegisterModule('catalog');

		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/events/del_events.php");
		return true;
	}

	function GetModuleTasks()
	{
		return array(
			'catalog_denied' => array(
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => array(
				),
			),
			'catalog_view' => array(
				'LETTER' => 'M',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'catalog_view'
				)
			),
			'catalog_read' => array(
				"LETTER" => "R",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'catalog_read'
				)
			),
			'catalog_price_edit' => array(
				"LETTER" => "T",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'catalog_read',
					'catalog_price',
					'catalog_group',
					'catalog_discount',
					'catalog_vat',
					'catalog_extra',
					'catalog_store',
				),
			),
			'catalog_store_edit' => array(
				"LETTER" => "S",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'catalog_read',
					'catalog_price',
					'catalog_extra',
					'catalog_store',
					'catalog_purchas_info',
				),
			),
			'catalog_export_import' => array(
				"LETTER" => "U",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'catalog_read',
					'catalog_export_edit',
					'catalog_export_exec',
					'catalog_import_edit',
					'catalog_import_exec',
				),
			),
			'catalog_full_access' => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'catalog_read',
					'catalog_price',
					'catalog_group',
					'catalog_discount',
					'catalog_vat',
					'catalog_extra',
					'catalog_store',
					'catalog_measure',
					'catalog_purchas_info',
					'catalog_export_edit',
					'catalog_export_exec',
					'catalog_import_edit',
					'catalog_import_exec',
					'catalog_settings',
				),
			),
		);
	}

	public static function getTableSchema()
	{
		return array(
			'iblock' => array(
				'b_iblock' => array(
					'ID' => array(
						'b_catalog_iblock' => 'IBLOCK_ID',
						'b_catalog_iblock^' => 'PRODUCT_IBLOCK_ID'
					)
				),
				'b_iblock_property' => array(
					'ID' => array(
						'b_catalog_iblock' => 'SKU_PROPERTY_ID'
					)
				),
				'b_iblock_element' => array(
					'ID' => array(
						'b_catalog_product' => 'ID'
					)
				)
			),
			'catalog' => array(
				'b_catalog_product' => array(
					'ID' => array(
						'b_catalog_price' => 'PRODUCT_ID',
						'b_catalog_store_product' => 'PRODUCT_ID',
						'b_catalog_product' => 'TRIAL_PRICE_ID',
						'b_catalog_store_barcode' => 'PRODUCT_ID',
						'b_catalog_product2group' => 'PRODUCT_ID'
					)
				),
				'b_catalog_vat' => array(
					'ID' => array(
						'b_catalog_product' => 'VAT_ID',
						'b_catalog_iblock' => 'VAT_ID'
					)
				),
				'b_catalog_extra' => array(
					'ID' => array(
						'b_catalog_price' => 'EXTRA_ID'
					)
				),
				'b_catalog_group' => array(
					'ID' => array(
						'b_catalog_price' => 'CATALOG_GROUP_ID',
						'b_catalog_group_lang' => 'CATALOG_GROUP_ID',
						'b_catalog_group2group' => 'CATALOG_GROUP_ID'
					)
				),
				'b_catalog_measure' => array(
					'ID' => array(
						'b_catalog_product' => 'MEASURE'
					)
				),
				'b_catalog_store' => array(
					'ID' => array(
						'b_catalog_store_product' => 'STORE_ID',
						'b_catalog_store_barcode' => 'STORE_ID'
					)
				)
			)
		);
	}

	private function __getLangMessages($path, $messID, $langList)
	{
		$result = array();
		if (empty($messID))
			return $result;
		if (!is_array($messID))
			$messID = array($messID);
		if (!is_array($langList))
			$langList = array($langList);
		if (empty($langList))
		{
			$languageIterator = LanguageTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($oneLanguage = $languageIterator->fetch())
				$langList[] = $oneLanguage['ID'];
			unset($oneLanguage, $languageIterator);
		}

		foreach ($langList as &$oneLanguage)
		{
			$mess = Loc::loadLanguageFile($path, $oneLanguage);
			foreach ($messID as &$oneMess)
			{
				if (empty($oneMess) || !isset($mess[$oneMess]) || empty($mess[$oneMess]))
					continue;
				if (!isset($result[$oneMess]))
					$result[$oneMess] = array();
				$result[$oneMess][$oneLanguage] = $mess[$oneMess];
			}
			unset($oneMess, $mess);
		}
		unset($oneLanguage);

		return $result;
	}
}