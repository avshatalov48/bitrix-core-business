<?php

/**
 * @var $component \CatalogProductDetailsComponent
 * @var $arParams array
 * @var $arResult array
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$catalogId = 0;
$productBuilderContext = null;
if (\Bitrix\Main\Loader::includeModule('crm'))
{
	$catalogId = \Bitrix\Crm\Product\Catalog::getDefaultId();
}

$APPLICATION->IncludeComponent(
	'bitrix:catalog.store.document.product.list',
	'.default',
	[
		'ALLOW_EDIT' => !$arResult['IS_MAIN_CARD_READ_ONLY'] ? 'Y' : 'N',
		'CATALOG_ID' => $catalogId,
		'CURRENCY' => $arResult['FORM']['ENTITY_DATA']['CURRENCY'] ?? null,
		'BUILDER_CONTEXT' => \Bitrix\Catalog\Url\InventoryBuilder::TYPE_ID,
		'ALLOW_ADD_PRODUCT' => 'Y',
		'ALLOW_CREATE_NEW_PRODUCT' => 'Y',
		'DOCUMENT_ID' => $arResult['FORM']['ENTITY_DATA']['ID'] ?? null,
		'DOCUMENT_TYPE' => $arResult['FORM']['ENTITY_DATA']['DOC_TYPE'] ?? null,
		'PRODUCT_DATA_FIELD_NAME' => 'DOCUMENT_PRODUCTS',
		'PRESELECTED_PRODUCT_ID' => $arParams['PRESELECTED_PRODUCT_ID'],
		'PRODUCTS' => $arParams['PRELOADED_FIELDS']['PRODUCTS'] ?? null,
	],
	$component
);
