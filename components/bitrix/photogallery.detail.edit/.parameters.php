<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}'),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}'),
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_BEHAVIOUR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"SIMPLE" => GetMessage("IBLOCK_BEHAVIOUR_SIMPLE"),
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"),
		"USER_ALIAS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_USER_ALIAS"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["USER_ALIAS"]}'),
		"ACTION" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_ACTION"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ACTION"]}'),

		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#"),
		"DETAIL_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
			
		"DATE_TIME_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SET_STATUS_404" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_SET_STATUS_404"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"), 
		
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
		"SET_TITLE" => Array(),
	),
);
if($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_SIZE"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "");
	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#");
}
?>
