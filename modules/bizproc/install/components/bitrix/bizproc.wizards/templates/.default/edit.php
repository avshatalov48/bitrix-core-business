<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:bizproc.wizards.new",
	"",
	array(
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"TASK_VAR" => $arResult["ALIASES"]["task_id"],
		"BLOCK_VAR" => $arResult["ALIASES"]["block_id"],
		"BLOCK_ID" => $arResult["VARIABLES"]["block_id"],
		"PATH_TO_INDEX" => $arResult["PATH_TO_INDEX"],
		"PATH_TO_LIST" => $arResult["PATH_TO_LIST"],
		"PATH_TO_TASK" => $arResult["PATH_TO_TASK"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"ADMIN_ACCESS" => $arParams["ADMIN_ACCESS"],
	),
	$component
);
?>