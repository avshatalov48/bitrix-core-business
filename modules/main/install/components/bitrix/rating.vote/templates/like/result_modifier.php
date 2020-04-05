<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

$arResult["LIKE_TEMPLATE"] = (
	!empty($arParams["LIKE_TEMPLATE"])
		? $arParams["LIKE_TEMPLATE"]
		: 'light'
);
?>