<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.compare.result",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"FIELD_CODE" => $arParams["COMPARE_FIELD_CODE"],
		"PROPERTY_CODE" => $arParams["COMPARE_PROPERTY_CODE"],
		"NAME" => $arParams["COMPARE_NAME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
 		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
		"DISPLAY_ELEMENT_SELECT_BOX" => $arParams["DISPLAY_ELEMENT_SELECT_BOX"],
		"ELEMENT_SORT_FIELD_BOX" => $arParams["ELEMENT_SORT_FIELD_BOX"],
		"ELEMENT_SORT_ORDER_BOX" => $arParams["ELEMENT_SORT_ORDER_BOX"],
		"ELEMENT_SORT_FIELD" => $arParams["COMPARE_ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["COMPARE_ELEMENT_SORT_ORDER"],

		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
	),
	$component
);?>
