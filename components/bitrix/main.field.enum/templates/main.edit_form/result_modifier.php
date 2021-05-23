<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Web\Json;

if(
	($arResult['userField']['ENTITY_VALUE_ID'] < 1)
	&&
	mb_strlen($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
)
{
	$arResult['additionalParameters']['VALUE'] =
		(int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
}

if($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI)
{
	$arResult['isEnabled'] = ($arResult['userField']['EDIT_IN_LIST'] === 'Y');
	$value = $arResult['value'];

	\CJSCore::Init('ui');

	$startValue = [];
	$itemList = [];

	$emptyValue = [
		'NAME' => EnumType::getEmptyCaption($arResult['userField']),
		'VALUE' => '',
	];

	$startValue = [];

	if(
		$arResult['userField']['MANDATORY'] !== 'Y'
		&& $arResult['userField']['MULTIPLE'] !== 'Y'
	)
	{
		$itemList[] = $emptyValue;
	}

	foreach($arResult['additionalParameters']['items'] as $key => $element)
	{
		if($key === '' && $arResult['userField']['MULTIPLE'] === 'Y')
		{
			continue;
		}

		$item = [
			'NAME' => $element['VALUE'],
			'VALUE' => $key,
		];

		if(in_array($key, $value))
		{
			$startValue[] = $item;
		}

		$itemList[] = $item;
	}

	$postfix = $this->randString();

	$arResult['params'] = [
		'isMulti' => ($arResult['userField']['MULTIPLE'] === 'Y'),
		'fieldName' => $arResult['fieldName']
	];

	$arResult['valueContainerId'] = $arResult['fieldName'] . '_value_' . $postfix;

	$arResult['spanAttrList'] = [
		'id' => $arResult['valueContainerId'],
		'style' => 'display: none'
	];

	$arResult['controlNodeId'] = $arResult['userField']['FIELD_NAME'] . '_control_' . $postfix;

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
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_LIST)
{
	if($arResult['userField']['SETTINGS']['LIST_HEIGHT'] > 1)
	{
		$arResult['size'] = $arResult['userField']['SETTINGS']['LIST_HEIGHT'];
	}
	else
	{
		$arResult['additionalParameters']['VALIGN'] = 'middle';
		$arResult['size'] = '';
	}
}

if(!is_array($arResult['additionalParameters']['VALUE']))
{
	$arResult['additionalParameters']['VALUE'] = [$arResult['additionalParameters']['VALUE']];
}