<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;

/**
 * @var $component UrlUfComponent
 */

$component = $this->getComponent();

CJSCore::init(['uf']);

$attrList = [];

$attrList['class'] = 'uf-field-url';
$attrList['id'] = $arResult['fieldName'];

if($arResult['userField']['SETTINGS']['POPUP'] === 'Y')
{
	$attrList['target'] = '_blank';
}

foreach($arResult['value'] as $key => $value)
{
	$attrList['href'] = $component->getHtmlBuilder()->encodeUrl($value);
	$arResult['value'][$key] = [
		'attrList' => $attrList,
		'value' => HtmlFilter::encode($value)
	];

	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/main.field.url/templates/main.view/mobile.js'
		);

		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = 'text';
		$attrList['placeholder'] = HtmlFilter::encode(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['name'] = $arResult['fieldName'];
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
		$attrList['value'] = HtmlFilter::encode($value);
		$arResult['value'][$key]['attrList'] = $attrList;
	}
}