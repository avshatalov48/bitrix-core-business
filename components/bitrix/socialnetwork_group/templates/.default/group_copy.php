<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_copy";

include("util_group_menu.php");

$componentParameters = [
	"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
];
/*
$APPLICATION->IncludeComponent(
	'bitrix:socialnetwork.group.card.menu',
	'',
	[
		'GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'TAB' => 'copy',
		'URLS' => ComponentHelper::getWorkgroupSliderMenuUrlList($arResult),
		'SIGNED_PARAMETERS' => ComponentHelper::listWorkgroupSliderMenuSignedParameters($componentParameters),
	]
);
*/
$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group_copy',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
	]
);
