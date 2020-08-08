<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_ORDER_PAGE'] !== 'Y')
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

$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDERS"), $arResult['PATH_TO_ORDERS']);
$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.order.list",
	"bootstrap_v4",
	array(
		"PATH_TO_DETAIL" => $arResult["PATH_TO_ORDER_DETAIL"],
		"PATH_TO_CANCEL" => $arResult["PATH_TO_ORDER_CANCEL"],
		"PATH_TO_CATALOG" => $arParams["PATH_TO_CATALOG"],
		"PATH_TO_COPY" => $arResult["PATH_TO_ORDER_COPY"],
		"PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
		"PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
		"SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
		"ORDERS_PER_PAGE" => $arParams["ORDERS_PER_PAGE"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
		"ID" => $arResult["VARIABLES"]["ID"],
		"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
		"ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
		"HISTORIC_STATUSES" => $arParams["ORDER_HISTORIC_STATUSES"],
		"ALLOW_INNER" => $arParams["ALLOW_INNER"],
		"ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"DEFAULT_SORT" => $arParams["ORDER_DEFAULT_SORT"],
		"DISALLOW_CANCEL" => $arParams["ORDER_DISALLOW_CANCEL"],
		"RESTRICT_CHANGE_PAYSYSTEM" => $arParams["ORDER_RESTRICT_CHANGE_PAYSYSTEM"],
		"REFRESH_PRICES" => $arParams["ORDER_REFRESH_PRICES"],
		"CONTEXT_SITE_ID" => $arParams["CONTEXT_SITE_ID"],
		"AUTH_FORM_IN_TEMPLATE" => 'Y',
	),
	$component
);
?>

