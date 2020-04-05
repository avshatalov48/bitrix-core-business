<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

//ib types
$arTypesEx = Array("-"=>" ");
$db_iblock_type = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypesEx[$arRes["ID"]] = $arIBType["NAME"];

//ib
$arIBlocks = Array("-"=>" ");
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
		"SETTINGS" => array(
			"NAME" => GetMessage("SUPPORT_FAQ_EL_GROUP_SETTINGS"),
			"SORT" => 10,
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_EL_SETTING_IBTYPES"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 10,
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_EL_SETTING_IBLIST"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 20,
		),
		"CACHE_TIME"  =>  Array(
			"DEFAULT" => 3600,
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BSFEL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		//"AJAX_MODE" => array(),
	),
);

if(isset($arCurrentValues["IBLOCK_ID"]) && intval($arCurrentValues["IBLOCK_ID"])>0)
{
	$arListSections = Array('-'=>'');
	$arFilter = Array(
		'IBLOCK_ID' => intval($arCurrentValues["IBLOCK_ID"]),
		'GLOBAL_ACTIVE'=>'Y',
		'IBLOCK_ACTIVE'=>'Y',
	);
	if(isset($arCurrentValues["IBLOCK_TYPE"]) && $arCurrentValues["IBLOCK_TYPE"]!='')
		$arFilter['IBLOCK_TYPE'] = $arCurrentValues["IBLOCK_TYPE"];

	$arSec = CIBlockSection::GetList(Array('LEFT_MARGIN'=>'ASC'), $arFilter, false);
	while($arRes = $arSec->Fetch())
		$arListSections[$arRes['ID']] = str_repeat(".", $arRes['DEPTH_LEVEL']).$arRes['NAME'];
}
?>