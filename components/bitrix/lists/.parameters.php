<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("lists"))
	return;

$strSelectedType = $arCurrentValues["IBLOCK_TYPE_ID"];

$arTypes = array();
$rsTypes = CLists::GetIBlockTypes();
while($ar = $rsTypes->Fetch())
{
	$arTypes[$ar["IBLOCK_TYPE_ID"]] = "[".$ar["IBLOCK_TYPE_ID"]."] ".$ar["NAME"];
	if(!$strSelectedType)
		$strSelectedType = $ar["IBLOCK_TYPE_ID"];
}

$arIBlocks = array();
$rsIBlocks = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $strSelectedType, "ACTIVE"=>"Y"));
while($ar = $rsIBlocks->Fetch())
{
	$arIBlocks[$ar["ID"]] = "[".$ar["ID"]."] ".$ar["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
			"list_id" => Array("NAME" => GetMessage("CP_BL_LIST_ID")),
			"field_id" => Array("NAME" => GetMessage("CP_BL_FIELD_ID")),
			"section_id" => Array("NAME" => GetMessage("CP_BL_SECTION_ID")),
			"element_id" => Array("NAME" => GetMessage("CP_BL_ELEMENT_ID")),
			"file_id" => Array("NAME" => GetMessage("CP_BL_FILE_ID")),
			"mode" => Array("NAME" => GetMessage("CP_BL_MODE")),
			"document_state_id" => Array("NAME" => GetMessage("CP_BL_DOCUMENT_STATE_ID")),
			"task_id" => Array("NAME" => GetMessage("CP_BL_TASK_ID")),
			"ID" => Array("NAME" => GetMessage("CP_BL_BP_ID")),
		),
		"SEF_MODE" => Array(
			"lists" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LISTS"),
				"DEFAULT" => "",
				"VARIABLES" => array(),
			),
			"list" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST"),
				"DEFAULT" => "#list_id#/view/#section_id#/",
				"VARIABLES" => array("list_id", "section_id"),
			),
			"list_sections" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_SECTIONS"),
				"DEFAULT" => "#list_id#/edit/#section_id#/",
				"VARIABLES" => array("list_id", "section_id"),
			),
			"list_edit" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_EDIT"),
				"DEFAULT" => "#list_id#/edit/",
				"VARIABLES" => array("list_id"),
			),
			"list_fields" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_FIELDS"),
				"DEFAULT" => "#list_id#/fields/",
				"VARIABLES" => array("list_id"),
			),
			"list_field_edit" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_FIELD_EDIT"),
				"DEFAULT" => "#list_id#/field/#field_id#/",
				"VARIABLES" => array("list_id", "field_id"),
			),
			"list_element_edit" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_ELEMENT_EDIT"),
				"DEFAULT" => "#list_id#/element/#section_id#/#element_id#/",
				"VARIABLES" => array("list_id", "section_id", "element_id"),
			),
			"list_file" => array(
				"NAME" => GetMessage("CP_BL_PAGE_LIST_ELEMENT_EDIT"),
				"DEFAULT" => "#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/",
				"VARIABLES" => array("list_id", "section_id", "element_id", "field_id", "file_id"),
			),
			"bizproc_log" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_LOG"),
				"DEFAULT" => "#list_id#/bp_log/#document_state_id#/",
				"VARIABLES" => array("list_id", "document_state_id"),
			),
			"bizproc_workflow_start" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_WORKFLOW_START"),
				"DEFAULT" => "#list_id#/bp_start/#element_id#/",
				"VARIABLES" => array("list_id", "element_id"),
			),
			"bizproc_task" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_TASK"),
				"DEFAULT" => "#list_id#/bp_task/#section_id#/#element_id#/#task_id#/",
				"VARIABLES" => array("list_id", "section_id", "element_id", "task_id"),
			),
			"bizproc_workflow_admin" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_WORKFLOW_ADMIN"),
				"DEFAULT" => "#list_id#/bp_list/",
				"VARIABLES" => array("list_id"),
			),
			"bizproc_workflow_edit" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_WORKFLOW_EDIT"),
				"DEFAULT" => "#list_id#/bp_edit/#ID#/",
				"VARIABLES" => array("list_id", "ID"),
			),
			"bizproc_workflow_vars" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_WORKFLOW_VARS"),
				"DEFAULT" => "#list_id#/bp_vars/#ID#/",
				"VARIABLES" => array("list_id", "ID"),
			),
			"bizproc_workflow_constants" => array(
				"NAME" => GetMessage("CP_BL_PAGE_BIZPROC_WORKFLOW_CONSTANTS"),
				"DEFAULT" => "#list_id#/bp_constants/#ID#/",
				"VARIABLES" => array("list_id", "ID"),
			),
			"list_export_excel" => array(
				"NAME" => GetMessage("CP_BL_PAGE_EXPORT_EXCEL"),
				"DEFAULT" => "#list_id#/excel/",
				"VARIABLES" => array("list_id"),
			),
			"catalog_processes" => array(
				"NAME" => GetMessage("CP_BL_PAGE_CATALOG_PROCESSES"),
				"DEFAULT" => "catalog_processes/",
				"VARIABLES" => array(),
			),
		),
		"IBLOCK_TYPE_ID" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BL_IBLOCK_TYPE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "lists",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);
?>
