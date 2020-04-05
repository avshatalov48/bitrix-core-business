<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Доска объявлений");
?>
<h4><a href="#SITE_DIR#board/my/?edit=Y" >Добавить объявление</a>  <span class="small-grey">|</span>  <a href="#SITE_DIR#board/my/" >Мои объявления</a></h4>
 <?$APPLICATION->IncludeComponent("bitrix:catalog.section", "board", array(
	"IBLOCK_TYPE" => "services",
	"IBLOCK_ID" => "#BOARD_IBLOCK_ID#",
	"SECTION_ID" => "",
	"SECTION_CODE" => $_REQUEST["SECTION_CODE"],
	"SECTION_USER_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"FILTER_NAME" => "arrFilter",
	"INCLUDE_SUBSECTIONS" => "Y",
	"SHOW_ALL_WO_SECTION" => "N",
	"PAGE_ELEMENT_COUNT" => "30",
	"LINE_ELEMENT_COUNT" => "1",
	"PROPERTY_CODE" => array(
		0 => "E_MAIL",
		1 => "URL",
		2 => "PHONE",
		3 => "USER_ID",
		4 => "",
	),
	"SECTION_URL" => "#SITE_DIR#board/#CODE#/",
	"DETAIL_URL" => "#SITE_DIR#board/#CODE#/",
	"BASKET_URL" => "",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"AJAX_MODE" => "Y",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "N",
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"BROWSER_TITLE" => "-",
	"ADD_SECTIONS_CHAIN" => "N",
	"DISPLAY_COMPARE" => "N",
	"SET_TITLE" => "N",
	"SET_STATUS_404" => "Y",
	"CACHE_FILTER" => "N",
	"PRICE_CODE" => array(
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "N",
	"PRODUCT_PROPERTIES" => array(
	),
	"USE_PRODUCT_QUANTITY" => "N",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Объявления",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
	"PAGER_SHOW_ALL" => "N",
	"TREE_LINE_ELEMENT_COUNT" => "2",
	"TREE_DETAIL_PAGE_URL" => "Y",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?> 
<br />
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>