<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\ProductTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |    Attention!
 * |    The following comments are for system use
 * |    and are required for the component to work correctly in ajax mode:
 * |    <!-- items-container -->
 * |    <!-- pagination-container -->
 * |    <!-- component-end -->
 */

$this->setFrameMode(true);

$cyclicLoading = [
	'mode' => $arParams['CYCLIC_LOADING'] === 'Y',
	'counterName' => $arParams['CYCLIC_LOADING_COUNTER_NAME'],
	'counter' => $arResult['ORIGINAL_PARAMETERS']['CYCLIC_COUNT']
];

if (!empty($arResult['NAV_RESULT']))
{
	$navParams = [
		'NavPageCount' => $arResult['NAV_RESULT']->NavPageCount,
		'NavPageNomer' => $arResult['NAV_RESULT']->NavPageNomer,
		'NavNum' => $arResult['NAV_RESULT']->NavNum,
	];
	if ($arParams['SECTIONS_OFFSET_MODE'] === 'F')
	{
		if ($navParams['NavPageNomer'] > 0 && $navParams['NavPageCount'] > 0)
		{
			$arParams['SECTIONS_OFFSET_VALUE'] = (($navParams['NavPageNomer'] - 1) * 100) / $navParams['NavPageCount'];
		}
		else
		{
			$arParams['SECTIONS_OFFSET_VALUE'] = 0;
		}
	}
	if ($arParams['CYCLIC_LOADING'] === 'Y')
	{
		if ($navParams['NavPageNomer'] >= $navParams['NavPageCount'])
		{
			$arResult['ORIGINAL_PARAMETERS']['CYCLIC_COUNT']++;
		}
	}
}
else
{
	$navParams = [
		'NavPageCount' => 1,
		'NavPageNomer' => 1,
		'NavNum' => $this->randString(),
	];
	if ($arParams['SECTIONS_OFFSET_MODE'] === 'F')
	{
		$arParams['SECTIONS_OFFSET_VALUE'] = 0;
	}
}

$showTopPager = false;
$showBottomPager = false;
$showLazyLoad = false;

if ($arParams['PAGE_ELEMENT_COUNT'] > 0 && $navParams['NavPageCount'] > 1)
{
	$showTopPager = $arParams['DISPLAY_TOP_PAGER'];
	$showBottomPager = $arParams['DISPLAY_BOTTOM_PAGER'];
	$showLazyLoad = $arParams['LAZY_LOAD'] === 'Y' && $navParams['NavPageNomer'] != $navParams['NavPageCount'];
}

$templateLibrary = ['popup', 'ajax', 'fx', 'main.loader'];
$currencyList = '';

if (!empty($arResult['CURRENCIES']))
{
	$templateLibrary[] = 'currency';
	$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = [
	'TEMPLATE_LIBRARY' => $templateLibrary,
	'CURRENCIES' => $currencyList,
	'NAV_PARAMS' => $navParams,
	'USE_PAGINATION_CONTAINER' => $showTopPager || $showBottomPager,
];
unset($currencyList, $templateLibrary);

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = ['CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM')];

$positionClassMap = [
	'left' => 'product-item-label-left',
	'center' => 'product-item-label-center',
	'right' => 'product-item-label-right',
	'bottom' => 'product-item-label-bottom',
	'middle' => 'product-item-label-middle',
	'top' => 'product-item-label-top',
];

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION']))
{
	foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos)
	{
		$labelPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$arParams['~MESS_BTN_BUY'] = ($arParams['~MESS_BTN_BUY'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_BUY');
$arParams['~MESS_BTN_DETAIL'] = ($arParams['~MESS_BTN_DETAIL'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_DETAIL');
$arParams['~MESS_BTN_COMPARE'] = ($arParams['~MESS_BTN_COMPARE'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_COMPARE');
$arParams['~MESS_BTN_SUBSCRIBE'] = ($arParams['~MESS_BTN_SUBSCRIBE'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_SUBSCRIBE');
$arParams['~MESS_BTN_ADD_TO_BASKET'] = ($arParams['~MESS_BTN_ADD_TO_BASKET'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET');
$arParams['~MESS_NOT_AVAILABLE'] = ($arParams['~MESS_NOT_AVAILABLE'] ?? '') ?: Loc::getMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE');
$arParams['~MESS_NOT_AVAILABLE_SERVICE'] = ($arParams['~MESS_NOT_AVAILABLE_SERVICE'] ?? '') ?: Loc::getMessage('CP_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE_SERVICE');
$arParams['~BTN_MESSAGE_CONTINUE_SHOPPING'] = ($arParams['~BTN_MESSAGE_CONTINUE_SHOPPING'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_BTN_MESSAGE_CONTINUE_SHOPPING');
$arParams['~BTN_MESSAGE_CREATE_ORDER'] = ($arParams['~BTN_MESSAGE_CREATE_ORDER'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_BTN_MESSAGE_CREATE_ORDER');
$arParams['~MESS_SHOW_MAX_QUANTITY'] = ($arParams['~MESS_SHOW_MAX_QUANTITY'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_SHOW_MAX_QUANTITY');
$arParams['~MESS_RELATIVE_QUANTITY_MANY'] = ($arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = ($arParams['MESS_RELATIVE_QUANTITY_MANY'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['~MESS_RELATIVE_QUANTITY_FEW'] = ($arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_FEW');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = ($arParams['MESS_RELATIVE_QUANTITY_FEW'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_FEW');

$arParams['MESS_BTN_LAZY_LOAD'] = ($arParams['MESS_BTN_LAZY_LOAD'] ?? '') ?: Loc::getMessage('CT_BCS_CATALOG_MESS_BTN_LAZY_LOAD');

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($navParams['NavNum']));
$containerName = 'container-'.$navParams['NavNum'];

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';
?>
<div class="catalog-section<?=$themeClass?> container-fluid" data-entity="<?=$containerName?>">
	<!-- items-container -->
	<div data-entity="items-row" class="row flex-wrap">
		<?php
		if (!empty($arResult['ITEMS']))
		{
			$generalParams = [
				'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
				'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
				'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
				'MESS_SHOW_MAX_QUANTITY' => $arParams['~MESS_SHOW_MAX_QUANTITY'],
				'MESS_RELATIVE_QUANTITY_MANY' => $arParams['~MESS_RELATIVE_QUANTITY_MANY'],
				'MESS_RELATIVE_QUANTITY_FEW' => $arParams['~MESS_RELATIVE_QUANTITY_FEW'],
				'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
				'USE_PRODUCT_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
				'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
				'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
				'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
				'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
				'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'],
				'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
				'COMPARE_PATH' => $arParams['COMPARE_PATH'],
				'COMPARE_NAME' => $arParams['COMPARE_NAME'],
				'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
				'PRODUCT_BLOCKS_ORDER' => $arParams['PRODUCT_BLOCKS_ORDER'],
				'LABEL_POSITION_CLASS' => $labelPositionClass,
				'~BASKET_URL' => $arParams['~BASKET_URL'],
				'~ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
				'~BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
				'~COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
				'~COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
				'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
				'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
				'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
				'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY'],
				'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
				'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
				'MESS_BTN_COMPARE' => $arParams['~MESS_BTN_COMPARE'],
				'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
				'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
				'BTN_MESSAGE_CONTINUE_SHOPPING' => $arParams['~BTN_MESSAGE_CONTINUE_SHOPPING'],
				'BTN_MESSAGE_CREATE_ORDER' => $arParams['~BTN_MESSAGE_CREATE_ORDER'],
				'USE_OFFER_NAME' => $arParams['USE_OFFER_NAME'],
				'CUSTOM_SITE_ID' => $component->getSiteId(),
			];

			$itemParameters = [];
			foreach ($arResult['ITEMS'] as $item)
			{
				$uniqueId = $item['ID'].'_'.md5($this->randString().$component->getAction()).$arResult['AREA_ID_ADDITIONAL_SALT'];
				$this->addEditAction($uniqueId, $item['EDIT_LINK'], $elementEdit);
				$this->addDeleteAction($uniqueId, $item['DELETE_LINK'], $elementDelete, $elementDeleteParams);

			?><div class="catalog-section-item-wrapper col-12 col-sm-6 d-flex align-items-stretch"><?php
				$APPLICATION->IncludeComponent(
					'bitrix:catalog.item',
					'store_v3',
					[
						'RESULT' => [
							'ITEM' => $item,
							'AREA_ID' => $this->getEditAreaId($uniqueId),
						],
						'PARAMS' => $generalParams + [
								'SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']],
								'MESS_NOT_AVAILABLE' => ($arResult['MODULES']['catalog'] && $item['PRODUCT']['TYPE'] === ProductTable::TYPE_SERVICE
									? $arParams['~MESS_NOT_AVAILABLE_SERVICE']
									: $arParams['~MESS_NOT_AVAILABLE']
								),
							],
					],
					$component,
					[
						'HIDE_ICONS' => 'Y',
					]
				);
			?></div><?php
			}
			unset($generalParams);

			if ($arParams['SHOW_SECTIONS'] === 'Y')
			{
				$APPLICATION->IncludeComponent(
					'bitrix:catalog.section.list',
					'store_v3',
					[
						'ADD_SECTIONS_CHAIN' => 'N',
						'CACHE_FILTER' => $arParams['CACHE_FILTER'],
						'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
						'CACHE_TIME' => $arParams['CACHE_TIME'],
						'CACHE_TYPE' => $arParams['CACHE_TYPE'],
						'COUNT_ELEMENTS' => 'Y',
						'COUNT_ELEMENTS_FILTER' => 'CNT_AVAILABLE', // it's no use
						'FILTER_NAME' => $arParams['SECTIONS_FILTER_NAME'],
						'IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
						'SECTION_CODE' => $arParams['SECTIONS_SECTION_CODE'],
						'SECTION_FIELDS' => ["", ""],
						'SECTION_ID' => $arParams['SECTIONS_SECTION_ID'],
						'SECTION_URL' => $arParams['SECTION_URL'],
						'SECTION_USER_FIELDS' => ["", ""],  // check and replace to $arParams['SECTION_USER_FIELDS']
						'SHOW_TITLE' => 'N',
						'TOP_DEPTH' => $arParams['SECTIONS_TOP_DEPTH'],
						'OFFSET_MODE' => $arParams['SECTIONS_OFFSET_MODE'],
						'OFFSET_VALUE' => $arParams['SECTIONS_OFFSET_VALUE'],
						'OFFSET_VARIABLE' => $arParams['SECTIONS_OFFSET_VARIABLE'],
						'AREA_ID' => $arResult['AREA_ID_ADDITIONAL_SALT']
					],
					$component,
					[
						'HIDE_ICONS' => 'Y',
					]
				);
			}
		}
		else
		{
			// load css for bigData/deferred load
			$APPLICATION->IncludeComponent(
				'bitrix:catalog.item',
				'store_v3',
				[],
				$component,
				[
					'HIDE_ICONS' => 'Y',
				]
			);
		}
		?>
	</div>
	<!-- items-container -->
</div>
<?php
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'catalog.section');
$signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.section');

$templateData['SIGNED_PARAMETERS'] = $signedParams;

?>
<script>
	BX.message({
		BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
		BASKET_URL: '<?=$arParams['BASKET_URL']?>',
		ADD_TO_BASKET_OK: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
		TITLE_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_ERROR')?>',
		TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_BASKET_PROPS')?>',
		TITLE_SUCCESSFUL: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
		BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_BASKET_UNKNOWN_ERROR')?>',
		BTN_MESSAGE_SEND_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_SEND_PROPS')?>',
		BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE')?>',
		BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
		COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_OK')?>',
		COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
		COMPARE_TITLE: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_TITLE')?>',
		PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCS_CATALOG_PRICE_TOTAL_PREFIX')?>',
		RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
		RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
		BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
		BTN_MESSAGE_LAZY_LOAD: '<?=CUtil::JSEscape($arParams['MESS_BTN_LAZY_LOAD'])?>',
		BTN_MESSAGE_LAZY_LOAD_WAITER: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_LAZY_LOAD_WAITER')?>',
		SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
	});
	var <?=$obName?> = new JCCatalogSectionComponent({
		siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
		componentPath: '<?=CUtil::JSEscape($componentPath)?>',
		navParams: <?=CUtil::PhpToJSObject($navParams)?>,
		deferredLoad: <?=($arParams['DEFERRED_LOAD'] === 'Y') ? 'true' : 'false' ?>, // enable it for deferred load
		initiallyShowHeader: '<?=!empty($arResult['ITEM_ROWS'])?>',
		bigData: <?=CUtil::PhpToJSObject($arResult['BIG_DATA'])?>,
		lazyLoad: !!'<?=$showLazyLoad?>',
		loadOnScroll: <?=($arParams['LOAD_ON_SCROLL'] === 'Y') ? 'true' : 'false' ?>,
		cyclicLoading: <?=CUtil::PhpToJSObject($cyclicLoading, false, false, true) ?>,
		template: '<?=CUtil::JSEscape($signedTemplate)?>',
		ajaxId: '<?=CUtil::JSEscape($arParams['AJAX_ID'])?>',
		parameters: '<?=CUtil::JSEscape($signedParams)?>',
		container: '<?=$containerName?>'
	});
</script>
<!-- component-end -->
