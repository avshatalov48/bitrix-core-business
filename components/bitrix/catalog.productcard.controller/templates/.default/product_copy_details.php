<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$iblockId = (int)($arResult['VARIABLES']['IBLOCK_ID'] ?? 0);
$copyProductId = (int)($arResult['VARIABLES']['COPY_PRODUCT_ID'] ?? 0);

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:catalog.productcard.details',
	'',
	[
		'PATH_TO' => $arResult['PATH_TO'],
		'IBLOCK_ID' => $iblockId,
		'PRODUCT_ID' => 0,
		'COPY_PRODUCT_ID' => $copyProductId,
	]
);