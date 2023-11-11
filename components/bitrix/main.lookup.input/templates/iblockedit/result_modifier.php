<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */

$arParams['BAN_SYM'] = trim((string)($arParams['BAN_SYM'] ?? ''));
$arParams['REP_SYM'] = mb_substr((string)($arParams['REP_SYM'] ?? ''), 0, 1);
$arParams['FILTER'] = ($arParams['FILTER'] ?? 'N') === 'Y' ? 'Y' : 'N';
$arParams['TYPE'] = ($arParams['TYPE'] ?? '') === 'SECTION' ? 'SECTION' : 'ELEMENT';
$arParams['RESULT_COUNT'] = (int)($arParams['RESULT_COUNT'] ?? 0);
if ($arParams['RESULT_COUNT'] <= 0)
{
	$arParams['RESULT_COUNT'] = 20;
}

$arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
$arParams['WITHOUT_IBLOCK'] = ($arParams['WITHOUT_IBLOCK'] ?? 'N') === 'Y' ? 'Y' : 'N';

$arParams['MAX_HEIGHT'] = (int)($arParams['MAX_HEIGHT'] ?? 0);
if ($arParams['MAX_HEIGHT'] <= 0)
{
	$arParams['MAX_HEIGHT'] = 1000;
}
$arParams['MIN_HEIGHT'] = (int)($arParams['MIN_HEIGHT'] ?? 0);
if ($arParams['MIN_HEIGHT'] <= 0)
{
	$arParams['MIN_HEIGHT'] = 30;
}
$arParams['MAX_WIDTH'] = (int)($arParams['MAX_WIDTH'] ?? 0);
if ($arParams['MAX_WIDTH'] < 0)
{
	$arParams['MAX_WIDTH'] = 0;
}
if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
{
	$arParams['MAX_WIDTH'] = 500;
}

$arParams['MAIN_UI_FILTER'] = ($arParams['MAIN_UI_FILTER'] ?? 'N') === 'Y' ? 'Y' : 'N';
$arParams['MULTIPLE'] = ($arParams['MULTIPLE'] ?? 'N') === 'Y' ? 'Y' : 'N';
