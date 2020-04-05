<?
if(IsModuleInstalled("sale"))
	return false;

if (!CModule::IncludeModule("catalog"))
	return false;

IncludeModuleLangFile(__FILE__);

$boolRead = $USER->CanDoOperation('catalog_read');
$boolDiscount = $USER->CanDoOperation('catalog_discount');
$boolStore = $USER->CanDoOperation('catalog_store');
$boolGroup = $USER->CanDoOperation('catalog_group');
$boolPrice = $USER->CanDoOperation('catalog_price');
$boolVat = $USER->CanDoOperation('catalog_vat');
$boolMeasure = $USER->CanDoOperation('catalog_measure');
$boolExportEdit = $USER->CanDoOperation('catalog_export_edit');
$boolExportExec = $USER->CanDoOperation('catalog_export_exec');
$boolImportEdit = $USER->CanDoOperation('catalog_import_edit');
$boolImportExec = $USER->CanDoOperation('catalog_import_exec');

global $adminMenu;

function __get_export_profiles($strItemID)
{
	// this code is copy CCatalogAdmin::OnBuildSaleExportMenu
	global $USER;

	global $adminMenu;

	if (!isset($USER) || !(($USER instanceof CUser) && ('CUser' == get_class($USER))))
		return array();

	if (empty($strItemID))
			return array();

	$boolRead = $USER->CanDoOperation('catalog_read');
	$boolExportEdit = $USER->CanDoOperation('catalog_export_edit');
	$boolExportExec = $USER->CanDoOperation('catalog_export_exec');

	$arProfileList = array();

	if (($boolRead || $boolExportEdit || $boolExportExec) && method_exists($adminMenu, "IsSectionActive"))
	{
		if ($adminMenu->IsSectionActive($strItemID))
		{
			$rsProfiles = CCatalogExport::GetList(array("NAME"=>"ASC", "ID"=>"ASC"), array("IN_MENU"=>"Y"));
			while ($arProfile = $rsProfiles->Fetch())
			{
				$strName = (strlen($arProfile["NAME"]) > 0 ? $arProfile["NAME"] : $arProfile["FILE_NAME"]);
				if ('Y' == $arProfile['DEFAULT_PROFILE'])
				{
					$arProfileList[] = array(
						"text" => htmlspecialcharsbx($strName),
						"url" => "cat_exec_exp.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=EXPORT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
						"title" => GetMessage("CAM_EXPORT_DESCR_EXPORT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
						"readonly" => !$boolExportExec,
					);
				}
				else
				{
					$arProfileList[] = array(
						"text" => htmlspecialcharsbx($strName),
						"url" => "cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=EXPORT_EDIT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
						"title"=>GetMessage("CAM_EXPORT_DESCR_EDIT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
						"readonly" => !$boolExportEdit,
					);
				}
			}
		}
	}
	return $arProfileList;
}

function __get_import_profiles($strItemID)
{
	global $USER;

	global $adminMenu;

	if (!isset($USER) || !(($USER instanceof CUser) && ('CUser' == get_class($USER))))
		return array();

	if (empty($strItemID))
		return array();

	$boolRead = $USER->CanDoOperation('catalog_read');
	$boolImportEdit = $USER->CanDoOperation('catalog_import_edit');
	$boolImportExec = $USER->CanDoOperation('catalog_import_exec');

	$arProfileList = array();

	if (($boolRead || $boolImportEdit || $boolImportExec) && method_exists($adminMenu, "IsSectionActive"))
	{
		if ($adminMenu->IsSectionActive($strItemID))
		{
			$rsProfiles = CCatalogImport::GetList(array("NAME"=>"ASC", "ID"=>"ASC"), array("IN_MENU"=>"Y"));
			while ($arProfile = $rsProfiles->Fetch())
			{
				$strName = (strlen($arProfile["NAME"]) > 0 ? $arProfile["NAME"] : $arProfile["FILE_NAME"]);
				if ('Y' == $arProfile['DEFAULT_PROFILE'])
				{
					$arProfileList[] = array(
						"text" => htmlspecialcharsbx($strName),
						"url" => "cat_exec_imp.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=IMPORT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
						"title" => GetMessage("CAM_IMPORT_DESCR_IMPORT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
						"readonly" => !$boolImportExec,
					);
				}
				else
				{
					$arProfileList[] = array(
						"text" => htmlspecialcharsbx($strName),
						"url" => "cat_import_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".$arProfile["FILE_NAME"]."&ACTION=IMPORT_EDIT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get(),
						"title" => GetMessage("CAM_IMPORT_DESCR_EDIT")." &quot;".htmlspecialcharsbx($strName)."&quot;",
						"readonly" => !$boolImportEdit,
					);
				}
			}
		}
	}

	return $arProfileList;
}

$arSubItems = array();

if ($boolRead || $boolDiscount)
{
	$dscItems = array();
	$dscItems[] = array(
		"text" => GetMessage("CM_DISCOUNTS2"),
		"url" => "cat_discount_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_discount_edit.php"),
		"title" => GetMessage("CM_DISCOUNTS_ALT2"),
		"readonly" => !$boolDiscount,
	);
	$dscItems[] = array(
		"text" => GetMessage("CM_COUPONS"),
		"url" => "cat_discount_coupon.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_discount_coupon_edit.php"),
		"title" => GetMessage("CM_COUPONS_ALT"),
		"readonly" => !$boolDiscount,
	);
	$arSubItems[] = array(
		"text" => GetMessage("CM_DISCOUNTS"),
		"more_url" => array("cat_discount_edit.php", "cat_discount_coupon.php", "cat_discount_coupon_edit.php"),
		"title" => GetMessage("CM_DISCOUNTS_ALT"),
		"dynamic" => false,
		"module_id" => "catalog",
		"items_id" => "mnu_catalog_discount",
		"readonly" => !$boolDiscount,
		"page_icon" => "catalog_page_icon",
		"items" => $dscItems,
	);
	if (CBXFeatures::IsFeatureEnabled('CatDiscountSave'))
	{
		$arSubItems[] = array(
			"text" => GetMessage("CAT_DISCOUNT_SAVE"),
			"url" => "cat_discsave_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_discsave_edit.php"),
			"title" => GetMessage("CAT_DISCOUNT_SAVE_DESCR"),
			"readonly" => !$boolDiscount,
		);
	}
}

if ($boolRead || $boolStore)
{
	if (COption::GetOptionString('catalog','default_use_store_control') == 'Y')
	{
		$arSubItems[] = array(
			"text" => GetMessage("CM_STORE_DOCS"),
			"url" => "cat_store_document_list.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_store_document_edit.php"),
			"title" => GetMessage("CM_STORE_DOCS"),
			"readonly" => !$boolStore,
		);

		$arSubItems[] = array(
			"text" => GetMessage("CM_CONTRACTORS"),
			"url" => "cat_contractor_list.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_contractor_edit.php"),
			"title" => GetMessage("CM_CONTRACTORS"),
			"readonly" => !$boolStore,
		);
	}
	$arSubItems[] = array(
		"text" => GetMessage("CM_STORE"),
		"url" => "cat_store_list.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_store_edit.php"),
		"title" => GetMessage("CM_STORE"),
		"readonly" => !$boolStore,
	);
}

if ($boolRead || $boolMeasure)
{
	$arSubItems[] = array(
		"text" => GetMessage("MEASURE"),
		"url" => "cat_measure_list.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_measure_edit.php"),
		"title" => GetMessage("MEASURE_ALT"),
		"readonly" => !$boolMeasure,
	);
}

$showPrices = $boolRead || $boolGroup;
$showExtra = (CBXFeatures::IsFeatureEnabled('CatMultiPrice') && ($boolRead || $boolPrice));
if ($showPrices || $showExtra)
{
	$section = array(
		'text' => GetMessage('PRICES_SECTION'),
		'title' => GetMessage('PRICES_SECTION_TITLE'),
		'items_id' => 'menu_catalog_prices',
		'items' => array()
	);
	if ($showPrices)
	{
		$section['items'][] = array(
			"text" => GetMessage("GROUP"),
			"title" => GetMessage("GROUP_ALT"),
			"url" => "cat_group_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_group_edit.php"),
			"readonly" => !$boolGroup
		);
		$section['items'][] = array(
			'text' => GetMessage('PRICE_ROUND'),
			'title' => GetMessage('PRICE_ROUND_TITLE'),
			'url' => 'cat_round_list.php?lang='.LANGUAGE_ID,
			'more_url' => array('cat_round_edit.php'),
			'readonly' => !$boolGroup
		);
	}
	if ($showExtra)
	{
		$section['items'][] = array(
			"text" => GetMessage("EXTRA"),
			"title" => GetMessage("EXTRA_ALT"),
			"url" => "cat_extra.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_extra_edit.php"),
			"readonly" => !$boolPrice
		);
	}
	$arSubItems[] = $section;
	unset($section);
}
unset($showExtra, $showPrices);

if ($boolRead || $boolVat)
{
	$arSubItems[] = array(
		"text" => GetMessage("VAT"),
		"url" => "cat_vat_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_vat_edit.php"),
		"title" => GetMessage("VAT_ALT"),
		"readonly" => !$boolVat,
	);
}

if ($boolRead || $boolExportEdit || $boolExportExec)
{
	$arSubItems[] = array(
		"text" => GetMessage("SETUP_UNLOAD_DATA"),
		"url" => "cat_export_setup.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_exec_exp.php"),
		"title" => GetMessage("SETUP_UNLOAD_DATA_ALT"),
		"dynamic" => true,
		"module_id" => "catalog",
		"items_id" => "mnu_catalog_exp",
		"readonly" => !$boolExportEdit && !$boolExportExec,
		"items" => __get_export_profiles("mnu_catalog_exp"),
	);
}

if ($boolRead || $boolImportEdit || $boolImportExec)
{
	$arSubItems[] = array(
		"text" => GetMessage("SETUP_LOAD_DATA"),
		"url" => "cat_import_setup.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_exec_imp.php"),
		"title" => GetMessage("SETUP_LOAD_DATA_ALT"),
		"dynamic" => true,
		"module_id" => "catalog",
		"items_id" => "mnu_catalog_imp",
		"readonly" => !$boolImportEdit && !$boolImportExec,
		"items" => __get_import_profiles("mnu_catalog_imp"),
	);
}

if ($boolRead)
{
	$arSubItems[] = array(
		"text" => GetMessage("SUBSCRIPTION_PRODUCT"),
		"url" => "cat_subscription_list.php?lang=".LANGUAGE_ID,
		"more_url" => array("cat_subscription_list.php"),
		"title" => GetMessage("SUBSCRIPTION_PRODUCT"),
	);
}

if (empty($arSubItems))
	return false;

$aMenu = array(
	"parent_menu" => "global_menu_store",
	"section" => "catalog",
	"sort" => 200,
	"text" => GetMessage("CATALOG_CONTROL"),
	"title" => GetMessage("CATALOG_MNU_TITLE"),
	"icon" => "trade_catalog_menu_icon",
	"page_icon" => "catalog_page_icon",
	"items_id" => "mnu_catalog",
	"items" => $arSubItems,
);
return $aMenu;