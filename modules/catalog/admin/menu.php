<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

if (ModuleManager::isModuleInstalled('sale'))
{
	return false;
}

if (!Loader::includeModule('catalog'))
{
	return false;
}

$accessController = AccessController::getCurrent();

$boolRead = $accessController->check(ActionDictionary::ACTION_CATALOG_READ);
$boolDiscount = $accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET);
$boolStore = $accessController->check(ActionDictionary::ACTION_STORE_VIEW);
$boolGroup = $accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT);
$boolPrice = $accessController->check(ActionDictionary::ACTION_PRICE_EDIT);
$boolVat = $accessController->check(ActionDictionary::ACTION_VAT_EDIT);
$boolMeasure = $accessController->check(ActionDictionary::ACTION_MEASURE_EDIT);
$boolExportEdit = $accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EDIT);
$boolExportExec = $accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EXECUTION);
$boolImportEdit = $accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EDIT);
$boolImportExec = $accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EXECUTION);

global $adminMenu;

if (!function_exists("__get_export_profiles"))
{
	function __get_export_profiles($strItemID): array
	{
		// this code is copy CCatalogAdmin::OnBuildSaleExportMenu
		global $USER;

		global $adminMenu;

		if (!isset($USER) || !($USER instanceof CUser))
		{
			return [];
		}

		if (empty($strItemID))
		{
			return [];
		}

		$accessController = AccessController::getCurrent();
		$boolRead = $accessController->check(ActionDictionary::ACTION_CATALOG_READ);
		$boolExportEdit = $accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EDIT);
		$boolExportExec = $accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EXECUTION);

		$arProfileList = [];

		if (($boolRead || $boolExportEdit || $boolExportExec) && method_exists($adminMenu, "IsSectionActive"))
		{
			if ($adminMenu->IsSectionActive($strItemID))
			{
				$rsProfiles = CCatalogExport::GetList(
					[
						'NAME' => 'ASC',
						'ID' => 'ASC',
					],
					[
						'IN_MENU' => 'Y',
					]
				);
				while ($arProfile = $rsProfiles->Fetch())
				{
					$arProfile['NAME'] = (string)$arProfile['NAME'];
					$strName = ($arProfile['NAME'] ?: $arProfile['FILE_NAME']);
					if ('Y' === $arProfile['DEFAULT_PROFILE'])
					{
						$arProfileList[] = array(
							"text" => htmlspecialcharsbx($strName),
							"url" => "cat_exec_exp.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=EXPORT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
							"title" => Loc::getMessage("CAM_EXPORT_DESCR_EXPORT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
							"readonly" => !$boolExportExec,
						);
					}
					else
					{
						$arProfileList[] = array(
							"text" => htmlspecialcharsbx($strName),
							"url" => "cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=EXPORT_EDIT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
							"title"=>Loc::getMessage("CAM_EXPORT_DESCR_EDIT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
							"readonly" => !$boolExportEdit,
						);
					}
				}
			}
		}
		return $arProfileList;
	}
}

if (!function_exists("__get_import_profiles"))
{
	function __get_import_profiles($strItemID): array
	{
		global $USER;

		global $adminMenu;

		if (!isset($USER) || !($USER instanceof CUser))
		{
			return [];
		}

		if (empty($strItemID))
		{
			return [];
		}

		$accessController = AccessController::getCurrent();
		$boolRead = $accessController->check(ActionDictionary::ACTION_CATALOG_READ);
		$boolImportEdit = $accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EDIT);
		$boolImportExec = $accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EXECUTION);

		$arProfileList = [];

		if (($boolRead || $boolImportEdit || $boolImportExec) && method_exists($adminMenu, 'IsSectionActive'))
		{
			if ($adminMenu->IsSectionActive($strItemID))
			{
				$rsProfiles = CCatalogImport::GetList(
					[
						'NAME' => 'ASC',
						'ID' => 'ASC',
					],
					[
						'IN_MENU' => 'Y',
					]
				);
				while ($arProfile = $rsProfiles->Fetch())
				{
					$arProfile['NAME'] = (string)$arProfile['NAME'];
					$strName = ($arProfile['NAME'] ?: $arProfile['FILE_NAME']);
					if ('Y' === $arProfile['DEFAULT_PROFILE'])
					{
						$arProfileList[] = [
							'text' => htmlspecialcharsbx($strName),
							'url' =>
								'cat_exec_imp.php?lang=' . LANGUAGE_ID
								. '&ACT_FILE=' . $arProfile['FILE_NAME']
								. '&ACTION=IMPORT&PROFILE_ID=' . $arProfile['ID']
								. '&'.bitrix_sessid_get()
							,
							'title' =>
								Loc::getMessage('CAM_IMPORT_DESCR_IMPORT')
								. ' &quot;' . htmlspecialcharsbx($strName) . '&quot;'
							,
							'readonly' => !$boolImportExec,
						];
					}
					else
					{
						$arProfileList[] = [
							'text' => htmlspecialcharsbx($strName),
							'url' =>
								'cat_import_setup.php?lang=' . LANGUAGE_ID
								. '&ACT_FILE=' . $arProfile['FILE_NAME']
								. '&ACTION=IMPORT_EDIT&PROFILE_ID=' . $arProfile['ID']
								. '&'.bitrix_sessid_get()
							,
							'title' =>
								Loc::getMessage('CAM_IMPORT_DESCR_EDIT')
								. ' &quot;' . htmlspecialcharsbx($strName) . '&quot;'
							,
							'readonly' => !$boolImportEdit,
						];
					}
				}
			}
		}

		return $arProfileList;
	}
}

$arSubItems = [];

if ($boolRead || $boolDiscount)
{
	$dscItems = [];
	$dscItems[] = [
		'text' => Loc::getMessage('CM_DISCOUNTS2'),
		'url' => 'cat_discount_admin.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_discount_edit.php',
		],
		'title' => Loc::getMessage('CM_DISCOUNTS_ALT2'),
		'readonly' => !$boolDiscount,
		'items_id' => 'cat_discount_admin',
	];
	$dscItems[] = [
		'text' => Loc::getMessage('CM_COUPONS'),
		'url' => 'cat_discount_coupon.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_discount_coupon_edit.php',
		],
		'title' => Loc::getMessage('CM_COUPONS_ALT'),
		'readonly' => !$boolDiscount,
		'items_id' => 'cat_discount_coupon',
	];
	$arSubItems[] = [
		'text' => Loc::getMessage('CM_DISCOUNTS'),
		'more_url' => [
			'cat_discount_edit.php',
			'cat_discount_coupon.php',
			'cat_discount_coupon_edit.php',
		],
		'title' => Loc::getMessage('CM_DISCOUNTS_ALT'),
		'dynamic' => false,
		'module_id' => 'catalog',
		'items_id' => 'mnu_catalog_discount',
		'readonly' => !$boolDiscount,
		'page_icon' => 'catalog_page_icon',
		'items' => $dscItems,
	];
	if (Catalog\Config\Feature::isCumulativeDiscountsEnabled())
	{
		$arSubItems[] = [
			'text' => Loc::getMessage('CAT_DISCOUNT_SAVE'),
			'url' => 'cat_discsave_admin.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_discsave_edit.php',
			],
			'title' => Loc::getMessage('CAT_DISCOUNT_SAVE_DESCR'),
			'readonly' => !$boolDiscount,
			'items_id' => 'cat_discsave_admin',
		];
	}
}

if ($boolRead || $boolStore)
{
	if ($boolStore && Catalog\Config\State::isUsedInventoryManagement())
	{
		$arSubItems[] = [
			'text' => Loc::getMessage('CM_STORE_DOCS'),
			'url' => 'cat_store_document_list.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_store_document_edit.php',
			],
			'title' => Loc::getMessage('CM_STORE_DOCS'),
			'readonly' => false,
			'items_id' => 'cat_store_document_list',
		];

		$arSubItems[] = [
			'text' => Loc::getMessage('CM_CONTRACTORS'),
			'url' => 'cat_contractor_list.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_contractor_edit.php',
			],
			'title' => Loc::getMessage('CM_CONTRACTORS'),
			'readonly' => false,
			'items_id' => 'cat_contractor_list',
		];
	}
	$arSubItems[] = [
		'text' => Loc::getMessage('CM_STORE'),
		'url' => 'cat_store_list.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_store_edit.php',
		],
		'title' => Loc::getMessage('CM_STORE'),
		'readonly' => !$boolStore,
		'items_id' => 'cat_store_list',
	];
}

if ($boolRead || $boolMeasure)
{
	$arSubItems[] = [
		'text' => Loc::getMessage('MEASURE'),
		'url' => 'cat_measure_list.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_measure_edit.php',
		],
		'title' => Loc::getMessage('MEASURE_ALT'),
		'readonly' => !$boolMeasure,
		'items_id' => 'cat_measure_list',
	];
}

$showPrices = $boolRead || $boolGroup;
$showExtra = (Catalog\Config\Feature::isMultiPriceTypesEnabled() && ($boolRead || $boolPrice));
if ($showPrices || $showExtra)
{
	$section = [
		'text' => Loc::getMessage('PRICES_SECTION'),
		'title' => Loc::getMessage('PRICES_SECTION_TITLE'),
		'url' => 'cat_group_admin.php?lang=' . LANGUAGE_ID,
		'items_id' => 'menu_catalog_prices',
		'items' => [],
	];
	if ($showPrices)
	{
		$section['items'][] = [
			'text' => Loc::getMessage('GROUP'),
			'title' => Loc::getMessage('GROUP_ALT'),
			'url' => 'cat_group_admin.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_group_edit.php',
			],
			'readonly' => !$boolGroup,
			'items_id' => 'cat_group_admin',
		];
		$section['items'][] = [
			'text' => Loc::getMessage('PRICE_ROUND'),
			'title' => Loc::getMessage('PRICE_ROUND_TITLE'),
			'url' => 'cat_round_list.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_round_edit.php',
			],
			'readonly' => !$boolGroup,
			'items_id' => 'cat_round_list',
		];
	}
	if ($showExtra)
	{
		$section['items'][] = [
			'text' => Loc::getMessage('EXTRA'),
			'title' => Loc::getMessage('EXTRA_ALT'),
			'url' => 'cat_extra.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'cat_extra_edit.php',
			],
			'readonly' => !$boolPrice,
			'items_id' => 'cat_extra',
		];
	}
	$arSubItems[] = $section;
	unset($section);
}
unset($showExtra, $showPrices);

if ($boolRead || $boolVat)
{
	$arSubItems[] = [
		'text' => Loc::getMessage('VAT'),
		'url' => 'cat_vat_admin.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_vat_edit.php'],
		'title' => Loc::getMessage('VAT_ALT'),
		'readonly' => !$boolVat,
		'items_id' => 'cat_vat_admin',
	];
}

if ($boolRead || $boolExportEdit || $boolExportExec)
{
	$arSubItems[] = [
		'text' => Loc::getMessage('SETUP_UNLOAD_DATA'),
		'url' => 'cat_export_setup.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_exec_exp.php',
		],
		'title' => Loc::getMessage('SETUP_UNLOAD_DATA_ALT'),
		'dynamic' => true,
		'module_id' => 'catalog',
		'items_id' => 'mnu_catalog_exp',
		'readonly' => !$boolExportEdit && !$boolExportExec,
		'items' => __get_export_profiles('mnu_catalog_exp'),
	];
}

if ($boolRead || $boolImportEdit || $boolImportExec)
{
	$arSubItems[] = [
		'text' => Loc::getMessage('SETUP_LOAD_DATA'),
		'url' => 'cat_import_setup.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_exec_imp.php'
		],
		'title' => Loc::getMessage('SETUP_LOAD_DATA_ALT'),
		'dynamic' => true,
		'module_id' => 'catalog',
		'items_id' => 'mnu_catalog_imp',
		'readonly' => !$boolImportEdit && !$boolImportExec,
		'items' => __get_import_profiles('mnu_catalog_imp'),
	];
}

if ($boolRead)
{
	$arSubItems[] = [
		'text' => Loc::getMessage('SUBSCRIPTION_PRODUCT'),
		'url' => 'cat_subscription_list.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'cat_subscription_list.php',
		],
		'title' => Loc::getMessage('SUBSCRIPTION_PRODUCT'),
		'items_id' => 'cat_subscription_list',
	];
}

if (empty($arSubItems))
{
	return false;
}

return [
	'parent_menu' => 'global_menu_store',
	'section' => 'catalog',
	'sort' => 200,
	'text' => Loc::getMessage('CATALOG_CONTROL'),
	'title' => Loc::getMessage('CATALOG_MNU_TITLE'),
	'icon' => 'trade_catalog_menu_icon',
	'page_icon' => 'catalog_page_icon',
	'items_id' => 'mnu_catalog',
	'items' => $arSubItems,
];
