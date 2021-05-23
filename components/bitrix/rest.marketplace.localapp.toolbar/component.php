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

use Bitrix\Main\Localization\Loc;

if(!\Bitrix\Main\Loader::includeModule('rest'))
{
	return;
}

$arResult['ITEMS'] = array();

if($arParams["COMPONENT_PAGE"] !== "list")
{
	$arResult['ITEMS'][] = array(
		"TEXT" => Loc::getMessage("MARKETPLACE_LIST"),
		"LINK" => $arParams['LIST_URL'],
		"PARAMS" => array("class" => "list"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "list",
	);
}

if($arParams["COMPONENT_PAGE"] !== "edit")
{
	$arResult['ITEMS'][] = array(
		"TEXT" => Loc::getMessage("MARKETPLACE_ADD"),
		"LINK" => $arParams['ADD_URL'],
		"PARAMS" => array("class" => "webform-small-button-accept"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "edit",
	);
}

$this->includeComponentTemplate();