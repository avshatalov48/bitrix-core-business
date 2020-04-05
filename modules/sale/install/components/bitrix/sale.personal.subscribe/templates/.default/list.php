<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.subscribe.list",
	"",
	array(
		"PATH_TO_CANCEL" => $arResult["PATH_TO_CANCEL"],
		"PER_PAGE" => $arParams["PER_PAGE"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
	),
	$component
);
?>
