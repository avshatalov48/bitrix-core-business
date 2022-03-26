<?php

/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
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
	],
);
?>
<div style="clear: both;"></div>