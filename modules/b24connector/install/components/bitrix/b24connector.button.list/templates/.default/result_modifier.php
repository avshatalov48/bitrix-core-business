<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
Loc::loadMessages(__FILE__);

$debugVarOneItemAsSystemInited = false;
$arResult['ITEMS_BY_IS_SYSTEM'] = array(
	'N' => array(
		'NAME' => Loc::getMessage('B24C_BL_WIDGETS_MINE'),
		'ITEMS' => array()
	),
	'Y' => array(
		'NAME' => Loc::getMessage('B24C_BL_WIDGETS_PRESET'),
		'ITEMS' => array()
	)
);

foreach($arResult['ITEMS'] as $item)
{
	$item['IS_SYSTEM'] = $item['IS_SYSTEM'] == 'Y' ? 'Y' : 'N';
	$arResult['ITEMS_BY_IS_SYSTEM'][$item['IS_SYSTEM']]['ITEMS'][] = $item;
}

if(count($arResult['ITEMS_BY_IS_SYSTEM']['N']['ITEMS']) == 0)
{
	unset($arResult['ITEMS_BY_IS_SYSTEM']['N']);
}

if(count($arResult['ITEMS_BY_IS_SYSTEM']['Y']['ITEMS']) == 0)
{
	unset($arResult['ITEMS_BY_IS_SYSTEM']['Y']);
}