<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_create";
include("util_menu.php");

$componentParameters = array(
	"PATH_TO_USER" => $arResult["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
	"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
	"PAGE_VAR" => $arResult["ALIASES"]["page"],
	"USER_VAR" => $arResult["ALIASES"]["user_id"],
	"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
	"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"USER_ID" => $arResult["VARIABLES"]["user_id"],
	"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
	"USE_AUTOSUBSCRIBE" => "N",
	'THEME_ENTITY_TYPE' => 'SONET_GROUP',
);

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group_create.ex',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
		'POPUP_COMPONENT_PARENT' => $this->getComponent(),
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'POPUP_COMPONENT_BITRIX24_THEME_BEHAVIOUR' => 'return',
	]
);
