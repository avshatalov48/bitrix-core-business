<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BooleanType;

$label = BooleanType::getLabels($arResult['userField']);

if(!isset($arResult['userField']['ENTITY_VALUE_ID']) || $arResult['userField']['ENTITY_VALUE_ID'] < 1)
{
	$arResult['additionalParameters']['VALUE'] =
		(int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
}

$name = $arResult['additionalParameters']['NAME'];
if($arResult['userField']['MULTIPLE'] === 'Y')
{
	$name .= '[0]';
}

$value = (
(isset($arResult['userField']['VALUE']) && $arResult['userField']['VALUE'] !== false)
	? (int)$arResult['userField']['VALUE']
	: (int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE']
);

switch($arResult['userField']['SETTINGS']['DISPLAY'])
{
	case 'DROPDOWN':
		$arResult['additionalParameters']['VALIGN'] = 'middle';
		$type = BooleanType::DISPLAY_DROPDOWN;
		break;
	case 'RADIO':
		$type = BooleanType::DISPLAY_RADIO;
		break;
	default:
		$arResult['additionalParameters']['VALIGN'] = 'middle';
		$type = 'checkbox';
		break;
}

$arResult['value'] = $value;
$arResult['type'] = $type;
$arResult['label'] = $label;
$arResult['name'] = $name;