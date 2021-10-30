<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Page\Asset;

/**
 * @var $arResult array
 */

$fieldName = $arResult['fieldName'];
$value = $arResult['value'];

$arResult['isEnabled'] = ($arResult['userField']['EDIT_IN_LIST'] === 'Y');
$isMultiple = $this->getComponent()->isMultiple();
$arResult['isMultiple'] = $isMultiple;
$isMobileMode = $this->getComponent()->isMobileMode();

if($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_UI && !$isMobileMode)
{
	$arResult['params'] = [
		'isMulti' => $isMultiple,
		'fieldName' => $arResult['fieldName'],
	];

	$arResult['valueContainerId'] = $arResult['fieldName'] . '_value_';

	$arResult['spanAttrList'] = [
		'id' => $arResult['valueContainerId'],
		'style' => 'display: none'
	];

	$arResult['controlNodeId'] = $arResult['userField']['FIELD_NAME'] . '_control_';

	$arResult['items'] = $this->getComponent()->getItems();

	$arResult['attrList'] = [];
	$i = 0;

	$arResult['selectedItems'] = [];
	foreach($arResult['items'] as $item)
	{
		if ($item['IS_SELECTED'])
		{
			$arResult['selectedItems'][] = $item;

			$attrList = [
				'type' => 'hidden',
				'name' => $fieldName,
				'value' => $item['VALUE'],
			];

			$arResult['attrList'][] = $attrList;
		}
	}

	if (!$isMultiple && count($arResult['selectedItems']))
	{
		$arResult['selectedItems'] = array_shift($arResult['selectedItems']);
	}
	$block = ($isMultiple ? 'main-ui-multi-select' : 'main-ui-select');

	$arResult['block'] = $block;
	$arResult['fieldNameJs'] = \CUtil::JSEscape($fieldName);

	\CJSCore::Init(['ui']);
	\Bitrix\Main\UI\Extension::load([
		'ui.entity-selector',
	]);

	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.edit/dist/display.bundle.js'
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

	if($isMultiple)
	{
		$attrList['multiple'] = 'multiple';
	}

	$arResult['attrList'] = $attrList;
}
elseif($arResult['userField']['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_DIALOG && !$isMobileMode)
{
	$arResult['targetNodeId'] = $fieldName . '_value';
	$arResult['fieldName'] = \CUtil::JSEscape($fieldName);
	$arResult['items'] = $this->getComponent()->getItems(true);

	\CJSCore::Init(['ui']);
	\Bitrix\Main\UI\Extension::load([
		'ui.entity-selector',
	]);

	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.edit/dist/display.bundle.js'
	);
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
