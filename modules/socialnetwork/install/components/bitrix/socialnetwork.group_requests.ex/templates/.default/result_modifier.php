<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arResult["PATH_TO_GROUP_INVITE"] = $arResult["Urls"]["GroupEdit"].(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=invite";
?>