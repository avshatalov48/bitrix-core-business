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

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
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
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")
			),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"
		),
		"SET_TITLE" => Array(),
		"SET_NAV_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600)));
if ($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["USER_ALIAS"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_USER_ALIAS"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["USER_ALIAS"]}');
}

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
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")
	),
	"ADDITIONAL_VALUES" => "Y",
	"DEFAULT" => "SORT");
$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"asc" => GetMessage("IBLOCK_SORT_ASC"),
		"desc" => GetMessage("IBLOCK_SORT_DESC")),
	"DEFAULT" => "asc");
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
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")
	),
	"ADDITIONAL_VALUES" => "Y",
	"DEFAULT" => "");
$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER1"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"asc" => GetMessage("IBLOCK_SORT_ASC"),
		"desc" => GetMessage("IBLOCK_SORT_DESC")),
	"DEFAULT" => "asc");
/*$arComponentParameters["PARAMETERS"]["ELEMENT_FILTER"] = array();
/*$arComponentParameters["PARAMETERS"]["ELEMENT_SELECT_FIELD"] = array();
*/
$arComponentParameters["PARAMETERS"]["PROPERTY_CODE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
	"TYPE" => "LIST",
	"MULTIPLE" => "Y",
	"VALUES" => $arProperty_LNS,
	"ADDITIONAL_VALUES" => "Y");

if($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#");
}
$arComponentParameters["PARAMETERS"]["DETAIL_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
		"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
$arComponentParameters["PARAMETERS"]["DETAIL_EDIT_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_DETAIL_EDIT_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "detail_edit.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
		"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
$arComponentParameters["PARAMETERS"]["DETAIL_SLIDE_SHOW_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_DETAIL_SLIDE_SHOW_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "slide_show.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
		"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEARCH_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SEARCH_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "search.php");
}
$arComponentParameters["PARAMETERS"]["SECTION_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_SECTION_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "section.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#");
$arComponentParameters["PARAMETERS"]["UPLOAD_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_UPLOAD_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "upload.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ACTION=upload");

$arComponentParameters["PARAMETERS"]["USE_PERMISSIONS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");
$arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
	"TYPE" => "LIST",
	"VALUES" => $arUGroupsEx,
	"DEFAULT" => Array(1),
	"MULTIPLE" => "Y");
$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS");
$arComponentParameters["PARAMETERS"]["SHOW_TAGS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("P_SHOW_TAGS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");
$arComponentParameters["PARAMETERS"]["SET_STATUS_404"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("P_SET_STATUS_404"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");
?>