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
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => ($arCurrentValues["IBLOCK_TYPE"] ?? null), "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList();
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}
if (empty($arCurrentValues["INDEX_URL"]) && !empty($arCurrentValues["SECTIONS_TOP_URL"]))
	$arCurrentValues["INDEX_URL"] = $arCurrentValues["SECTIONS_TOP_URL"];
$arComponentParameters = array(
	"GROUPS" => array(),
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
		"SECTION_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => ''),
		"USER_ALIAS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_USER_ALIAS"),
			"TYPE" => "STRING",
			"DEFAULT" => ''),
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_BEHAVIOUR"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
			"REFRESH" => "Y"),

		"INDEX_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_INDEX_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#"),
		"SECTION_EDIT_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_EDIT_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section_edit.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#"),
		"SECTION_EDIT_ICON_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_EDIT_ICON_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section_edit_icon.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#"),
		"DETAIL_SLIDE_SHOW_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_SLIDE_SHOW_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "slide_show.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
		"UPLOAD_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_UPLOAD_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "upload.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#"),

		"DATE_TIME_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),

		"ALBUM_PHOTO_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "150"),
		"ALBUM_PHOTO_THUMBS_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "70"),
		"RETURN_SECTION_INFO" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_RETURN_SECTION_INFO"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SET_STATUS_404" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_SET_STATUS_404"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"CACHE_TIME"  => array("DEFAULT" => 3600),
		"SET_TITLE" => array(),
		// "DISPLAY_PANEL" => array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
	)
);
if (($arCurrentValues["BEHAVIOUR"] ?? null) == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#");
	$arComponentParameters["PARAMETERS"]["INDEX_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_INDEX_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php");
	$arComponentParameters["PARAMETERS"]["GALLERY_SIZE"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "");
}
?>