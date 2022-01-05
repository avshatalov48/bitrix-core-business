<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$defaultPrecision = 4;
$defaultSize = 20;
$defaultValue = '';

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$precision = $GLOBALS[$arResult['additionalParameters']['NAME']]['PRECISION'] ?? '';
	$value = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'])
		? (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']
		: ''
	;
	$size = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'])
		? (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE']
		: ''
	;
	$min = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE'])
		? (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE']
		: 0
	;
	$max = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE'])
		? (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE']
		: 0
	;
}
elseif(isset($arResult['userField']['SETTINGS']))
{
	$precision = ($arResult['userField']['SETTINGS']['PRECISION'] ?? $defaultPrecision);
	$value = !empty($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
		? (double)$arResult['userField']['SETTINGS']['DEFAULT_VALUE']
		: $defaultValue
	;
	$size = !empty($arResult['userField']['SETTINGS']['SIZE'])
		? (int)$arResult['userField']['SETTINGS']['SIZE']
		: $defaultSize
	;
	$min = (double)($arResult['userField']['SETTINGS']['MIN_VALUE'] ?? 0);
	$max = (double)($arResult['userField']['SETTINGS']['MAX_VALUE'] ?? 0);
}
else
{
	$precision = $defaultPrecision;
	$value = $defaultValue;
	$size = $defaultSize;
	$min = 0;
	$max = 0;
}

$arResult['precision'] = ($precision !== '' ? (int)$precision : '');
$arResult['value'] = $value;
$arResult['size'] = $size;
$arResult['min'] = $min;
$arResult['max'] = $max;