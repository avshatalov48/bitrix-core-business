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

$arParams["WIDTH"] = (isset($arParams["WIDTH"]) ? (int)$arParams["WIDTH"] : 0);
if ($arParams["WIDTH"] > 0)
	$arResult["WIDTH"] = $arParams["WIDTH"];

$arResult["ID"] = (isset($arParams["ID"]) && !empty($arParams["ID"]) ? $arParams["ID"] : "cat_tab_".$this->randString());

$this->includeComponentTemplate();