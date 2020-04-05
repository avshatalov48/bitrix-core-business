<?php
use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\Localization\LanguageTable;

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

	function __construct()
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

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('sale', 'onBuildCouponProviders', 'catalog', '\Bitrix\Catalog\DiscountCouponTable', 'couponManager');
		$eventManager->registerEventHandler('sale', 'onBuildDiscountProviders', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'catalogDiscountManager');
		$eventManager->registerEventHandler('sale', 'onExtendOrderData', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'extendOrderData');
		$eventManager->registerEventHandler('currency', 'onAfterUpdateCurrencyBaseRate', 'catalog', '\Bitrix\Catalog\Product\Price', 'handlerAfterUpdateCurrencyBaseRate');
		$eventManager->registerEventHandler('iblock', 'Bitrix\Iblock\Model\PropertyFeature::OnPropertyFeatureBuildList', 'catalog', '\Bitrix\Catalog\Product\PropertyCatalogFeature', 'handlerPropertyFeatureBuildList');

		$eventManager->registerEventHandlerCompatible('main', 'onUserDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onUserDelete');
		$eventManager->registerEventHandlerCompatible('catalog', 'onAddContactType', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onAddContactType');
		$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onSaleOrderSaved');

		$eventManager->registerEventHandlerCompatible("iblock", "OnBeforeIBlockUpdate", "catalog", "CCatalog", "OnBeforeIBlockUpdate");
		$eventManager->registerEventHandlerCompatible("iblock", "OnAfterIBlockUpdate", "catalog", "CCatalog", "OnAfterIBlockUpdate");
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockElementDelete", "catalog", "CCatalogProduct", "OnIBlockElementDelete");
		$eventManager->registerEventHandlerCompatible("iblock", "OnIBlockElementDelete", "catalog", "CCatalogDocs", "OnIBlockElementDelete");
		$eventManager->registerEventHandlerCompatible("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalogDocs", "OnBeforeIBlockElementDelete");
		$eventManager->registerEventHandlerCompatible("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		$eventManager->registerEventHandlerCompatible("main", "OnGroupDelete", "catalog", "CCatalogProductGroups", "OnGroupDelete");
		$eventManager->registerEventHandlerCompatible("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");
		$eventManager->registerEventHandlerCompatible("iblock", "OnBeforeIBlockDelete", "catalog", "CCatalog", "OnBeforeCatalogDelete", 300);
		$eventManager->registerEventHandlerCompatible("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalog", "OnBeforeIBlockElementDelete", 10000);
		$eventManager->registerEventHandlerCompatible("main", "OnEventLogGetAuditTypes", "catalog", "CCatalogEvent", "GetAuditTypes");
		$eventManager->registerEventHandlerCompatible('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildGlobalMenu');
		$eventManager->registerEventHandlerCompatible('main', 'OnAdminListDisplay', 'catalog', 'CCatalogAdmin', 'OnAdminListDisplay');
		$eventManager->registerEventHandlerCompatible('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildSaleMenu');
		$eventManager->registerEventHandlerCompatible("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlGroup", "GetControlDescr", 100);
		$eventManager->registerEventHandlerCompatible("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockFields", "GetControlDescr", 200);
		$eventManager->registerEventHandlerCompatible("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockProps", "GetControlDescr", 300);
		$eventManager->registerEventHandlerCompatible("catalog", "OnDocumentBarcodeDelete", "catalog", "CCatalogStoreDocsElement", "OnDocumentBarcodeDelete");
		$eventManager->registerEventHandlerCompatible("catalog", "OnBeforeDocumentDelete", "catalog", "CCatalogStoreDocsBarcode", "OnBeforeDocumentDelete");
		$eventManager->registerEventHandlerCompatible("catalog", "OnCatalogStoreDelete", "catalog", "CCatalogDocs", "OnCatalogStoreDelete");
		$eventManager->registerEventHandlerCompatible("iblock", "OnBeforeIBlockPropertyDelete", "catalog", "CCatalog", "OnBeforeIBlockPropertyDelete");

		$eventManager->registerEventHandlerCompatible("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductFields", "GetControlDescr", 1100);
		$eventManager->registerEventHandlerCompatible("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductProps", "GetControlDescr", 1200);
		$eventManager->registerEventHandlerCompatible("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductFields", "GetControlDescr", 1200);
		$eventManager->registerEventHandlerCompatible("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductProps", "GetControlDescr", 1300);
		$eventManager->registerEventHandlerCompatible("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogGifterProduct", "GetControlDescr", 200);
		$eventManager->registerEventHandlerCompatible("sale", "OnExtendBasketItems", "catalog", "CCatalogDiscount", "ExtendBasketItems", 100);

		$eventManager->registerEventHandlerCompatible('iblock', 'OnModuleUnInstall', 'catalog', 'CCatalog', 'OnIBlockModuleUnInstall');

		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementAdd');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnAfterIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementAdd');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementUpdate');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnAfterIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementUpdate');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementDelete');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnAfterIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementDelete');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValues');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnAfterIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIBlockElementSetPropertyValues');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockElementSetPropertyValuesEx', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValuesEx');
		$eventManager->registerEventHandlerCompatible('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementSetPropertyValuesEx');

		$eventManager->registerEventHandlerCompatible('perfmon', 'OnGetTableSchema', 'catalog', 'catalog', 'getTableSchema');

		if (!$bitrix24)
		{
			CAgent::AddAgent('\Bitrix\Catalog\CatalogViewedProductTable::clearAgent();', 'catalog', 'N', (int)COption::GetOptionString("catalog", "viewed_period") * 24 * 3600);
		}

		Main\Config\Option::set('catalog', 'subscribe_repeated_notify', 'Y', '');
		if ($bitrix24)
		{
			Main\Config\Option::set('catalog', 'default_quantity_trace', 'Y', '');
			Main\Config\Option::set('catalog', 'default_can_buy_zero', 'Y', '');
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

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockUpdate", "catalog", "CCatalog", "OnBeforeIBlockUpdate");
		$eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockUpdate", "catalog", "CCatalog", "OnAfterIBlockUpdate");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockElementDelete", "catalog", "CProduct", "OnIBlockElementDelete");
		$eventManager->unRegisterEventHandler("iblock", "OnIBlockElementDelete", "catalog", "CCatalogDocs", "OnIBlockElementDelete");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalogDocs", "OnBeforeIBlockElementDelete");
		$eventManager->unRegisterEventHandler("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		$eventManager->unRegisterEventHandler("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockDelete", "catalog", "CCatalog", "OnBeforeCatalogDelete");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockElementDelete", "catalog", "CCatalog", "OnBeforeIBlockElementDelete");
		$eventManager->unRegisterEventHandler("main", "OnEventLogGetAuditTypes", "catalog", "CCatalogEvent", "GetAuditTypes");
		$eventManager->unRegisterEventHandler('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildGlobalMenu');
		$eventManager->unRegisterEventHandler('main', 'OnAdminListDisplay', 'catalog', 'CCatalogAdmin', 'OnAdminListDisplay');
		$eventManager->unRegisterEventHandler('main', 'OnBuildGlobalMenu', 'catalog', 'CCatalogAdmin', 'OnBuildSaleMenu');
		$eventManager->unRegisterEventHandler("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlGroup", "GetControlDescr");
		$eventManager->unRegisterEventHandler("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockFields", "GetControlDescr");
		$eventManager->unRegisterEventHandler("catalog", "OnCondCatControlBuildList", "catalog", "CCatalogCondCtrlIBlockProps", "GetControlDescr");
		$eventManager->unRegisterEventHandler("catalog", "OnDocumentBarcodeDelete", "catalog", "CCatalogStoreDocsElement", "OnDocumentBarcodeDelete");
		$eventManager->unRegisterEventHandler("catalog", "OnBeforeDocumentDelete", "catalog", "CCatalogStoreDocsBarcode", "OnBeforeDocumentDelete");
		$eventManager->unRegisterEventHandler("catalog", "OnCatalogStoreDelete", "catalog", "CCatalogDocs", "OnCatalogStoreDelete");
		$eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockPropertyDelete", "catalog", "CCatalog", "OnBeforeIBlockPropertyDelete");

		$eventManager->unRegisterEventHandler("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductFields", "GetControlDescr");
		$eventManager->unRegisterEventHandler("sale", "OnCondSaleControlBuildList", "catalog", "CCatalogCondCtrlBasketProductProps", "GetControlDescr");
		$eventManager->unRegisterEventHandler("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductFields", "GetControlDescr");
		$eventManager->unRegisterEventHandler("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogActionCtrlBasketProductProps", "GetControlDescr");
		$eventManager->unRegisterEventHandler("sale", "OnCondSaleActionsControlBuildList", "catalog", "CCatalogGifterProduct", "GetControlDescr");
		$eventManager->unRegisterEventHandler("sale", "OnExtendBasketItems", "catalog", "CCatalogDiscount", "ExtendBasketItems");

		$eventManager->unRegisterEventHandler('iblock', 'OnModuleUnInstall', 'catalog', 'CCatalog', 'OnIBlockModuleUnInstall');

		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementAdd');
		$eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementAdd', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementAdd');
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementUpdate');
		$eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementUpdate', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementUpdate');
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementDelete');
		$eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementDelete', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementDelete');
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValues');
		$eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIBlockElementSetPropertyValues');
		$eventManager->unRegisterEventHandler('iblock', 'OnIBlockElementSetPropertyValuesEx', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerIblockElementSetPropertyValuesEx');
		$eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', 'catalog', '\Bitrix\Catalog\Product\Sku', 'handlerAfterIblockElementSetPropertyValuesEx');

		$eventManager->unRegisterEventHandler('perfmon', 'OnGetTableSchema', 'catalog', 'catalog', 'getTableSchema');

		$eventManager->unRegisterEventHandler('sale', 'onBuildCouponProviders', 'catalog', '\Bitrix\Catalog\DiscountCouponTable', 'couponManager');
		$eventManager->unRegisterEventHandler('sale', 'onBuildDiscountProviders', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'catalogDiscountManager');
		$eventManager->unRegisterEventHandler('sale', 'onExtendOrderData', 'catalog', '\Bitrix\Catalog\Discount\DiscountManager', 'extendOrderData');
		$eventManager->unRegisterEventHandler('currency', 'onAfterUpdateCurrencyBaseRate', 'catalog', '\Bitrix\Catalog\Product\Price', 'handlerAfterUpdateCurrencyBaseRate');
		$eventManager->registerEventHandler('iblock', 'Bitrix\Iblock\Model\PropertyFeature::OnPropertyFeatureBuildList', 'catalog', '\Bitrix\Catalog\Product\PropertyCatalogFeature', 'handlerPropertyFeatureBuildList');

		$eventManager->unRegisterEventHandler('main', 'onUserDelete', 'catalog', '\Bitrix\Catalog\SubscribeTable', 'onUserDelete');
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