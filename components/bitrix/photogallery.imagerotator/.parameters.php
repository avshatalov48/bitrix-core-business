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
		"APPEARANCE" => array(
			"NAME" => GetMessage("APPEARANCE_GROUP"),
			"SORT" =>100
		),
	),
	"PARAMETERS" => array(
		"WIDTH" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_WIDTH"),
			"DEFAULT" => 300
		),
		"HEIGHT" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_HEIGHT"),
			"DEFAULT" => 300
		),
		"ROTATETIME" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_ROTATETIME"),
			"DEFAULT" => 5
		),
		"BACKCOLOR" => array(
			"TYPE" => "COLORPICKER",
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_BACKCOLOR"),
			"DEFAULT" => "#000000"
		),
		"FRONTCOLOR" => array(
			"TYPE" => "COLORPICKER",
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_FRONTCOLOR"),
			"DEFAULT" => "#FFFFFF"
		),
		"LIGHTCOLOR" => array(
			"TYPE" => "COLORPICKER",
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_LIGHTCOLOR"),
			"DEFAULT" => "#e8e8e8"
		),
		"SCREENCOLOR" => array(
			"TYPE" => "COLORPICKER",
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_SCREENCOLOR"),
			"DEFAULT" => "#FFFFFF"
		),
		"LOGO" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_LOGO"),
			"TYPE" => "FILE",
			"FD_TARGET" => "F",
			"FD_EXT" => "png,gif,jpg,jpeg",
			"FD_UPLOAD" => true,
			"FD_USE_MEDIALIB" => true,
			"FD_MEDIALIB_TYPES" => Array('image'),
			"DEFAULT" => ""
		),
		"OVERSTRETCH" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_OVERSTRETCH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		// "SHUFFLE" => array(
			// "PARENT" => "APPEARANCE",
			// "NAME" => GetMessage("P_IR_SHUFFLE"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"
		// ),
		// "SHOWICONS" => array(
			// "PARENT" => "APPEARANCE",
			// "NAME" => GetMessage("P_IR_SHOWICONS"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"
		// ),
		"SHOWNAVIGATION" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_SHOWNAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"USEFULLSCREEN" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_USEFULLSCREEN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		"TRANSITION" => array(
			"PARENT" => "APPEARANCE",
			"NAME" => GetMessage("P_IR_TRANSITION"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"random" => GetMessage("P_IR_TR_RANDOM"),
				"fade" => GetMessage("P_IR_TR_FADE"),
				"bgfade" => GetMessage("P_IR_TR_BGFADE"),
				"blocks" => GetMessage("P_IR_TR_BLOCKS"),
				"bubbles" => GetMessage("P_IR_TR_BUBBLES"),
				"circles" => GetMessage("P_IR_TR_CIRCLES"),
				"flash" => GetMessage("P_IR_TR_FLASH"),
				"fluids" => GetMessage("P_IR_TR_FLUIDS"),
				"lines" => GetMessage("P_IR_TR_LINES"),
				"slowfade" => GetMessage("P_IR_TR_SLOWFADE")
			),
			"DEFAULT" => "random"
		),	
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
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_BEHAVIOUR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"SIMPLE" => GetMessage("IBLOCK_BEHAVIOUR_SIMPLE"),
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"), 		
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600000))
	);
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
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")),
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
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")),
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

$arComponentParameters["PARAMETERS"]["DETAIL_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
		"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");

$arComponentParameters["PARAMETERS"]["USE_PERMISSIONS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");

?>