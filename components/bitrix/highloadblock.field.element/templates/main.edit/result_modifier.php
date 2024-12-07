<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;

/** @var array $arResult */

$fieldName = $arResult['fieldName'];
$value = $arResult['value'];

if (empty($arResult['userField']['SETTINGS']['DISPLAY']))
{
	$arResult['userField']['SETTINGS']['DISPLAY'] = \CUserTypeHlblock::DISPLAY_LIST;
}

$isMultiple = ($arResult['userField']['MULTIPLE'] === 'Y');

if ($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_LIST)
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
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.view/mobile.js'
	);
}
