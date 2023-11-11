<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

$iblockId = (int)($arResult['VARIABLES']['IBLOCK_ID'] ?? 0);

// load menu
$APPLICATION->IncludeComponent('bitrix:crm.shop.page.controller', '', [
	'CONNECT_PAGE' => 'N',
]);

$APPLICATION->IncludeComponent(
	'bitrix:catalog.product.grid',
	'',
	[
		'IBLOCK_ID' => $iblockId,
	]
);
