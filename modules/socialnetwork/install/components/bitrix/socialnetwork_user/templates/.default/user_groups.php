<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


$pageId = "user_groups";
include("util_menu.php");
include("util_profile.php");

$APPLICATION->AddHeadScript("/bitrix/js/socialnetwork/sonet-iframe-popup.js");

$componentParams = [
	'USER_ID' => $arResult['VARIABLES']['user_id'],
	'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
	'PATH_TO_GROUP_CREATE' => $arResult['PATH_TO_GROUP_CREATE'],
	'PATH_TO_GROUP_EDIT' => $arResult['PATH_TO_GROUP_EDIT'],
	'PATH_TO_GROUP_DELETE' => $arResult['PATH_TO_GROUP_DELETE'],
	'PATH_TO_USER' => $arResult['PATH_TO_USER'],
	'PAGE' => $pageId,
	'SET_TITLE' => $arResult['SET_TITLE'],
	'SET_NAV_CHAIN' => $arResult['SET_NAV_CHAIN'],
	'MODE' => \Bitrix\Socialnetwork\Component\WorkgroupList::MODE_USER,
];

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	[
		'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.group.list",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => $componentParams,
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
		'USE_UI_TOOLBAR' => 'Y',
	]
);
