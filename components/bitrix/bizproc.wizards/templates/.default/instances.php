<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:bizproc.workflow.instances",
	"",
	array(
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"ADMIN_ACCESS" => $arParams["ADMIN_ACCESS"],
	),
	$component
);
?>