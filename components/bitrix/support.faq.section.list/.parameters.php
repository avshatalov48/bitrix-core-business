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
			"NAME" => GetMessage("SUPPORT_FAQ_SL_GROUP_SETTINGS"),
			"SORT" => 10,
		],
	],
	"PARAMETERS" => [
		"IBLOCK_TYPE" => [
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_SL_SETTING_IBTYPES"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 10,
		],
		"IBLOCK_ID" => [
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_SL_SETTING_IBLIST"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 20,
		],
		"CACHE_TIME"  =>  ["DEFAULT" => 36000000],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BSFSL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"AJAX_MODE" => [],
	],
];

if ($iblockExists)
{
	$arListSections = ['-'=>''];
	$arFilter = [
		'IBLOCK_ID' => intval($arCurrentValues["IBLOCK_ID"]),
		'GLOBAL_ACTIVE'=>'Y',
		'IBLOCK_ACTIVE'=>'Y',
	];
	if(isset($arCurrentValues["IBLOCK_TYPE"]) && $arCurrentValues["IBLOCK_TYPE"]!='')
		$arFilter['IBLOCK_TYPE'] = $arCurrentValues["IBLOCK_TYPE"];

	$arSec = CIBlockSection::GetList(['LEFT_MARGIN'=>'ASC'], $arFilter, false, ["ID", "DEPTH_LEVEL", "NAME"]);
	while($arRes = $arSec->Fetch())
		$arListSections[$arRes['ID']] = str_repeat(".", $arRes['DEPTH_LEVEL']).$arRes['NAME'];

	$arComponentParameters["PARAMETERS"]["SECTION"] = [
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_SL_SETTING_SECTIONS_LIST"),
		"TYPE" => "LIST",
		"VALUES" => $arListSections,
		"SORT" => 30,
	];

	$arComponentParameters["PARAMETERS"]["EXPAND_LIST"] = [
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_SL_SETTING_EXPAND_LIST"),
		"TYPE" => "CHECKBOX",
		"SORT" => 40,
	];

	$arComponentParameters["PARAMETERS"]["SECTION_URL"] = [
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_SL_SETTING_LINK_SECTION_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "faq_detail.php?SECTION_ID=#SECTION_ID#",
		"SORT" => 50,
	];
}
