<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

$arParams['SHOW_ANGLE'] = (string)($arParams['SHOW_ANGLE'] ?? 'Y');
if ($arParams['SHOW_ANGLE'] !== 'N')
{
	$arParams['SHOW_ANGLE'] = 'Y';
}
