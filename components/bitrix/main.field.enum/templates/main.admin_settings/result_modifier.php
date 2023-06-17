<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component DateUfComponent
 */

$component = $this->getComponent();

if(isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$display = $GLOBALS[$arResult['additionalParameters']['NAME']]['DISPLAY'] ?? '';
	$listHeight = (int)($GLOBALS[$arResult['additionalParameters']['NAME']]['LIST_HEIGHT'] ?? 0);
	$captionNoValue = trim($GLOBALS[$arResult['additionalParameters']['NAME']]['CAPTION_NO_VALUE'] ?? '');
	$showNoValue = trim($GLOBALS[$arResult['additionalParameters']['NAME']]['SHOW_NO_VALUE'] ?? '');
}
elseif (isset($arResult['userField']) && is_array($arResult['userField']))
{
	$display = $arResult['userField']['SETTINGS']['DISPLAY'] ?? '';
	$listHeight = (int)($arResult['userField']['SETTINGS']['LIST_HEIGHT'] ?? 0);
	$captionNoValue = trim($arResult['userField']['SETTINGS']['CAPTION_NO_VALUE'] ?? '');
	$showNoValue = trim($arResult['userField']['SETTINGS']['SHOW_NO_VALUE'] ?? '');
}
else
{
	$display = EnumType::DISPLAY_LIST;
	$listHeight = 5;
	$captionNoValue = '';
	$showNoValue = '';
}

$arResult['display'] = $display;
$arResult['listHeight'] = $listHeight;
$arResult['captionNoValue'] = HtmlFilter::encode($captionNoValue);
$arResult['showNoValue'] = $showNoValue;