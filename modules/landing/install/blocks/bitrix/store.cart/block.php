<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \CMain $APPLICATION
 * @var StoreCartBlock $classBlock
 */

$iblockId = $classBlock->get('IBLOCK_ID') ?? '';
$skuIblockId = $classBlock->get('SKU_IBLOCK_ID') ?? '';
$detailUrl = '#system_catalogitem/#ELEMENT_CODE#/';
?>
<section class="landing-block g-pt-100 g-pb-100">
	<div class="container g-font-size-13">
		<?$APPLICATION->IncludeComponent(
			'bitrix:sale.basket.basket',
			'bootstrap_v4',
			array(
				'COUNT_DISCOUNT_4_ALL_QUANTITY' => 'N',
				'COLUMNS_LIST' => array(
					'NAME',
					'DISCOUNT',
					'PRICE',
					'QUANTITY',
					'SUM',
					'DELETE',
					'DELAY',
				),
				'ADDITIONAL_PICT_PROP' => array(
					$iblockId => 'MORE_PHOTO',
					$skuIblockId => 'MORE_PHOTO',
				),
				'AJAX_MODE' => 'N',
				'AJAX_OPTION_JUMP' => 'N',
				'AJAX_OPTION_STYLE' => 'Y',
				'AJAX_OPTION_HISTORY' => 'N',
				'PATH_TO_ORDER' => '#system_order',
				'HIDE_COUPON' => 'N',
				'QUANTITY_FLOAT' => 'N',
				'PRICE_VAT_SHOW_VALUE' => 'Y',
				'TEMPLATE_THEME' => 'vendor',
				'SET_TITLE' => 'N',
				'AJAX_OPTION_ADDITIONAL' => '',
				'OFFERS_PROPS' => array(
					'SIZES_SHOES',
					'SIZES_CLOTHES',
					'COLOR_REF',
				),
				'GIFTS_DETAIL_URL' => '#system_catalogitem/#ELEMENT_CODE#/',
				'PRICE_CODE' => $classBlock->get('PRICE_CODE'),
				'USE_ENHANCED_ECOMMERCE' => $classBlock->get('USE_ENHANCED_ECOMMERCE'),
				'DATA_LAYER_NAME' => $classBlock->get('DATA_LAYER_NAME'),
				'BRAND_PROPERTY' => $classBlock->get('BRAND_PROPERTY'),
				'EMPTY_BASKET_HINT_PATH' => $classBlock->get('EMPTY_PATH'),
				'DEFERRED_REFRESH' => 'N',
				'SHOW_FILTER' => 'N',
				'TOTAL_BLOCK_DISPLAY' => ['top', 'bottom'],
				'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
				'DETAIL_URL' => $detailUrl
			),
		 	false
		);?>
	</div>
</section>
