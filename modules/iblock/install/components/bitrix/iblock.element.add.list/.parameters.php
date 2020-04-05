<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

if($arCurrentValues["IBLOCK_ID"] > 0)
	$bWorkflowIncluded = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"], "WORKFLOW") == "Y" && CModule::IncludeModule("workflow");
else
	$bWorkflowIncluded = CModule::IncludeModule("workflow");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arProperty_LNSF = array(
	"NAME" => GetMessage("IBLOCK_ADD_NAME"),
	"TAGS" => GetMessage("IBLOCK_ADD_TAGS"),
	"IBLOCK_SECTION" => GetMessage("IBLOCK_ADD_IBLOCK_SECTION"),
	"PREVIEW_TEXT" => GetMessage("IBLOCK_ADD_PREVIEW_TEXT"),
	"PREVIEW_PICTURE" => GetMessage("IBLOCK_ADD_PREVIEW_PICTURE"),
	"DETAIL_TEXT" => GetMessage("IBLOCK_ADD_DETAIL_TEXT"),
	"DETAIL_PICTURE" => GetMessage("IBLOCK_ADD_DETAIL_PICTURE"),
);
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "F")))
	{
		$arProperty_LNSF[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arGroups = array();
$rsGroups = CGroup::GetList($by="c_sort", $order="asc", Array("ACTIVE" => "Y"));
while ($arGroup = $rsGroups->Fetch())
{
	$arGroups[$arGroup["ID"]] = $arGroup["NAME"];
}

if ($bWorkflowIncluded)
{
	$rsWFStatus = CWorkflowStatus::GetList($by="c_sort", $order="asc", Array("ACTIVE" => "Y"), $is_filtered);
	$arWFStatus = array();
	while ($arWFS = $rsWFStatus->Fetch())
	{
		$arWFStatus[$arWFS["ID"]] = $arWFS["TITLE"];
	}
}
else
{
	$arActive = array("ANY" => GetMessage("IBLOCK_STATUS_ANY"), "INACTIVE" => GetMessage("IBLOCK_STATUS_INCATIVE"));
}

$arAllowEdit = array("N" => GetMessage("IBLOCK_ALLOW_N"), "CREATED_BY" => GetMessage("IBLOCK_CREATED_BY"), "PROPERTY_ID" => GetMessage("IBLOCK_PROPERTY_ID"));

$arComponentParameters = array(
	"GROUPS" => array(
		"PARAMS" => array(
			"NAME" => GetMessage("IBLOCK_PARAMS"),
			"SORT" => "200"
		),
		"ACCESS" => array(
			"NAME" => GetMessage("IBLOCK_ACCESS"),
			"SORT" => "400",
		),
	),

	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			/*"edit" => array(
				"NAME" => GetMessage("IBLOCK_SEF_EDIT"),
				"DEFAULT" => "?edit=Y",
			),*/
		),

		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),

		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),

		"GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_GROUPS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),

		"STATUS" => array(
			"PARENT" => "ACCESS",
			"NAME" => $bWorkflowIncluded? GetMessage("CP_BIEAL_IBLOCK_STATUS") : GetMessage("IBLOCK_S_ACTIVE"),
			"TYPE" => "LIST",
			"MULTIPLE" => $bWorkflowIncluded ? "Y" : "N",
			"VALUES" => $bWorkflowIncluded ? $arWFStatus : $arActive,
		),

		"EDIT_URL" => array(
			"PARENT" => "PARAMS",
			"TYPE" => "TEXT",
			"NAME" => GetMessage("IBLOCK_ADD_EDIT_URL"),
		),


		"ELEMENT_ASSOC" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ASSOC"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arAllowEdit,
			"REFRESH" => "Y",
			"DEFAULT" => "CREATED_BY",
		),
	),
);

if ($arCurrentValues["ELEMENT_ASSOC"] == "PROPERTY_ID")
{
	$arComponentParameters["PARAMETERS"]["ELEMENT_ASSOC_PROPERTY"] = array(
		"PARENT" => "ACCESS",
		"NAME" => GetMessage("IBLOCK_ELEMENT_ASSOC_PROPERTY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty,
		"ADDITIONAL_VALUES" => "Y",
	);
}
if ($arCurrentValues["ELEMENT_ASSOC"] != "N")
{
	$arComponentParameters["PARAMETERS"]["ALLOW_EDIT"] = array(
		"PARENT" => "ACCESS",
		"NAME" => GetMessage("IBLOCK_ALLOW_EDIT"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);

	$arComponentParameters["PARAMETERS"]["ALLOW_DELETE"] = array(
		"PARENT" => "ACCESS",
		"NAME" => GetMessage("IBLOCK_ALLOW_DELETE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);
}


$arComponentParameters["PARAMETERS"]["NAV_ON_PAGE"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_NAV_ON_PAGE"),
	"TYPE" => "TEXT",
	"DEFAULT" => "10",
);

$arComponentParameters["PARAMETERS"]["MAX_USER_ENTRIES"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_MAX_USER_ENTRIES"),
	"TYPE" => "TEXT",
	"DEFAULT" => "100000",
);

?>