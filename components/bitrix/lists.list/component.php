<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));
	return;
}

$IBLOCK_ID = intval($arParams["~IBLOCK_ID"]);
if(isset($_REQUEST["list_section_id"]))
	$section_id = intval($_REQUEST["list_section_id"]);
else
	$section_id = intval($arParams["~SECTION_ID"]);

$arResult['IS_SOCNET_GROUP_CLOSED'] = false;
if (
	intval($arParams["~SOCNET_GROUP_ID"] ?? null) > 0
	&& CModule::IncludeModule("socialnetwork")
)
{
	$arSonetGroup = CSocNetGroup::GetByID(intval($arParams["~SOCNET_GROUP_ID"]));
	if (
		is_array($arSonetGroup)
		&& $arSonetGroup["CLOSED"] == "Y"
		&& !CSocNetUser::IsCurrentUserModuleAdmin()
		&& (
			$arSonetGroup["OWNER_ID"] != $GLOBALS["USER"]->GetID()
			|| COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y"
		)
	)
	{
		$arResult["IS_SOCNET_GROUP_CLOSED"] = true;
	}
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	$IBLOCK_ID,
	$arParams["~SOCNET_GROUP_ID"] ?? null
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLL_UNKNOWN_ERROR"));
		return;
	}
}
elseif(
	$lists_perm < CListPermissions::CAN_READ
	&& !(
		CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_read")
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "section_element_bind")
	)
)
{
	ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] =	(
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		$lists_perm >= CListPermissions::IS_ADMIN
		|| CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit")
	)
);
$arResult["CAN_ADD_ELEMENT"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		$lists_perm > CListPermissions::CAN_READ
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "section_element_bind")
	)
);
$arResult["CAN_READ"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		$lists_perm > CListPermissions::CAN_READ
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "element_read")
	)
);
$arResult["CAN_EDIT_SECTIONS"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		$lists_perm >= CListPermissions::CAN_WRITE
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "section_edit")
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $section_id, "section_section_bind")
	)
);
$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = $arIBlock;
$arResult["IBLOCK_ID"] = $arIBlock["ID"];
$arResult["PROCESSES"] = false;
$arResult["USE_COMMENTS"] = false;
$arResult["RAND_STRING"] = $this->randString();
$arResult['JS_OBJECT'] = 'ListClass_'.$arResult['RAND_STRING'];
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$arResult["USE_COMMENTS"] = (bool)CModule::includeModule("forum");
	$arResult["PROCESSES"] = true;
}

if (
	$arResult["IBLOCK"]["BIZPROC"] == "Y"
	&& CModule::IncludeModule('bizproc')
	&& CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"])
)
{
	$arParams["CAN_EDIT_BIZPROC"] = (
		!$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$USER->GetID(),
			BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $IBLOCK_ID),
			array("UserGroups" => $arResult["USER_GROUPS"])
		)
	);
}

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
{
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
}
else
{
	$arParams["SOCNET_GROUP_ID"] = "";
}

$APPLICATION->SetTitle(htmlspecialcharsbx($arResult["IBLOCK"]["NAME"]));

$arResult["GRID_ID"] = "lists_list_elements_".$arResult["IBLOCK_ID"];
$arResult["FILTER_ID"] = "lists_list_elements_".$arResult["IBLOCK_ID"];

$arResult["ANY_SECTION"] = (isset($_REQUEST["list_section_id"]) && $_REQUEST["list_section_id"] == '')
	|| (!isset($_REQUEST["list_section_id"]));
$arResult["SECTION"] = false;
$arResult["SECTION_ID"] = false;
$arResult["PARENT_SECTION_ID"] = false;
$sectionUpperUrl = CHTTP::urlAddParams(str_replace(array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams['LIST_URL']), array('list_section_id' => ""));
$arResult["SECTIONS"] = array(
	array(
		"NAME" => GetMessage("CC_BLL_UPPER_LEVEL"),
		"NAME_HTML" => '<a href="'.$sectionUpperUrl .'">'.GetMessage("CC_BLL_UPPER_LEVEL").'</a>',
	)
);
$arResult["LIST_SECTIONS"] = array("" => GetMessage("CC_BLL_UPPER_LEVEL"));
$arResult["~LIST_SECTIONS"] = array("" => GetMessage("CC_BLL_UPPER_LEVEL"));
$arResult["SECTION_PATH"] = array();
$sectionTreeId = array();
$sectionListParentId = array();

$rsSections = CIBlockSection::GetList(
	array("left_margin" => "asc"),
	array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"GLOBAL_ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::CAN_READ? "N": "Y"),
	),
	false,
	array()
);
while($arSection = $rsSections->GetNext())
{
	if($section_id && !$arResult["SECTION"])
	{
		while(count($arResult["SECTION_PATH"]) && $arSection["DEPTH_LEVEL"] <= $arResult["SECTION_PATH"]
			[count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_pop($arResult["SECTION_PATH"]);

		if(!count($arResult["SECTION_PATH"])|| $arSection["DEPTH_LEVEL"] > $arResult["SECTION_PATH"]
			[count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_push($arResult["SECTION_PATH"], $arSection);
	}

	if($arSection["ID"] == $section_id)
	{
		$arResult["SECTION"] = $arSection;
		$arResult["SECTION_ID"] = intval($arSection["ID"]);
		$arResult["PARENT_SECTION_ID"] = $arSection["IBLOCK_SECTION_ID"];
	}

	$arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
	$arResult["~LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["~NAME"];

	$sectionUrl = CHTTP::URN2URI(CHTTP::urlAddParams(str_replace(array("#list_id#", "#section_id#", "#group_id#"),
		array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
		$arParams['LIST_URL']), array('list_section_id' => $arSection["ID"])));

	$arResult["SECTIONS"][$arSection["ID"]] = array(
		"ID" => $arSection["ID"],
		"NAME" => $arSection["NAME"],
		"LIST_URL" => str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arSection["IBLOCK_ID"], $arSection["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams['LIST_URL']
		),
		"PARENT_ID" => intval($arSection["IBLOCK_SECTION_ID"]),
		"SECTION_URL" => $sectionUrl,
		"NAME_HTML_LABLE" => '<a href="'.$sectionUrl .'">'.htmlspecialcharsbx($arSection["~NAME"]).'</a>',
		"NAME_HTML" => '<a href="'.$sectionUrl .'">'.htmlspecialcharsbx(str_repeat(
			" . ", ($arSection["DEPTH_LEVEL"])).$arSection["~NAME"]).'</a>',
		"DEPTH_LEVEL" => intval($arSection["DEPTH_LEVEL"])
	);
}

foreach($arResult["SECTION_PATH"] as $i => $arSection)
{
	$arResult["SECTION_PATH"][$i] = array(
		"NAME" => $arSection["NAME"],
		"URL" => str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arIBlock["ID"], intval($arSection["ID"]), $arParams["SOCNET_GROUP_ID"]),
			$arParams["LIST_URL"]
		),
	);
}

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_FIELDS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_FIELDS_URL"]
);
$arResult["LIST_FIELDS_URL"] = htmlspecialcharsbx($arResult["~LIST_FIELDS_URL"]);

$arResult["~LIST_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_SECTION_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
);
$arResult["LIST_SECTION_URL"] = htmlspecialcharsbx($arResult["~LIST_SECTION_URL"]);

$parentSectionUrl = $arResult["PARENT_SECTION_ID"] ? $arResult["PARENT_SECTION_ID"] : "";
$arResult["~LIST_PARENT_URL"] = CHTTP::urlAddParams(str_replace(array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams['LIST_URL']), array('list_section_id' => $parentSectionUrl));
$arResult["LIST_PARENT_URL"] = htmlspecialcharsbx($arResult["~LIST_PARENT_URL"]);

$arResult["~BIZPROC_WORKFLOW_ADMIN_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~BIZPROC_WORKFLOW_ADMIN_URL"]
);
$arResult["BIZPROC_WORKFLOW_ADMIN_URL"] = htmlspecialcharsbx($arResult["~BIZPROC_WORKFLOW_ADMIN_URL"]);

$arResult["~EXPORT_EXCEL_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~EXPORT_EXCEL_URL"]
);
$arResult["EXPORT_EXCEL_URL"] = htmlspecialcharsbx($arResult["~EXPORT_EXCEL_URL"]);

$obList = new CList($arIBlock["ID"]);

$strError = "";
$errorID = $arResult["GRID_ID"]."_error";

//Form submitted
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& (
		isset($_POST["action_button_".$arResult["GRID_ID"]])
	)
)
{
	$obSection = new CIBlockSection;
	$obElement = new CIBlockElement;

	/*Build filter*/
	$arFilter = array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
	);

	if (isset($_POST["action_all_rows_".$arResult["GRID_ID"]]) && $_POST["action_all_rows_".$arResult["GRID_ID"]] === "Y")
	{
		if(!$arResult["ANY_SECTION"])
			$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];
	}
	else
	{
		$arFilter["=ID"] = (is_array($_POST["ID"]) ? $_POST["ID"] : []);
	}

	/*Take action*/
	if ($_POST["action_button_".$arResult["GRID_ID"]] == "delete" && !empty($arFilter["=ID"]))
	{
		$arFilter["SHOW_NEW"] = "Y";

		$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
		while($arElement = $rsElements->Fetch())
		{
			if(
				!$arResult["IS_SOCNET_GROUP_CLOSED"]
				&& (
					$lists_perm >= CListPermissions::CAN_WRITE
					|| CIBlockElementRights::UserHasRightTo($arIBlock["ID"], $arElement["ID"], "element_delete")
				)
			)
			{
				$DB->StartTransaction();
				$APPLICATION->ResetException();
				if(!$obElement->Delete($arElement["ID"]))
				{
					$DB->Rollback();
					if($ex = $APPLICATION->GetException())
						$strError = GetMessage("CC_BLL_DELETE_ERROR")." ".$ex->GetString();
					else
						$strError = GetMessage("CC_BLL_DELETE_ERROR")." ".GetMessage("CC_BLL_UNKNOWN_ERROR");
					break;
				}
				else
				{
					$DB->Commit();
				}
			}
		}
	}
}

//Show error message if required
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['error']))
{
	if ($_GET['error'] === $errorID)
	{
		if (!isset($_SESSION[$errorID]))
		{
			LocalRedirect(CHTTP::urlDeleteParams($APPLICATION->GetCurPage(), array('error')));
		}

		$strError = strval($_SESSION[$errorID]);
		unset($_SESSION[$errorID]);
		if ($strError !== '')
		{
			ShowError(htmlspecialcharsbx($strError));
		}
	}
}

$arResult["GRID_MESSAGES"] = array();
if ($strError <> "")
{
	$arResult["GRID_MESSAGES"] = array(
		array(
			"TYPE" => Bitrix\Main\Grid\MessageType::ERROR,
			"TEXT" => $strError
		)
	);
}

$grid_options = new Bitrix\Main\Grid\Options($arResult["GRID_ID"]);
$grid_columns = $grid_options->GetVisibleColumns();
$grid_sort = $grid_options->GetSorting(array("sort"=>array("name"=>"asc")));

if (
	$arResult["IBLOCK"]["BIZPROC"]=="Y"
	&& CModule::IncludeModule('bizproc')
	&& CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"])
)
{
	$arDocumentTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(
		BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
		false
	);
	$arResult["BIZPROC"] = "Y";
}
else
{
	$arDocumentTemplates = array();
	$arResult["BIZPROC"] = "N";
}

/* FIELDS */
$arResult["ELEMENTS_HEADERS"] = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"default" => false,
		"sort" => "ID"
	)
);
$ignoreSortFields = array("S:Money", "PREVIEW_TEXT", "DETAIL_TEXT", "S:ECrm", "S:map_yandex");
$arSelect = array("ID", "IBLOCK_ID");
$arProperties = array();
$arResult["FIELDS"] = $listFields = $obList->GetFields();
foreach($listFields as $FIELD_ID => $arField)
{
	if(!count($grid_columns) || in_array($FIELD_ID, $grid_columns))
	{
		if(mb_substr($FIELD_ID, 0, 9) == "PROPERTY_")
			$arProperties[] = $FIELD_ID;
		else
			$arSelect[] = $FIELD_ID;
	}

	if($FIELD_ID == "CREATED_BY")
		$arSelect[] = "CREATED_USER_NAME";

	if($FIELD_ID == "MODIFIED_BY")
		$arSelect[] = "USER_NAME";

	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => $FIELD_ID,
		"name" => $arField["NAME"],
		"default" => true,
		"sort" => $arField["MULTIPLE"] == "Y" || in_array($arField["TYPE"], $ignoreSortFields)? "": $FIELD_ID,
	);
}
if(!count($grid_columns) || in_array("IBLOCK_SECTION_ID", $grid_columns))
{
	$arSelect[] = "IBLOCK_SECTION_ID";
}
$arResult["ELEMENTS_HEADERS"][] = array(
	"id" => "IBLOCK_SECTION_ID",
	"name" => GetMessage("CC_BLL_COLUMN_SECTION"),
	"default" => true,
	"sort" => false,
);
if(count($arDocumentTemplates) > 0)
{
	$arSelect[] = "CREATED_BY";
	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => "BIZPROC",
		"name" => GetMessage("CC_BLL_COLUMN_BIZPROC"),
		"default" => true,
		"sort" => false,
	);
}
if($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
{
	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => "COMMENTS",
		"name" => GetMessage("CC_BLL_COMMENTS"),
		"default" => true,
		"sort" => false,
		'hideName' => true,
		'iconCls' => 'bp-comments-icon',
	);
}

if (CLists::isEnabledLockFeature($IBLOCK_ID))
{
	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => "LOCK_STATUS",
		"name" => GetMessage("CC_BLL_EXTERNAL_LOCK"),
		"default" => true,
		"sort" => false,
	);
}

/* FILTER */
$sections = array('' => GetMessage("CC_BLL_ANY"));
foreach($arResult["~LIST_SECTIONS"] as $id => $name)
	$sections[$id] = $name;

$arResult["FILTER"] = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "list_section_id",
		"name" => GetMessage("CC_BLL_SECTION"),
		"type" => "list",
		"items" => $sections,
		"filtered" => $arResult["SECTION_ID"] !== false,
		"filter_value" => $arResult["SECTION_ID"],
		"value" => $arResult["SECTION_ID"],
	),
);

$filterable = array("ID" => "");
$dateFilter = array();
$customFilter = array();
$listNotFilterField = array("PREVIEW_PICTURE", "DETAIL_PICTURE", "F", "S:DiskFile");
$arResult["FILTER_CUSTOM_ENTITY"] = array();
foreach($listFields as $fieldId => $field)
{
	if(in_array($field["TYPE"], $listNotFilterField)) continue;

	if(is_array($field["PROPERTY_USER_TYPE"])
		&& array_key_exists("GetPublicFilterHTML", $field["PROPERTY_USER_TYPE"]))
	{
		$field["GRID_ID"] = $arResult["GRID_ID"];
		$field["FILTER_ID"] = $arResult["FILTER_ID"];
	}

	// todo Temporary condition
	if($field["TYPE"] == "S:ECrm" && !empty($field["USER_TYPE_SETTINGS"]["VISIBLE"]))
		unset($field["USER_TYPE_SETTINGS"]["VISIBLE"]);

	$preparedField = Bitrix\Lists\Field::prepareFieldDataForFilter($field);

	$filterable[$preparedField["id"]] = $preparedField["filterable"];
	if(!empty($preparedField["dateFilter"]))
	{
		$dateFilter[$preparedField["id"]] = true;
	}
	if(!empty($preparedField["customFilter"]))
	{
		$customFilter[$preparedField["id"]] = $preparedField["customFilter"];
	}
	if(isset($preparedField['type']) && $preparedField["type"] === "custom_entity")
	{
		if(!empty($field["PROPERTY_USER_TYPE"]["USER_TYPE"]))
			$fieldType = $field["PROPERTY_USER_TYPE"]["USER_TYPE"];
		else
			$fieldType = $field["TYPE"];
		$field["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
		$field["IBLOCK_TYPE_ID"] = $arParams["IBLOCK_TYPE_ID"];
		if (!isset($arResult["FILTER_CUSTOM_ENTITY"][$fieldType]) || !is_array($arResult["FILTER_CUSTOM_ENTITY"][$fieldType]))
		{
			$arResult["FILTER_CUSTOM_ENTITY"][$fieldType] = [];
		}
		$arResult["FILTER_CUSTOM_ENTITY"][$fieldType][] = $field;
	}

	$arResult["FILTER"][] = $preparedField;
}

$arFilter = array();
$filterOption = new Bitrix\Main\UI\Filter\Options($arResult["FILTER_ID"]);
$filterData = $filterOption->getFilter($arResult["FILTER"]);
foreach($filterData as $key => $value)
{
	if (is_array($value))
	{
		if (empty($value))
			continue;
	}
	elseif($value == '')
		continue;

	if(mb_substr($key, -5) == "_from")
	{
		$new_key = mb_substr($key, 0, -5);
		$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "more") ? ">" : ">=";
	}
	elseif(mb_substr($key, -3) == "_to")
	{
		$new_key = mb_substr($key, 0, -3);
		$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "less") ? "<" : "<=";
		if(array_key_exists($new_key, $dateFilter))
		{
			$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
			$dateParse = date_parse_from_format($dateFormat, $value);
			if(!mb_strlen($dateParse["hour"]) && !mb_strlen($dateParse["minute"]) && !mb_strlen($dateParse["second"]))
			{
				$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
				$value .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
			}
		}
	}
	else
	{
		$op = "";
		$new_key = $key;
	}

	if($key == "CREATED_BY" || $key == "MODIFIED_BY")
	{
		if(!intval($value))
		{
			$userId = array();
			$userQuery = CUser::GetList(
				"ID",
				"ASC",
				array("NAME" => $value),
				array("FIELDS" => array("ID"))
			);
			while($user = $userQuery->fetch())
				$userId[] = $user["ID"];
			if(!empty($userId))
				$value = $userId;
		}
	}

	if(array_key_exists($new_key, $filterable))
	{
		if($op == "")
			$op = $filterable[$new_key];
		$arFilter[$op.$new_key] = $value;
	}

	if($key == "FIND" && trim($value))
	{
		$op = "*";
		$arFilter[$op."SEARCHABLE_CONTENT"] = $value;
	}
}

foreach($customFilter as $fieldId => $callback)
{
	$filtered = false;
	call_user_func_array($callback, array(
		$arResult["FIELDS"][$fieldId],
		array(
			"VALUE" => $fieldId,
			"FILTER_ID" => $arResult["FILTER_ID"],
		),
		&$arFilter,
		&$filtered,
	));
}

if(!empty($filterData["list_section_id"]))
{
	$arResult["ANY_SECTION"] = false;
	$arResult["SECTION_ID"] = $filterData["list_section_id"];
}

$arFilter["IBLOCK_ID"] = $arIBlock["ID"];
$arFilter["CHECK_PERMISSIONS"] = ($lists_perm >= CListPermissions::CAN_READ? "N": "Y");
$listChildSection = array();
if (!$arResult["ANY_SECTION"])
{
	CLists::getChildSection($arResult["SECTION_ID"], $arResult["SECTIONS"], $listChildSection);
	$arFilter["SECTION_ID"] = $listChildSection;
}

$arResult["ELEMENTS_ROWS"] = array();
$arResult["SHOW_SECTION_GRID"] = CUserOptions::getOption("lists_show_section_grid", $arResult["GRID_ID"], "N");
if ($arResult["SHOW_SECTION_GRID"] == "Y" && !array_key_exists("*SEARCHABLE_CONTENT", $arFilter))
{
	foreach($arResult["SECTIONS"] as $section)
	{
		if ($arResult["ANY_SECTION"])
		{
			if (($section["DEPTH_LEVEL"] ?? 0) != 1)
			{
				continue;
			}
		}
		else
		{
			$currentDepthLevel = (int) $arResult["SECTION"]["DEPTH_LEVEL"];
			$sectionDepthLevel = (int)($section["DEPTH_LEVEL"] ?? null);
			if (
				$sectionDepthLevel <= $currentDepthLevel
				|| (abs($sectionDepthLevel - $currentDepthLevel) > 1)
				|| !in_array($section["ID"], $listChildSection)
			)
			{
				continue;
			}
		}

		$sectionActions = array();
		if(!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::userHasRightTo($arIBlock["ID"], $section["ID"], "section_read")))
		{
			$sectionActions[] = array(
				"text" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_OPEN"),
				"onclick" => 'document.location.href="'.$section["SECTION_URL"].'"',
				"default" => true,
			);
		}
		if(!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::userHasRightTo($arIBlock["ID"], $section["ID"], "section_edit")))
		{
			$sectionActions[] = array(
				"text" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_EDIT"),
				"onclick" => "BX.Lists['".$arResult['JS_OBJECT']."'].editSection('".$section["ID"]."')",
			);
		}
		if(!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::userHasRightTo($arIBlock["ID"], $section["ID"], "section_delete")))
		{
			$sectionActions[] = array(
				"text" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE"),
				"onclick" => "BX.Lists['".$arResult['JS_OBJECT']."'].deleteSection('".
					$arResult["GRID_ID"]."', '".$section["ID"]."')",
			);
		}
		$arResult["ELEMENTS_ROWS"][] = array(
			"id" => $section["ID"],
			'editable' => false,
			"data" => array(),
			"actions" => $sectionActions,
			"columns" => array(
				"NAME" => '<table><tr><td class="lists-section-icon"><span></span></td>
				<td class="lists-section-text">'.$section["NAME_HTML_LABLE"].'</td></tr></table>',
			),
		);
	}

	if ($arResult["ANY_SECTION"])
	{
		$arFilter["SECTION_ID"] = false;
	}
	else
	{
		$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];
	}
}

if ($arParams["CAN_EDIT"])
{
	$arFilter["SHOW_NEW"] = "Y";
}

$request = Bitrix\Main\Context::getCurrent()->getRequest();
$session = Bitrix\Main\Application::getInstance()->getSession();

if ($request->isAjaxRequest() && $request->get('action') == 'getTotalCount')
{
	$totalCount = \CIBlockElement::getList(
		$grid_sort['sort'],
		$arFilter,
		[],
		false,
		['ID']
	);

	$response = new Bitrix\Main\Engine\Response\Json($totalCount);

	$response = Bitrix\Main\Context::getCurrent()->getResponse()->copyHeadersTo($response);

	Bitrix\Main\Application::getInstance()->end(0, $response);
}

$pageNum = 1;
if (
	!Bitrix\Main\Grid\Context::isInternalRequest()
	&& !($request->get('clear_nav') === 'Y')
	&& isset($session['LISTS_LIST_PAGINATION_DATA'][$arResult['GRID_ID']])
)
{
	$paginationData = $session['LISTS_LIST_PAGINATION_DATA'][$arResult['GRID_ID']];
	if (isset($paginationData['PAGE_NUM']))
	{
		$pageNum = (int) $paginationData['PAGE_NUM'];
	}
}

$nav = new \Bitrix\Main\UI\PageNavigation($arResult['GRID_ID']);
$nav->setPageSize($grid_options->getNavParams()['nPageSize']);
$nav->setCurrentPage($pageNum);
$nav->initFromUri();

if (Bitrix\Main\Grid\Context::isInternalRequest())
{
	if (!isset($session['LISTS_LIST_PAGINATION_DATA']))
	{
		$session['LISTS_LIST_PAGINATION_DATA'] = [];
	}
	$session['LISTS_LIST_PAGINATION_DATA'][$arResult['GRID_ID']] = ['PAGE_NUM' => $nav->getCurrentPage()];
}

/** @var CIBlockResult $rsElements */
$rsElements = CIBlockElement::getList(
	$grid_sort['sort'],
	$arFilter,
	false,
	[
		'nOffset' => $nav->getOffset(),
		'nTopCount' => $nav->getLimit() + 1,
	],
	$arSelect
);

if ($arResult["BIZPROC"] == "Y")
{
	$arUserGroupsForBP = CUser::GetUserGroup($USER->GetID());
	$arDocumentStatesForBP = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
		BizprocDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arIBlock["ID"])
	);
}
else
{
	$arUserGroupsForBP = array();
	$arDocumentStatesForBP = array();
}

$processesWithComments = null;

if ($arResult["PROCESSES"])
{
	$arResult["USE_COMMENTS"] = (bool) CModule::includeModule("forum");
	$processesWithComments = ($arResult["PROCESSES"] && $arResult["USE_COMMENTS"]);
}

$isBizprocActive = $arResult["BIZPROC"] == "Y";
$isBizprocVisible = empty($grid_columns) || in_array('BIZPROC', $grid_columns);
$userId = $USER->GetID();

$n = 0;

$listValues = array();
while ($obElement = $rsElements->GetNextElement())
{
	if (++$n > $nav->getLimit())
	{
		break;
	}

	$arResult["CAN_EXPORT"] = true;
	$columns = array();
	$data = $obElement->GetFields();
	if(!is_array($data))
		continue;

	if ($isBizprocActive)
	{
		$documentComplexType = BizprocDocument::generateDocumentComplexType($arIBlock["IBLOCK_TYPE_ID"], $arIBlock["ID"]);
		$documentComplexId = BizprocDocument::getDocumentComplexId($arIBlock["IBLOCK_TYPE_ID"], $data["ID"]);

		$canStartBizproc = CBPDocument::canUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$userId,
			$documentComplexId,
			[
				"IBlockId" => $arIBlock["ID"],
				"AllUserGroups" => $arUserGroupsForBPTmp ?? null,
				"DocumentStates" => $arDocumentStatesForBP,
				"WorkflowId" => isset($arWorkflowTemplate, $arWorkflowTemplate['ID']) ? $arWorkflowTemplate["ID"] : null,
			]
		);

		$documentStates = CBPDocument::getActiveStates($documentComplexId, 5);
		if (empty($documentStates))
		{
			$workflowIds = CBPStateService::getIdsByDocument($documentComplexId, 1);
			if ($workflowIds)
			{
				$documentStates = CBPStateService::getDocumentStates($documentComplexId, $workflowIds);
			}
		}
	}

	if(!is_array($listValues[$data["ID"]] ?? null))
		$listValues[$data["ID"]] = array();

	foreach($data as $fieldId => $fieldValue)
		$listValues[$data["ID"]][$fieldId] = $fieldValue;

	if(!empty($arProperties))
	{
		$propertyValuesObject = \CIblockElement::getPropertyValues($arIBlock["ID"],
			array("ID" => $data["ID"], "SHOW_NEW" => ($arParams["CAN_EDIT"] ? "Y" : "N")));
		while($propertyValues = $propertyValuesObject->fetch())
		{
			foreach($propertyValues as $propertyId => $propertyValue)
			{
				if($propertyId == "IBLOCK_ELEMENT_ID")
					continue;
				$listValues[$data["ID"]]['PROPERTY_'.$propertyId] = $propertyValue;
			}
		}
	}

	$iblockSectionId = 0;
	if(!empty($data["IBLOCK_SECTION_ID"]) && array_key_exists($data["IBLOCK_SECTION_ID"], $arResult["SECTIONS"]))
		$iblockSectionId = $data["IBLOCK_SECTION_ID"];

	foreach($arResult["FIELDS"] as $fieldId => $field)
	{
		$field["LIST_SECTIONS_URL"] = $arParams["LIST_SECTIONS_URL"];
		$field["LIST_URL"] = $arParams["LIST_URL"];
		$field["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		$field["LIST_ELEMENT_URL"] = $arParams["~LIST_ELEMENT_URL"];
		$field["LIST_FILE_URL"] = $arParams["~LIST_FILE_URL"];
		$field["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
		$field["SECTION_ID"] = $iblockSectionId;
		$field["ELEMENT_ID"] = $data["ID"];
		$field["FIELD_ID"] = $fieldId;
		$valueKey = (mb_substr($fieldId, 0, 9) == "PROPERTY_") ? $fieldId : "~".$fieldId;
		$field["VALUE"] = $listValues[$data["ID"]][$valueKey] ?? null;
		$columns[$fieldId] = \Bitrix\Lists\Field::renderField($field);
	}

	if($iblockSectionId)
	{
		$sectionName = array();
		$columns["IBLOCK_SECTION_ID"] = $arResult["SECTIONS"][0]["NAME_HTML"];
		$parentId = $arResult["SECTIONS"][$iblockSectionId]["PARENT_ID"];
		if($parentId)
		{
			for($count = 1; $count < $arResult["SECTIONS"][$iblockSectionId]["DEPTH_LEVEL"]; $count++)
			{
				foreach($arResult["SECTIONS"] as $sectionId => $sectionData)
				{
					if($sectionId == $parentId)
					{
						$sectionName[] = $sectionData["NAME_HTML"];
						if($sectionData["PARENT_ID"])
							$parentId = $sectionData["PARENT_ID"];
					}
				}
			}
			krsort($sectionName);
			foreach($sectionName as $name)
				$columns["IBLOCK_SECTION_ID"] .= "<br>".$name;
		}
		$columns["IBLOCK_SECTION_ID"] .= "<br>".$arResult["SECTIONS"][$iblockSectionId]["NAME_HTML"];
	}

	$arBPStart = array();
	if ($isBizprocActive)
	{
		if ($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
		{
			if (!empty($documentStates))
			{
				$stateTemporary = current($documentStates);
				$data["WORKFLOW_ID"] = $stateTemporary["ID"];
			}
			else
			{
				$data["WORKFLOW_ID"] = '';
			}
		}

		$backUrl = $APPLICATION->GetCurPageParam(
			"", array("bxajaxid", "grid_action", "grid_id", "internal", "sessid"));
		$arUserGroupsForBPTmp = $arUserGroupsForBP;
		if ($USER->GetID() == ($data["CREATED_BY"] ?? 0))
		{
			$arUserGroupsForBPTmp[] = "Author";
		}
		foreach($arDocumentTemplates as $arWorkflowTemplate)
		{
			if ($canStartBizproc)
			{
				$url = CHTTP::urlAddParams(str_replace(
					array("#list_id#", "#section_id#", "#element_id#", "#workflow_template_id#", "#group_id#"),
					array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]),
						$arWorkflowTemplate["ID"], $arParams["SOCNET_GROUP_ID"]),
					$arParams["BIZPROC_WORKFLOW_START_URL"]
				), array("workflow_template_id" => $arWorkflowTemplate["ID"], "back_url" => $backUrl));
				$url .= ((mb_strpos($url, "?") === false) ? "?" : "&").bitrix_sessid_get();
				$arBPStart[] = array(
					"TEXT" => $arWorkflowTemplate["NAME"],
					"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				);
			}
		}

		/* Fields BIZPROC and COMMENTS */
		$ii = 0;
		$html = "";
		$workflows = [];
		foreach ($documentStates as $kk => $vv)
		{
			if(!$vv["ID"])
				continue;

			if($processesWithComments && !empty($data["WORKFLOW_ID"]))
				$workflows[] = 'WF_'.$vv["ID"];

			$canViewWorkflow = BizprocDocument::canUserOperateDocument(
				CBPCanUserOperateOperation::ViewWorkflow,
				$USER->GetID(),
				$data["ID"],
				array(
					"IBlockPermission" => $arResult["IBLOCK_PERM"],
					"AllUserGroups" => $arUserGroupsForBPTmp,
					"DocumentStates" => $documentStates,
					"WorkflowId" => $kk,
				)
			);
			if (!$canViewWorkflow)
				continue;

			if($vv["TEMPLATE_NAME"] <> '')
				$html .= "<b>".htmlspecialcharsbx($vv["TEMPLATE_NAME"])."</b>:<br />";
			else
				$html .= "<b>".(++$ii)."</b>:<br />";

			$url = CHTTP::urlAddParams(str_replace(
				array("#list_id#", "#document_state_id#", "#group_id#"),
				array($arResult["IBLOCK_ID"], $vv["ID"], $arParams["SOCNET_GROUP_ID"]),
				$arParams["~BIZPROC_LOG_URL"]
			),
				array("back_url" => $backUrl),
				array("skip_empty" => true, "encode" => true)
			);

			$html .= "<a href=\"".htmlspecialcharsbx($url)."\">".($vv["STATE_TITLE"] <> '' ?
				htmlspecialcharsbx($vv["STATE_TITLE"]) : htmlspecialcharsbx($vv["STATE_NAME"]))."</a><br />";
		}

		if ($processesWithComments)
		{
			$workflows = array_unique($workflows);
			if ($workflows)
			{
				$iterator = CForumTopic::getList(array(), array("@XML_ID" => $workflows));
				while ($row = $iterator->fetch())
					$arResult["COMMENTS_COUNT"][$row["XML_ID"]] = $row["POSTS"];

				$columns["COMMENTS"] = '<div class="bp-comments"><a href="#" onclick="
					if(BX.Bizproc.showWorkflowInfoPopup) return BX.Bizproc.showWorkflowInfoPopup(\''.
					$data["WORKFLOW_ID"].'\')"><span class="bp-comments-icon"></span>'
					.(!empty($arResult["COMMENTS_COUNT"]['WF_'.$data["WORKFLOW_ID"]]) ?
						(int) $arResult["COMMENTS_COUNT"]['WF_'.$data["WORKFLOW_ID"]] : '0')
					.'</a></div>';
			}
		}
		else
		{
			$columns["COMMENTS"] = GetMessage("CC_BLL_COMMENTS_ACCESS_DENIED");
		}
		$columns["BIZPROC"] = $html;
		/* *** */
	}

	if (
		CLists::isEnabledLockFeature($IBLOCK_ID)
		&& in_array("LOCK_STATUS", $grid_columns) || empty($grid_columns)
	)
	{
		ob_start();
		$APPLICATION->IncludeComponent(
			"bitrix:lists.lock.status.widget",
			"",
			[
				"ELEMENT_ID" => $data["ID"],
				"ELEMENT_NAME" => $arResult["IBLOCK"]["ELEMENT_NAME"],
			],
			null,
			["HIDE_ICONS" => "Y"]
		);
		$columns["LOCK_STATUS"] = ob_get_clean();
	}

	$url = str_replace(
		array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
		array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]), $arParams["SOCNET_GROUP_ID"]),
		$arParams["LIST_ELEMENT_URL"]
	);
	$url = CHTTP::urlAddParams($url, ["list_section_id" => ($arResult["ANY_SECTION"] ? "" : $section_id)]);

	$aActions = array();
	if(!$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& ($lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_edit")))
	{
		$aActions[] = array(
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_EDIT"),
			"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
			"DEFAULT" => true,
		);
	}
	else
	{
		$aActions[] = array(
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_VIEW"),
			"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
			"DEFAULT" => true,
		);
	}
	if(!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm > CListPermissions::CAN_READ
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, intval($arResult["SECTION_ID"]), "section_element_bind")))
	{
		$urlCopy = CHTTP::urlAddParams(str_replace(
				["#list_id#", "#section_id#", "#element_id#", "#group_id#"],
				[$arIBlock["ID"], intval($arResult["SECTION_ID"]), 0, $arParams["SOCNET_GROUP_ID"]],
				$arParams["LIST_ELEMENT_URL"]
			),
			["copy_id" => $data["~ID"]],
			["skip_empty" => true, "encode" => true]
		);
		$urlCopy = CHTTP::urlAddParams($urlCopy, ["list_section_id" => ($arResult["ANY_SECTION"] ? "" : $section_id)]);
		$aActions[] = array(
			"TEXT"=>GetMessage("CC_BLL_ELEMENT_ACTION_MENU_COPY"),
			"HREF" => $urlCopy,
		);
	}

	if ($isBizprocActive)
	{
		if(count($arBPStart) && !$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_BIZPROC
				|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_bizproc_start")))
		{
			$aActions[] = array(
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_START_BP"),
				"MENU" => $arBPStart,
			);
		}
		if(!empty($documentStates))
		{
			$currentUserGroups = $arResult["USER_GROUPS"];
			if($data["CREATED_BY"] == $GLOBALS["USER"]->GetID())
				$currentUserGroups[] = "author";

			$listProcesses = array();
			foreach($documentStates as $documentState)
			{
				if(!$documentState["ID"])
					continue;

				$actionsProcess = array();
				$canViewWorkflow = CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::ViewWorkflow,
					$GLOBALS["USER"]->GetID(),
					$documentComplexId,
					array(
						"AllUserGroups" => $currentUserGroups,
						"DocumentStates" => $documentStates,
						"WorkflowId" => $documentState["ID"]
					)
				);
				if(!$canViewWorkflow)
					continue;

				/* Stop workflow */
				if (mb_strlen($documentState["ID"]) && mb_strlen($documentState["WORKFLOW_STATUS"]))
				{
					if (CBPDocument::CanUserOperateDocument(
						CBPCanUserOperateOperation::StartWorkflow,
						$GLOBALS["USER"]->GetID(),
						$documentComplexId,
						array("UserGroups" => $currentUserGroups))
					)
					{
						$actionsProcess[] = array(
							"TEXT" => GetMessage("CT_BLL_BIZPROC_STOP"),
							"ONCLICK" => "javascript:BX.Lists['".$arResult['JS_OBJECT']."']
							.performActionBp('".$documentState['ID']."', ".$data["ID"].", 'stop');",
						);
					}
				}
				/* Removal workflow */
				if (mb_strlen($documentState["STATE_NAME"]) && mb_strlen($documentState["ID"]))
				{
					if (CBPDocument::CanUserOperateDocument(
						CBPCanUserOperateOperation::CreateWorkflow,
						$GLOBALS["USER"]->GetID(),
						$documentComplexId,
						array("UserGroups" => $currentUserGroups))
					)
					{
						$actionsProcess[] = array(
							"TEXT" => GetMessage("CT_BLL_BIZPROC_DELETE"),
							"ONCLICK" => "javascript:BX.Lists['".$arResult['JS_OBJECT']."']
							.performActionBp('".$documentState['ID']."', ".$data["ID"].", 'delete');",
						);
					}
				}
				/* Tasks workflow */
				if($isBizprocVisible && $documentState["ID"] && $documentState['WORKFLOW_STATUS'])
				{
					$tasks = CBPDocument::getUserTasksForWorkflow($GLOBALS["USER"]->GetID(), $documentState["ID"]);
					if(!empty($tasks))
					{
						foreach($tasks as $task)
						{
							$url = CHTTP::urlAddParams(str_replace(
								array("#list_id#", "#section_id#", "#element_id#", "#task_id#", "#group_id#"),
								array($arIBlock["ID"], intval($arResult["SECTION_ID"]), $data["ID"],
									$task["ID"], $arParams["SOCNET_GROUP_ID"]),
								$arParams["~BIZPROC_TASK_URL"]
							),
								array("back_url" => $backUrl),
								array("skip_empty" => true, "encode" => true)
							);
							$actionsProcess[] = array(
								"TEXT" => $task["NAME"],
								"ONCLICK" => "jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
							);
						}
					}
				}

				if(!empty($actionsProcess))
				{
					$listProcesses[] = array(
						"TEXT" => htmlspecialcharsbx($documentState["TEMPLATE_NAME"])." (". $documentState["STARTED"].")",
						"MENU" => $actionsProcess,
					);
				}
			}
			if(!empty($listProcesses))
			{
				$aActions[] = array(
					"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_RUNNING_BP"),
					"MENU" => $listProcesses,
				);
			}
		}
	}

	if (CLists::isEnabledLockFeature($IBLOCK_ID))
	{
		$lockStatus = CIBlockElement::WF_GetLockStatus($data["ID"], $lockedBy, $dateLock);
		if (($arParams["CAN_EDIT"] && $lockStatus == "red") || $lockStatus == "yellow")
		{
			$aActions[] = [
				"ID" => "unLockElement",
				"TEXT" => GetMessage("CT_BLL_UN_LOCK_ELEMENT"),
				"ONCLICK" => "javascript:BX.Lists['".$arResult['JS_OBJECT']."'].unLock(".$data["ID"].");"
			];
		}
	}

	if(!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $data["~ID"], "element_delete")))
	{
		$aActions[] = array(
			"ID" => "delete",
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE"),
			"ONCLICK" => "bxGrid_".$arResult["GRID_ID"].".DeleteItem('".$data["ID"]."', '".
				GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE_CONF")."')",
		);
		$arResult["ELEMENTS_CAN_DELETE"][] = $data["ID"];
	}

	$arResult["ELEMENTS_ROWS"][] = array(
		"id" => $data["ID"],
		"data" => $data,
		"actions" => $aActions,
		"columns" => $columns,
	);
}

if (!$arResult["IS_SOCNET_GROUP_CLOSED"] && ($lists_perm >= CListPermissions::CAN_WRITE
	|| CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_delete")))
{
	/* Create sctructure group actions for grid  */
	$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
	$actionsPanel = array(
		"GROUPS" => array(
			array(
				"ITEMS" => array(
					$snippet->getRemoveButton(),
				)
			)
		)
	);
	$arResult["GRID_ACTION_PANEL"] = $actionsPanel;
}

/* Grid navigation */
$nav->setRecordCount($nav->getOffset() + $n);

$arResult["CURRENT_PAGE"] = $nav->getCurrentPage();
$arResult["GRID_ENABLE_NEXT_PAGE"] = $nav->getCurrentPage() < $nav->getPageCount();

$arResult["NAV_OBJECT"] = $nav;

$arResult["GRID_PAGE_SIZES"] = array(
	array("NAME" => "5", "VALUE" => "5"),
	array("NAME" => "10", "VALUE" => "10"),
	array("NAME" => "20", "VALUE" => "20"),
	array("NAME" => "50", "VALUE" => "50"),
	array("NAME" => "100", "VALUE" => "100")
);
/* *** */

$arResult["LIST_NEW_ELEMENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
	array($arIBlock["ID"], intval($arResult["SECTION_ID"]), 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["LIST_ELEMENT_URL"]
);
$arResult["LIST_NEW_ELEMENT_URL"] = CHTTP::urlAddParams(
	$arResult["LIST_NEW_ELEMENT_URL"], ["list_section_id" => ($arResult["ANY_SECTION"] ? "" : $section_id)]);

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => "")));

foreach($arResult["SECTION_PATH"] as $arPath)
{
	$APPLICATION->AddChainItem($arPath["NAME"], $arPath["URL"]);
}

$this->IncludeComponentTemplate();
?>
