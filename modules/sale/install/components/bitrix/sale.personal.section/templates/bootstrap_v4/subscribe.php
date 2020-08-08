<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_SUBSCRIBE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

global $USER;
if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized())
{
	LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_SUBSCRIBE_NEW"));
$APPLICATION->IncludeComponent(
	'bitrix:catalog.product.subscribe.list',
	"bootstrap_v4",
	array(
		'SET_TITLE' => $arParams['SET_TITLE'],
		'DETAIL_URL' => $arParams['SUBSCRIBE_DETAIL_URL']
	),
	$component
);

