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
/** @global CIntranetToolbar $INTRANET_TOOLBAR */

global $INTRANET_TOOLBAR;

$component = $this->getComponent();

if (CModule::IncludeModule('intranet'))
{
	$INTRANET_TOOLBAR->Show();
}

$APPLICATION->IncludeComponent(
	'bitrix:socialnetwork.group.list',
	'',
	[
		'PATH_TO_GROUP' => $arResult['PATH_TO_GROUP'],
		'PATH_TO_GROUP_CREATE' => $arParams['PATH_TO_GROUP_CREATE'],
		'PATH_TO_GROUP_EDIT' => $arResult['PATH_TO_GROUP_EDIT'],
		'PATH_TO_GROUP_DELETE' => $arResult['PATH_TO_GROUP_DELETE'],
		'PATH_TO_USER' => $arParams['PATH_TO_USER'],
		'PAGE' => 'groups_list',
		'SET_TITLE' => $arResult['SET_TITLE'],
		'SET_NAV_CHAIN' => $arResult['SET_NAV_CHAIN'],
		'USE_UI_TOOLBAR' => 'Y',
	],
	$component
);
