<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$arResult['values']['popup'] =
		($GLOBALS[$arResult['additionalParameters']['NAME']]['POPUP'] === 'N' ? 'N' : 'Y');
	$arResult['values']['default_value'] =
		HtmlFilter::encode($GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']);
	$arResult['values']['size'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'];
	$arResult['values']['min_length'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_LENGTH'];
	$arResult['values']['max_length'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_LENGTH'];
}
elseif(is_array($arResult['userField']))
{
	$arResult['values']['popup'] =
		($arResult['userField']['SETTINGS']['POPUP'] === 'N' ? 'N' : 'Y');
	$arResult['values']['default_value'] =
		HtmlFilter::encode($arResult['userField']['SETTINGS']['DEFAULT_VALUE']);
	$arResult['values']['size'] =
		(int)$arResult['userField']['SETTINGS']['SIZE'];
	$arResult['values']['min_length'] =
		(int)$arResult['userField']['SETTINGS']['MIN_LENGTH'];
	$arResult['values']['max_length'] =
		(int)$arResult['userField']['SETTINGS']['MAX_LENGTH'];
}
else
{
	$arResult['values']['popup'] = 'Y';
	$arResult['values']['default_value'] = '';
	$arResult['values']['size'] = 20;
	$arResult['values']['min_length'] = 0;
	$arResult['values']['max_length'] = 0;
}