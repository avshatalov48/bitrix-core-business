<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$value = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'])
		? (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']
		: ''
	;
	$size = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'])
		? (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE']
		: 20
	;
	$min = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE'])
		? (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE']
		: 0
	;
	$max = isset($GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE'])
		? (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE']
		: 0
	;
}
elseif(is_array($arResult['userField']))
{
	$value = isset($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
		? (int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE']
		: ''
	;
	$size = isset($arResult['userField']['SETTINGS']['SIZE'])
		? (int)$arResult['userField']['SETTINGS']['SIZE']
		: 20
	;
	$min = isset($arResult['userField']['SETTINGS']['MIN_VALUE'])
		? (int)$arResult['userField']['SETTINGS']['MIN_VALUE']
		: 0
	;
	$max = isset($arResult['userField']['SETTINGS']['MAX_VALUE'])
		? (int)$arResult['userField']['SETTINGS']['MAX_VALUE']
		: 0
	;
}
else
{
	$value = '';
	$size = 20;
	$min = 0;
	$max = 0;
}

$arResult['value'] = $value;
$arResult['size'] = $size;
$arResult['min'] = $min;
$arResult['max'] = $max;