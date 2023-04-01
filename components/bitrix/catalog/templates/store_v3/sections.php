<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

?><div class="bx-<?=$arParams['TEMPLATE_THEME']?>"><?php
/*$sectionListParams = array(
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
	"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
	"TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
	"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
	"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
	"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
	"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"]) ? $arParams["SECTIONS_HIDE_SECTION_NAME"] : "N"),
	"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : '')
);
if ($sectionListParams["COUNT_ELEMENTS"] === "Y")
{
	$sectionListParams["COUNT_ELEMENTS_FILTER"] = "CNT_ACTIVE";
	if ($arParams["HIDE_NOT_AVAILABLE"] == "Y")
	{
		$sectionListParams["COUNT_ELEMENTS_FILTER"] = "CNT_AVAILABLE";
	}
}
$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"store_v3",
	$sectionListParams,
	$component,
	($arParams["SHOW_TOP_ELEMENTS"] !== "N" ? array("HIDE_ICONS" => "Y") : array())
);
unset($sectionListParams); */

if ($arParams["USE_COMPARE"] === "Y")
{
	$APPLICATION->IncludeComponent("bitrix:catalog.compare.list", "", array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"NAME" => $arParams["COMPARE_NAME"],
			"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
			"COMPARE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["compare"],
			"ACTION_VARIABLE" => (!empty($arParams["ACTION_VARIABLE"]) ? $arParams["ACTION_VARIABLE"] : "action"),
			"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
			'POSITION_FIXED' => $arParams['COMPARE_POSITION_FIXED'] ?? '',
			'POSITION' => $arParams['COMPARE_POSITION'] ?? ''
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}

if ($arParams["SHOW_TOP_ELEMENTS"] !== "N")
{
	if (isset($arParams['USE_COMMON_SETTINGS_BASKET_POPUP']) && $arParams['USE_COMMON_SETTINGS_BASKET_POPUP'] === 'Y')
	{
		$basketAction = $arParams['COMMON_ADD_TO_BASKET_ACTION'] ?? '';
	}
	else
	{
		$basketAction = $arParams['TOP_ADD_TO_BASKET_ACTION'] ?? '';
	}

	$APPLICATION->IncludeComponent("bitrix:catalog.section", "store_v3", array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ELEMENT_SORT_FIELD" => $arParams["TOP_ELEMENT_SORT_FIELD"],
			"ELEMENT_SORT_ORDER" => $arParams["TOP_ELEMENT_SORT_ORDER"],
			"ELEMENT_SORT_FIELD2" => $arParams["TOP_ELEMENT_SORT_FIELD2"],
			"ELEMENT_SORT_ORDER2" => $arParams["TOP_ELEMENT_SORT_ORDER2"],
			"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
			"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
			"BASKET_URL" => $arParams["BASKET_URL"],
			"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
			"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
			"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
			"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
			"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
			// -- delete "ELEMENT_COUNT" => $arParams["TOP_ELEMENT_COUNT"],
			"LINE_ELEMENT_COUNT" => $arParams["TOP_LINE_ELEMENT_COUNT"],
			"PROPERTY_CODE" => ($arParams["TOP_PROPERTY_CODE"] ?? []),
			"PROPERTY_CODE_MOBILE" => $arParams["TOP_PROPERTY_CODE_MOBILE"],
			"PRICE_CODE" => $arParams["~PRICE_CODE"],
			"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
			"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
			"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
			"PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"], // ?
			"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
			"ADD_PROPERTIES_TO_BASKET" => ($arParams["ADD_PROPERTIES_TO_BASKET"] ?? ''),
			"PARTIAL_PRODUCT_PROPERTIES" => ($arParams["PARTIAL_PRODUCT_PROPERTIES"] ?? ''),
			"PRODUCT_PROPERTIES" => ($arParams["PRODUCT_PROPERTIES"] ?? []),
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"OFFERS_CART_PROPERTIES" => ($arParams["OFFERS_CART_PROPERTIES"] ?? []),
			"OFFERS_FIELD_CODE" => $arParams["TOP_OFFERS_FIELD_CODE"],
			"OFFERS_PROPERTY_CODE" => ($arParams["TOP_OFFERS_PROPERTY_CODE"] ?? []),
			"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
			"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
			"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
			"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
			"OFFERS_LIMIT" => ($arParams["TOP_OFFERS_LIMIT"] ?? 0),
			'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
			'CURRENCY_ID' => $arParams['CURRENCY_ID'],
			'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
			// -- delete 'VIEW_MODE' => (isset($arParams['TOP_VIEW_MODE']) ? $arParams['TOP_VIEW_MODE'] : ''),
			// --delete 'ROTATE_TIMER' => (isset($arParams['TOP_ROTATE_TIMER']) ? $arParams['TOP_ROTATE_TIMER'] : ''),
			'TEMPLATE_THEME' => ($arParams['TEMPLATE_THEME'] ?? ''),

			'LABEL_PROP' => $arParams['LABEL_PROP'] ?? '',
			'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'] ?? '',
			'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'] ?? '',
			'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'] ?? '',
			'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
			'PRODUCT_BLOCKS_ORDER' => $arParams['TOP_PRODUCT_BLOCKS_ORDER'],
			'PRODUCT_ROW_VARIANTS' => $arParams['TOP_PRODUCT_ROW_VARIANTS'],
			'ENLARGE_PRODUCT' => $arParams['TOP_ENLARGE_PRODUCT'],
			'ENLARGE_PROP' => $arParams['TOP_ENLARGE_PROP'] ?? '',
			'SHOW_SLIDER' => $arParams['TOP_SHOW_SLIDER'],
			'SLIDER_INTERVAL' => $arParams['TOP_SLIDER_INTERVAL'] ?? '',
			'SLIDER_PROGRESS' => $arParams['TOP_SLIDER_PROGRESS'] ?? '',

			'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'] ?? '',
			'OFFER_TREE_PROPS' => ($arParams['OFFER_TREE_PROPS'] ?? []),
			'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
			'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
			'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
			'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
			'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
			'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
			'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
			'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
			'MESS_NOT_AVAILABLE' => $arParams['~MESS_NOT_AVAILABLE'] ?? '',
			'MESS_NOT_AVAILABLE_SERVICE' => $arParams['~MESS_NOT_AVAILABLE_SERVICE'] ?? '',
			'ADD_TO_BASKET_ACTION' => $basketAction,
			'SHOW_CLOSE_POPUP' => $arParams['COMMON_SHOW_CLOSE_POPUP'] ?? '',
			'COMPARE_PATH' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['compare'],
			'USE_COMPARE_LIST' => 'Y',

			'COMPATIBLE_MODE' => ($arParams['COMPATIBLE_MODE'] ?? ''),

			// section

			//"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
			//"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
			//"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
			//"SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
			//"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
			"INCLUDE_SUBSECTIONS" => "Y",
			//"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
			//"FILTER_NAME" => $arParams["FILTER_NAME"],
			//"CACHE_FILTER" => $arParams["CACHE_FILTER"],
			//"SET_TITLE" => $arParams["SET_TITLE"],
			//"MESSAGE_404" => $arParams["~MESSAGE_404"],
			//"SET_STATUS_404" => $arParams["SET_STATUS_404"],
			//"SHOW_404" => $arParams["SHOW_404"],
			//"FILE_404" => $arParams["FILE_404"],
			"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],

			"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
			"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
			"PAGER_TITLE" => $arParams["PAGER_TITLE"],
			"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
			"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
			"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
			"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
			"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
			"PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
			"PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
			"PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
			"LAZY_LOAD" => $arParams["LAZY_LOAD"] ?? '',
			"MESS_BTN_LAZY_LOAD" => $arParams["~MESS_BTN_LAZY_LOAD"] ?? '',
			"LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"] ?? '',
			"CYCLIC_LOADING" => "Y",
			"CYCLIC_LOADING_COUNTER_NAME" => "cycleCount",
			"SECTIONS_OFFSET_MODE" => "F",

			//"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
			//"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
			"SHOW_ALL_WO_SECTION" => "N",
			"USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
			'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

			'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
			'MESS_SHOW_MAX_QUANTITY' => ($arParams['~MESS_SHOW_MAX_QUANTITY'] ?? ''),
			'RELATIVE_QUANTITY_FACTOR' => ($arParams['RELATIVE_QUANTITY_FACTOR'] ?? ''),
			'MESS_RELATIVE_QUANTITY_MANY' => ($arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?? ''),
			'MESS_RELATIVE_QUANTITY_FEW' => ($arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?? ''),

			'MESS_BTN_COMPARE' => ($arParams['~MESS_BTN_COMPARE'] ?? ''),

			'USE_ENHANCED_ECOMMERCE' => ($arParams['USE_ENHANCED_ECOMMERCE'] ?? ''),
			'DATA_LAYER_NAME' => ($arParams['DATA_LAYER_NAME'] ?? ''),
			'BRAND_PROPERTY' => ($arParams['BRAND_PROPERTY'] ?? ''),

			"ADD_SECTIONS_CHAIN" => "N",

			'COMPARE_NAME' => $arParams['COMPARE_NAME'],
			'BACKGROUND_IMAGE' => ($arParams['SECTION_BACKGROUND_IMAGE'] ?? ''),
			'DISABLE_INIT_JS_IN_COMPONENT' => ($arParams['DISABLE_INIT_JS_IN_COMPONENT'] ?? '')
		),
		$component
	);
	unset($basketAction);
}
?></div><?php
