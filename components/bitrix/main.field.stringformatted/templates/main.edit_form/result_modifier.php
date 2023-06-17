<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component StringFormattedUfComponent
 */

$component = $this->getComponent();

$arResult['additionalParameters']['VALIGN'] = (
$arResult['userField']['MULTIPLE'] === 'Y' ? 'top' : 'middle'
);

$attrList = [];

if($arResult['userField']['SETTINGS']['MAX_LENGTH'] > 0)
{
	$attrList['maxlength'] = $arResult['userField']['SETTINGS']['MAX_LENGTH'];
}

if($arResult['userField']['EDIT_IN_LIST'] !== 'Y')
{
	$attrList['disabled'] = 'disabled';
}

if($arResult['userField']['SETTINGS']['ROWS'] < 2)
{
	$tag = 'input';
	$attrList['type'] = 'text';
	$attrList['valign'] = 'middle';
	$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
}
else
{
	$tag = 'textarea';
	$attrList['cols'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
	$attrList['rows'] = (int)$arResult['userField']['SETTINGS']['ROWS'];
}

$values = (
is_array($arResult['userField']['VALUE'])
	? (count($arResult['userField']['VALUE']) ? $arResult['userField']['VALUE'] : [0 => null])
	: [$arResult['userField']['VALUE']]
);

foreach($values as $key => $value)
{
	if(
		(!isset($arResult['userField']['ENTITY_VALUE_ID']) || $arResult['userField']['ENTITY_VALUE_ID'] < 1)
		&&
		mb_strlen($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
	)
	{
		$value = HtmlFilter::encode($arResult['userField']['SETTINGS']['DEFAULT_VALUE']);
	}

	if(!empty($value))
	{
		$value = HtmlFilter::encode($value);
	}

	$attrList['name'] = str_replace('[]', '[' . $key . ']', $arResult['fieldName']);

	$arResult['value'][$key] = [
		'attrList' => $attrList,
		'tag' => $tag,
	];

	if($arResult['userField']['SETTINGS']['ROWS'] < 2)
	{
		$arResult['value'][$key]['attrList']['value'] = $value;
	}
	else
	{
		$arResult['value'][$key]['value'] = $value;
	}
}