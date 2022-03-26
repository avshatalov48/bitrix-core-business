<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$iblockId = (int)($arResult['VARIABLES']['IBLOCK_ID'] ?? 0);
$productId = (int)($arResult['VARIABLES']['PRODUCT_ID'] ?? 0);
$variationId = (int)($arResult['VARIABLES']['VARIATION_ID'] ?? 0);
$pathTo = $arResult['PATH_TO'] ?? [];

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:catalog.productcard.store.amount.details.slider',
	'',
	[
		'IBLOCK_ID' => $iblockId,
		'PRODUCT_ID' => $productId,
		'VARIATION_ID' => $variationId,
		'PATH_TO' => $pathTo,
		'BUILDER_CONTEXT' => $arResult['BUILDER_CONTEXT'],
		'SCOPE' => $arResult['SCOPE'],
	]
);
