<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

$smthSelected = false;
if (!empty($arResult['TYPES']) && is_array($arResult['TYPES']))
{
	foreach($arResult['TYPES'] as $type)
	{
		if ($type['SELECTED'])
		{
			$smthSelected = true;
			break;
		}
	}
}
$arResult['TYPES_UNSELECTED'] = !$smthSelected;

$smthSelected = false;
if (!empty($arResult['LANGS']) && is_array($arResult['LANGS']))
{
	foreach($arResult['LANGS'] as $lang)
	{
		if ($lang['SELECTED'])
		{
			$smthSelected = true;
			break;
		}
	}
}
$arResult['LANGS_UNSELECTED'] = !$smthSelected;
