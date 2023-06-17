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

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = [];
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(["SORT" => "ASC"], $iblockFilter);
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arListSections = [];
if ($iblockExists)
{
	$arFilter = [
		'IBLOCK_ID' => (int)$arCurrentValues["IBLOCK_ID"],
		'GLOBAL_ACTIVE' =>'Y',
		'IBLOCK_ACTIVE' =>'Y',
	];

	$arSec = CIBlockSection::GetList(['LEFT_MARGIN'=>'ASC'], $arFilter, false, ["ID", "DEPTH_LEVEL", "NAME"]);
	while($arRes = $arSec->Fetch())
	{
		$arListSections[$arRes['ID']] = str_repeat(".", (int)$arRes['DEPTH_LEVEL']) . $arRes['NAME'];
	}
}

$arSorts = [
	"ASC" => GetMessage("CP_BRO_SORT_ASC"),
	"DESC" => GetMessage("CP_BRO_SORT_DESC"),
];

$arSortFields = [
	"ID" => GetMessage("CP_BRO_SORT_ID"),
	"NAME" => GetMessage("CP_BRO_SORT_NAME"),
	"ACTIVE_FROM" => GetMessage("CP_BRO_SORT_ACTIVE_FROM"),
	"SORT" => GetMessage("CP_BRO_SORT_SORT"),
	"TIMESTAMP_X" => GetMessage("CP_BRO_SORT_TIMESTAMP_X"),
	"CREATED" => GetMessage("CP_BRO_SORT_CREATED"),
];

$arComponentParameters = [
	"GROUPS" => [
		"RSS" => [
			"NAME" => GetMessage("CP_BRO_RSS"),
		],
	],
	"PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		],
		"SECTION_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_SECTION_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arListSections,
			"REFRESH" => "Y",
			"DEFAULT" => "",
		],
		"SECTION_CODE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		"NUM_NEWS" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_NUM_NEWS"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'20',
		],
		"NUM_DAYS" => [
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BRO_NUM_DAYS"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'30',
		],
		"RSS_TTL" => [
			"PARENT" => "RSS",
			"NAME"=>GetMessage("CP_BRO_RSS_TTL"),
			"TYPE"=>"STRING",
			"DEFAULT"=>"60",
		],
		"YANDEX" => [
			"PARENT" => "RSS",
			"NAME"=>GetMessage("CP_BRO_YANDEX"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT"=>"N",
		],
		"SORT_BY1"  =>  [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_BY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SORT_ORDER1"  =>  [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_ORDER1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SORT_BY2"  =>  [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_BY2"),
			"TYPE" => "LIST",
			"DEFAULT" => "SORT",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SORT_ORDER2"  =>  [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_ORDER2"),
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		],
		"FILTER_NAME" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_FILTER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		"CACHE_TIME"  =>  ["DEFAULT"=>3600],
		"CACHE_FILTER" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BRO_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BRO_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
	],
];
