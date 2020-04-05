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
			"NAME" => GetMessage("SUPPORT_FAQ_ED_GROUP_SETTINGS"),
			"SORT" => 10,
		),
		"RATING_SETTINGS" => array(
			"NAME" => GetMessage("SUPPORT_RATING_SETTINGS"),
			"SORT" => 20,
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_ED_SETTING_IBTYPES"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 10,
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("SUPPORT_FAQ_ED_SETTING_IBLIST"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => "-",
			"REFRESH" => "Y",
			"SORT" => 20,
		),
		"CACHE_TIME"  =>  Array("DEFAULT" => 36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BSFED_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"AJAX_MODE" => array(),
		"SHOW_RATING" => array(
			"NAME" => GetMessage("SHOW_RATING"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("SHOW_RATING_CONFIG"),
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "RATING_SETTINGS",
		),
		"RATING_TYPE" => Array(
			"NAME" => GetMessage("RATING_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("RATING_TYPE_CONFIG"),
				"like" => GetMessage("RATING_TYPE_LIKE_TEXT"),
				"like_graphic" => GetMessage("RATING_TYPE_LIKE_GRAPHIC"),
				"standart_text" => GetMessage("RATING_TYPE_STANDART_TEXT"),
				"standart" => GetMessage("RATING_TYPE_STANDART_GRAPHIC"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "RATING_SETTINGS",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 50,
			"PARENT" => "RATING_SETTINGS",
		),
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

	$arSec = CIBlockSection::GetList(Array('LEFT_MARGIN'=>'ASC'), $arFilter, false, array("ID", "DEPTH_LEVEL", "NAME"));
	while($arRes = $arSec->Fetch())
		$arListSections[$arRes['ID']] = str_repeat(".", $arRes['DEPTH_LEVEL']).$arRes['NAME'];

	$arComponentParameters["PARAMETERS"]["SECTION_ID"] = Array(
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_ED_SETTING_SECTIONS_LIST"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		"SORT" => 30,
	);

	$arComponentParameters["PARAMETERS"]["ELEMENT_ID"] = Array(
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("SUPPORT_FAQ_ED_SETTING_ELEMENTS_LIST"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		"SORT" => 30,
	);
}
?>