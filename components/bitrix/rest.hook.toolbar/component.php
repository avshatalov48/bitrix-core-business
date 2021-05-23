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
		"TEXT" => Loc::getMessage("REST_HOOK_LIST"),
		"LINK" => $arParams['LIST_URL'],
		"PARAMS" => array("class" => "list"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "list",
	);
}

$arResult['ITEMS'][] = array(
	"TEXT" => Loc::getMessage("REST_HOOK_ADD"),
	"PARAMS" => array("class" => "webform-small-button-accept"),
	"SELECTED" => $arParams["COMPONENT_PAGE"] == "edit",
	"MENU" => array(
		array(
			"href" => $arParams['EVENT_ADD_URL'],
			"text" => Loc::getMessage('REST_HOOK_ADD_EVENT'),
		),
		array(
			"href" => $arParams['AP_ADD_URL'],
			"text" => Loc::getMessage('REST_HOOK_ADD_AP'),
		),
	)
);

$this->includeComponentTemplate();