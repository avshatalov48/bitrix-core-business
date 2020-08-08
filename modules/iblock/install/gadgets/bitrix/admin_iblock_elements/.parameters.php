<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("iblock"))
	return false;

$arSortBy = array(
	"ID" => GetMessage("GD_IBEL_SORT_BY_ID"),
	"NAME" => GetMessage("GD_IBEL_SORT_BY_NAME"),
	"DATE_ACTIVE_FROM" => GetMessage("GD_IBEL_SORT_BY_DATE_ACTIVE_FROM"),
	"DATE_CREATE" => GetMessage("GD_IBEL_SORT_BY_DATE_CREATE"),
	"TIMESTAMP_X" => GetMessage("GD_IBEL_SORT_BY_TIMESTAMP_X")
);

$arSortOrder= array(
	"ASC" => GetMessage("GD_IBEL_SORT_ORDER_ASC"),
	"DESC" => GetMessage("GD_IBEL_SORT_ORDER_DESC")
);

$arSelect = array(
	"ID" => GetMessage("GD_IBEL_SELECT_ID"),
	"NAME" => GetMessage("GD_IBEL_SELECT_NAME"),
	"DATE_ACTIVE_FROM" => GetMessage("GD_IBEL_SELECT_DATE_ACTIVE_FROM"),
	"DATE_CREATE" => GetMessage("GD_IBEL_SELECT_DATE_CREATE"),
	"TIMESTAMP_X" => GetMessage("GD_IBEL_SELECT_TIMESTAMP_X"),
	"PREVIEW_PICTURE" => GetMessage("GD_IBEL_SELECT_PREVIEW_PICTURE"),
	"PREVIEW_TEXT" => GetMessage("GD_IBEL_SELECT_PREVIEW_TEXT"),
	"DETAIL_PICTURE" => GetMessage("GD_IBEL_SELECT_DETAIL_PICTURE"),
	"DETAIL_TEXT" => GetMessage("GD_IBEL_SELECT_DETAIL_TEXT")
);

$dbIBlock = CIBlock::GetList(
	array("SORT"=>"ASC", "NAME"=>"ASC"), 
	array(
		"CHECK_PERMISSIONS" => "Y", 
		"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W")
	)
);
while($arIBlock = $dbIBlock->GetNext())
	$arIBlock_Types[$arIBlock["IBLOCK_TYPE_ID"]] = $arIBlock;

$arTypes = array("" => GetMessage("GD_IBEL_EMPTY"));
$rsTypes = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arType = $rsTypes->Fetch())
{
	if (is_array($arIBlock_Types) && array_key_exists($arType["ID"], $arIBlock_Types))
	{
		$arType = CIBlockType::GetByIDLang($arType["ID"], LANGUAGE_ID);
		$arTypes[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["NAME"];
	}
}

$arIBlocks = array("" => GetMessage("GD_IBEL_EMPTY"));
if (
	is_array($arAllCurrentValues)
	&& array_key_exists("IBLOCK_TYPE", $arAllCurrentValues)
	&& array_key_exists("VALUE", $arAllCurrentValues["IBLOCK_TYPE"])
	&& $arAllCurrentValues["IBLOCK_TYPE"]["VALUE"] <> ''
)
{
	$dbIBlock = CIBlock::GetList(
		array("SORT" => "ASC"), 
		array(
			"CHECK_PERMISSIONS" => "Y", 
			"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W"), 
			"TYPE" => $arAllCurrentValues["IBLOCK_TYPE"]["VALUE"]
		)
	);
	while($arIBlock = $dbIBlock->GetNext())
		$arIBlocks[$arIBlock["ID"]] = "[".$arIBlock["ID"]."] ".$arIBlock["NAME"];
}

$arIBlockProperties = array();
if (
	is_array($arAllCurrentValues)
	&& array_key_exists("IBLOCK_ID", $arAllCurrentValues)
	&& array_key_exists("VALUE", $arAllCurrentValues["IBLOCK_ID"])
	&& intval($arAllCurrentValues["IBLOCK_ID"]["VALUE"]) > 0
	&& array_key_exists($arAllCurrentValues["IBLOCK_ID"]["VALUE"], $arIBlocks)
)
{

	$dbIBlockProperties = CIBlockProperty::GetList(
		array("SORT" => "ASC"),
		array(
			"IBLOCK_ID" => $arAllCurrentValues["IBLOCK_ID"]["VALUE"],
			"ACTIVE" => "Y"
		)
	);
	while($arIBlockProperty = $dbIBlockProperties->GetNext())
		$arIBlockProperties["PROPERTY_".$arIBlockProperty["CODE"]] = "[".$arIBlockProperty["CODE"]."] ".$arIBlockProperty["NAME"];
}

$arParameters = Array(
	"PARAMETERS"=> Array(),
	"USER_PARAMETERS"=> Array(
		"IBLOCK_TYPE" => Array(
			"NAME" => GetMessage("GD_IBEL_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"REFRESH" => "Y"
		)
	)
);

if (count($arIBlocks) > 0)
	$arParameters["USER_PARAMETERS"]["IBLOCK_ID"] = Array(
		"NAME" => GetMessage("GD_IBEL_IBLOCK_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlocks,
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"REFRESH" => "Y"
	);

$arParameters["USER_PARAMETERS"]["SORT_BY"] = Array(
	"NAME" => GetMessage("GD_IBEL_SORT_BY"),
	"TYPE" => "LIST",
	"VALUES" => $arSortBy,
	"MULTIPLE" => "N",
	"DEFAULT" => "ID"
);

$arParameters["USER_PARAMETERS"]["SORT_ORDER"] = Array(
	"NAME" => GetMessage("GD_IBEL_SORT_ORDER"),
	"TYPE" => "LIST",
	"VALUES" => $arSortOrder,
	"MULTIPLE" => "N",
	"DEFAULT" => "DESC"
);

$arParameters["USER_PARAMETERS"]["TITLE_FIELD"] = Array(
	"NAME" => GetMessage("GD_IBEL_TITLE_FIELD"),
	"TYPE" => "LIST",
	"VALUES" => $arSelect,
	"MULTIPLE" => "N",
	"DEFAULT" => "NAME"
);

$arParameters["USER_PARAMETERS"]["DATE_FIELD"] = Array(
	"NAME" => GetMessage("GD_IBEL_DATE_FIELD"),
	"TYPE" => "LIST",
	"VALUES" => array_merge(array("EMPTY" => GetMessage("GD_IBEL_DONOTUSE")), $arSelect),
	"MULTIPLE" => "N",
	"DEFAULT" => "DATE_ACTIVE_FROM"
);

$arParameters["USER_PARAMETERS"]["PICTURE_FIELD"] = Array(
	"NAME" => GetMessage("GD_IBEL_PICTURE_FIELD"),
	"TYPE" => "LIST",
	"VALUES" => array_merge(array("EMPTY" => GetMessage("GD_IBEL_DONOTUSE")), $arSelect),
	"MULTIPLE" => "N",
	"DEFAULT" => "PREVIEW_PICTURE"
);

$arParameters["USER_PARAMETERS"]["DESCRIPTION_FIELD"] = Array(
	"NAME" => GetMessage("GD_IBEL_DESCRIPTION_FIELD"),
	"TYPE" => "LIST",
	"VALUES" => array_merge(array("EMPTY" => GetMessage("GD_IBEL_DONOTUSE")), $arSelect),
	"MULTIPLE" => "N",
	"DEFAULT" => "PREVIEW_TEXT",
	"REFRESH" => "Y"
);

if (
	!is_array($arAllCurrentValues)
	|| !array_key_exists("DESCRIPTION_FIELD", $arAllCurrentValues)
	|| !array_key_exists("VALUE", $arAllCurrentValues["DESCRIPTION_FIELD"])
	|| $arAllCurrentValues["DESCRIPTION_FIELD"]["VALUE"] != "EMPTY"
)
	$arParameters["USER_PARAMETERS"]["DESCRIPTION_CUT"] = Array(
		"NAME" => GetMessage("GD_IBEL_DESCRIPTION_CUT"),
		"TYPE" => "STRING",
		"DEFAULT" => "500"
	);

$arParameters["USER_PARAMETERS"]["ADDITIONAL_FIELDS"] = Array(
	"NAME" => GetMessage("GD_IBEL_ADDITIONAL_FIELDS"),
	"TYPE" => "LIST",
	"VALUES" => $arSelect,
	"MULTIPLE" => "Y",
	"DEFAULT" => array()
);

if (count($arIBlockProperties) > 0)
{
	$arParameters["USER_PARAMETERS"]["TITLE_FIELD"]["VALUES"] = array_merge($arParameters["USER_PARAMETERS"]["TITLE_FIELD"]["VALUES"], $arIBlockProperties);
	$arParameters["USER_PARAMETERS"]["DATE_FIELD"]["VALUES"] = array_merge($arParameters["USER_PARAMETERS"]["DATE_FIELD"]["VALUES"], $arIBlockProperties);
	$arParameters["USER_PARAMETERS"]["PICTURE_FIELD"]["VALUES"] = array_merge($arParameters["USER_PARAMETERS"]["PICTURE_FIELD"]["VALUES"], $arIBlockProperties);
	$arParameters["USER_PARAMETERS"]["DESCRIPTION_FIELD"]["VALUES"] = array_merge($arParameters["USER_PARAMETERS"]["DESCRIPTION_FIELD"]["VALUES"], $arIBlockProperties);
	$arParameters["USER_PARAMETERS"]["ADDITIONAL_FIELDS"]["VALUES"] = array_merge($arParameters["USER_PARAMETERS"]["ADDITIONAL_FIELDS"]["VALUES"], $arIBlockProperties);
}

$arParameters["USER_PARAMETERS"]["THUMBNAIL_SIZE"] = Array(
	"NAME" => GetMessage("GD_IBEL_THUMBNAIL_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "100"
);

$arParameters["USER_PARAMETERS"]["ITEMS_COUNT"] = Array(
	"NAME" => GetMessage("GD_IBEL_ITEMS_COUNT"),
	"TYPE" => "STRING",
	"DEFAULT" => "10"
);

?>