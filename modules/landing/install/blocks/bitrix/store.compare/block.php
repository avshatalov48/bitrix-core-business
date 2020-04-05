<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreCompareBlock $classBlock
 */
?>
<section class="landing-block g-pt-100 g-pb-100">
	<div class="container g-font-size-13">
		<?$APPLICATION->IncludeComponent(
			'bitrix:catalog.compare.result',
			'',
			array(
				'IBLOCK_TYPE' => '',
				'IBLOCK_ID' => $classBlock->get('IBLOCK_ID'),
				'BASKET_URL' => '#system_cart',
				'ACTION_VARIABLE' => 'action_ccr',
				'PRODUCT_ID_VARIABLE' => 'id',
				'SECTION_ID_VARIABLE' => 'section_id',
				'FIELD_CODE' => array(),
				'PROPERTY_CODE' => array(
					0 => 'ARTNUMBER',
					1 => 'MANUFACTURER',
					2 => 'MATERIAL',
				),
				'NAME' => 'CATALOG_COMPARE_LIST',
				'CACHE_TYPE' => 'A',
				'CACHE_TIME' => '36000000',
				'CACHE_GROUPS' => 'N',
				'PRICE_VAT_SHOW_VALUE' => 'Y',
				'ELEMENT_SORT_FIELD' => 'sort',
				'ELEMENT_SORT_ORDER' => 'asc',
				'DETAIL_URL' => '#system_catalogitem/#ELEMENT_CODE#/',
				'OFFERS_FIELD_CODE' => array(),
				'OFFERS_PROPERTY_CODE' => array(
					0 => 'COLOR_REF',
					1 => 'SIZES_SHOES',
					2 => 'SIZES_CLOTHES',
				),
				'OFFERS_CART_PROPERTIES' => array(
					0 => 'ARTNUMBER',
					1 => 'COLOR_REF',
					2 => 'SIZES_SHOES',
					3 => 'SIZES_CLOTHES'
				),
				'CONVERT_CURRENCY' => 'Y',
				'TEMPLATE_THEME' => 'red',
				'HIDE_NOT_AVAILABLE' => $classBlock->get('HIDE_NOT_AVAILABLE'),
				'HIDE_NOT_AVAILABLE_OFFERS' => $classBlock->get('HIDE_NOT_AVAILABLE_OFFERS'),
				'PRODUCT_SUBSCRIPTION' => $classBlock->get('PRODUCT_SUBSCRIPTION'),
				'USE_PRODUCT_QUANTITY' => $classBlock->get('USE_PRODUCT_QUANTITY'),
				'DISPLAY_COMPARE' => $classBlock->get('DISPLAY_COMPARE'),
				'PRICE_CODE' => $classBlock->get('PRICE_CODE'),
				'USE_PRICE_COUNT' => $classBlock->get('USE_PRICE_COUNT'),
				'SHOW_PRICE_COUNT' => $classBlock->get('SHOW_PRICE_COUNT'),
				'CURRENCY_ID' => $classBlock->get('CURRENCY_ID'),
				'PRICE_VAT_INCLUDE' => $classBlock->get('PRICE_VAT_INCLUDE'),
				'SHOW_OLD_PRICE' => $classBlock->get('SHOW_OLD_PRICE'),
				'SHOW_DISCOUNT_PERCENT' => $classBlock->get('SHOW_DISCOUNT_PERCENT'),
				'USE_ENHANCED_ECOMMERCE' => $classBlock->get('USE_ENHANCED_ECOMMERCE'),
				'DATA_LAYER_NAME' => $classBlock->get('DATA_LAYER_NAME'),
				'BRAND_PROPERTY' => $classBlock->get('BRAND_PROPERTY'),
				'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID')
			),
			false
		);?>
	</div>
</section>
