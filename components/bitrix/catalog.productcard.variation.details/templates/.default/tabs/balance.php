<?php

/**
 * @global \CMain $APPLICATION
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$APPLICATION->includeComponent(
	'bitrix:catalog.productcard.store.amount',
	'.default',
	[
		'PRODUCT_ID' => $arResult['VARIATION_FIELDS']['ID'],
		'PRODUCT_IBLOCK_ID' => null,
		'PATH_TO' => $arParams['PATH_TO'],
	],
);
?>
<div style="clear: both;"></div>