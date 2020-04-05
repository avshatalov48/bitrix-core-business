<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CUtil::initJSCore(array('fx'));

$settings = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
	isset($landing) ? $landing->getSiteId() : null
);
?>
<section class="landing-block g-pt-100 g-pb-100">
	<div class="container g-font-size-13">
		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.basket.basket",
			"bootstrap_v4",
			array(
				"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
				"COLUMNS_LIST" => array(
					0 => "NAME",
					1 => "DISCOUNT",
					2 => "PRICE",
					3 => "QUANTITY",
					4 => "SUM",
					5 => "PROPS",
					6 => "DELETE",
					7 => "DELAY",
				),
				"AJAX_MODE" => "N",
				"AJAX_OPTION_JUMP" => "N",
				"AJAX_OPTION_STYLE" => "Y",
				"AJAX_OPTION_HISTORY" => "N",
				"PATH_TO_ORDER" => "#system_order",
				"HIDE_COUPON" => "N",
				"QUANTITY_FLOAT" => "N",
				"PRICE_VAT_SHOW_VALUE" => "Y",
				"TEMPLATE_THEME" => "vendor",
				"SET_TITLE" => "N",
				"AJAX_OPTION_ADDITIONAL" => "",
				"OFFERS_PROPS" => array(
					0 => "SIZES_SHOES",
					1 => "SIZES_CLOTHES",
					2 => "COLOR_REF",
				),
				"GIFTS_DETAIL_URL" => "#system_catalogitem/#ELEMENT_CODE#/",
				"PRICE_CODE" => $settings['PRICE_CODE'],
				"USE_ENHANCED_ECOMMERCE" => $settings['USE_ENHANCED_ECOMMERCE'],
				"DATA_LAYER_NAME" => $settings['DATA_LAYER_NAME'],
				"BRAND_PROPERTY" => $settings['BRAND_PROPERTY']
			),
		 	false
		);?>
	</div>
</section>
