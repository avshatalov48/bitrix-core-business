<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

$attrList = [
	'size' => (int)$arResult['userField']['SETTINGS']['SIZE'],
	'value' => $arResult['additionalParameters']['VALUE']
];

$attrList['name'] = $arResult['additionalParameters']['NAME'];

if(!empty($arResult['userField']['SETTINGS']['MAX_LENGTH']))
{
	$attrList['maxlength'] = (int)$arResult['userField']['SETTINGS']['MAX_LENGTH'];
}

$arResult['fieldValues'] = [
	'attrList' => $attrList,
	'value' => $arResult['additionalParameters']['VALUE']
];