<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");
?>
<?if (IsModuleInstalled("advertising")):?>
	<div class="mb-5">
		<?$APPLICATION->IncludeComponent(
			"bitrix:advertising.banner",
			"bootstrap_v4",
			array(
				"COMPONENT_TEMPLATE" => "bootstrap_v4",
				"TYPE" => "MAIN",
				"NOINDEX" => "Y",
				"QUANTITY" => "3",
				"BS_EFFECT" => "fade",
				"BS_CYCLING" => "N",
				"BS_WRAP" => "Y",
				"BS_PAUSE" => "Y",
				"BS_KEYBOARD" => "Y",
				"BS_ARROW_NAV" => "Y",
				"BS_BULLET_NAV" => "Y",
				"BS_HIDE_FOR_TABLETS" => "N",
				"BS_HIDE_FOR_PHONES" => "Y",
				"CACHE_TYPE" => "A",
				"CACHE_TIME" => "36000000",
			),
			false
		);?>
	</div>
<?endif?>

<?
global $trendFilter;
$trendFilter = array('PROPERTY_TREND' => '#TREND_PROPERTY_VALUE_ID#');
?>
<h2>Тренды сезона</h2>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section",
	"bootstrap_v4",
	array(
		"IBLOCK_TYPE_ID" => "catalog",
		"IBLOCK_ID" => "#CATALOG_IBLOCK_ID#",
		"BASKET_URL" => "#SITE_DIR#personal/cart/",
		"COMPONENT_TEMPLATE" => "",
		"IBLOCK_TYPE" => "catalog",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_CODE" => "",
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_FIELD2" => "id",
		"ELEMENT_SORT_ORDER2" => "desc",
		"FILTER_NAME" => "trendFilter",
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"PAGE_ELEMENT_COUNT" => "12",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array(
			0 => "NEWPRODUCT",
			1 => "",
		),
		"OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "COLOR_REF",
			1 => "SIZES_SHOES",
			2 => "SIZES_CLOTHES",
			3 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "desc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"TEMPLATE_THEME" => "site",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"ADD_PICT_PROP" => "MORE_PHOTO",
		"LABEL_PROP" => array(
			0 => "NEWPRODUCT"
		),
		"OFFER_ADD_PICT_PROP" => "-",
		"OFFER_TREE_PROPS" => array(
			0 => "COLOR_REF",
			1 => "SIZES_SHOES",
			2 => "SIZES_CLOTHES",
		),
		"PRODUCT_SUBSCRIPTION" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"SHOW_CLOSE_POPUP" => "N",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_SUBSCRIBE" => "Подписаться",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"SEF_MODE" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"SET_TITLE" => "Y",
		"SET_BROWSER_TITLE" => "Y",
		"BROWSER_TITLE" => "-",
		"SET_META_KEYWORDS" => "Y",
		"META_KEYWORDS" => "-",
		"SET_META_DESCRIPTION" => "Y",
		"META_DESCRIPTION" => "-",
		"SET_LAST_MODIFIED" => "N",
		"USE_MAIN_ELEMENT_SECTION" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"CACHE_FILTER" => "N",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "N",
		"USE_PRODUCT_QUANTITY" => "N",
		"PRODUCT_QUANTITY_VARIABLE" => "",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(
		),
		"OFFERS_CART_PROPERTIES" => array(
			0 => "COLOR_REF",
			1 => "SIZES_SHOES",
			2 => "SIZES_CLOTHES",
		),
		"ADD_TO_BASKET_ACTION" => "ADD",
		"PAGER_TEMPLATE" => "round",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => "",
		"COMPATIBLE_MODE" => "N",
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>