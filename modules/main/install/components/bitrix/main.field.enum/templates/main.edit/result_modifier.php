<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;

$fieldName = $arResult['fieldName'];
$value = $arResult['value'];

$arResult['isEnabled'] = ($arResult['userField']['EDIT_IN_LIST'] === 'Y');

if($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI)
{
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

	$params = Json::encode([
		'isMulti' => ($arResult['userField']['MULTIPLE'] === 'Y'),
		'fieldName' => $arResult['userField']['FIELD_NAME']
	]);
	$arResult['params'] = $params;

	$result = '';

	$controlNodeId = $arResult['userField']['FIELD_NAME'] . '_control_';
	$valueContainerId = $arResult['userField']['FIELD_NAME'] . '_value_';

	$spanAttrList = [
		'id' => $valueContainerId,
		'style' => 'display: none'
	];

	$arResult['spanAttrList'] = $spanAttrList;

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

	$items = Json::encode($itemList);
	$currentValue = Json::encode($startValue);

	$arResult['items'] = $items;
	$arResult['currentValue'] = $currentValue;

	$fieldNameJs = CUtil::JSEscape($arResult['userField']['FIELD_NAME']);
	$htmlFieldNameJs = CUtil::JSEscape($fieldName);
	$controlNodeIdJs = CUtil::JSEscape($controlNodeId);
	$valueContainerIdJs = CUtil::JSEscape($valueContainerId);
	$block = ($arResult['userField']['MULTIPLE'] === 'Y' ?
		'main-ui-multi-select' : 'main-ui-select'
	);

	$arResult['block'] = $block;
	$arResult['controlNodeId'] = $controlNodeId;
	$arResult['fieldNameJs'] = $fieldNameJs;
	$arResult['valueContainerIdJs'] = $valueContainerIdJs;
	$arResult['htmlFieldNameJs'] = $htmlFieldNameJs;
	$arResult['controlNodeIdJs'] = $controlNodeIdJs;

}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST)
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