<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

$fieldName = $arResult['fieldName'];
$value = $arResult['value'];

if (empty($arResult['userField']['SETTINGS']['DISPLAY']))
{
	$arResult['userField']['SETTINGS']['DISPLAY'] = ElementType::DISPLAY_UI;
}

$isMultiple = ($arResult['userField']['MULTIPLE'] === 'Y');

if ($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_DIALOG)
{
	\Bitrix\Main\UI\Extension::load('iblock.userfield-selector');
}
elseif ($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_UI)
{
	\Bitrix\Main\UI\Extension::load('ui');

	$startValue = [];
	$itemList = [];

	foreach ($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
	{
		if ($key === '' && $isMultiple)
		{
			continue;
		}

		$item = [
			'NAME' => $val,
			'VALUE' => $key,
		];

		if (in_array($key, $value))
		{
			$startValue[] = $item;
		}

		$itemList[] = $item;
	}

	$arResult['params'] = Json::encode([
		'isMulti' => $isMultiple,
		'fieldName' => $arResult['userField']['FIELD_NAME'],
	]);

	$controlNodeId = $arResult['userField']['FIELD_NAME'] . '_control_';
	$valueContainerId = $arResult['userField']['FIELD_NAME'] . '_value_';

	$spanAttrList = [
		'id' => $valueContainerId,
		'style' => 'display: none',
	];

	$arResult['spanAttrList'] = $spanAttrList;

	$arResult['attrList'] = [];

	for ($i = 0, $n = count($startValue); $i < $n; $i++)
	{
		$attrList = [
			'type' => 'hidden',
			'name' => $fieldName,
			'value' => $startValue[$i]['VALUE'],
		];

		$arResult['attrList'][] = $attrList;
	}

	if (!$isMultiple)
	{
		$startValue = $startValue[0] ?? [];
	}

	$items = Json::encode($itemList);
	$currentValue = Json::encode($startValue);

	$arResult['items'] = $items;
	$arResult['currentValue'] = $currentValue;

	$fieldNameJs = CUtil::JSEscape($arResult['userField']['FIELD_NAME']);
	$htmlFieldNameJs = CUtil::JSEscape($fieldName);
	$controlNodeIdJs = CUtil::JSEscape($controlNodeId);
	$valueContainerIdJs = CUtil::JSEscape($valueContainerId);
	$block = ($isMultiple ? 'main-ui-multi-select' : 'main-ui-select');

	$arResult['block'] = $block;
	$arResult['controlNodeId'] = $controlNodeId;
	$arResult['fieldNameJs'] = $fieldNameJs;
	$arResult['valueContainerIdJs'] = $valueContainerIdJs;
	$arResult['htmlFieldNameJs'] = $htmlFieldNameJs;
	$arResult['controlNodeIdJs'] = $controlNodeIdJs;
}
elseif ($arResult['userField']['SETTINGS']['DISPLAY'] === ElementType::DISPLAY_LIST)
{
	$attrList = [
		'name' => $fieldName,
		'tabindex' => '0',
	];

	if ($arResult['userField']['SETTINGS']['LIST_HEIGHT'] > 1)
	{
		$attrList['size'] = (int)$arResult['userField']['SETTINGS']['LIST_HEIGHT'];
	}

	if ($isMultiple)
	{
		$attrList['multiple'] = 'multiple';
	}

	$arResult['attrList'] = $attrList;
}

if ($this->getComponent()->isMobileMode())
{
	$asset = Asset::getInstance();
	$asset->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	$asset->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.view/mobile.js'
	);
}
