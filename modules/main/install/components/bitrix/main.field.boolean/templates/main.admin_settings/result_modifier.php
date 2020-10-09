<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BooleanType;

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$labels = [
		trim($GLOBALS[$arResult['additionalParameters']['NAME']]['LABEL'][0]),
		trim($GLOBALS[$arResult['additionalParameters']['NAME']]['LABEL'][1]),
	];
	$defaultValue = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
	$display = $GLOBALS[$arResult['additionalParameters']['NAME']]['DISPLAY'];
	$labelCheckbox = trim($GLOBALS[$arResult['additionalParameters']['NAME']]['LABEL_CHECKBOX']);
}
elseif(is_array($arResult['userField']))
{
	$labels = BooleanType::getLabels($arResult['userField']);
	$defaultValue = $arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
	$display = $arResult['userField']['SETTINGS']['DISPLAY'];

	if (isset($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
	{
		if (is_array($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
		{
			$labelCheckbox = trim($arResult['userField']['SETTINGS']['LABEL_CHECKBOX'][LANGUAGE_ID]);
		}
		elseif (trim($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']) <> '')
		{
			$labelCheckbox = trim($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']);
		}
	}

	if(empty($labelCheckbox))
	{
		$labelCheckbox = Loc::getMessage('MAIN_YES');
	}
}
else
{
	$labels = [Loc::getMessage('MAIN_NO'), Loc::getMessage('MAIN_YES')];
	$defaultValue = 1;
	$display = BooleanType::DISPLAY_CHECKBOX;
	$labelCheckbox = Loc::getMessage('MAIN_YES');
}

$arResult['labels'] = $labels;
$arResult['defaultValue'] = $defaultValue;
$arResult['display'] = $display;
$arResult['labelCheckbox'] = $labelCheckbox;
$arResult['displays'] = BooleanType::getAllDisplays();