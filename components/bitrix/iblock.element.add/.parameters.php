<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if(!Loader::includeModule("iblock"))
{
	return;
}

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

if ($iblockExists)
{
	$arIBlock = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"]);

	$bWorkflowIncluded = ($arIBlock["WORKFLOW"] == "Y") && Loader::includeModule("workflow");
	$bBizproc = ($arIBlock["BIZPROC"] == "Y") && Loader::includeModule("bizproc");
}
else
{
	$bWorkflowIncluded = Loader::includeModule("workflow");
	$bBizproc = false;
}

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), $iblockFilter);
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arProperty_LNSF = array(
	"NAME" => GetMessage("IBLOCK_ADD_NAME"),
	"TAGS" => GetMessage("IBLOCK_ADD_TAGS"),
	"DATE_ACTIVE_FROM" => GetMessage("IBLOCK_ADD_ACTIVE_FROM"),
	"DATE_ACTIVE_TO" => GetMessage("IBLOCK_ADD_ACTIVE_TO"),
	"IBLOCK_SECTION" => GetMessage("IBLOCK_ADD_IBLOCK_SECTION"),
	"PREVIEW_TEXT" => GetMessage("IBLOCK_ADD_PREVIEW_TEXT"),
	"PREVIEW_PICTURE" => GetMessage("IBLOCK_ADD_PREVIEW_PICTURE"),
	"DETAIL_TEXT" => GetMessage("IBLOCK_ADD_DETAIL_TEXT"),
	"DETAIL_PICTURE" => GetMessage("IBLOCK_ADD_DETAIL_PICTURE"),
);
$arVirtualProperties = $arProperty_LNSF;

$arProperty = [];
if ($iblockExists)
{
	$rsProp = CIBlockProperty::GetList(
		[
			"SORT" => "ASC",
			"NAME" => "ASC",
		],
		[
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]
		]
	);
	while ($arr = $rsProp->Fetch())
	{
		$arProperty[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
		if (in_array($arr["PROPERTY_TYPE"], ["L", "N", "S", "F"]))
		{
			$arProperty_LNSF[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
		}
	}
}

$arGroups = array();
$rsGroups = CGroup::GetList("c_sort", "asc", Array("ACTIVE" => "Y"));
while ($arGroup = $rsGroups->Fetch())
{
	$arGroups[$arGroup["ID"]] = $arGroup["NAME"];
}

if ($bWorkflowIncluded)
{
	$rsWFStatus = CWorkflowStatus::GetList("c_sort", "asc", Array("ACTIVE" => "Y"));
	$arWFStatus = array();
	while ($arWFS = $rsWFStatus->Fetch())
	{
		$arWFStatus[$arWFS["ID"]] = $arWFS["TITLE"];
	}
}
else
{
	$arActive = array("ANY" => GetMessage("IBLOCK_STATUS_ANY"), "INACTIVE" => GetMessage("IBLOCK_STATUS_INCATIVE"));
	$arActiveNew = array("N" => GetMessage("IBLOCK_ALLOW_N"), "NEW" => GetMessage("IBLOCK_ACTIVE_NEW_NEW"), "ANY" => GetMessage("IBLOCK_ACTIVE_NEW_ANY"));
}

$arAllowEdit = array("CREATED_BY" => GetMessage("IBLOCK_CREATED_BY"), "PROPERTY_ID" => GetMessage("IBLOCK_PROPERTY_ID"));

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
		"FIELDS" => array(
			"NAME" => GetMessage("IBLOCK_FIELDS"),
			"SORT" => "300",
		),
		"TITLES" => array(
			"NAME" => GetMessage("IBLOCK_TITLES"),
			"SORT" => "1000",
		),
	),

	"PARAMETERS" => array(
		"SEF_MODE" => Array(),
		"AJAX_MODE" => array(),
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

		"PROPERTY_CODES" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNSF,
		),

		"PROPERTY_CODES_REQUIRED" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("IBLOCK_PROPERTY_REQUIRED"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arProperty_LNSF,
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
			"NAME" => $bWorkflowIncluded? GetMessage("IBLOCK_STATUS_STATUS"): GetMessage("IBLOCK_STATUS_ACTIVE"),
			"TYPE" => "LIST",
			"MULTIPLE" => $bWorkflowIncluded ? "Y" : "N",
			"VALUES" => $bWorkflowIncluded ? $arWFStatus : $arActive,
		),

		"STATUS_NEW" => array(
			"PARENT" => "ACCESS",
			"NAME" => $bWorkflowIncluded? GetMessage("IBLOCK_STATUS_NEW"): ($bBizproc? GetMessage("IBLOCK_BP_NEW"): GetMessage("IBLOCK_ACTIVE_NEW")),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $bWorkflowIncluded ? $arWFStatus : $arActiveNew,
		),

		"ALLOW_EDIT" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ALLOW_EDIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),

		"ALLOW_DELETE" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ALLOW_DELETE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
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

if (($arCurrentValues["ELEMENT_ASSOC"] ?? 'CREATED_BY') === "PROPERTY_ID")
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

$arComponentParameters["PARAMETERS"]["NAV_ON_PAGE"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_NAV_ON_PAGE"),
	"TYPE" => "TEXT",
	"DEFAULT" => "10",
);

$arComponentParameters["PARAMETERS"]["MAX_USER_ENTRIES"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_MAX_USER_ENTRIES"),
	"TYPE" => "TEXT",
	"DEFAULT" => "100000",
);

$arComponentParameters["PARAMETERS"]["MAX_LEVELS"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_MAX_LEVELS"),
	"TYPE" => "TEXT",
	"DEFAULT" => "100000",
);

$arComponentParameters["PARAMETERS"]["LEVEL_LAST"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_LEVEL_LAST"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);

$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_USE_CAPTCHA"),
	"TYPE" => "CHECKBOX",
);

$arComponentParameters["PARAMETERS"]["USER_MESSAGE_ADD"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_USER_MESSAGE_ADD"),
	"TYPE" => "TEXT",
);

$arComponentParameters["PARAMETERS"]["USER_MESSAGE_EDIT"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_USER_MESSAGE_EDIT"),
	"TYPE" => "TEXT",
);

$arComponentParameters["PARAMETERS"]["DEFAULT_INPUT_SIZE"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_DEFAULT_INPUT_SIZE"),
	"TYPE" => "TEXT",
	"DEFAULT" => 30,
);

$arComponentParameters["PARAMETERS"]["RESIZE_IMAGES"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("CP_BIEA_RESIZE_IMAGES"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);

$arComponentParameters["PARAMETERS"]["MAX_FILE_SIZE"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_MAX_FILE_SIZE"),
	"TYPE" => "TEXT",
	"DEFAULT" => "0",
);

$arComponentParameters["PARAMETERS"]["PREVIEW_TEXT_USE_HTML_EDITOR"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("CP_BIEA_PREVIEW_TEXT_USE_HTML_EDITOR"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);

$arComponentParameters["PARAMETERS"]["DETAIL_TEXT_USE_HTML_EDITOR"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("CP_BIEA_DETAIL_TEXT_USE_HTML_EDITOR"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);

foreach ($arVirtualProperties as $key => $title)
{
	$arComponentParameters["PARAMETERS"]["CUSTOM_TITLE_".$key] = array(
		"PARENT" => "TITLES",
		"NAME" => $title,
		"TYPE" => "STRING",
	);
}
