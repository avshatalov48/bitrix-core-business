<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$arResult['value'] = (
		isset($GLOBALS[$arResult['additionalParameters']['NAME']]['SHOW_MAP'])
		&& $GLOBALS[$arResult['additionalParameters']['NAME']]['SHOW_MAP'] === 'N'
			? 'N'
			: 'Y'
	);
}
elseif (isset($arResult['userField']) && is_array($arResult['userField']))
{
	$arResult['value'] = (
		isset($arResult['userField']['SETTINGS']['SHOW_MAP'])
		&& $arResult['userField']['SETTINGS']['SHOW_MAP'] === 'N'
			? 'N'
			: 'Y'
	);
}
else
{
	$arResult['value'] = 'Y';
}