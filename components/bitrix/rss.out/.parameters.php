<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arListSections = array();
if(isset($arCurrentValues["IBLOCK_ID"]) && intval($arCurrentValues["IBLOCK_ID"])>0)
{
	$arFilter = Array(
		'IBLOCK_ID' => intval($arCurrentValues["IBLOCK_ID"]),
		'GLOBAL_ACTIVE'=>'Y',
		'IBLOCK_ACTIVE'=>'Y',
	);

	$arSec = CIBlockSection::GetList(Array('LEFT_MARGIN'=>'ASC'), $arFilter, false, array("ID", "DEPTH_LEVEL", "NAME"));
	while($arRes = $arSec->Fetch())
		$arListSections[$arRes['ID']] = str_repeat(".", $arRes['DEPTH_LEVEL']).$arRes['NAME'];
}

$arSorts = Array(
	"ASC" => GetMessage("CP_BRO_SORT_ASC"),
	"DESC" => GetMessage("CP_BRO_SORT_DESC"),
);

$arSortFields = Array(
		"ID" => GetMessage("CP_BRO_SORT_ID"),
		"NAME" => GetMessage("CP_BRO_SORT_NAME"),
		"ACTIVE_FROM" => GetMessage("CP_BRO_SORT_ACTIVE_FROM"),
		"SORT" => GetMessage("CP_BRO_SORT_SORT"),
		"TIMESTAMP_X" => GetMessage("CP_BRO_SORT_TIMESTAMP_X"),
		"CREATED" => GetMessage("CP_BRO_SORT_CREATED"),
);

$arComponentParameters = array(
	"GROUPS" => array(
		"RSS" => array(
			"NAME" => GetMessage("CP_BRO_RSS"),
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_SECTION_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arListSections,
			"REFRESH" => "Y",
			"DEFAULT" => "",
		),
		"SECTION_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"NUM_NEWS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BRO_NUM_NEWS"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'20',
		),
		"NUM_DAYS" => array(
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BRO_NUM_DAYS"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'30',
		),
		"RSS_TTL" => array(
			"PARENT" => "RSS",
			"NAME"=>GetMessage("CP_BRO_RSS_TTL"),
			"TYPE"=>"STRING",
			"DEFAULT"=>"60",
		),
		"YANDEX" => array(
			"PARENT" => "RSS",
			"NAME"=>GetMessage("CP_BRO_YANDEX"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT"=>"N",
		),
		"SORT_BY1"  =>  Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_BY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER1"  =>  Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_ORDER1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_BY2"  =>  Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_BY2"),
			"TYPE" => "LIST",
			"DEFAULT" => "SORT",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER2"  =>  Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_SORT_ORDER2"),
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"FILTER_NAME" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BRO_FILTER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"CACHE_FILTER" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BRO_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BRO_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>
