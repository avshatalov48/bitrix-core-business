<?
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install.php"));



Class sale extends CModule
{
	var $MODULE_ID = "sale";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function sale()
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
			$this->MODULE_VERSION = SALE_VERSION;
			$this->MODULE_VERSION_DATE = SALE_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("SALE_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SALE_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("SALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/step1.php");
		}
		elseif($step==2)
		{
			$this->InstallFiles();
			if($this->InstallDB())
				$this->InstallEvents();
			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("SALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("SALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallFiles();
			if($_REQUEST["saveemails"] != "Y")
				$this->UnInstallEvents();

			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("SALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D",/* "R",*/ "P", "U", "W"),
			"reference" => array(
					"[D] ".GetMessage("SINS_PERM_D"),
					//"[R] ".GetMessage("SINS_PERM_R"),
					"[P] ".GetMessage("SINS_PERM_P"),
					"[U] ".GetMessage("SINS_PERM_U"),
					"[W] ".GetMessage("SINS_PERM_W")
				)
			);
		return $arr;
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		$clearInstall = false;
		if(!$DB->Query("SELECT 'x' FROM b_sale_basket", true))
		{
			$clearInstall = true;
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/db/".$DBType."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		ModuleManager::registerModule('sale');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('main', 'OnUserLogout', 'sale', '\Bitrix\Sale\DiscountCouponsManager', 'logout');
		$eventManager->registerEventHandler('sale', 'OnSaleBasketItemRefreshData', 'sale', '\Bitrix\Sale\Compatible\DiscountCompatibility', 'OnSaleBasketItemRefreshData');

		RegisterModuleDependences("main", "OnUserLogin", "sale", "CSaleUser", "OnUserLogin");
		RegisterModuleDependences("main", "OnUserLogout", "sale", "CSaleUser", "OnUserLogout");
		RegisterModuleDependences("main", "OnBeforeLangDelete", "sale", "CSalePersonType", "OnBeforeLangDelete");
		RegisterModuleDependences("main", "OnLanguageDelete", "sale", "CSaleLocation", "OnLangDelete");
		RegisterModuleDependences("main", "OnLanguageDelete", "sale", "CSaleLocationGroup", "OnLangDelete");

		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleOrderUserProps", "OnUserDelete");
		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUserAccount", "OnUserDelete");
		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleAuxiliary", "OnUserDelete");
		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUser", "OnUserDelete");
		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleRecurring", "OnUserDelete");
		RegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUserCards", "OnUserDelete");

		RegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleOrder", "OnBeforeUserDelete");
		RegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleAffiliate", "OnBeforeUserDelete");
		RegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleUserAccount", "OnBeforeUserDelete");

		RegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", 100, "/modules/sale/affiliate.php");

		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSaleYMHandler", 'OnEventLogGetAuditTypes');
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSalePaySystemAction", 'OnEventLogGetAuditTypes');

		RegisterModuleDependences("main", "OnUserConsentProviderList", "sale", "\\Bitrix\\Sale\\UserConsent", "onProviderList");
		RegisterModuleDependences("main", "OnUserConsentDataProviderList", "sale", "\\Bitrix\\Sale\\UserConsent", "onDataProviderList");

		RegisterModuleDependences("currency", "OnBeforeCurrencyDelete", "sale", "CSaleOrder", "OnBeforeCurrencyDelete");
		RegisterModuleDependences("currency", "OnBeforeCurrencyDelete", "sale", "CSaleLang", "OnBeforeCurrencyDelete");
		RegisterModuleDependences("currency", "OnModuleUnInstall", "sale", "", "CurrencyModuleUnInstallSale");

		RegisterModuleDependences("catalog", "OnSaleOrderSumm", "sale", "CSaleOrder", "__SaleOrderCount");

		RegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "sale", "CSaleMobileOrderUtils", "buildSaleAdminMobileMenu");
		RegisterModuleDependences("sender", "OnConnectorList", "sale", "\\Bitrix\\Sale\\SenderEventHandler", "onConnectorListBuyer");
		RegisterModuleDependences("sender", "OnTriggerList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onTriggerList");
		RegisterModuleDependences("sender", "OnPresetMailingList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetMailingList");
		RegisterModuleDependences("sender", "OnPresetTemplateList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetTemplateList");

		RegisterModuleDependences("sender", "OnConnectorList", "sale", "Bitrix\\Sale\\Bigdata\\TargetSaleMailConnector", "onConnectorList");

		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlGroup", "GetControlDescr", 100);
		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlBasketGroup", "GetControlDescr", 200);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionGiftCtrlGroup", "GetControlDescr", 200);
		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlBasketFields", "GetControlDescr", 300);
		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlOrderFields", "GetControlDescr", 1000);
		RegisterModuleDependences("sale", "onBuildDiscountConditionInterfaceControls", "sale", "CSaleCondCtrlPastOrder", "onBuildDiscountConditionInterfaceControls", 1000);
		RegisterModuleDependences("sale", "onBuildDiscountConditionInterfaceControls", "sale", "CSaleCondCumulativeCtrl", "onBuildDiscountConditionInterfaceControls", 1000);

		RegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlCommon", "GetControlDescr", 10000);

		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlGroup", "GetControlDescr", 100);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlDelivery", "GetControlDescr", 200);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlBasketGroup", "GetControlDescr", 300);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlSubGroup", "GetControlDescr", 1000);
		RegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCondCtrlBasketFields", "GetControlDescr", 1100);

		RegisterModuleDependences("sale", "onBuildDiscountActionInterfaceControls", "sale", "CSaleCumulativeAction", "onBuildDiscountActionInterfaceControls", 1000);

		//pulling for mobile orders
		RegisterModuleDependences("sale", "OnOrderDelete", "sale", "CSaleMobileOrderPull", "onOrderDelete", 100);
		RegisterModuleDependences("sale", "OnOrderAdd", "sale", "CSaleMobileOrderPull", "onOrderAdd", 100);
		RegisterModuleDependences("sale", "OnOrderUpdate", "sale", "CSaleMobileOrderPull", "onOrderUpdate", 100);

		// sale product2product
		RegisterModuleDependences("sale", "OnBasketOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleOrderAdd", 100);
		RegisterModuleDependences("sale", "OnSaleStatusOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleStatusOrderHandler", 100);
		RegisterModuleDependences("sale", "OnSaleDeliveryOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleDeliveryOrderHandler", 100);
		RegisterModuleDependences("sale", "OnSaleDeductOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleDeductOrderHandler", 100);
		RegisterModuleDependences("sale", "OnSaleCancelOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleCancelOrderHandler", 100);
		RegisterModuleDependences("sale", "OnSalePayOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSalePayOrderHandler", 100);
		CAgent::AddAgent("\\Bitrix\\Sale\\Product2ProductTable::deleteOldProducts(10);", "sale", "N", 10 * 24 * 3600, "", "Y");

		// conversion
		RegisterModuleDependences('conversion', 'OnGetCounterTypes'    , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetCounterTypes'    );
		RegisterModuleDependences('conversion', 'OnGetRateTypes'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetRateTypes'       );
		RegisterModuleDependences('conversion', 'OnGenerateInitialData', 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGenerateInitialData');
		RegisterModuleDependences('sale'      , 'OnBeforeBasketAdd'    , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketAdd'    );
		RegisterModuleDependences('sale'      , 'OnBasketAdd'          , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketAdd'          );
		RegisterModuleDependences('sale'      , 'OnBeforeBasketUpdate' , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketUpdate' );
		RegisterModuleDependences('sale'      , 'OnBasketUpdate'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketUpdate'       );
		RegisterModuleDependences('sale'      , 'OnBeforeBasketDelete' , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketDelete' );
		RegisterModuleDependences('sale'      , 'OnBasketDelete'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketDelete'       );
		RegisterModuleDependences('sale'      , 'OnOrderAdd'           , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onOrderAdd'           );
		RegisterModuleDependences('sale'      , 'OnSalePayOrder'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onSalePayOrder'       );

		RegisterModuleDependences('sale', 'OnGetBusinessValueGroups', 'sale', '\Bitrix\Sale\PaySystem\Manager', 'getBusValueGroups');
		RegisterModuleDependences('sale', 'OnGetBusinessValueConsumers', 'sale', '\Bitrix\Sale\PaySystem\Manager', 'getConsumersList');

		RegisterModuleDependences('sale', 'OnGetBusinessValueGroups', 'sale', '\Bitrix\Sale\Delivery\Services\Manager', 'onGetBusinessValueGroups');
		RegisterModuleDependences('sale', 'OnGetBusinessValueConsumers', 'sale', '\Bitrix\Sale\Delivery\Services\Manager', 'onGetBusinessValueConsumers');

		RegisterModuleDependences("perfmon", "OnGetTableSchema", "sale", "sale", "OnGetTableSchema");

		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'sale', '\Bitrix\Sale\PaySystem\RestService', 'onRestServiceBuildDescription');

		COption::SetOptionString("sale", "viewed_capability", "N");
		COption::SetOptionString("sale", "viewed_count", 10);
		COption::SetOptionString("sale", "viewed_time", 5);
		COption::SetOptionString("main", "~sale_converted_15", 'Y');
		COption::SetOptionString("main", "~sale_paysystem_converted", 'Y');

		COption::SetOptionString("sale", "expiration_processing_events", 'Y');

		$eventManager->registerEventHandler('sale', 'OnSaleBasketItemEntitySaved', 'sale', '\Bitrix\Sale\Internals\Events', 'onSaleBasketItemEntitySaved');
		$eventManager->registerEventHandler('sale', 'OnSaleBasketItemDeleted', 'sale', '\Bitrix\Sale\Internals\Events', 'onSaleBasketItemDeleted');


		COption::SetOptionString("sale", "p2p_status_list", serialize(array(
			"N", "P", "F", "F_CANCELED", "F_DELIVERY", "F_PAY", "F_OUT"
		)));

		if ($clearInstall)
		{
			Option::set('sale', 'basket_discount_converted', 'Y', '');
			//set to use new discounts by default.
			Option::set('sale', 'use_sale_discount_only', 'Y');
		}

		CAgent::AddAgent("CSaleRecurring::AgentCheckRecurring();", "sale", "N", 7200, "", "Y");
		CAgent::AddAgent("CSaleOrder::RemindPayment();", "sale", "N", 86400, "", "Y");
		CAgent::AddAgent("CSaleViewedProduct::ClearViewed();", "sale", "N", 86400, "", "Y");

		CAgent::AddAgent("CSaleOrder::ClearProductReservedQuantity();", "sale", "N", 86400, "", "Y");
		COption::SetOptionString("sale", "product_reserve_clear_period", "3");

		Option::set('sale', 'sale_locationpro_import_performed', 'Y');
		Option::set('sale', 'product_viewed_save', 'N', '');

		// install tasks + operations for statuses
		$operations = array();
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_view'     ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_cancel'   ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_mark'     ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_delivery' ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_deduction'));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_payment'  ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_to'       ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_update'   ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_delete'   ));
		$operations []= Bitrix\Main\OperationTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_from'     ));
		Bitrix\Main\TaskTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_none', 'SYS' => 'Y', 'LETTER' => 'D'));
		$result = Bitrix\Main\TaskTable::add(array('MODULE_ID' => 'sale', 'BINDING' => 'status', 'NAME' => 'sale_status_all', 'SYS' => 'Y', 'LETTER' => 'X'));
		if ($result->isSuccess())
		{
			$taskId = $result->getId();
			foreach ($operations as $result)
				if ($result->isSuccess())
					Bitrix\Main\TaskOperationTable::add(array('TASK_ID' => $taskId, 'OPERATION_ID' => $result->getId()));
		}

		if (\Bitrix\Main\Loader::includeModule('sale'))
		{
			\Bitrix\Sale\Compatible\EventCompatibility::registerEvents();
			
			// install statuses
			$orderInitialStatus = Bitrix\Sale\OrderStatus::getInitialStatus();
			$orderFinalStatus   = Bitrix\Sale\OrderStatus::getFinalStatus();
			$deliveryInitialStatus = Bitrix\Sale\DeliveryStatus::getInitialStatus();
			$deliveryFinalStatus   = Bitrix\Sale\DeliveryStatus::getFinalStatus();
			$statusLanguages = array();
			$result = Bitrix\Main\Localization\LanguageTable::getList(array(
				'select' => array('LID'),
				'filter' => array('=ACTIVE' => 'Y'),
			));
			while ($row = $result->Fetch())
			{
				$languageId = $row['LID'];
				Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/status.php', $languageId);
				foreach (array($orderInitialStatus, $orderFinalStatus, $deliveryInitialStatus, $deliveryFinalStatus) as $statusId)
					if ($statusName = Bitrix\Main\Localization\Loc::getMessage("SALE_STATUS_{$statusId}"))
						$statusLanguages[$statusId] []= array(
							'LID'         => $languageId,
							'NAME'        => $statusName,
							'DESCRIPTION' => Bitrix\Main\Localization\Loc::getMessage("SALE_STATUS_{$statusId}_DESCR"),
						);
			}
			Bitrix\Sale\OrderStatus::install(array(
				'ID'     => $orderInitialStatus,
				'SORT'   => 100,
				'NOTIFY' => 'Y',
				'LANG'   => $statusLanguages[$orderInitialStatus],
			));
			Bitrix\Sale\OrderStatus::install(array(
				'ID'     => $orderFinalStatus,
				'SORT'   => 200,
				'NOTIFY' => 'Y',
				'LANG'   => $statusLanguages[$orderFinalStatus],
			));
			Bitrix\Sale\DeliveryStatus::install(array(
				'ID'     => $deliveryInitialStatus,
				'SORT'   => 300,
				'NOTIFY' => 'Y',
				'LANG'   => $statusLanguages[$deliveryInitialStatus],
			));
			Bitrix\Sale\DeliveryStatus::install(array(
				'ID'     => $deliveryFinalStatus,
				'SORT'   => 400,
				'NOTIFY' => 'Y',
				'LANG'   => $statusLanguages[$deliveryFinalStatus],
			));

			// enabling location pro
			COption::SetOptionString("sale", "sale_locationpro_migrated", "Y");
			COption::SetOptionString("sale", "sale_locationpro_enabled", "Y");

			if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
			{
				// this will create at least base types if we are at Bitrix24
				include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/sale/lib/location/migration/migrate.php");
				\Bitrix\Sale\Location\Migration\CUpdaterLocationPro::createBaseTypes();
			}

			CSaleYMHandler::install();
		}

		if(Option::get('sale', 'use_sale_discount_only') !== 'Y')
		{
			\CAdminNotify::add(
				array(
					"MESSAGE" => Loc::getMessage('SALE_UPDATER_16036_MIGRATE_NOTIFY', array(
						"#LINK#" => "/bitrix/admin/sale_discount_catalog_migrator.php?lang=" . LANGUAGE_ID,
					)),
					"TAG" => "sale_discount_catalog_migrator",
					"MODULE_ID" => "sale",
					"ENABLE_CLOSE" => "N",
				)
			);
		}


		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/db/".$DBType."/uninstall.sql");

			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode("", $this->errors));
				return false;
			}
		}

		UnRegisterModuleDependences("catalog", "OnSaleOrderSumm", "sale", "CSaleOrder", "__SaleOrderCount");

		UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", "/modules/sale/affiliate.php");
		UnRegisterModuleDependences("main", "OnUserLogin", "sale", "CSaleUser", "OnUserLogin");
		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "sale", "CSalePersonType", "OnBeforeLangDelete");
		UnRegisterModuleDependences("main", "OnLanguageDelete", "sale", "CSaleLocation", "OnLangDelete");
		UnRegisterModuleDependences("main", "OnLanguageDelete", "sale", "CSaleLocationGroup", "OnLangDelete");

		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleOrderUserProps", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUserAccount", "OnUserDelete");

		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleAuxiliary", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUser", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleRecurring", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "sale", "CSaleUserCards", "OnUserDelete");

		UnRegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleOrder", "OnBeforeUserDelete");
		UnRegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleAffiliate", "OnBeforeUserDelete");
		UnRegisterModuleDependences("main", "OnBeforeUserDelete", "sale", "CSaleUserAccount", "OnBeforeUserDelete");

		UnRegisterModuleDependences("currency", "OnBeforeCurrencyDelete", "sale", "CSaleOrder", "OnBeforeCurrencyDelete");
		UnRegisterModuleDependences("currency", "OnBeforeCurrencyDelete", "sale", "CSaleLang", "OnBeforeCurrencyDelete");
		UnRegisterModuleDependences("currency", "OnModuleUnInstall", "sale", "", "CurrencyModuleUnInstallSale");

		UnRegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "sale", "CSaleMobileOrderUtils", "buildSaleAdminMobileMenu");
		UnRegisterModuleDependences("sender", "OnConnectorList", "sale", "\\Bitrix\\Sale\\SenderEventHandler", "onConnectorListBuyer");
		UnRegisterModuleDependences("sender", "OnTriggerList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onTriggerList");
		UnRegisterModuleDependences("sender", "OnPresetMailingList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetMailingList");
		UnRegisterModuleDependences("sender", "OnPresetTemplateList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetTemplateList");

		UnRegisterModuleDependences("sender", "OnConnectorList", "sale", "Bitrix\\Sale\\Bigdata\\TargetSaleMailConnector", "onConnectorList");

		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlBasketGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionGiftCtrlGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlBasketFields", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlOrderFields", "GetControlDescr");
		UnRegisterModuleDependences("sale", "onBuildDiscountConditionInterfaceControls", "sale", "CSaleCondCtrlPastOrder", "onBuildDiscountConditionInterfaceControls");
		UnRegisterModuleDependences("sale", "onBuildDiscountConditionInterfaceControls", "sale", "CSaleCondCumulativeCtrl", "onBuildDiscountConditionInterfaceControls");
		UnRegisterModuleDependences("sale", "OnCondSaleControlBuildList", "sale", "CSaleCondCtrlCommon", "GetControlDescr");

		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlDelivery", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlBasketGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCtrlSubGroup", "GetControlDescr");
		UnRegisterModuleDependences("sale", "OnCondSaleActionsControlBuildList", "sale", "CSaleActionCondCtrlBasketFields", "GetControlDescr");

		UnRegisterModuleDependences("sale", "onBuildDiscountActionInterfaceControls", "sale", "CSaleCumulativeAction", "onBuildDiscountActionInterfaceControls");

		UnRegisterModuleDependences("sale", "OnOrderDelete", "sale", "CSaleMobileOrderPull", "onOrderDelete");
		UnRegisterModuleDependences("sale", "OnOrderAdd", "sale", "CSaleMobileOrderPull", "onOrderAdd");
		UnRegisterModuleDependences("sale", "OnOrderUpdate", "sale", "CSaleMobileOrderPull", "onOrderUpdate");

		UnRegisterModuleDependences("sale", "OnSaleStatusOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleStatusOrderHandler");
		UnRegisterModuleDependences("sale", "OnSaleDeliveryOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleDeliveryOrderHandler");
		UnRegisterModuleDependences("sale", "OnSaleDeductOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleDeductOrderHandler");
		UnRegisterModuleDependences("sale", "OnSaleCancelOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSaleCancelOrderHandler");
		UnRegisterModuleDependences("sale", "OnSalePayOrder", "sale", "\\Bitrix\\Sale\\Product2ProductTable", "onSalePayOrderHandler");

		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSaleYMHandler", 'OnEventLogGetAuditTypes');
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSalePaySystemAction", 'OnEventLogGetAuditTypes');

		UnRegisterModuleDependences("main", "OnUserConsentProviderList", "sale", "\\Bitrix\\Sale\\UserConsent", "onProviderList");
		UnRegisterModuleDependences("main", "OnUserConsentDataProviderList", "sale", "\\Bitrix\\Sale\\UserConsent", "onDataProviderList");

		UnRegisterModuleDependences('sale', 'OnGetBusinessValueGroups', 'sale', '\Bitrix\Sale\PaySystem\Manager', 'getBusValueGroups');
		UnRegisterModuleDependences('sale', 'OnGetBusinessValueConsumers', 'sale', '\Bitrix\Sale\PaySystem\Manager', 'getConsumersList');

		UnRegisterModuleDependences('sale', 'OnGetBusinessValueGroups', 'sale', '\Bitrix\Sale\Delivery\Services\Manager', 'onGetBusinessValueGroups');
		UnRegisterModuleDependences('sale', 'OnGetBusinessValueConsumers', 'sale', '\Bitrix\Sale\Delivery\Services\Manager', 'onGetBusinessValueConsumers');

		// conversion
		UnRegisterModuleDependences('conversion', 'OnGetCounterTypes'    , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetCounterTypes'    );
		UnRegisterModuleDependences('conversion', 'OnGetRateTypes'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetRateTypes'       );
		UnRegisterModuleDependences('conversion', 'OnGenerateInitialData', 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGenerateInitialData');
		UnRegisterModuleDependences('sale'      , 'OnBeforeBasketAdd'    , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketAdd'    );
		UnRegisterModuleDependences('sale'      , 'OnBasketAdd'          , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketAdd'          );
		UnRegisterModuleDependences('sale'      , 'OnBeforeBasketUpdate' , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketUpdate' );
		UnRegisterModuleDependences('sale'      , 'OnBasketUpdate'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketUpdate'       );
		UnRegisterModuleDependences('sale'      , 'OnBeforeBasketDelete' , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBeforeBasketDelete' );
		UnRegisterModuleDependences('sale'      , 'OnBasketDelete'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onBasketDelete'       );
		UnRegisterModuleDependences('sale'      , 'OnOrderAdd'           , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onOrderAdd'           );
		UnRegisterModuleDependences('sale'      , 'OnSalePayOrder'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onSalePayOrder'       );
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "sale", "sale", "OnGetTableSchema");

		UnRegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'sale', '\Bitrix\Sale\PaySystem\RestService', 'onRestServiceBuildDescription');

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnUserLogout', 'sale', '\Bitrix\Sale\DiscountCouponsManager', 'logout');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleBasketItemEntitySaved', 'sale', '\Bitrix\Sale\Internals\Events', 'onSaleBasketItemEntitySaved');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleBasketItemDeleted', 'sale', '\Bitrix\Sale\Internals\Events', 'onSaleBasketItemDeleted');
		$eventManager->unRegisterEventHandler('sale', 'OnSaleBasketItemRefreshData', 'sale', '\Bitrix\Sale\Compatible\DiscountCompatibility', 'OnSaleBasketItemRefreshData');

		if (\Bitrix\Main\Loader::includeModule('sale'))
		{
			\Bitrix\Sale\Compatible\EventCompatibility::unRegisterEvents();
		}

		CAgent::RemoveModuleAgents("sale");

		ModuleManager::unRegisterModule('sale');

		return true;
	}

	function InstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/events.php");
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		$statusMes = Array();
		$dbStatus = $DB->Query("SELECT * FROM b_sale_status", true);

		if($dbStatus)
		{
			while($arStatus = $dbStatus->Fetch())
			{
				$statusMes[] = "SALE_STATUS_CHANGED_".$arStatus["ID"];
			}
		}

		$statusMes[] = "SALE_NEW_ORDER";
		$statusMes[] = "SALE_ORDER_CANCEL";
		$statusMes[] = "SALE_ORDER_PAID";
		$statusMes[] = "SALE_ORDER_DELIVERY";
		$statusMes[] = "SALE_RECURRING_CANCEL";
		$statusMes[] = "SALE_STATUS_CHANGED";
		$statusMes[] = "SALE_ORDER_REMIND_PAYMENT";
		$statusMes[] = "SALE_NEW_ORDER_RECURRING";
		$statusMes[] = "SALE_ORDER_TRACKING_NUMBER";
		$statusMes[] = "SALE_SUBSCRIBE_PRODUCT";
		$statusMes[] = "SALE_CHECK_PRINT";
		$statusMes[] = "SALE_ORDER_SHIPMENT_STATUS_CHANGED";

		$eventType = new CEventType;
		$eventM = new CEventMessage;
		foreach($statusMes as $v)
		{
			$eventType->Delete($v);
			$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => $v));
			while($arEvent = $dbEvent->Fetch())
			{
				$eventM->Delete($arEvent["ID"]);
			}
		}

		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/panel", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/sale", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/wizards", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/services", $_SERVER["DOCUMENT_ROOT"]."/bitrix/services", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/fonts", $_SERVER["DOCUMENT_ROOT"]."/bitrix/fonts", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if ($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFilesEx("/bitrix/js/sale/");//javascript
			DeleteDirFilesEx("/bitrix/css/sale/");//javascript
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
			DeleteDirFilesEx("/bitrix/themes/.default/icons/sale/");//icons
			DeleteDirFilesEx("/bitrix/images/sale/");//images
			DeleteDirFilesEx("/bitrix/panel/sale/");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");//tools
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/services", $_SERVER["DOCUMENT_ROOT"]."/bitrix/services");
		}

		return true;
	}

	function OnGetTableSchema()
	{
		return array(
			'sale' => array(
				'b_sale_discount' => array(
					'ID' => array(
						'b_sale_discount_coupon' => 'DISCOUNT_ID',
						'b_sale_discount_group' => 'DISCOUNT_ID',
						'b_sale_discount_module' => 'DISCOUNT_ID',
						'b_sale_discount_entities' => 'DISCOUNT_ID',
						'b_sale_order_discount' => 'DISCOUNT_ID',
					),
				),
				'b_sale_order_discount' => array(
					'ID' => array(
						'b_sale_order_coupons' => 'ORDER_DISCOUNT_ID',
						'b_sale_order_modules' => 'ORDER_DISCOUNT_ID',
						'b_sale_order_rules' => 'ORDER_DISCOUNT_ID',
						'b_sale_order_rules_descr' => 'ORDER_DISCOUNT_ID',
					),
				),
				'b_sale_order' => array(
					'ID' => array(
						'b_sale_order_coupons' => 'ORDER_ID',
						'b_sale_order_rules' => 'ORDER_ID',
						'b_sale_order_discount_data' => 'ORDER_ID',
						'b_sale_order_rules_descr' => 'ORDER_ID',
					),
				),
				'b_sale_discount_coupon' => array(
					'ID' => array(
						'b_sale_order_coupons' => 'COUPON_ID',
						'b_sale_order_rules' => 'COUPON_ID',
					),
				),
				'b_sale_order_rules' => array(
					'ID' => array(
						'b_sale_order_rules_descr' => 'RULE_ID',
					),
				),
			),
			'main' => array(
				'b_group' => array(
					'ID' => array(
						'b_sale_discount_group' => 'GROUP_ID',
					)
				),
				'b_user' => array(
					'ID' => array(
						'b_sale_discount' => 'MODIFIED_BY',
						'b_sale_discount^' => 'CREATED_BY',
						'b_sale_discount_coupon' => 'USER_ID',
						'b_sale_discount_coupon^' => 'MODIFIED_BY',
					)
				),
			),
		);
	}
}
