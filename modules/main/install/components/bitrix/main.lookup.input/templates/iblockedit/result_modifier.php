<?
/** @global array $arParams */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams['BAN_SYM'] = trim($arParams['BAN_SYM']);
$arParams['REP_SYM'] = substr($arParams['REP_SYM'], 0, 1);
$arParams['FILTER'] = (isset($arParams['FILTER']) && $arParams['FILTER'] == 'Y' ? 'Y' : 'N');
$arParams['TYPE'] = (isset($arParams['TYPE']) && $arParams['TYPE'] == 'SECTION' ? 'SECTION' : 'ELEMENT');
$arParams['RESULT_COUNT'] = (isset($arParams['RESULT_COUNT']) ? (int)$arParams['RESULT_COUNT'] : 0);
if ($arParams['RESULT_COUNT'] <= 0)
	$arParams['RESULT_COUNT'] = 20;

$arParams['IBLOCK_ID'] = (isset($arParams['IBLOCK_ID']) ? (int)$arParams['IBLOCK_ID'] : 0);
$arParams['WITHOUT_IBLOCK'] = (isset($arParams['WITHOUT_IBLOCK']) && $arParams['WITHOUT_IBLOCK'] == 'Y' ? 'Y' : 'N');

$arParams['MAX_HEIGHT'] = (isset($arParams['MAX_HEIGHT']) ? (int)$arParams['MAX_HEIGHT'] : 0);
if ($arParams['MAX_HEIGHT'] <= 0)
	$arParams['MAX_HEIGHT'] = 1000;
$arParams['MIN_HEIGHT'] = (isset($arParams['MIN_HEIGHT']) ? (int)$arParams['MIN_HEIGHT'] : 0);
if ($arParams['MIN_HEIGHT'] <= 0)
	$arParams['MIN_HEIGHT'] = 30;
$arParams['MAX_WIDTH'] = (isset($arParams['MAX_WIDTH']) ? (int)$arParams['MAX_WIDTH'] : 0);
if ($arParams['MAX_WIDTH'] < 0)
	$arParams['MAX_WIDTH'] = 0;
if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
	$arParams['MAX_WIDTH'] = 500;