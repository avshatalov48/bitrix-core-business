<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$defaultPrecision = 4;
$defaultSize = 20;
$defaultValue = '';

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$precision = $GLOBALS[$arResult['additionalParameters']['NAME']]['PRECISION'];
	$value = (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
	$size = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'];
	$min = (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE'];
	$max = (double)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE'];
}
elseif(isset($arResult['userField']['SETTINGS']))
{
	$precision = ($arResult['userField']['SETTINGS']['PRECISION'] ?? $defaultPrecision);
	$value = (
	isset($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
		?
		(double)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'] : $defaultValue
	);
	$size = (
	isset($arResult['userField']['SETTINGS']['SIZE'])
		?
		(int)$arResult['userField']['SETTINGS']['SIZE'] : $defaultSize
	);
	$min = (double)$arResult['userField']['SETTINGS']['MIN_VALUE'];
	$max = (double)$arResult['userField']['SETTINGS']['MAX_VALUE'];
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