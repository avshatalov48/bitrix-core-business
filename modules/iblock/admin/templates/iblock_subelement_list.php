<?
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CMain $APPLICATION */
/** @global string $strSubIBlockType */
/** @global int $intSubPropValue */
/** @global int $strSubTMP_ID */
/** @global array $arSubCatalog */
/** @global array $arSubIBlock */
/** @global string $by */
/** @global string $order */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Iblock\Grid\ActionType,
	Bitrix\Currency,
	Bitrix\Catalog;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminSidePanelHelper->isSidePanelFrame();
$selfFolderUrl = $adminPage->getSelfFolderUrl();

/*
* B_ADMIN_SUBELEMENTS
* if defined and equal 1 - working, another die
* B_ADMIN_SUBELEMENTS_LIST - true/false
* if not defined - die
* if equal true - get list mode
* 	include prolog and epilog
* other - get simple html
*
* need variables
* 		$strSubElementAjaxPath - path for ajax
* 		$strSubIBlockType - iblock type
* 		$arSubIBlockType - iblock type array
* 		$intSubIBlockID - iblock ID
* 		$arSubIBlock	- array with info about iblock
*		$boolSubWorkFlow - workflow and iblock in workflow
*		$boolSubBizproc - business process and iblock in business
*		$boolSubCatalog - catalog and iblock in catalog
*		$arSubCatalog - info about catalog (with product_iblock_id and sku_property_id info)
*		$intSubPropValue - ID for filter
*		$strSubTMP_ID - string identifier for link with new product ($intSubPropValue = 0, in edit form send -1)
*
*
*created variables
*		$arSubElements - array subelements for product with ID = 0
*/
if (!defined('B_ADMIN_SUBELEMENTS') || 1 != B_ADMIN_SUBELEMENTS)
	return '';
if (!defined('B_ADMIN_SUBELEMENTS_LIST'))
	return '';

Loc::loadMessages(__FILE__);

$strSubElementAjaxPath = trim($strSubElementAjaxPath);
$strSubIBlockType = trim($strSubIBlockType);
$intSubIBlockID = (int)$intSubIBlockID;
if ($intSubIBlockID <= 0)
	return;
$boolSubWorkFlow = ($boolSubWorkFlow === true);
$boolSubBizproc = ($boolSubBizproc === true);
$boolSubCatalog = ($boolSubCatalog === true);
$boolSubSearch = false;
$boolSubCurrency = false;
$arCurrencyList = array();
$subuniq_id = 0;
$useStoreControl = false;
$strSaveWithoutPrice = '';
$boolCatalogRead = false;
$boolCatalogPrice = false;
$boolCatalogPurchasInfo = false;
$catalogPurchasInfoEdit = false;
$boolCatalogSet = false;
$productTypeList = array();
$priceTypeList = array();
$priceTypeIndex = array();
$basePriceType = [];
$basePriceTypeId = 0;
if ($boolSubCatalog)
{
	$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();
	$strSaveWithoutPrice = Main\Config\Option::get('catalog','save_product_without_price');
	$boolCatalogRead = $USER->CanDoOperation('catalog_read');
	$boolCatalogPrice = $USER->CanDoOperation('catalog_price');
	$boolCatalogPurchasInfo = $USER->CanDoOperation('catalog_purchas_info');
	$boolCatalogSet = Catalog\Config\Feature::isProductSetsEnabled();
	$productTypeList = CCatalogAdminTools::getIblockProductTypeList($intSubIBlockID, true);
	if ($boolCatalogPurchasInfo)
		$catalogPurchasInfoEdit = $boolCatalogPrice && !$useStoreControl;
	$basePriceType = \CCatalogGroup::GetBaseGroup();
	if (!empty($basePriceType))
		$basePriceTypeId = $basePriceType['ID'];
}
$changeUserByActive = Main\Config\Option::get('iblock', 'change_user_by_group_active_modify') === 'Y';

const MODULE_ID = "iblock";
const ENTITY = "CIBlockDocument";
define("DOCUMENT_TYPE", "iblock_".$intSubIBlockID);

$currentUser = array(
	'ID' => $USER->GetID(),
	'GROUPS' => $USER->GetUserGroupArray()
);

/** @var CIBlockDocument $iblockDocument */
$iblockDocument = null;
if ($boolSubBizproc)
{
	$iblockDocument = new CIBlockDocument();
}

if (isset($_REQUEST['mode']) && ($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame'))
	CFile::DisableJSFunction(true);

$intSubPropValue = (int)$intSubPropValue;

$strSubTMP_ID = (int)$strSubTMP_ID;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
$minImageSize = array("W" => 1, "H"=>1);
$maxImageSize = array(
	"W" => $listImageSize,
	"H" => $listImageSize,
);
unset($listImageSize);
$useCalendarTime = Main\Config\Option::get('iblock', 'list_full_date_edit') == 'Y';

$arProps = array();
$iterator = Iblock\PropertyTable::getList([
	'select' => ['*'],
	'filter' => ['=IBLOCK_ID' => $intSubIBlockID, '=ACTIVE' => 'Y'],
	'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
]);
while($arProp = $iterator->fetch())
{
	$arProp["USER_TYPE"] = (string)$arProp["USER_TYPE"];
	$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
	$arProp["USER_TYPE_SETTINGS"] = $arProp["USER_TYPE_SETTINGS_LIST"];
	unset($arProp["USER_TYPE_SETTINGS_LIST"]);
	$arProps[] = $arProp;
}

$sTableID = "tbl_iblock_sub_element_".md5($strSubIBlockType.".".$intSubIBlockID);

$arHideFields = array('PROPERTY_'.$arSubCatalog['SKU_PROPERTY_ID']);
$lAdmin = new CAdminSubList($sTableID,false,$strSubElementAjaxPath,$arHideFields);

$groupParams = array(
	'ENTITY_ID' => $sTableID,
	'IBLOCK_ID' => $intSubIBlockID,
	'GRID_TYPE' => Iblock\Grid\Panel\GroupAction::GRID_TYPE_SUBLIST
);
$panelAction = new Catalog\Grid\Panel\ProductGroupAction($groupParams);
unset($groupParams);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'asc';
$by = mb_strtoupper($by);
if ($by == 'CATALOG_TYPE')
	$by = 'TYPE';

// only sku property filter
$arFilterFields = array(
	"find_el_property_".$arSubCatalog['SKU_PROPERTY_ID'],
);

$find_section_section = -1;

$section_id = intval($find_section_section);
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;
$sThisSectionUrl = '';

// simple filter
$arFilter = array(
	"IBLOCK_ID" => $intSubIBlockID,
);

if (0 < $intSubPropValue)
	$arFilter["=PROPERTY_".$arSubCatalog['SKU_PROPERTY_ID']] = $intSubPropValue;
else
{
	$arFilter["=PROPERTY_".$arSubCatalog['SKU_PROPERTY_ID']] = $intSubPropValue;
}
$arFilter["CHECK_PERMISSIONS"] = "Y";
$arFilter["MIN_PERMISSION"] = "R";

$transferHeaders = [
	'CATALOG_TYPE' => 'TYPE',
	'CATALOG_BUNDLE' => 'BUNDLE',
	'CATALOG_AVAILABLE' => 'AVAILABLE',
	'CATALOG_QUANTITY' => 'QUANTITY',
	'CATALOG_QUANTITY_RESERVED' => 'QUANTITY_RESERVED',
	'CATALOG_MEASURE' => 'MEASURE',
	'CATALOG_QUANTITY_TRACE' => 'QUANTITY_TRACE_RAW',
	'CAN_BUY_ZERO' => 'CAN_BUY_ZERO_RAW',
	'CATALOG_WEIGHT' => 'WEIGHT',
	'CATALOG_WIDTH' => 'WIDTH',
	'CATALOG_LENGTH' => 'LENGTH',
	'CATALOG_HEIGHT' => 'HEIGHT',
	'CATALOG_VAT_INCLUDED' => 'VAT_INCLUDED',
	'CATALOG_PURCHASING_PRICE' => 'PURCHASING_PRICE',
	'CATALOG_PURCHASING_CURRENCY' => 'PURCHASING_CURRENCY'
];
$revertFields = array_flip($transferHeaders);

$arHeader = array();
if ($boolSubCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_TYPE",
		"content" => Loc::getMessage("IBEL_CATALOG_TYPE"),
		"title" => Loc::getMessage('IBEL_CATALOG_TYPE_TITLE'),
		"align" => "right",
		"sort" => "TYPE",
		"default" => true,
	);
}
$arHeader[] = array("id"=>"NAME", "content"=>Loc::getMessage("IBLOCK_FIELD_NAME"), "sort"=>"name", "default"=>true);

$arHeader[] = array("id"=>"ACTIVE", "content"=>Loc::getMessage("IBLOCK_FIELD_ACTIVE"), "sort"=>"active", "default"=>true, "align"=>"center");
$arHeader[] = array("id"=>"SORT", "content"=>Loc::getMessage("IBLOCK_FIELD_SORT"), "sort"=>"sort", "default"=>true, "align"=>"right");
$arHeader[] = array("id"=>"TIMESTAMP_X", "content"=>Loc::getMessage("IBLOCK_FIELD_TIMESTAMP_X"), "sort"=>"timestamp_x");
$arHeader[] = array("id"=>"USER_NAME", "content"=>Loc::getMessage("IBLOCK_FIELD_USER_NAME"), "sort"=>"modified_by");
$arHeader[] = array("id"=>"DATE_CREATE", "content"=>Loc::getMessage("IBLOCK_EL_ADMIN_DCREATE"), "sort"=>"created");
$arHeader[] = array("id"=>"CREATED_USER_NAME", "content"=>Loc::getMessage("IBLOCK_EL_ADMIN_WCREATE2"), "sort"=>"created_by");

$arHeader[] = array("id"=>"CODE", "content"=>Loc::getMessage("IBEL_A_CODE"), "sort"=>"code");
$arHeader[] = array("id"=>"EXTERNAL_ID", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_ID"), "sort"=>"external_id");
$arHeader[] = array("id"=>"TAGS", "content"=>Loc::getMessage("IBEL_A_TAGS"), "sort"=>"tags");

if ($boolSubWorkFlow)
{
	$arHeader[] = array("id"=>"WF_STATUS_ID", "content"=>Loc::getMessage("IBLOCK_FIELD_STATUS"), "sort"=>"status", "default"=>true);
	$arHeader[] = array("id"=>"WF_NEW", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_WFNEW"), "sort"=>"");
	$arHeader[] = array("id"=>"LOCK_STATUS", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_LOCK"), "default"=>true);
	$arHeader[] = array("id"=>"LOCKED_USER_NAME", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_LOCK_BY"));
	$arHeader[] = array("id"=>"WF_DATE_LOCK", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_LOCK_WHEN"));
	$arHeader[] = array("id"=>"WF_COMMENTS", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_COM"));
}

$arHeader[] = array("id"=>"ID", "content"=>'ID', "sort"=>"id", "default"=>true, "align"=>"right");
$arHeader[] = array("id"=>"SHOW_COUNTER", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_SHOWS"), "sort"=>"show_counter", "align"=>"right");
$arHeader[] = array("id"=>"SHOW_COUNTER_START", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_SHOW_F"), "sort"=>"show_counter_start", "align"=>"right");
$arHeader[] = array("id"=>"PREVIEW_PICTURE", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_PREV_PIC"), "sort" => "has_preview_picture");
$arHeader[] = array("id"=>"PREVIEW_TEXT", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_PREV_TEXT"));
$arHeader[] = array("id"=>"DETAIL_PICTURE", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_DET_PIC"), "sort" => "has_detail_picture");
$arHeader[] = array("id"=>"DETAIL_TEXT", "content"=>Loc::getMessage("IBEL_A_EXTERNAL_DET_TEXT"));


foreach ($arProps as &$arFProps)
{
	if ($arSubCatalog['SKU_PROPERTY_ID'] != $arFProps['ID'])
	{
		$arHeader[] = array(
			"id" => "PROPERTY_".$arFProps['ID'],
			"content" => htmlspecialcharsEx($arFProps['NAME']),
			"align" => ($arFProps["PROPERTY_TYPE"] == 'N' ? "right" : "left"),
			"sort" => ($arFProps["MULTIPLE"] != 'Y' ? "PROPERTY_".$arFProps['ID'] : "")
		);
	}
}
if (isset($arFProps))
	unset($arFProps);

$arWFStatus = array();
if ($boolSubWorkFlow)
{
	$rsWF = CWorkflowStatus::GetDropDownList('Y');
	while($arWF = $rsWF->GetNext())
		$arWFStatus[$arWF["~REFERENCE_ID"]] = $arWF["~REFERENCE"];
}

if ($boolSubCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_AVAILABLE",
		"content" => Loc::getMessage("IBEL_CATALOG_AVAILABLE"),
		"title" => Loc::getMessage("IBEL_CATALOG_AVAILABLE_TITLE_EXT"),
		"align" => "center",
		"sort" => "AVAILABLE",
		"default" => true,
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY",
		"content" => Loc::getMessage("IBEL_CATALOG_QUANTITY_EXT"),
		"align" => "right",
		"sort" => "QUANTITY",
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY_RESERVED",
		"content" => Loc::getMessage("IBEL_CATALOG_QUANTITY_RESERVED"),
		"align" => "right"
	);
	$arHeader[] = array(
		"id" => "CATALOG_MEASURE_RATIO",
		"content" => Loc::getMessage("IBEL_CATALOG_MEASURE_RATIO"),
		"title" => Loc::getMessage('IBEL_CATALOG_MEASURE_RATIO_TITLE'),
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_MEASURE",
		"content" => Loc::getMessage("IBEL_CATALOG_MEASURE"),
		"title" => Loc::getMessage('IBEL_CATALOG_MEASURE_TITLE'),
		"align" => "right",
		"sort" => "MEASURE",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY_TRACE",
		"content" => Loc::getMessage("IBEL_CATALOG_QUANTITY_TRACE_EXT"),
		"title" => Loc::getMessage("IBEL_CATALOG_QUANTITY_TRACE"),
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CAN_BUY_ZERO",
		"content" => Loc::getMessage("IBEL_CATALOG_CAN_BUY_ZERO"),
		"title" => Loc::getMessage("IBEL_CATALOG_CAN_BUY_ZERO_TITLE"),
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_WEIGHT",
		"content" => Loc::getMessage("IBEL_CATALOG_WEIGHT"),
		"align" => "right",
		"sort" => "WEIGHT",
	);
	$arHeader[] = array(
		"id" => "CATALOG_WIDTH",
		"content" => Loc::getMessage("IBEL_CATALOG_WIDTH"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_LENGTH",
		"content" => Loc::getMessage("IBEL_CATALOG_LENGTH"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_HEIGHT",
		"content" => Loc::getMessage("IBEL_CATALOG_HEIGHT"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_VAT_INCLUDED",
		"content" => Loc::getMessage("IBEL_CATALOG_VAT_INCLUDED"),
		"align" => "right",
	);
	$arHeader[] = array(
		"id" => "VAT_ID",
		"content" => GetMessage('IBEL_CATALOG_VAT_ID'),
		"title" => "",
		"align" => "right",
	);
	$vatList = array(
		0 => GetMessage('IBEL_CATALOG_EMPTY_VALUE')
	);
	$vatIterator = Catalog\VatTable::getList([
		'select' => ['ID', 'NAME', 'SORT'],
		'filter' => ['=ACTIVE' => 'Y'],
		'order' => ['SORT' => 'ASC', 'ID' => 'ASC']
	]);
	while ($vat = $vatIterator->fetch())
	{
		$vat['ID'] = (int)$vat['ID'];
		$vatList[$vat['ID']] = $vat['NAME'];
	}
	unset($vat, $vatIterator);

	if ($boolCatalogPurchasInfo)
	{
		$arHeader[] = array(
			"id" => "CATALOG_PURCHASING_PRICE",
			"content" => Loc::getMessage("IBEL_CATALOG_PURCHASING_PRICE"),
			"title" => "",
			"align" => "right",
			"sort" => "PURCHASING_PRICE",
			"default" => false,
		);
	}
	if ($useStoreControl)
	{
		$arHeader[] = array(
			"id" => "CATALOG_BAR_CODE",
			"content" => Loc::getMessage("IBEL_CATALOG_BAR_CODE"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
	}

	$priceTypeList = CCatalogGroup::GetListArray();
	if (!empty($priceTypeList))
	{
		foreach ($priceTypeList as $priceType)
		{
			$arHeader[] = array(
				"id" => "CATALOG_GROUP_".$priceType["ID"],
				"content" => htmlspecialcharsEx(!empty($priceType["NAME_LANG"]) ? $priceType["NAME_LANG"] : $priceType["NAME"]),
				"align" => "right",
				"sort" => "SCALED_PRICE_".$priceType["ID"],
				"default" => false,
			);
		}
		unset($priceType);
	}

	$arCatExtra = array();
	$db_extras = CExtra::GetList(array("ID" =>"ASC"));
	while ($extras = $db_extras->Fetch())
		$arCatExtra[$extras['ID']] = $extras;
	unset($extras, $db_extras);
}

if($bCatalog)
{
	$arHeader[] = array(
		"id" => "SUBSCRIPTIONS",
		"content" => Loc::getMessage("IBLOCK_FIELD_SUBSCRIPTIONS"),
		"default" => false,
	);
}

if ($boolSubBizproc)
{
	$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$intSubIBlockID));
	foreach ($arWorkflowTemplates as $arTemplate)
	{
		$arHeader[] = array(
			"id" => "WF_".$arTemplate["ID"],
			"content" => $arTemplate["NAME"],
		);
	}
	$arHeader[] = array(
		"id" => "BIZPROC",
		"content" => Loc::getMessage("IBEL_A_BP_H"),
	);
	$arHeader[] = array(
		"id" => "BP_PUBLISHED",
		"content" => Loc::getMessage("IBLOCK_FIELD_BP_PUBLISHED"),
		"sort" => "status",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeader);

$arSelectedFieldsMap = array_fill_keys($lAdmin->GetVisibleHeaderColumns(), true);

$arSelectedProps = array();
$selectedPropertyIds = array();
$arSelect = array();
foreach ($arProps as $arProperty)
{
	if (isset($arSelectedFieldsMap['PROPERTY_'.$arProperty['ID']]))
	{
		$arSelectedProps[] = $arProperty;
		$selectedPropertyIds[] = $arProperty['ID'];
		if ($arProperty["PROPERTY_TYPE"] == "L")
		{
			$arSelect[$arProperty['ID']] = array();
			$rs = CIBlockProperty::GetPropertyEnum($arProperty['ID'], ['SORT' => 'ASC', 'VALUE' => 'ASC', 'ID' => 'ASC']);
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = $ar["VALUE"];
		}
		unset($arSelectedFieldsMap['PROPERTY_'.$arProperty['ID']]);
	}
}

$arSelectedFieldsMap["ID"] = true;
$arSelectedFieldsMap["CREATED_BY"] = true;
$arSelectedFieldsMap["LANG_DIR"] = true;
$arSelectedFieldsMap["LID"] = true;
$arSelectedFieldsMap["WF_PARENT_ELEMENT_ID"] = true;
$arSelectedFieldsMap["ACTIVE"] = true;

if (isset($arSelectedFieldsMap["LOCKED_USER_NAME"]))
	$arSelectedFieldsMap["WF_LOCKED_BY"] = true;
if (isset($arSelectedFieldsMap["USER_NAME"]))
	$arSelectedFieldsMap["MODIFIED_BY"] = true;
if (isset($arSelectedFieldsMap["PREVIEW_TEXT"]))
	$arSelectedFieldsMap["PREVIEW_TEXT_TYPE"] = true;
if (isset($arSelectedFieldsMap["DETAIL_TEXT"]))
	$arSelectedFieldsMap["DETAIL_TEXT_TYPE"] = true;

$arSelectedFieldsMap["LOCK_STATUS"] = true;
$arSelectedFieldsMap["WF_NEW"] = true;
$arSelectedFieldsMap["WF_STATUS_ID"] = true;
$arSelectedFieldsMap["DETAIL_PAGE_URL"] = true;
$arSelectedFieldsMap["SITE_ID"] = true;
$arSelectedFieldsMap["CODE"] = true;
$arSelectedFieldsMap["EXTERNAL_ID"] = true;

$measureList = array(0 => ' ');
if ($boolSubCatalog)
{
	if (isset($arSelectedFieldsMap['CATALOG_QUANTITY_RESERVED']) || isset($arSelectedFieldsMap['CATALOG_MEASURE']))
		$arSelectedFieldsMap['CATALOG_TYPE'] = true;

	$boolPriceInc = false;
	if ($boolCatalogPurchasInfo)
	{
		if (isset($arSelectedFieldsMap["CATALOG_PURCHASING_PRICE"]))
		{
			$arSelectedFieldsMap["CATALOG_PURCHASING_CURRENCY"] = true;
			$boolPriceInc = true;
		}
	}
	if (!empty($priceTypeList))
	{
		$priceIndex = array_keys($priceTypeList);
		foreach($priceIndex as $index)
		{
			if (isset($arSelectedFieldsMap["CATALOG_GROUP_".$index]))
			{
				$boolPriceInc = true;
			}
			else
			{
				unset($priceTypeList[$index]);
			}
		}
		unset($index, $priceIndex);
		if (!empty($priceTypeList))
			$priceTypeIndex = array_keys($priceTypeList);
	}
	if ($boolPriceInc)
	{
		if (!isset($arSelectedFieldsMap['CATALOG_TYPE']))
			$arSelectedFieldsMap['CATALOG_TYPE'] = true;
		$boolSubCurrency = Loader::includeModule('currency');
		if ($boolSubCurrency)
			$arCurrencyList = array_keys(Currency\CurrencyManager::getCurrencyList());
	}
	unset($boolPriceInc);

	if (isset($arSelectedFieldsMap['CATALOG_TYPE']) && $boolCatalogSet)
		$arSelectedFieldsMap['CATALOG_BUNDLE'] = true;

	if (isset($arSelectedFieldsMap['CATALOG_MEASURE']))
	{
		$measureIterator = CCatalogMeasure::getList(array(), array(), false, false, array('ID', 'MEASURE_TITLE', 'SYMBOL_RUS'));
		while($measure = $measureIterator->Fetch())
			$measureList[$measure['ID']] = ($measure['SYMBOL_RUS'] != '' ? $measure['SYMBOL_RUS'] : $measure['MEASURE_TITLE']);
		unset($measure, $measureIterator);
	}
}

$arSelectedFields = $arSelectedFieldsMap;
if ($bCatalog)
{
	if (!empty($priceTypeIndex))
	{
		foreach($priceTypeIndex as $index)
			unset($arSelectedFields["CATALOG_GROUP_".$index]);
		unset($index);
	}
}
$arSelectedFields = array_keys($arSelectedFields);
if ($bCatalog)
{
	foreach ($arSelectedFields as $index => $field)
	{
		if (isset($transferHeaders[$field]))
			$arSelectedFields[$index] = $transferHeaders[$field];
	}
	unset($index, $field);
}

$nonCatalogFields = $arSelectedFields;
$catalogFieldsView = false;
if ($bCatalog)
{
	foreach ($nonCatalogFields as $index => $field)
	{
		if (!\CProductQueryBuilder::isValidField($field))
			continue;
		unset($nonCatalogFields[$index]);
		$catalogFieldsView = true;
	}
	unset($index, $field);
}

switch ($by)
{
	case 'ID':
		$arOrder = array('ID' => $order);
		break;
	case 'TYPE':
		$arOrder = array('TYPE' => $order, 'BUNDLE' => $order, 'ID' => 'ASC');
		break;
	default:
		$arOrder = array($by => $order, 'ID' => 'ASC');
		break;
}
if ($bCatalog)
{
	$arFilter = \CProductQueryBuilder::modifyFilterFromOrder(
		\CProductQueryBuilder::convertOldFilter($arFilter),
		$arOrder,
		['QUANTITY' => 1]
	);
}

if (defined('B_ADMIN_SUBELEMENTS_LIST') && true === B_ADMIN_SUBELEMENTS_LIST)
{
	if ($lAdmin->EditAction())
	{
		if (!empty($_FILES['FIELDS']) && is_array($_FILES['FIELDS']))
			CFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

		if ($boolSubCatalog)
		{
			Catalog\Product\Sku::enableDeferredCalculation();
		}

		$ib = new CIBlockElement();

		foreach ($_POST['FIELDS'] as $subID => $arFields)
		{
			if (!$lAdmin->IsUpdated($subID))
				continue;
			$subID = (int)$subID;
			if ($subID <= 0)
				continue;

			$arRes = CIBlockElement::GetByID($subID);
			$arRes = $arRes->Fetch();
			if (!$arRes)
				continue;

			$WF_ID = $subID;
			if ($boolSubWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($subID);
				if ($WF_ID != $subID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if ($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $subID;
				}

				if ($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR1")." (ID:".$subID.")", $subID);
					continue;
				}
			}
			elseif ($boolSubBizproc)
			{
				if ($iblockDocument->IsDocumentLocked($subID, ""))
				{
					$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $subID)), $subID);
					continue;
				}
			}

			if (
				$boolSubWorkFlow
			)
			{
				if (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
					continue;
				}
				$STATUS_PERMISSION = 2;
				// change is under workflow find status and its permissions
				if (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_any_wf_status"))
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]);
				if ($STATUS_PERMISSION < 2)
				{
					$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
					continue;
				}

				// status change  - check permissions
				if (isset($arFields["WF_STATUS_ID"]))
				{
					if (CIBlockElement::WF_GetStatusPermission($arFields["WF_STATUS_ID"])<1)
					{
						$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
						continue;
					}
				}
			}
			elseif ($bBizproc)
			{
				$bCanWrite = $iblockDocument->CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$currentUser['ID'],
					$subID,
					array(
						"IBlockId" => $intSubIBlockID,
						'IBlockRightsMode' => $arSubIBlock['RIGHTS_MODE'],
						'UserGroups' => $currentUser['GROUPS'],
					)
				);

				if (!$bCanWrite)
				{
					$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
					continue;
				}
			}
			elseif (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
			{
				$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
				continue;
			}

			if (!is_array($arFields["PROPERTY_VALUES"]))
				$arFields["PROPERTY_VALUES"] = array();
			$bFieldProps = array();
			foreach ($arFields as $k=>$v)
			{
				if (
					$k != "PROPERTY_VALUES"
					&& strncmp($k, "PROPERTY_", 9) == 0
				)
				{
					$prop_id = mb_substr($k, 9);
					$arFields["PROPERTY_VALUES"][$prop_id] = $v;
					unset($arFields[$k]);
					$bFieldProps[$prop_id]=true;
				}
			}
			if (!empty($bFieldProps))
			{
				//We have to read properties from database in order not to delete its values
				if (!$boolSubWorkFlow)
				{
					$dbPropV = CIBlockElement::GetProperty($intSubIBlockID, $subID, "sort", "asc", array("ACTIVE"=>"Y"));
					while ($arPropV = $dbPropV->Fetch())
					{
						if (!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
						{
							if (!array_key_exists($arPropV["ID"], $arFields["PROPERTY_VALUES"]))
								$arFields["PROPERTY_VALUES"][$arPropV["ID"]] = array();

							$arFields["PROPERTY_VALUES"][$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]] = array(
								"VALUE" => $arPropV["VALUE"],
								"DESCRIPTION" => $arPropV["DESCRIPTION"],
							);
						}
					}
				}
			}
			else
			{
				//We will not update property values
				unset($arFields["PROPERTY_VALUES"]);
			}

			//All not displayed required fields from DB
			foreach ($arSubIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if (
					$field["IS_REQUIRED"] === "Y"
					&& !array_key_exists($FIELD_ID, $arFields)
					&& $FIELD_ID !== "DETAIL_PICTURE"
					&& $FIELD_ID !== "PREVIEW_PICTURE"
				)
					$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
			}
			if ($arRes["IN_SECTIONS"] == "Y")
			{
				$arFields["IBLOCK_SECTION"] = array();
				$rsSections = CIBlockElement::GetElementGroups($arRes["ID"], true, array('ID', 'IBLOCK_ELEMENT_ID'));
				while ($arSection = $rsSections->Fetch())
					$arFields["IBLOCK_SECTION"][] = $arSection["ID"];
			}

			$arFields["MODIFIED_BY"] = $currentUser['ID'];
			$ib->LAST_ERROR = '';
			$DB->StartTransaction();

			if (!$ib->Update($subID, $arFields, true, true, true))
			{
				$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_SAVE_ERROR", array("#ID#"=>$subID, "#ERROR_TEXT#"=>$ib->LAST_ERROR)), $subID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}

			if ($boolSubCatalog)
			{
				if (
					$boolCatalogPrice && CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_price")
				)
				{
					$arCatalogProduct = array();
					if (isset($arFields['CATALOG_WEIGHT']) && '' != $arFields['CATALOG_WEIGHT'])
						$arCatalogProduct['WEIGHT'] = $arFields['CATALOG_WEIGHT'];

					if (isset($arFields['CATALOG_WIDTH']) && '' != $arFields['CATALOG_WIDTH'])
						$arCatalogProduct['WIDTH'] = $arFields['CATALOG_WIDTH'];
					if (isset($arFields['CATALOG_LENGTH']) && '' != $arFields['CATALOG_LENGTH'])
						$arCatalogProduct['LENGTH'] = $arFields['CATALOG_LENGTH'];
					if (isset($arFields['CATALOG_HEIGHT']) && '' != $arFields['CATALOG_HEIGHT'])
						$arCatalogProduct['HEIGHT'] = $arFields['CATALOG_HEIGHT'];

					if (isset($arFields['CATALOG_VAT_INCLUDED']) && !empty($arFields['CATALOG_VAT_INCLUDED']))
						$arCatalogProduct['VAT_INCLUDED'] = $arFields['CATALOG_VAT_INCLUDED'];
					if (isset($arFields['VAT_ID']) && is_string($arFields['VAT_ID']))
					{
						$arCatalogProduct['VAT_ID'] = ((int)$arFields['VAT_ID'] > 0 ? $arFields['VAT_ID'] : false);
					}
					if (isset($arFields['CATALOG_QUANTITY_TRACE']) && !empty($arFields['CATALOG_QUANTITY_TRACE']))
						$arCatalogProduct['QUANTITY_TRACE'] = $arFields['CATALOG_QUANTITY_TRACE'];
					if (isset($arFields['CAN_BUY_ZERO']) && !empty($arFields['CAN_BUY_ZERO']))
						$arCatalogProduct['CAN_BUY_ZERO'] = $arFields['CAN_BUY_ZERO'];
					if (isset($arFields['CATALOG_MEASURE']) && is_string($arFields['CATALOG_MEASURE']) && (int)$arFields['CATALOG_MEASURE'] > 0)
						$arCatalogProduct['MEASURE'] = $arFields['CATALOG_MEASURE'];

					if ($catalogPurchasInfoEdit)
					{
						if (
							isset($arFields['CATALOG_PURCHASING_PRICE']) && is_string($arFields['CATALOG_PURCHASING_PRICE'])
							&& isset($arFields['CATALOG_PURCHASING_CURRENCY']) && is_string($arFields['CATALOG_PURCHASING_CURRENCY'])
						)
						{
							if ($arFields['CATALOG_PURCHASING_CURRENCY'] != '') // currency control without empty value
							{
								$arFields['CATALOG_PURCHASING_PRICE'] = trim($arFields['CATALOG_PURCHASING_PRICE']);
								if ($arFields['CATALOG_PURCHASING_PRICE'] === '')
								{
									$arFields['CATALOG_PURCHASING_PRICE'] = null;
									$arFields['PURCHASING_CURRENCY'] = null;
								}
								$arCatalogProduct['PURCHASING_PRICE'] = $arFields['CATALOG_PURCHASING_PRICE'];
								$arCatalogProduct['PURCHASING_CURRENCY'] = $arFields['CATALOG_PURCHASING_CURRENCY'];
							}
						}
					}

					if (!$useStoreControl)
					{
						if (isset($arFields['CATALOG_QUANTITY']) && '' != $arFields['CATALOG_QUANTITY'])
							$arCatalogProduct['QUANTITY'] = $arFields['CATALOG_QUANTITY'];
					}

					$product = Catalog\Model\Product::getList(array(
						'select' => array('ID'),
						'filter' => array('=ID' => $subID)
					))->fetch();
					if (empty($product))
					{
						$arCatalogProduct['ID'] = $subID;
						$result = Catalog\Model\Product::add(array('fields' => $arCatalogProduct));
					}
					else
					{
						if (!empty($arCatalogProduct))
						{
							$result = Catalog\Model\Product::update($subID, array('fields' => $arCatalogProduct));
						}
					}
					unset($product);

					if (isset($arFields['CATALOG_MEASURE_RATIO']))
					{
						$newValue = trim($arFields['CATALOG_MEASURE_RATIO']);
						if ($newValue != '')
						{
							$intRatioID = 0;
							$ratio = Catalog\MeasureRatioTable::getList(array(
								'select' => array('ID', 'PRODUCT_ID'),
								'filter' => array('=PRODUCT_ID' => $subID, '=IS_DEFAULT' => 'Y'),
							))->fetch();
							if (!empty($ratio))
								$intRatioID = (int)$ratio['ID'];
							if ($intRatioID > 0)
								$ratioResult = CCatalogMeasureRatio::update($intRatioID, array('RATIO' => $newValue));
							else
								$ratioResult = CCatalogMeasureRatio::add(array('PRODUCT_ID' => $subID, 'RATIO' => $newValue, 'IS_DEFAULT' => 'Y'));
						}
						unset($newValue);
					}
				}
			}
		}

		unset($ib);

		if ($boolSubCatalog)
		{
			if ($boolCatalogPrice && (isset($_POST["CATALOG_PRICE"]) || isset($_POST["CATALOG_CURRENCY"])))
			{
				$CATALOG_PRICE = $_POST["CATALOG_PRICE"];
				$CATALOG_CURRENCY = $_POST["CATALOG_CURRENCY"];
				$CATALOG_EXTRA = $_POST["CATALOG_EXTRA"];
				$CATALOG_PRICE_ID = $_POST["CATALOG_PRICE_ID"];
				$CATALOG_QUANTITY_FROM = $_POST["CATALOG_QUANTITY_FROM"];
				$CATALOG_QUANTITY_TO = $_POST["CATALOG_QUANTITY_TO"];
				$CATALOG_PRICE_old = $_POST["CATALOG_old_PRICE"];
				$CATALOG_CURRENCY_old = $_POST["CATALOG_old_CURRENCY"];

				$arCatExtraUp = array();
				$db_extras = CExtra::GetList(array("ID" => "ASC"));
				while ($extras = $db_extras->Fetch())
					$arCatExtraUp[$extras["ID"]] = $extras["PERCENTAGE"];

				$arBaseGroup = CCatalogGroup::GetBaseGroup();
				$arCatalogGroupList = CCatalogGroup::GetListArray();
				foreach ($CATALOG_PRICE as $elID => $arPrice)
				{
					if (!(CIBlockElementRights::UserHasRightTo($intSubIBlockID, $elID, "element_edit_price")
						&& CIBlockElementRights::UserHasRightTo($intSubIBlockID, $elID, "element_edit")))
						continue;
					//1 Find base price ID
					//2 If such a column is displayed then
					//	check if it is greater than 0
					//3 otherwise
					//	look up it's value in database and
					//	output an error if not found or found less or equal then zero
					$bError = false;

					if ($strSaveWithoutPrice != 'Y')
					{
						if (isset($arPrice[$arBaseGroup['ID']]))
						{
							if ($arPrice[$arBaseGroup['ID']] < 0)
							{
								$bError = true;
								$lAdmin->AddUpdateError($elID.': '.Loc::getMessage('IB_CAT_NO_BASE_PRICE'), $elID);
							}
						}
						else
						{
							$arBasePrice = CPrice::GetBasePrice(
								$elID,
								$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']],
								$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']],
								false
							);

							if (!is_array($arBasePrice) || $arBasePrice['PRICE'] < 0)
							{
								$bError = true;
								$lAdmin->AddGroupError($elID.': '.Loc::getMessage('IB_CAT_NO_BASE_PRICE'), $elID);
							}
						}
					}
					if ($bError)
						continue;

					$arCurrency = $CATALOG_CURRENCY[$elID];

					if (!empty($arCatalogGroupList))
					{
						foreach ($arCatalogGroupList as $arCatalogGroup)
						{
							if ($arPrice[$arCatalogGroup["ID"]] != $CATALOG_PRICE_old[$elID][$arCatalogGroup["ID"]]
								|| $arCurrency[$arCatalogGroup["ID"]] != $CATALOG_CURRENCY_old[$elID][$arCatalogGroup["ID"]])
							{
								if ('Y' == $arCatalogGroup["BASE"]) // if base price check extra for other prices
								{
									$arFields = array(
										"PRODUCT_ID" => $elID,
										"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
										"PRICE" => $arPrice[$arCatalogGroup["ID"]],
										"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
										"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
										"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]],
									);
									if (is_string($arFields['PRICE']))
										$arFields['PRICE'] = str_replace(',', '.', $arFields['PRICE']);
									if ($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
										CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
									elseif ((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
										CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
									elseif ($arFields["PRICE"] >= 0)
										CPrice::Add($arFields);

									$arPrFilter = array(
										"PRODUCT_ID" => $elID,
									);
									if ($arPrice[$arCatalogGroup["ID"]] >= 0)
									{
										$arPrFilter["!CATALOG_GROUP_ID"] = $arCatalogGroup["ID"];
										$arPrFilter["+QUANTITY_FROM"] = "1";
										$arPrFilter["!EXTRA_ID"] = false;
									}
									$db_res = CPrice::GetListEx(
										array(),
										$arPrFilter,
										false,
										false,
										array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "EXTRA_ID")
									);
									while ($ar_res = $db_res->Fetch())
									{
										$arFields = array(
											"PRICE" => $arPrice[$arCatalogGroup["ID"]]*(1+$arCatExtraUp[$ar_res["EXTRA_ID"]]/100) ,
											"EXTRA_ID" => $ar_res["EXTRA_ID"],
											"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
											"QUANTITY_FROM" => $ar_res["QUANTITY_FROM"],
											"QUANTITY_TO" => $ar_res["QUANTITY_TO"]
										);
										if ($arFields["PRICE"] <= 0)
											CPrice::Delete($ar_res["ID"]);
										else
											CPrice::Update($ar_res["ID"], $arFields);
									}
								}
								elseif (!isset($CATALOG_EXTRA[$elID][$arCatalogGroup["ID"]]))
								{
									$arFields = array(
										"PRODUCT_ID" => $elID,
										"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
										"PRICE" => $arPrice[$arCatalogGroup["ID"]],
										"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
										"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
										"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]]
									);
									if (is_string($arFields['PRICE']))
										$arFields['PRICE'] = str_replace(',', '.', $arFields['PRICE']);
									if ($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
										CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
									elseif ((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
										CPrice::Update((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
									elseif ($arFields["PRICE"] >= 0)
										CPrice::Add($arFields);
								}
							}
						}
						unset($arCatalogGroup);
					}
				}
				unset($arCatalogGroupList);
			}

			if ($intSubPropValue > 0)
			{
				$productIblock = CIBlockElement::GetIBlockByID($intSubPropValue);
				$ipropValues = new Iblock\InheritedProperty\ElementValues($productIblock, $intSubPropValue);
				$ipropValues->clearValues();
				Iblock\PropertyIndex\Manager::updateElementIndex($productIblock, $intSubPropValue);
				unset($productIblock);
			}
		}

		if ($boolSubCatalog)
		{
			Catalog\Product\Sku::disableDeferredCalculation();
			Catalog\Product\Sku::calculate();
		}
	}

	if ($arID = $lAdmin->GroupAction())
	{
		$actionId = $lAdmin->GetAction();
		$actionParams = null;
		if (is_string($actionId))
		{
			$actionParams = $panelAction->getRequest($actionId);
		}

		if ($actionId !== null && $actionParams !== null)
		{
			$elementsList = array(
				'ELEMENTS' => array()
			);

			if ($lAdmin->IsGroupActionToAll())
			{
				$rsData = CIBlockElement::GetList($arOrder, $arFilter, false, false, array('ID'));
				while ($arRes = $rsData->Fetch())
				{
					$arID[] = $arRes['ID'];
				}
				unset($arRes, $rsData);
			}

			if ($boolSubCatalog)
			{
				Catalog\Product\Sku::enableDeferredCalculation();
			}

			$ob = new CIBlockElement();

			foreach ($arID as $subID)
			{
				$subID = (int)$subID;
				if ($subID <= 0)
					continue;

				$arRes = CIBlockElement::GetByID($subID);
				$arRes = $arRes->Fetch();
				if (!$arRes)
					continue;

				$WF_ID = $subID;
				if ($boolSubWorkFlow)
				{
					$WF_ID = CIBlockElement::WF_GetLast($subID);
					if ($WF_ID != $subID)
					{
						$rsData2 = CIBlockElement::GetByID($WF_ID);
						if ($arRes = $rsData2->Fetch())
							$WF_ID = $arRes["ID"];
						else
							$WF_ID = $subID;
					}

					if ($arRes["LOCK_STATUS"] == 'red' && !($_REQUEST['action'] == 'unlock' && CWorkflow::IsAdmin()))
					{
						$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR1")." (ID:".$subID.")", $subID);
						continue;
					}
				}
				elseif ($boolSubBizproc)
				{
					if ($iblockDocument->IsDocumentLocked($subID, "") && !($_REQUEST['action'] == 'unlock' && CBPDocument::IsAdmin()))
					{
						$lAdmin->AddUpdateError(Loc::getMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $subID)), $subID);
						continue;
					}
				}

				$bPermissions = false;
				//delete and modify can:
				if ($boolSubWorkFlow)
				{
					//For delete action we have to check all statuses in element history
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission(
						$arRes["WF_STATUS_ID"],
						$_REQUEST['action'] == ActionType::DELETE ? $subID : false
					);
					if ($STATUS_PERMISSION >= 2)
						$bPermissions = true;
				}
				elseif ($boolSubBizproc)
				{
					$bCanWrite = $iblockDocument->CanUserOperateDocument(
						CBPCanUserOperateOperation::WriteDocument,
						$currentUser['ID'],
						$subID,
						array(
							"IBlockId" => $intSubIBlockID,
							'IBlockRightsMode' => $arSubIBlock['RIGHTS_MODE'],
							'UserGroups' => $currentUser['GROUPS'],
						)
					);

					if ($bCanWrite)
						$bPermissions = true;
				}
				else
				{
					$bPermissions = true;
				}

				if (!$bPermissions)
				{
					$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
					continue;
				}

				switch ($actionId)
				{
					case ActionType::DELETE:
						if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_delete"))
						{
							@set_time_limit(0);
							$DB->StartTransaction();
							$APPLICATION->ResetException();
							if (!CIBlockElement::Delete($subID))
							{
								$DB->Rollback();
								if ($ex = $APPLICATION->GetException())
									$lAdmin->AddGroupError(Loc::getMessage("IBLOCK_DELETE_ERROR")." [".$ex->GetString()."]", $subID);
								else
									$lAdmin->AddGroupError(Loc::getMessage("IBLOCK_DELETE_ERROR"), $subID);
							}
							else
							{
								$DB->Commit();
							}
						}
						else
						{
							$lAdmin->AddGroupError(Loc::getMessage("IBLOCK_DELETE_ERROR")." [".$subID."]", $subID);
						}
						break;
					case ActionType::ACTIVATE:
					case ActionType::DEACTIVATE:
						if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
						{
							$ob->LAST_ERROR = '';
							$arFields = array("ACTIVE" => ($actionId == ActionType::ACTIVATE ? "Y" : "N"));
							if ($changeUserByActive)
							{
								$arFields['MODIFIED_BY'] = $currentUser['ID'];
							}
							if (!$ob->Update($subID, $arFields, true))
								$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $subID);
						}
						else
						{
							$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
						}
						break;
					case ActionType::ELEMENT_WORKFLOW_STATUS:
						if ($boolSubWorkFlow)
						{
							$new_status = (int)$actionParams['WF_STATUS_ID'];
							if (
								$new_status > 0
							)
							{
								if (CIBlockElement::WF_GetStatusPermission($new_status) > 0
									|| CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_any_wf_status")
								)
								{
									if ($arRes["WF_STATUS_ID"] != $new_status)
									{
										$ob->LAST_ERROR = '';
										$res = $ob->Update($subID, array(
											"WF_STATUS_ID" => $new_status,
											"MODIFIED_BY" => $currentUser['ID'],
										), true);
										if (!$res)
											$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_SAVE_ERROR", array("#ID#" => $subID, "#ERROR_TEXT#" => $ob->LAST_ERROR)), $subID);
									}
								}
								else
								{
									$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
								}
							}
						}
						break;
					case ActionType::ELEMENT_LOCK:
						$elementAccess = true;
						if ($boolSubWorkFlow && !CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
						{
							$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
							$elementAccess = false;
						}
						if ($elementAccess)
							CIBlockElement::WF_Lock($subID);
						unset($elementAccess);
						break;
					case ActionType::ELEMENT_UNLOCK:
						$elementAccess = true;
						if ($boolSubWorkFlow && !CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
						{
							$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
							$elementAccess = false;
						}
						if ($elementAccess)
						{
							if ($bBizproc)
								call_user_func(array(ENTITY, "UnlockDocument"), $subID, "");
							else
								CIBlockElement::WF_UnLock($subID);
						}
						unset($elementAccess);
						break;
					case ActionType::CLEAR_COUNTER:
						if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
						{
							$ob->LAST_ERROR = '';
							$arFields = array('SHOW_COUNTER' => false, 'SHOW_COUNTER_START' => false);
							if (!$ob->Update($subID, $arFields, true))
								$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $subID);
						}
						else
						{
							$lAdmin->AddGroupError(Loc::getMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
						}
						break;
				}

				if ($boolSubCatalog)
				{
					switch ($actionId)
					{
						case Catalog\Grid\ProductAction::CHANGE_PRICE:
							if ($boolCatalogPrice)
							{
								if (
									CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit")
									&& CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_price")
								)
								{
									$elementsList['ELEMENTS'][$subID] = $subID;
								}
								else
								{
									$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
								}
							}
							break;
						case Catalog\Grid\ProductAction::SET_FIELD:
							if (
								$boolCatalogPrice
								&& CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit")
								&& CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_price")
							)
							{
								$elementsList['ELEMENTS'][$subID] = $subID;
							}
							else
							{
								$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $subID)), $subID);
							}
							break;
					}
				}
			}

			unset($ob);

			if (
				$boolSubCatalog
				&& !empty($elementsList['ELEMENTS'])
			)
			{
				switch ($actionId)
				{
					case Catalog\Grid\ProductAction::CHANGE_PRICE:
						$changePrice = new Catalog\Helpers\Admin\IblockPriceChanger($actionParams, $intSubIBlockID);
						$resultChanging = $changePrice->updatePrices($elementsList);

						if (!$resultChanging->isSuccess())
						{
							foreach ($resultChanging->getErrors() as $error)
							{
								$lAdmin->AddGroupError($error->getMessage(), $error->getCode());
							}
						}
						unset($resultChanging, $changePrice);
						break;
					case Catalog\Grid\ProductAction::SET_FIELD:
						$result = Catalog\Grid\ProductAction::updateElementList(
							$intSubIBlockID,
							$elementsList['ELEMENTS'],
							$actionParams
						);
						if (!$result->isSuccess())
						{
							foreach ($result->getErrors() as $error)
							{
								$lAdmin->AddGroupError($error->getMessage(), $error->getCode());
							}
							unset($error);
						}
						unset($result);
						break;
				}
			}

			if ($boolSubCatalog)
			{
				Catalog\Product\Sku::disableDeferredCalculation();
				Catalog\Product\Sku::calculate();
			}
		}
	}
}

CJSCore::Init(array('translit'));

if (true == B_ADMIN_SUBELEMENTS_LIST)
	CJSCore::Init(array('date'));

if (!(false == B_ADMIN_SUBELEMENTS_LIST && $bCopy))
{
	if ($lAdmin->isExportMode())
	{
		$arNavParams = false;
	}
	else
	{
		//TODO:: remove this hack after refactoring CAdminResult::GetNavSize
		$navResult = new CAdminSubResult(null, '', '');
		$arNavParams = array("nPageSize" => $navResult->GetNavSize($sTableID, 20, $lAdmin->GetListUrl(true)));
		unset($navResult);
	}

	$rawRows = [];
	$elementIds = [];

	$rsData = CIBlockElement::GetList(
		$arOrder,
		$arFilter,
		false,
		$arNavParams,
		array('ID', 'IBLOCK_ID')
	);
	$rsData = new CAdminSubResult($rsData, $sTableID, $lAdmin->GetListUrl(true));

	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(htmlspecialcharsbx($arSubIBlock["ELEMENTS_NAME"])));

	function GetElementName($ID)
	{
		$ID = intval($ID);
		static $cache = array();
		if (!isset($cache[$ID]))
		{
			$rsElement = CIBlockElement::GetList(array(), array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false, array("ID","IBLOCK_ID","NAME"));
			$cache[$ID] = $rsElement->GetNext();
		}
		return $cache[$ID];
	}

	function GetSectionName($ID)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return '';
		static $cache = array();
		if(!isset($cache[$ID]))
		{
			$rsSection = CIBlockSection::GetList(
				array(),
				array("ID" => $ID),
				false,
				array('ID', 'NAME', 'IBLOCK_ID')
			);
			$cache[$ID] = $rsSection->GetNext();
		}
		return $cache[$ID];
	}

	$arRows = array();
	$listItemId = array();

	$productShowPrices = [];
	$productEditPrices = [];

	$boolSubSearch = Loader::includeModule('search');

	while($row = $rsData->Fetch())
	{
		$rawRows[$row['ID']] = $row;
		$elementIds[] = $row['ID'];
	}
	$selectedCount = $rsData->SelectedRowsCount();
	unset($rsData, $row);
	if (!empty($elementIds))
	{
		sort($elementIds);
		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$elementFilter = [
				'IBLOCK_ID' => $intSubIBlockID,
				'ID' => $pageIds,
				'CHECK_PERMISSIONS' => 'N',
				'SHOW_NEW' => 'Y'
			];
			$iterator = \CIBlockElement::GetList(array(), $elementFilter, false, false, $arSelectedFields);
			while ($row = $iterator->Fetch())
			{
				if ($bCatalog)
				{
					foreach ($revertFields as $index => $field)
					{
						if (!array_key_exists($index, $row))
							continue;
						$row[$field] = $row[$index];
						unset($row[$index]);
					}
					unset($index, $field);
				}
				$rawRows[$row['ID']] = $rawRows[$row['ID']] + $row;
			}
		}
		unset($row);
		unset($iterator);
		unset($elementFilter);
		unset($pageIds);
	}

	$boolOldOffers = false;
	foreach (array_keys($rawRows) as $rowId)
	{
		$arRes = $rawRows[$rowId];
		unset($rawRows[$rowId]);

		$itemId = $arRes['ID'];

		$arRes_orig = $arRes;
		// in workflow mode show latest changes
		if ($boolSubWorkFlow)
		{
			$LAST_ID = CIBlockElement::WF_GetLast($arRes['ID']);
			if ($LAST_ID!=$arRes['ID'])
			{
				$rsData2 = CIBlockElement::GetList(
					array(),
					array(
						"ID"=>$LAST_ID,
						"SHOW_HISTORY"=>"Y"
						),
					false,
					array("nTopCount"=>1),
					$nonCatalogFields
				);
				$arRes = $rsData2->NavNext(false);
				if ($catalogFieldsView)
					$arRes = array_merge($arRes_orig, $arRes);
				$arRes["WF_NEW"] = $arRes_orig["WF_NEW"];
			}
			$lockStatus = $arRes_orig['LOCK_STATUS'];
		}
		elseif ($boolSubBizproc)
		{
			$lockStatus = $iblockDocument->IsDocumentLocked($itemId, "") ? "red" : "green";
		}
		else
		{
			$lockStatus = "";
		}

		if (isset($arRes['CATALOG_TYPE']))
			$arRes['CATALOG_TYPE'] = (int)$arRes['CATALOG_TYPE'];

		if (isset($arSelectedFieldsMap['CATALOG_MEASURE']))
		{
			$arRes['CATALOG_MEASURE'] = (int)$arRes['CATALOG_MEASURE'];
			if ($arRes['CATALOG_MEASURE'] < 0)
				$arRes['CATALOG_MEASURE'] = 0;
		}

		$arRes['lockStatus'] = $lockStatus;
		$arRes["orig"] = $arRes_orig;

		$edit_url = CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$arRes_orig['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		);
		$arRows[$itemId] = $row = $lAdmin->AddRow($itemId, $arRes, $edit_url, Loc::getMessage("IB_SE_L_EDIT_ELEMENT"), true);

		$listItemId[] = $itemId;

		$boolEditPrice = CIBlockElementRights::UserHasRightTo($intSubIBlockID, $itemId, "element_edit_price");

		$row->AddViewField("ID", $itemId);

		if (isset($arRes['LOCKED_USER_NAME']) && $arRes['LOCKED_USER_NAME'] != '')
		{
			$arRes['LOCKED_USER_NAME'] = htmlspecialcharsEx($arRes['LOCKED_USER_NAME']);
			if ($publicMode)
			{
				$contentLockedUserName = '['.$arRes['WF_LOCKED_BY'].']&nbsp;'.$arRes['LOCKED_USER_NAME'];
			}
			else
			{
				$contentLockedUserName = '[<a href="'.$selfFolderUrl.'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes['WF_LOCKED_BY'].'" title="'.Loc::getMessage("IBEL_A_USERINFO").'">'.$arRes['WF_LOCKED_BY'].'</a>]&nbsp;'.$arRes['LOCKED_USER_NAME'];
			}
			$row->AddViewField("LOCKED_USER_NAME", $contentLockedUserName);
			unset($contentLockedUserName);
		}
		if (isset($arRes['USER_NAME']) && $arRes['USER_NAME'] != '')
		{
			$arRes['USER_NAME'] = htmlspecialcharsEx($arRes['USER_NAME']);
			if ($publicMode)
			{
				$contentUserName = '['.$arRes['MODIFIED_BY'].']&nbsp;'.$arRes['USER_NAME'];
			}
			else
			{
				$contentUserName = '[<a href="'.$selfFolderUrl.'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes['MODIFIED_BY'].'" title="'.Loc::getMessage("IBEL_A_USERINFO").'">'.$arRes['MODIFIED_BY'].'</a>]&nbsp;'.$arRes['USER_NAME'];
			}
			$row->AddViewField("USER_NAME", $contentUserName);
			unset($contentUserName);
		}
		if (isset($arRes['CREATED_USER_NAME']) && $arRes['CREATED_USER_NAME'] != '')
		{
			$arRes['CREATED_USER_NAME'] = htmlspecialcharsEx($arRes['CREATED_USER_NAME']);
			if ($publicMode)
			{
				$contentCreatedUserName = '['.$arRes['CREATED_BY'].']&nbsp;'.$arRes['CREATED_USER_NAME'];
			}
			else
			{
				$contentCreatedUserName = '[<a href="'.$selfFolderUrl.'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes['CREATED_BY'].'" title="'.Loc::getMessage("IBEL_A_USERINFO").'">'.$arRes['CREATED_BY'].'</a>]&nbsp;'.$arRes['CREATED_USER_NAME'];
			}
			$row->AddViewField("CREATED_USER_NAME", $contentCreatedUserName);
			unset($contentCreatedUserName);
		}

		if ($boolSubWorkFlow || $boolSubBizproc)
		{
			$lamp = '<span class="adm-lamp adm-lamp-in-list adm-lamp-'.$lockStatus.'"></span>';
			if ($lockStatus=='red' && $arRes_orig['LOCKED_USER_NAME']!='')
				$row->AddViewField("LOCK_STATUS", $lamp.htmlspecialcharsEx($arRes_orig['LOCKED_USER_NAME']));
			else
				$row->AddViewField("LOCK_STATUS", $lamp);
		}

		if ($boolSubBizproc)
			$row->AddCheckField("BP_PUBLISHED", false);

		$row->arRes['props'] = array();
		$arProperties = array();
		if (!empty($arSelectedProps))
		{
			$rsProperties = CIBlockElement::GetProperty($intSubIBlockID, $arRes['ID'], 'id', 'asc', array('ID' => $selectedPropertyIds));
			while($ar = $rsProperties->GetNext())
			{
				if (!isset($arProperties[$ar["ID"]]))
					$arProperties[$ar["ID"]] = array();
				$ar["PROPERTY_VALUE_ID"] = (int)$ar["PROPERTY_VALUE_ID"];
				$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar;
			}
			unset($ar);
			unset($rsProperties);
		}

		foreach ($arSelectedProps as $aProp)
		{
			$arViewHTML = array();
			$arEditHTML = array();
			$arUserType = $aProp['PROPERTY_USER_TYPE'];
			$max_file_size_show=100000;

			$last_property_id = false;
			foreach ($arProperties[$aProp["ID"]] as $valueId => $prop)
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.'][VALUE]';
				$DESCR_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.'][DESCRIPTION]';
				//View part
				if (isset($arUserType["GetAdminListViewHTML"]))
				{
					$arViewHTML[] = call_user_func_array($arUserType["GetAdminListViewHTML"],
						array(
							$prop,
							array(
								"VALUE" => $prop["~VALUE"],
								"DESCRIPTION" => $prop["~DESCRIPTION"]
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
								"GRID" => ($publicMode ? "PUBLIC" : "ADMIN")
							),
						));
				}
				elseif ($prop['PROPERTY_TYPE']=='N')
					$arViewHTML[] = $prop["VALUE"];
				elseif ($prop['PROPERTY_TYPE']=='S')
					$arViewHTML[] = $prop["VALUE"];
				elseif ($prop['PROPERTY_TYPE']=='L')
					$arViewHTML[] = $prop["VALUE_ENUM"];
				elseif ($prop['PROPERTY_TYPE']=='F')
				{
					$arViewHTML[] = CFileInput::Show('NO_FIELDS['.$valueId.']', $prop["VALUE"], array(
						"IMAGE" => "Y",
						"PATH" => "Y",
						"FILE_SIZE" => "Y",
						"DIMENSIONS" => "Y",
						"IMAGE_POPUP" => "Y",
						"MAX_SIZE" => $maxImageSize,
						"MIN_SIZE" => $minImageSize,
						), array(
							'upload' => false,
							'medialib' => false,
							'file_dialog' => false,
							'cloud' => false,
							'del' => false,
							'description' => false,
						)
					);
				}
				elseif ($prop['PROPERTY_TYPE']=='G')
				{
					if ((int)$prop["VALUE"]>0)
					{
						$arSection = GetSectionName($prop["VALUE"]);
						if (!empty($arSection))
						{
							$arViewHTML[] = $arSection['NAME'].
							' [<a href="'.
							htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminSectionEditLink($arSection['IBLOCK_ID'], $arSection['ID'], array("replace_script_name" => true))).
							'" title="'.Loc::getMessage("IBEL_A_SEC_EDIT").'">'.$arSection['ID'].'</a>]';
						}
					}
				}
				elseif ($prop['PROPERTY_TYPE']=='E')
				{
					if ($t = GetElementName($prop["VALUE"]))
					{
						$arViewHTML[] = $t['NAME'].
						' [<a href="'.htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'], array(
						'WF' => 'Y', 'replace_script_name' => true
						))).'" title="'.Loc::getMessage("IBEL_A_EL_EDIT").'">'.$t['ID'].'</a>]';
					}
				}
				//Edit Part
				$bUserMultiple = $prop["MULTIPLE"] == "Y" && isset($arUserType["GetPropertyFieldHtmlMulty"]);
				if ($bUserMultiple)
				{
					if ($last_property_id != $prop["ID"])
					{
						$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']';
						$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtmlMulty"], array(
							$prop,
							$arProperties[$prop["ID"]],
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $VALUE_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							)
						));
					}
				}
				elseif (isset($arUserType["GetPropertyFieldHtml"]))
				{
					$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$prop,
							array(
								"VALUE" => $prop["~VALUE"],
								"DESCRIPTION" => $prop["~DESCRIPTION"],
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							),
						));
				}
				elseif ($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
				{
					if ($prop["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'">'.$prop["VALUE"].'</textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="'.$prop["COL_COUNT"].'">';
					if ($prop["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").
							'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='L' && ($last_property_id!=$prop["ID"]))
				{
					$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].'][]';
					$arValues = array();
					foreach ($arProperties[$prop["ID"]] as $g_prop)
					{
						$g_prop = (int)$g_prop["VALUE"];
						if ($g_prop > 0)
							$arValues[$g_prop] = $g_prop;
					}
					if ($prop['LIST_TYPE']=='C')
					{
						if ($prop['MULTIPLE'] == "Y" || count($arSelect[$prop['ID']]) == 1)
						{
							$html = '<input type="hidden" name="'.$VALUE_NAME.'" value="">';
							foreach ($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="checkbox" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value="'.$value.'"';
								if (isset($arValues[$value]))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.$display.'</label><br>';
								$subuniq_id++;
							}
						}
						else
						{
							$html = '<input type="radio" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value=""';
							if (empty($arValues))
								$html .= ' checked';
							$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</label><br>';
							$subuniq_id++;
							foreach ($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="radio" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value="'.$value.'"';
								if (isset($arValues[$value]))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.$display.'</label><br>';
								$subuniq_id++;
							}
						}
					}
					else
					{
						$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
						$html .= '<option value=""'.(empty($arValues)? ' selected': '').'>'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
						foreach ($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<option value="'.$value.'"';
							if (isset($arValues[$value]))
								$html .= ' selected';
							$html .= '>'.$display.'</option>'."\n";
						}
						$html .= "</select>\n";
					}
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='F' && ($last_property_id!=$prop["ID"]))
				{
					if($prop['MULTIPLE'] == "Y")
					{
						$arOneFileControl = array();
						foreach($arProperties[$prop["ID"]] as $g_prop)
						{
							$arOneFileControl[] = CFileInput::Show(
								'NO_FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$g_prop['PROPERTY_VALUE_ID'].']',
								$g_prop["VALUE"],
								array(
									"IMAGE" => "Y",
									"PATH" => "Y",
									"FILE_SIZE" => "Y",
									"DIMENSIONS" => "Y",
									"IMAGE_POPUP" => "Y",
									"MAX_SIZE" => $maxImageSize,
									"MIN_SIZE" => $minImageSize,
								),
								array(
									'upload' => false,
									'medialib' => false,
									'file_dialog' => false,
									'cloud' => false,
									'del' => false,
									'description' => false,
								)
							);
						}
						if (!empty($arOneFileControl))
						{
							$arEditHTML[] = implode('<br>', $arOneFileControl);
						}
					}
					else
					{
						$arEditHTML[] = CFileInput::Show(
							$VALUE_NAME,
							$prop["VALUE"],
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => $maxImageSize,
								"MIN_SIZE" => $minImageSize,
								),
							array(
								'upload' => false,
								'medialib' => false,
								'file_dialog' => false,
								'cloud' => false,
								'del' => false,
								'description' => false,
							)
						);
					}
				}
				elseif (($prop['PROPERTY_TYPE']=='G'))
				{
					$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.']';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					if ($t = GetSectionName($prop["VALUE"]))
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="5">'.
							'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_section_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
							'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$t['NAME'].'</span>';
					}
					else
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
							'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_section_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
							'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					}
					unset($windowTableId);
					unset($fixIBlock);
				}
				elseif ($prop['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.']';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					if ($t = GetElementName($prop["VALUE"]))
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$t['NAME'].'</span>';
					}
					else
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					}
					unset($windowTableId);
					unset($fixIBlock);
				}
				$last_property_id = $prop['ID'];
			}
			$table_id = md5($itemId.':'.$aProp['ID']);
			if ($aProp["MULTIPLE"] == "Y")
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0][VALUE]';
				$DESCR_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0][DESCRIPTION]';
				if (isset($arUserType["GetPropertyFieldHtmlMulty"]))
				{
				}
				elseif (('F' != $aProp['PROPERTY_TYPE']) && isset($arUserType["GetPropertyFieldHtml"]))
				{
					$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$aProp,
							array(
								"VALUE" => "",
								"DESCRIPTION" => "",
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							),
						));
				}
				elseif ($aProp['PROPERTY_TYPE']=='N' || $aProp['PROPERTY_TYPE']=='S')
				{
					if ($aProp["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$aProp["COL_COUNT"].'" rows="'.$aProp["ROW_COUNT"].'"></textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="" size="'.$aProp["COL_COUNT"].'">';
					if ($aProp["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif ($aProp['PROPERTY_TYPE']=='F')
				{
				}
				elseif($aProp['PROPERTY_TYPE']=='G')
				{
					$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0]';
					$fixIBlock = $aProp["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$aProp['ID'].'-'.$aProp['LINK_IBLOCK_ID'];
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_section_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$aProp["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					unset($windowTableId);
					unset($fixIBlock);
				}
				elseif ($aProp['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0]';
					$fixIBlock = $aProp["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$aProp['ID'].'-'.$aProp['LINK_IBLOCK_ID'];
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$selfFolderUrl.'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$aProp["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					unset($windowTableId);
					unset($fixIBlock);
				}

				if ($aProp["PROPERTY_TYPE"]!=="F" && $aProp["PROPERTY_TYPE"]!=="L" && !$bUserMultiple)
					$arEditHTML[] = '<input type="button" value="'.Loc::getMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="BX.IBlock.Tools.addNewRow(\'tb'.$table_id.'\')">';
			}
			if (!empty($arViewHTML))
				$row->AddViewField("PROPERTY_".$aProp['ID'], implode(" / ", $arViewHTML)."&nbsp;");
			if (!empty($arEditHTML))
				$row->arRes['props']["PROPERTY_".$aProp['ID']] = array("table_id"=>$table_id, "html"=>$arEditHTML);
		}

		if ($boolSubCatalog)
		{
			if (!empty($priceTypeList))
			{
				$productShowPrices[$itemId] = true;

				if (
					$boolCatalogPrice
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $itemId, "element_edit_price")
				)
					$productEditPrices[$itemId] = true;

				foreach ($priceTypeIndex as $index)
				{
					$row->AddViewField('CATALOG_GROUP_'.$index, ' ');
					$row->arRes['CATALOG_GROUP_'.$index] = ' ';
				}
				unset($index);
			}
			if (isset($arSelectedFieldsMap['CATALOG_MEASURE_RATIO']))
			{
				$row->arRes['CATALOG_MEASURE_RATIO'] = ' ';
			}
		}

		if ($boolSubBizproc)
		{
			$arDocumentStates = CBPDocument::GetDocumentStates(
				array("iblock", "CIBlockDocument", "iblock_".$intSubIBlockID),
				array("iblock", "CIBlockDocument", $itemId)
			);

			$arRes["CURRENT_USER_GROUPS"] = $currentUser['GROUPS'];
			if ($arRes["CREATED_BY"] == $currentUser['ID'])
				$arRes["CURRENT_USER_GROUPS"][] = "Author";
			$row->arRes["CURRENT_USER_GROUPS"] = $arRes["CURRENT_USER_GROUPS"];

			$arStr = array();
			$arStr1 = array();
			foreach ($arDocumentStates as $kk => $vv)
			{
				$canViewWorkflow = $iblockDocument->CanUserOperateDocument(
					CBPCanUserOperateOperation::ViewWorkflow,
					$currentUser['ID'],
					$itemId,
					array("AllUserGroups" => $arRes["CURRENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates, "WorkflowId" => $kk)
				);
				if (!$canViewWorkflow)
					continue;

				$arStr1[$vv["TEMPLATE_ID"]] = $vv["TEMPLATE_NAME"];
				$arStr[$vv["TEMPLATE_ID"]] .= "<a href=\"".$selfFolderUrl."bizproc_log.php?ID=".$kk."\">".($vv["STATE_TITLE"] <> '' ? $vv["STATE_TITLE"] : $vv["STATE_NAME"])."</a><br />";

				if ($vv["ID"] <> '')
				{
					$arTasks = CBPDocument::GetUserTasksForWorkflow($currentUser['ID'], $vv["ID"]);
					foreach ($arTasks as $arTask)
					{
						$arStr[$vv["TEMPLATE_ID"]] .= Loc::getMessage("IBEL_A_BP_TASK").":<br /><a href=\"".$selfFolderUrl."bizproc_task.php?id=".$arTask["ID"]."\" title=\"".$arTask["DESCRIPTION"]."\">".$arTask["NAME"]."</a><br /><br />";
					}
				}
			}

			$str = "";
			foreach ($arStr as $k => $v)
			{
				$row->AddViewField("WF_".$k, $v);
				$str .= "<b>".($arStr1[$k] <> '' ? $arStr1[$k] : Loc::getMessage("IBEL_A_BP_PROC"))."</b>:<br />".$v."<br />";
			}

			$row->AddViewField("BIZPROC", $str);
		}
	}
	unset($row);

	$boolIBlockElementAdd = CIBlockSectionRights::UserHasRightTo($intSubIBlockID, $find_section_section, "section_element_bind");

$defaultQuantityTrace = (Main\Config\Option::get("catalog", "default_quantity_trace") == 'Y'
	? Loc::getMessage("IBEL_YES_VALUE")
	: Loc::getMessage("IBEL_NO_VALUE")
);
$quantityTraceStatus = array(
	Catalog\ProductTable::STATUS_DEFAULT => Loc::getMessage("IBEL_DEFAULT_VALUE")." (".$defaultQuantityTrace.")",
	Catalog\ProductTable::STATUS_YES => Loc::getMessage("IBEL_YES_VALUE"),
	Catalog\ProductTable::STATUS_NO => Loc::getMessage("IBEL_NO_VALUE"),
);
$defaultCanBuyZero = (Main\Config\Option::get('catalog', 'default_can_buy_zero') == 'Y'
	? Loc::getMessage("IBEL_YES_VALUE")
	: Loc::getMessage("IBEL_NO_VALUE")
);
$canBuyZeroStatus = array(
	Catalog\ProductTable::STATUS_DEFAULT => Loc::getMessage("IBEL_DEFAULT_VALUE")." (".$defaultCanBuyZero.")",
	Catalog\ProductTable::STATUS_YES => Loc::getMessage("IBEL_YES_VALUE"),
	Catalog\ProductTable::STATUS_NO => Loc::getMessage("IBEL_NO_VALUE")
);

if (!empty($arRows))
{
	$arRowKeys = array_keys($arRows);
	if ($useStoreControl && in_array("CATALOG_BAR_CODE", $arSelectedFields))
	{
		$productsWithBarCode = array();
		$rsProducts = Catalog\ProductTable::getList(array(
			'select' => array('ID', 'BARCODE_MULTI'),
			'filter' => array('@ID' => $arRowKeys)
		));
		while ($product = $rsProducts->fetch())
		{
			if (isset($arRows[$product["ID"]]))
			{
				if ($product["BARCODE_MULTI"] == "Y")
					$arRows[$product["ID"]]->arRes["CATALOG_BAR_CODE"] = Loc::getMessage("IBEL_CATALOG_BAR_CODE_MULTI");
				else
					$productsWithBarCode[] = $product["ID"];
			}
		}
		if (!empty($productsWithBarCode))
		{
			$rsProducts = CCatalogStoreBarCode::getList(array(), array(
				"PRODUCT_ID" => $productsWithBarCode,
			));
			while ($product = $rsProducts->Fetch())
			{
				if (isset($arRows[$product["PRODUCT_ID"]]))
				{
					$arRows[$product["PRODUCT_ID"]]->arRes["CATALOG_BAR_CODE"] = htmlspecialcharsEx($product["BARCODE"]);
				}
			}
		}
	}

	if (isset($arSelectedFieldsMap['CATALOG_MEASURE_RATIO']))
	{
		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('ID', 'PRODUCT_ID', 'RATIO'),
			'filter' => array('@PRODUCT_ID' => $arRowKeys, '=IS_DEFAULT' => 'Y')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PRODUCT_ID'];
			if (isset($arRows[$id]))
				$arRows[$id]->arRes['CATALOG_MEASURE_RATIO'] = $row['RATIO'];
			unset($id);
		}
		unset($row, $iterator);
	}
}

	$arElementOps = CIBlockElementRights::UserHasRightTo(
		$intSubIBlockID,
		array_keys($arRows),
		"",
		CIBlockRights::RETURN_OPERATIONS
	);
	/** @var CAdminListRow $row */
	foreach ($arRows as $itemId => $row)
	{
		$edit_url = CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		);

		if (isset($arSelectedFieldsMap["PREVIEW_PICTURE"]))
		{
			$row->AddFileField("PREVIEW_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)
			);
		}
		if (isset($arSelectedFieldsMap["DETAIL_PICTURE"]))
		{
			$row->AddFileField("DETAIL_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)
			);
		}
		if (isset($arSelectedFieldsMap["PREVIEW_TEXT"]))
			$row->AddViewField("PREVIEW_TEXT", ($row->arRes["PREVIEW_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["PREVIEW_TEXT"]) : HTMLToTxt($row->arRes["PREVIEW_TEXT"])));
		if (isset($arSelectedFieldsMap["DETAIL_TEXT"]))
			$row->AddViewField("DETAIL_TEXT", ($row->arRes["DETAIL_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["DETAIL_TEXT"]) : HTMLToTxt($row->arRes["DETAIL_TEXT"])));

		if (isset($arElementOps[$itemId]) && isset($arElementOps[$itemId]["element_edit"]))
		{
			if (isset($arElementOps[$itemId]) && isset($arElementOps[$itemId]["element_edit_price"]))
			{
				if (isset($row->arRes['price']) && is_array($row->arRes['price']))
					foreach ($row->arRes['price'] as $price_id => $sHTML)
						$row->AddEditField($price_id, $sHTML);
			}

			$row->AddCheckField("ACTIVE");
			$row->AddInputField("NAME", array('size'=>'35'));
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_elements"></div>'.htmlspecialcharsEx($row->arRes["NAME"]));
			$row->AddInputField("SORT", array('size'=>'3'));
			$row->AddInputField("CODE");
			$row->AddInputField("EXTERNAL_ID");
			if ($boolSubSearch)
			{
				$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
				$row->AddEditField("TAGS", InputTags("FIELDS[".$itemId."][TAGS]", $row->arRes["TAGS"], $arSubIBlock["SITE_ID"]));
			}
			else
			{
				$row->AddInputField("TAGS");
			}
			if ($arWFStatus)
			{
				$row->AddSelectField("WF_STATUS_ID", $arWFStatus);
				if ($row->arRes['orig']['WF_NEW']=='Y' || $row->arRes['WF_STATUS_ID']=='1')
					$row->AddViewField("WF_STATUS_ID", htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]));
				else
					$row->AddViewField("WF_STATUS_ID", '<a href="'.$edit_url.'" title="'.Loc::getMessage("IBEL_A_ED_TITLE").'">'.htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]).'</a> / <a href="'.'iblock_element_edit.php?ID='.$row->arRes['orig']['ID'].(!isset($arElementOps[$itemId]) || !isset($arElementOps[$itemId]["element_edit_any_wf_status"])?'&view=Y':'').$sThisSectionUrl.'" title="'.Loc::getMessage("IBEL_A_ED2_TITLE").'">'.htmlspecialcharsEx($arWFStatus[$row->arRes['orig']['WF_STATUS_ID']]).'</a>');
			}
			if (isset($arSelectedFieldsMap["PREVIEW_TEXT"]))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$itemId.'][PREVIEW_TEXT_TYPE]" value="text" id="'.$itemId.'PREVIEWtext"';
				if ($row->arRes["PREVIEW_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$itemId.'PREVIEWtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$itemId.'][PREVIEW_TEXT_TYPE]" value="html" id="'.$itemId.'PREVIEWhtml"';
				if ($row->arRes["PREVIEW_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$itemId.'PREVIEWhtml">html</label><br>';
				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$itemId.'][PREVIEW_TEXT]">'.htmlspecialcharsbx($row->arRes["PREVIEW_TEXT"]).'</textarea>';
				$row->AddEditField("PREVIEW_TEXT", $sHTML);
			}
			if (isset($arSelectedFieldsMap["DETAIL_TEXT"]))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$itemId.'][DETAIL_TEXT_TYPE]" value="text" id="'.$itemId.'DETAILtext"';
				if ($row->arRes["DETAIL_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$itemId.'DETAILtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$itemId.'][DETAIL_TEXT_TYPE]" value="html" id="'.$itemId.'DETAILhtml"';
				if ($row->arRes["DETAIL_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$itemId.'DETAILhtml">html</label><br>';

				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$itemId.'][DETAIL_TEXT]">'.htmlspecialcharsbx($row->arRes["DETAIL_TEXT"]).'</textarea>';
				$row->AddEditField("DETAIL_TEXT", $sHTML);
			}
			foreach ($row->arRes['props'] as $prop_id => $arEditHTML)
				$row->AddEditField($prop_id, '<table id="tb'.$arEditHTML['table_id'].'" border=0 cellpadding=0 cellspacing=0><tr><td nowrap>'.implode("</td></tr><tr><td nowrap>", $arEditHTML['html']).'</td></tr></table>');

			if (isset($arElementOps[$itemId]["element_edit_price"]) && $boolCatalogPrice)
			{
				if ($useStoreControl)
				{
					$row->AddInputField("CATALOG_QUANTITY", false);
				}
				else
				{
					$row->AddInputField("CATALOG_QUANTITY");
				}
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $quantityTraceStatus);
				$row->AddSelectField("CAN_BUY_ZERO", $canBuyZeroStatus);
				$row->AddInputField("CATALOG_WEIGHT");
				$row->AddInputField('CATALOG_WIDTH');
				$row->AddInputField('CATALOG_HEIGHT');
				$row->AddInputField('CATALOG_LENGTH');
				$row->AddCheckField("CATALOG_VAT_INCLUDED");
				$row->AddSelectField('VAT_ID', $vatList);
				if ($boolCatalogPurchasInfo)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes['CATALOG_PURCHASING_PRICE'] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
					if ($catalogPurchasInfoEdit && $boolSubCurrency)
					{
						$editFieldCode = '<input type="hidden" name="FIELDS_OLD['.$itemId.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<input type="hidden" name="FIELDS_OLD['.$itemId.'][CATALOG_PURCHASING_CURRENCY]" value="'.$row->arRes['CATALOG_PURCHASING_CURRENCY'].'">';
						$editFieldCode .= '<input type="text" size="5" name="FIELDS['.$itemId.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<select name="FIELDS['.$itemId.'][CATALOG_PURCHASING_CURRENCY]">';
						foreach ($arCurrencyList as $currencyCode)
						{
							$editFieldCode .= '<option value="'.$currencyCode.'"';
							if ($currencyCode == $row->arRes['CATALOG_PURCHASING_CURRENCY'])
								$editFieldCode .= ' selected';
							$editFieldCode .= '>'.$currencyCode.'</option>';
						}
						unset($currencyCode);
						$editFieldCode .= '</select>';
						$row->AddEditField('CATALOG_PURCHASING_PRICE', $editFieldCode);
						unset($editFieldCode);
					}
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO");
			}
			elseif ($boolCatalogRead)
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField("CATALOG_QUANTITY", false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $quantityTraceStatus, false);
				$row->AddSelectField("CAN_BUY_ZERO", $canBuyZeroStatus, false);
				$row->AddInputField("CATALOG_WEIGHT", false);
				$row->AddInputField('CATALOG_WIDTH', false);
				$row->AddInputField('CATALOG_HEIGHT', false);
				$row->AddInputField('CATALOG_LENGTH', false);
				$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
				$row->AddSelectField('VAT_ID', $vatList, false);
				if ($boolCatalogPurchasInfo)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO", false);
			}
		}
		else
		{
			$row->AddCheckField("ACTIVE", false);
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_elements"></div>'.htmlspecialcharsEx($row->arRes["NAME"]));
			$row->AddInputField("SORT", false);
			$row->AddInputField("CODE", false);
			$row->AddInputField("EXTERNAL_ID", false);
			$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
			if ($arWFStatus)
			{
				$row->AddViewField("WF_STATUS_ID", htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]));
			}
			if ($boolSubCatalog)
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField("CATALOG_QUANTITY", false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $quantityTraceStatus, false);
				$row->AddSelectField("CAN_BUY_ZERO", $canBuyZeroStatus, false);
				$row->AddInputField("CATALOG_WEIGHT", false);
				$row->AddInputField('CATALOG_WIDTH', false);
				$row->AddInputField('CATALOG_HEIGHT', false);
				$row->AddInputField('CATALOG_LENGTH', false);
				$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
				$row->AddSelectField('VAT_ID', $vatList, false);
				if ($boolCatalogPurchasInfo)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO", false);
			}
		}
		if (isset($arSelectedFieldsMap['CATALOG_TYPE']))
		{
			$strProductType = '';
			if ($intSubPropValue <= 0 && $row->arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER)
				$row->arRes['CATALOG_TYPE'] = Catalog\ProductTable::TYPE_OFFER;
			if (isset($productTypeList[$row->arRes["CATALOG_TYPE"]]))
				$strProductType = $productTypeList[$row->arRes["CATALOG_TYPE"]];
			if ($row->arRes['CATALOG_BUNDLE'] == 'Y' && $boolCatalogSet)
				$strProductType .= ('' != $strProductType ? ', ' : '').Loc::getMessage('IBEL_CATALOG_TYPE_MESS_GROUP');
			$row->AddViewField('CATALOG_TYPE', $strProductType);
		}
		if ($bCatalog && isset($arSelectedFieldsMap['CATALOG_MEASURE']))
		{
			if (isset($arElementOps[$itemId]["element_edit_price"]) && $boolCatalogPrice)
			{
				$row->AddSelectField('CATALOG_MEASURE', $measureList);
			}
			else
			{
				$measureTitle = (isset($measureList[$row->arRes['CATALOG_MEASURE']])
					? $measureList[$row->arRes['CATALOG_MEASURE']]
					: $measureList[0]
				);
				$row->AddViewField('CATALOG_MEASURE', $measureTitle);
				unset($measureTitle);
			}
		}

		$arActions = array();

		$actionEdit = $lAdmin->getRowAction(CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		));
		$actionCopy = $lAdmin->getRowAction(CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID, 'action' => 'copy'),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		));

		if($row->arRes['ACTIVE'] == 'Y')
		{
			$arActive = array(
				"TEXT" => Loc::getMessage("IBSEL_A_DEACTIVATE"),
				"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DEACTIVATE, $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
		else
		{
			$arActive = array(
				"TEXT" => Loc::getMessage("IBSEL_A_ACTIVATE"),
				"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::ACTIVATE, $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
		$clearCounter = array(
			"TEXT" => Loc::getMessage('IBSEL_A_CLEAR_COUNTER'),
			"TITLE" => Loc::getMessage('IBSEL_A_CLEAR_COUNTER_TITLE'),
			"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage("IBSEL_A_CLEAR_COUNTER_CONFIRM"))."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::CLEAR_COUNTER, $sThisSectionUrl),
			"ONCLICK" => ""
		);

		if (
			$boolSubWorkFlow
		)
			{
				if (isset($arElementOps[$itemId])
					&& isset($arElementOps[$itemId]["element_edit_any_wf_status"])
			)
			{
				$STATUS_PERMISSION = 2;
			}
			else
			{
				$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"]);
			}
			$intMinPerm = 2;

			$arUnLock = array(
				"ICON" => "unlock",
				"TEXT" => Loc::getMessage("IBEL_A_UNLOCK"),
				"TITLE" => Loc::getMessage("IBLOCK_UNLOCK_ALT"),
				"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage("IBLOCK_UNLOCK_CONFIRM"))."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::ELEMENT_UNLOCK, $sThisSectionUrl)
			);

			if ($row->arRes['orig']['LOCK_STATUS'] == "red")
			{
				if (CWorkflow::IsAdmin())
					$arActions[] = $arUnLock;
			}
			else
			{
				if (
					isset($arElementOps[$itemId])
					&& isset($arElementOps[$itemId]["element_edit"])
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					if ($row->arRes['orig']['LOCK_STATUS'] == "yellow")
					{
						$arActions[] = $arUnLock;
						$arActions[] = array("SEPARATOR"=>true);
					}

					$arActions[] = array(
						"ICON" => "edit",
						"TEXT" => Loc::getMessage("IBEL_A_CHANGE"),
						"DEFAULT" => true,
						"ACTION" => $actionEdit
					);
					$arActions[] = $arActive;
					$arActions[] = array('SEPARATOR' => 'Y');
					$arActions[] = $clearCounter;
					$arActions[] = array('SEPARATOR' => 'Y');
				}

				if ($boolIBlockElementAdd
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					$arActions[] = array(
						"ICON" => "copy",
						"TEXT" => Loc::getMessage("IBEL_A_COPY_ELEMENT"),
						"ACTION" => $actionCopy
					);
				}

				if (
					isset($arElementOps[$itemId])
					&& isset($arElementOps[$itemId]["element_delete"])
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					if (!isset($arElementOps[$itemId]["element_edit_any_wf_status"]))
						$intMinPerm = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"], $itemId);
					if (2 <= $intMinPerm)
					{
						if (!empty($arActions))
							$arActions[] = array("SEPARATOR"=>true);
						$arActions[] = array(
							"ICON" => "delete",
							"TEXT" => Loc::getMessage('IBLOCK_SUBELEMENT_LIST_ACTION_DELETE'),
							"TITLE" => Loc::getMessage("IBLOCK_DELETE_ALT"),
							"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage('IBLOCK_CONFIRM_DEL_MESSAGE'))."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DELETE, $sThisSectionUrl)
						);

					}
				}
			}
		}
		elseif ($boolSubBizproc)
		{
			$bWritePermission = $iblockDocument->CanUserOperateDocument(
				CBPCanUserOperateOperation::WriteDocument,
				$currentUser['ID'],
				$itemId,
				array(
					"IBlockId" => $intSubIBlockID,
					"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
					"DocumentStates" => $arDocumentStates,
				)
			);

			$bStartWorkflowPermission = $iblockDocument->CanUserOperateDocument(
				CBPCanUserOperateOperation::StartWorkflow,
				$currentUser['ID'],
				$itemId,
				array(
					"IBlockId" => $intSubIBlockID,
					"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
					"DocumentStates" => $arDocumentStates,
				)
			);

			if ($row->arRes['lockStatus'] == "red")
			{
				if (CBPDocument::IsAdmin())
				{
					$arActions[] = Array(
						"ICON" => "unlock",
						"TEXT" => Loc::getMessage("IBEL_A_UNLOCK"),
						"TITLE" => Loc::getMessage("IBEL_A_UNLOCK_ALT"),
						"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage("IBEL_A_UNLOCK_CONFIRM"))."')) ".$lAdmin->ActionDoGroup($itemId, ActionType::ELEMENT_UNLOCK, $sThisSectionUrl),
					);
				}
			}
			elseif ($bWritePermission)
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => Loc::getMessage("IBEL_A_CHANGE"),
					"DEFAULT" => true,
					"ACTION" => $actionEdit
				);
				$arActions[] = $arActive;
				$arActions[] = array('SEPARATOR' => 'Y');
				$arActions[] = $clearCounter;
				$arActions[] = array('SEPARATOR' => 'Y');

				$arActions[] = array(
					"ICON" => "copy",
					"TEXT" => Loc::getMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION" => $actionCopy
				);

				$arActions[] = array("SEPARATOR" => true);
				$arActions[] = array(
					"ICON" => "delete",
					"TEXT" => Loc::getMessage('IBLOCK_SUBELEMENT_LIST_ACTION_DELETE'),
					"TITLE" => Loc::getMessage("IBLOCK_DELETE_ALT"),
					"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage('IBLOCK_CONFIRM_DEL_MESSAGE'))."')) ".$lAdmin->ActionDoGroup($itemId, ActionType::DELETE, $sThisSectionUrl),
				);
			}
		}
		else
		{
			if (
				isset($arElementOps[$itemId])
				&& isset($arElementOps[$itemId]["element_edit"])
			)
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => Loc::getMessage("IBEL_A_CHANGE"),
					"DEFAULT" => true,
					"ACTION" => $actionEdit
				);
				$arActions[] = $arActive;
				$arActions[] = array('SEPARATOR' => 'Y');
				$arActions[] = $clearCounter;
				$arActions[] = array('SEPARATOR' => 'Y');
			}

			if ($boolIBlockElementAdd && isset($arElementOps[$itemId])
				&& isset($arElementOps[$itemId]["element_edit"]))
			{
				$arActions[] = array(
					"ICON" => "copy",
					"TEXT" => Loc::getMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION" => $actionCopy
				);
			}

			if (
				isset($arElementOps[$itemId])
				&& isset($arElementOps[$itemId]["element_delete"])
			)
			{
				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"ICON" => "delete",
					"TEXT" => Loc::getMessage('IBLOCK_SUBELEMENT_LIST_ACTION_DELETE'),
					"TITLE" => Loc::getMessage("IBLOCK_DELETE_ALT"),
					"ACTION" => "if (confirm('".CUtil::JSEscape(Loc::getMessage('IBLOCK_CONFIRM_DEL_MESSAGE'))."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DELETE, $sThisSectionUrl)
				);
			}
		}

		if (!empty($arActions))
			$row->AddActions($arActions);
	}
	unset($row);

	if (!empty($priceTypeIndex) && !empty($productShowPrices))
	{
		$operateIdList = array_keys($productShowPrices);
		$emptyPrices = array();
		if (!empty($productEditPrices))
		{
			$emptyPrices = array_fill_keys(
				array_keys($productEditPrices),
				array_fill_keys($priceTypeIndex, true)
			);
		}
		sort($operateIdList);
		foreach (array_chunk($operateIdList, 500) as $pageIds)
		{
			$iterator = Catalog\PriceTable::getList(array(
				'select' => array('ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'EXTRA_ID', 'QUANTITY_FROM', 'QUANTITY_TO'),
				'filter' => array(
					'@CATALOG_GROUP_ID' => $priceTypeIndex,
					'@PRODUCT_ID' => $pageIds,
					array(
						'LOGIC' => 'OR',
						'<=QUANTITY_FROM' => 1,
						'=QUANTITY_FROM' => null
					),
					array(
						'LOGIC' => 'OR',
						'>=QUANTITY_TO' => 1,
						'=QUANTITY_TO' => null
					)
				),
				'order' => ['PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC']
			));
			while ($row = $iterator->fetch())
			{
				$productId = (int)$row['PRODUCT_ID'];
				$priceType = (int)$row['CATALOG_GROUP_ID'];
				$extraId = (int)$row['EXTRA_ID'];

				$price = \CCurrencyLang::CurrencyFormat($row['PRICE'], $row['CURRENCY'], true);

				if ($extraId > 0)
				{
					$price .= ' <span title="'.
						htmlspecialcharsbx(Loc::getMessage(
							'IBSEL_CATALOG_EXTRA_DESCRIPTION',
							array('#VALUE#' => $arCatExtra[$extraId]['NAME'])
						)).
						'">(+'.$arCatExtra[$extraId]['PERCENTAGE'].'%)</span>';
				}

				$arRows[$productId]->AddViewField('CATALOG_GROUP_'.$priceType, $price);
				$arRows[$productId]->arRes['CATALOG_GROUP_'.$priceType] = $price;
				unset($price);
				if (isset($productEditPrices[$productId]))
				{
					unset($emptyPrices[$productId][$priceType]);
					$currencyControl = '<select name="CATALOG_CURRENCY['.$productId.']['.$priceType.']" id="CATALOG_CURRENCY['.$productId.']['.$priceType.']"';
					if ($priceType == $basePriceTypeId)
						$currencyControl .= ' onchange="top.SubChangeBaseCurrency('.$productId.')"';
					elseif ($extraId > 0)
						$currencyControl .= ' disabled="disabled" readonly="readonly"';
					$currencyControl .= '>';
					foreach ($arCurrencyList as $currencyCode)
					{
						$currencyControl .= '<option value="'.$currencyCode.'"';
						if ($currencyCode == $row['CURRENCY'])
							$currencyControl .= ' selected';
						$currencyControl .= '>'.$currencyCode.'</option>';
					}
					unset($currencyCode);
					$currencyControl .= '</select>';

					$priceControl = '<input type="text" size="9" id="CATALOG_PRICE['.$productId.']['.$priceType.']" name="CATALOG_PRICE['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['PRICE']).'"';
					if ($priceType == $basePriceTypeId)
						$priceControl .= ' onchange="top.SubChangeBasePrice('.$productId.')"';
					elseif ($extraId > 0)
						$priceControl .= ' disabled readonly';
					$priceControl .= '> '.$currencyControl;
					if ($extraId > 0)
						$priceControl .= '<input type="hidden" id="CATALOG_EXTRA['.$productId.']['.$priceType.']" name="CATALOG_EXTRA['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['EXTRA_ID']).'">';

					$priceControl .= '<input type="hidden" name="CATALOG_old_PRICE['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['PRICE']).'">';
					$priceControl .= '<input type="hidden" name="CATALOG_old_CURRENCY['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['CURRENCY']).'">';
					$priceControl .= '<input type="hidden" name="CATALOG_PRICE_ID['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['ID']).'">';
					$priceControl .= '<input type="hidden" name="CATALOG_QUANTITY_FROM['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($row['QUANTITY_FROM']).'">';
					$priceControl .= '<input type="hidden" name="CATALOG_QUANTITY_TO['.$productId.']['.$priceType.']" value="'.htmlspecialcharsbx($arRes['QUANTITY_TO']).'">';

					$arRows[$productId]->AddEditField('CATALOG_GROUP_'.$priceType, $priceControl);
					unset($priceControl, $currencyControl);
				}
			}
			unset($row);
			unset($iterator);
		}
		if (!empty($emptyPrices))
		{
			foreach (array_keys($emptyPrices) as $productId)
			{
				if (empty($emptyPrices[$productId]))
					continue;
				foreach (array_keys($emptyPrices[$productId]) as $priceType)
				{
					$currencyControl = '<select name="CATALOG_CURRENCY['.$productId.']['.$priceType.']" id="CATALOG_CURRENCY['.$productId.']['.$priceType.']"';
					if ($priceType == $basePriceTypeId)
						$currencyControl .= ' onchange="top.SubChangeBaseCurrency('.$productId.')"';
					$currencyControl .= '>';
					foreach ($arCurrencyList as $currencyCode)
					{
						$currencyControl .= '<option value="'.$currencyCode.'">'.$currencyCode.'</option>';
					}
					unset($currencyCode);
					$currencyControl .= '</select>';

					$priceControl = '<input type="text" size="9" id="CATALOG_PRICE['.$productId.']['.$priceType.']" name="CATALOG_PRICE['.$productId.']['.$priceType.']" value=""';
					if ($priceType == $basePriceTypeId)
						$priceControl .= ' onchange="top.SubChangeBasePrice('.$productId.')"';
					$priceControl .= '> '.$currencyControl;

					$priceControl .= '<input type="hidden" name="CATALOG_old_PRICE['.$productId.']['.$priceType.']" value="">';
					$priceControl .= '<input type="hidden" name="CATALOG_old_CURRENCY['.$productId.']['.$priceType.']" value="">';
					$priceControl .= '<input type="hidden" name="CATALOG_PRICE_ID['.$productId.']['.$priceType.']" value="">';
					$priceControl .= '<input type="hidden" name="CATALOG_QUANTITY_FROM['.$productId.']['.$priceType.']" value="">';
					$priceControl .= '<input type="hidden" name="CATALOG_QUANTITY_TO['.$productId.']['.$priceType.']" value="">';

					$arRows[$productId]->AddEditField('CATALOG_GROUP_'.$priceType, $priceControl);
					unset($priceControl, $currencyControl);
				}
				unset($priceType);
			}
			unset($productId);
		}
		unset($emptyPrices);
	}

	if($bCatalog && !empty($listItemId))
	{
		$subscriptions = Catalog\SubscribeTable::getList(array(
			'select' => array('ITEM_ID', 'CNT'),
			'filter' => array('@ITEM_ID' => $listItemId, 'DATE_TO' => null),
			'runtime' => array(new Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		));
		while($subscribe = $subscriptions->fetch())
		{
			if(isset($arRows[$subscribe['ITEM_ID']]))
			{
				$arRows[$subscribe['ITEM_ID']]->addField('SUBSCRIPTIONS', $subscribe['CNT']);
			}
		}
	}

	$lAdmin->AddFooter(
		array(
			array("title" => Loc::getMessage("IBLOCK_SUBELEMENT_LIST_SELECTED"), "value" => $selectedCount),
			array("counter" => true, "title" => Loc::getMessage("IBLOCK_SUBELEMENT_LIST_CHECKED"), "value" => "0"),
		)
	);

	$actionList = [];
	$arParams = array(
		'disable_action_target' => true
	);
	foreach ($arElementOps as $id => $arOps)
	{
		if (isset($arOps["element_delete"]))
		{
			$actionList[] = ActionType::DELETE;
			break;
		}
	}

	$elementEdit = false;
	foreach ($arElementOps as $id => $arOps)
	{
		if (isset($arOps["element_edit"]))
		{
			$elementEdit = true;
			break;
		}
	}

	if ($elementEdit)
	{
		$actionList[] = ActionType::ACTIVATE;
		$actionList[] = ActionType::DEACTIVATE;
		$actionList[] = ActionType::CLEAR_COUNTER;
		if ($boolCatalogPrice)
		{
			$elementEditPrice = false;
			foreach($arElementOps as $id => $arOps)
			{
				if (isset($arOps["element_edit_price"]))
				{
					$elementEditPrice = true;
					break;
				}
			}
			if ($elementEditPrice)
			{
				$actionList[] = Catalog\Grid\ProductAction::SET_FIELD;
			}
		}
	}

	if ($boolSubWorkFlow)
	{
		$actionList[] = ActionType::ELEMENT_UNLOCK;
		$actionList[] = ActionType::ELEMENT_LOCK;
		$actionList[] = ActionType::ELEMENT_WORKFLOW_STATUS;
	}
	elseif ($bBizproc)
	{
		$actionList[] = ActionType::ELEMENT_UNLOCK;
	}

	$lAdmin->AddGroupActionTable($panelAction->getList($actionList), $arParams);

?><script type="text/javascript">
/**
 * @return {boolean|string}
 */
function CheckProductName(id)
{
	if (!id)
		return false;
	var obj = BX(id),
		obFormElement;
	if (!obj)
		return false;
	obj.blur();
	obFormElement = BX.findParent(obj,{tag: 'form'});
	if (!obFormElement)
		return false;
	if ((obFormElement.elements['NAME']) && (0 < obFormElement.elements['NAME'].value.length))
		return obFormElement.elements['NAME'].value;
	else
		return false;

}
function ShowNewOffer(id)
{
	var mxProductName = CheckProductName(id),
		PostParams;
	if (!mxProductName)
		alert('<? echo CUtil::JSEscape(Loc::getMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
		PostParams = {};
		PostParams.bxpublic = 'Y';
		<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
		{
			?>PostParams.bxsku = 'Y';<?
		}
		?>
		PostParams.PRODUCT_NAME = mxProductName;
		PostParams.sessid = BX.bitrix_sessid();
		(new BX.CAdminDialog({
			'content_url': '<? echo CIBlock::GetAdminSubElementEditLink($intSubIBlockID, $intSubPropValue, 0, array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID), $sThisSectionUrl, ((defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))); ?>',
			'content_post': PostParams,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
}

function ShowNewOfferExt(id)
{
	var mxProductName = CheckProductName(id),
		PostParams;
	if (!mxProductName)
		alert('<? echo CUtil::JSEscape(Loc::getMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
		PostParams = {};
		PostParams.bxpublic = 'Y';
		<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
		{
			?>PostParams.bxsku = 'Y';<?
		}
		?>
		PostParams.PRODUCT_NAME = mxProductName;
		PostParams.sessid = BX.bitrix_sessid();
		(new BX.CAdminDialog({
			'content_url': '<? echo CIBlock::GetAdminSubElementEditLink($intSubIBlockID, $intSubPropValue, 0, array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID, 'SUBPRODUCT_TYPE' => CCatalogAdminTools::TAB_GROUP), $sThisSectionUrl, defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1); ?>',
			'content_post': PostParams,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
}

function ShowSkuGenerator(id)
{
	var mxProductName = CheckProductName(id),
		requriedFields = '',
		PostParams = {};
	<?
	$arIBlock = CIBlock::GetArrayByID($intSubIBlockID);
	if (isset($arIBlock['FIELDS']) && is_array($arIBlock['FIELDS']))
	{
		foreach($arIBlock['FIELDS'] as $fieldName => $arFieldValue)
		{
			switch($fieldName)
			{
				case "IBLOCK_SECTION":
				case "ACTIVE_FROM":
				case "ACTIVE_TO":
				case "SORT":
				case "PREVIEW_TEXT":
				case "DETAIL_TEXT":
				case "CODE":
				case "TAGS":
					if($arFieldValue["IS_REQUIRED"] === 'Y')
					{
					?>
						requriedFields += '- <?=$arFieldValue["NAME"]?>\n';
					<?
					}
					break;
			}
		}
	}
?>
	if(requriedFields !== '')
	{
		requriedFields = '<? echo CUtil::JSEscape(Loc::getMessage('IB_SE_L_REQUIRED_FIELDS_FIND')); ?>:\n' + requriedFields;
		alert(requriedFields);
	}
	else if (!mxProductName)
		alert('<? echo CUtil::JSEscape(Loc::getMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
	PostParams.bxpublic = 'Y';
	PostParams.PRODUCT_NAME = mxProductName;
	<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
	{
		?>PostParams.bxsku = 'Y';<?
	}
	?>
	PostParams.sessid = BX.bitrix_sessid();

	(new BX.CAdminDialog({
		'content_url': '/bitrix/tools/catalog/iblock_subelement_generator.php?subIBlockId=<? echo $intSubIBlockID; ?>&subPropValue=<? echo $intSubPropValue; ?>&subTmpId=<? echo $strSubTMP_ID; ?>&iBlockId=<? echo $arSubCatalog['PRODUCT_IBLOCK_ID']; ?>',
		'content_post': PostParams,
		'draggable': true,
		'resizable': true,
		'height': screen.height / 2,
		'width': screen.width / 2,
		'buttons': [
			{
				title: '<? echo CUtil::JSEscape(Loc::getMessage('IB_SE_L_GENERATE')); ?>',
				id: 'savebtn',
				name: 'savebtn',
				className: 'adm-btn-save',
				action: function () {
					this.disableUntilError();
					this.parentWindow.Submit();
				}
			},
			BX.CAdminDialog.btnCancel
		]
	})).Show();
	}
}
</script><?
	//We need javascript not in excel mode
	if (($_REQUEST["mode"]=='list' || $_REQUEST["mode"]=='frame') && $boolSubCatalog && $boolSubCurrency)
	{
		?><script type="text/javascript">
		top.arSubCatalogShowedGroups = [];
		top.arSubExtra = [];
		top.arSubCatalogGroups = [];
		top.SubBaseIndex = <?=$basePriceTypeId;?>;
		<?
		if (!empty($priceTypeIndex))
		{
			$i = 0;
			$j = 0;
			foreach($priceTypeIndex as $priceType)
			{
				echo "top.arSubCatalogShowedGroups[".$i."]=".$priceType.";\n";
				$i++;
				if ($priceType != $basePriceTypeId)
				{
					echo "top.arSubCatalogGroups[".$j."]=".$priceType.";\n";
					$j++;
				}
			}
			unset($priceType);
		}
		if (!empty($arCatExtra))
		{
			$i = 0;
			foreach ($arCatExtra as &$CatExtra)
			{
				echo "top.arSubExtra[".$CatExtra["ID"]."]=".$CatExtra["PERCENTAGE"].";\n";
				$i++;
			}
			unset($CatExtra);
		}
		?>
		top.SubChangeBasePrice = function(id)
		{
			var i,
				cnt,
				pr,
				price,
				extraId,
				esum,
				eps;
			for (i = 0, cnt = top.arSubCatalogShowedGroups.length; i < cnt; i++)
			{
				pr = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.arSubCatalogShowedGroups[i]+"]");
				if (pr.disabled)
				{
					price = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.SubBaseIndex+"]").value;
					if (price > 0)
					{
						extraId = top.document.getElementById("CATALOG_EXTRA["+id+"]"+"["+top.arSubCatalogShowedGroups[i]+"]").value;
						esum = parseFloat(price) * (1 + top.arSubExtra[extraId] / 100);
						eps = 1.00/Math.pow(10, 6);
						esum = Math.round((esum+eps)*100)/100;
					}
					else
						esum = "";

					pr.value = esum;
				}
			}
		};

		top.SubChangeBaseCurrency = function(id)
		{
			var currency = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.SubBaseIndex+"]"),
				i,
				cnt,
				pr;
			for (i = 0, cnt = top.arSubCatalogShowedGroups.length; i < cnt; i++)
			{
				pr = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.arSubCatalogShowedGroups[i]+"]");
				if (pr.disabled)
				{
					pr.selectedIndex = currency.selectedIndex;
				}
			}
		};
	</script>
	<?
	}

	$aContext = array();
	if ($boolIBlockElementAdd)
	{
		$aContext[] = array(
			"ICON" => "btn_sub_new",
			"TEXT" => htmlspecialcharsEx('' != trim($arSubIBlock["ELEMENT_ADD"]) ? $arSubIBlock["ELEMENT_ADD"] : Loc::getMessage('IB_SE_L_ADD_NEW_ELEMENT')),
			"ONCLICK" => "javascript:ShowNewOffer('btn_sub_new')",
			"TITLE" => Loc::getMessage("IB_SE_L_ADD_NEW_ELEMENT_DESCR")
		);
		if (Catalog\Config\Feature::isProductSetsEnabled())
		{
			$aContext[] = array(
				"ICON" => "btn",
				"TEXT" => Loc::getMessage('IB_SE_L_ADD_NEW_ELEMENT_WITH_GROUP'),
				"ONCLICK" => "javascript:ShowNewOfferExt('btn_sub_new')",
				"TITLE" => Loc::getMessage("IB_SE_L_ADD_NEW_ELEMENT_WITH_GROUP_DESCR")
			);
		}
	}
	$aContext[] = array(
		"ICON"=>"btn_sub_refresh",
		"TEXT"=>htmlspecialcharsEx(Loc::getMessage('IB_SE_L_REFRESH_ELEMENTS')),
		"ONCLICK" => "javascript:".$lAdmin->ActionAjaxReload($lAdmin->GetListUrl(true)),
		"TITLE"=>Loc::getMessage("IB_SE_L_REFRESH_ELEMENTS_DESCR"),
	);
	if ($boolIBlockElementAdd)
	{
		$arJSDescription = array(
			'js' => '/bitrix/js/catalog/sub_generator.js',
			'css' => '/bitrix/panel/catalog/sub-generator.css',
			'lang' => '/bitrix/modules/catalog/lang/'.LANGUAGE_ID.'/tools/iblock_subelement_generator.php'
		);
		CJSCore::RegisterExt('iblock_generator', $arJSDescription);
		CJSCore::Init(array('iblock_generator', 'file_input'));

		$aContext[] = array(
			"ICON"=>"btn_sub_gen",
			"TEXT"=>htmlspecialcharsEx(Loc::getMessage('IB_SE_L_GENERATE_ELEMENTS')),
			"ONCLICK" => "javascript:ShowSkuGenerator('btn_sub_gen');",
			"TITLE"=>Loc::getMessage("IB_SE_L_GENERATE_ELEMENTS")
		);
	}

	$excelExport = (Main\Config\Option::get("iblock", "excel_export_rights") == "Y"
		? CIBlockRights::UserHasRightTo($intSubIBlockID, $intSubIBlockID, "iblock_export")
		: true
	);
	$lAdmin->AddAdminContextMenu(
		$aContext,
		$excelExport
	);

	$lAdmin->CheckListMode();

	$lAdmin->DisplayList(B_ADMIN_SUBELEMENTS_LIST);
	if ($boolSubWorkFlow || $boolSubBizproc)
	{
		echo BeginNote();?>
		<span class="adm-lamp adm-lamp-green"></span> - <?echo Loc::getMessage("IBLOCK_GREEN_ALT")?><br>
		<span class="adm-lamp adm-lamp-yellow"></span> - <?echo Loc::getMessage("IBLOCK_YELLOW_ALT")?><br>
		<span class="adm-lamp adm-lamp-red"></span> - <?echo Loc::getMessage("IBLOCK_RED_ALT")?><br>
		<?echo EndNote();
	}
}
else
{
	ShowMessage(Loc::getMessage('IB_SE_L_SHOW_PRICES_AFTER_COPY'));
}
