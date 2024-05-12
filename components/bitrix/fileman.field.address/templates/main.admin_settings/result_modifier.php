<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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
	$showMap = ($arResult['userField']['SETTINGS']['SHOW_MAP'] ?? null);
	$arResult['value'] = ($showMap === 'N' ? 'N' : 'Y');
}
else
{
	$arResult['value'] = 'Y';
}