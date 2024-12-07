<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

if(
	((int)($arResult['userField']['ENTITY_VALUE_ID'] ?? 0) < 1)
	&& (int)($arResult['userField']['SETTINGS']['DEFAULT_VALUE'] ?? 0) > 0
)
{
	$arResult['additionalParameters']['VALUE'] =
		(int)$arResult['userField']['SETTINGS']['DEFAULT_VALUE']
	;
}

if ($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_LIST)
{
	if ($arResult['userField']['SETTINGS']['LIST_HEIGHT'] > 1)
	{
		$arResult['size'] = $arResult['userField']['SETTINGS']['LIST_HEIGHT'];
	}
	else
	{
		$arResult['additionalParameters']['VALIGN'] = 'middle';
		$arResult['size'] = '';
	}
}

if (!is_array($arResult['additionalParameters']['VALUE']))
{
	$arResult['additionalParameters']['VALUE'] = [$arResult['additionalParameters']['VALUE']];
}
