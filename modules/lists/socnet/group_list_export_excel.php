<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$APPLICATION->IncludeComponent("bitrix:lists.export.excel", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],
	"LIST_FILE_URL" => $arResult["PATH_TO_GROUP_LIST_FILE"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component
);