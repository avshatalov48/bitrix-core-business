<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;

$fieldName = $arResult['fieldName'];
$value = $arResult['value'];

$arResult['isEnabled'] = ($arResult['userField']['EDIT_IN_LIST'] === 'Y');
$isMobileMode = $this->getComponent()->isMobileMode();

if($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI && !$isMobileMode)
{
	$value = $arResult['value'];

	\CJSCore::Init('ui');

	$startValue = [];
	$itemList = [];

	foreach($arResult['userField']['USER_TYPE']['~FIELDS'] as $key => $val)
	{
		if($key === '' && $arResult['userField']['MULTIPLE'] === 'Y')
		{
			continue;
		}

		$item = [
			'NAME' => $val,
			'VALUE' => $key,
		];

		if(in_array($key, $value))
		{
			$startValue[] = $item;
		}

		$itemList[] = $item;
	}

	$arResult['params'] = [
		'isMulti' => ($arResult['userField']['MULTIPLE'] === 'Y'),
		'fieldName' => $arResult['fieldName']
	];

	$arResult['valueContainerId'] = $arResult['fieldName'] . '_value_';

	$arResult['spanAttrList'] = [
		'id' => $arResult['valueContainerId'],
		'style' => 'display: none'
	];

	$arResult['controlNodeId'] = $arResult['userField']['FIELD_NAME'] . '_control_';

	$arResult['attrList'] = [];

	for($i = 0, $n = count($startValue); $i < $n; $i++)
	{
		$attrList = [
			'type' => 'hidden',
			'name' => $arResult['fieldName'],
			'value' => $startValue[$i]['VALUE'],
		];

		$arResult['attrList'][] = $attrList;
	}

	if($arResult['userField']['MULTIPLE'] !== 'Y')
	{
		$startValue = $startValue[0];
	}

	$arResult['items'] = $itemList;
	$arResult['currentValue'] = $startValue;

	$block = (
	$arResult['userField']['MULTIPLE'] === 'Y'
		? 'main-ui-multi-select'
		: 'main-ui-select'
	);

	$arResult['block'] = $block;
	$arResult['fieldNameJs'] = \CUtil::JSEscape($arResult['fieldName']);

	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.edit/desktop.js'
	);
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST && !$isMobileMode)
{
	$attrList = [
		'name' => $fieldName,
		'tabindex' => '0',
	];

	if($arResult['userField']['SETTINGS']['LIST_HEIGHT'] > 1)
	{
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['LIST_HEIGHT'];
	}

	if($arResult['userField']['MULTIPLE'] === 'Y')
	{
		$attrList['multiple'] = 'multiple';
	}

	$arResult['attrList'] = $attrList;
}

if($this->getComponent()->isMobileMode())
{
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.view/mobile.js'
	);
}