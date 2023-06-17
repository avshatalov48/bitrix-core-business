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

$arProperty_LNS = array();
if ($iblockExists)
{
	$rsProp = CIBlockProperty::GetList(
		[
			"SORT" => "ASC",
			"NAME" => "ASC",
		],
		[
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"],
		]
	);
	while ($arr = $rsProp->Fetch())
	{
		$arProperty[$arr["CODE"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
		if (in_array($arr["PROPERTY_TYPE"], ["L", "N", "S"]))
		{
			$arProperty_LNS[$arr["CODE"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
		}
	}
}

$arSectProperty_LNS = array();
if ($iblockExists)
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_"
		. $arCurrentValues["IBLOCK_ID"]
		. "_SECTION");
	foreach ($arUserFields as $FIELD_NAME => $arUserField)
	{
		if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "string")
		{
			$arSectProperty_LNS[$FIELD_NAME] = $arUserField["LIST_COLUMN_LABEL"] ?? $FIELD_NAME;
		}
	}
}

$arAscDesc = [
	"asc" => GetMessage("IBLOCK_SORT_ASC"),
	"desc" => GetMessage("IBLOCK_SORT_DESC"),
];

$arUGroupsEx = [];
$dbUGroups = CGroup::GetList();
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$arComponentParameters = [
	"GROUPS" => [
		"RATING_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_RATING_SETTINGS"),
		],
		"REVIEW_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_REVIEW_SETTINGS"),
		],
		"FILTER_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_FILTER_SETTINGS"),
		],
		"TOP_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_TOP_SETTINGS"),
		],
		"LIST_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_SETTINGS"),
		],
		"DETAIL_SETTINGS" => [
			"NAME" => GetMessage("T_IBLOCK_DESC_DETAIL_SETTINGS"),
		],
	],
	"PARAMETERS" => [
		"AJAX_MODE" => [],

		"VARIABLE_ALIASES" => [
			"SECTION_ID" => ["NAME" => GetMessage("SECTION_ID_DESC")],
			"ELEMENT_ID" => ["NAME" => GetMessage("ELEMENT_ID_DESC")],
		],
		"SEF_MODE" => [
			"sections_top" => [
				"NAME" => GetMessage("SECTIONS_TOP_PAGE"),
				"DEFAULT" => "",
				"VARIABLES" => [],
			],
			"section" => [
				"NAME" => GetMessage("SECTION_PAGE"),
				"DEFAULT" => "#SECTION_ID#/",
				"VARIABLES" => ["SECTION_ID"],
			],
			"detail" => [
				"NAME" => GetMessage("DETAIL_PAGE"),
				"DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/",
				"VARIABLES" => ["ELEMENT_ID", "SECTION_ID"],
			],
		],
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		],
		"USE_RATING" => [
			"PARENT" => "RATING_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		],
		"USE_REVIEW" => [
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_REVIEW"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		],
		"USE_FILTER" => [
			"PARENT" => "FILTER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		],
		"SECTION_COUNT" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		],
		"TOP_ELEMENT_COUNT" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_TOP_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "9",
		],
		"TOP_LINE_ELEMENT_COUNT" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_TOP_LINE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "3",
		],
		"SECTION_SORT_FIELD" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => [
				"sort" => GetMessage("IBLOCK_SORT_SORT"),
				"timestamp_x" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
				"name" => GetMessage("IBLOCK_SORT_NAME"),
				"id" => GetMessage("IBLOCK_SORT_ID"),
				"depth_level" => GetMessage("IBLOCK_SORT_DEPTH_LEVEL"),
			],
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "sort",
		],
		"SECTION_SORT_ORDER" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => $arAscDesc,
			"DEFAULT" => "asc",
			"ADDITIONAL_VALUES" => "Y",
		],
		"TOP_ELEMENT_SORT_FIELD" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => [
				"shows" => GetMessage("IBLOCK_SORT_SHOWS"),
				"sort" => GetMessage("IBLOCK_SORT_SORT"),
				"timestamp_x" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
				"name" => GetMessage("IBLOCK_SORT_NAME"),
				"id" => GetMessage("IBLOCK_SORT_ID"),
				"active_from" => GetMessage("IBLOCK_SORT_ACTIVE_FROM"),
				"active_to" => GetMessage("IBLOCK_SORT_ACTIVE_TO"),
			],
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "sort",
		],
		"TOP_ELEMENT_SORT_ORDER" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => $arAscDesc,
			"DEFAULT" => "asc",
			"ADDITIONAL_VALUES" => "Y",
		],
		"TOP_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "TOP_SETTINGS"),
		"TOP_PROPERTY_CODE" => [
			"PARENT" => "TOP_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SECTION_PAGE_ELEMENT_COUNT" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		],
		"SECTION_LINE_ELEMENT_COUNT" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_LINE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "3",
		],
		"ELEMENT_SORT_FIELD" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => [
				"shows" => GetMessage("IBLOCK_SORT_SHOWS"),
				"sort" => GetMessage("IBLOCK_SORT_SORT"),
				"timestamp_x" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
				"name" => GetMessage("IBLOCK_SORT_NAME"),
				"id" => GetMessage("IBLOCK_SORT_ID"),
				"active_from" => GetMessage("IBLOCK_SORT_ACTIVE_FROM"),
				"active_to" => GetMessage("IBLOCK_SORT_ACTIVE_TO"),
			],
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "sort",
		],
		"ELEMENT_SORT_ORDER" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => $arAscDesc,
			"DEFAULT" => "asc",
			"ADDITIONAL_VALUES" => "Y",
		],
		"LIST_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "LIST_SETTINGS"),
		"LIST_PROPERTY_CODE" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		],
		"LIST_BROWSER_TITLE" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("CP_BP_LIST_BROWSER_TITLE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(["-"=>" ", "NAME" => GetMessage("IBLOCK_FIELD_NAME")], $arSectProperty_LNS),
		],
		"META_KEYWORDS" => [
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_KEYWORDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(["-"=>" "],$arProperty_LNS),
		],
		"META_DESCRIPTION" => [
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_DESCRIPTION"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(["-"=>" "],$arProperty_LNS),
		],
		"BROWSER_TITLE" => [
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("CP_BP_BROWSER_TITLE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(["-"=>" ", "NAME" => GetMessage("IBLOCK_FIELD_NAME")], $arProperty_LNS),
		],
		"DETAIL_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DETAIL_SETTINGS"),
		"DETAIL_PROPERTY_CODE" => [
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SET_TITLE" => [],
		"SET_LAST_MODIFIED" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BP_SET_LAST_MODIFIED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		"USE_PERMISSIONS" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		],
		"GROUP_PERMISSIONS" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => [1],
			"MULTIPLE" => "Y",
		],
		"CACHE_TIME"  =>  ["DEFAULT"=>36000000],
		"CACHE_FILTER" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BP_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
	],
];

CIBlockParameters::AddPagerSettings(
	$arComponentParameters,
	GetMessage("T_IBLOCK_DESC_PAGER_PHOTO"), //$pager_title
	true, //$bDescNumbering
	true, //$bShowAllParam
	true, //$bBaseLink
	($arCurrentValues["PAGER_BASE_LINK_ENABLE"] ?? '') ==="Y" //$bBaseLinkEnabled
);

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);

if (($arCurrentValues["USE_FILTER"] ?? 'N') === "Y")
{
	$arComponentParameters["PARAMETERS"]["FILTER_NAME"] = array(
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_FILTER"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	$arComponentParameters["PARAMETERS"]["FILTER_FIELD_CODE"] = CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "FILTER_SETTINGS");
	$arComponentParameters["PARAMETERS"]["FILTER_PROPERTY_CODE"] = array(
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
}
if (($arCurrentValues["USE_PERMISSIONS"] ?? 'N') !== "Y")
{
	unset($arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"]);
}
if (($arCurrentValues["USE_RATING"] ?? 'N') === "Y")
{
	$arComponentParameters["PARAMETERS"]["MAX_VOTE"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
		"TYPE" => "STRING",
		"DEFAULT" => "5",
	);
	$arComponentParameters["PARAMETERS"]["VOTE_NAMES"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
		"TYPE" => "STRING",
		"VALUES" => array(),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("1","2","3","4","5"),
		"ADDITIONAL_VALUES" => "Y",
	);
}
if(!IsModuleInstalled("forum"))
{
	unset($arComponentParameters["GROUPS"]["REVIEW_SETTINGS"]);
	unset($arComponentParameters["PARAMETERS"]["USE_REVIEW"]);
}
elseif(($arCurrentValues["USE_REVIEW"] ?? 'N') === "Y")
{
	$arForumList = array();
	if(CModule::IncludeModule("forum"))
	{
		$rsForum = CForumNew::GetList();
		while($arForum=$rsForum->Fetch())
			$arForumList[$arForum["ID"]]=$arForum["NAME"];
	}
	$arComponentParameters["PARAMETERS"]["MESSAGES_PER_PAGE"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
		"TYPE" => "STRING",
		"DEFAULT" => intval(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))
	);
	$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_USE_CAPTCHA"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_PATH_TO_SMILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/smile/",
	);
	$arComponentParameters["PARAMETERS"]["FORUM_ID"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_FORUM_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arForumList,
		"DEFAULT" => "",
	);
	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_READ_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	$arComponentParameters["PARAMETERS"]["SHOW_LINK_TO_FORUM"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_SHOW_LINK_TO_FORUM"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);
}
