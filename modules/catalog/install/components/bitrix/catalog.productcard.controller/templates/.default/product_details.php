<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */

$iblockId = (int)($arResult['VARIABLES']['IBLOCK_ID'] ?? 0);
$productId = (int)($arResult['VARIABLES']['PRODUCT_ID'] ?? 0);
$productTypeId = (int)($arResult['ADDITIONAL_TEMPLATE_PARAMETERS']['PRODUCT_TYPE_ID'] ?? 0);

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:catalog.productcard.details',
	'',
	[
		'PATH_TO' => $arResult['PATH_TO'],
		'IBLOCK_ID' => $iblockId,
		'PRODUCT_ID' => $productId,
		'PRODUCT_TYPE_ID' => $productTypeId,
		'BUILDER_CONTEXT' => $arResult['BUILDER_CONTEXT'],
		'SCOPE' => $arResult['SCOPE'],
	]
);
