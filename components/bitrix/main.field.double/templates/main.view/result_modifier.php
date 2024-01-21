<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component DoubleUfComponent
 */

$component = $this->getComponent();

CJSCore::init(['uf']);
$i = 0;

foreach($arResult['value'] as $key => $value)
{
	$value = (double) $value;

	if (!empty($arResult['userField']['PROPERTY_VALUE_LINK']))
	{
		$tag = 'a';
		$href = HtmlFilter::encode(
			str_replace('#VALUE#', (int)$value, $arResult['userField']['PROPERTY_VALUE_LINK'])
		);
	}
	else
	{
		$tag = null;
		$href = null;
	}

	$arResult['value'][$key] = [
		'value' => $value,
		'href' => $href
	];

	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/main.field.double/templates/main.view/mobile.js'
		);

		$type = $arResult['userField']['USER_TYPE_ID'];

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = $type;
		$attrList['name'] = $arResult['fieldName'];
		$attrList['placeholder'] = HtmlFilter::encode(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $value;
		$arResult['value'][$key]['attrList'] = $attrList;
	}
}
