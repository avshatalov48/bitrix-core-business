<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");

$clockParams = [];
if (!empty($arParams['INPUT_ID']))
{
	$clockParams['inputId'] = $arParams['INPUT_ID'];
}
if (!empty($arParams['INPUT_NAME']))
{
	$clockParams['inputName'] = $arParams['INPUT_NAME'];
}
if (!empty($arParams['INPUT_TITLE']))
{
	$clockParams['inputTitle'] = $arParams['INPUT_TITLE'];
}
if (!empty($arParams['INIT_TIME']))
{
	$clockParams['initTime'] = $arParams['INIT_TIME'];
}
if (!empty($arParams['ZINDEX']))
{
	$clockParams['zIndex'] = intval($arParams['ZINDEX']);
}
if (isset($arParams['STEP']) && $arParams['STEP'] != 5)
{
	$clockParams['STEP'] = $arParams['STEP'];
}

$arResult['clockParams'] = $clockParams;
$this->IncludeComponentTemplate();
?>