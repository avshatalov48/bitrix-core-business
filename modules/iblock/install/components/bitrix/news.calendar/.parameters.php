<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	return;
}

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arTypes = CIBlockParameters::GetIBlockTypes();

$arIBlocks = [];
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
if (isset($_REQUEST['site']))
{
	$iblockFilter['SITE_ID'] = $_REQUEST['site'];
}
$db_iblock = CIBlock::GetList(["SORT"=>"ASC"], $iblockFilter);
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arDATE_FIELD = Array(
	"DATE_ACTIVE_FROM" => "[DATE_ACTIVE_FROM] ".GetMessage("T_IBLOCK_DESC_CAL_DATE_ACTIVE_FROM"),
	"DATE_ACTIVE_TO" => "[DATE_ACTIVE_TO] ".GetMessage("T_IBLOCK_DESC_CAL_DATE_ACTIVE_TO"),
	"TIMESTAMP_X" => "[TIMESTAMP_X] ".GetMessage("T_IBLOCK_DESC_CAL_TIMESTAMP_X"),
	"DATE_CREATE" => "[DATE_CREATE] ".GetMessage("T_IBLOCK_DESC_CAL_DATE_CREATE"),
	);

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS"  => [
		"AJAX_MODE" => [],
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES"=>$arTypes,
			"DEFAULT" => "news",
			"MULTIPLE" => "N",
			"REFRESH" => "Y",
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		],
		"MONTH_VAR_NAME" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_MVN"),
			"TYPE" => "STRING",
			"DEFAULT" => "month",
		],
		"YEAR_VAR_NAME" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_YVN"),
			"TYPE" => "STRING",
			"DEFAULT" => "year",
		],
		"WEEK_START" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_WS"),
			"TYPE" => "LIST",
			"DEFAULT" => 1,
			"VALUES" => [
				"0" => GetMessage("T_IBLOCK_DESC_CAL_WS_0"),
				"1" => GetMessage("T_IBLOCK_DESC_CAL_WS_1"),
				"2" => GetMessage("T_IBLOCK_DESC_CAL_WS_2"),
				"3" => GetMessage("T_IBLOCK_DESC_CAL_WS_3"),
				"4" => GetMessage("T_IBLOCK_DESC_CAL_WS_4"),
				"5" => GetMessage("T_IBLOCK_DESC_CAL_WS_5"),
				"6" => GetMessage("T_IBLOCK_DESC_CAL_WS_6"),
			],
		],
		"DATE_FIELD" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_DATE_FIELD"),
			"TYPE" => "LIST",
			"DEFAULT" => "DATE_ACTIVE_FROM",
			"VALUES" => $arDATE_FIELD,
		],
		"TYPE" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_TYPE"),
			"TYPE" => "LIST",
			"DEFAULT" => "EVENTS",
			"VALUES" => [
				"EVENTS" => GetMessage("T_IBLOCK_DESC_CAL_TYPE_EVENTS"),
				"NEWS" => GetMessage("T_IBLOCK_DESC_CAL_TYPE_NEWS"),
			],
		],
		"SHOW_YEAR" => [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_SHOW_YEAR"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_TIME" => [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_SHOW_TIME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"TITLE_LEN" => [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_TITLE_LEN"),
			"TYPE" => "STRING",
			"DEFAULT"=>"0",
		],
		"SET_TITLE" => [],
		"SHOW_CURRENT_DATE" => [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_SHOW_CURRENT_DATE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_MONTH_LIST" => [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_SHOW_MONTH_LIST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"NEWS_COUNT"=> [
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("T_IBLOCK_DESC_CAL_NEWS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "0",
		],
		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("IBLOCK_DETAIL_URL"),
			"",
			"URL_TEMPLATES",
		),
		"CACHE_TIME" => ["DEFAULT"=>36000000],
	],
];
