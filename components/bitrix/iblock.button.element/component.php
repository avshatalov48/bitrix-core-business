<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


/*
 * ONSELECT - object name from main.lookup.input
 * MULTIPLE - Y/N
 * IBLOCK_ID - ID iblock
 * LANG - lang id
 * BUTTON_TITLE - title for button
 * BUTTON_CAPTION - button value 
 */

$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : 'ius_'.$this->randString();

$arParams['HIDDEN_WINDOW'] = ('Y' == $arParams['HIDDEN_WINDOW'] ? 'Y' : 'N');

$arParams['CONTENT_URL'] = trim($arParams['CONTENT_URL']);
if ('' == $arParams['CONTENT_URL'])
	return;

$arParams['BUTTON_CAPTION'] = trim($arParams['BUTTON_CAPTION']);
$arParams['BUTTON_TITLE'] = trim($arParams['BUTTON_TITLE']);

$arParams['SEPARATE_BUTTON'] = ('Y' == $arParams['SEPARATE_BUTTON'] ? 'Y' : 'N');

$this->IncludeComponentTemplate();
?>