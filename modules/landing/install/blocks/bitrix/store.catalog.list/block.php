<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\ModuleManager;

// get settings data
$params = \Bitrix\Landing\Node\Component::getIblockParams();
$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
	isset($landing) ? $landing->getSiteId() : null
);
$iblockId = isset($settings['IBLOCK_ID'])
			? $settings['IBLOCK_ID']
			: (isset($params['id']) ? $params['id'] : 0);

// calc variables
/*$variables = \Bitrix\Landing\Landing::getVariables();
$sectionCode = isset($variables['sef'][0]) ? $variables['sef'][0] : '';
$sectionId = 0;
if (\Bitrix\Main\Loader::includeModule('iblock'))
{
	$sectionId = \CIBlockFindTools::GetSectionIDByCodePath(
		$iblockId, $sectionCode
	);
}*/

// actions for edit mode
$editMode = \Bitrix\Landing\Landing::getEditMode();
$setStatus404 = $editMode ? 'N' : 'Y';
$setTitle = $editMode ? 'N' : 'Y';
if ($editMode && isset($landing))
{
	$siteId = $landing->getSmnSiteId();
}
else
{
	$siteId = SITE_ID;
}
?>
<?$APPLICATION->IncludeComponent(
	'bitrix:landing.blocks.cmpfilter',
	'',
	array(
		'FILTER' => array(),
		'FILTER_NAME' => 'arrFilter'
	)
);?>
<?if (!$editMode && ModuleManager::isModuleInstalled('sale')):?>
	<?/*$APPLICATION->IncludeComponent(
		"bitrix:sale.basket.basket.line",
		".default",
		array(
			"PATH_TO_BASKET" => "#system_cart",
			"PATH_TO_PERSONAL" => "#system_personal",
			"SHOW_PERSONAL_LINK" => "N",
			"SHOW_NUM_PRODUCTS" => "Y",
			"SHOW_TOTAL_PRICE" => "Y",
			"SHOW_PRODUCTS" => "N",
			"POSITION_FIXED" => "Y",
			"SHOW_AUTHOR" => "N",
			"PATH_TO_REGISTER" => "/auth/",
			"PATH_TO_PROFILE" => "#system_personal",
			"COMPONENT_TEMPLATE" => ".default",
			"PATH_TO_ORDER" => "#system_order",
			"SHOW_EMPTY_VALUES" => "Y",
			"PATH_TO_AUTHORIZE" => "/auth/",
			"POSITION_HORIZONTAL" => "left",
			"POSITION_VERTICAL" => "bottom",
			"HIDE_ON_BASKET_PAGES" => "Y"
		),
		false
	);?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:catalog.compare.list",
		"",
		array(
			"IBLOCK_TYPE" => $iblockType,
			"IBLOCK_ID" => $iblockId,
			"NAME" => "CATALOG_COMPARE_LIST",
			"DETAIL_URL" => "#system_catalogitem/#ELEMENT_CODE#/",
			"COMPARE_URL" => "#system_compare",
			"ACTION_VARIABLE" => "action",
			"PRODUCT_ID_VARIABLE" => "id",
			'POSITION_FIXED' => "Y",
			'POSITION' => 'top left'
		),
		false
	);*/?>
<?endif;?>
<section class="landing-block g-pt-20 g-pb-20">
	<div class="container">
		<div class="tab-content g-pt-20">
			<div class="tab-pane fade show active">
				<div class="landing-component">
				<?$APPLICATION->IncludeComponent(
					"bitrix:catalog.section",
					"bootstrap_v4",
					array(
						"IBLOCK_TYPE" => "",
						"IBLOCK_ID" => $iblockId,
						"SECTION_ID" => "",
						"SECTION_CODE" => "",
						"SECTION_USER_FIELDS" => array(),
						"ELEMENT_SORT_FIELD" => "sort",
						"ELEMENT_SORT_ORDER" => "desc",
						"ELEMENT_SORT_FIELD2" => "",
						"ELEMENT_SORT_ORDER2" => "",
						"FILTER_NAME" => "arrFilter",
						"INCLUDE_SUBSECTIONS" => "Y",
						"SHOW_ALL_WO_SECTION" => "Y",
						"PAGE_ELEMENT_COUNT" => "12",
						"LINE_ELEMENT_COUNT" => "3",
						"PROPERTY_CODE" => array(
							0 => "ARTNUMBER",
							1 => "MANUFACTURER",
							2 => "MATERIAL",
						),
						"OFFERS_FIELD_CODE" => array(),
						"OFFERS_PROPERTY_CODE" => array(
							0 => "COLOR_REF",
							1 => "SIZES_SHOES",
							2 => "SIZES_CLOTHES",
						),
						"OFFERS_SORT_FIELD" => "sort",
						"OFFERS_SORT_ORDER" => "desc",
						"OFFERS_SORT_FIELD2" => "",
						"OFFERS_SORT_ORDER2" => "",
						"OFFERS_LIMIT" => "0",
						"TEMPLATE_THEME" => "vendor",
						"PRODUCT_DISPLAY_MODE" => "Y",
						"ADD_PICT_PROP" => "MORE_PHOTO",
						"LABEL_PROP" => array(
							0 => "NEWPRODUCT",
							1 => "SALELEADER",
							2 => "SPECIALOFFER",
						),
						"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
						"OFFER_TREE_PROPS" => array(
							0 => "COLOR_REF",
							1 => "SIZES_SHOES",
							2 => "SIZES_CLOTHES",
						),
						"MESS_BTN_BUY" => "",
						"MESS_BTN_ADD_TO_BASKET" => "",
						"MESS_BTN_SUBSCRIBE" => "",
						"MESS_BTN_DETAIL" => "",
						"MESS_NOT_AVAILABLE" => "",
						"SECTION_URL" => "#system_catalog#SECTION_CODE_PATH#/",
						"DETAIL_URL" => "#system_catalogitem/#ELEMENT_CODE#/",
						"SECTION_ID_VARIABLE" => "SECTION_CODE",
						"AJAX_MODE" => "N",
						"AJAX_OPTION_JUMP" => "Y",
						"AJAX_OPTION_STYLE" => "Y",
						"AJAX_OPTION_HISTORY" => "N",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "36000000",
						"CACHE_GROUPS" => "N",
						"SET_META_KEYWORDS" => "N",
						"META_KEYWORDS" => "",
						"SET_META_DESCRIPTION" => "N",
						"META_DESCRIPTION" => "",
						"BROWSER_TITLE" => "-",
						"ADD_SECTIONS_CHAIN" => "Y",
						"SET_TITLE" => $setTitle,
						"SET_STATUS_404" => $setStatus404,
						"CACHE_FILTER" => "N",
						"CONVERT_CURRENCY" => "Y",
						"BASKET_URL" => "#system_cart",
						"ACTION_VARIABLE" => "action",
						"PRODUCT_ID_VARIABLE" => "id",
						"PRODUCT_QUANTITY_VARIABLE" => "quantity",
						"ADD_PROPERTIES_TO_BASKET" => "N",
						"PRODUCT_PROPS_VARIABLE" => "prop",
						"PARTIAL_PRODUCT_PROPERTIES" => "Y",
						"PRODUCT_PROPERTIES" => array(
							0 => "BRAND_REF",
							1 => "NEWPRODUCT",
							2 => "SALELEADER",
							3 => "SPECIALOFFER",
							4 => "MATERIAL",
							5 => "RECOMMEND",
						),
						"OFFERS_CART_PROPERTIES" => array(
							0 => "ARTNUMBER",
							1 => "COLOR_REF",
							2 => "SIZES_SHOES",
							3 => "SIZES_CLOTHES",
						),
						"PAGER_TEMPLATE" => "round",
						"DISPLAY_TOP_PAGER" => "N",
						"DISPLAY_BOTTOM_PAGER" => "N",
						"PAGER_TITLE" => "",
						"PAGER_SHOW_ALWAYS" => "N",
						"PAGER_DESC_NUMBERING" => "N",
						"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
						"PAGER_SHOW_ALL" => "N",
						"AJAX_OPTION_ADDITIONAL" => "",
						"SET_BROWSER_TITLE" => "N",
						"SHOW_CLOSE_POPUP" => "Y",
						"MESS_BTN_COMPARE" => "",
						"ADD_TO_BASKET_ACTION" => "BUY",
						"COMPONENT_TEMPLATE" => ".default",
						"SEF_MODE" => "N",
						"SET_LAST_MODIFIED" => "N",
						"USE_MAIN_ELEMENT_SECTION" => "N",
						"PAGER_BASE_LINK_ENABLE" => "N",
						"SHOW_404" => "N",
						"MESSAGE_404" => "",
						"PAGER_BASE_LINK" => "",
						"PAGER_PARAMS_NAME" => "arrPager",
						"BACKGROUND_IMAGE" => "UF_BACKGROUND_IMAGE",
						"DISABLE_INIT_JS_IN_COMPONENT" => "N",
						"CUSTOM_FILTER" => "",
						"PRODUCT_BLOCKS_ORDER" => "props,sku,price,quantity,buttons,quantityLimit,compare",
						"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
						"SHOW_SLIDER" => "Y",
						"ENLARGE_PRODUCT" => "STRICT",
						"LABEL_PROP_MOBILE" => array(
							0 => "NEWPRODUCT",
							1 => "SALELEADER",
							2 => "SPECIALOFFER",
						),
						"LABEL_PROP_POSITION" => "top-left",
						"DISCOUNT_PERCENT_POSITION" => "bottom-right",
						"RCM_TYPE" => "personal",
						"RCM_PROD_ID" => "",
						"LAZY_LOAD" => "Y",
						"LOAD_ON_SCROLL" => "N",
						"PROPERTY_CODE_MOBILE" => array(
							0 => "ARTNUMBER",
							1 => "MANUFACTURER",
							2 => "MATERIAL",
						),
						"SLIDER_INTERVAL" => "3000",
						"SLIDER_PROGRESS" => "Y",
						"MESS_BTN_LAZY_LOAD" => "",
						"SHOW_MAX_QUANTITY" => "M",
						"SHOW_FROM_SECTION" => "Y",
						"COMPATIBLE_MODE" => "N",
						"COMPOSITE_FRAME_MODE" => "A",
						"COMPOSITE_FRAME_TYPE" => "AUTO",
						"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
						"MESS_SHOW_MAX_QUANTITY" => "",
						"RELATIVE_QUANTITY_FACTOR" => "5",
						"MESS_RELATIVE_QUANTITY_MANY" => "",
						"MESS_RELATIVE_QUANTITY_FEW" => "",
						"USE_COMPARE_LIST" => "Y",
						"STRICT_SECTION_CHECK" => "Y",
						"PREDICT_ELEMENT_COUNT" => "Y",
						"COMPARE_PATH" => "#system_compare",
						"HIDE_NOT_AVAILABLE" => $settings['HIDE_NOT_AVAILABLE'],
						"HIDE_NOT_AVAILABLE_OFFERS" => $settings['HIDE_NOT_AVAILABLE_OFFERS'],
						"PRODUCT_SUBSCRIPTION" => $settings['PRODUCT_SUBSCRIPTION'],
						"USE_PRODUCT_QUANTITY" => $settings['USE_PRODUCT_QUANTITY'],
						"DISPLAY_COMPARE" => $settings['DISPLAY_COMPARE'],
						"PRICE_CODE" => $settings['PRICE_CODE'],
						"USE_PRICE_COUNT" => $settings['USE_PRICE_COUNT'],
						"SHOW_PRICE_COUNT" => $settings['SHOW_PRICE_COUNT'],
						"CURRENCY_ID" => $settings['CURRENCY_ID'],
						"PRICE_VAT_INCLUDE" => $settings['PRICE_VAT_INCLUDE'],
						"SHOW_OLD_PRICE" => $settings['SHOW_OLD_PRICE'],
						"SHOW_DISCOUNT_PERCENT" => $settings['SHOW_DISCOUNT_PERCENT'],
						"USE_ENHANCED_ECOMMERCE" => $settings['USE_ENHANCED_ECOMMERCE'],
						"DATA_LAYER_NAME" => $settings['DATA_LAYER_NAME'],
						"BRAND_PROPERTY" => $settings['BRAND_PROPERTY'],
						"CUSTOM_SITE_ID" => $siteId
					),
					false
				);?>
				</div>
			</div>
		</div>
	</div>
</section>