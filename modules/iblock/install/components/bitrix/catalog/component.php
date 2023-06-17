<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @global CMain $APPLICATION */

// region Check common parameters
$arParams['COMPATIBLE_MODE'] = (string)($arParams['COMPATIBLE_MODE'] ?? 'N');

$arParams['USE_FILTER'] = (string)($arParams['USE_FILTER'] ?? 'N');
if ($arParams['USE_FILTER'] !== 'Y')
{
	$arParams['USE_FILTER'] = 'N';
}
if ($arParams['USE_FILTER'] === 'Y')
{
	$arParams["FILTER_NAME"] = trim((string)($arParams['FILTER_NAME'] ?? ''));
	if (
		$arParams["FILTER_NAME"] === ''
		|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams['FILTER_NAME'])
	)
	{
		$arParams['FILTER_NAME'] = 'arrFilter';
	}
}
else
{
	$arParams['FILTER_NAME'] = '';
}

$arParams['ADD_PROPERTIES_TO_BASKET'] ??= '';
$arParams['PARTIAL_PRODUCT_PROPERTIES'] ??= '';
$arParams['SET_LAST_MODIFIED'] ??= '';

$arParams['USE_MAIN_ELEMENT_SECTION'] ??= '';

$arParams['USE_COMPARE'] = (string)($arParams['USE_COMPARE'] ?? 'N');
$arParams['COMPARE_NAME'] ??= '';

$arParams['COMPARE_FIELD_CODE'] = ($arParams['COMPARE_FIELD_CODE'] ?? []);
if (!is_array($arParams['COMPARE_FIELD_CODE']))
{
	$arParams['COMPARE_FIELD_CODE'] = [];
}
$arParams['COMPARE_FIELD_CODE'] = array_filter($arParams['COMPARE_FIELD_CODE']);

$arParams['COMPARE_PROPERTY_CODE'] = ($arParams['COMPARE_PROPERTY_CODE'] ?? []);
if (!is_array($arParams['COMPARE_PROPERTY_CODE']))
{
	$arParams['COMPARE_PROPERTY_CODE'] = [];
}
$arParams['COMPARE_PROPERTY_CODE'] = array_filter($arParams['COMPARE_PROPERTY_CODE']);

$arParams['COMPARE_OFFERS_FIELD_CODE'] = ($arParams['COMPARE_OFFERS_FIELD_CODE'] ?? []);
if (!is_array($arParams['COMPARE_OFFERS_FIELD_CODE']))
{
	$arParams['COMPARE_OFFERS_FIELD_CODE'] = [];
}
$arParams['COMPARE_OFFERS_FIELD_CODE'] = array_filter($arParams['COMPARE_OFFERS_FIELD_CODE']);

$arParams['COMPARE_OFFERS_PROPERTY_CODE'] = ($arParams['COMPARE_OFFERS_PROPERTY_CODE'] ?? []);
if (!is_array($arParams['COMPARE_OFFERS_PROPERTY_CODE']))
{
	$arParams['COMPARE_OFFERS_PROPERTY_CODE'] = [];
}
$arParams['COMPARE_OFFERS_PROPERTY_CODE'] = array_filter($arParams['COMPARE_OFFERS_PROPERTY_CODE']);

$arParams['COMPARE_ELEMENT_SORT_FIELD'] = (string)($arParams['COMPARE_ELEMENT_SORT_FIELD'] ?? 'SORT');
$arParams['COMPARE_ELEMENT_SORT_ORDER'] = (string)($arParams['COMPARE_ELEMENT_SORT_ORDER'] ?? 'ASC');
$arParams['DISPLAY_ELEMENT_SELECT_BOX'] = (string)($arParams['DISPLAY_ELEMENT_SELECT_BOX'] ?? 'N');
$arParams['ELEMENT_SORT_FIELD_BOX'] = (string)($arParams['ELEMENT_SORT_FIELD_BOX'] ?? 'NAME');
$arParams['ELEMENT_SORT_ORDER_BOX'] = (string)($arParams['ELEMENT_SORT_ORDER_BOX'] ?? 'ASC');
$arParams['ELEMENT_SORT_FIELD_BOX2'] = (string)($arParams['ELEMENT_SORT_FIELD_BOX2'] ?? 'ID');
$arParams['ELEMENT_SORT_ORDER_BOX2'] = (string)($arParams['ELEMENT_SORT_ORDER_BOX2'] ?? 'DESC');

//default gifts
if(empty($arParams['USE_GIFTS_SECTION']))
{
	$arParams['USE_GIFTS_SECTION'] = 'Y';
}
if(empty($arParams['GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT']))
{
	$arParams['GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT'] = 3;
}
$arParams['GIFTS_SECTION_LIST_HIDE_BLOCK_TITLE'] ??= '';
$arParams['GIFTS_SECTION_LIST_BLOCK_TITLE'] ??= '';
$arParams['GIFTS_SECTION_LIST_TEXT_LABEL_GIFT'] ??= '';
if(empty($arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT']))
{
	$arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
}
$arParams['GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE'] ??= '';
$arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'] ??= '';

$arParams['USE_GIFTS_DETAIL'] ??= '';
if(empty($arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT']))
{
	$arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
}
$arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'] ??= '';
$arParams['GIFTS_DETAIL_BLOCK_TITLE'] ??= '';
$arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'] ??= '';

$arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] ??= '';

$arParams['GIFTS_SHOW_DISCOUNT_PERCENT'] ??= '';
$arParams['GIFTS_SHOW_OLD_PRICE'] ??= '';
$arParams['GIFTS_SHOW_NAME'] ??= '';
$arParams['GIFTS_SHOW_IMAGE'] ??= '';
$arParams['GIFTS_MESS_BTN_BUY'] ??= '';
$arParams['~GIFTS_MESS_BTN_BUY'] ??= '';

$arParams['HIDE_NOT_AVAILABLE'] ??= '';
$arParams['HIDE_NOT_AVAILABLE_OFFERS'] ??= '';
$arParams['CONVERT_CURRENCY'] ??= '';
$arParams['CURRENCY_ID'] ??= '';

$arParams['STORES'] ??= [];
$arParams['SHOW_EMPTY_STORE'] ??= '';
$arParams['SHOW_GENERAL_STORE_INFORMATION'] ??= '';
$arParams['USER_FIELDS'] ??= [];

$arParams['ACTION_VARIABLE'] = (isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : 'action');
if ($arParams["ACTION_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
{
	$arParams["ACTION_VARIABLE"] = "action";
}

$arParams['VARIABLE_ALIASES'] ??= [];

$arParams['PAGER_PARAMS_NAME'] ??= '';
$arParams['PAGER_BASE_LINK_ENABLE'] ??= '';
$arParams['PAGER_BASE_LINK'] ??= '';
$arParams['FILE_404'] ??= '';
// end region

// region hidden parameters
$arParams['SHOW_404'] ??= '';
$arParams['~MESSAGE_404'] ??= '';
// endregion

// region Standart template parameters for put on warnings in custom templates
$arParams['INSTANT_RELOAD'] ??= '';

$arParams['LABEL_PROP'] ??= '';
$arParams['LABEL_PROP_MOBILE'] ??= '';
$arParams['LABEL_PROP_POSITION'] ??= '';
$arParams['ADD_PICT_PROP'] ??= '';

$arParams['DISCOUNT_PERCENT_POSITION'] ??= '';

$arParams['SHOW_MAX_QUANTITY'] ??= '';

$arParams['LIST_PRODUCT_BLOCKS_ORDER'] ??= '';
$arParams['LIST_SHOW_SLIDER'] ??= '';
$arParams['LIST_PROPERTY_CODE_MOBILE'] ??= '';
$arParams['LIST_PRODUCT_ROW_VARIANTS'] ??= '';
$arParams['LIST_ENLARGE_PRODUCT'] ??= '';

$arParams['DETAIL_SET_CANONICAL_URL'] ??= '';
$arParams['SHOW_SKU_DESCRIPTION'] ??= '';
$arParams['SHOW_DEACTIVATED'] ??= '';

$arParams['PRODUCT_SUBSCRIPTION'] ??= '';

$arParams['LAZY_LOAD'] ??= '';
$arParams['LOAD_ON_SCROLL'] ??= '';

$arParams['~MESS_BTN_BUY'] ??= '';
$arParams['MESS_BTN_BUY'] ??= '';
$arParams['~MESS_BTN_ADD_TO_BASKET'] ??= '';
$arParams['MESS_BTN_ADD_TO_BASKET'] ??= '';
$arParams['~MESS_BTN_COMPARE'] ??= '';
$arParams['MESS_BTN_COMPARE'] ??= '';
$arParams['~MESS_BTN_DETAIL'] ??= '';
$arParams['MESS_BTN_DETAIL'] ??= '';
$arParams['~MESS_NOT_AVAILABLE'] ??= '';
$arParams['MESS_NOT_AVAILABLE'] ??= '';
$arParams['~MESS_NOT_AVAILABLE_SERVICE'] ??= '';
$arParams['MESS_NOT_AVAILABLE_SERVICE'] ??= '';
$arParams['~MESS_BTN_SUBSCRIBE'] ??= '';
$arParams['MESS_BTN_SUBSCRIBE'] ??= '';
$arParams['~MESS_BTN_LAZY_LOAD'] ??= '';
$arParams['MESS_BTN_LAZY_LOAD'] ??= '';
// endregion

$smartBase = ($arParams["SEF_URL_TEMPLATES"]["section"]?: "#SECTION_ID#/");
$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_ID#/",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"compare" => "compare.php?action=COMPARE",
	"smart_filter" => $smartBase."filter/#SMART_FILTER_PATH#/apply/"
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
	"action",
);

$arVariables = array();
if ($arParams["SEF_MODE"] === "Y")
{
	$engine = new CComponentEngine($this);
	if (\Bitrix\Main\Loader::includeModule('iblock'))
	{
		$engine->addGreedyPart("#SECTION_CODE_PATH#");
		$engine->addGreedyPart("#SMART_FILTER_PATH#");
		$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
	}
	$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if ($componentPage === "smart_filter")
		$componentPage = "section";

	if(!$componentPage && isset($_REQUEST["q"]))
		$componentPage = "search";

	$b404 = false;
	if(!$componentPage)
	{
		$componentPage = "sections";
		$b404 = true;
	}

	if($componentPage == "section")
	{
		if (isset($arVariables["SECTION_ID"]))
			$b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
		else
			$b404 |= !isset($arVariables["SECTION_CODE"]);
	}

	if($b404 && CModule::IncludeModule('iblock'))
	{
		$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
		if ($folder404 != "/")
			$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
		if (mb_substr($folder404, -1) == "/")
			$folder404 .= "index.php";

		if ($folder404 != $APPLICATION->GetCurPage(true))
		{
			\Bitrix\Iblock\Component\Tools::process404(
				""
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SHOW_404"] === "Y")
				,$arParams["FILE_404"]
			);
		}
	}

	CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}
else
{
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	$arCompareCommands = array(
		"COMPARE",
		"DELETE_FEATURE",
		"ADD_FEATURE",
		"DELETE_FROM_COMPARE_RESULT",
		"ADD_TO_COMPARE_RESULT",
		"COMPARE_BUY",
		"COMPARE_ADD2BASKET"
	);

	if(isset($arVariables["action"]) && in_array($arVariables["action"], $arCompareCommands))
		$componentPage = "compare";
	elseif(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "element";
	elseif(isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')
		$componentPage = "element";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
		$componentPage = "section";
	elseif(isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> '')
		$componentPage = "section";
	elseif(isset($_REQUEST["q"]))
		$componentPage = "search";
	else
		$componentPage = "sections";

	$currentPage = htmlspecialcharsbx($APPLICATION->GetCurPage())."?";
	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => array(
			"section" => $currentPage.$arVariableAliases["SECTION_ID"]."=#SECTION_ID#",
			"element" => $currentPage.$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#",
			"compare" => $currentPage."action=COMPARE",
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}

$arResult["VARIABLES"]["SMART_FILTER_PATH"] ??= '';
$arResult["VARIABLES"]["SECTION_ID"] ??= 0;
$arResult['VARIABLES']['ELEMENT_ID'] ??= 0;

$this->IncludeComponentTemplate($componentPage);