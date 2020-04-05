<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_PROFILE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}


if (strlen($arParams["MAIN_CHAIN_NAME"]) > 0)
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PROFILE"));
$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.profile.list",
	"bootstrap_v4",
	array(
		"PATH_TO_DETAIL" => $arResult['PATH_TO_PROFILE_DETAIL'],
		"PATH_TO_DELETE" => $arResult['PATH_TO_PROFILE_DELETE'],
		"PER_PAGE" => $arParams["PROFILES_PER_PAGE"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
	),
	$component
);
?>
