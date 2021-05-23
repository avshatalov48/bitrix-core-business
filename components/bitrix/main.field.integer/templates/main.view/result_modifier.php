<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

CJSCore::init(['uf']);
$i = 0;

foreach($arResult['value'] as $key => $value)
{
	$value = ($value !== null && $value !== '' ? (int)$value : '');

	if(!empty($arResult['userField']['PROPERTY_VALUE_LINK']))
	{
		$tag = 'a';
		$href = htmlspecialcharsbx(
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


	/**
	 * @var $component IntegerUfComponent
	 */
	$component = $this->getComponent();

	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/main.field.integer/templates/main.view/mobile.js'
		);

		$type = $arResult['userField']['USER_TYPE_ID'];

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = $type;
		$attrList['name'] = $arResult['fieldName'];
		$attrList['placeholder'] = htmlspecialcharsbx(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $value;
		$arResult['value'][$key]['attrList'] = $attrList;
	}
}
