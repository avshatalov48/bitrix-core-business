<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

$this->setFrameMode(true);

if (!empty($arResult['RECOMMENDED_IDS']))
{
	global $recommendedFilter;

	$recommendedFilter = array(
		"=ID" => $arResult['RECOMMENDED_IDS'],
	);

	if (empty($arParams['HIDE_BLOCK_TITLE']) || $arParams['HIDE_BLOCK_TITLE'] !== 'Y')
	{
		?>
		<h4 class="bx_item_list_title">
			<?=($arParams['BLOCK_TITLE'] ? htmlspecialcharsbx($arParams['BLOCK_TITLE']) : GetMessage('CATALOG_RECOMMENDED_PRODUCTS_HREF_TITLE'))?>
		</h4>
		<?
	}

	if (!empty($arParams['SHOW_PRODUCTS']))
	{
		$iblockId = key($arParams['SHOW_PRODUCTS']);
	}
	else
	{
		$iblockId = $arParams['IBLOCK_ID'];
	}

	$arSku = CCatalogSku::GetInfoByProductIBlock($iblockId);
	$offerIblockId = !empty($arSku) ? $arSku['IBLOCK_ID'] : 0;

	CBitrixComponent::includeComponentClass('bitrix:catalog.section');
	$APPLICATION->IncludeComponent(
		'bitrix:catalog.section',
		'bootstrap_v4',
		array(

			'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
			'IBLOCK_ID' => $iblockId,

			'PRODUCT_ROW_VARIANTS' => \Bitrix\Main\Web\Json::encode(
				CatalogSectionComponent::predictRowVariants(
					$arParams['LINE_ELEMENT_COUNT'],
					$arParams['PAGE_ELEMENT_COUNT']
				)
			),
			'PAGE_ELEMENT_COUNT' => $arParams['PAGE_ELEMENT_COUNT'],
			'LINE_ELEMENT_COUNT' => $arParams['LINE_ELEMENT_COUNT'],

			'CACHE_TYPE' => $arParams['CACHE_TYPE'],
			'CACHE_TIME' => $arParams['CACHE_TIME'],
			'CACHE_FILTER' => $arParams['CACHE_FILTER'],
			'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],

			'BY_LINK' => 'N',
			'DISPLAY_TOP_PAGER' => 'N',
			'DISPLAY_BOTTOM_PAGER' => 'N',
			'HIDE_SECTION_DESCRIPTION' => 'Y',
			'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
			'SHOW_ALL_WO_SECTION' => 'Y',

			'ELEMENT_SORT_FIELD' => $arParams['ELEMENT_SORT_FIELD'],
			'ELEMENT_SORT_ORDER' => $arParams['ELEMENT_SORT_ORDER'],
			'ELEMENT_SORT_FIELD2' => $arParams['ELEMENT_SORT_FIELD2'],
			'ELEMENT_SORT_ORDER2' => $arParams['ELEMENT_SORT_ORDER2'],

			'FILTER_NAME' => 'recommendedFilter',
			'SECTION_URL' => $arParams['SECTION_URL'],
			'DETAIL_URL' => $arParams['DETAIL_URL'],
			'BASKET_URL' => $arParams['BASKET_URL'],
			'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
			'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
			'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],

			'SET_TITLE' => $arParams['SET_TITLE'],
			'PRICE_CODE' => $arParams['PRICE_CODE'],
			'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
			'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],

			'PROPERTY_CODE' => (isset($arParams['PROPERTY_CODE'][$iblockId]) ? $arParams['PROPERTY_CODE'][$iblockId] : ''),
			'PROPERTY_CODE_MOBILE' => (isset($arParams['PROPERTY_CODE_MOBILE']) ? $arParams['PROPERTY_CODE_MOBILE'] : ''),

			'OFFERS_FIELD_CODE' => (isset($arParams['OFFERS_FIELD_CODE']) ? $arParams['FIELD_CODE'] : ''),
			'OFFERS_PROPERTY_CODE' => (isset($arParams['PROPERTY_CODE'][$offerIblockId]) ? $arParams['PROPERTY_CODE'][$offerIblockId] : ''),
			'OFFERS_CART_PROPERTIES' => (isset($arParams['CART_PROPERTIES'][$offerIblockId]) ? $arParams['CART_PROPERTIES'][$offerIblockId] : ''),

			'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
			'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
			'CURRENCY_ID' => $arParams['CURRENCY_ID'],
			'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
			'HIDE_NOT_AVAILABLE_OFFERS' => $arParams['HIDE_NOT_AVAILABLE_OFFERS'],
			'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
			'PRODUCT_BLOCKS_ORDER' => $arParams['PRODUCT_BLOCKS_ORDER'],

			'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
			'SLIDER_INTERVAL' => isset($arParams['SLIDER_INTERVAL']) ? $arParams['SLIDER_INTERVAL'] : '',
			'SLIDER_PROGRESS' => isset($arParams['SLIDER_PROGRESS']) ? $arParams['SLIDER_PROGRESS'] : '',

			'LABEL_PROP' => (isset($arParams['LABEL_PROP_MULTIPLE']) ? $arParams['LABEL_PROP_MULTIPLE'] : ''),
			'LABEL_PROP_MOBILE' => (isset($arParams['LABEL_PROP_MOBILE']) ? $arParams['LABEL_PROP_MOBILE'] : ''),
			'LABEL_PROP_POSITION' => (isset($arParams['LABEL_PROP_POSITION']) ? $arParams['LABEL_PROP_POSITION'] : ''),
			'ADD_PICT_PROP' => (isset($arParams['ADDITIONAL_PICT_PROP'][$iblockId]) ? $arParams['ADDITIONAL_PICT_PROP'][$iblockId] : ''),
			'OFFER_ADD_PICT_PROP' => (isset($arParams['ADDITIONAL_PICT_PROP'][$offerIblockId]) ? $arParams['ADDITIONAL_PICT_PROP'][$offerIblockId] : ''),
			'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS'][$offerIblockId]) ? $arParams['OFFER_TREE_PROPS'][$offerIblockId] : ''),

			'SHOW_DISCOUNT_PERCENT' => (isset($arParams['SHOW_DISCOUNT_PERCENT']) ? $arParams['SHOW_DISCOUNT_PERCENT'] : ''),
			'DISCOUNT_PERCENT_POSITION' => (isset($arParams['DISCOUNT_PERCENT_POSITION']) ? $arParams['DISCOUNT_PERCENT_POSITION'] : ''),
			'SHOW_OLD_PRICE' => (isset($arParams['SHOW_OLD_PRICE']) ? $arParams['SHOW_OLD_PRICE'] : ''),
			'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],

			'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
			'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
			'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
			'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),

			'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
			'SHOW_CLOSE_POPUP' => (isset($arParams['SHOW_CLOSE_POPUP']) ? $arParams['SHOW_CLOSE_POPUP'] : ''),
			'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
			'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : ''),

			'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
			'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
			'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY'],
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}