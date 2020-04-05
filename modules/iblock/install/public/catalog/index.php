<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("IBLOCK_INSTALL_PUBLIC_CATALOG_TITLE"));
?>
<?$APPLICATION->IncludeComponent("bitrix:catalog", ".default", Array(
	"IBLOCK_TYPE"	=>	"catalog",
	"IBLOCK_ID"	=>	GetMessage("IBLOCK_INSTALL_PUBLIC_IBLOCK_ID"),
	"USE_FILTER"	=>	"Y",
	"USE_REVIEW"	=>	"Y",
	"USE_COMPARE"	=>	"Y",
	"BASKET_URL"	=>	"/personal/cart/",
	"ACTION_VARIABLE"	=>	"action",
	"PRODUCT_ID_VARIABLE"	=>	"id",
	"SECTION_ID_VARIABLE"	=>	"SECTION_ID",
	"SEF_MODE"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"MESSAGES_PER_PAGE"	=>	"5",
	"USE_CAPTCHA"	=>	"Y",
	"PATH_TO_SMILE"	=>	"/bitrix/images/forum/smile/",
	"FORUM_ID"	=>	"6",
	"URL_TEMPLATES_READ"	=>	"/communication/forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#",
	"FILTER_NAME"	=>	"FILTER",
	"FILTER_FIELD_CODE"	=>	array(
		0	=>	"NAME",
	),
	"FILTER_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"COMPARE_NAME"	=>	"CATALOG_COMPARE_LIST",
	"COMPARE_FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"COMPARE_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"DISPLAY_ELEMENT_SELECT_BOX"	=>	"N",
	"ELEMENT_SORT_FIELD_BOX"	=>	"name",
	"ELEMENT_SORT_ORDER_BOX"	=>	"asc",
	"COMPARE_ELEMENT_SORT_FIELD"	=>	"sort",
	"COMPARE_ELEMENT_SORT_ORDER"	=>	"asc",
	"PAGE_ELEMENT_COUNT"	=>	"5",
	"LINE_ELEMENT_COUNT"	=>	"1",
	"ELEMENT_SORT_FIELD"	=>	"sort",
	"ELEMENT_SORT_ORDER"	=>	"asc",
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"SECTION_SORT_FIELD"	=>	"sort",
	"SECTION_SORT_ORDER"	=>	"asc",
	"SHOW_TOP_ELEMENTS"	=>	"Y",
	"TOP_ELEMENT_COUNT"	=>	"3",
	"TOP_LINE_ELEMENT_COUNT"	=>	"1",
	"TOP_ELEMENT_SORT_FIELD"	=>	"id",
	"TOP_ELEMENT_SORT_ORDER"	=>	"desc",
	"TOP_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"ISBN",
		1	=>	"YEAR",
		2	=>	"PUBLISHER",
		3	=>	"PAGES",
		4	=>	"AUTHORS",
	),
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	"",
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_TEMPLATE"	=>	"orange",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"PRICE_CODE"	=>	array(
		0	=>	"RETAIL",
	),
	"USE_PRICE_COUNT"	=>	"N",
	"SHOW_PRICE_COUNT"	=>	"1",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"ELEMENT_ID",
	)
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>