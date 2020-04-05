<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\ModuleManager;

// get settings data
if ($params = \Bitrix\Landing\Node\Component::getIblockParams())
{
	$iblockId = $params['id'];
	$defaultElementCode = $params['default_product'];
}
$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
	isset($landing) ? $landing->getSiteId() : null
);
if ($settings['IBLOCK_ID'])
{
	$iblockId = $settings['IBLOCK_ID'];
}

// calc variables
$variables = \Bitrix\Landing\Landing::getVariables();
$sectionCode = isset($variables['sef'][0]) ? $variables['sef'][0] : '';
$elementCode = isset($variables['sef'][1]) ? $variables['sef'][1] : '';
if (!$sectionCode && !$elementCode)
{
	$elementCode = $defaultElementCode;
}

// actions for edit mode
$editMode = \Bitrix\Landing\Landing::getEditMode();
$setTitle = $editMode ? 'N' : 'Y';
$setStatus404 = $editMode ? 'N' : 'Y';
if ($editMode && isset($landing))
{
	$siteId = $landing->getSmnSiteId();
}
else
{
	$siteId = SITE_ID;
}
?>
<?if (!$editMode  && ModuleManager::isModuleInstalled('sale')):?>
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
			"IBLOCK_TYPE" => "",
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
				<?$APPLICATION->IncludeComponent(
					"bitrix:catalog.element",
					"bootstrap_v4",
					array(
						"IBLOCK_TYPE" => "",
						"IBLOCK_ID" => $iblockId,
						"ELEMENT_ID" => "",
						"ELEMENT_CODE" => $elementCode,
						"SECTION_ID" => "",
						"SECTION_CODE" => "",
						"PROPERTY_CODE" => array(
							0 => "ARTNUMBER",
							1 => "MANUFACTURER",
							2 => "MATERIAL"
						),
						"OFFERS_FIELD_CODE" => array(),
						"OFFERS_PROPERTY_CODE" => array(
							0 => "COLOR_REF",
							1 => "SIZES_SHOES",
							2 => "SIZES_CLOTHES"
						),
						"OFFERS_SORT_FIELD" => "sort",
						"OFFERS_SORT_ORDER" => "desc",
						"OFFERS_SORT_FIELD2" => "sort",
						"OFFERS_SORT_ORDER2" => "desc",
						"OFFERS_LIMIT" => "0",
						"TEMPLATE_THEME" => "vendor",
						"ADD_PICT_PROP" => "MORE_PHOTO",
						"LABEL_PROP" => array(
							0 => "NEWPRODUCT",
							1 => "SALELEADER",
							2 => "SPECIALOFFER"
						),
						"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
						"OFFER_TREE_PROPS" => array(
							0 => "COLOR_REF",
							1 => "SIZES_SHOES",
							2 => "SIZES_CLOTHES"
						),
						"DISPLAY_NAME" => "Y",
						"DETAIL_PICTURE_MODE" => array(
							0 => "POPUP"
						),
						"ADD_DETAIL_TO_SLIDER" => "Y",
						"DISPLAY_PREVIEW_TEXT_MODE" => "E",
						"SHOW_MAX_QUANTITY" => "M",
						"MESS_BTN_BUY" => "",
						"MESS_BTN_ADD_TO_BASKET" => "",
						"MESS_BTN_SUBSCRIBE" => "",
						"MESS_BTN_COMPARE" => "",
						"MESS_NOT_AVAILABLE" => "",
						"USE_VOTE_RATING" => "Y",
						"USE_COMMENTS" => "Y",
						"BLOG_USE" => "N",
						"BLOG_URL" => "catalog_comments",
						"VK_USE" => "Y",
						"VK_API_ID" => "",
						"FB_USE" => "Y",
						"FB_APP_ID" => "",
						"BRAND_USE" => "Y",
						"BRAND_PROP_CODE" => array(
							0 => "BRAND_REF"
						),
						"SECTION_URL" => "#system_catalog#SECTION_CODE_PATH#/",
						"DETAIL_URL" => "#system_catalog#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
						"SECTION_ID_VARIABLE" => "SECTION_CODE",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600",
						"CACHE_GROUPS" => "N",
						"META_KEYWORDS" => "KEYWORDS",
						"META_DESCRIPTION" => "META_DESCRIPTION",
						"BROWSER_TITLE" => "TITLE",
						"SET_TITLE" => $setTitle,
						"SET_STATUS_404" => $setStatus404,
						"ADD_SECTIONS_CHAIN" => "Y",
						"ADD_ELEMENT_CHAIN" => "Y",
						"USE_ELEMENT_COUNTER" => "Y",
						"PRICE_VAT_SHOW_VALUE" => "N",
						"CONVERT_CURRENCY" => "N",
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
							5 => "RECOMMEND"
						),
						"OFFERS_CART_PROPERTIES" => array(
							0 => "ARTNUMBER",
							1 => "COLOR_REF",
							2 => "SIZES_SHOES",
							3 => "SIZES_CLOTHES"
						),
						"LINK_IBLOCK_TYPE" => "",
						"LINK_IBLOCK_ID" => "",
						"LINK_PROPERTY_SID" => "",
						"LINK_ELEMENTS_URL" => "",
						"VOTE_DISPLAY_AS_RATING" => "vote_avg",
						"BLOG_EMAIL_NOTIFY" => "Y",
						"SET_BROWSER_TITLE" => "Y",
						"SET_META_KEYWORDS" => "Y",
						"SET_META_DESCRIPTION" => "Y",
						"SHOW_CLOSE_POPUP" => "Y",
						"CHECK_SECTION_ID_VARIABLE" => "N",
						"SHOW_BASIS_PRICE" => "N",
						"ADD_TO_BASKET_ACTION" => array(
							0 => "BUY",
						),
						"COMPONENT_TEMPLATE" => ".default",
						"SET_CANONICAL_URL" => "Y",
						"SHOW_DEACTIVATED" => "Y",
						"SEF_MODE" => "N",
						"SET_LAST_MODIFIED" => "Y",
						"USE_MAIN_ELEMENT_SECTION" => "N",
						"SHOW_404" => "N",
						"MESSAGE_404" => "",
						"BACKGROUND_IMAGE" => "-",
						"DISABLE_INIT_JS_IN_COMPONENT" => "N",
						"SET_VIEWED_IN_COMPONENT" => "N",
						"USE_GIFTS_DETAIL" => "N",
						"USE_GIFTS_MAIN_PR_SECTION_LIST" => "N",
						"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "4",
						"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
						"GIFTS_DETAIL_BLOCK_TITLE" => "",
						"GIFTS_DETAIL_TEXT_LABEL_GIFT" => "",
						"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
						"GIFTS_SHOW_OLD_PRICE" => "Y",
						"GIFTS_SHOW_NAME" => "Y",
						"GIFTS_SHOW_IMAGE" => "Y",
						"GIFTS_MESS_BTN_BUY" => "",
						"GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "4",
						"GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
						"GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "",
						"PRODUCT_INFO_BLOCK_ORDER" => "props,sku",
						"PRODUCT_PAY_BLOCK_ORDER" => "rating,price,priceRanges,quantity,buttons,quantityLimit",
						"MAIN_BLOCK_PROPERTY_CODE" => array(
							0 => "ARTNUMBER",
							1 => "MANUFACTURER",
							2 => "MATERIAL",
						),
						"MAIN_BLOCK_OFFERS_PROPERTY_CODE" => array(
							0 => "COLOR_REF",
							1 => "SIZES_SHOES",
							2 => "SIZES_CLOTHES",
						),
						"LABEL_PROP_MOBILE" => array(
							0 => "NEWPRODUCT",
							1 => "SALELEADER",
							2 => "SPECIALOFFER",
						),
						"LABEL_PROP_POSITION" => "top-left",
						"SHOW_SLIDER" => "N",
						"DISCOUNT_PERCENT_POSITION" => "bottom-right",
						"MESS_DESCRIPTION_TAB" => "",
						"MESS_PROPERTIES_TAB" => "",
						"MESS_COMMENTS_TAB" => "",
						"STRICT_SECTION_CHECK" => "N",
						"ADD_TO_BASKET_ACTION_PRIMARY" => array(
							0 => "ADD",
						),
						"COMPATIBLE_MODE" => "N",
						"IMAGE_RESOLUTION" => "1by1",
						"MESS_PRICE_RANGES_TITLE" => "",
						"MESS_SHOW_MAX_QUANTITY" => "",
						"RELATIVE_QUANTITY_FACTOR" => "5",
						"MESS_RELATIVE_QUANTITY_MANY" => "",
						"MESS_RELATIVE_QUANTITY_FEW" => "",
						"USE_RATIO_IN_RANGES" => "N",
						"COMPARE_PATH" => "",
						"USE_COMPARE_LIST" => "Y",
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
</section>