<?

/** @global CUser $USER
 * @global CMain $APPLICATION
 * @global CAdminMenu $adminMenu */

use Bitrix\Main\Application;
use Bitrix\Sale\Location;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Sale\Configuration;
use Bitrix\Sale\ShopSitesController;

IncludeModuleLangFile(__FILE__);
$aMenu = array();

$bViewAll = false;
$boolVat = false;
$boolStore = false;
$boolGroup = false;
$boolPrice = false;
$boolExportEdit = false;
$boolExportExec = false;
$boolImportEdit = false;
$boolImportExec = false;
$discountView = false;

$catalogInstalled = ModuleManager::isModuleInstalled('catalog');
if ($catalogInstalled)
{
	$bViewAll = $USER->CanDoOperation('catalog_read');
	$boolVat = $USER->CanDoOperation('catalog_vat');

	$boolStore = $USER->CanDoOperation('catalog_store');
	$boolGroup = $USER->CanDoOperation('catalog_group');
	$boolPrice = $USER->CanDoOperation('catalog_price');
	$boolExportEdit = $USER->CanDoOperation('catalog_export_edit');
	$boolExportExec = $USER->CanDoOperation('catalog_export_exec');
	$boolImportEdit = $USER->CanDoOperation('catalog_import_edit');
	$boolImportExec = $USER->CanDoOperation('catalog_import_exec');
	$discountView = $USER->CanDoOperation('catalog_discount');
}

global $adminMenu;

if ($APPLICATION->GetGroupRight("sale")!="D")
{

	/* Converter Begin */
	if (Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y') == 'N')
	{

		$aMenu[] = array(
			"parent_menu" => "global_menu_store",
			"sort" => 100,
			"text" => GetMessage("SALE_ORDERS"),
			"title" => GetMessage("SALE_ORDERS_DESCR"),
			"icon" => "sale_menu_icon_orders",
			"page_icon" => "sale_page_icon_orders",
			"url" => "sale_order.php?lang=".LANGUAGE_ID,
			"items_id" => "menu_order",
			"more_url" => array(
				"sale_order_detail.php",
				"sale_order_edit.php",
				"sale_order_print.php",
				"sale_order_new.php"
			),
		);

		$arMenu = array(
			"parent_menu" => "global_menu_store",
			"sort" => 1,
			"text" => GetMessage("SALE_MASTER_CONVERTER_TEXT"),
			"title" => GetMessage("SALE_MASTER_CONVERTER_TITLE"),
			"url" => "sale_converter.php?lang=".LANGUAGE_ID,
			"icon" => "workflow_menu_icon",
			"page_icon" => "sale_page_icon"
		);

		$aMenu[] = $arMenu;
	}
	else
	{
		/* Orders Begin*/
		$arMenu = array(
			"parent_menu" => "global_menu_store",
			"sort" => 100,
			"text" => GetMessage("SALE_ORDERS"),
			"title" => GetMessage("SALE_ORDERS_DESCR"),
			"icon" => "sale_menu_icon_orders",
			"page_icon" => "sale_page_icon_orders",
			"url" => "sale_order.php?lang=".LANGUAGE_ID,
			"items_id" => "menu_order",
			"more_url" => array(
				"sale_order_detail.php",
				"sale_order_edit.php",
				"sale_order_print.php",
				"sale_order_new.php",
				"sale_order_create.php",
				"sale_order_view.php"
			),
			"items" => array()
		);

		if (Loader::includeModule('sale') && !\Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
		{
			$arMenu["items"] = array(
				array(
					"text" => GetMessage("SALE_ORDER_PAYMENT"),
					"title" => GetMessage("SALE_ORDER_PAYMENT_DESCR"),
					"url" => "sale_order_payment.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_order_payment_edit.php"),
					"items_id" => "sale_order_payment_edit",
				),
				array(
					"text" => GetMessage("SALE_ORDER_DELIVERY"),
					"title" => GetMessage("SALE_ORDER_DELIVERY_DESCR"),
					"url" => "sale_order_shipment.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_order_shipment_edit.php"),
					"items_id" => "sale_order_shipment_edit",
				),
				array(
					"text" => GetMessage("SALE_MENU_DELIVERY_REQUESTS"),
					"title" => GetMessage("SALE_MENU_DELIVERY_REQUESTS"),
					"url" => "sale_delivery_request_list.php?lang=".LANGUAGE_ID,
					"page_icon" => "sale_page_icon",
					"more_url" => array(
						"sale_delivery_request.php",
						"sale_delivery_request_list.php",
						"sale_delivery_request_view.php"
					),
					"items_id" => "sale_delivery_request_list",
				),
				array(
					"text" => GetMessage("SALE_ORDERS_ARCHIVE"),
					"title" => GetMessage("SALE_ORDERS_ARCHIVE"),
					"url" => "sale_order_archive.php?lang=".LANGUAGE_ID,
					"more_url" => array(
						"sale_order_archive_view.php",
						"sale_archive.php?lang=".LANGUAGE_ID
					),
					"items_id" => "sale_order_archive",
				)
			);
		}

		$aMenu[] = $arMenu;
	}

	/* Orders End*/

	if (Loader::includeModule('sale') && \Bitrix\Sale\Configuration::isCanUsePersonalization())
	{
		$aMenu[] = array(
			"parent_menu" => "global_menu_marketing",
			"sort" => 800,
			"text" => GetMessage("SALE_BIGDATA"),
			"title" => GetMessage("SALE_BIGDATA"),
			"icon" => "sale_menu_icon_bigdata",
			"url" => "sale_personalization.php?lang=".LANGUAGE_ID,
			"items_id" => "sale_personalization",
		);
	}

	$aMenu[] = array(
		"parent_menu" => "global_menu_marketing",
		"sort" => 1100,
		"text" => GetMessage("SALE_MENU_MARKETING_MARKETPLACE_ADD"),
		"title" => GetMessage("SALE_MENU_MARKETING_MARKETPLACE_ADD"),
		"icon" => "sale_menu_icon_marketplace",
		"url" => "update_system_market.php?category=89&lang=".LANGUAGE_ID,
		"items_id" => "update_system_market",
	);

	$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
	$isAllowedRegion = $region !== null && $region !== 'ru';

	$hasShops = !empty(ShopSitesController::getShops());

	if ($isAllowedRegion && $hasShops)
	{
		$aMenu[] = array(
			"parent_menu" => "global_menu_marketing",
			"sort" => 1200,
			"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES"),
			"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES"),
			"icon" => "sale_menu_icon_facebook",
			"items_id" => "facebook_audiences",
			"items" => [
				[
					"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_ADD_TO_CART"),
					"url" => "facebook_audiences_add_to_cart.php?lang=" . LANGUAGE_ID,
					"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_ADD_TO_CART"),
				],
				[
					"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_INITIATE_CHECKOUT"),
					"url" => "facebook_audiences_initiate_checkout.php?lang=" . LANGUAGE_ID,
					"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_INITIATE_CHECKOUT"),
				],
				[
					"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_ADD_PAYMENT_INFO"),
					"url" => "facebook_audiences_add_payment_info.php?lang=" . LANGUAGE_ID,
					"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_ADD_PAYMENT_INFO"),
				],
				[
					"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_CUSTOMIZE_PRODUCT"),
					"url" => "facebook_audiences_customize_product.php?lang=" . LANGUAGE_ID,
					"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_CUSTOMIZE_PRODUCT"),
				],
				[
					"text" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_CONTACT"),
					"url" => "facebook_audiences_contact.php?lang=" . LANGUAGE_ID,
					"title" => GetMessage("SALE_MENU_MARKETING_FACEBOOK_AUDIENCES_CONTACT"),
				],
			]
		);
	}

	/* Catalog Begin*/
	// included in catalog/general/admin.php
	/* Catalog End*/

	/* CASHBOX Begin*/
	if ($APPLICATION->GetGroupRight("sale") == "W")
	{
		$currentZone = '';
		if (Loader::includeModule("bitrix24"))
		{
			$currentZone = \CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$currentZone = \CIntranetUtils::getPortalZone();
		}

		$isAvailable = true;
		if (
			$currentZone !== ''
			&& !in_array($currentZone, ['ru', 'ua'])
		)
		{
			$isAvailable = false;
		}

		if ($isAvailable)
		{
			$arMenu = [
				"parent_menu" => "global_menu_store",
				"sort" => 300,
				"text" => GetMessage("SALE_CASHBOX_TITLE"),
				"title" => GetMessage("SALE_CASHBOX"),
				"icon" => "crm-cashbox-icon",
				"url" => $currentZone !== 'ua' ? "sale_cashbox.php?lang=".LANGUAGE_ID : '',
				"page_icon" => "sale_page_icon_crm",
				"items_id" => "menu_sale_cashbox",
				"items" => [],
			];

			$arMenu["items"][] = [
				"text" => GetMessage("SALE_CASHBOX_LIST"),
				"title" => GetMessage("SALE_CASHBOX_LIST"),
				"url" => "sale_cashbox_list.php?lang=".LANGUAGE_ID,
				"more_url" => ["sale_cashbox_edit.php"],
				"items_id" => "sale_cashbox_list",
				"sort" => 301,
			];

			$arMenu["items"][] = [
				"text" => GetMessage("SALE_CASHBOX_CHECK"),
				"title" => GetMessage("SALE_CASHBOX_CHECK"),
				"url" => "sale_cashbox_check.php?lang=".LANGUAGE_ID,
				"more_url" => ["sale_cashbox_check_edit.php"],
				"items_id" => "sale_cashbox_check",
				"sort" => 302,
			];

			if (
				IsModuleInstalled('crm')
				&& \Bitrix\Sale\Cashbox\CheckManager::isAvailableCorrection()
			)
			{
				$arMenu["items"][] = [
					"text" => GetMessage("SALE_CASHBOX_CHECK_CORRECTION"),
					"title" => GetMessage("SALE_CASHBOX_CHECK_CORRECTION"),
					"url" => "sale_cashbox_correction.php?lang=".LANGUAGE_ID,
					"more_url" => [],
					"items_id" => "sale_cashbox_correction",
					"sort" => 303,
				];
			}

			$arMenu["items"][] = [
				"text" => GetMessage("SALE_CASHBOX_ZREPORT"),
				"title" => GetMessage("SALE_CASHBOX_ZREPORT"),
				"url" => "sale_cashbox_zreport.php?lang=".LANGUAGE_ID,
				"more_url" => [],
				"items_id" => "sale_cashbox_zreport",
				"sort" => 304,
			];

			$aMenu[] = $arMenu;
		}
	}
	/* CASHBOX End*/

	/* CRM Begin*/
	if ($APPLICATION->GetGroupRight("sale") == "W")
	{
		$arMenu =
			array(
				"parent_menu" => "global_menu_store",
				"sort" => 300,
				"text" => GetMessage("SM_CRM"),
				"title" => GetMessage("SALE_CRM_DESCR"),
				"icon" => "sale_menu_icon_crm",
				"page_icon" => "sale_page_icon_crm",
				"url" => "sale_crm.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_crm.php"),
				"items_id" => "sale_crm",
				"items" => Array(),
			);

		if (Loader::includeModule('sale') && Configuration::isAvailableOrdersImportFromB24())
		{
			$arMenu["items"][] = array(
				"text" =>  GetMessage("SALE_SYNC_DESCR"),
				"title" => GetMessage("SALE_SYNC_TITLE"),
				"url" => "sale_synchronizer_settings.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_synchronizer_settings.php"),
				"items_id" => "sale_synchronizer_settings",
				"sort" => 302,
			);
		}

		$aMenu[] = $arMenu;
	}
	/* CRM End*/

	/* Buyers Begin*/
	$arMenu = array(
		"parent_menu" => "global_menu_store",
		"sort" => 400,
		"text" => GetMessage("SALE_BUYERS"),
		"title" => GetMessage("SALE_BUYERS"),
		"icon" => "sale_menu_icon_buyers",
		"page_icon" => "sale_page_icon_buyers",
		"items_id" => "menu_sale_buyers",
		"url" => "sale_buyers.php?lang=".LANGUAGE_ID,
		"items" => Array(),
	);
	if(CBXFeatures::IsFeatureEnabled('SaleAccounts'))
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_BUYERS_DESCR"),
			"title" => GetMessage("SALE_BUYERS_DESCR"),
			"url" => "sale_buyers.php?lang=".LANGUAGE_ID,
			"more_url" => array(
				"sale_buyers_profile.php",
				"sale_buyers_profile_edit.php",
				"sale_buyers_account.php",
				"sale_buyers_user.php",
			),
			"items_id" => "sale_buyers",
			"sort" => 401,
		);
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_BASKET"),
			"title" => GetMessage("SALE_BASKET"),
			"url" => "sale_basket.php?lang=".LANGUAGE_ID,
			"items_id" => "sale_basket",
			"sort" => 402,
		);
	}
	$arMenu["items"][] = array(
		"text" => GetMessage("SM_ACCOUNTS"),
		"title" => GetMessage("SM_ACCOUNTS_ALT"),
		"url" => "sale_account_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array("sale_account_edit.php"),
		"items_id" => "sale_account_admin",
		"sort" => 403,
	);
	$arMenu["items"][] = array(
		"text" => GetMessage("SM_TRANSACT"),
		"title" => GetMessage("SM_TRANSACT"),
		"url" => "sale_transact_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array("sale_transact_edit.php"),
		"items_id" => "sale_transact_admin",
		"sort" => 404,
	);

	if(CBXFeatures::IsFeatureEnabled('SaleRecurring'))
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SM_RENEW"),
			"title" => GetMessage("SM_RENEW_ALT"),
			"url" => "sale_recurring_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_recurring_edit.php"),
			"items_id" => "sale_recurring_admin",
			"sort" => 405,
		);
	}
	if (CBXFeatures::IsFeatureEnabled('SaleCCards') && COption::GetOptionString("sale", "use_ccards", "N") == "Y")
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SM_CCARDS"),
			"title" => GetMessage("SM_CCARDS"),
			"url" => "sale_ccards_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_ccards_edit.php"),
			"items_id" => "sale_ccards_admin",
			"sort" => 406,
		);
	}

	$aMenu[] = $arMenu;
	/* Buyers End*/
}
/* Discounts Begin*/
if ($APPLICATION->GetGroupRight("sale") == "W" || $discountView || $bViewAll)
{
	$useSaleDiscountOnly = (string)Option::get('sale', 'use_sale_discount_only') == 'Y';
	$arMenu = array(
		"parent_menu" => "global_menu_marketing",
		"sort" => 500,
		"text" => GetMessage("CM_PRODUCTS_MARKETING"),
		"title" => GetMessage("CM_PRODUCTS_MARKETING_TITLE"),
		"icon" => "sale_menu_icon_catalog",
		"page_icon" => "sale_page_icon_catalog",
		"items_id" => "menu_sale_discounts",
		"items" => array(),
	);

	if ($useSaleDiscountOnly)
	{
		if ($APPLICATION->GetGroupRight('sale') > 'D')
		{
			if ($APPLICATION->GetGroupRight('sale') >= 'W')
			{
				$arMenu["items"][] = array(
					"text" => GetMessage("SALE_MENU_DISCOUNT_PRESETS_NEW"),
					"title" => GetMessage("SALE_MENU_DISCOUNT_PRESETS_NEW"),
					"url" => "sale_discount_preset_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_discount_preset_detail.php"),
					"items_id" => "sale_discount_preset_list",
				);
			}
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_MENU_DISCOUNT"),
				"title" => GetMessage("SALE_MENU_DISCOUNT_TITLE"),
				"url" => "sale_discount.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_discount_edit.php"),
				"items_id" => "sale_discount",
			);
			$arMenu["items"][] = array(
				"text" => (GetMessage('SALE_MENU_DISCOUNT_COUPONS')),
				"title" => GetMessage("SALE_MENU_DISCOUNT_COUPONS_TITLE"),
				"url" => "sale_discount_coupons.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_discount_coupon_edit.php"),
				"items_id" => "sale_discount_coupons",
			);
		}
	}
	else
	{
		if ($APPLICATION->GetGroupRight('sale') > 'D')
		{
			if ($APPLICATION->GetGroupRight('sale') >= 'W')
			{
				$arMenu["items"][] = array(
					"text" => GetMessage("SALE_MENU_DISCOUNT_PRESETS_NEW"),
					"title" => GetMessage("SALE_MENU_DISCOUNT_PRESETS_NEW"),
					"url" => "sale_discount_preset_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_discount_preset_detail.php"),
					"items_id" => "sale_discount_preset_list",
				);
			}
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_MENU_DISCOUNT"),
				"title" => GetMessage("SALE_MENU_DISCOUNT_TITLE"),
				"items_id" => "menu_sale_discount",
				"items" => array(
					array(
						"text" => GetMessage("SALE_MENU_DISCOUNT"),
						"title" => GetMessage("SALE_MENU_DISCOUNT_TITLE"),
						"url" => "sale_discount.php?lang=".LANGUAGE_ID,
						"more_url" => array("sale_discount_edit.php"),
						"items_id" => "sale_discount",
					),
					array(
						"text" => (GetMessage('SALE_MENU_DISCOUNT_COUPONS_EXT')),
						"title" => GetMessage("SALE_MENU_DISCOUNT_COUPONS_TITLE"),
						"url" => "sale_discount_coupons.php?lang=".LANGUAGE_ID,
						"more_url" => array("sale_discount_coupon_edit.php"),
						"items_id" => "sale_discount_coupons",
					)
				)
			);
		}
	}

	if ($USER->CanDoOperation('install_updates'))
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_MENU_MARKETING_MARKETPLACE_ADD"),
			"title" => GetMessage("SALE_MENU_MARKETING_MARKETPLACE_ADD"),
			"items_id" => "menu_sale_marketplace",
			"url" => "update_system_market.php?category=111&lang=".LANGUAGE_ID,
			"more_url" => array("update_system_market.php?category=111")
		);
	}

	$aMenu[] = $arMenu;
}
/* Discounts End*/

if ($boolStore || $bViewAll)
{
	$arMenu = array(
		"parent_menu" => "global_menu_store",
		"sort" => 550,
		"text" => GetMessage("SALE_STORE_1"),
		"title" => GetMessage("SALE_STORE_DESCR_1"),
		"icon" => "sale_menu_icon_store",
		"page_icon" => "sale_page_icon_store",
		"items_id" => "menu_catalog_store",
		"url" => "cat_store_list.php?lang=".LANGUAGE_ID,
		"items" => array(),
	);
	$aMenu[] = $arMenu;
}

if ($APPLICATION->GetGroupRight("sale") != "D")
{
	/* Reports Begin*/
	if(CBXFeatures::IsFeatureEnabled('SaleReports'))
	{
		$arMenu = array(
			"parent_menu" => "global_menu_store",
			"sort" => 600,
			"text" => GetMessage("SALE_REPORTS"),
			"title" => GetMessage("SALE_REPORTS_DESCR"),
			"icon" => "sale_menu_icon_statistic",
			"page_icon" => "sale_page_icon_statistic",
			"items_id" => "menu_sale_stat",
			"items" => array(),
		);

		if (IsModuleInstalled('report'))
		{
			$arSaleReports = array();
			if($adminMenu && method_exists($adminMenu, "IsSectionActive"))
			{
				if($adminMenu->IsSectionActive("menu_sale_report") && CModule::IncludeModule("report"))
				{
					CModule::IncludeModule("sale");
					CBaseSaleReportHelper::initOwners();
					$dbRepList = Bitrix\Report\ReportTable::getList(array(
																		'select' => array('ID', 'TITLE', 'DESCRIPTION'),
																		'filter' => array('=CREATED_BY' => $USER->GetID(), '=OWNER_ID' => CBaseSaleReportHelper::getOwners())
																	));
					while($arReport = $dbRepList->fetch())
					{
						$arSaleReports[] = array(
							"text" => htmlspecialcharsbx($arReport["TITLE"]),
							"title" => htmlspecialcharsbx($arReport["DESCRIPTION"]),
							"url" => "sale_report_view.php?lang=".LANGUAGE_ID."&ID=".$arReport["ID"],
							"more_url" => array("sale_report_construct.php?lang=".LANGUAGE_ID."&ID=".$arReport["ID"]),
							"items_id" => "sale_report_view",
						);
					}
				}
			}

			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_REPORTS_DESCR"),
				"title" => GetMessage("SALE_REPORTS_DESCR"),
				"url" => "sale_report.php?lang=".LANGUAGE_ID,
				"more_url" => array(
					"sale_report_construct.php",
					"sale_report_view.php"
				),
				"dynamic" => true,
				"module_id" => "sale",
				"items_id" => "menu_sale_report",
				"items" => $arSaleReports,
			);
		}

		$arMenu["items"][] = array(
			"text" => GetMessage("SM1_STAT"),
			"title" => GetMessage("SM1_STAT_ALT"),
			"url" => "sale_stat.php?lang=".LANGUAGE_ID."&set_default=Y",
			"more_url" => array(),
			"items_id" => "sale_stat",
		);
		$arMenu["items"][] = array(
			"text" => GetMessage("SM1_STAT_PRODUCTS"),
			"title" => GetMessage("SM1_STAT_PRODUCTS_ALT"),
			"url" => "sale_stat_products.php?lang=".LANGUAGE_ID."&set_default=Y",
			"more_url" => array(),
			"items_id" => "sale_stat_products",
		);
		$arMenu["items"][] = array(
			"text" => GetMessage("SM1_STAT_GRAPH"),
			"title" => GetMessage("SM1_STAT_GRAPH_DESCR"),
			"items_id" => "menu_sale_stat_graph",
			"items" => array(
				array(
					"text" => GetMessage("SM1_STAT_GRAPH_QUANTITY"),
					"title" => GetMessage("SM1_STAT_GRAPH_QUANTITY_DESCR"),
					"url" => "sale_stat_graph_index.php?lang=".LANGUAGE_ID."&set_default=Y",
					"items_id" => "sale_stat_graph_index",
				),
				array(
					"text" => GetMessage("SM1_STAT_GRAPH_MONEY"),
					"title" => GetMessage("SM1_STAT_GRAPH_MONEY_DESCR"),
					"url" => "sale_stat_graph_money.php?lang=".LANGUAGE_ID."&set_default=Y",
					"items_id" => "sale_stat_graph_money",
				),
			),
		);
		$aMenu[] = $arMenu;
	}
	/* Reports End*/
}

/* Settings Begin*/
if ($APPLICATION->GetGroupRight("sale") == "W" ||
	$bViewAll || $boolVat || $boolStore || $boolGroup || $boolPrice ||
	$boolExportEdit || $boolExportExec || $boolImportEdit || $boolImportExec
)
{
	$arMenu = array(
		"parent_menu" => "global_menu_store",
		"sort" => 700,
		"text" => GetMessage("SM_SETTINGS"),
		"title"=> GetMessage("SM_SETTINGS"),
		"icon" => "sale_menu_icon",
		"page_icon" => "sale_page_icon",
		"items_id" => "menu_sale_settings",
		"items" => array(),
	);

	if ($APPLICATION->GetGroupRight("sale") == "W")
	{
		if (Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y') != 'N')
		{
			if (CModule::IncludeModule("sale"))
			{
				$deliveryMenu = new \Bitrix\Sale\Delivery\Menu();
				$arMenu["items"][] = $deliveryMenu->getItems();
			}
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_COMPANY"),
				"title" => GetMessage("SALE_COMPANY_DESCR"),
				"url" => "sale_company.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_company_edit.php"),
				"items_id" => "sale_company",
				"sort" => 701,
			);
		}
		else
		{
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_DELIVERY"),
				"title" => GetMessage("SALE_DELIVERY_DESCR"),
				"items_id" => "menu_sale_delivery",
				"items" => array(
					array(
						"text" => GetMessage("SALE_DELIVERY_OLD"),
						"title" => GetMessage("SALE_DELIVERY_OLD_DESCR"),
						"url" => "sale_delivery.php?lang=".LANGUAGE_ID,
						"page_icon" => "sale_page_icon",
						"more_url" => array("sale_delivery_edit.php"),
						"items_id" => "sale_delivery",
						"sort" => 702,
					),
					array(
						"text" => GetMessage("SALE_DELIVERY_HANDLERS"),
						"title" => GetMessage("SALE_DELIVERY_HANDLERS_DESCR"),
						"url" => "sale_delivery_handlers.php?lang=".LANGUAGE_ID,
						"page_icon" => "sale_page_icon",
						"more_url" => array("sale_delivery_handler_edit.php"),
						"items_id" => "sale_delivery_handlers",
						"sort" => 703,
					),
				),
				"sort" => 701,
			);

		}

		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_PAY_SYS"),
			"title" => GetMessage("SALE_PAY_SYS_DESCR"),
			"url" => "sale_pay_system.php?lang=".LANGUAGE_ID,
			"items_id" => "sale_pay_system",
			"more_url" => array("sale_pay_system_edit.php", "sale_yandexinvoice_settings.php"),
			"items" => array(
				array(
					"text" => GetMessage("SALE_PAY_SYS_RETURN"),
					"title" => GetMessage("SALE_PAY_SYS_RETURN_DESCR"),
					"url" => "sale_ps_handler_refund.php?lang=".LANGUAGE_ID,
					"page_icon" => "sale_page_icon",
					"more_url" => array("sale_ps_handler_refund_edit.php"),
					"items_id" => "sale_ps_handler_refund",
					"sort" => 705,
				),
			),
			"sort" => 704,
		);

	}

	$arSubItems = array();
	if ($APPLICATION->GetGroupRight("sale") == "W")
	{
		$arSubItems[] = array(
			"text" => GetMessage("sale_menu_taxes"),
			"title" => GetMessage("sale_menu_taxes_title"),
			"url" => "sale_tax.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_tax_edit.php"),
			"items_id" => "sale_tax",
			"sort" => 706,
		);
		$arSubItems[] = array(
			"text" => GetMessage("SALE_TAX_RATE"),
			"title" => GetMessage("SALE_TAX_RATE_DESCR"),
			"url" => "sale_tax_rate.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_tax_rate_edit.php"),
			"items_id" => "sale_tax_rate",
			"sort" => 707,
		);
		$arSubItems[] = array(
			"text" => GetMessage("SALE_TAX_EX"),
			"title" => GetMessage("SALE_TAX_EX_DESCR"),
			"url" => "sale_tax_exempt.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_tax_exempt_edit.php"),
			"items_id" => "sale_tax_exempt",
			"sort" => 708,
		);
	}
	if ($APPLICATION->GetGroupRight("sale") == "W" || $bViewAll || $boolVat)
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_TAX"),
			"title" => GetMessage("SALE_TAX_DESCR"),
			"url" => "sale_tax.php?lang=".LANGUAGE_ID,
			"items_id" => "menu_sale_taxes",
			"items"=> $arSubItems,
			"sort" => 705,
		);
	}

	if ($APPLICATION->GetGroupRight("sale") == "W")
	{
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_PERSON_TYPE"),
			"title" => GetMessage("SALE_PERSON_TYPE_DESCR"),
			"url" => "sale_person_type.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_person_type_edit.php"),
			"items_id" => "sale_person_type",
			"sort" => 709,
		);
		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_STATUS"),
			"title" => GetMessage("SALE_STATUS_DESCR"),
			"url" => "sale_status.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_status_edit.php"),
			"items_id" => "sale_status",
			"sort" => 710,
		);

		if (Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y') != 'N')
		{
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_BUSINESS_VALUE"),
				"title" => GetMessage("SALE_BUSINESS_VALUE_DESCR"),
				"url" => "sale_business_value.php?lang=".LANGUAGE_ID,
				"more_url" => array("sale_business_value.php"),
				"items_id" => "menu_sale_bizval",
				"sort" => 711,
			);
		}

		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_ORDER_PROPS"),
			"title" => GetMessage("SALE_ORDER_PROPS_DESCR"),
			"items_id" => "menu_sale_properties",
			"items"=>array(
				array(
					"text" => GetMessage("sale_menu_properties"),
					"title" => GetMessage("sale_menu_properties_title"),
					"url" => "sale_order_props.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_order_props_edit.php"),
					"items_id" => "sale_order_props",
					"sort" => 714,
				),
				array(
					"text" => GetMessage("SALE_ORDER_PROPS_GR"),
					"title" => GetMessage("SALE_ORDER_PROPS_GR_DESCR"),
					"url" => "sale_order_props_group.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_order_props_group_edit.php"),
					"items_id" => "sale_order_props_group",
					"sort" => 715,
				),
			),
			"sort" => 713,
		);

		$arMenu["items"][] = array(
			"text" => GetMessage("SALE_ARCHIVE"),
			"title" => GetMessage("SALE_ARCHIVE_DESCR"),
			"url" => "sale_archive.php?lang=".LANGUAGE_ID,
			"items_id" => "sale_archive",
			"sort" => 716,
		);

		/* LOCATIONS BEGIN */
		// this file can be loaded directly, without module include, so ...
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

		if(class_exists('CSaleLocation'))
		{
			$locationMenu = array(
				"text" => GetMessage("SALE_LOCATION"),
				"title" => GetMessage("SALE_LOCATION_DESCR"),
				"url" => CHTTP::urlAddParams(Location\Admin\LocationHelper::getListUrl(0), ["apply_filter" => "y"]),
				"items_id" => "menu_sale_locations",
				"sort" => 717,
			);

			if(CSaleLocation::isLocationProEnabled())
			{
				$locationSubMenu = [];
				foreach (Location\Admin\LocationHelper::getLocationSubMenu() as $location)
				{
					if (!empty($location["url"]))
					{
						$location["url"] = CHTTP::urlAddParams($location["url"], ["apply_filter" => "y"]);
					}
					$locationSubMenu[] = $location;
				}
				$locationMenu["items"] = array(
					array(
						"text" => GetMessage("sale_menu_locations"),
						"title" => GetMessage("sale_menu_locations_title"),
						"url" => CHTTP::urlAddParams(Location\Admin\LocationHelper::getListUrl(0), ["apply_filter" => "y"]),
						"more_url" => array(Location\Admin\LocationHelper::getEditUrl()),
						"module_id" => "sale",
						"items_id" => "sale_location_node_list",
						"dynamic" => true,
						"items" => $locationSubMenu,
						"sort" => 718,
					),
					array(
						"text" => GetMessage("SALE_LOCATION_GROUPS"),
						"title" => GetMessage("SALE_LOCATION_GROUPS_DESCR"),
						"url" => Location\Admin\GroupHelper::getListUrl(),
						"more_url" => array(Location\Admin\GroupHelper::getEditUrl()),
						"items_id" => "sale_location_group_list",
						"sort" => 719,
					),
					array(
						"text" => GetMessage("SALE_MENU_LOCATION_ZONES"),
						"title" => GetMessage("SALE_MENU_LOCATION_ZONES_TITLE"),
						"url" => Location\Admin\SiteLocationHelper::getListUrl(),
						"more_url" => array(Location\Admin\SiteLocationHelper::getEditUrl()),
						"items_id" => "sale_location_zone_list",
						"sort" => 720,
					),
					array(
						"text" => GetMessage("SALE_MENU_LOCATION_DEFAULT"),
						"title" => GetMessage("SALE_MENU_LOCATION_DEFAULT_TITLE"),
						"url" => Location\Admin\DefaultSiteHelper::getListUrl(),
						"more_url" => array(Location\Admin\DefaultSiteHelper::getEditUrl()),
						"items_id" => "sale_location_default_list",
						"sort" => 721,
					),

					array(
						"text" => GetMessage("SALE_MENU_LOCATION_TYPES"),
						"title" => GetMessage("SALE_MENU_LOCATION_TYPES_TITLE"),
						"url" => Location\Admin\TypeHelper::getListUrl(),
						"more_url" => array(Location\Admin\TypeHelper::getEditUrl()),
						"items_id" => "sale_location_type_list",
						"sort" => 722,
					),
					array(
						"text" => GetMessage("SALE_MENU_LOCATION_SERVICES"),
						"title" => GetMessage("SALE_MENU_LOCATION_SERVICES_TITLE"),
						"url" => Location\Admin\ExternalServiceHelper::getListUrl(),
						"more_url" => array(Location\Admin\ExternalServiceHelper::getEditUrl()),
						"items_id" => "sale_location_external_service_list",
						"sort" => 723,
					),
					array(
						"text" => GetMessage("SALE_LOCATION_IMPORT"),
						"title" => GetMessage("SALE_LOCATION_IMPORT_DESCR"),
						"url" => Location\Admin\Helper::getImportUrl(),
						"items_id" => "sale_location_import",
						"sort" => 724,
					),
					array(
						"text" => GetMessage("SALE_LOCATION_REINDEX"),
						"title" => GetMessage("SALE_LOCATION_REINDEX_DESCR"),
						"url" => Location\Admin\Helper::getReindexUrl(),
						"items_id" => "sale_location_reindex",
						"sort" => 725,
					)
				);
			}
			else
			{
				$locationMenu["items"] = array(
					array(
						"text" => GetMessage("sale_menu_locations"),
						"title" => GetMessage("sale_menu_locations_title"),
						"url" => "sale_location_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array("sale_location_edit.php"),
						"items_id" => "sale_location_admin",
						"sort" => 718,
					),
					array(
						"text" => GetMessage("SALE_LOCATION_GROUPS"),
						"title" => GetMessage("SALE_LOCATION_GROUPS_DESCR"),
						"url" => "sale_location_group_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array("sale_location_group_edit.php"),
						"items_id" => "sale_location_group_admin",
						"sort" => 719,
					),
					array(
						"text" => GetMessage("SALE_LOCATION_IMPORT"),
						"title" => GetMessage("SALE_LOCATION_IMPORT_DESCR"),
						"url" => "sale_location_import.php?lang=".LANGUAGE_ID,
						"items_id" => "sale_location_import",
						"sort" => 720,
					),
				);

				$locationMenu["items"][] = array(
					"text" => GetMessage("SALE_MENU_LOCATION_MIGRATION"),
					"title" => GetMessage("SALE_MENU_LOCATION_MIGRATION_TITLE"),
					"url" => Location\Admin\Helper::getMigrationUrl(),
					"items_id" => "sale_location_migration",
					"sort" => 721,
				);
			}

			$arMenu["items"][] = $locationMenu;
			unset($locationMenu);
		}
		/* LOCATIONS END */

		if (Loader::includeModule('sale') && \Bitrix\Sale\Configuration::isCanUse1c())
		{
			$arMenu["items"][] = array(
				"text" => GetMessage("MAIN_MENU_1C_INTEGRATION"),
				"title" => GetMessage("MAIN_MENU_1C_INTEGRATION_TITLE"),
				"url" => "1c_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("1c_admin.php"),
				"items" => array(
					array(
						"text" => GetMessage("MAIN_MENU_1C_INTEGRATION_LOG"),
						"title" => GetMessage("MAIN_MENU_1C_INTEGRATION_LOG_TITLE"),
						"url" => "sale_exchange_log.php?lang=".LANGUAGE_ID,
						"items_id" => "sale_exchange_log",
					)
				),
				"items_id" => "1c_admin",
				"sort" => 726,
			);
		}

		$arMenu["items"][] = array(
			"text" => GetMessage("MAIN_MENU_REPORT_EDIT"),
			"title" => GetMessage("MAIN_MENU_REPORT_EDIT_TITLE"),
			"url" => "sale_report_edit.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_report_edit.php"),
			"items_id" => "sale_report_edit",
			"sort" => 727,
		);

		if ($APPLICATION->GetGroupRight("sale") == "W" && (LANGUAGE_ID == "ru" || LANGUAGE_ID == "ua"))
		{
			$arMenu["items"][] = array(
				"text" => GetMessage("SALE_TRADING_PLATFORMS"),
				"title" => GetMessage("SALE_TRADING_PLATFORMS_DESCR"),
				"items_id" => "menu_sale_trading_platforms",
				"items"=>array(
					array(
						"text" => GetMessage("SALE_MENU_VK"),
						"title" => GetMessage("SALE_MENU_VK_DESC"),
						"items_id" => "menu_sale_trading_platforms_vk",
						"more_url" => array("sale_vk_exchange.php"),
						"items" => array(
							array(
								"text" => GetMessage("SALE_MENU_VK_EXPORT"),
								"title" => GetMessage("SALE_MENU_VK_EXPORT_DESC"),
								"url" => "sale_vk_export_list.php?lang=" . LANGUAGE_ID,
								"more_url" => array("sale_vk_export_list.php", "sale_vk_export_edit.php"),
								"items_id" => "sale_vk_export_list",
								"sort" => 738,
							),
							array(
								"text" => GetMessage("SALE_MENU_VK_MANUAL"),
								"title" => GetMessage("SALE_MENU_VK_MANUAL_DESC"),
								"url" => "sale_vk_manual.php?lang=" . LANGUAGE_ID,
								"more_url" => array("sale_vk_manual.php"),
								"items_id" => "sale_vk_manual",
								"sort" => 739,
							),
						),
						"sort" => 737,
					),
					array(
						"text" => GetMessage("SALE_MENU_TRADING_PLATFORMS_MARKETPLACE_ADD"),
						"title" => GetMessage("SALE_MENU_TRADING_PLATFORMS_MARKETPLACE_ADD"),
						"items_id" => "menu_sale_trading_platforms_marketplace",
						"url" => "update_system_market.php?category=141&lang=".LANGUAGE_ID,
						"sort" => 740,
					),
				),
				"sort" => 728,
			);
		}
	}
	$aMenu[] = $arMenu;
}
/* Settings End*/

if ($APPLICATION->GetGroupRight("sale") != "D")
{
	/* Affiliates Begin*/
	if(CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	{
		$aMenu[] = array(
			"parent_menu" => "global_menu_store",
			"sort" => 800,
			"text" => GetMessage("SM1_AFFILIATES"),
			"title" => GetMessage("SM1_SHOP_AFFILIATES"),
			"icon" => "sale_menu_icon_buyers_affiliate",
			"page_icon" => "sale_page_icon_buyers",
			"items_id" => "menu_sale_affiliates",
			"items" => array(
				array(
					"text" => GetMessage("SM1_AFFILIATES_CALC"),
					"url" => "sale_affiliate_calc.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("SM1_AFFILIATES_CALC_ALT"),
					"items_id" => "sale_affiliate_calc",
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES"),
					"url" => "sale_affiliate.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_edit.php"),
					"title" => GetMessage("SM1_SHOP_AFFILIATES"),
					"items_id" => "sale_affiliate",
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_TRAN"),
					"url" => "sale_affiliate_transact.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("SM1_AFFILIATES_TRAN_ALT"),
					"items_id" => "sale_affiliate_transact",
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_PLAN"),
					"url" => "sale_affiliate_plan.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_plan_edit.php"),
					"title" => GetMessage("SM1_AFFILIATES_PLAN_ALT"),
					"items_id" => "sale_affiliate_plan",
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_TIER"),
					"url" => "sale_affiliate_tier.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_tier_edit.php"),
					"title" => GetMessage("SM1_AFFILIATES_TIER_ALT"),
					"items_id" => "sale_affiliate_tier",
				),
			),
		);
	}
	/* Affiliates End*/
}

if ($APPLICATION->GetGroupRight("sale") != "D" && $USER->CanDoOperation('install_updates'))
{
	$aMenu[] = array(
		"parent_menu" => "global_menu_store",
		"sort" => 900,
		"text" => GetMessage("SALE_MENU_MARKETPLACE_READY_SHOPS"),
		"title" => GetMessage("SALE_MENU_MARKETPLACE_READY_SHOPS"),
		"url" => "update_system_market.php?category=14&lang=".LANGUAGE_ID,
		"more_url" => array("update_system_market.php?category=14"),
		"icon" => "sale_menu_icon_marketplace",
		"page_icon" => "",
		"items_id" => "update_system_market",
	);
}

EventManager::getInstance()->addEventHandler("main", "OnBuildGlobalMenu", function (&$arGlobalMenu, &$arModuleMenu) {
	if (in_array(Application::getInstance()->getContext()->getLanguage(), ["ru", "ua"])
		&&
		(
			!ModuleManager::isModuleInstalled("intranet")
			|| Option::get("sale", "~IS_CRM_SITE_MASTER_OPENED", "N") === "Y"
		)
	)
	{
		$arGlobalMenu["global_menu_crm_site_master"] = [
			"menu_id" => "crm-site-master",
			"text" => GetMessage("SALE_MENU_CRM_SITE_MASTER"),
			"title" => GetMessage("SALE_MENU_CRM_SITE_MASTER"),
			"sort" => 475,
			"items_id" => "global_menu_crm_site_master",
			"help_section" => "crm-site-master",
			"items" => [
				[
					"parent_menu" => "global_menu_crm_site_master",
					"text" => GetMessage("SALE_MENU_CRM_SITE_MASTER_ITEM"),
					"title" => GetMessage("SALE_MENU_CRM_SITE_MASTER_ITEM"),
					"url" => "sale_crm_site_master.php?lang=".LANGUAGE_ID,
					"icon" => "sale_crm_site_master_icon",
					"sort" => 100,
				]
			],
		];
	}
});

return (!empty($aMenu) ? $aMenu : false);