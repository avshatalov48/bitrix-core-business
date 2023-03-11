<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

if (!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));

	return;
}

$arDefaultUrlTemplates404 = array(
	"lists" => "",
	"list" =>"#list_id#/view/#section_id#/",
	"list_edit" => "#list_id#/edit/",
	"list_fields" => "#list_id#/fields/",
	"list_field_edit" => "#list_id#/field/#field_id#/",
	"list_element_edit" => "#list_id#/element/#section_id#/#element_id#/",
	"list_file" => "#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/",
	"bizproc_log" => "#list_id#/bp_log/#document_state_id#/",
	"bizproc_workflow_start" => "#list_id#/bp_start/#element_id#/",
	"bizproc_task" => "#list_id#/bp_task/#section_id#/#element_id#/#task_id#/",
	"bizproc_workflow_admin" => "#list_id#/bp_list/",
	"bizproc_workflow_edit" => "#list_id#/bp_edit/#ID#/",
	"bizproc_workflow_vars" => "#list_id#/bp_vars/#ID#/",
	"bizproc_workflow_constants" => "#list_id#/bp_constants/#ID#/",
	"list_export_excel" => "#list_id#/excel/",
);

$processes = CLists::isListProcesses($arParams["IBLOCK_TYPE_ID"]);
$arDefaultUrlTemplates404["catalog_processes"] = "catalog_processes/";

$featureName = ($processes ? "lists_processes" : "lists");

if (CModule::IncludeModule("lists") && !CLists::isFeatureEnabled($featureName))
{
	ShowError(GetMessage("CC_BLL_ACCESS_DENIDED"));

	return;
}

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"list_id",
	"field_id",
	"section_id",
	"element_id",
	"file_id",
	"mode",
	"document_state_id",
	"task_id",
	"ID",
);

/* We set the option on the component, as well as accounting for multiple sites */
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	if($arParams["SEF_FOLDER"] != COption::GetOptionString('lists', 'livefeed_url'))
	{
		$sitDirTrim = trim(SITE_DIR, '/');
		if(!empty($sitDirTrim))
		{
			$setOptions = str_replace(SITE_DIR, '/', $arParams["SEF_FOLDER"]);
			COption::SetOptionString("lists", "livefeed_url", $setOptions);
		}
		else
		{
			COption::SetOptionString("lists", "livefeed_url", $arParams["SEF_FOLDER"]);
		}

	}
}

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"] ?? []);

	if(isset($_GET['livefeed']) && $_GET['livefeed'] == 'y')
	{
		$componentPage = 'list_element_edit';
		$arVariables = array('list_id' => $_GET['list_id'], 'element_id' => $_GET['element_id'], 'section_id' => 0);
	}
	elseif(isset($_GET['bp_constants']) && $_GET['bp_constants'] == 'y')
	{
		$componentPage = "bizproc_workflow_constants";
		$arVariables = array('list_id' => $_GET['list_id'], 'ID' => $_GET['id']);
	}
	elseif($processes && isset($_GET["bp_catalog"]) && $_GET["bp_catalog"] == "y")
	{
		$componentPage = "catalog_processes";
	}
	else
	{
		$componentPage = CComponentEngine::ParseComponentPath(
			$arParams["SEF_FOLDER"],
			$arUrlTemplates,
			$arVariables
		);
	}

	if(!$componentPage)
		$componentPage = "lists";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);

	// Registering routes for building preview by url
	Bitrix\Main\UrlPreview\Router::setRouteHandler(
		$arParams["SEF_FOLDER"].$arUrlTemplates['list_element_edit'],
		'lists',
		'\Bitrix\Lists\Preview\Element',
		array(
			'listId' => '$list_id',
			'sectionId' => '$section_id',
			'elementId' => '$element_id',
			'IBLOCK_TYPE_ID' => $arParams['IBLOCK_TYPE_ID']
		)
	);
}
else
{
	$arVariables = array();
	if(!isset($arParams["VARIABLE_ALIASES"]["ID"]))
		$arParams["VARIABLE_ALIASES"]["ID"] = "ID";

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	if(!isset($arVariableAliases["file_id"]))
		$arVariableAliases["file_id"] = "file_id";

	if($_GET['livefeed'] == 'y')
	{
		$componentPage = 'list_element_edit';
		$arVariables = array('list_id' => $_GET['list_id'], 'element_id' => $_GET['element_id'], 'section_id' => 0);
	}
	elseif($_GET['bp_constants'] == 'y')
	{
		$componentPage = "bizproc_workflow_constants";
		$arVariables = array('list_id' => $_GET['list_id'], 'ID' => $_GET['id']);
	}
	elseif($processes && $_GET["bp_catalog"] == "y")
	{
		$componentPage = "catalog_processes";
	}
	else
	{
		CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
		$componentPage = "lists"; //default page
	}

	if(isset($arVariables["list_id"]) && isset($arVariables["mode"]))
	{
		switch($arVariables["mode"])
		{
		case "field":
			if(isset($arVariables["field_id"]))
				$componentPage = "list_field_edit";
			break;
		case "fields":
			$componentPage = "list_fields";
			break;
		case "edit":
			if(isset($arVariables["element_id"]))
				$componentPage = "list_element_edit";
			elseif(isset($arVariables["section_id"]))
				$componentPage = "list_sections";
			else
				$componentPage = "list_edit";
			break;
		case "bp":
			if(isset($arVariables["document_state_id"]))
				$componentPage = "bizproc_log";
			elseif(isset($arVariables["task_id"]))
				$componentPage = "bizproc_task";
			elseif(isset($arVariables["element_id"]) && isset($_GET["action"]) && $_GET["action"] === "del_bizproc")
				$componentPage = "bizproc_workflow_delete";
			elseif(isset($arVariables["section_id"]) && isset($arVariables["element_id"]))
				$componentPage = "bizproc_workflow_start";
			elseif(isset($arVariables["ID"]) && !isset($_GET["action"]))
				$componentPage = "bizproc_workflow_edit";
			else
				$componentPage = "bizproc_workflow_admin";
			break;
		case "bp_vars":
			$componentPage = "bizproc_workflow_vars";
			break;
		case "bp_constants":
			$componentPage = "bizproc_workflow_constants";
			break;
		case "view":
			if(isset($arVariables["file_id"]))
				$componentPage = "list_file";
			else
				$componentPage = "list";
			break;
		case "excel":
			$componentPage = "list_export_excel";
			break;
		}
	}

	if($processes)
	{
		if(isset($arVariables["mode"]))
		{
			switch($arVariables["mode"])
			{
				case "catalog":
					if($processes)
						$componentPage = "catalog_processes";
					break;
			}
		}
	}

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"list_field_edit" => $APPLICATION->GetCurPage()
				."?mode=field"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["field_id"]."=#field_id#"
			,
			"list_fields" => $APPLICATION->GetCurPage()
				."?mode=fields"
				."&".$arVariableAliases["list_id"]."=#list_id#"
			,
			"list_edit" => $APPLICATION->GetCurPage()
				."?mode=edit"
				."&".$arVariableAliases["list_id"]."=#list_id#"
			,
			"list_element_edit" => $APPLICATION->GetCurPage()
				."?mode=edit"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
				."&".$arVariableAliases["element_id"]."=#element_id#"
			,
			"list_sections" => $APPLICATION->GetCurPage()
				."?mode=edit"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
			,
			"bizproc_log" => $APPLICATION->GetCurPage()
				."?mode=bp"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["document_state_id"]."=#document_state_id#"
			,
			"bizproc_task" => $APPLICATION->GetCurPage()
				."?mode=bp&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["task_id"]."=#task_id#"
			,
			"bizproc_workflow_start" => $APPLICATION->GetCurPage()
				."?mode=bp"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
				."&".$arVariableAliases["element_id"]."=#element_id#"
			,
			"bizproc_workflow_delete" => $APPLICATION->GetCurPage()
				."?mode=bp"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
				."&".$arVariableAliases["element_id"]."=#element_id#"
			,
			"bizproc_workflow_admin" => $APPLICATION->GetCurPage()
				."?mode=bp"
				."&".$arVariableAliases["list_id"]."=#list_id#"
			,
			"bizproc_workflow_edit" => $APPLICATION->GetCurPage()
				."?mode=bp"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["ID"]."=#ID#"
			,
			"bizproc_workflow_vars" => $APPLICATION->GetCurPage()
				."?mode=bp_vars"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["ID"]."=#ID#"
			,
			"bizproc_workflow_constants" => $APPLICATION->GetCurPage()
				."?mode=bp_constants"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["ID"]."=#ID#"
		,
			"list_file" => $APPLICATION->GetCurPage()
				."?mode=view"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
				."&".$arVariableAliases["element_id"]."=#element_id#"
				."&".$arVariableAliases["field_id"]."=#field_id#"
				."&".$arVariableAliases["file_id"]."=#file_id#"
			,
			"list" => $APPLICATION->GetCurPage()
				."?mode=view"
				."&".$arVariableAliases["list_id"]."=#list_id#"
				."&".$arVariableAliases["section_id"]."=#section_id#"
			,
			"lists" => $APPLICATION->GetCurPage(),
			"list_export_excel" => $APPLICATION->GetCurPage()."?mode=excel"
				."&".$arVariableAliases["list_id"]."=#list_id#",
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
	if($processes)
		$arResult["URL_TEMPLATES"]["catalog_processes"] = $APPLICATION->GetCurPage()."?mode=catalog";
}

$arResult['URL_TEMPLATES']['bizproc_workflow_delete'] = $arResult['URL_TEMPLATES']['bizproc_workflow_delete'] ?? '';

$p = mb_strpos($arResult["URL_TEMPLATES"]["bizproc_workflow_delete"], "?");
if($p === false)
	$ch = "?";
else
	$ch = "&";
$arResult["URL_TEMPLATES"]["bizproc_workflow_delete"] .= $ch."action=del_bizproc";

if(
	isset($arVariables["document_state_id"])
	&& !isset($arVariables["element_id"])
	&& CModule::IncludeModule("bizproc")
	&& CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"])
)
{
	$arWorkflowState = CBPStateService::GetWorkflowState($arVariables["document_state_id"]);
	if(is_array($arWorkflowState) && is_array($arWorkflowState["DOCUMENT_ID"]))
		list(, , $arResult["VARIABLES"]["element_id"]) = CBPHelper::ParseDocumentId($arWorkflowState["DOCUMENT_ID"]);
}

$this->IncludeComponentTemplate($componentPage);
