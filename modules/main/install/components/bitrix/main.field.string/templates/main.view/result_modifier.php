<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;

CJSCore::init(['uf']);
$i = 0;

foreach($arResult['value'] as $key => $value)
{
	if($arResult['userField']['SETTINGS']['ROWS'] < 2)
	{
		$value = HtmlFilter::encode($value);
	}
	elseif($value <> '')
	{
		$value = nl2br(HtmlFilter::encode($value));
	}

	$href = '';
	if($arResult['userField']['PROPERTY_VALUE_LINK'] <> '')
	{
		$href = HtmlFilter::encode(
			str_replace(
				'#VALUE#',
				urlencode($value),
				$arResult['userField']['PROPERTY_VALUE_LINK']
			)
		);
	}

	$arResult['value'][$key] = [
		'value' => $value,
		'href' => $href
	];

	/**
	 * @var $component StringUfComponent
	 */

	$component = $this->getComponent();

	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/main.field.string/templates/main.view/mobile.js'
		);

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = 'text';
		$attrList['placeholder'] = HtmlFilter::encode(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['name'] = $arResult['fieldName'];
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['SIZE'];
		$attrList['value'] = $value;
		$arResult['value'][$key]['attrList'] = $attrList;
	}

}
