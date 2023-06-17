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

//ib types
$arTypesEx = ["-"=>" "];
$db_iblock_type = CIBlockType::GetList(["SORT"=>"ASC"]);
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypesEx[$arRes["ID"]] = $arIBType["NAME"];

//ib
$arIBlocks = ["-"=>" "];
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
	$arIBlocks[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arComponentParameters = [
	"GROUPS" => [
		"SETTINGS" => [
			"NAME" => GetMessage("SUPPORT_FAQ_EL_GROUP_SETTINGS"),
			"SORT" => 10,
		],
		"RATING_SETTINGS" => [
			"NAME" => GetMessage("SUPPORT_RATING_SETTINGS"),
			"SORT" => 20,
		],
	],
	"PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_EL_SETTING_IBTYPES"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 10,
		],
		"IBLOCK_ID" => [
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_EL_SETTING_IBLIST"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 20,
		],
		"SHOW_RATING" => [
			"NAME" => GetMessage("SHOW_RATING"),
			"TYPE" => "LIST",
			"VALUES" => [
				"" => GetMessage("SHOW_RATING_CONFIG"),
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO"),
			],
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "RATING_SETTINGS",
		],
		"RATING_TYPE" => [
			"NAME" => GetMessage("RATING_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => [
				"" => GetMessage("RATING_TYPE_CONFIG"),
				"like" => GetMessage("RATING_TYPE_LIKE_TEXT"),
				"like_graphic" => GetMessage("RATING_TYPE_LIKE_GRAPHIC"),
				"standart_text" => GetMessage("RATING_TYPE_STANDART_TEXT"),
				"standart" => GetMessage("RATING_TYPE_STANDART_GRAPHIC"),
			],
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "RATING_SETTINGS",
		],
		"PATH_TO_USER" => [
			"NAME" => GetMessage("PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 50,
			"PARENT" => "RATING_SETTINGS",
		],
		"CACHE_TIME"  =>  ["DEFAULT" => 36000000],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BSFEL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"AJAX_MODE" => [],
	],
];

if ($iblockExists)
{
	$arComponentParameters["PARAMETERS"]["SECTION_ID"] = [
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_EL_SETTING_SECTIONS_LIST"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		"SORT" => 30,
	];
}
