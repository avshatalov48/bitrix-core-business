<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$value = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
	$size = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'];
	$min = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MIN_VALUE'];
	$max = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_VALUE'];
}
elseif(is_array($arResult['userField']))
{
	$value = (int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
	$size = (int)$arResult['userField']['SETTINGS']['SIZE'];
	$min = (int)$arResult['userField']['SETTINGS']['MIN_VALUE'];
	$max = (int)$arResult['userField']['SETTINGS']['MAX_VALUE'];
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