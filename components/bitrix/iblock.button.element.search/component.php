<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;

/*
 * ONSELECT - object name from main.lookup.input
 * MULTIPLE - Y/N
 * IBLOCK_ID - ID iblock
 * LANG - lang id
 * BUTTON_TITLE - title for button
 * BUTTON_CAPTION - button value 
 */

$arParams['ONSELECT'] = trim($arParams['ONSELECT']);
if ('' == $arParams['ONSELECT'])
	return;

$arParams['MULTIPLE'] = ('Y' == $arParams['MULTIPLE'] ? 'Y' : 'N');

$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if (0 >= $arParams['IBLOCK_ID'])
	return;

$arParams['LANG'] = trim($arParams['LANG']);
if ('' == $arParams['LANG'])
	$arParams['LANG'] = 'ru';

$arParams['BUTTON_CAPTION'] = trim($arParams['BUTTON_CAPTION']);
$arParams['BUTTON_TITLE'] = trim($arParams['BUTTON_TITLE']);

$this->IncludeComponentTemplate();
?>