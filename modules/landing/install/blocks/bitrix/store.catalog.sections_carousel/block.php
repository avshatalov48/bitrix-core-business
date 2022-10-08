<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreCatalogSectionsCarousel $classBlock
 * @global \CMain $APPLICATION
 */

$sectionId = $classBlock->get('SECTION_ID');
$sectionCode = $classBlock->get('SECTION_CODE');
$sectionUrl = $classBlock->get('SECTION_URL');
$detailUrl = $classBlock->get('DETAIL_URL');
$showElementSection = (int)$sectionId > 0 && (int)$sectionId !== (int)$classBlock->get('LANDING_SECTION_ID');
$editMode = $classBlock->get('EDIT_MODE');

// for replace in public mode
if ($sectionUrl)
{
	$sectionUrl = '#system_catalog#SECTION_CODE_PATH#/';
}
if ($detailUrl)
{
	$detailUrl = '#system_catalogitem/#ELEMENT_CODE#/';
}

?>


<section class="landing-block g-pt-20 g-pb-20">
	<?php
	// pass only params from settings or calculated by classBlock and the same for both components
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.catalog_section_with_carousel',
		'.default',
		[
			// settings
			'SECTION_ID' => $sectionId,
			'ALLOW_SEO_DATA' => $classBlock->get('ALLOW_SEO_DATA'),
			'HIDE_NOT_AVAILABLE' => $classBlock->get('HIDE_NOT_AVAILABLE'),
			'HIDE_NOT_AVAILABLE_OFFERS' => $classBlock->get('HIDE_NOT_AVAILABLE_OFFERS'),
			'ELEMENT_SORT_FIELD' => 'sort',
			'ELEMENT_SORT_ORDER' => 'desc',
			'CURRENCY_ID' => $classBlock->get('CURRENCY_ID'),
			'PRICE_CODE' => $classBlock->get('PRICE_CODE'),
			'USE_PRICE_COUNT' => $classBlock->get('USE_PRICE_COUNT'),
			'SHOW_PRICE_COUNT' => $classBlock->get('SHOW_PRICE_COUNT'),
			'PRICE_VAT_INCLUDE' => $classBlock->get('PRICE_VAT_INCLUDE'),
			'DISPLAY_COMPARE' => 'N',
			'USE_PRODUCT_QUANTITY' => $classBlock->get('USE_PRODUCT_QUANTITY'),
			'SHOW_DISCOUNT_PERCENT' => $classBlock->get('SHOW_DISCOUNT_PERCENT'),
			'SHOW_OLD_PRICE' => $classBlock->get('SHOW_OLD_PRICE'),
			'ADD_TO_BASKET_ACTION' => 'BUY',
			'MESS_BTN_BUY' => '',
			'MESS_BTN_ADD_TO_BASKET' => '',
			'MESS_BTN_SUBSCRIBE' => '',
			'MESS_NOT_AVAILABLE' => '',
			'USE_ENHANCED_ECOMMERCE' => $classBlock->get('USE_ENHANCED_ECOMMERCE'),
			'DATA_LAYER_NAME' => $classBlock->get('DATA_LAYER_NAME'),
			'BRAND_PROPERTY' => $classBlock->get('BRAND_PROPERTY'),
			'LABEL_PROP_POSITION' => 'top-left',
			'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
			'SHOW_SECTIONS' => '',
			'BASKET_URL' => '#system_order',

			// computed then same for both
			'IBLOCK_ID' => $classBlock->get('IBLOCK_ID'),
			'SECTION_URL' => $sectionUrl,
			'DETAIL_URL' => $detailUrl,
			'HIDE_DETAIL_URL' => $classBlock->get('HIDE_DETAIL_URL'),
			'ACTION_VARIABLE' => $classBlock->get('ACTION_VARIABLE'),
			'CUSTOM_SITE_ID' => $classBlock->get('SITE_ID'),
			'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
			'SECTIONS_SECTION_ID' => $classBlock->get('LANDING_SECTION_ID'),

			// differents
			'ADDITIONAL' => [
				'EDIT_MODE' => $editMode,
				'SHOW_ELEMENT_SECTION' => $showElementSection,
				'SECTION_ID' => $sectionId,
				'LANDING_SECTION_ID' => $classBlock->get('LANDING_SECTION_ID'),
				'SECTION_CODE' => $classBlock->get('SECTION_CODE'),
				'FILTER_NAME' => $classBlock->get('FILTER_NAME'),
				'SECTIONS_FILTER_NAME' => $classBlock->get('SECTIONS_FILTER_NAME'),
				'CATALOG_FILTER_NAME' => $classBlock->get('CATALOG_FILTER_NAME'),
				'SET_TITLE' => $classBlock->get('SET_TITLE'),
				'ALLOW_SEO_DATA' => $classBlock->get('ALLOW_SEO_DATA'),
			],
		],
		false
	); ?>
</section>
