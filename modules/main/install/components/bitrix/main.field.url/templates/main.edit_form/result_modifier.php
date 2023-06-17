<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component StringUfComponent
 */

$component = $this->getComponent();

if(isset($arResult['userField']['MULTIPLE']) && $arResult['userField']['MULTIPLE'] === 'Y')
{
	$arResult['additionalParameters']['VALIGN'] = 'top';
}
else
{
	$arResult['additionalParameters']['VALIGN'] = 'middle';
}

$attrList = [];

if($arResult['userField']['SETTINGS']['MAX_LENGTH'] > 0)
{
	$attrList['maxlength'] = $arResult['userField']['SETTINGS']['MAX_LENGTH'];
}

if($arResult['userField']['EDIT_IN_LIST'] !== 'Y')
{
	$attrList['disabled'] = 'disabled';
}

$attrList['type'] = 'text';
$attrList['valign'] = 'middle';
$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];

foreach($arResult['value'] as $key => $value)
{
	if(
		(!isset($arResult['userField']['ENTITY_VALUE_ID']) || $arResult['userField']['ENTITY_VALUE_ID'] < 1)
		&&
		mb_strlen($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
	)
	{
		$value = $arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
	}

	$attrList['name'] = str_replace('[]', '[' . $key . ']', $arResult['fieldName']);

	$arResult['value'][$key] = [
		'attrList' => $attrList
	];

	$arResult['value'][$key]['attrList']['value'] = $value;
}