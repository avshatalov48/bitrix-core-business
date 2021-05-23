<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if (!CModule::IncludeModule("rest"))
{
	return;
}

$arParams["SEARCH_URL"] = !empty($arParams["SEARCH_URL"]) ? $arParams["SEARCH_URL"] : "/marketplace/";
$arResult["SEARCH"] = isset($_GET['q']) ? trim($_GET['q']) : '';

$this->IncludeComponentTemplate();
