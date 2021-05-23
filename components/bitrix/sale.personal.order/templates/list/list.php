<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arChildParams = array(
	"PATH_TO_DETAIL" => $arResult["PATH_TO_DETAIL"],
	"PATH_TO_CANCEL" => $arResult["PATH_TO_CANCEL"],
	"PATH_TO_COPY" => $arResult["PATH_TO_LIST"].'?ID=#ID#',
	"PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
	"PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
	"PATH_TO_CATALOG" => $arParams["PATH_TO_CATALOG"],
	"SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
	"ORDERS_PER_PAGE" => $arParams["ORDERS_PER_PAGE"],
	"SET_TITLE" =>$arParams["SET_TITLE"],
	"ID" => $arResult["VARIABLES"]["ID"],
	"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
	"ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
	"HISTORIC_STATUSES" => $arParams["HISTORIC_STATUSES"],
	"ALLOW_INNER" => $arParams["ALLOW_INNER"],
	"ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],

	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
);

foreach ($arParams as $key => $val)
	if(mb_strpos($key, "STATUS_COLOR_") !== false && mb_strpos($key, "~") !== 0)
		$arChildParams[$key] = $val;

$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.order.list",
	"",
	$arChildParams,
	$component
);
?>
