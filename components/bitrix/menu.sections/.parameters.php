<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("all"=>" "));

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="all"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IS_SEF" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BMS_IS_SEF"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"SEF_BASE_URL" => array(
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BMS_SEF_BASE_URL"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'/catalog/phone/',
		),
		"SECTION_PAGE_URL" => CIBlockParameters::GetPathTemplateParam(
			"SECTION",
			"SECTION_PAGE_URL",
			GetMessage("CP_BMS_SECTION_PAGE_URL"),
			"#SECTION_ID#/",
			"BASE"
		),
		"DETAIL_PAGE_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_PAGE_URL",
			GetMessage("CP_BMS_DETAIL_PAGE_URL"),
			"#SECTION_ID#/#ELEMENT_ID#",
			"BASE"
		),
		"ID" => Array(
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BMS_ID"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'={$_REQUEST["ID"]}',
		),
		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BMS_IBLOCK_TYPE"),
			"TYPE"=>"LIST",
			"VALUES"=>$arTypesEx,
			"DEFAULT"=>"catalog",
			"ADDITIONAL_VALUES"=>"N",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME"=>GetMessage("CP_BMS_IBLOCK_ID"),
			"TYPE"=>"LIST",
			"VALUES"=>$arIBlocks,
			"DEFAULT"=>'1',
			"MULTIPLE"=>"N",
			"ADDITIONAL_VALUES"=>"N",
			"REFRESH" => "Y",
		),
		"SECTION_URL" => CIBlockParameters::GetPathTemplateParam(
			"SECTION",
			"SECTION_URL",
			GetMessage("CP_BMS_SECTION_URL"),
			"",
			"BASE"
		),
		"DEPTH_LEVEL" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BMS_DEPTH_LEVEL"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
	),
);
if($arCurrentValues["IS_SEF"] === "Y")
{
	unset($arComponentParameters["PARAMETERS"]["ID"]);
	unset($arComponentParameters["PARAMETERS"]["SECTION_URL"]);
}
else
{
	unset($arComponentParameters["PARAMETERS"]["SEF_BASE_URL"]);
	unset($arComponentParameters["PARAMETERS"]["DETAIL_PAGE_URL"]);
	unset($arComponentParameters["PARAMETERS"]["SECTION_PAGE_URL"]);
}
?>
