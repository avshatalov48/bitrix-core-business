<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->IncludeComponent(
	"bitrix:sender.ads.list",
	"",
	array(
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'] ?? '',
		'PATH_TO_LIST' => $arResult['PATH_TO_LIST'] ?? '',
		'PATH_TO_ADD' => $arResult['PATH_TO_ADD'] ?? '',
		'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'] ?? '',
		'PATH_TO_TIME' => $arResult['PATH_TO_TIME'] ?? '',
		'PATH_TO_STAT' => $arResult['PATH_TO_STAT'] ?? '',
		'PATH_TO_RECIPIENT' => $arResult['PATH_TO_RECIPIENT'] ?? '',
		'IS_ADS' => $arParams['IS_ADS'] ?? '',
		'SHOW_CAMPAIGNS' => $arParams['SHOW_CAMPAIGNS'] ?? '',
	)
);