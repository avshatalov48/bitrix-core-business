<?php
/**
 * @var $component \CatalogProductCardIblockSectionField
 * @var $this \CBitrixComponentTemplate
 */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$GLOBALS['APPLICATION']->includeComponent(
	'bitrix:ui.tile.selector',
	'',
	[
		'ID' => "catalog-iblocksectionfield-{$arParams['IBLOCK_ID']}-{$arParams['PRODUCT_ID']}",
		'INPUT_NAME' => 'IBLOCK_SECTION',
		'MULTIPLE' => true,
		'LIST' => $arResult['LIST'],
		'CAN_REMOVE_TILES' => true,
		'SHOW_BUTTON_SELECT' => true,
		'SHOW_BUTTON_ADD' => true,
		'MANUAL_INPUT_END' => true,
		// 'MANUAL_INPUT_END' => true,
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('CATALOG_IBLOCKSECTIONFIELD_SELECT'),
		'BUTTON_SELECT_CAPTION_MORE' => Loc::getMessage('CATALOG_IBLOCKSECTIONFIELD_SELECT'),
	]
);