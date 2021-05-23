<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$arResult['value'] =
		($GLOBALS[$arResult['additionalParameters']['NAME']]['SHOW_MAP'] === 'N' ? 'N' : 'Y');
}
elseif(is_array($arResult['userField']))
{
	$arResult['value'] =
		($arResult['userField']['SETTINGS']['SHOW_MAP'] === 'N' ? 'N' : 'Y');
}
else
{
	$arResult['value'] = 'Y';
}