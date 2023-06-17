<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateFolder
 * @var CBitrixComponent $component
 */

$this->setFrameMode(true);

if (isset($arResult['ITEM']))
{
	$messages = [
		'CLOSE_BTN' => Loc::getMessage('CT_BCI_TPL_MESS_CLOSE_BTN_TITLE')
	];

	$item = $arResult['ITEM'];
	$areaId = $arResult['AREA_ID'];
	$itemIds = [
		'ID' => $areaId,
		'NAME' => $areaId.'_name',
		'POPUP' => $areaId.'_popup',
		'PREBUY' => $areaId.'_prebuy',
		'PICT_SLIDER' => $areaId.'_pict_slider',
		'STICKER_ID' => $areaId.'_sticker',
		'SECOND_STICKER_ID' => $areaId.'_secondsticker',
		'QUANTITY' => $areaId.'_quantity',
		'QUANTITY_COUNTER' => $areaId.'_counter',
		'QUANTITY_DOWN' => $areaId.'_quant_down',
		'QUANTITY_UP' => $areaId.'_quant_up',
		'QUANTITY_MEASURE' => $areaId.'_quant_measure',
		'QUANTITY_MEASURE_CONTAINER' => $areaId.'_quant_measure_container',
		'QUANTITY_LIMIT' => $areaId.'_quant_limit',
		'BUY_LINK' => $areaId.'_buy_link',
		'ADD_BASKET_LINK' => $areaId.'_add_basket_link',
		'BASKET_ACTIONS' => $areaId.'_basket_actions',
		'NOT_AVAILABLE_MESS' => $areaId.'_not_avail',
		'SUBSCRIBE_LINK' => $areaId.'_subscribe',
		'COMPARE_LINK' => $areaId.'_compare_link',
		'PRICE' => $areaId.'_price',
		'PRICE_TWIN' => $areaId.'_price_twin',
		'BLOCK_PRICE_OLD' => $areaId.'_block_price',
		'BLOCK_PRICE_OLD_TWIN' => $areaId.'_block_price_twin',
		'PRICE_OLD' => $areaId.'_price_old',
		'PRICE_OLD_TWIN' => $areaId.'_price_old_twin',
		'PRICE_DISCOUNT' => $areaId.'_price_discount',
		'PRICE_DISCOUNT_TWIN' => $areaId.'_price_discount_twin',
		'PRICE_TOTAL' => $areaId.'_price_total',
		'PROP_DIV' => $areaId.'_sku_tree',
		'PROP' => $areaId.'_prop_',
		'DISPLAY_PROP_DIV' => $areaId.'_sku_prop',
		'BASKET_PROP_DIV' => $areaId.'_basket_prop',
	];
	$itemIds['PREBUY_OPEN_BTN'] = $itemIds['PREBUY'].'_open_btn';
	$itemIds['PREBUY_OVERLAY'] = $itemIds['PREBUY'].'_overlay';
	$itemIds['PREBUY_CONTAINER'] = $itemIds['PREBUY'].'_container';
	$itemIds['PREBUY_SWIPE_BTN'] = $itemIds['PREBUY'].'_swipe_btn';
	$itemIds['PREBUY_CLOSE_BTN'] = $itemIds['PREBUY'].'_close_btn';
	$itemIds['PREBUY_NAME'] = $itemIds['PREBUY'].'_name';
	$itemIds['PREBUY_PICT'] = $itemIds['PREBUY'].'_pict';

	$obName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $areaId);

	$productTitle = isset($item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
		? $item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
		: $item['NAME'];

	$imgTitle = isset($item['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $item['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
		? $item['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
		: $item['NAME'];

	$resizedSlider = [
		'X' => [],
		'X2' => [],
	];

	foreach ($item['MORE_PHOTO'] as $morePhoto)
	{
		if ($morePhoto['ID'] === 0)
		{
			$photoFile = $morePhoto;
		}
		else
		{
			$photoFile = $morePhoto['ID'];
		}
		$xResizedImage = \CFile::ResizeImageGet(
			$photoFile,
			[
				'width' => 410,
				'height' => 410,
			],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$x2ResizedImage = \CFile::ResizeImageGet(
			$photoFile,
			[
				'width' => 820,
				'height' => 820,
			],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$xResizedImage['src'] = \Bitrix\Iblock\Component\Tools::getImageSrc([
			'SRC' => $xResizedImage['src']
		]);
		$x2ResizedImage['src'] = \Bitrix\Iblock\Component\Tools::getImageSrc([
			'SRC' => $x2ResizedImage['src']
		]);

		$resizedSlider['X'][] = [
			'ID' => $morePhoto['ID'],
			'SRC' => $xResizedImage['src'],
			'WIDTH' => $xResizedImage['width'],
			'HEIGHT' => $xResizedImage['height'],
		];
		$resizedSlider['X2'][] = [
			'ID' => $morePhoto['ID'],
			'SRC' => $x2ResizedImage['src'],
			'WIDTH' => $x2ResizedImage['width'],
			'HEIGHT' => $x2ResizedImage['height'],
		];
	}

	$jsParams = [
		'PRODUCT_TYPE' => $item['PRODUCT']['TYPE'],
		'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
		'SITE_ID' => $component->getSiteId(),
		'SHOW_ABSENT' => true, // are you sure
		'SECOND_PICT' => $item['SECOND_PICT'], // are you sure
		'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
		'SHOW_MAX_QUANTITY' => 'N', // $arParams['SHOW_MAX_QUANTITY'],
		'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
		'SHOW_SKU_PROPS' => false, // overwrite later
		'USE_SUBSCRIBE' => false, // from $arParams
		'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'], // need to change string to array
		'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
		'DISPLAY_COMPARE' => false, // $arParams['DISPLAY_COMPARE'],
		'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
		'USE_OFFER_NAME' => $arParams['USE_OFFER_NAME'] === 'Y',
		'BIG_DATA' => $item['BIG_DATA'],
		'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
		'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
		'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
		'BRAND_PROPERTY' => (!empty($item['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
			? $item['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
			: null
		),
		'VISUAL' => [
			'ID' => $itemIds['ID'],
			'NAME' => $itemIds['NAME'],
			'PICT_SLIDER_ID' => $itemIds['PICT_SLIDER'],

			'PRICE_ID' => $itemIds['PRICE'],
			'PRICE_TWIN_ID' => $itemIds['PRICE_TWIN'],

			'BUY_ID' => $itemIds['BUY_LINK'],
			'ADD_BASKET_ID' => $itemIds['ADD_BASKET_LINK'],

			'PREBUY' => $itemIds['PREBUY'],
			'PREBUY_SWIPE_BTN' => $itemIds['PREBUY_SWIPE_BTN'],
			'PREBUY_CLOSE_BTN' => $itemIds['PREBUY_CLOSE_BTN'],
			'PREBUY_OPEN_BTN' => $itemIds['PREBUY_OPEN_BTN'],
			'PREBUY_OVERLAY' => $itemIds['PREBUY_OVERLAY'],
			'PREBUY_CONTAINER' => $itemIds['PREBUY_CONTAINER'],
			'PREBUY_NAME' => $itemIds['PREBUY_NAME'],
			'PREBUY_PICT' => $itemIds['PREBUY_PICT'],

			// two next rows need refactor
			'BASKET_ACTIONS_ID' => $itemIds['BASKET_ACTIONS'],
			'NOT_AVAILABLE_MESS' => $itemIds['NOT_AVAILABLE_MESS'],
		],
		'PRODUCT' => [
			'ID' => $item['ID'],
			'NAME' => $productTitle,
			'DETAIL_PAGE_URL' => $item['DETAIL_PAGE_URL'],
			'MORE_PHOTO' => $item['MORE_PHOTO'],
			'MORE_PHOTO_COUNT' => $item['MORE_PHOTO_COUNT'],
			'RESIZED_SLIDER' => $resizedSlider,
		],
		'BASKET' => [
			'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
			'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
			'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
			'BASKET_URL' => $arParams['~BASKET_URL'],
			'ADD_URL_TEMPLATE' => $arParams['~ADD_URL_TEMPLATE'],
			'BUY_URL_TEMPLATE' => $arParams['~BUY_URL_TEMPLATE'],
		],
	];

	unset($xResizedImage, $x2ResizedImage, $resizedSlider);

	if ($jsParams['SHOW_QUANTITY'])
	{
		$jsParams['VISUAL']['QUANTITY_ID'] = $itemIds['QUANTITY'];
		$jsParams['VISUAL']['QUANTITY_UP_ID'] = $itemIds['QUANTITY_UP'];
		$jsParams['VISUAL']['QUANTITY_COUNTER_ID'] = $itemIds['QUANTITY_COUNTER'];
		$jsParams['VISUAL']['QUANTITY_DOWN_ID'] = $itemIds['QUANTITY_DOWN'];
		$jsParams['VISUAL']['QUANTITY_MEASURE'] = $itemIds['QUANTITY_MEASURE'];
		$jsParams['VISUAL']['QUANTITY_MEASURE_CONTAINER'] = $itemIds['QUANTITY_MEASURE_CONTAINER'];

		$jsParams['VISUAL']['PRICE_TOTAL_ID'] = $itemIds['PRICE_TOTAL'];

		if ($jsParams['SHOW_MAX_QUANTITY'] === 'Y')
		{
			$jsParams['VISUAL']['QUANTITY_LIMIT'] = $itemIds['QUANTITY_LIMIT'];
		}
	}

	if ($jsParams['SHOW_OLD_PRICE'])
	{
		$jsParams['VISUAL']['BLOCK_PRICE_OLD_ID'] = $itemIds['BLOCK_PRICE_OLD'];
		$jsParams['VISUAL']['BLOCK_PRICE_OLD_TWIN_ID'] = $itemIds['BLOCK_PRICE_OLD_TWIN'];
		$jsParams['VISUAL']['PRICE_OLD_ID'] = $itemIds['PRICE_OLD'];
		$jsParams['VISUAL']['PRICE_OLD_TWIN_ID'] = $itemIds['PRICE_OLD_TWIN'];
		$jsParams['VISUAL']['PRICE_DISCOUNT_ID'] = $itemIds['PRICE_DISCOUNT'];
		$jsParams['VISUAL']['PRICE_DISCOUNT_TWIN_ID'] = $itemIds['PRICE_DISCOUNT_TWIN'];
	}

	if ($jsParams['DISPLAY_COMPARE'])
	{
		$jsParams['VISUAL']['COMPARE_LINK_ID'] = $itemIds['COMPARE_LINK'];
	}

	if ($jsParams['USE_SUBSCRIBE'])
	{
		$jsParams['VISUAL']['SUBSCRIBE_ID'] = $itemIds['SUBSCRIBE_LINK'];
	}

	$templateData = [
		'JS_OBJ' => $obName,
		'ITEM' => [
			'ID' => $item['ID'],
			'IBLOCK_ID' => $item['IBLOCK_ID'],
		],
	];
	$documentRoot = Main\Application::getDocumentRoot();
	$file = new Main\IO\File($documentRoot.$templateFolder.'/'.
		(isset($item['PRODUCT']['USE_OFFERS']) && $item['PRODUCT']['USE_OFFERS']
			? 'sku.php'
			: 'simple.php'
		)
	);
	if ($file->isExists())
	{
		/** @noinspection PhpIncludeInspection */
		include($file->getPath());
	}

	if ($jsParams['DISPLAY_COMPARE'])
	{
		$jsParams['COMPARE'] = [
			'COMPARE_URL_TEMPLATE' => $arParams['~COMPARE_URL_TEMPLATE'],
			'COMPARE_DELETE_URL_TEMPLATE' => $arParams['~COMPARE_DELETE_URL_TEMPLATE'],
			'COMPARE_PATH' => $arParams['COMPARE_PATH'],
		];
	}

	if ($jsParams['BIG_DATA'])
	{
		$jsParams['PRODUCT']['RCM_ID'] = $item['RCM_ID'];
	}

	$jsParams['IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED'] =
		$arResult['IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED']
	;

	?>
	<script>
		var <?=$obName?> = new JCCatalogItem(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
	</script>
	<?php
}