<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

$arResult['SHOW_TIME'] = ($arParams['SHOW_TIME'] ?? 'N') === 'Y';

#region values

$arResult['VALUES'] = [];
if (is_array($arParams['VALUE']))
{
	$arResult['VALUES'] = $arParams['VALUE'];
}
elseif (!empty($arParams['VALUE']))
{
	$arResult['VALUES'][] = $arParams['VALUE'];
}

$arResult['VALUES'] = array_filter($arResult['VALUES']);

#endregion values
