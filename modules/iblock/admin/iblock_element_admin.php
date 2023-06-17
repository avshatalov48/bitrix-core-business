<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Crm\Order\Import\Instagram;
use Bitrix\Crm;
use Bitrix\Currency;
use Bitrix\Iblock;
use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

$bBizproc = Loader::includeModule("bizproc");
$bWorkflow = Loader::includeModule("workflow");
$bFileman = Loader::includeModule("fileman");
$dsc_cookie_name = Main\Config\Option::get('main', 'cookie_name', 'BITRIX_SM')."_DSC";

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;

$selfFolderUrl = $adminPage->getSelfFolderUrl();

$bSearch = false;
$bCurrency = false;
$arCurrencyList = array();

$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
$minImageSize = array("W" => 1, "H"=>1);
$maxImageSize = array(
	"W" => $listImageSize,
	"H" => $listImageSize,
);
unset($listImageSize);
$useCalendarTime = Main\Config\Option::get('iblock', 'list_full_date_edit') == 'Y';

if (isset($_REQUEST['mode']) && ($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame'))
	CFile::DisableJSFunction(true);

$type = '';
if (isset($_REQUEST['type']) && is_string($_REQUEST['type']))
	$type = trim($_REQUEST['type']);
if ($type === '')
	$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
if($arIBTYPE===false)
	$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

$IBLOCK_ID = 0;
if (isset($_REQUEST['IBLOCK_ID']))
	$IBLOCK_ID = (int)$_REQUEST["IBLOCK_ID"];

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
	$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
else
	$bBadBlock = true;

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>
	<a href="<?echo htmlspecialcharsbx("iblock_admin.php?lang=".LANGUAGE_ID."&type=".urlencode($_REQUEST["type"]))?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?php
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$request = Main\Context::getCurrent()->getRequest();
// TODO: hack for psevdo-excel export in crm (\CAdminUiList::GetSystemContextMenu)
$urlBuilderManager = Iblock\Url\AdminPage\BuilderManager::getInstance();
$urlBuilder = null;
$urlBuilderId = (string)$request->get('urlBuilderId') ;
if ($urlBuilderId !== '')
{
	$urlBuilder = $urlBuilderManager->getBuilder($urlBuilderId);
}
// TODO end
if ($urlBuilder === null)
{
	$urlBuilder = $urlBuilderManager->getBuilder();
}
unset($urlBuilderManager);
if ($urlBuilder === null)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBEL_ERR_BUILDER_ADSENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$urlBuilderId = $urlBuilder->getId();
//TODO: hack fo compensation BX.adminSidePanel.prototype.checkActionByUrl where remove IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER from url
if ($urlBuilderId === 'INVENTORY')
{
	$urlBuilder->setSliderMode(true);
}
// end hack
$urlBuilder->setIblockId($IBLOCK_ID);
$urlBuilder->setUrlParams([]);

// TODO: remove after realization of the new grid of products.
if ($publicMode)
{
	/**
	 * @var CMain $APPLICATION
	 */

	$bodyClass = $APPLICATION->GetPageProperty('BodyClass', '');
	$APPLICATION->SetPageProperty('BodyClass', str_replace('no-background', '', $bodyClass));
}

$pageConfig = array(
	'IBLOCK_EDIT' => false,
	'CHECK_NEW_CARD' => false,
	'USE_NEW_CARD' => false,
	'CATALOG' => false,
	'PUBLIC_CRM_CATALOG' => false,
	'PUBLIC_MODE' => false,

	'LIST_ID_PREFIX' => '',
	'LIST_ID' => $_REQUEST["type"].'.'.$IBLOCK_ID,
	'SHOW_NAVCHAIN' => true,
	'NAVCHAIN_ROOT' => false,
	'ALLOW_EXTERNAL_LINK' => true,

	'ALLOW_USER_EDIT' => true,

	'SLIDER_CRM' => false,
	'DEFAULT_ACTION_TYPE' => CAdminUiListRow::LINK_TYPE_URL,
	'SKIP_URL_MODIFICATION' => false,
);
switch ($urlBuilderId)
{
	case 'SHOP':
	case 'CRM':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_product_admin_';
		$pageConfig['CHECK_NEW_CARD'] = true;
		$pageConfig['SHOW_NAVCHAIN'] = false;
		$pageConfig['CONTEXT_PATH'] = '/shop/settings/cat_product_admin.php?' . $urlBuilder->getUrlBuilderIdParam(); // TODO: temporary hack
		$pageConfig['CATALOG'] = true;
		$pageConfig['ALLOW_EXTERNAL_LINK'] = false;
		$pageConfig['ALLOW_USER_EDIT'] = false;
		$pageConfig['SLIDER_CRM'] = $urlBuilder->isSliderMode();
		$pageConfig['DEFAULT_ACTION_TYPE'] = CAdminUiListRow::LINK_TYPE_SLIDER;
		if (Loader::includeModule('crm'))
		{
			$pageConfig['PUBLIC_CRM_CATALOG'] = \Bitrix\Crm\Product\Catalog::getDefaultId() === $IBLOCK_ID;
		}
		$pageConfig['PUBLIC_MODE'] = true;
		break;
	case 'INVENTORY':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_product_admin_';
		$pageConfig['CHECK_NEW_CARD'] = true;
		$pageConfig['SHOW_NAVCHAIN'] = false;
		$pageConfig['CONTEXT_PATH'] = '/shop/settings/cat_product_admin.php?' . $urlBuilder->getUrlBuilderIdParam(); // TODO: temporary hack
		$pageConfig['CATALOG'] = true;
		$pageConfig['ALLOW_EXTERNAL_LINK'] = false;
		$pageConfig['ALLOW_USER_EDIT'] = false;
		$pageConfig['DEFAULT_ACTION_TYPE'] = CAdminUiListRow::LINK_TYPE_SLIDER;
		if (Loader::includeModule('crm'))
		{
			$pageConfig['PUBLIC_CRM_CATALOG'] = \Bitrix\Crm\Product\Catalog::getDefaultId() === $IBLOCK_ID;
		}
		$pageConfig['PUBLIC_MODE'] = true;
		break;
	case 'CATALOG':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_product_admin_';
		$pageConfig['CONTEXT_PATH'] = '/bitrix/admin/cat_product_admin.php'; // TODO: temporary hack
		$pageConfig['CATALOG'] = true;
		break;
	case 'IBLOCK':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_iblock_element_';
		$pageConfig['IBLOCK_EDIT'] = true;
		$pageConfig['NAVCHAIN_ROOT'] = true;
		$pageConfig['CONTEXT_PATH'] = '/bitrix/admin/iblock_element_admin.php'; // TODO: temporary hack
		break;
}

$currentUser = array(
	'ID' => $USER->GetID(),
	'GROUPS' => $USER->GetUserGroupArray()
);

$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
);
$canViewUser = (
	(
		$USER->CanDoOperation('edit_all_users')
		|| $USER->CanDoOperation('edit_subordinate_users')
		|| $canViewUserList
	)
	&& $pageConfig['ALLOW_USER_EDIT']
);

$arIBlock["SITE_ID"] = array();
$rsSites = CIBlock::GetSite($IBLOCK_ID);
while($arSite = $rsSites->Fetch())
	$arIBlock["SITE_ID"][] = $arSite["LID"];

$bWorkFlow = $bWorkflow && (CIBlock::GetArrayByID($IBLOCK_ID, "WORKFLOW") != "N");
$bBizproc = $bBizproc && (CIBlock::GetArrayByID($IBLOCK_ID, "BIZPROC") != "N");

/** @var CIBlockDocument $iblockDocument */
$iblockDocument = null;
if ($bBizproc)
{
	$iblockDocument = new CIBlockDocument();
}

$elementTranslit = $arIBlock["FIELDS"]["CODE"]["DEFAULT_VALUE"];
$useElementTranslit = $elementTranslit["TRANSLITERATION"] == "Y" && $elementTranslit["USE_GOOGLE"] != "Y";
$elementTranslitSettings = array();
if ($useElementTranslit)
{
	$elementTranslitSettings = array(
		"max_len" => $elementTranslit['TRANS_LEN'],
		"change_case" => $elementTranslit['TRANS_CASE'],
		"replace_space" => $elementTranslit['TRANS_SPACE'],
		"replace_other" => $elementTranslit['TRANS_OTHER'],
		"delete_repeat_replace" => ($elementTranslit['TRANS_EAT'] == 'Y')
	);
}
$changeUserByActive = Main\Config\Option::get('iblock', 'change_user_by_group_active_modify') === 'Y';

const MODULE_ID = "iblock";
const ENTITY = "CIBlockDocument";
define("DOCUMENT_TYPE", "iblock_".$IBLOCK_ID);

$bCatalog = Loader::includeModule("catalog");
$arCatalog = false;
$boolSKU = false;
$boolSKUFiltrable = false;
$uniq_id = 0;
$useStoreControl = false;
$strSaveWithoutPrice = '';
$boolCatalogRead = false;
$boolCatalogPrice = false;
$boolCatalogPurchasInfo = false;
$catalogPurchasInfoEdit = false;
$boolCatalogSet = false;
$boolCatalogExport = false;
$boolCatalogProductAdd = false;
$boolCatalogProductEdit = false;
$boolCatalogProductDelete = false;
$showCatalogWithOffers = false;
$useSummaryStoreAmount = false;
$productTypeList = array();
$productLimits = false;
$priceTypeList = array();
$priceTypeIndex = array();
$basePriceType = [];
$basePriceTypeId = 0;
$measureList = array();
$vatList = array();
$newProductCard = false;
$clearedGridFields = array();
$lockedGridFields = array();
$enableConversionToService = false;
if ($bCatalog)
{
	$accessController = AccessController::getCurrent();
	$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();
	$strSaveWithoutPrice = Main\Config\Option::get('catalog','save_product_without_price');
	$boolCatalogExport = $accessController->check(ActionDictionary::ACTION_CATALOG_EXPORT_EXECUTION);
	$boolCatalogProductAdd = $accessController->check(ActionDictionary::ACTION_PRODUCT_ADD);
	$boolCatalogProductEdit = $accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT);
	$boolCatalogProductDelete = $accessController->check(ActionDictionary::ACTION_PRODUCT_DELETE);
	$boolCatalogRead = $accessController->check(ActionDictionary::ACTION_CATALOG_READ);
	$boolCatalogPrice = $accessController->check(ActionDictionary::ACTION_PRICE_EDIT);
	$boolCatalogPurchasInfo = $accessController->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW);
	$boolCatalogSet = Catalog\Config\Feature::isProductSetsEnabled();
	$useSummaryStoreAmount = \CCatalogAdminTools::needSummaryStoreAmountByPermissions();
	$enableConversionToService = Main\Config\Option::get('catalog', 'enable_convert_product_to_service') === 'Y';
	$arCatalog = CCatalogSKU::GetInfoByIBlock($arIBlock["ID"]);
	if (empty($arCatalog))
	{
		$bCatalog = false;
	}
	else
	{
		if (CCatalogSKU::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE'] || CCatalogSKU::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
		{
			if (CIBlockRights::UserHasRightTo($arCatalog['IBLOCK_ID'], $arCatalog['IBLOCK_ID'], "iblock_admin_display"))
			{
				$boolSKU = true;
			}
		}
		if (!$boolCatalogRead && !$boolCatalogPrice)
			$bCatalog = false;
		$productTypeList = \CCatalogAdminTools::getIblockProductTypeList($arIBlock['ID'], true);
	}
	if ($bCatalog)
	{
		if ($pageConfig['CHECK_NEW_CARD'])
		{
			switch ($urlBuilderId)
			{
				case 'SHOP':
				case 'INVENTORY':
					$pageConfig['USE_NEW_CARD'] = Catalog\Config\State::isProductCardSliderEnabled();
					break;
				case 'CRM':
					if (Loader::includeModule('crm'))
					{
						$pageConfig['USE_NEW_CARD'] = Crm\Settings\LayoutSettings::getCurrent()->isFullCatalogEnabled();
					}
					break;
			}
		}
		if ($pageConfig['USE_NEW_CARD'])
		{
			$pageConfig['LIST_ID'] .= '.NEW';
		}
		$newProductCard = $pageConfig['USE_NEW_CARD'];
	}
	if ($boolCatalogPurchasInfo)
		$catalogPurchasInfoEdit = $boolCatalogPrice && !$useStoreControl;
	$basePriceType = \CCatalogGroup::GetBaseGroup();
	if (!empty($basePriceType))
		$basePriceTypeId = $basePriceType['ID'];
	$measureIterator = CCatalogMeasure::getList(
		array(),
		array(),
		false,
		false,
		array('ID', 'MEASURE_TITLE', 'SYMBOL_RUS', 'CODE')
	);
	while($measure = $measureIterator->Fetch())
	{
		$measureList[$measure['ID']] =
			$measure['SYMBOL_RUS'] !== ''
				? $measure['SYMBOL_RUS']
				: $measure['MEASURE_TITLE']
		;
	}
	unset($measure, $measureIterator);
	asort($measureList);

	$clearedGridFields = \CCatalogAdminTools::getClearedGridFields(['USE_NEW_CARD' => $pageConfig['USE_NEW_CARD']]);
	$lockedGridFields = \CCatalogAdminTools::getLockedGridFields(['USE_NEW_CARD' => $pageConfig['USE_NEW_CARD']]);
}

$arFileProps = array();
$arProps = array();
$iterator = Iblock\PropertyTable::getList([
	'select' => ['*'],
	'filter' => ['=IBLOCK_ID' => $IBLOCK_ID],
	'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
]);
while ($arProp = $iterator->fetch())
{
	$arProp["USER_TYPE"] = (string)$arProp["USER_TYPE"];
	$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
	$arProp["USER_TYPE_SETTINGS"] = $arProp["USER_TYPE_SETTINGS_LIST"];
	unset($arProp["USER_TYPE_SETTINGS_LIST"]);
	if ($arProp["ACTIVE"] == "Y")
		$arProps[] = $arProp;

	if ($arProp["PROPERTY_TYPE"] == Iblock\PropertyTable::TYPE_FILE)
		$arFileProps[$arProp["ID"]] = $arProp;
}
unset($arProp, $iterator);

if ($boolSKU)
{
	$arSKUProps = array();
	$iterator = Iblock\PropertyTable::getList([
		'select' => ['*'],
		'filter' => [
			'=IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
			'!=ID' => $arCatalog['SKU_PROPERTY_ID'],
			'!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE,
			'=ACTIVE' => 'Y',
			'=FILTRABLE' => 'Y'
		],
		'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
	]);
	while ($arProp = $iterator->fetch())
	{
		$arProp["USER_TYPE"] = (string)$arProp["USER_TYPE"];
		$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
		$arProp["USER_TYPE_SETTINGS"] = $arProp["USER_TYPE_SETTINGS_LIST"];
		unset($arProp["USER_TYPE_SETTINGS_LIST"]);
		$boolSKUFiltrable = true;
		$arSKUProps[] = $arProp;
	}
	unset($arProp, $iterator);
}

$sTableID = $pageConfig['LIST_ID_PREFIX'].md5($pageConfig['LIST_ID']);
if ($pageConfig['SLIDER_CRM'])
{
	$sTableID .= '_slider_' . $urlBuilderId;
	$gridOptions = new Bitrix\Main\Grid\Options($sTableID);
	$gridOptions->resetView('default');
	unset($gridOptions);
}

$oSort = new CAdminUiSorting($sTableID, "timestamp_x", "desc");
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$lAdmin = new CAdminUiList($sTableID, $oSort);
$lAdmin->bMultipart = true;
$lAdmin->setPublicModeState($pageConfig['PUBLIC_MODE']);

$bExcel = $lAdmin->isExportMode();

$groupParams = array(
	'ENTITY_ID' => $sTableID,
	'IBLOCK_ID' => $IBLOCK_ID
);
if ($bCatalog)
{
	$panelAction = new Catalog\Grid\Panel\ProductGroupAction($groupParams);
}
else
{
	$panelAction = new Iblock\Grid\Panel\GroupAction($groupParams);
}
unset($groupParams);

if(isset($_REQUEST["del_filter"]) && $_REQUEST["del_filter"] != "")
	$find_section_section = -1;
elseif(isset($_REQUEST["find_section_section"]))
	$find_section_section = $_REQUEST["find_section_section"];
else
	$find_section_section = -1;
//We have to handle current section in a special way
$section_id = (int)$find_section_section;
if(!$pageConfig['CATALOG'])
	$find_section_section = $section_id;
//This is all parameters needed for proper navigation
$sThisSectionUrl = $urlBuilder->getUrlParams([
	'find_section_section' => (int)$find_section_section
]);

if (!empty($arCatalog))
{
	$productLimits = Catalog\Config\State::getExceedingProductLimit((int)$arIBlock['ID'], (int)$find_section_section);
}

$sectionItems = array(
	"" => GetMessage("IBLOCK_ALL"),
	"0" => GetMessage("IBLOCK_UPPER_LEVEL"),
);
$sectionQueryObject = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
while($arSection = $sectionQueryObject->Fetch())
	$sectionItems[$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];

// region Filter definitions
/*
 * TODO Initially, the ability to filter by these fields was not implemented.
 * "find_el_modified_by",
 * "find_el_created_by",
 */

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("IBLOCK_FIELD_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true
	),
	array(
		"id" => "ID",
		"name" => rtrim(GetMessage("IBLOCK_FILTER_FROMTO_ID"), ":"),
		"type" => "number",
		"filterable" => ""
	)
);
if ($arIBTYPE["SECTIONS"] == "Y" && !$pageConfig['SLIDER_CRM'])
{
	$filterFields[] = array(
		"id" => "SECTION_ID",
		"name" => GetMessage("IBLOCK_FIELD_SECTION_ID"),
		"type" => "list",
		"items" => $sectionItems,
		"filterable" => "",
		"default" => true
	);
	$filterFields[] = array(
		"id" => "INCLUDE_SUBSECTIONS",
		"name" => GetMessage("IBLOCK_INCLUDING_SUBSECTIONS"),
		"type" => "checkbox",
		"filterable" => "",
		"default" => true
	);
}
$filterFields[] = array(
	"id" => "DATE_MODIFY_FROM",
	"name" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
	"type" => "date",
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "MODIFIED_USER_ID",
	"name" => GetMessage("IBLOCK_FIELD_MODIFIED_BY"),
	"type" => "custom_entity",
	"selector" => array("type" => "user"),
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "DATE_CREATE",
	"name" => GetMessage("IBLOCK_EL_ADMIN_DCREATE"),
	"type" => "date",
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "CREATED_USER_ID",
	"name" => rtrim(GetMessage("IBLOCK_EL_ADMIN_WCREATE"), ":"),
	"type" => "custom_entity",
	"selector" => array("type" => "user"),
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "DATE_ACTIVE_FROM",
	"name" => GetMessage("IBEL_A_ACTFROM"),
	"type" => "date",
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "DATE_ACTIVE_TO",
	"name" => GetMessage("IBEL_A_ACTTO"),
	"type" => "date",
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "ACTIVE",
	"name" => GetMessage("IBLOCK_FIELD_ACTIVE"),
	"type" => "list",
	"items" => array(
		"Y" => GetMessage("IBLOCK_YES"),
		"N" => GetMessage("IBLOCK_NO")
	),
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "SEARCHABLE_CONTENT",
	"name" => rtrim(GetMessage("IBLOCK_EL_ADMIN_DESC"), ":"),
	"filterable" => "?"
);
if ($bWorkFlow)
{
	$workflowStatus = array();
	$rs = CWorkflowStatus::GetDropDownList("Y");
	while ($arRs = $rs->Fetch())
	{
		$workflowStatus[$arRs["REFERENCE_ID"]] = $arRs["REFERENCE"];
	}
	unset($rs, $arRs);
	$filterFields[] = array(
		"id" => "WF_STATUS",
		"name" => GetMessage("IBEL_A_F_STATUS"),
		"type" => "list",
		"items" => $workflowStatus,
		"filterable" => ""
	);
	$filterFields[] = array(
		"id" => "WF_LAST_STATUS_ID",
		"name" => GetMessage("IBEL_A_F_LAST_STATUS"),
		"type" => "list",
		"items" => $workflowStatus,
		"filterable" => ""
	);
	unset($workflowStatus);
}
$filterFields[] = array(
	"id" => "CODE",
	"name" => GetMessage("IBEL_A_CODE"),
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "EXTERNAL_ID",
	"name" => GetMessage("IBEL_A_EXTERNAL_ID"),
	"filterable" => ""
);
$filterFields[] = array(
	"id" => "TAGS",
	"name" => GetMessage("IBEL_A_TAGS"),
	"filterable" => "?"
);

if ($bCatalog)
{
	$filterFields[] = array(
		"id" => "CATALOG_TYPE",
		"name" => GetMessage("IBEL_CATALOG_TYPE"),
		"type" => "list",
		"items" => $productTypeList,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	);
	if ($boolCatalogSet)
	{
		$filterFields[] = array(
			"id" => "CATALOG_BUNDLE",
			"name" => GetMessage("IBEL_CATALOG_BUNDLE"),
			"type" => "list",
			"items" => array(
				"Y" => GetMessage("IBLOCK_YES"),
				"N" => GetMessage("IBLOCK_NO")
			),
			"filterable" => ""
		);
	}
	$filterFields[] = array(
		"id" => "CATALOG_AVAILABLE",
		"name" => GetMessage("IBEL_CATALOG_AVAILABLE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => ""
	);
	if (!$useSummaryStoreAmount)
	{
		$filterFields[] = [
			"id" => "QUANTITY",
			"name" => GetMessage("IBEL_CATALOG_QUANTITY_EXT"),
			"type" => "number",
			"filterable" => ""
		];
	}
	$filterFields[] = array(
		"id" => "MEASURE",
		"name" => GetMessage("IBEL_CATALOG_MEASURE_TITLE"),
		"type" => "list",
		"items" => $measureList,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	);
}

$propertyManager = new Iblock\Helpers\Filter\PropertyManager($IBLOCK_ID);
$filterFields = array_merge($filterFields, $propertyManager->getFilterFields());

// endregion

$lAdmin->BeginEpilogContent();
$propertyManager->renderCustomFields($sTableID);
$lAdmin->EndEpilogContent();
$propertySKUFilterFields = [];
/** @var Iblock\Helpers\Filter\PropertyManager $propertySKUManager */
$propertySKUManager = null;
if ($boolSKU)
{
	$propertySKUManager = new Iblock\Helpers\Filter\PropertyManager($arCatalog["IBLOCK_ID"]);
	$propertySKUFilterFields = $propertySKUManager->getFilterFields();
	$lAdmin->BeginEpilogContent();
	$propertySKUManager->renderCustomFields($sTableID);
	$lAdmin->EndEpilogContent();
}

$arFilter = array(
	"IBLOCK_ID" => $IBLOCK_ID,
	"SHOW_NEW" => "Y",
	"CHECK_PERMISSIONS" => "Y",
	"MIN_PERMISSION" => "R",
);

$lAdmin->AddFilter($filterFields, $arFilter);
$propertyManager->AddFilter($sTableID, $arFilter);

$arSubQuery = array();
if ($boolSKU)
{
	$filterFields = array_merge($filterFields, $propertySKUFilterFields);
	if ($boolSKUFiltrable)
	{
		$arSubQuery = array("IBLOCK_ID" => $arCatalog["IBLOCK_ID"]);
		$lAdmin->AddFilter($propertySKUFilterFields, $arSubQuery);
		$propertySKUManager->AddFilter($sTableID, $arSubQuery);
	}
}
if (isset($arFilter["SECTION_ID"]))
{
	$find_section_section = (int)$arFilter["SECTION_ID"];
}

if ($bBizproc && "E" != $arIBlock["RIGHTS_MODE"])
{
	$strPerm = CIBlock::GetPermission($IBLOCK_ID);
	if ("W" > $strPerm)
	{
		unset($arFilter["CHECK_PERMISSIONS"]);
		unset($arFilter["MIN_PERMISSION"]);
		$arFilter["CHECK_BP_PERMISSIONS"] = "read";
	}
}

if (!empty($arFilter[">=DATE_MODIFY_FROM"]))
{
	$arFilter["DATE_MODIFY_FROM"] = $arFilter[">=DATE_MODIFY_FROM"];
	$arFilter["DATE_MODIFY_TO"] = $arFilter["<=DATE_MODIFY_FROM"];
	unset($arFilter[">=DATE_MODIFY_FROM"]);
	unset($arFilter["<=DATE_MODIFY_FROM"]);
}

if ($boolSKU && 1 < sizeof($arSubQuery))
{
	$arFilter["ID"] = CIBlockElement::SubQuery("PROPERTY_".$arCatalog["SKU_PROPERTY_ID"], $arSubQuery);
}

$emptySectionId =
	$find_section_section === ''
	|| $find_section_section === null
	|| (int)$find_section_section < 0
;

if ($pageConfig['SLIDER_CRM'])
{
	if (!$emptySectionId)
	{
		$arFilter['SECTION_ID'] = $find_section_section;
		$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
	}
}
else
{
	if ($emptySectionId)
	{
		unset($arFilter['SECTION_ID']);
		unset($arFilter['INCLUDE_SUBSECTIONS']);
	}
}

$selectedSkuId = null;
$isChangeVariationRequest = $request->get('grid_action') === 'changeVariation';
if ($isChangeVariationRequest)
{
	$productId = (int)$request->get('productId');
	$selectedSkuId = (int)$request->get('variationId');
	if (!empty($productId) && !empty($selectedSkuId))
	{
		$arFilter['ID'] = $productId;
	}
}

// region Columns definitions

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

if ($pageConfig['USE_NEW_CARD'])
{
	if (!$pageConfig['SLIDER_CRM'])
	{
		$defaultHeaders = [
			'CATALOG_PRODUCT',
			'MORE_PHOTO',
			'SECTIONS',
			'CATALOG_QUANTITY',
			'CATALOG_MEASURE',
		];
	}
	else
	{
		$defaultHeaders = [
			'CATALOG_PRODUCT',
			'MORE_PHOTO',
			'CATALOG_QUANTITY',
			'CATALOG_MEASURE',
		];
	}
}
else
{
	$defaultHeaders = [
		'CATALOG_TYPE', 'NAME', 'ACTIVE', 'SORT', 'TIMESTAMP_X', 'ID',
		'WF_STATUS_ID', 'LOCK_STATUS',
		'CATALOG_AVAILABLE',
		'BP_PUBLISHED',
	];
}

$defaultHeaders = array_fill_keys($defaultHeaders, true);

$arHeader = [];
if ($bCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_TYPE",
		"content" => GetMessage("IBEL_CATALOG_TYPE"),
		"title" => GetMessage('IBEL_CATALOG_TYPE_TITLE'),
		"align" => "right",
		"sort" => "TYPE",
	);
}

if (!$pageConfig['USE_NEW_CARD'])
{
	$arHeader[] = array(
		"id" => "NAME",
		"content" => GetMessage("IBLOCK_FIELD_NAME"),
		"title" => "",
		"sort" => "name",
		"column_sort" => 200,
	);
}
if ($arIBTYPE["SECTIONS"] == "Y")
{
	$arHeader[] = array(
		"id" => "SECTIONS",
		"content" => GetMessage("IBEL_A_SECTIONS"),
		"title" => "",
		"column_sort" => 700,
	);
}
$arHeader[] = array(
	"id" => "ACTIVE",
	"content" => GetMessage("IBLOCK_FIELD_ACTIVE"),
	"title" => "",
	"sort" => "active",
	"align" => "center",
	"column_sort" => 100,
);
$arHeader[] = array(
	"id" => "DATE_ACTIVE_FROM",
	"content" => GetMessage("IBEL_A_ACTFROM"),
	"title" => "",
	"sort" => "date_active_from",
);
$arHeader[] = array(
	"id" => "DATE_ACTIVE_TO",
	"content" => GetMessage("IBEL_A_ACTTO"),
	"title" => "",
	"sort" => "date_active_to",
);
$arHeader[] = array(
	"id" => "SORT",
	"content" => GetMessage("IBLOCK_FIELD_SORT"),
	"title" => "",
	"sort" => "sort",
	"align" => "right",
);
$arHeader[] = array(
	"id" => "TIMESTAMP_X",
	"content" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
	"title" => "",
	"sort" => "timestamp_x",
);
$arHeader[] = array(
	"id" => "USER_NAME",
	"content" => GetMessage("IBLOCK_FIELD_USER_NAME"),
	"title" => "",
	"sort" => "modified_by",
);
$arHeader[] = array(
	"id" => "DATE_CREATE",
	"content" => GetMessage("IBLOCK_EL_ADMIN_DCREATE"),
	"title" => "",
	"sort" => "created",
);
$arHeader[] = array(
	"id" => "CREATED_USER_NAME",
	"content" => GetMessage("IBLOCK_EL_ADMIN_WCREATE2"),
	"title" => "",
	"sort" => "created_by",
);
$arHeader[] = array(
	"id" => "CODE",
	"content" => GetMessage("IBEL_A_CODE"),
	"title" => "",
	"sort" => "code",
);
$arHeader[] = array(
	"id" => "EXTERNAL_ID",
	"content" => GetMessage("IBEL_A_EXTERNAL_ID"),
	"title" => "",
	"sort" => "external_id",
);
$arHeader[] = array(
	"id" => "TAGS",
	"content" => GetMessage("IBEL_A_TAGS"),
	"title" => "",
	"sort" => "tags",
);

if($bWorkFlow)
{
	$arHeader[] = array(
		"id" => "WF_STATUS_ID",
		"content" => GetMessage("IBLOCK_FIELD_STATUS"),
		"title" => "",
		"sort" => "status",
	);
	$arHeader[] = array(
		"id" => "WF_NEW",
		"content" => GetMessage("IBEL_A_EXTERNAL_WFNEW"),
		"title" => "",
		"sort" => "",
	);
	$arHeader[] = array(
		"id" => "LOCK_STATUS",
		"content" => GetMessage("IBEL_A_EXTERNAL_LOCK"),
		"title" => "",
	);
	$arHeader[] = array(
		"id" => "LOCKED_USER_NAME",
		"content" => GetMessage("IBEL_A_EXTERNAL_LOCK_BY"),
		"title" => "",
	);
	$arHeader[] = array(
		"id" => "WF_DATE_LOCK",
		"content" => GetMessage("IBEL_A_EXTERNAL_LOCK_WHEN"),
		"title" => "",
	);
	$arHeader[] = array(
		"id" => "WF_COMMENTS",
		"content" => GetMessage("IBEL_A_EXTERNAL_COM"),
		"title" => "",
	);
}

$arHeader[] = array(
	"id" => "SHOW_COUNTER",
	"content" => GetMessage("IBEL_A_EXTERNAL_SHOWS"),
	"title" => "",
	"sort" => "show_counter",
	"align" => "right",
	"column_sort" => 800,
);
$arHeader[] = array(
	"id" => "SHOW_COUNTER_START",
	"content" => GetMessage("IBEL_A_EXTERNAL_SHOW_F"),
	"title" => "",
	"sort" => "show_counter_start",
	"align" => "right",
);

if (!$pageConfig['USE_NEW_CARD'])
{
	$arHeader[] = [
		"id" => "PREVIEW_PICTURE",
		"content" => GetMessage("IBEL_A_EXTERNAL_PREV_PIC"),
		"title" => "",
		"sort" => "has_preview_picture",
		"align" => "right",
		"editable" => true,
		"prevent_default" => true,
	];
}

$arHeader[] = [
	"id" => "PREVIEW_TEXT",
	"content" => GetMessage("IBEL_A_EXTERNAL_PREV_TEXT"),
	"title" => "",
];

if (!$pageConfig['USE_NEW_CARD'])
{
	$arHeader[] = [
		"id" => "DETAIL_PICTURE",
		"content" => GetMessage("IBEL_A_EXTERNAL_DET_PIC"),
		"title" => "",
		"sort" => "has_detail_picture",
		"align" => "center",
		"editable" => true,
		"prevent_default" => true,
	];
}

$arHeader[] = array(
	"id" => "DETAIL_TEXT",
	"content" => GetMessage("IBEL_A_EXTERNAL_DET_TEXT"),
	"title" => "",
);
$arHeader[] = array(
	"id" => "ID",
	"content" => "ID",
	"title" => "",
	"sort" => "id",
	"align" => "right",
);

$moreProtoPropertyId = null;
foreach ($arProps as $arFProps)
{
	$editable = true;
	$preventDefault = true;

	$extendedMorePhoto = $pageConfig['USE_NEW_CARD'] && $arFProps['CODE'] === 'MORE_PHOTO';

	if (
		$arFProps['PROPERTY_TYPE'] === 'F'
		&& $arFProps['MULTIPLE'] === 'Y'
		&& !$extendedMorePhoto
	)
	{
		$editable = false;
		$preventDefault = false;
	}

	$columnSort = null;
	$headerId = 'PROPERTY_' . $arFProps['ID'];
	if ($extendedMorePhoto)
	{
		$moreProtoPropertyId = $arFProps['ID'];
		$headerId = 'MORE_PHOTO';
		$columnSort = 200;
	}

	$arHeader[] = [
		'id' => $headerId,
		'content' => $arFProps['NAME'],
		'title' => '',
		'align' => ($arFProps['PROPERTY_TYPE'] === 'N'? 'right': 'left'),
		"sort" => ($arFProps['MULTIPLE'] !== 'Y' && !$extendedMorePhoto ? 'PROPERTY_' . $arFProps['ID']: ''),
		'default' => isset($defaultHeaders[$headerId]),
		'editable' => $editable,
		'prevent_default' => $preventDefault,
		'column_sort' => $columnSort,
	];
}
unset($arFProps);

$arWFStatusAll = Array();
$arWFStatusPerm = Array();
if($bWorkFlow)
{
	$rsWF = CWorkflowStatus::GetDropDownList("Y");
	while($arWF = $rsWF->Fetch())
	{
		$arWFStatusAll[$arWF["REFERENCE_ID"]] = htmlspecialcharsEx($arWF["REFERENCE"]);
	}
	$rsWF = CWorkflowStatus::GetDropDownList("N", "desc");
	while($arWF = $rsWF->Fetch())
	{
		$arWFStatusPerm[$arWF["REFERENCE_ID"]] = $arWF["REFERENCE"];
	}
	unset($rsWF);
}

$arCatExtra = [];
if($bCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_AVAILABLE",
		"content" => GetMessage("IBEL_CATALOG_AVAILABLE"),
		"title" => GetMessage("IBEL_CATALOG_AVAILABLE_TITLE_EXT"),
		"align" => "center",
		"sort" => "AVAILABLE",
	);

	if ($pageConfig['USE_NEW_CARD'])
	{
		$arHeader[] = [
			"id" => "CATALOG_PRODUCT",
			"content" => GetMessage("IBEL_A_CATALOG_PRODUCT"),
			"title" => GetMessage("IBEL_A_CATALOG_PRODUCT_TITLE"),
			"align" => "left",
			"column_sort" => 200,
			"width" => 420,
			"sort" => "NAME",
		];
	}

	if ($arCatalog['CATALOG_TYPE'] != CCatalogSKU::TYPE_PRODUCT)
	{
		if (\CCatalogAdminTools::allowedShowQuantityFields())
		{
			$arHeader[] = [
				"id" => "CATALOG_QUANTITY",
				"content" => ($pageConfig["USE_NEW_CARD"]
					? GetMessage("IBEL_CATALOG_QUANTITY_NEW_CARD")
					: GetMessage("IBEL_CATALOG_QUANTITY_EXT")
				),
				"title" => "",
				"align" => "right",
				"sort" => ($pageConfig["USE_NEW_CARD"] || $useSummaryStoreAmount ? "" : "QUANTITY"),
				"column_sort" => 400,
			];
			$arHeader[] = [
				"id" => "CATALOG_QUANTITY_RESERVED",
				"content" => ($pageConfig["USE_NEW_CARD"]
					? GetMessage("IBEL_CATALOG_QUANTITY_RESERVED_NEW_CARD")
					: GetMessage("IBEL_CATALOG_QUANTITY_RESERVED")
				),
				"align" => "right",
			];
		}
		$arHeader[] = array(
			"id" => "CATALOG_MEASURE_RATIO",
			"content" => GetMessage("IBEL_CATALOG_MEASURE_RATIO"),
			"title" => GetMessage('IBEL_CATALOG_MEASURE_RATIO_TITLE'),
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_MEASURE",
			"content" => GetMessage("IBEL_CATALOG_MEASURE"),
			"title" => GetMessage('IBEL_CATALOG_MEASURE_TITLE'),
			"align" => "right",
			"sort" => "MEASURE",
			"column_sort" => 500,
		);
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY_TRACE",
			"content" => GetMessage("IBEL_CATALOG_QUANTITY_TRACE_EXT"),
			"title" => GetMessage("IBEL_CATALOG_QUANTITY_TRACE"),
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CAN_BUY_ZERO",
			"content" => GetMessage("IBEL_CATALOG_CAN_BUY_ZERO"),
			"title" => GetMessage("IBEL_CATALOG_CAN_BUY_ZERO_TITLE"),
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_WEIGHT",
			"content" => GetMessage("IBEL_CATALOG_WEIGHT"),
			"title" => "",
			"align" => "right",
			"sort" => "WEIGHT",
		);
		$arHeader[] = array(
			"id" => "CATALOG_WIDTH",
			"content" => GetMessage("IBEL_CATALOG_WIDTH"),
			"title" => "",
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_LENGTH",
			"content" => GetMessage("IBEL_CATALOG_LENGTH"),
			"title" => "",
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_HEIGHT",
			"content" => GetMessage("IBEL_CATALOG_HEIGHT"),
			"title" => "",
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_VAT_INCLUDED",
			"content" => GetMessage("IBEL_CATALOG_VAT_INCLUDED"),
			"title" => "",
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "VAT_ID",
			"content" => GetMessage('IBEL_CATALOG_VAT_ID'),
			"title" => "",
			"align" => "right",
		);

		$iblockData = \CCatalog::GetByID($IBLOCK_ID);

		$vatList = array(
			0 => GetMessage('IBEL_CATALOG_EMPTY_VALUE') . ' ' . GetMessage('IBEL_CATALOG_DEFAULT')
		);
		$vatIterator = Catalog\VatTable::getList([
			'select' => ['ID', 'NAME', 'SORT'],
			'filter' => ['=ACTIVE' => 'Y'],
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC']
		]);
		while ($vat = $vatIterator->fetch())
		{
			if ($vat['ID'] === $iblockData['VAT_ID'])
			{
				$vatList[0] = $vat['NAME'] .
					' ' . GetMessage("IBEL_CATALOG_DEFAULT");
			}

			$vat['ID'] = (int)$vat['ID'];
			$vatList[$vat['ID']] = $vat['NAME'];
		}
		unset($vat, $vatIterator);

		if ($boolCatalogPurchasInfo)
		{
			$arHeader[] = array(
				"id" => "CATALOG_PURCHASING_PRICE",
				"content" => GetMessage("IBEL_CATALOG_PURCHASING_PRICE"),
				"title" => "",
				"align" => "right",
				"sort" => "PURCHASING_PRICE",
			);
		}
		if ($useStoreControl)
		{
			$arHeader[] = array(
				"id" => "CATALOG_BAR_CODE",
				"content" => GetMessage("IBEL_CATALOG_BAR_CODE"),
				"title" => "",
				"align" => "right",
			);
		}

		$priceTypeList = Catalog\GroupTable::getTypeList();
		if (!empty($priceTypeList))
		{
			foreach ($priceTypeList as $priceType)
			{
				$default = isset($defaultHeaders["CATALOG_GROUP_".$priceType["ID"]]);
				if ($pageConfig['USE_NEW_CARD'])
				{
					$default = $default || $priceType['BASE'] === 'Y';
				}
				$arHeader[] = array(
					"id" => "CATALOG_GROUP_".$priceType["ID"],
					"content" => $priceType["NAME_LANG"] ?? $priceType["NAME"],
					"align" => "right",
					"sort" => "SCALED_PRICE_".$priceType["ID"],
					"default" => $default,
					"column_sort" => $default ? 600 : null,
				);
			}
			unset($priceType);
		}

		$db_extras = CExtra::GetList(array("ID" => "ASC"));
		while ($extras = $db_extras->Fetch())
			$arCatExtra[$extras['ID']] = $extras;
		unset($extras, $db_extras);
	}
}

if ($bBizproc)
{
	$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID));
	foreach ($arWorkflowTemplates as $arTemplate)
	{
		$arHeader[] = array(
			"id" => "WF_".$arTemplate["ID"],
			"content" => $arTemplate["NAME"],
		);
	}
	$arHeader[] = array(
		"id" => "BIZPROC",
		"content" => GetMessage("IBEL_A_BP_H"),
	);
	$arHeader[] = array(
		"id" => "BP_PUBLISHED",
		"content" => GetMessage("IBLOCK_FIELD_BP_PUBLISHED"),
		"sort" => "status",
	);
}

foreach ($arHeader as &$row)
{
	if (!isset($row['default']))
	{
		$row['default'] = isset($defaultHeaders[$row['id']]);
	}
}
unset($row);

if ($pageConfig['USE_NEW_CARD'])
{
	Main\Type\Collection::sortByColumn($arHeader, ['column_sort' => SORT_ASC], '', PHP_INT_MAX);
}

// endregion

$lAdmin->AddHeaders($arHeader);
$lAdmin->AddVisibleHeaderColumn('ID');

$showCatalogWithOffers = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
$isUsedNewProductField = in_array('CATALOG_PRODUCT', $lAdmin->GetVisibleHeaderColumns(), true);
if ($isUsedNewProductField)
{
	$showCatalogWithOffers = true;
}

$arSelectedFieldsMap = array_fill_keys($lAdmin->GetVisibleHeaderColumns(), true);

if ($arIBTYPE['SECTIONS'] != 'Y')
{
	if (isset($arSelectedFieldsMap['SECTIONS']))
		unset($arSelectedFieldsMap['SECTIONS']);
}

$arSelectedProps = array();
$selectedPropertyIds = array();
$arSelect = array();
foreach($arProps as $arProperty)
{
	if (isset($arSelectedFieldsMap['PROPERTY_'.$arProperty['ID']]))
	{
		$arSelectedProps[] = $arProperty;
		$selectedPropertyIds[] = $arProperty['ID'];
		if($arProperty["PROPERTY_TYPE"] == "L")
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
$arSelectedFieldsMap["NAME"] = true;
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

$measureList[0] = ' ';
if ($bCatalog)
{
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
		$bCurrency = Loader::includeModule('currency');
		if ($bCurrency)
		{
			$arCurrencyList = Currency\CurrencyManager::getSymbolList();
		}
	}
	unset($boolPriceInc);

	if ($boolCatalogSet)
	{
		$arSelectedFieldsMap['CATALOG_BUNDLE'] = true;
	}
}

$arSelectedFields = $arSelectedFieldsMap;
if (isset($arSelectedFields['SECTIONS']))
	unset($arSelectedFields['SECTIONS']);
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
	case 'CATALOG_TYPE':
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

if($lAdmin->EditAction())
{
	if (!empty($_FILES['FIELDS']) && is_array($_FILES['FIELDS']))
		CFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

	if ($bCatalog)
	{
		Catalog\Product\Sku::enableDeferredCalculation();
	}

	if (is_array($_POST['FIELDS']))
	{
		$ib = new CIBlockElement();

		foreach($_POST['FIELDS'] as $ID=>$arFields)
		{
			if(!$lAdmin->IsUpdated($ID))
				continue;
			$ID = (int)$ID;

			$arRes = CIBlockElement::GetByID($ID);
			$arRes = $arRes->Fetch();
			if(!$arRes)
				continue;

			$elementIblockID = $arRes['IBLOCK_ID'];

			$WF_ID = $ID;
			if($bWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($ID);
				if($WF_ID!=$ID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $ID;
				}

				if($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']==ActionType::ELEMENT_UNLOCK && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR1")." (ID:".$ID.")", $ID);
					continue;
				}
			}
			elseif ($bBizproc)
			{
				if ($iblockDocument->IsDocumentLocked($ID, ""))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $ID)), $ID);
					continue;
				}
			}

			if($bWorkFlow)
			{
				if (!CIBlockElementRights::UserHasRightTo($elementIblockID, $ID, "element_edit"))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					continue;
				}

				// handle workflow status access permissions
				if (CIBlockElementRights::UserHasRightTo($elementIblockID, $ID, "element_edit_any_wf_status"))
					$STATUS_PERMISSION = true;
				elseif ($arFields["WF_STATUS_ID"] > 0)
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arFields["WF_STATUS_ID"]) >= 1;
				else
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]) >= 2;

				if (!$STATUS_PERMISSION)
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					continue;
				}

			}
			elseif($bBizproc)
			{
				$bCanWrite = $iblockDocument->CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$currentUser['ID'],
					$ID,
					array(
						"IBlockId" => $elementIblockID,
						'IBlockRightsMode' => $arIBlock['RIGHTS_MODE'],
						'UserGroups' => $currentUser['GROUPS']
					)
				);
				if(!$bCanWrite)
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					continue;
				}
			}
			elseif(!CIBlockElementRights::UserHasRightTo($elementIblockID, $ID, "element_edit"))
			{
				$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
				continue;
			}

			if (array_key_exists("PREVIEW_PICTURE", $arFields))
			{
				$arFields["PREVIEW_PICTURE"] = CIBlock::makeFileArray(
					$arFields["PREVIEW_PICTURE"],
					$arFields["PREVIEW_PICTURE"] === "null",
					$_REQUEST["FIELDS_descr"][$ID]["PREVIEW_PICTURE"]
				);
			}

			if (array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$arFields["DETAIL_PICTURE"] = CIBlock::makeFileArray(
					$arFields["DETAIL_PICTURE"],
					$arFields["DETAIL_PICTURE"] === "null",
					$_REQUEST["FIELDS_descr"][$ID]["DETAIL_PICTURE"]
				);
			}

			if (!isset($arFields["PROPERTY_VALUES"]) || !is_array($arFields["PROPERTY_VALUES"]))
			{
				$arFields["PROPERTY_VALUES"] = [];
			}

			$bFieldProps = array();
			foreach ($arFields as $k=>$v)
			{
				if ($k != "PROPERTY_VALUES" && strncmp($k, "PROPERTY_", 9) == 0)
				{
					$prop_id = mb_substr($k, 9);
					if (isset($arFileProps[$prop_id]))
					{
						if ($v === "null")
						{
							$v = [];
							$pvObjectQuery = CIBlockElement::getPropertyValues(
								$elementIblockID,
								['ID' => $ID],
								true,
								['ID' => $prop_id]
							);
							$propertyValues = $pvObjectQuery->fetch();
							if (
								!empty($propertyValues) &&
								!empty($propertyValues["PROPERTY_VALUE_ID"][$prop_id])
							)
							{
								$propertyValueId = $propertyValues["PROPERTY_VALUE_ID"][$prop_id];
								$v[$propertyValueId] = CIBlock::makeFilePropArray($v, true);
							}
							unset($pvObjectQuery);
						}
						else
						{
							$v = CIBlock::makeFilePropArray($v);
						}
					}

					$arFields["PROPERTY_VALUES"][$prop_id] = $v;
					unset($arFields[$k]);
					$bFieldProps[$prop_id] = true;
				}

				if ($k == "TAGS" && is_array($v))
					$arFields[$k] = $v[0];
			}

			if ($pageConfig['USE_NEW_CARD'] && $moreProtoPropertyId !== null)
			{
				if (isset($arFields['MORE_PHOTO']) && is_array($arFields['MORE_PHOTO']))
				{
					foreach ($arFields['MORE_PHOTO'] as $key => $value)
					{
						if ($key === 'DETAIL_PICTURE' || $key === 'PREVIEW_PICTURE')
						{
							$value = is_array($value) ? $value : (int)$value;
							$arFields[$key] = \CIBlock::makeFileArray(
								$value,
								($arFields['MORE_PHOTO'][$key.'_del'] ?? 'N') === 'Y',
								$arFields['MORE_PHOTO'][$key . '_descr'] ?? '',
								['allow_file_id' => true]
							);
						}
						else
						{
							[$type, $propertyId, $valueId, $additionalInfo] = explode('_', $key);
							if ($type === 'PROPERTY' && $additionalInfo === null)
							{
								$bFieldProps[$propertyId] = true;
								$arFields['PROPERTY_VALUES'][$propertyId] = $arFields['PROPERTY_VALUES'][$propertyId] ?? [];
								$pureKey = $type . '_' . $propertyId . '_' . $valueId;
								$description = $arFields['MORE_PHOTO'][$pureKey .'_descr'] ?? '';
								$deleted = ($arFields['MORE_PHOTO'][$pureKey .'_del'] ?? 'N') === 'Y';

								if (is_array($value))
								{
									$newValueId = (int)$valueId > 0 ? $valueId : null;
									$arFields['PROPERTY_VALUES'][$propertyId][$newValueId] = \CIBlock::makeFilePropArray(
										$value,
										$deleted,
										$description
									);
								}
								elseif ((int)$value > 0)
								{
									$arFields['PROPERTY_VALUES'][$propertyId][$valueId] = \CIBlock::makeFilePropArray(
										(int)$value,
										$deleted,
										$description,
										['allow_file_id' => true]
									);
								}
							}
						}
					}

					unset($arFields['MORE_PHOTO']);
				}
			}

			if(!empty($bFieldProps))
			{
				//We have to read properties from database in order not to delete its values
				if(!$bWorkFlow)
				{
					$dbPropV = CIBlockElement::GetProperty(
						$elementIblockID,
						$ID,
						'sort',
						'asc',
						['ACTIVE' => 'Y']
					);
					while($arPropV = $dbPropV->Fetch())
					{
						if(!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
						{
							if(!array_key_exists($arPropV["ID"], $arFields["PROPERTY_VALUES"]))
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
			foreach ($arIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if ($field["VISIBLE"] === "N")
				{
					continue;
				}
				if (preg_match("/^(SECTION_|LOG_)/", $FIELD_ID))
				{
					continue;
				}
				if (
					$field["IS_REQUIRED"] === "Y"
					&& !array_key_exists($FIELD_ID, $arFields)
					&& $FIELD_ID !== "DETAIL_PICTURE"
					&& $FIELD_ID !== "PREVIEW_PICTURE"
				)
				{
					$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
				}
			}
			if($arRes["IN_SECTIONS"] == "Y")
			{
				$arFields["IBLOCK_SECTION"] = array();
				$rsSections = CIBlockElement::GetElementGroups($arRes["ID"], true, array('ID', 'IBLOCK_ELEMENT_ID'));
				while($arSection = $rsSections->Fetch())
					$arFields["IBLOCK_SECTION"][] = $arSection["ID"];
			}

			$arFields["MODIFIED_BY"] = $currentUser['ID'];
			$ib->LAST_ERROR = '';
			$DB->StartTransaction();

			if(!$ib->Update($ID, $arFields, true, true, true))
			{
				$lAdmin->AddGroupError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ib->LAST_ERROR)), $ID);
				$DB->Rollback();
			}
			else
			{
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($elementIblockID, $ID);
				$ipropValues->clearValues();
				$DB->Commit();
			}

			if ($bCatalog)
			{
				if(
					$boolCatalogProductEdit
					&& CIBlockElementRights::UserHasRightTo($elementIblockID, $ID, "element_edit_price")
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
					if (
						isset($arFields['CATALOG_AVAILABLE'])
						&& (
							$arFields['CATALOG_AVAILABLE'] === Catalog\ProductTable::STATUS_YES
							|| $arFields['CATALOG_AVAILABLE'] === Catalog\ProductTable::STATUS_NO
						)
					)
					{
						$arCatalogProduct['AVAILABLE'] = $arFields['CATALOG_AVAILABLE'];
					}

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
						'filter' => array('=ID' => $ID)
					))->fetch();
					if (empty($product))
					{
						$arCatalogProduct['ID'] = $ID;
						$result = Catalog\Model\Product::add(array('fields' => $arCatalogProduct));
					}
					else
					{
						if (!empty($arCatalogProduct))
						{
							$result = Catalog\Model\Product::update($ID, array('fields' => $arCatalogProduct));
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
								'filter' => array('=PRODUCT_ID' => $ID, '=IS_DEFAULT' => 'Y'),
							))->fetch();
							if (!empty($ratio))
								$intRatioID = (int)$ratio['ID'];
							if ($intRatioID > 0)
								$ratioResult = CCatalogMeasureRatio::update($intRatioID, array('RATIO' => $newValue));
							else
								$ratioResult = CCatalogMeasureRatio::add(array('PRODUCT_ID' => $ID, 'RATIO' => $newValue, 'IS_DEFAULT' => 'Y'));
						}
						unset($newValue);
					}
				}
			}
		}

		unset($ib);
	}

	if ($bCatalog)
	{
		if (
			$boolCatalogPrice
			&& isset($_POST['CATALOG_PRICE'])
			&& is_array($_POST['CATALOG_PRICE'])
			&& isset($_POST['CATALOG_CURRENCY'])
			&& is_array($_POST['CATALOG_CURRENCY'])
		)
		{
			$CATALOG_PRICE = $_POST["CATALOG_PRICE"];
			$CATALOG_CURRENCY = $_POST["CATALOG_CURRENCY"];
			$CATALOG_EXTRA = ($_POST["CATALOG_EXTRA"] ?? []);
			if (!is_array($CATALOG_EXTRA))
			{
				$CATALOG_EXTRA = [];
			}
			$CATALOG_PRICE_ID = $_POST["CATALOG_PRICE_ID"];
			$CATALOG_QUANTITY_FROM = ($_POST["CATALOG_QUANTITY_FROM"] ?? []);
			if (!is_array($CATALOG_QUANTITY_FROM))
			{
				$CATALOG_QUANTITY_FROM = [];
			}
			$CATALOG_QUANTITY_TO = ($_POST["CATALOG_QUANTITY_TO"] ?? []);
			if (!is_array($CATALOG_QUANTITY_TO))
			{
				$CATALOG_QUANTITY_TO = [];
			}
			$CATALOG_PRICE_old = $_POST["CATALOG_old_PRICE"];
			$CATALOG_CURRENCY_old = $_POST["CATALOG_old_CURRENCY"];

			$arCatExtraUp = array();
			$db_extras = CExtra::GetList(array("ID" => "ASC"));
			while ($extras = $db_extras->Fetch())
				$arCatExtraUp[$extras["ID"]] = $extras["PERCENTAGE"];

			$checkBasePriceTypeId = Catalog\GroupTable::getBasePriceTypeId();
			$arCatalogGroupList = Catalog\GroupTable::getTypeList();
			foreach($CATALOG_PRICE as $elID => $arPrice)
			{
				if (!is_array($arPrice))
				{
					continue;
				}
				if (!(isset($CATALOG_CURRENCY[$elID]) && is_array($CATALOG_CURRENCY[$elID])))
				{
					continue;
				}
				if (!(
					CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $elID, "element_edit")
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $elID, "element_edit_price")
				))
				{
					continue;
				}

				$bError = false;

				if ($checkBasePriceTypeId !== null && $strSaveWithoutPrice !== 'Y')
				{
					if (isset($arPrice[$checkBasePriceTypeId]))
					{
						if ((float)$arPrice[$checkBasePriceTypeId] < 0)
						{
							$bError = true;
							$lAdmin->AddGroupError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
						}
					}
					else
					{
						$quantityFrom = null;
						if (
							isset($CATALOG_QUANTITY_FROM[$elID][$basePriceTypeId])
							&& is_string($CATALOG_QUANTITY_FROM[$elID][$basePriceTypeId])
							&& $CATALOG_QUANTITY_FROM[$elID][$basePriceTypeId] !== ''
						)
						{
							$quantityFrom = (int)$CATALOG_QUANTITY_FROM[$elID][$basePriceTypeId];
							if ($quantityFrom <= 0)
							{
								$quantityFrom = null;
							}
						}
						$quantityTo = null;
						if (
							isset($CATALOG_QUANTITY_TO[$elID][$basePriceTypeId])
							&& is_string($CATALOG_QUANTITY_TO[$elID][$basePriceTypeId])
							&& $CATALOG_QUANTITY_TO[$elID][$basePriceTypeId] !== ''
						)
						{
							$quantityTo = (int)$CATALOG_QUANTITY_TO[$elID][$basePriceTypeId];
							if ($quantityTo <= 0)
							{
								$quantityTo = null;
							}
						}

						$basePrice = Catalog\PriceTable::getRow([
							'select' => [
								'ID',
								'PRICE',
							],
							'filter' => [
								'=PRODUCT_ID' => $elID,
								'=CATALOG_GROUP_ID' => $basePriceTypeId,
								'=QUANTITY_FROM' => $quantityFrom,
								'=QUANTITY_TO' => $quantityTo,
							],
						]);
						if ($basePrice === null || (float)$basePrice['PRICE'] < 0)
						{
							$bError = true;
							$lAdmin->AddGroupError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
						}
					}
				}

				if($bError)
					continue;

				$arCurrency = $CATALOG_CURRENCY[$elID];

				if (!empty($arCatalogGroupList))
				{
					foreach ($arCatalogGroupList as $arCatalogGroup)
					{
						$priceTypeId = $arCatalogGroup['ID'];
						if (!isset(
							$arPrice[$priceTypeId],
							$arCurrency[$priceTypeId],
							$CATALOG_PRICE_old[$elID][$priceTypeId],
							$CATALOG_CURRENCY_old[$elID][$priceTypeId]
						))
						{
							continue;
						}

						if (
							$arPrice[$priceTypeId] != $CATALOG_PRICE_old[$elID][$priceTypeId]
							|| $arCurrency[$priceTypeId] != $CATALOG_CURRENCY_old[$elID][$priceTypeId]
						)
						{
							if ($arCatalogGroup["BASE"] == 'Y') // if base price check extra for other prices
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
								if($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
									CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
								elseif((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
									CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
								elseif($arFields["PRICE"] >= 0)
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
								while($ar_res = $db_res->Fetch())
								{
									$arFields = array(
										"PRICE" => $arPrice[$arCatalogGroup["ID"]]*(1+$arCatExtraUp[$ar_res["EXTRA_ID"]]/100),
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
									CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
								elseif($arFields["PRICE"] >= 0)
									CPrice::Add($arFields);
							}
						}
					}
					unset($arCatalogGroup);
				}

				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $elID);
				$ipropValues->clearValues();
				\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $elID);
			}
			unset($arCatalogGroupList);
		}

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
			$arID = array();
			$rsData = CIBlockElement::GetList($arOrder, $arFilter, false, false, array('ID'));
			while ($arRes = $rsData->Fetch())
			{
				$arID[] = $arRes['ID'];
			}
			unset($arRes, $rsData);
		}

		if ($bCatalog)
		{
			Catalog\Product\Sku::enableDeferredCalculation();
		}

		$obE = new CIBlockElement();

		foreach ($arID as $ID)
		{
			$ID = (int)$ID;
			if ($ID <= 0)
				continue;

			$arRes = CIBlockElement::GetByID($ID);
			$arRes = $arRes->Fetch();
			if (!$arRes)
				continue;

			$WF_ID = $ID;
			if ($bWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($ID);
				if ($WF_ID != $ID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if ($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $ID;
				}

				if ($arRes["LOCK_STATUS"] == 'red' && !($actionId == ActionType::ELEMENT_UNLOCK && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR1")." (ID:".$ID.")", $ID);
					continue;
				}
			}
			elseif ($bBizproc)
			{
				if ($iblockDocument->IsDocumentLocked($ID, "") && !($actionId == ActionType::ELEMENT_UNLOCK && CBPDocument::IsAdmin()))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $ID)), $ID);
					continue;
				}
			}

			$bPermissions = false;
			//delete and modify can:
			if ($bWorkFlow)
			{
				//For delete action we have to check all statuses in element history
				$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission(
					$arRes["WF_STATUS_ID"],
					$actionId == ActionType::DELETE ? $ID : false
				);
				if ($STATUS_PERMISSION >= 2)
					$bPermissions = true;
			}
			elseif ($bBizproc)
			{
				$bCanWrite = $iblockDocument->CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$currentUser['ID'],
					$ID,
					array(
						"IBlockId" => $IBLOCK_ID,
						'IBlockRightsMode' => $arIBlock['RIGHTS_MODE'],
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
				$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
				continue;
			}

			switch ($actionId)
			{
				case ActionType::DELETE:
					if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_delete"))
					{
						@set_time_limit(0);
						$DB->StartTransaction();
						$APPLICATION->ResetException();
						if (!CIBlockElement::Delete($ID))
						{
							$DB->Rollback();
							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
							else
								$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR"), $ID);
						}
						else
						{
							$DB->Commit();
						}
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR")." [".$ID."]", $ID);
					}
					break;
				case ActionType::ACTIVATE:
				case ActionType::DEACTIVATE:
					if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
					{
						$obE->LAST_ERROR = '';
						$arFields = array("ACTIVE" => ($actionId == ActionType::ACTIVATE ? "Y" : "N"));
						if ($changeUserByActive)
						{
							$arFields['MODIFIED_BY'] = $currentUser['ID'];
						}
						if (!$obE->Update($ID, $arFields, true))
							$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$obE->LAST_ERROR, $ID);
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					}
					break;
				case ActionType::MOVE_TO_SECTION:
				case ActionType::ADD_TO_SECTION:
					if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
					{
						$new_section = (int)$actionParams['SECTION_ID'];
						if ($new_section >= 0)
						{
							if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $new_section, "section_element_bind"))
							{
								$obE->LAST_ERROR = '';

								$arSections = array($new_section);
								if ($actionId == ActionType::ADD_TO_SECTION)
								{
									$rsSections = $obE->GetElementGroups($ID, true, array('ID', 'IBLOCK_ELEMENT_ID'));
									while ($ar = $rsSections->Fetch())
									{
										$arSections[] = $ar["ID"];
									}
								}

								$arFields = array(
									"IBLOCK_SECTION" => $arSections,
								);
								if ($actionId == ActionType::MOVE_TO_SECTION)
								{
									$arFields["IBLOCK_SECTION_ID"] = $new_section;
								}

								if (!$obE->Update($ID, $arFields))
									$lAdmin->AddGroupError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_TEXT#" => $obE->LAST_ERROR)), $ID);
							}
							else
							{
								$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
							}
						}
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					}
					break;
				case ActionType::ELEMENT_WORKFLOW_STATUS:
					if ($bWorkFlow)
					{
						$new_status = (int)$actionParams['WF_STATUS_ID'];
						if (
							$new_status > 0
						)
						{
							if (CIBlockElement::WF_GetStatusPermission($new_status) > 0
								|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_any_wf_status"))
							{
								if ($arRes["WF_STATUS_ID"] != $new_status)
								{
									$obE->LAST_ERROR = '';
									$res = $obE->Update($ID, array(
										"WF_STATUS_ID" => $new_status,
										"MODIFIED_BY" => $currentUser['ID'],
									), true);
									if (!$res)
										$lAdmin->AddGroupError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_TEXT#" => $obE->LAST_ERROR)), $ID);
								}
							}
							else
							{
								$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
							}
						}
					}
					break;
				case ActionType::ELEMENT_LOCK:
					$elementAccess = true;
					if ($bWorkFlow && !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
						$elementAccess = false;
					}
					if ($elementAccess)
						CIBlockElement::WF_Lock($ID);
					unset($elementAccess);
					break;
				case ActionType::ELEMENT_UNLOCK:
					$elementAccess = true;
					if ($bWorkFlow && !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
						$elementAccess = false;
					}
					if ($elementAccess)
					{
						if ($bBizproc)
							call_user_func(array(ENTITY, "UnlockDocument"), $ID, "");
						else
							CIBlockElement::WF_UnLock($ID);
					}
					unset($elementAccess);
					break;
				case ActionType::CODE_TRANSLIT:
					if ($useElementTranslit && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, 'element_edit'))
					{
						$iterator = Iblock\ElementTable::getList(array(
							'select' => array('ID', 'NAME'),
							'filter' => array('=ID' => $ID)
						));
						$current = $iterator->fetch();
						$arFields = array(
							'CODE' => CUtil::translit(
								$current['NAME'],
								LANGUAGE_ID,
								$elementTranslitSettings
							)
						);
						$obE->LAST_ERROR = '';
						if (!$obE->Update($ID, $arFields, false, false))
							$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$obE->LAST_ERROR, $ID);
						unset($arFields);
						unset($current);
						unset($iterator);
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					}
					break;
				case ActionType::CLEAR_COUNTER:
					if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
					{
						$obE->LAST_ERROR = '';
						$arFields = array('SHOW_COUNTER' => false, 'SHOW_COUNTER_START' => false);
						if (!$obE->Update($ID, $arFields, false, false))
							$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$obE->LAST_ERROR, $ID);
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
					}
					break;
			}

			if ($bCatalog)
			{
				switch ($actionId)
				{
					case Catalog\Grid\ProductAction::CHANGE_PRICE:
						if ($boolCatalogPrice)
						{
							if (
								CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit")
								&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_price")
							)
							{
								$elementsList['ELEMENTS'][$ID] = $ID;
							}
							else
							{
								$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
							}
						}
						break;
					case Catalog\Grid\ProductAction::SET_FIELD:
					case Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE:
					case Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT:
						if (
							$boolCatalogProductEdit
							&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit")
							&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_price")
						)
						{
							$elementsList['ELEMENTS'][$ID] = $ID;
						}
						else
						{
							$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR_ACCESS", array("#ID#" => $ID)), $ID);
						}
						break;
				}
			}
		}

		unset($obE);

		if (
			$bCatalog
			&& !empty($elementsList['ELEMENTS'])
		)
		{
			switch ($actionId)
			{
				case Catalog\Grid\ProductAction::CHANGE_PRICE:
					$changePrice = new Catalog\Helpers\Admin\IblockPriceChanger($actionParams, $IBLOCK_ID);
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
						$IBLOCK_ID,
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
				case Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE:
					$labelStart = !empty($elementsList['ELEMENTS'])
						? 'withData'
						: 'emptyData'
					;
					AddEventToStatFile(
						'catalog',
						Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE,
						'start',
						$labelStart
					);
					$labelResult = 'fail';
					$result = Catalog\Grid\ProductAction::convertToServiceElementList(
						$IBLOCK_ID,
						$elementsList['ELEMENTS']
					);
					if (!$result->isSuccess())
					{
						foreach ($result->getErrors() as $error)
						{
							$lAdmin->AddGroupError($error->getMessage(), $error->getCode());
						}
						unset($error);
					}
					$resultData = $result->getData();
					if (isset($resultData['CONVERT_COMPLETE']))
					{
						$labelResult = 'success';
					}
					unset($result);
					AddEventToStatFile(
						'catalog',
						Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE,
						'finish',
						$labelResult
					);
					break;
				case Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT:
					$labelStart = !empty($elementsList['ELEMENTS'])
						? 'withData'
						: 'emptyData'
					;
					AddEventToStatFile(
						'catalog',
						Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT,
						'start',
						$labelStart
					);
					$labelResult = 'fail';
					$result = Catalog\Grid\ProductAction::convertToProductElementList(
						$IBLOCK_ID,
						$elementsList['ELEMENTS']
					);
					if (!$result->isSuccess())
					{
						foreach ($result->getErrors() as $error)
						{
							$lAdmin->AddGroupError($error->getMessage(), $error->getCode());
						}
						unset($error);
					}
					$resultData = $result->getData();
					if (isset($resultData['CONVERT_COMPLETE']))
					{
						$labelResult = 'success';
					}
					unset($result);
					AddEventToStatFile(
						'catalog',
						Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT,
						'finish',
						$labelResult
					);
					break;
			}
		}

		if ($bCatalog)
		{
			Catalog\Product\Sku::disableDeferredCalculation();
			Catalog\Product\Sku::calculate();
			CIBlock::clearIblockTagCache($IBLOCK_ID);
		}

		unset($elementsList);
	}
	unset($actionParams);
	unset($actionId);

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}

	$returnUrl = trim((string)$request->get('return_url'));
	if ($returnUrl !== '')
	{
		LocalRedirect($returnUrl);
	}
}
CJSCore::Init(array('date'));

if ($bExcel)
{
	$arNavParams = false;
}
else
{
	//TODO:: remove this hack after refactoring CAdminResult::GetNavSize
	$navResult = new CAdminUiResult(null, '');
	$arNavParams = array("nPageSize"=>$navResult->GetNavSize($sTableID));
	unset($navResult);
}

$rsData = CIBlockElement::GetList(
	$arOrder,
	$arFilter,
	false,
	$arNavParams,
	array('ID', 'IBLOCK_ID')
);
$rsData->SetTableID($sTableID);

$rawRows = [];
$elementIds = [];

$rsData = new CAdminUiResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->SetNavigationParams($rsData, array());

$arRows = array();
$productShowPrices = [];
$productEditPrices = [];

$bSearch = Loader::includeModule('search');

function GetElementName($ID)
{
	$ID = (int)$ID;
	if ($ID <= 0)
		return '';
	static $cache = array();
	if(!isset($cache[$ID]))
	{
		$rsElement = CIBlockElement::GetList(
			array(),
			array("ID"=>$ID, "SHOW_HISTORY"=>"Y"),
			false,
			false,
			array("ID","IBLOCK_ID","NAME")
		);
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

while($row = $rsData->Fetch())
{
	$rawRows[$row['ID']] = $row;
	$elementIds[] = $row['ID'];
}
$selectedCount = $rsData->SelectedRowsCount();
unset($rsData, $row);

$skuFields = [
	'VAT_ID',
	'CATALOG_MEASURE_RATIO',
	'CATALOG_MEASURE',
	'CATALOG_VAT_INCLUDED',
	'CATALOG_PURCHASING_PRICE',
	'CATALOG_PURCHASING_CURRENCY',
	'CATALOG_QUANTITY_TRACE',
	'CATALOG_QUANTITY',
	'CATALOG_QUANTITY_RESERVED',
	'CATALOG_WIDTH',
	'CATALOG_LENGTH',
	'CATALOG_WEIGHT',
	'CATALOG_HEIGHT',
	'CAN_BUY_ZERO',
	'CATALOG_BAR_CODE',
];
foreach ($priceTypeList as $priceType)
{
	$skuFields[] = 'CATALOG_GROUP_'.$priceType['ID'];
}

if ($pageConfig['USE_NEW_CARD'] && $moreProtoPropertyId !== null)
{
	$skuFields[] = 'MORE_PHOTO';
}

$productSkuTree = [];
$selectedSkuMap = [];

/** @var \Bitrix\Catalog\Component\SkuTree $skuTree */
$skuTree = null;
if (
	$boolSKU
	&& $pageConfig['USE_NEW_CARD']
)
{
	$skuTree = Catalog\v2\IoC\ServiceContainer::make('sku.tree', ['iblockId' => $IBLOCK_ID]);
}

if (!empty($elementIds))
{
	sort($elementIds);
	foreach (array_chunk($elementIds, 500) as $pageIds)
	{
		$elementFilter = [
			'IBLOCK_ID' => $IBLOCK_ID,
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
			$rawRows[$row['ID']] += $row;
		}

		if ($boolSKU && $skuTree)
		{
			$pageSkuIds = [];
			$skuTreeFormatted = [];
			$pageProductSkuTree = $skuTree->load($pageIds);
			foreach ($pageProductSkuTree as $productId => $item)
			{
				$offers = [];
				if (!empty($item['OFFERS']))
				{
					$offerIds = array_column($item['OFFERS'], 'ID');

					if ($selectedSkuId && in_array($selectedSkuId, $offerIds, true))
					{
						$pageSkuIds[$productId] = $selectedSkuId;
					}
					else
					{
						$pageSkuIds[$productId] = reset($offerIds);
					}

					foreach ($item['OFFERS'] as $offer)
					{
						$offers[] = array_intersect_key(
							$offer,
							array_flip(['TREE', 'ID'])
						);
					}
				}

				$skuTreeFormatted[$productId] = [
					'EXISTING_VALUES' => $item['EXISTING_VALUES'],
					'OFFERS' => $offers,
					'IBLOCK_ID' => $IBLOCK_ID,
				];
			}
			$selectedSkuMap += $pageSkuIds;

			if ($isUsedNewProductField)
			{
				$productSkuTree += $skuTreeFormatted;
				if (!empty($pageSkuIds))
				{
					$fieldsToSelect = $skuFields;
					foreach ($fieldsToSelect as $index => $field)
					{
						if (isset($transferHeaders[$field]))
						{
							$fieldsToSelect[$index] = $transferHeaders[$field];
						}
					}

					$fieldsToSelect = array_intersect($arSelectedFields, $fieldsToSelect);
					$fieldsToSelect[] = 'ID';
					$elementFilter = [
						'IBLOCK_ID' => $arCatalog["IBLOCK_ID"],
						'ID' => array_values($pageSkuIds),
						'CHECK_PERMISSIONS' => 'N',
						'SHOW_NEW' => 'Y'
					];
					$iterator = \CIBlockElement::GetList(array(), $elementFilter, false, false, $fieldsToSelect);
					while ($row = $iterator->Fetch())
					{
						$skuId = (int)$row['ID'];
						unset($row['ID']);

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

						$productId = array_search($skuId, $pageSkuIds, true);
						$rawRows[$productId] = array_merge($rawRows[$productId], $row);
					}
				}
			}
		}
	}
	unset($row);
	unset($iterator);
	unset($elementFilter);
	unset($pageIds);

	// region Replacing the total quantity in grid with the amount of products from available stores (store permissions by current user)
	if (
		$useSummaryStoreAmount
		&& ($arSelectedFieldsMap['CATALOG_QUANTITY'] || $arSelectedFieldsMap['CATALOG_QUANTITY_RESERVED'])
	)
	{
		$productIdList = [];
		foreach ($elementIds as $productId)
		{
			// Only for simple products and offers
			$productType = $rawRows[$productId]['CATALOG_TYPE'];
			if (
				$productType === Catalog\ProductTable::TYPE_SET
				|| $productType === Catalog\ProductTable::TYPE_EMPTY_SKU
			)
			{
				continue;
			}
			$productIdList[$productId] = $selectedSkuMap[$productId] ?? $productId;
		}

		if (!empty($productIdList))
		{
			$quantityList = \CCatalogAdminTools::getSummaryStoreAmountByPermissions($productIdList);
			if (!empty($quantityList))
			{
				foreach ($productIdList as $productId => $resultId)
				{
					if (!isset($quantityList[$resultId]))
					{
						continue;
					}
					$rawRows[$productId]['CATALOG_QUANTITY'] = $quantityList[$resultId]['QUANTITY'];
					$rawRows[$productId]['CATALOG_QUANTITY_RESERVED'] = $quantityList[$resultId]['QUANTITY_RESERVED'];
				}
				unset($productId);
				unset($resultId);
			}
			unset($quantityList);
		}
		unset($productIdList);
	}
	// endregion
}

$elementUrlParams = array(
	'find_section_section' => (int)$find_section_section,
	'WF' => 'Y'
);

foreach (array_keys($rawRows) as $rowId)
{
	$arRes = $rawRows[$rowId];
	unset($rawRows[$rowId]);
	if (!isset($arRes['ID']))
	{
		continue;
	}
	$itemId = (int)$arRes['ID'];
	if ($itemId <= 0)
	{
		continue;
	}

	$arRes_orig = $arRes;
	// in workflow mode show latest changes
	if($bWorkFlow)
	{
		$LAST_ID = CIBlockElement::WF_GetLast($arRes['ID']);
		if($LAST_ID!=$arRes['ID'])
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
	elseif($bBizproc)
	{
		$lockStatus = $iblockDocument->IsDocumentLocked($itemId, "") ? "red" : "green";
	}
	else
	{
		$lockStatus = "";
	}
	if ($bCatalog)
	{
		$arRes['CATALOG_TYPE'] = (int)$arRes['CATALOG_TYPE'];

		if (isset($clearedGridFields[$arRes['CATALOG_TYPE']]))
		{
			foreach ($clearedGridFields[$arRes['CATALOG_TYPE']] as $fieldName)
			{
				if (isset($arRes[$fieldName]))
				{
					$arRes[$fieldName] = '';
				}
			}
		}

		if (isset($arRes['CATALOG_MEASURE']))
		{
			$arRes['CATALOG_MEASURE'] = (int)$arRes['CATALOG_MEASURE'];
			if ($arRes['CATALOG_MEASURE'] <= 0)
				$arRes['CATALOG_MEASURE'] = '';
		}
	}

	$arRes['lockStatus'] = $lockStatus;
	$arRes["orig"] = $arRes_orig;

	$arRes["edit_url"] = $urlBuilder->getElementDetailUrl($arRes_orig['ID'], $elementUrlParams);
	$arRows[$itemId] = $row = $lAdmin->AddRow($itemId, $arRes, $arRes["edit_url"], GetMessage("IBEL_A_EDIT"));
	$row->setConfig([
		CAdminUiListRow::DEFAULT_ACTION_TYPE_FIELD => $pageConfig['DEFAULT_ACTION_TYPE'],
		CAdminUiListRow::SKIP_URL_MODIFY_FIELD => $pageConfig['SKIP_URL_MODIFICATION'],
	]);

	if (isset($arSelectedFieldsMap['VAT_ID']))
	{
		if ($row->arRes['VAT_ID'] === null)
		{
			$row->arRes['VAT_ID'] = '0';
		}
	}

	$row->AddViewField("ID", '<a href="'.$arRes["edit_url"].'" title="'.GetMessage("IBEL_A_EDIT_TITLE").'">'.$itemId.'</a>');

	if(isset($arRes["LOCKED_USER_NAME"]) && $arRes["LOCKED_USER_NAME"] != '')
	{
		$arRes["LOCKED_USER_NAME"] = htmlspecialcharsEx($arRes["LOCKED_USER_NAME"]);
		$userName = (!$canViewUser
			? '['.$arRes["WF_LOCKED_BY"].']&nbsp;'.$arRes["LOCKED_USER_NAME"]
			: '[<a href="'.$selfFolderUrl.'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["WF_LOCKED_BY"].'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$arRes["WF_LOCKED_BY"].'</a>]&nbsp;'.$arRes["LOCKED_USER_NAME"]
		);
		$row->AddViewField("LOCKED_USER_NAME", $userName);
		unset($userName);
	}
	if(isset($arRes["USER_NAME"]) && $arRes["USER_NAME"] != '')
	{
		$arRes["USER_NAME"] = htmlspecialcharsEx($arRes["USER_NAME"]);
		$userName = (!$canViewUser
			? '['.$arRes["MODIFIED_BY"].']&nbsp;'.$arRes["USER_NAME"]
			: '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["MODIFIED_BY"].'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$arRes["MODIFIED_BY"].'</a>]&nbsp;'.$arRes["USER_NAME"]
		);
		$row->AddViewField("USER_NAME", $userName);
		unset($userName);
	}
	if(isset($arRes["CREATED_USER_NAME"]) && $arRes["CREATED_USER_NAME"] != '')
	{
		$arRes["CREATED_USER_NAME"] = htmlspecialcharsEx($arRes["CREATED_USER_NAME"]);
		$userName = (!$canViewUser
			? '['.$arRes["CREATED_BY"].']&nbsp;'.$arRes["CREATED_USER_NAME"]
			: '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["CREATED_BY"].'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$arRes["CREATED_BY"].'</a>]&nbsp;'.$arRes["CREATED_USER_NAME"]
		);
		$row->AddViewField("CREATED_USER_NAME", $userName);
		unset($userName);
	}

	if($bWorkFlow || $bBizproc)
	{
		$lamp = '<span class="adm-lamp adm-lamp-in-list adm-lamp-'.$lockStatus.'"></span>';
		if($lockStatus=='red' && $arRes_orig['LOCKED_USER_NAME'] != '')
			$row->AddViewField("LOCK_STATUS", $lamp.htmlspecialcharsEx($arRes_orig['LOCKED_USER_NAME']));
		else
			$row->AddViewField("LOCK_STATUS", $lamp);
	}

	if($bBizproc)
		$row->AddCheckField("BP_PUBLISHED", false);

	$row->arRes['props'] = array();
	$arProperties = array();
	if (!empty($arSelectedProps))
	{
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $arRes['ID'], 'id', 'asc', array('ID' => $selectedPropertyIds));
		while($ar = $rsProperties->GetNext())
		{
			if(!isset($arProperties[$ar["ID"]]))
				$arProperties[$ar["ID"]] = array();
			$ar["PROPERTY_VALUE_ID"] = (int)$ar["PROPERTY_VALUE_ID"];
			$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar;
		}
		unset($ar);
		unset($rsProperties);
	}

	if ($bCatalog && $pageConfig['USE_NEW_CARD'] && isset($arSelectedFieldsMap['MORE_PHOTO']))
	{
		$skuId = $selectedSkuMap[$itemId] ?? $itemId;
		$repositoryFacade = ServiceContainer::getRepositoryFacade();
		if ($repositoryFacade)
		{
			if ($isUsedNewProductField)
			{
				$entity = $repositoryFacade->loadVariation($skuId);
			}
			else
			{
				$entity = $repositoryFacade->loadProduct($itemId);
			}

			if ($entity)
			{
				$imageInput = new ImageInput($entity);
				$field = $imageInput->getFormattedField();
				$row->AddViewField('MORE_PHOTO', $field['preview']);
				if (!$bReadOnly)
				{
					$row->AddEditField('MORE_PHOTO', $field['input']);
				}
			}
		}
	}

	foreach($arSelectedProps as $aProp)
	{
		$arViewHTML = array();
		$arEditHTML = array();
		$arUserType = $aProp['PROPERTY_USER_TYPE'];

		if (
			(
				$pageConfig['USE_NEW_CARD']
				&& $aProp['CODE'] === 'MORE_PHOTO'
			)
			&& $aProp['PROPERTY_TYPE'] === 'F'
			&& !$bExcel
		)
		{
			continue;
		}

		$last_property_id = false;
		foreach($arProperties[$aProp["ID"]] as $valueId => $prop)
		{
			$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.'][VALUE]';
			$DESCR_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.'][DESCRIPTION]';
			//View part
			if(isset($arUserType["GetAdminListViewHTML"]))
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
			elseif($prop['PROPERTY_TYPE']=='N')
				$arViewHTML[] = $bExcel && isset($_COOKIE[$dsc_cookie_name])? number_format($prop["VALUE"], 4, chr($_COOKIE[$dsc_cookie_name]), ''): $prop["VALUE"];
			elseif($prop['PROPERTY_TYPE']=='S')
				$arViewHTML[] = $prop["VALUE"];
			elseif($prop['PROPERTY_TYPE']=='L')
			{
				if ($prop['LIST_TYPE'] === PropertyTable::CHECKBOX && count($arSelect[$prop['ID']]) === 1)
				{
					if ($prop["VALUE_ENUM"] === 'Y')
					{
						$arViewHTML[] = GetMessage('IBLOCK_YES');
					}
					elseif ($prop["VALUE_ENUM"] === null)
					{
						$arViewHTML[] = GetMessage('IBLOCK_NO');
					}
					else
					{
						$arViewHTML[] = $prop["VALUE_ENUM"];
					}
				}
				else
				{
					$arViewHTML[] = $prop["VALUE_ENUM"];
				}
			}
			elseif($prop['PROPERTY_TYPE']=='F')
			{
				if ($bExcel)
				{
					$arFile = CFile::GetFileArray($prop["VALUE"]);
					if (is_array($arFile))
						$arViewHTML[] = CHTTP::URN2URI($arFile["SRC"]);
					else
						$arViewHTML[] = "";
				}
				else
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
			}
			elseif($prop['PROPERTY_TYPE']=='G')
			{
				if((int)$prop["VALUE"]>0)
				{
					$arSection = GetSectionName($prop["VALUE"]);
					if (!empty($arSection))
					{
						$arViewHTML[] = $arSection['NAME'].' '.
							($pageConfig['ALLOW_EXTERNAL_LINK']
								? '[<a href="'.
									htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminSectionEditLink(
										$arSection['IBLOCK_ID'], $arSection['ID'], array("replace_script_name" => true))).
									'" title="'.GetMessage("IBEL_A_SEC_EDIT").'">'.$arSection['ID'].'</a>]'
								: '['.$arSection['ID'].']'
							);
					}
				}
			}
			elseif($prop['PROPERTY_TYPE']=='E')
			{
				if($t = GetElementName($prop["VALUE"]))
				{
					$arViewHTML[] = $t['NAME'].' '.
						($pageConfig['ALLOW_EXTERNAL_LINK']
							? '[<a href="'.htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'], array(
								"find_section_section" => $find_section_section, 'WF' => 'Y', "replace_script_name" => true
								))).'" title="'.GetMessage("IBEL_A_EL_EDIT").'">'.$t['ID'].'</a>]'
							: '['.$t['ID'].']'
						);
				}
			}
			//Edit Part
			$bUserMultiple = $prop["MULTIPLE"] == "Y" && isset($arUserType["GetPropertyFieldHtmlMulty"]);
			if($bUserMultiple)
			{
				if($last_property_id != $prop["ID"])
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
			elseif(isset($arUserType["GetPropertyFieldHtml"]))
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
			elseif($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
			{
				if($prop["ROW_COUNT"] > 1)
					$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'">'.$prop["VALUE"].'</textarea>';
				else
					$html = '<input type="text" name="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="'.$prop["COL_COUNT"].'">';
				if($prop["WITH_DESCRIPTION"] == "Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").
						'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='L' && ($last_property_id!=$prop["ID"]))
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].'][]';
				$arValues = array();
				foreach($arProperties[$prop["ID"]] as $g_prop)
				{
					$g_prop = (int)$g_prop["VALUE"];
					if($g_prop > 0)
						$arValues[$g_prop] = $g_prop;
				}
				if($prop['LIST_TYPE']=='C')
				{
					if($prop['MULTIPLE'] == "Y" || count($arSelect[$prop['ID']]) == 1)
					{
						$html = '<input type="hidden" name="'.$VALUE_NAME.'" value="">';
						foreach($arSelect[$prop['ID']] as $value => $display)
						{
							$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$value.']';
							$html .= '<input type="checkbox" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value="'.$value.'"';
							if(isset($arValues[$value]))
								$html .= ' checked';
							if (count($arSelect[$prop['ID']]) === 1 && $display === 'Y')
							{
								$html .= '>&nbsp;<label for="id' . $uniq_id . '"></label><br>';
							}
							else
							{
								$html .= '>&nbsp;<label for="id' . $uniq_id . '">' . $display . '</label><br>';
							}
							$uniq_id++;
						}
					}
					else
					{
						$html = '<input type="radio" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value=""';
						if(empty($arValues))
							$html .= ' checked';
						$html .= '>&nbsp;<label for="id'.$uniq_id.'">'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</label><br>';
						$uniq_id++;
						foreach($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<input type="radio" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value="'.$value.'"';
							if(isset($arValues[$value]))
								$html .= ' checked';
							$html .= '>&nbsp;<label for="id'.$uniq_id.'">'.$display.'</label><br>';
							$uniq_id++;
						}
					}
				}
				else
				{
					$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
					$html .= '<option value=""'.(empty($arValues) ? ' selected': '').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
					foreach($arSelect[$prop['ID']] as $value => $display)
					{
						$html .= '<option value="'.$value.'"';
						if(isset($arValues[$value]))
							$html .= ' selected';
						$html .= '>'.$display.'</option>'."\n";
					}
					$html .= "</select>\n";
				}
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='F' && ($last_property_id != $prop["ID"]))
			{
				if($prop['MULTIPLE'] == "Y")
				{
					$inputName = array();
					foreach($arProperties[$prop["ID"]] as $g_prop)
					{
						$inputName['FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$g_prop['PROPERTY_VALUE_ID'].'][VALUE]'] = $g_prop["VALUE"];
					}
					if (class_exists('\Bitrix\Main\UI\FileInput', true))
					{
						$arEditHTML[] = \Bitrix\Main\UI\FileInput::createInstance(array(
								"name" => 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].'][n#IND#]',
								"description" => $prop["WITH_DESCRIPTION"]=="Y",
								"upload" => true,
								"medialib" => false,
								"fileDialog" => false,
								"cloud" => false,
								"delete" => true,
							))->show($inputName);
					}
					else
					{
						$arEditHTML[] = CFileInput::ShowMultiple($inputName, 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].'][n#IND#]', array(
							"IMAGE" => "Y",
							"PATH" => "Y",
							"FILE_SIZE" => "Y",
							"DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y",
							"MAX_SIZE" => $maxImageSize,
							"MIN_SIZE" => $minImageSize,
							), false, array(
								'upload' => true,
								'medialib' => false,
								'file_dialog' => false,
								'cloud' => false,
								'del' => true,
								'description' => $prop["WITH_DESCRIPTION"]=="Y",
							)
						);
					}
				}
				else
				{
					$arEditHTML[] = CFileInput::Show($VALUE_NAME, $prop["VALUE"], array(
						"IMAGE" => "Y",
						"PATH" => "Y",
						"FILE_SIZE" => "Y",
						"DIMENSIONS" => "Y",
						"IMAGE_POPUP" => "Y",
						"MAX_SIZE" => $maxImageSize,
						"MIN_SIZE" => $minImageSize,
						), array(
							'upload' => true,
							'medialib' => false,
							'file_dialog' => false,
							'cloud' => false,
							'del' => true,
							'description' => $prop["WITH_DESCRIPTION"]=="Y",
						)
					);
					$row->arRes["PROPERTY_".$prop["ID"]] = $prop["VALUE"];
				}
			}
			elseif(($prop['PROPERTY_TYPE']=='G'))
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.']';

				$searchParams = array(
					'IBLOCK_ID' => (string)$prop['LINK_IBLOCK_ID'],
					'n' => $VALUE_NAME,
					'tableId' => 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID']
				);
				if ($aProp["LINK_IBLOCK_ID"] > 0)
				{
					$searchParams['iblockfix'] = 'y';
				}
				$searchUrl = htmlspecialcharsbx($urlBuilder->getSectionSearchUrl($searchParams));

				$currentSectionValue = '';
				$currentSectionName = '';
				if ($t = GetSectionName($prop["VALUE"]))
				{
					$currentSectionValue = $prop["VALUE"];
					$currentSectionName = $t['NAME'];
				}
				$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$currentSectionValue.'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$currentSectionName.'</span>';
			}
			elseif($prop['PROPERTY_TYPE']=='E')
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$prop['ID'].']['.$valueId.']';

				$searchParams = array(
					'IBLOCK_ID' => (string)$prop['LINK_IBLOCK_ID'],
					'n' => $VALUE_NAME,
					'tableId' => 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID']
				);
				if ($prop["LINK_IBLOCK_ID"] > 0)
				{
					$searchParams['iblockfix'] = 'y';
				}
				$searchUrl = htmlspecialcharsbx($urlBuilder->getElementSearchUrl($searchParams));

				$currentElementValue = '';
				$currentElementName = '';
				if($t = GetElementName($prop["VALUE"]))
				{
					$currentElementValue = $prop["VALUE"];
					$currentElementName = $t['NAME'];
				}
				unset($t);
				$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$currentElementValue.'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$currentElementName.'</span>';
				unset($searchUrl);
				unset($searchParams);
			}
			$last_property_id = $prop['ID'];
		}
		$table_id = md5($itemId.':'.$aProp['ID']);
		if($aProp["MULTIPLE"] == "Y")
		{
			$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0][VALUE]';
			$DESCR_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0][DESCRIPTION]';
			if(isset($arUserType["GetPropertyFieldHtmlMulty"]))
			{
			}
			elseif(isset($arUserType["GetPropertyFieldHtml"]))
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
			elseif($aProp['PROPERTY_TYPE']=='N' || $aProp['PROPERTY_TYPE']=='S')
			{
				if($aProp["ROW_COUNT"] > 1)
					$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$aProp["COL_COUNT"].'" rows="'.$aProp["ROW_COUNT"].'"></textarea>';
				else
					$html = '<input type="text" name="'.$VALUE_NAME.'" value="" size="'.$aProp["COL_COUNT"].'">';
				if($aProp["WITH_DESCRIPTION"] == "Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
				$arEditHTML[] = $html;
			}
			elseif($aProp['PROPERTY_TYPE']=='F')
			{
			}
			elseif($aProp['PROPERTY_TYPE']=='G')
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0]';

				$searchParams = array(
					'IBLOCK_ID' => (string)$prop['LINK_IBLOCK_ID'],
					'n' => $VALUE_NAME,
					'tableId' => 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$aProp['ID'].'-'.$aProp['LINK_IBLOCK_ID']
				);
				if ($aProp["LINK_IBLOCK_ID"] > 0)
				{
					$searchParams['iblockfix'] = 'y';
				}
				$searchUrl = htmlspecialcharsbx($urlBuilder->getSectionSearchUrl($searchParams));
				$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
			}
			elseif($aProp['PROPERTY_TYPE']=='E')
			{
				$VALUE_NAME = 'FIELDS['.$itemId.'][PROPERTY_'.$aProp['ID'].'][n0]';

				$searchParams = array(
					'IBLOCK_ID' => (string)$prop['LINK_IBLOCK_ID'],
					'n' => $VALUE_NAME,
					'tableId' => 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$aProp['ID'].'-'.$aProp['LINK_IBLOCK_ID']
				);
				if ($aProp["LINK_IBLOCK_ID"] > 0)
				{
					$searchParams['iblockfix'] = 'y';
				}
				$searchUrl = htmlspecialcharsbx($urlBuilder->getElementSearchUrl($searchParams));
				$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
			}

			if(
				$aProp["PROPERTY_TYPE"] !== "L"
				&& $aProp["PROPERTY_TYPE"] !== "F"
				&& !$bUserMultiple
			)
				$arEditHTML[] = '<input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="BX.IBlock.Tools.addNewRow(\'tb'.$table_id.'\')">';
		}
		if (!empty($arViewHTML))
		{
			if ($aProp["PROPERTY_TYPE"] == "F")
			{
				$arEditHTML = [];
				$row->AddFileField("PROPERTY_".$aProp['ID'], [
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => $maxImageSize,
					"MIN_SIZE" => $minImageSize
				]);
				$row->AddViewField("PROPERTY_".$aProp['ID'], implode("", $arViewHTML));
			}
			else
			{
				$row->AddViewField("PROPERTY_".$aProp['ID'], implode(" / ", $arViewHTML));
			}
		}

		if(!empty($arEditHTML))
			$row->arRes['props']["PROPERTY_".$aProp['ID']] = array("table_id"=>$table_id, "html"=>$arEditHTML);
	}

	if ($bCatalog)
	{
		if (!empty($priceTypeIndex))
		{
			$row->arRes['PHANTOM_PRICE'] = 'N';
			if (
				!$showCatalogWithOffers
				&& (
					$arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SKU
					|| $arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU
				)
			)
			{
				$row->arRes['PHANTOM_PRICE'] = 'Y';
			}

			if ($arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_EMPTY_SKU)
				$productShowPrices[$itemId] = true;

			if (
				$boolCatalogPrice
				&& (
					$row->arRes['PHANTOM_PRICE'] == 'N'
					|| (
						($arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SKU
							|| $arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU
						) && $showCatalogWithOffers
					)
				)
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
	}

	if ($bBizproc)
	{
		$arDocumentStates = CBPDocument::GetDocumentStates(
			array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID),
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
					$arStr[$vv["TEMPLATE_ID"]] .= GetMessage("IBEL_A_BP_TASK").":<br /><a href=\"bizproc_task.php?id=".$arTask["ID"]."\" title=\"".$arTask["DESCRIPTION"]."\">".$arTask["NAME"]."</a><br /><br />";
				}
			}
		}

		$str = "";
		foreach ($arStr as $k => $v)
		{
			$row->AddViewField("WF_".$k, $v);
			$str .= "<b>".($arStr1[$k] <> '' ? $arStr1[$k] : GetMessage("IBEL_A_BP_PROC"))."</b>:<br />".$v."<br />";
		}

		$row->AddViewField("BIZPROC", $str);
	}
}
unset($row);

$boolIBlockElementAdd = CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_element_bind");
$iblockElementAdd = $boolIBlockElementAdd;
if (!empty($productLimits))
{
	$boolIBlockElementAdd = false;
}

$arElementOps = CIBlockElementRights::UserHasRightTo(
	$IBLOCK_ID,
	array_keys($arRows),
	"",
	CIBlockRights::RETURN_OPERATIONS
);
$quantityTraceStatus = array();
$canBuyZeroStatus = array();
if ($bCatalog && !empty($arRows))
{
	$defaultQuantityTrace = (Main\Config\Option::get("catalog", "default_quantity_trace") == 'Y'
		? GetMessage("IBEL_YES_VALUE")
		: GetMessage("IBEL_NO_VALUE")
	);
	$quantityTraceStatus = array(
		Catalog\ProductTable::STATUS_DEFAULT => GetMessage("IBEL_DEFAULT_VALUE")." (".$defaultQuantityTrace.")",
		Catalog\ProductTable::STATUS_YES => GetMessage("IBEL_YES_VALUE"),
		Catalog\ProductTable::STATUS_NO => GetMessage("IBEL_NO_VALUE"),
	);
	$defaultCanBuyZero = (Main\Config\Option::get('catalog', 'default_can_buy_zero') == 'Y'
		? GetMessage("IBEL_YES_VALUE")
		: GetMessage("IBEL_NO_VALUE")
	);
	$canBuyZeroStatus = array(
		Catalog\ProductTable::STATUS_DEFAULT => GetMessage("IBEL_DEFAULT_VALUE")." (".$defaultCanBuyZero.")",
		Catalog\ProductTable::STATUS_YES => GetMessage("IBEL_YES_VALUE"),
		Catalog\ProductTable::STATUS_NO => GetMessage("IBEL_NO_VALUE")
	);
	$arRowKeys = array_keys($arRows);
	if ($useStoreControl && isset($arSelectedFieldsMap["CATALOG_BAR_CODE"]))
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
					$arRows[$product["ID"]]->arRes["CATALOG_BAR_CODE"] = GetMessage("IBEL_CATALOG_BAR_CODE_MULTI");
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
		$productMap = [];

		foreach ($arRowKeys as $index)
		{
			$arRows[$index]->arRes['CATALOG_MEASURE_RATIO'] = ' ';
			$productMap[$selectedSkuMap[$index] ?? $index] = $index;
		}
		unset($index);

		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('ID', 'PRODUCT_ID', 'RATIO'),
			'filter' => array('@PRODUCT_ID' => array_keys($productMap), '=IS_DEFAULT' => 'Y')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PRODUCT_ID'];
			$rowId = $productMap[$id];

			if (isset($arRows[$rowId]))
			{
				$arRows[$rowId]->arRes['CATALOG_MEASURE_RATIO'] = $row['RATIO'];
			}
		}
		unset($id, $row, $iterator, $productMap);
	}

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
			$pageIdsWithOffers = array_values(array_intersect_key($selectedSkuMap, array_flip($pageIds)));
			$pageIds = array_diff($pageIds, array_flip($selectedSkuMap));
			$iterator = Catalog\PriceTable::getList(array(
				'select' => array('ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'EXTRA_ID', 'QUANTITY_FROM', 'QUANTITY_TO'),
				'filter' => array(
					'@CATALOG_GROUP_ID' => $priceTypeIndex,
					'@PRODUCT_ID' => array_merge($pageIds, $pageIdsWithOffers),
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
				$skuId = $productId;

				// it's parent of selected offer
				if (isset($selectedSkuMap[$productId]))
				{
					continue;
				}

				$parentId = array_search($productId, $selectedSkuMap, true);
				// it's selected offer, otherwise it's simple product
				if ($parentId !== false)
				{
					$skuId = $productId;
					$productId = $parentId;
				}

				$priceType = (int)$row['CATALOG_GROUP_ID'];
				$extraId = (int)$row['EXTRA_ID'];

				$price = \CCurrencyLang::CurrencyFormat($row['PRICE'], $row['CURRENCY'], true);

				if ($arRows[$productId]->arRes['PHANTOM_PRICE'] == 'N')
				{
					if ($extraId > 0)
					{
						$price .= ' <span title="'.
							htmlspecialcharsbx(GetMessage(
								'IBEL_CATALOG_EXTRA_DESCRIPTION',
								array('#VALUE#' => $arCatExtra[$extraId]['NAME'])
							)).
							'">(+'.$arCatExtra[$extraId]['PERCENTAGE'].'%)</span>';
					}
				}
				else
				{
					$price = htmlspecialcharsEx(GetMessage(
						'IBEL_CATALOG_SKU_PRICE',
						array('#PRICE#' => $price)
					));
				}

				$arRows[$productId]->AddViewField('CATALOG_GROUP_'.$priceType, $price);
				$arRows[$productId]->arRes['CATALOG_GROUP_'.$priceType] = $price;
				unset($price);
				if (isset($productEditPrices[$productId]))
				{
					unset($emptyPrices[$productId][$priceType]);
					$hiddenFields = [
						[
							'NAME' => 'CATALOG_old_PRICE['.$skuId.']['.$priceType.']',
							'VALUE' => htmlspecialcharsbx($row['PRICE']),
						],
						[
							'NAME' => 'CATALOG_old_CURRENCY['.$skuId.']['.$priceType.']',
							'VALUE' => htmlspecialcharsbx($row['CURRENCY']),
						],
						[
							'NAME' => 'CATALOG_PRICE_ID['.$skuId.']['.$priceType.']',
							'VALUE' => (int)($row['ID']),
						],
						[
							'NAME' => 'CATALOG_QUANTITY_FROM['.$skuId.']['.$priceType.']',
							'VALUE' => htmlspecialcharsbx($row['QUANTITY_FROM']),
						],
						[
							'NAME' => 'CATALOG_QUANTITY_TO['.$skuId.']['.$priceType.']',
							'VALUE' => htmlspecialcharsbx($row['QUANTITY_TO']),
						],
					];

					if ($extraId > 0)
					{
						$hiddenFields[] = [
							'NAME' => 'CATALOG_EXTRA['.$skuId.']['.$priceType.']',
							'VALUE' => htmlspecialcharsbx($row['EXTRA_ID']),
						];
					}

					$currency = !empty($row['CURRENCY']) ? $row['CURRENCY'] : Currency\CurrencyManager::getBaseCurrency();

					$arRows[$productId]->AddMoneyField('CATALOG_GROUP_'.$priceType,
						[
							'CURRENCY_LIST' => $arCurrencyList,
							'HTML_ENTITY' => true,
							'ATTRIBUTES' => [
								'data-base' => ($priceType == $basePriceTypeId),
								'data-product' => $skuId,
							],
							'PRICE' => [
								'NAME' => 'CATALOG_PRICE['.$skuId.']['.$priceType.']',
								'VALUE' => htmlspecialcharsbx($row['PRICE']),
								'DISABLED' => ($extraId > 0),
							],
							'CURRENCY' => [
								'NAME' => 'CATALOG_CURRENCY['.$skuId.']['.$priceType.']',
								'VALUE' => htmlspecialcharsbx($currency),
								'DISABLED' => ($extraId > 0),
							],
							'HIDDEN' => $hiddenFields
						]
					);
					unset($hiddenFields, $currency);
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
					$skuId = $selectedSkuMap[$productId] ?? $productId;
					$hiddenFields = [
						[
							'NAME' => 'CATALOG_old_PRICE['.$skuId.']['.$priceType.']',
							'VALUE' => '',
						],
						[
							'NAME' => 'CATALOG_old_CURRENCY['.$skuId.']['.$priceType.']',
							'VALUE' => '',
						],
						[
							'NAME' => 'CATALOG_PRICE_ID['.$skuId.']['.$priceType.']',
							'VALUE' => '',
						],
						[
							'NAME' => 'CATALOG_QUANTITY_FROM['.$skuId.']['.$priceType.']',
							'VALUE' => '',
						],
						[
							'NAME' => 'CATALOG_QUANTITY_TO['.$skuId.']['.$priceType.']',
							'VALUE' => '',
						],
					];

					$arRows[$productId]->AddMoneyField('CATALOG_GROUP_'.$priceType,
						[
							'CURRENCY_LIST' => $arCurrencyList,
							'HTML_ENTITY' => true,
							'ATTRIBUTES' => [
								'data-base' => ($priceType == $basePriceTypeId),
								'data-product' => $skuId,
							],
							'PRICE' => [
								'NAME' => 'CATALOG_PRICE['.$skuId.']['.$priceType.']',
								'VALUE' => '',
							],
							'CURRENCY' => [
								'NAME' => 'CATALOG_CURRENCY['.$skuId.']['.$priceType.']',
								'VALUE' => htmlspecialcharsbx(Currency\CurrencyManager::getBaseCurrency()),
							],
							'HIDDEN' => $hiddenFields
						]
					);
				}
				unset($priceType);
			}
			unset($productId, $skuId);
		}
		unset($emptyPrices);
	}
}

if ($arIBTYPE['SECTIONS'] == 'Y' && isset($arSelectedFieldsMap['SECTIONS']) && !empty($arRows))
{
	$sectionList = [];
	$itemSections = [];
	$sectionToItem = [];
	$sectionIds = [];

	$elementIds = array_keys($arRows);
	sort($elementIds);
	foreach (array_chunk($elementIds, 500) as $pageIds)
	{
		$iterator = Iblock\SectionElementTable::getList([
			'select' => ['IBLOCK_SECTION_ID', 'IBLOCK_ELEMENT_ID'],
			'filter' => ['@IBLOCK_ELEMENT_ID' => $pageIds, '=ADDITIONAL_PROPERTY_ID' => null]
		]);
		while ($row = $iterator->fetch())
		{
			$itemSectionId = (int)$row['IBLOCK_SECTION_ID'];
			$itemId = (int)$row['IBLOCK_ELEMENT_ID'];
			if (!isset($sectionToItem[$itemSectionId]))
				$sectionToItem[$itemSectionId] = [];
			$sectionToItem[$itemSectionId][] = $itemId;
			$sectionIds[$itemSectionId] = $itemSectionId;
		}
		unset($itemId, $itemSectionId, $row, $iterator);
	}
	unset($pageIds, $elementIds);

	if (!empty($sectionIds))
	{
		sort($sectionIds);
		foreach (array_chunk($sectionIds, 500) as $pageIds)
		{
			$iterator = \CIBlockSection::GetList(
				[],
				['IBLOCK_ID' => $IBLOCK_ID, 'ID' => $pageIds, 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'S'],
				false,
				['ID', 'IBLOCK_ID', 'NAME', 'LEFT_MARGIN']
			);
			while ($row = $iterator->Fetch())
			{
				$rowId = (int)$row['ID'];
				if (empty($sectionToItem[$rowId]))
					continue;
				$row['LEFT_MARGIN'] = (int)$row['LEFT_MARGIN'];
				$sectionLink = $urlBuilder->getElementListUrl($row['ID']);
				$sectionList[$rowId] = '<a href="'.CHTTP::URN2URI($sectionLink).'" title="'.GetMessage('IBEL_SECTIONS_LINK_TITLE').'">'.htmlspecialcharsEx($row['NAME']).'</a>';
				foreach ($sectionToItem[$rowId] as $itemId)
				{
					if (!isset($itemSections[$itemId]))
						$itemSections[$itemId] = [];
					$itemSections[$itemId][] = [
						'LEFT_MARGIN' => $row['LEFT_MARGIN'],
						'ID' => $rowId
					];
				}
			}
			unset($row, $iterator);
		}
		unset($pageIds);
	}
	unset($sectionIds, $sectionToItem);

	/** @var CAdminListRow $row */
	foreach ($arRows as $itemId => $row)
	{
		$sectionsContent = '';
		if (!empty($itemSections[$itemId]))
		{
			Main\Type\Collection::sortByColumn(
				$itemSections[$itemId],
				'LEFT_MARGIN'
			);
			foreach ($itemSections[$itemId] as $data)
				$sectionsContent .= $sectionList[$data['ID']].'<br>';
			unset($data);
		}
		$row->AddViewField('SECTIONS', $sectionsContent);
	}
	unset($sectionsContent, $itemId, $row);

	unset($itemSections, $sectionList);
}

foreach($arRows as $idRow => $row)
{
	/** @var CAdminListRow $row */
	if (isset($arSelectedFieldsMap["PREVIEW_TEXT"]))
		$row->AddViewField("PREVIEW_TEXT", ($row->arRes["PREVIEW_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["PREVIEW_TEXT"]) : HTMLToTxt($row->arRes["PREVIEW_TEXT"])));
	if (isset($arSelectedFieldsMap["DETAIL_TEXT"]))
		$row->AddViewField("DETAIL_TEXT", ($row->arRes["DETAIL_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["DETAIL_TEXT"]) : HTMLToTxt($row->arRes["DETAIL_TEXT"])));

	if(isset($arElementOps[$idRow]) && isset($arElementOps[$idRow]["element_edit"]))
	{
		$row->AddCheckField("WF_NEW", false);
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", array('size'=>'35'));
		$row->AddViewField("NAME", '<a href="'.$row->arRes["edit_url"].'" title="'.GetMessage("IBEL_A_EDIT_TITLE").'">'.htmlspecialcharsEx($row->arRes["NAME"]).'</a>');
		$row->AddInputField("SORT", array('size'=>'3'));
		$row->AddInputField("CODE");
		$row->AddInputField("EXTERNAL_ID");
		if (isset($arSelectedFieldsMap['TAGS']))
		{
			if ($bSearch)
			{
				$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
				$row->AddEditField("TAGS",
					InputTags("FIELDS[" . $idRow . "][TAGS]", $row->arRes["TAGS"], $arIBlock["SITE_ID"]));
			}
			else
			{
				$row->AddInputField("TAGS");
			}
		}
		$row->AddCalendarField("DATE_ACTIVE_FROM", array(), $useCalendarTime);
		$row->AddCalendarField("DATE_ACTIVE_TO", array(), $useCalendarTime);

		if ($bWorkFlow)
		{
			if (!empty($arWFStatusPerm))
			{
				$row->AddSelectField("WF_STATUS_ID", $arWFStatusPerm);
			}
			if ($row->arRes['orig']['WF_NEW'] == 'Y' || $row->arRes['WF_STATUS_ID'] == '1')
			{
				$row->AddViewField("WF_STATUS_ID", $arWFStatusAll[$row->arRes['WF_STATUS_ID']]);
			}
			else
			{
				$row->AddViewField("WF_STATUS_ID", '<a href="'
					. $row->arRes["edit_url"]
					. '" title="'
					.
					GetMessage("IBEL_A_ED_TITLE")
					. '">'
					. $arWFStatusAll[$row->arRes['WF_STATUS_ID']]
					.
					'</a> / <a href="'
					. htmlspecialcharsbx($selfFolderUrl
						. CIBlock::GetAdminElementEditLink($IBLOCK_ID, $row->arRes['orig']['ID'], [
							"find_section_section" => $find_section_section,
							"replace_script_name" => true,
							'view' => (!isset($arElementOps[$idRow])
							|| !isset($arElementOps[$idRow]["element_edit_any_wf_status"]) ? 'Y' : null)
						]))
					. '" title="'
					. GetMessage("IBEL_A_ED2_TITLE")
					. '">'
					. $arWFStatusAll[$row->arRes['orig']['WF_STATUS_ID']]
					. '</a>');
			}
		}

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
					'upload' => true,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => true,
					'del' => true,
					'description' => true,
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
					'upload' => true,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => true,
					'del' => true,
					'description' => true,
				)
			);
		}
		if(isset($arSelectedFieldsMap["PREVIEW_TEXT"]))
		{
			$sHTML = '<input type="radio" name="FIELDS['.$idRow.'][PREVIEW_TEXT_TYPE]" value="text" id="'.$idRow.'PREVIEWtext"';
			if($row->arRes["PREVIEW_TEXT_TYPE"]!="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$idRow.'PREVIEWtext">text</label> /';
			$sHTML .= '<input type="radio" name="FIELDS['.$idRow.'][PREVIEW_TEXT_TYPE]" value="html" id="'.$idRow.'PREVIEWhtml"';
			if($row->arRes["PREVIEW_TEXT_TYPE"]=="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$idRow.'PREVIEWhtml">html</label><br>';
			$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$idRow.'][PREVIEW_TEXT]">'.htmlspecialcharsbx($row->arRes["PREVIEW_TEXT"]).'</textarea>';
			$row->AddEditField("PREVIEW_TEXT", $sHTML);
		}
		if(isset($arSelectedFieldsMap["DETAIL_TEXT"]))
		{
			$sHTML = '<input type="radio" name="FIELDS['.$idRow.'][DETAIL_TEXT_TYPE]" value="text" id="'.$idRow.'DETAILtext"';
			if($row->arRes["DETAIL_TEXT_TYPE"]!="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$idRow.'DETAILtext">text</label> /';
			$sHTML .= '<input type="radio" name="FIELDS['.$idRow.'][DETAIL_TEXT_TYPE]" value="html" id="'.$idRow.'DETAILhtml"';
			if($row->arRes["DETAIL_TEXT_TYPE"]=="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$idRow.'DETAILhtml">html</label><br>';

			$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$idRow.'][DETAIL_TEXT]">'.htmlspecialcharsbx($row->arRes["DETAIL_TEXT"]).'</textarea>';
			$row->AddEditField("DETAIL_TEXT", $sHTML);
		}
		foreach($row->arRes['props'] as $prop_id => $arEditHTML)
			$row->AddEditField($prop_id, '<table id="tb'.$arEditHTML['table_id'].'" border="0" cellpadding="0" cellspacing="0"><tr><td nowrap>'.implode("</td></tr><tr><td nowrap>", $arEditHTML['html']).'</td></tr></table>');

		if ($bCatalog)
		{
			if (isset($arElementOps[$idRow]["element_edit_price"]) && $boolCatalogPrice)
			{
				$lockFields = $lockedGridFields[$row->arRes['CATALOG_TYPE']] ?? [];

				$row->AddInputField('CATALOG_QUANTITY', $lockFields['CATALOG_QUANTITY'] ?? []);
				$row->AddInputField('CATALOG_QUANTITY_RESERVED', $lockFields['CATALOG_QUANTITY_RESERVED'] ?? []);
				$row->AddCheckField('CATALOG_AVAILABLE', $lockFields['CATALOG_AVAILABLE'] ?? []);
				$row->AddSelectField('CATALOG_QUANTITY_TRACE', $quantityTraceStatus, $lockFields['CATALOG_QUANTITY_TRACE'] ?? []);
				$row->AddSelectField('CAN_BUY_ZERO', $canBuyZeroStatus, $lockFields['CAN_BUY_ZERO'] ?? []);
				$row->AddCheckField('CATALOG_VAT_INCLUDED', $lockFields['CATALOG_VAT_INCLUDED'] ?? []);
				$row->AddSelectField('VAT_ID', $vatList, $lockFields['VAT_ID'] ?? []);
				$row->AddInputField('CATALOG_WEIGHT', $lockFields['CATALOG_WEIGHT'] ?? []);
				$row->AddInputField('CATALOG_WIDTH', $lockFields['CATALOG_WIDTH'] ?? []);
				$row->AddInputField('CATALOG_HEIGHT', $lockFields['CATALOG_HEIGHT'] ?? []);
				$row->AddInputField('CATALOG_LENGTH', $lockFields['CATALOG_LENGTH'] ?? []);
				$row->AddInputField('CATALOG_MEASURE_RATIO', $lockFields['CATALOG_MEASURE_RATIO'] ?? []);

				if (
					$boolCatalogPurchasInfo
					&& isset($arSelectedFieldsMap['CATALOG_PURCHASING_PRICE'])
				)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
					if ($catalogPurchasInfoEdit && $bCurrency)
					{
						$skuId = $selectedSkuMap[$idRow] ?? $idRow;
						$hiddenFields = [
							[
								'NAME' => 'FIELDS_OLD['.$skuId.'][CATALOG_PURCHASING_PRICE]',
								'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_PRICE']),
							],
							[
								'NAME' => 'FIELDS_OLD['.$skuId.'][CATALOG_PURCHASING_CURRENCY]',
								'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_CURRENCY']),
							],
						];

						$currency = !empty($row->arRes['CATALOG_PURCHASING_CURRENCY']) ? $row->arRes['CATALOG_PURCHASING_CURRENCY'] : Currency\CurrencyManager::getBaseCurrency();

						$row->AddMoneyField('CATALOG_PURCHASING_PRICE',
							[
								'CURRENCY_LIST' => $arCurrencyList,
								'HTML_ENTITY' => true,
								'PRICE' => [
									'NAME' => 'FIELDS['.$skuId.'][CATALOG_PURCHASING_PRICE]',
									'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_PRICE']),
								],
								'CURRENCY' => [
									'NAME' => 'FIELDS['.$skuId.'][CATALOG_PURCHASING_CURRENCY]',
									'VALUE' => htmlspecialcharsbx($currency),
								],
								'HIDDEN' => $hiddenFields
							]
						);
					}
				}
			}
			else
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField('CATALOG_QUANTITY', false);
				$row->AddSelectField('CATALOG_QUANTITY_TRACE', $quantityTraceStatus, false);
				$row->AddSelectField('CAN_BUY_ZERO', $canBuyZeroStatus, false);
				$row->AddInputField('CATALOG_WEIGHT', false);
				$row->AddInputField('CATALOG_WIDTH', false);
				$row->AddInputField('CATALOG_HEIGHT', false);
				$row->AddInputField('CATALOG_LENGTH', false);
				$row->AddCheckField('CATALOG_VAT_INCLUDED', false);
				$row->AddSelectField('VAT_ID', $vatList, false);
				$row->AddInputField('CATALOG_MEASURE_RATIO', false);

				if (
					$boolCatalogPurchasInfo
					&& isset($arSelectedFieldsMap['CATALOG_PURCHASING_PRICE'])
				)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
			}

/*			if ($showCatalogWithOffers || $row->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SKU)
			{
				if (isset($arElementOps[$idRow]["element_edit_price"]) && $boolCatalogPrice)
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
					$row->AddInputField("CATALOG_WIDTH");
					$row->AddInputField("CATALOG_HEIGHT");
					$row->AddInputField("CATALOG_LENGTH");
					$row->AddCheckField("CATALOG_VAT_INCLUDED");
					$row->AddSelectField('VAT_ID', $vatList);
					if ($boolCatalogPurchasInfo)
					{
						$price = '&nbsp;';
						if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
						{
							if ($bCurrency)
								$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
							else
								$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
						}
						$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
						unset($price);
						if ($catalogPurchasInfoEdit && $bCurrency)
						{
							$skuId = $selectedSkuMap[$idRow] ?? $idRow;
							$hiddenFields = [
								[
									'NAME' => 'FIELDS_OLD['.$skuId.'][CATALOG_PURCHASING_PRICE]',
									'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_PRICE']),
								],
								[
									'NAME' => 'FIELDS_OLD['.$skuId.'][CATALOG_PURCHASING_CURRENCY]',
									'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_CURRENCY']),
								],
							];

							$currency = !empty($row->arRes['CATALOG_PURCHASING_CURRENCY']) ? $row->arRes['CATALOG_PURCHASING_CURRENCY'] : Currency\CurrencyManager::getBaseCurrency();

							$row->AddMoneyField('CATALOG_PURCHASING_PRICE',
								[
									'CURRENCY_LIST' => $arCurrencyList,
									'HTML_ENTITY' => true,
									'PRICE' => [
										'NAME' => 'FIELDS['.$skuId.'][CATALOG_PURCHASING_PRICE]',
										'VALUE' => htmlspecialcharsbx($row->arRes['CATALOG_PURCHASING_PRICE']),
									],
									'CURRENCY' => [
										'NAME' => 'FIELDS['.$skuId.'][CATALOG_PURCHASING_CURRENCY]',
										'VALUE' => htmlspecialcharsbx($currency),
									],
									'HIDDEN' => $hiddenFields
								]
							);
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
					$row->AddInputField("CATALOG_WIDTH", false);
					$row->AddInputField("CATALOG_HEIGHT", false);
					$row->AddInputField("CATALOG_LENGTH", false);
					$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
					$row->AddSelectField('VAT_ID', $vatList, false);
					if ($boolCatalogPurchasInfo)
					{
						$price = '&nbsp;';
						if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
						{
							if ($bCurrency)
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
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddViewField('CATALOG_QUANTITY', ' ');
				$row->AddViewField('CATALOG_QUANTITY_TRACE', ' ');
				$row->AddViewField("CAN_BUY_ZERO", ' ');
				$row->AddViewField('CATALOG_WEIGHT', ' ');
				$row->AddViewField('CATALOG_WIDTH', ' ');
				$row->AddViewField('CATALOG_HEIGHT', ' ');
				$row->AddViewField('CATALOG_LENGTH', ' ');
				$row->AddViewField('CATALOG_VAT_INCLUDED', ' ');
				$row->AddViewField('VAT_ID', ' ');
				$row->AddViewField('CATALOG_PURCHASING_PRICE', '&nbsp;');
				$row->AddViewField('CATALOG_MEASURE_RATIO', ' ');
				$row->AddViewField('CATALOG_MEASURE', ' ');
				$row->arRes["CATALOG_BAR_CODE"] = ' ';
			} */
		}
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddViewField("NAME", '<a href="'.$row->arRes["edit_url"].'" title="'.GetMessage("IBEL_A_EDIT_TITLE").'">'.htmlspecialcharsEx($row->arRes["NAME"]).'</a>');
		$row->AddInputField("SORT", false);
		$row->AddInputField("CODE", false);
		$row->AddInputField("EXTERNAL_ID", false);
		if (isset($arSelectedFieldsMap['TAGS']))
		{
			$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
		}
		$row->AddCalendarField("DATE_ACTIVE_FROM", false);
		$row->AddCalendarField("DATE_ACTIVE_TO", false);
		if ($bWorkFlow)
		{
			$row->AddViewField("WF_STATUS_ID", $arWFStatusAll[$row->arRes['WF_STATUS_ID']]);
		}

		if ($bCatalog)
		{
			//TODO: change to lockFields
			if ($showCatalogWithOffers || $row->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SKU)
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField("CATALOG_QUANTITY", false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $quantityTraceStatus, false);
				$row->AddSelectField("CAN_BUY_ZERO", $canBuyZeroStatus, false);
				$row->AddInputField("CATALOG_WEIGHT", false);
				$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
				$row->AddSelectField('VAT_ID', $vatList, false);
				if ($boolCatalogPurchasInfo)
				{
					$price = '&nbsp;';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO", false);
			}
			else
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddViewField('CATALOG_QUANTITY', ' ');
				$row->AddViewField('CATALOG_QUANTITY_TRACE', ' ');
				$row->AddViewField("CAN_BUY_ZERO", ' ');
				$row->AddViewField('CATALOG_WEIGHT', ' ');
				$row->AddViewField('CATALOG_WIDTH', ' ');
				$row->AddViewField('CATALOG_HEIGHT', ' ');
				$row->AddViewField('CATALOG_LENGTH', ' ');
				$row->AddViewField('CATALOG_VAT_INCLUDED', ' ');
				$row->AddViewField('VAT_ID', ' ');
				$row->AddViewField('CATALOG_PURCHASING_PRICE', '&nbsp;');
				$row->AddViewField('CATALOG_MEASURE_RATIO', ' ');
				$row->AddViewField('CATALOG_MEASURE', ' ');
				$row->arRes["CATALOG_BAR_CODE"] = ' ';
			}
		}
		if (isset($arSelectedFieldsMap["PREVIEW_PICTURE"]))
		{
			$row->AddViewFileField("PREVIEW_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				)
			);
		}
		if (isset($arSelectedFieldsMap["DETAIL_PICTURE"]))
		{
			$row->AddViewFileField("DETAIL_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				)
			);
		}
	}

	if (isset($arSelectedFieldsMap['CATALOG_TYPE']))
	{
		$strProductType = '';
		if (isset($productTypeList[$row->arRes["CATALOG_TYPE"]]))
			$strProductType = $productTypeList[$row->arRes["CATALOG_TYPE"]];
		if ($row->arRes['CATALOG_BUNDLE'] == 'Y' && $boolCatalogSet)
			$strProductType .= ('' != $strProductType ? ', ' : '').GetMessage('IBEL_CATALOG_TYPE_MESS_GROUP');
		$row->AddViewField('CATALOG_TYPE', $strProductType);
	}
	if ($bCatalog && isset($arSelectedFieldsMap['CATALOG_MEASURE']) && ($showCatalogWithOffers || $row->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SKU))
	{
		if (isset($arElementOps[$idRow]["element_edit_price"]) && $boolCatalogProductEdit && $row->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SET)
		{
			$row->AddSelectField('CATALOG_MEASURE', $measureList);
		}
		else
		{
			$measureTitle = $measureList[$row->arRes['CATALOG_MEASURE']] ?? $measureList[0];
			$row->AddViewField('CATALOG_MEASURE', $measureTitle);
			unset($measureTitle);
		}
	}

	$arActions = array();

	if($row->arRes["ACTIVE"] == "Y")
	{
		$arActive = array(
			'ID' => 'deactivate',
			"TEXT" => GetMessage("IBEL_A_DEACTIVATE"),
			"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DEACTIVATE, $sThisSectionUrl),
			"ONCLICK" => "",
		);
	}
	else
	{
		$arActive = array(
			'ID' => 'activate',
			"TEXT" => GetMessage("IBEL_A_ACTIVATE"),
			"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::ACTIVATE, $sThisSectionUrl),
			"ONCLICK" => "",
		);
	}
	$clearCounter = array(
		'ID' => 'clear_counter',
		"TEXT" => GetMessage('IBEL_A_CLEAR_COUNTER'),
		"TITLE" => GetMessage('IBEL_A_CLEAR_COUNTER_TITLE'),
		"ACTION" => "if(confirm('".GetMessageJS("IBLOCK_CLEAR_COUNTER_CONFIRM")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::CLEAR_COUNTER, $sThisSectionUrl),
		"ONCLICK" => ""
	);
	$elementCodeTranslitAction = array(
		'ID' => 'code_translit',
		"TEXT" => GetMessage('IBEL_A_CODE_TRANSLIT'),
		"TITLE" => GetMessage('IBEL_A_CODE_TRANSLIT_TITLE'),
		"ACTION" => "if(confirm('".GetMessageJS("IBEL_A_CODE_TRANSLIT_CONFIRM")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::CODE_TRANSLIT, $sThisSectionUrl),
		"ONCLICK" => ""
	);

	if($bWorkFlow)
	{
		if(isset($arElementOps[$idRow]) && isset($arElementOps[$idRow]["element_edit_any_wf_status"]))
			$STATUS_PERMISSION = 2;
		else
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"]);

		$intMinPerm = 2;

		$arUnLock = array(
			"ICON" => "unlock",
			"TEXT" => GetMessage("IBEL_A_UNLOCK"),
			"TITLE" => GetMessage("IBLOCK_UNLOCK_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS("IBLOCK_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::ELEMENT_UNLOCK, $sThisSectionUrl),
			"ONCLICK" => "",
		);

		if($row->arRes['orig']['LOCK_STATUS'] == "red")
		{
			if (CWorkflow::IsAdmin())
				$arActions[] = $arUnLock;
		}
		else
		{
			/*
			 * yellow unlock
			 * edit
			 * copy
			 * history
			 * view (?)
			 * edit_orig (?)
			 * delete
			 */
			if (
				isset($arElementOps[$idRow])
				&& isset($arElementOps[$idRow]["element_edit"])
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				if ($row->arRes['orig']['LOCK_STATUS'] == "yellow")
				{
					$arActions[] = $arUnLock;
				}

				$arActions[] = array(
					'ID' => 'edit',
					"ICON" => "edit",
					"TEXT" => GetMessage("IBEL_A_CHANGE"),
					"DEFAULT" => true,
					"LINK" => $urlBuilder->getElementDetailUrl($row->arRes['orig']['ID'], $elementUrlParams),
				);
				$arActions[] = $arActive;
				if ($useElementTranslit)
					$arActions[] = $elementCodeTranslitAction;
				$arActions[] = $clearCounter;
			}

			if (
				$boolIBlockElementAdd
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				$arActions[] = array(
					'ID' => 'copy',
					"ICON" => "copy",
					"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
					"LINK" => $urlBuilder->getElementCopyUrl($row->arRes['orig']['ID'], $elementUrlParams),
				);
			}

			if(!$pageConfig['CATALOG'])
			{
				$arActions[] = array(
					"ICON" => "history",
					"TEXT" => GetMessage("IBEL_A_HIST"),
					"TITLE" => GetMessage("IBLOCK_HISTORY_ALT"),
					"ACTION" => $lAdmin->ActionRedirect('iblock_history_list.php?ELEMENT_ID='.$row->arRes['orig']['ID'].'&'.$sThisSectionUrl),
					"ONCLICK" => "",
				);
			}

			if ($row->arRes['DETAIL_PAGE_URL'] <> '' && $pageConfig['ALLOW_EXTERNAL_LINK'])
			{
				$tmpVar = CIBlock::ReplaceDetailUrl($row->arRes['orig']["DETAIL_PAGE_URL"], $row->arRes['orig'], true, "E");

				if (
					$row->arRes['orig']['WF_NEW'] == "Y"
					&& isset($arElementOps[$idRow])
					&& isset($arElementOps[$idRow]["element_edit"])
					&& 2 <= $STATUS_PERMISSION
				) // not published, under workflow
				{
					$arActions[] = array(
						"ICON" => "view",
						"TEXT" => GetMessage("IBLOCK_EL_ADMIN_VIEW_WF"),
						"TITLE" => GetMessage("IBEL_A_ORIG"),
						"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar).((mb_strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y"),
						"ONCLICK" => "",
					);
				}
				elseif ($row->arRes["WF_STATUS_ID"] > 1)
				{
					if (isset($arElementOps[$idRow])
						&& isset($arElementOps[$idRow]["element_edit"])
						&& isset($arElementOps[$idRow]["element_edit_any_wf_status"]))
					{
						$arActions[] = array(
							"ICON" => "view",
							"TEXT" => GetMessage("IBLOCK_EL_ADMIN_VIEW"),
							"TITLE" => GetMessage("IBEL_A_ORIG"),
							"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
							"ONCLICK" => "",
						);

						$arActions[] = array(
							"TEXT" => GetMessage("IBLOCK_EL_ADMIN_VIEW_WF"),
							"TITLE" => GetMessage("IBEL_A_ORIG"),
							"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar).((mb_strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y"),
							"ONCLICK" => "",
						);
					}
				}
				else
				{
					if (isset($arElementOps[$idRow])
						&& isset($arElementOps[$idRow]["element_edit"])
						&& 2 <= $STATUS_PERMISSION
					)
					{
						$arActions[] = array(
							"ICON" => "view",
							"TEXT" => GetMessage("IBLOCK_EL_ADMIN_VIEW"),
							"TITLE" => GetMessage("IBEL_A_ORIG"),
							"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
							"ONCLICK" => "",
						);
					}
				}
			}

			if ($row->arRes["WF_STATUS_ID"] > 1
				&& isset($arElementOps[$idRow])
				&& isset($arElementOps[$idRow]["element_edit"])
				&& isset($arElementOps[$idRow]["element_edit_any_wf_status"])
			)
			{
				$arActions[] = array(
					'ID' => 'edit_orig',
					"ICON" => "edit_orig",
					"TEXT" => GetMessage("IBEL_A_ORIG_ED"),
					"TITLE" => GetMessage("IBEL_A_ORIG_ED_TITLE"),
					"LINK" => $urlBuilder->getElementDetailUrl($row->arRes['orig']['ID'], $elementUrlParams),
				);
			}

			if (
				isset($arElementOps[$idRow])
				&& isset($arElementOps[$idRow]["element_delete"])
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				if (!isset($arElementOps[$idRow]["element_edit_any_wf_status"]))
					$intMinPerm = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"], $idRow);
				if (2 <= $intMinPerm)
				{
					$arActions[] = array(
						'ID' => 'delete',
						"ICON" => "delete",
						"TEXT" => GetMessage('MAIN_DELETE'),
						"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
						"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DELETE, $sThisSectionUrl),
						"ONCLICK" => "",
					);
				}
			}
		}
	}
	elseif($bBizproc)
	{
		$bWritePermission = $iblockDocument->CanUserOperateDocument(
			CBPCanUserOperateOperation::WriteDocument,
			$currentUser['ID'],
			$idRow,
			array(
				"IBlockId" => $IBLOCK_ID,
				"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
				"DocumentStates" => $arDocumentStates,
			)
		);

		$bStartWorkflowPermission = $iblockDocument->CanUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$currentUser['ID'],
			$idRow,
			array(
				"IBlockId" => $IBLOCK_ID,
				"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
				"DocumentStates" => $arDocumentStates,
			)
		);

		if(
			$bStartWorkflowPermission
			|| (
				isset($arElementOps[$idRow])
				&& isset($arElementOps[$idRow]["element_bizproc_start"])
			)
		)
		{
			$arActions[] = array(
				"ICON" => "",
				"TEXT" => GetMessage("IBEL_A_BP_RUN"),
				"ACTION" => $lAdmin->ActionRedirect('iblock_start_bizproc.php?document_id='.$idRow.'&document_type=iblock_'.$IBLOCK_ID.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array("mode", "table_id", "internal", "grid_id", "grid_action", "bxajaxid", "sessid"))).''), //todo replace to $lAdmin->getCurPageParam()
				"ONCLICK" => "",
			);
		}

		if ($row->arRes['lockStatus'] == "red")
		{
			if (CBPDocument::IsAdmin())
			{
				$arActions[] = array(
					"ICON" => "unlock",
					"TEXT" => GetMessage("IBEL_A_UNLOCK"),
					"TITLE" => GetMessage("IBEL_A_UNLOCK_ALT"),
					"ACTION" => "if(confirm('".GetMessageJS("IBEL_A_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($idRow, ActionType::ELEMENT_UNLOCK, $sThisSectionUrl),
					"ONCLICK" => "",
				);
			}
		}
		elseif ($bWritePermission)
		{
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => GetMessage("IBEL_A_CHANGE"),
				"DEFAULT" => true,
				"LINK" => $urlBuilder->getElementDetailUrl($idRow, $elementUrlParams),
			);
			$arActions[] = $arActive;
			if ($useElementTranslit)
				$arActions[] = $elementCodeTranslitAction;
			$arActions[] = $clearCounter;

			$arActions[] = array(
				'ID' => 'copy',
				"ICON" => "copy",
				"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
				"LINK" => $urlBuilder->getElementCopyUrl($idRow, $elementUrlParams),
			);

			if(!$pageConfig['CATALOG'])
			{
				$arActions[] = array(
					"ICON" => "history",
					"TEXT" => GetMessage("IBEL_A_HIST"),
					"TITLE" => GetMessage("IBEL_A_HISTORY_ALT"),
					"ACTION" => $lAdmin->ActionRedirect('iblock_bizproc_history.php?document_id='.$idRow.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array("internal", "grid_id", "grid_action", "bxajaxid", "sessid"))).''), //todo replace to $lAdmin->getCurPageParam()
					"ONCLICK" => "",
				);
			}

			$arActions[] = array(
				'ID' => 'delete',
				"ICON" => "delete",
				"TEXT" => GetMessage('MAIN_DELETE'),
				"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
				"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($idRow, ActionType::DELETE, $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
	}
	else
	{
		if(
			isset($arElementOps[$idRow])
			&& isset($arElementOps[$idRow]["element_edit"])
		)
		{
			$editAction = [
				"ICON" => "edit",
				"TEXT" => GetMessage("IBEL_A_CHANGE"),
				"DEFAULT" => true,
				"LINK" => $urlBuilder->getElementDetailUrl($row->arRes['orig']['ID'], $elementUrlParams)
			];

			$arActions[] = $editAction;
			$arActions[] = $arActive;
			if ($useElementTranslit)
				$arActions[] = $elementCodeTranslitAction;
			$arActions[] = $clearCounter;
		}

		if ($boolIBlockElementAdd && isset($arElementOps[$idRow])
			&& isset($arElementOps[$idRow]["element_edit"])
		)
		{
			$arActions[] = array(
				'ID' => 'copy',
				"ICON" => "copy",
				"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
				"LINK" => $urlBuilder->getElementCopyUrl($row->arRes['orig']['ID'], $elementUrlParams),
			);
		}

		if ($row->arRes['DETAIL_PAGE_URL'] <> '' && $pageConfig['ALLOW_EXTERNAL_LINK'])
		{
			$tmpVar = CIBlock::ReplaceDetailUrl($row->arRes['orig']["DETAIL_PAGE_URL"], $row->arRes['orig'], true, "E");
			$arActions[] = array(
				"ICON" => "view",
				"TEXT" => GetMessage("IBLOCK_EL_ADMIN_VIEW"),
				"TITLE" => GetMessage("IBEL_A_ORIG"),
				"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
				"ONCLICK" => "",
			);
		}

		if(
			isset($arElementOps[$idRow])
			&& isset($arElementOps[$idRow]["element_delete"])
		)
		{
			$arActions[] = array(
				"ID" => "delete",
				"ICON" => "delete",
				"TEXT" => GetMessage('MAIN_DELETE'),
				"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
				"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], ActionType::DELETE, $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
		if (
			isset($arElementOps[$idRow]['element_edit'])
			&& $bCatalog
			&& (
				$row->arRes['CATALOG_TYPE'] === Catalog\ProductTable::TYPE_PRODUCT
				|| $row->arRes['CATALOG_TYPE'] === Catalog\ProductTable::TYPE_SERVICE
			)
			&& $enableConversionToService
			&& $boolCatalogProductEdit
		)
		{
			if ($row->arRes['CATALOG_TYPE'] === Catalog\ProductTable::TYPE_PRODUCT)
			{
				$messageId =
					$useStoreControl
						? 'IBEL_A_CONVERT_PRODUCT_TO_SERVICE_CONFIRM_WITH_INVENTORY'
						: 'IBEL_A_CONVERT_PRODUCT_TO_SERVICE_CONFIRM_WITHOUT_INVENTORY'
				;
				$arActions[] = array(
					'TEXT' => GetMessage('IBEL_A_CONVERT_PRODUCT_TO_SERVICE_TITLE'),
					'TITLE' => GetMessage('IBEL_A_CONVERT_PRODUCT_TO_SERVICE_TITLE'),
					"ACTION" => "if(confirm('".GetMessageJS($messageId)."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE, $sThisSectionUrl),
					"ONCLICK" => ""
				);
			}
			else
			{
				$arActions[] = array(
					'TEXT' => GetMessage('IBEL_A_CONVERT_SERVICE_TO_PRODUCT_TITLE'),
					'TITLE' => GetMessage('IBEL_A_CONVERT_SERVICE_TO_PRODUCT_TITLE'),
					"ACTION" => "if(confirm('".GetMessageJS("IBEL_A_CONVERT_SERVICE_TO_PRODUCT_CONFIRM_MESSAGE")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT, $sThisSectionUrl),
					"ONCLICK" => ""
				);
			}
		}
	}

	if ($bCatalog && $pageConfig['USE_NEW_CARD'])
	{
		if ($bExcel)
		{
			$row->AddViewField('CATALOG_PRODUCT', $row->arRes['NAME']);
		}
		else
		{
			if (!$isChangeVariationRequest)
			{
				$productFields = array_merge($row->arRes, [
					'SKU_IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
					'SKU_ID' => $selectedSkuMap[$idRow] ?? null,
				]);
				ob_start();
				$APPLICATION->IncludeComponent(
					'bitrix:catalog.grid.product.field',
					'',
					[
						'GRID_ID' => $sTableID,
						'ROW_ID' => $idRow,
						'PRODUCT_FIELDS' => $productFields,
						'ENABLE_IMAGE_INPUT' => false,
						'ENABLE_CHANGES_RENDERING' => false,
						'SKU_TREE' => $productSkuTree[$idRow] ?? null,
					]
				);
				$field = ob_get_clean();
				$row->AddViewField('CATALOG_PRODUCT', $field);
			}
		}
	}
	else
	{
		if (!$bExcel)
		{
			$row->AddViewField(
				'NAME',
				'<a href="' . $row->arRes["edit_url"] . '" title="' . GetMessage("IBEL_A_EDIT_TITLE") . '">'
				. htmlspecialcharsEx($row->arRes["NAME"])
				. '</a>'
			);
		}
	}

	// filter actions for access rights
	if ($bCatalog)
	{
		$editActions = array_fill_keys([
			// 'edit', not included, because edit page it is view page
			'edit_orig',
			ActionType::ACTIVATE,
			ActionType::DEACTIVATE,
			ActionType::CLEAR_COUNTER,
			ActionType::CODE_TRANSLIT,
			Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE,
			Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT,
		], true);

		$arActions = array_filter(
			$arActions,
			static function ($item) use (
				$editActions,
				$boolCatalogProductAdd,
				$boolCatalogProductEdit,
				$boolCatalogProductDelete
			)
			{
				$id = $item['ID'] ?? null;

				if ($id === 'copy')
				{
					return $boolCatalogProductAdd;
				}
				elseif ($id === 'delete')
				{
					return $boolCatalogProductDelete;
				}
				elseif (isset($editActions[$id]))
				{
					return $boolCatalogProductEdit;
				}

				return true;
			}
		);
	}

	if (!empty($arActions))
	{
		$row->AddActions($arActions);
	}
}
unset($row);

$lAdmin->AddFooter([
	[
		'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
		'value' => $selectedCount,
	],
	[
		'counter' => true,
		'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'),
		'value' => '0',
	],
]);

$actionList = array();
foreach($arElementOps as $id => $arOps)
{
	if(isset($arOps["element_delete"]) && (!$bCatalog || $boolCatalogProductDelete))
	{
		$actionList[] = ActionType::DELETE;
		break;
	}
}
$elementEdit = false;
foreach($arElementOps as $id => $arOps)
{
	if(isset($arOps["element_edit"]))
	{
		$elementEdit = true;
		break;
	}
}

if ($elementEdit && (!$bCatalog || $boolCatalogProductEdit))
{
	$actionList[] = ActionType::EDIT;
	$actionList[] = ActionType::SELECT_ALL;
	$actionList[] = ActionType::ACTIVATE;
	$actionList[] = ActionType::DEACTIVATE;
	if ($useElementTranslit)
	{
		$actionList[ActionType::CODE_TRANSLIT] = [
			'CONFIRM_MESSAGE' => GetMessage('IBEL_A_CODE_TRANSLIT_CONFIRM_MULTI')
		];
	}
	$actionList[] = ActionType::CLEAR_COUNTER;
}

if ($elementEdit && (!$bCatalog || $boolCatalogProductEdit))
{
	$actionList[] = ActionType::MOVE_TO_SECTION;
	$actionList[] = ActionType::ADD_TO_SECTION;

	if ($bCatalog)
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
			if ($boolCatalogProductEdit)
			{
				if (
					$arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_FULL
					|| $arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_CATALOG
				)
				{
					$actionList[] = Catalog\Grid\ProductAction::CONVERT_PRODUCT_TO_SERVICE;
					$actionList[] = Catalog\Grid\ProductAction::CONVERT_SERVICE_TO_PRODUCT;
				}
				$actionList[] = Catalog\Grid\ProductAction::SET_FIELD;
			}
			if ($boolCatalogPrice)
			{
				$actionList[] = Catalog\Grid\ProductAction::CHANGE_PRICE;
			}
		}
	}
}

if($bWorkFlow)
{
	$actionList[] = ActionType::ELEMENT_UNLOCK;
	$actionList[] = ActionType::ELEMENT_LOCK;
	$actionList[] = ActionType::ELEMENT_WORKFLOW_STATUS;
}
elseif($bBizproc)
{
	$actionList[] = ActionType::ELEMENT_UNLOCK;
}

$lAdmin->AddGroupActionTable($panelAction->getList($actionList));
unset($actionList);

if ($bCatalog && $boolCatalogPrice)
{
	$lAdmin->BeginEpilogContent();

	/** Creation window of common price changer */
	CJSCore::Init(array('window'));
	?><!--suppress JSUnusedLocalSymbols --><script type="text/javascript">
		/**
		 * @func CreateDialogChPrice - creation of common changing price dialog
		 */
		function CreateDialogChPrice()
		{
			var paramsWindowChanger =
			{
				title: "<?=GetMessage("IBLOCK_CHANGING_PRICE")?>",
				content_url: "/bitrix/tools/catalog/iblock_catalog_change_price.php?lang=" + "<?=LANGUAGE_ID?>" + "&bxpublic=Y",
				content_post: "<?=bitrix_sessid_get()?>" + "&sTableID=<?=$sTableID?>",
				width: 800,
				height: 415,
				resizable: false,
				buttons: [
					{
						title: top.BX.message('JS_CORE_WINDOW_SAVE'),
						id: 'savebtn',
						name: 'savebtn',
						className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
					},
					top.BX.CAdminDialog.btnCancel
				]
			};
			var priceChanger = (new top.BX.CAdminDialog(paramsWindowChanger));
			priceChanger.Show();
		}
	</script>

	<?php
	$lAdmin->EndEpilogContent();
}

if ($pageConfig['SHOW_NAVCHAIN'])
{
	$chain = $lAdmin->CreateChain();

	if ($pageConfig['NAVCHAIN_ROOT'])
	{
		$sSectionUrl = $urlBuilder->getElementListUrl($arIBTYPE["SECTIONS"] == "Y" ? 0 : -1, array());
		$chain->AddItem(array(
			"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
			"LINK" => htmlspecialcharsbx($sSectionUrl),
			"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
		));
	}

	if ($arIBTYPE["SECTIONS"] == "Y" && $find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'), true);
		foreach ($nav as $ar_nav)
		{
			$sSectionUrl = $urlBuilder->getElementListUrl((int)$ar_nav["ID"], array());
			$chain->AddItem(array(
				"TEXT" => htmlspecialcharsEx($ar_nav["NAME"]),
				"LINK" => htmlspecialcharsbx($sSectionUrl),
				"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
			));
		}
		unset($sSectionUrl, $ar_nav, $nav);
	}
	$lAdmin->ShowChain($chain);
	unset($chain);
}

$aContext = array();

if ($iblockElementAdd && (!$bCatalog || $boolCatalogProductAdd))
{
	$params = array(
		'find_section_section' => $find_section_section,
		'IBLOCK_SECTION_ID' => $find_section_section,
	);

	if (!empty($arCatalog))
	{
		\CCatalogAdminTools::setProductFormParams();
		$arCatalogBtns = \CCatalogAdminTools::getIBlockElementMenu(
			$IBLOCK_ID,
			$arCatalog,
			$params,
			$urlBuilder,
			$sTableID
		);
		if (!empty($arCatalogBtns))
			$aContext = $arCatalogBtns;
	}
	if (empty($aContext))
	{
		$aContext[] = array(
			"ICON" => "btn_new",
			"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENT_ADD"]),
			"LINK" => $urlBuilder->getElementDetailUrl(0, $params),
			"ID" => "create_new_product_button_" . $sTableID,
			"LINK_PARAM" => "",
			'SHOW_TITLE' => true,
			"TITLE" => GetMessage("IBEL_A_ADDEL_TITLE")
		);
	}
}
elseif ($bCatalog && !$boolCatalogProductAdd && empty($productLimits))
{
	$aContext = \CCatalogAdminTools::getIblockElementMenuLocked();
}

if($bBizproc && IsModuleInstalled("bizprocdesigner"))
{
	$bCanDoIt = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$currentUser['ID'],
		array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID)
	);

	if($bCanDoIt)
	{
		$aContext[] = array(
			"TEXT" => GetMessage("IBEL_BTN_BP"),
			"LINK" => $selfFolderUrl.'iblock_bizproc_workflow_admin.php?document_type=iblock_'.$IBLOCK_ID.'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($_SERVER['REQUEST_URI']),
			"LINK_PARAM" => "",
		);
	}
}

$lAdmin->setContextSettings(array("pagePath" => $pageConfig['CONTEXT_PATH']));
$contextConfig = array();
$excelExport = (Main\Config\Option::get("iblock", "excel_export_rights") == "Y"
	? CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_export")
	: true
);

if ($excelExport && $bCatalog && !$boolCatalogExport)
{
	$excelExport = false;
}

if ($excelExport)
{
	$contextConfig['excel'] = true;
}
$additional = $urlBuilder->getContextMenuItems($urlBuilder::PAGE_ELEMENT_LIST);
if ($additional === null)
{
	$additional = [];
}
$lAdmin->SetContextMenu($aContext, $additional, $contextConfig);
$lAdmin->CheckListMode();

if ($pageConfig['PUBLIC_CRM_CATALOG'])
{
	$APPLICATION->SetTitle(GetMessage("IBEL_LIST_TITLE_2"));
}
else
{
	if ($pageConfig['CATALOG'])
		$APPLICATION->SetTitle(GetMessage("IBEL_LIST_TITLE", array("#IBLOCK_NAME#" => $arIBlock["NAME"])));
	else
		$APPLICATION->SetTitle($arIBlock["NAME"]);
}

Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//We need javascript not in excel mode
if (($lAdmin->isPageMode() || $lAdmin->isAjaxMode()) && $bCatalog && $bCurrency)
{
	?><script type="text/javascript">
		top.arCatalogShowedGroups = [];
		top.arExtra = [];
		top.arCatalogGroups = [];
		top.BaseIndex = <?=$basePriceTypeId;?>;
		<?php
	if (!empty($priceTypeIndex))
	{
		$i = 0;
		$j = 0;
		foreach($priceTypeIndex as $priceType)
		{
			echo "top.arCatalogShowedGroups[".$i."]=".$priceType.";\n";
			$i++;
			if ($priceType != $basePriceTypeId)
			{
				echo "top.arCatalogGroups[".$j."]=".$priceType.";\n";
				$j++;
			}
		}
		unset($priceType);
	}
	if (!empty($arCatExtra))
	{
		$i=0;
		foreach($arCatExtra as &$CatExtra)
		{
			echo "top.arExtra[".$CatExtra["ID"]."]=".$CatExtra["PERCENTAGE"].";\n";
			$i++;
		}
		unset($CatExtra);
	}
	?>
		BX.addCustomEvent(window, 'Grid.MoneyField::change', BX.delegate(
			function(event) {
				var data = event.getData();
				if (BX.type.isDomNode(data.field) && data.field.dataset.base === 'true')
				{
					top.ChangeBasePrice(data.field.dataset.product);
					top.ChangeBaseCurrency(data.field.dataset.product);
				}
			}, this)
		);
		top.ChangeBasePrice = function(id)
		{
			var i,
				cnt,
				pr,
				price,
				extraId,
				esum,
				eps;

			for(i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				pr = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]_control");
				if(BX.type.isDomNode(pr) && pr.disabled)
				{
					price = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.BaseIndex+"]_control").value;
					if(price > 0)
					{
						extraId = top.document.getElementById("CATALOG_EXTRA["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]_control").value;
						esum = parseFloat(price) * (1 + top.arExtra[extraId] / 100);
						eps = 1.00/Math.pow(10, 6);
						esum = Math.round((esum+eps)*100)/100;
					}
					else
						esum = "";

					pr.value = esum;
				}
			}
		}

		top.ChangeBaseCurrency = function(id)
		{
			var baseCurrency = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.BaseIndex+"]_control"),
				i,
				cnt,
				currencyItem;
			for(i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				currencyItem = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.arCatalogShowedGroups[i]+"]_control");
				if(currencyItem.dataset.disabled)
				{
					currencyItem.dataset.value = baseCurrency.dataset.value;
					currencyItem.innerHTML = baseCurrency.innerHTML;
				}
			}
		}
	</script>
	<?php
}

CJSCore::Init('file_input');

$lAdmin->BeginPrologContent();
if (!empty($productLimits))
{
	Loader::includeModule('ui');
	Main\UI\Extension::load("ui.alerts");
	?><div class="ui-alert ui-alert-warning">
	<span class="ui-alert-message"><?php echo $productLimits['MESSAGE']; ?></span>
	</div><?
}

// stepper
if ($bCatalog && !$isChangeVariationRequest && $pageConfig['USE_NEW_CARD'])
{
	echo \Bitrix\Main\Update\Stepper::getHtml('catalog');
}
$lAdmin->EndPrologContent();

$enableInstagram = Loader::includeModule('crm') && Instagram::isAvailable() && Instagram::isActiveStatus();
if ($enableInstagram)
{
	$lAdmin->setFilterPresets([
		'import_instagram' => [
			'name' => GetMessage('IBEL_PRODUCTS_INSTAGRAM'),
			'fields' => ['SECTION_ID' => Instagram::getSectionId()],
		],
	]);
}

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();
if($bWorkFlow || $bBizproc):
	echo BeginNote();?>
	<span class="adm-lamp adm-lamp-green"></span> - <?echo GetMessage("IBLOCK_GREEN_ALT")?><br>
	<span class="adm-lamp adm-lamp-yellow"></span> - <?echo GetMessage("IBLOCK_YELLOW_ALT")?><br>
	<span class="adm-lamp adm-lamp-red"></span> - <?echo GetMessage("IBLOCK_RED_ALT")?><br>
	<?echo EndNote();
endif;

$urlBuilder->showDetailPageSlider();

if ($pageConfig['IBLOCK_EDIT'] && CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit"))
{
	echo
		BeginNote(),
		GetMessage("IBEL_A_IBLOCK_MANAGE_HINT"),
		' <a href="'.htmlspecialcharsbx('iblock_edit.php?type='.urlencode($type).'&lang='.LANGUAGE_ID.'&ID='.urlencode($IBLOCK_ID).'&admin=Y&return_url='.urlencode(CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section' => intval($find_section_section))))).'">',
		GetMessage("IBEL_A_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}

if ($publicMode && !$bExcel && $enableInstagram)
{
	$APPLICATION->IncludeComponent('bitrix:crm.order.import.instagram.observer', '');
}

if ($bCatalog && !$isChangeVariationRequest && $pageConfig['USE_NEW_CARD'])
{
	$listData = [
		'gridId' => $sTableID,
		'variationFieldNames' => $skuFields,
		'productVariationMap' => $selectedSkuMap,
		'createNewProductHref' => $aContext[0]['LINK'],
		'showCatalogWithOffers' => $showCatalogWithOffers,
		'canEditPrice' => $boolCatalogPrice,
	];
	Main\UI\Extension::load([
		'catalog.iblock-product-list',
		'catalog.product-selector',
		'ui.forms'
	]);
	// manual load for product-selector dependencies when grid loads without products
	Main\Page\Asset::getInstance()->addJs('/bitrix/components/bitrix/catalog.grid.product.field/templates/.default/script.js');
	Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/catalog.grid.product.field/templates/.default/style.css');
	Main\Page\Asset::getInstance()->addJs('/bitrix/components/bitrix/ui.image.input/templates/.default/script.js');
	Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/ui.image.input/templates/.default/style.css');
	?>
	<script>
		BX.ready(function() {
			new BX.Catalog.IblockProductList(<?=CUtil::PhpToJsObject($listData)?>);
		});
	</script>
	<?php
}
elseif ($bCatalog && !$isChangeVariationRequest && $publicMode)
{
	$listData = [
		'gridId' => $sTableID,
		'canEditPrice' => $boolCatalogPrice,
	];
	Main\UI\Extension::load([
		'catalog.iblock-product-list',
	]);
	?>
	<script>
		BX.ready(function() {
			new BX.Catalog.IblockProductListHints(<?=CUtil::PhpToJsObject($listData)?>);
		});
	</script>
	<?php
}

if ($pageConfig['PUBLIC_CRM_CATALOG'])
{
	$urlBuilder->openSettingsPage();
	$urlBuilder->subscribeOnAfterSettingsSave();
}

unset($urlBuilder);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
