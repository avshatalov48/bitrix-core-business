<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

$arIBlockType = array();
$db = CIBlockType::GetList(array("sort" => "asc"), array("ACTIVE" => "Y"));
while ($ar = $db->Fetch())
{
	if ($ar1 = CIBlockType::GetByIDLang($ar["ID"], LANGUAGE_ID))
		$arIBlockType[$ar["ID"]] = "[".$ar["ID"]."] ".$ar1["NAME"];
}

$arGroups = array();
$db = CGroup::GetList("c_sort", "asc", array("ACTIVE" => "Y"));
while ($ar = $db->Fetch())
	$arGroups[$ar["ID"]] = $ar["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
		"ACCESS" => array(
			"NAME" => GetMessage("BPWC_WP_PERMS"),
			"SORT" => "400",
		),
	),
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => array(
		),
		"SEF_MODE" => array(
			"index" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			"new" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_NEW"),
				"DEFAULT" => "new.php",
				"VARIABLES" => array(),
			),
			"list" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_LIST"),
				"DEFAULT" => "#block_id#/",
				"VARIABLES" => array(),
			),
			"view" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_VIEW"),
				"DEFAULT" => "#block_id#/view-#bp_id#.php",
				"VARIABLES" => array(),
			),
			"start" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_START"),
				"DEFAULT" => "#block_id#/start.php",
				"VARIABLES" => array(),
			),
			"edit" => array(
				"NAME" => GetMessage("BPWC_WP_SEF_EDIT"),
				"DEFAULT" => "#block_id#/edit.php",
				"VARIABLES" => array(),
			),
			"task" => array(
				"NAME" => GetMessage("BPWC_WP_TASK"),
				"DEFAULT" => "#block_id#/task-#task_id#.php",
				"VARIABLES" => array(),
			),
			"bp" => array(
				"NAME" => GetMessage("BPWC_WP_BP"),
				"DEFAULT" => "#block_id#/bp.php",
				"VARIABLES" => array(),
			),
			"setvar" => array(
				"NAME" => GetMessage("BPWC_WP_SETVAR"),
				"DEFAULT" => "#block_id#/setvar.php",
				"VARIABLES" => array(),
			),
			"log" => array(
				"NAME" => GetMessage("BPWC_WP_LOG"),
				"DEFAULT" => "#block_id#/log-#bp_id#.php",
				"VARIABLES" => array(),
			),
		),
		"SET_TITLE" => array(),
		"SET_NAV_CHAIN" => array(
		  	"NAME" => GetMessage("BPWC_WP_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SKIP_BLOCK" => array(
		  	"NAME" => GetMessage("BPWC_WP_SKIP_BLOCK"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BPWC_WP_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"ADMIN_ACCESS" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("BPWC_WP_ADMIN_ACCESS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arGroups,
			"REFRESH" => "N",
		),
		"AJAX_MODE" => array()
	),
);
?>