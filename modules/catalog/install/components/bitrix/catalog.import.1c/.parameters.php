<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes(array("-" => GetMessage("CP_BCI1_CREATE")));

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
$arSites = array(
	"-" => GetMessage("CP_BCI1_CURRENT"),
);
while ($arSite = $rsSite->GetNext())
{
	$arSites[$arSite["LID"]] = $arSite["NAME"];
}

$arAction = array(
	"N" => GetMessage("CP_BCI1_NONE"),
	"A" => GetMessage("CP_BCI1_DEACTIVATE"),
	"D" => GetMessage("CP_BCI1_DELETE"),
);

$arComponentParameters = array(
	"GROUPS" => array(
		"PICTURE" => array(
			"NAME" => GetMessage("CP_BCI1_PICTURE"),
		),
		"TRANSLIT" => array(
			"NAME" => GetMessage("CP_BCI1_TRANSLIT"),
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
		),
		"SITE_LIST" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_SITE_LIST"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arSites,
		),
		"INTERVAL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_INTERVAL"),
			"TYPE" => "STRING",
			"DEFAULT" => 30,
		),
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => array(1),
			"MULTIPLE" => "Y",
		),
		"USE_OFFERS" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_USE_OFFERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"FORCE_OFFERS" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_FORCE_OFFERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"USE_IBLOCK_TYPE_ID" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_USE_IBLOCK_TYPE_ID"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SKIP_ROOT_SECTION" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_SKIP_ROOT_SECTION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"ELEMENT_ACTION" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_ELEMENT_ACTION"),
			"TYPE" => "LIST",
			"VALUES" => $arAction,
			"DEFAULT" => "D",
		),
		"SECTION_ACTION" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_SECTION_ACTION"),
			"TYPE" => "LIST",
			"VALUES" => $arAction,
			"DEFAULT" => "D",
		),
		"FILE_SIZE_LIMIT" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_FILE_SIZE_LIMIT"),
			"TYPE" => "STRING",
			"DEFAULT" => 200*1024,
		),
		"USE_CRC" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_USE_CRC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"USE_ZIP" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_USE_ZIP"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SKIP_SOURCE_CHECK" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_SKIP_SOURCE_CHECK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
	),
);

$arComponentParameters["PARAMETERS"]["USE_IBLOCK_PICTURE_SETTINGS"] = array(
	"PARENT" => "PICTURE",
	"NAME" => GetMessage("CP_BCI1_USE_IBLOCK_PICTURE_SETTINGS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

if($arCurrentValues["USE_IBLOCK_PICTURE_SETTINGS"] !== "Y")
{
	$arComponentParameters["PARAMETERS"]["GENERATE_PREVIEW"] = array(
		"PARENT" => "PICTURE",
		"NAME" => GetMessage("CP_BCI1_GENERATE_PREVIEW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	);

	if($arCurrentValues["GENERATE_PREVIEW"]!="N")
	{
		$arComponentParameters["PARAMETERS"]["PREVIEW_WIDTH"] = array(
			"PARENT" => "PICTURE",
			"NAME" => GetMessage("CP_BCI1_PREVIEW_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => 100,
		);
		$arComponentParameters["PARAMETERS"]["PREVIEW_HEIGHT"] = array(
			"PARENT" => "PICTURE",
			"NAME" => GetMessage("CP_BCI1_PREVIEW_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => 100,
		);
	}

	$arComponentParameters["PARAMETERS"]["DETAIL_RESIZE"] = array(
		"PARENT" => "PICTURE",
		"NAME" => GetMessage("CP_BCI1_DETAIL_RESIZE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	);

	if($arCurrentValues["DETAIL_RESIZE"]!="N")
	{
		$arComponentParameters["PARAMETERS"]["DETAIL_WIDTH"] = array(
			"PARENT" => "PICTURE",
			"NAME" => GetMessage("CP_BCI1_DETAIL_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => 300,
		);
		$arComponentParameters["PARAMETERS"]["DETAIL_HEIGHT"] = array(
			"PARENT" => "PICTURE",
			"NAME" => GetMessage("CP_BCI1_DETAIL_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => 300,
		);
	}
}

$arComponentParameters["PARAMETERS"]["TRANSLIT_ON_ADD"] = array(
	"PARENT" => "TRANSLIT",
	"NAME" => GetMessage("CP_BCI1_TRANSLIT_ON_ADD"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

$arComponentParameters["PARAMETERS"]["TRANSLIT_ON_UPDATE"] = array(
	"PARENT" => "TRANSLIT",
	"NAME" => GetMessage("CP_BCI1_TRANSLIT_ON_UPDATE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

if($arCurrentValues["TRANSLIT_ON_ADD"] === "Y" || $arCurrentValues["TRANSLIT_ON_UPDATE"] === "Y")
{
	$arComponentParameters["PARAMETERS"]["TRANSLIT_MAX_LEN"] = array(
		"PARENT" => "TRANSLIT",
		"NAME" => GetMessage("CP_BCI1_TRANSLIT_MAX_LEN"),
		"TYPE" => "STRING",
		"DEFAULT" => "100",
	);
	$arComponentParameters["PARAMETERS"]["TRANSLIT_CHANGE_CASE"] = array(
		"PARENT" => "TRANSLIT",
		"NAME" => GetMessage("CP_BCI1_TRANSLIT_CHANGE_CASE"),
		"TYPE" => "LIST",
		"DEFAULT" => "L",
		"VALUES" => array(
			"" => GetMessage("CP_BCI1_TRANSLIT_CHANGE_CASE_PRESERVE"),
			"L" => GetMessage("CP_BCI1_TRANSLIT_CHANGE_CASE_LOWER"),
			"U" => GetMessage("CP_BCI1_TRANSLIT_CHANGE_CASE_UPPER"),
		),
	);
	$arComponentParameters["PARAMETERS"]["TRANSLIT_REPLACE_SPACE"] = array(
		"PARENT" => "TRANSLIT",
		"NAME" => GetMessage("CP_BCI1_TRANSLIT_REPLACE_SPACE"),
		"TYPE" => "STRING",
		"DEFAULT" => "_",
	);
	$arComponentParameters["PARAMETERS"]["TRANSLIT_REPLACE_OTHER"] = array(
		"PARENT" => "TRANSLIT",
		"NAME" => GetMessage("CP_BCI1_TRANSLIT_REPLACE_OTHER"),
		"TYPE" => "STRING",
		"DEFAULT" => "_",
	);
	$arComponentParameters["PARAMETERS"]["TRANSLIT_DELETE_REPEAT_REPLACE"] = array(
		"PARENT" => "TRANSLIT",
		"NAME" => GetMessage("CP_BCI1_TRANSLIT_DELETE_REPEAT_REPLACE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);
}
?>
