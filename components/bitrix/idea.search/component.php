<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult = array();
$arResult["SEACRH"] = htmlspecialcharsbx($_REQUEST["LIFE_SEARCH_QUERY"]);

$this->IncludeComponentTemplate();
?>