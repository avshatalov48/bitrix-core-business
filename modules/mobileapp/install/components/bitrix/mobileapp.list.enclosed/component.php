<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arResult['DATA'] = $arParams['DATA'];
$arResult['JS_CONTAINER_ID'] = isset($arParams['JS_CONTAINER_ID']) ? $arParams['JS_CONTAINER_ID'] : "list_enclosed_".rand();
$arResult['INSCRIPTION_FOR_EMPTY'] = isset($arParams['INSCRIPTION_FOR_EMPTY']) ? $arParams['INSCRIPTION_FOR_EMPTY'] : GetMessage("INSCRIPTION_FOR_EMPTY");

$this->IncludeComponentTemplate();
?>