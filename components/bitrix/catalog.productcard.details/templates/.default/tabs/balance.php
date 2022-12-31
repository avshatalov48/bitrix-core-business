<?php

/**
 * @global \CMain $APPLICATION
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 * @var $arResult
 * @var $arParams
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$APPLICATION->includeComponent(
	'bitrix:catalog.productcard.store.amount',
	'.default',
	[
		'PRODUCT_ID' => $arResult['PRODUCT_FIELDS']['ID'],
		'PRODUCT_IBLOCK_ID' => $arResult['PRODUCT_FIELDS']['IBLOCK_ID'],
		'PATH_TO' => $arParams['PATH_TO'],
	],
);
?>
<div style="clear: both;"></div>