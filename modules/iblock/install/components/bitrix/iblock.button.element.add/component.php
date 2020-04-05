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
 * IBLOCK_ID - ID iblock
 * LANG - lang id
 * BUTTON_TITLE - title for button
 * BUTTON_CAPTION - button value
 */

$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if (0 >= $arParams['IBLOCK_ID'])
	return;

$arParams['LANG'] = trim($arParams['LANG']);
if ('' == $arParams['LANG'])
	$arParams['LANG'] = 'ru';

$arParams['BUTTON_CAPTION'] = trim($arParams['BUTTON_CAPTION']);
$arParams['BUTTON_TITLE'] = trim($arParams['BUTTON_TITLE']);

$arResult['IBLOCK_TYPE'] = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], "IBLOCK_TYPE_ID");

if (CIBlockRights::UserHasRightTo($arParams['IBLOCK_ID'], $arParams['IBLOCK_ID'], "iblock_admin_display"))
{
	$this->IncludeComponentTemplate();
}
?>