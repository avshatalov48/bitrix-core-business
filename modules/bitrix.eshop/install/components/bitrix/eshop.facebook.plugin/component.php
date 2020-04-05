<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!$arParams["ESHOP_FACEBOOK_LINK"])
	return;

$arParams["ESHOP_PLUGIN_WIDTH"] = intval($arParams["ESHOP_PLUGIN_WIDTH"]);
if (!$arParams["ESHOP_PLUGIN_WIDTH"])
	$arParams["ESHOP_PLUGIN_WIDTH"] = "230";

$arParams["ESHOP_PLUGIN_HEIGHT"] = intval($arParams["ESHOP_PLUGIN_HEIGHT"]);

$this->IncludeComponentTemplate();
?>