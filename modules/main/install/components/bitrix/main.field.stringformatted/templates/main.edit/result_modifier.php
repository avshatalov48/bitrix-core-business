<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $component StringUfComponent
 */

use Bitrix\Main\Text\HtmlFilter;

$component = $this->getComponent();

CJSCore::init(['uf']);

$attrList = [];

if($arResult['userField']['SETTINGS']['MAX_LENGTH'] > 0)
{
	$attrList['maxlength'] = (int)$arResult['userField']['SETTINGS']['MAX_LENGTH'];
}

if($arResult['userField']['EDIT_IN_LIST'] !== 'Y')
{
	$attrList['disabled'] = 'disabled';
}

if($arResult['userField']['SETTINGS']['ROWS'] < 2)
{
	if($arResult['userField']['SETTINGS']['SIZE'] > 0)
	{
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
	}
}
else
{
	$attrList['cols'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
	$attrList['rows'] = (int)$arResult['userField']['SETTINGS']['ROWS'];
}

if(array_key_exists('attribute', $arResult['additionalParameters']))
{
	$attrList = array_merge($attrList, $arResult['additionalParameters']['attribute']);
}

if(isset($attrList['class']) && is_array($attrList['class']))
{
	$attrList['class'] = implode(' ', $attrList['class']);
}

$attrList['class'] = implode(' ',[
	$component->getHtmlBuilder()->getCssClassName(),
	($attrList['class'] ?? '')
]);

$attrList['name'] = $arResult['fieldName'];
$attrList['tabindex'] = '0';

if($arResult['userField']['SETTINGS']['ROWS'] < 2)
{
	$attrList['type'] = 'text';
	$tag = 'input';
}
else
{
	$tag = 'textarea';
}

$i=0;

foreach($arResult['value'] as $key => $value)
{
	$attrList['value'] = $value;

	$arResult['fieldValues'][$key] = [
		'attrList' => $attrList,
		'tag' => $tag
	];

	if($component->isMobileMode())
	{
		$attrList['data-bx-type'] = 'text';
		$attrList['placeholder'] = HtmlFilter::encode(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['EDIT_FORM_LABEL']
		);
		$attrList['name'] = str_replace('[]', '['.$key.']', $arResult['fieldName']);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
		$attrList['value'] = HtmlFilter::encode($value);
		$arResult['fieldValues'][$key]['attrList'] = $attrList;
	}
}