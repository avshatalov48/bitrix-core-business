<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->IncludeComponent(
	"bitrix:sender.blacklist.list",
	"",
	array(
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_USER_PROFILE'] ?? '',
		'PATH_TO_LIST' => $arResult['PATH_TO_LIST'] ?? '',
		'PATH_TO_IMPORT' => $arResult['PATH_TO_IMPORT'] ?? '',
	)
);