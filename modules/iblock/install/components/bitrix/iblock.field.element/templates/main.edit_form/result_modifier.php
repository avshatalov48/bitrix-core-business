<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\UserField\Types\ElementType;
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

if($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_UI)
{
	CJSCore::Init('ui');

	$arResult['additionalParameters']['VALIGN'] = 'middle';

	$itemList = [];

	$emptyValue = [
		'NAME' => ElementType::getEmptyCaption($arResult['userField']),
		'VALUE' => '',
	];

	$startValue = [];

	if($arUserField['MANDATORY'] !== 'Y')
	{
		$itemList[] = $emptyValue;
	}

	foreach($arResult['additionalParameters']['items'] as $itemId => $item)
	{
		$element = [
			'NAME' => $item['VALUE'],
			'VALUE' => $item['ID'],
		];

		if(
			($arResult['userField']['ENTITY_VALUE_ID'] <= 0 && $item['DEF'] === 'Y')
			||
			in_array((string)$item['ID'], $arResult['value'], true)
		)
		{
			$startValue[] = $element;
		}
		$itemList[] = $element;
	}

	if($arResult['userField']['MANDATORY'] !== 'Y' && !count($startValue))
	{
		$startValue[] = $emptyValue;
	}

	$params = Json::encode([
		'isMulti' => ($arResult['userField']['MULTIPLE'] === 'Y'),
		'fieldName' => $arResult['userField']['FIELD_NAME']
	]);

	$items = Json::encode($itemList);
	$currentValue = (
	$arResult['userField']['MULTIPLE'] === 'Y' ?
		Json::encode($startValue) : Json::encode($startValue[0])
	);

	$controlNodeId = $arResult['userField']['FIELD_NAME'] . '_control';
	$valueContainerId = $arResult['userField']['FIELD_NAME'] . '_value';

	$fieldNameJS = CUtil::JSEscape($arResult['userField']['FIELD_NAME']);
	$htmlFieldNameJS = CUtil::JSEscape($arResult['fieldName']);
	$controlNodeIdJS = CUtil::JSEscape($controlNodeId);
	$valueContainerIdJS = CUtil::JSEscape($valueContainerId);

	$block = ($arResult['userField']['MULTIPLE'] === 'Y' ?
		'main-ui-multi-select' : 'main-ui-select'
	);

	$arResult['block'] = $block;
	$arResult['startValue'] = $startValue;
	$arResult['valueContainerId'] = $valueContainerId;
	$arResult['valueContainerIdJs'] = $valueContainerIdJS;
	$arResult['controlNodeIdJs'] = $controlNodeIdJS;
	$arResult['fieldNameJs'] = $fieldNameJS;
	$arResult['htmlFieldNameJs'] = $htmlFieldNameJS;

	$arResult['items'] = $items;
	$arResult['currentValue'] = $currentValue;
	$arResult['params'] = $params;

}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_LIST)
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