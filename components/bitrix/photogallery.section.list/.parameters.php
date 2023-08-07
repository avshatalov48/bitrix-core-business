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
/*		"USER_ALIAS" => array(),
*/
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_BEHAVIOUR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"SIMPLE" => GetMessage("IBLOCK_BEHAVIOUR_SIMPLE"),
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"
		),

		"PHOTO_LIST_MODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_PHOTO_LIST_MODE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"
		)
	)
);
if (($arCurrentValues["PHOTO_LIST_MODE"] ?? null) != "N")
{
	$arComponentParameters["PARAMETERS"]["SHOWN_ITEMS_COUNT"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("P_SHOWN_ITEMS_COUNT"),
		"DEFAULT" => "6"
	);

	$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_FIELD"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"SHOW_COUNTER" => GetMessage("IBLOCK_SORT_SHOWS"),
			"SORT" => GetMessage("IBLOCK_SORT_SORT"),
			"TIMESTAMP_X" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
			"NAME" => GetMessage("IBLOCK_SORT_NAME"),
			"ID" => GetMessage("IBLOCK_SORT_ID"),
			"PROPERTY_RATING" => GetMessage("IBLOCK_SORT_RATING"),
			"PROPERTY_FORUM_MESSAGE_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_FORUM"),
			"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")),
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "SORT"
	);
	$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"asc" => GetMessage("IBLOCK_SORT_ASC"),
			"desc" => GetMessage("IBLOCK_SORT_DESC")),
		"DEFAULT" => "asc"
	);
	$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_FIELD1"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD1"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"SHOW_COUNTER" => GetMessage("IBLOCK_SORT_SHOWS"),
			"SORT" => GetMessage("IBLOCK_SORT_SORT"),
			"TIMESTAMP_X" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
			"NAME" => GetMessage("IBLOCK_SORT_NAME"),
			"ID" => GetMessage("IBLOCK_SORT_ID"),
			"PROPERTY_RATING" => GetMessage("IBLOCK_SORT_RATING"),
			"PROPERTY_FORUM_MESSAGE_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_FORUM"),
			"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")),
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => ""
	);
	$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER1"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"asc" => GetMessage("IBLOCK_SORT_ASC"),
			"desc" => GetMessage("IBLOCK_SORT_DESC")),
		"DEFAULT" => "asc"
	);

	$arComponentParameters["PARAMETERS"]["SECTION_LIST_THUMBNAIL_SIZE"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("P_SECTION_LIST_THUMBS_SIZE"),
		"DEFAULT" => "70"
	);
}

if (($arCurrentValues["BEHAVIOUR"] ?? null) == "USER")
{
	$arComponentParameters["PARAMETERS"]["USER_ALIAS"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_USER_ALIAS"),
			"TYPE" => "STRING",
			"DEFAULT" => '');
}
if (empty($arCurrentValues["INDEX_URL"]) && !empty($arCurrentValues["SECTIONS_TOP_URL"]))
{
	$arCurrentValues["INDEX_URL"] = $arCurrentValues["SECTIONS_TOP_URL"];
}
$arComponentParameters["PARAMETERS"] = array_merge($arComponentParameters["PARAMETERS"], array(
		"SORT_BY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ID" => "ID",
				"NAME" => GetMessage("IBLOCK_SORT_NAME"),
				"SORT" => GetMessage("IBLOCK_SORT_SORT"),
				"ELEMENT_CNT" => GetMessage("IBLOCK_SORT_ELEMENTS_CNT"),
				"UF_DATE" => GetMessage("IBLOCK_SORT_DATE")),
			"DEFAULT" => "UF_DATE"
		),
		"SORT_ORD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ASC" => GetMessage("IBLOCK_SORT_ASC"),
				"DESC" => GetMessage("IBLOCK_SORT_DESC")),
			"DEFAULT" => "ASC"
		),

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
		"UPLOAD_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_UPLOAD_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "upload.php?".(($arCurrentValues["BEHAVIOUR"] ?? null) == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#"),

		"ALBUM_PHOTO_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "200"),
		"ALBUM_PHOTO_THUMBS_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "120"
		),

		"PAGE_ELEMENTS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 0),
		"PAGE_NAVIGATION_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"DATE_TIME_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SET_STATUS_404" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_SET_STATUS_404"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),

		"SET_TITLE" => array(),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N")
	));


if (($arCurrentValues["BEHAVIOUR"] ?? null) == "USER")
{

	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#");
	$arComponentParameters["PARAMETERS"]["GALLERY_SIZE"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "");
}
?>