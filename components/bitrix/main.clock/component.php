<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");

$clockParams = array();
if ($arParams['INPUT_ID'])
	$clockParams['inputId'] = $arParams['INPUT_ID'];
if ($arParams['INPUT_NAME'])
	$clockParams['inputName'] = $arParams['INPUT_NAME'];
if ($arParams['INPUT_TITLE'])
	$clockParams['inputTitle'] = $arParams['INPUT_TITLE'];
if ($arParams['INIT_TIME'])
	$clockParams['initTime'] = $arParams['INIT_TIME'];
if ($arParams['ZINDEX'])
	$clockParams['zIndex'] = intval($arParams['ZINDEX']);
if ($arParams['STEP'] && $arParams['STEP'] != 5)
	$clockParams['STEP'] = $arParams['STEP'];
$arResult['clockParams'] = $clockParams;
$this->IncludeComponentTemplate();
?>