<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$arResult['values']['defaultValue'] =
		HtmlFilter::encode($GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'] ?? '');
	$arResult['values']['size'] =
		(int)($GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'] ?? 0);
	$arResult['values']['rows'] =
		(int)($GLOBALS[$arResult['additionalParameters']['NAME']]['ROWS'] ?? 0);
	$arResult['values']['min_length'] =
		(int)($GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_LENGTH'] ?? 0);
	$arResult['values']['max_length'] =
		(int)($GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_LENGTH'] ?? 0);
	$arResult['values']['regexp'] =
		HtmlFilter::encode($GLOBALS[$arResult['additionalParameters']['NAME']]['REGEXP'] ?? '');
}
elseif (isset($arResult['userField']) && is_array($arResult['userField']))
{
	$arResult['values']['defaultValue'] =
		HtmlFilter::encode($arResult['userField']['SETTINGS']['DEFAULT_VALUE'] ?? '');
	$arResult['values']['size'] =
		(int)($arResult['userField']['SETTINGS']['SIZE'] ?? 0);
	$arResult['values']['rows'] =
		(int)($arResult['userField']['SETTINGS']['ROWS'] ?? 0);
	$arResult['values']['min_length'] =
		(int)($arResult['userField']['SETTINGS']['MIN_LENGTH'] ?? 0);
	$arResult['values']['max_length'] =
		(int)($arResult['userField']['SETTINGS']['MAX_LENGTH'] ?? 0);
	$arResult['values']['regexp'] =
		HtmlFilter::encode($arResult['userField']['SETTINGS']['REGEXP'] ?? '');
}
else
{
	$arResult['values']['defaultValue'] = '';
	$arResult['values']['size'] = 20;
	$arResult['values']['rows'] = 1;
	$arResult['values']['min_length'] = 0;
	$arResult['values']['max_length'] = 0;
	$arResult['values']['regexp'] = '';
}

if($arResult['values']['rows'] < 1)
{
	$arResult['values']['rows'] = 1;
}