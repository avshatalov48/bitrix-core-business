<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;

if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["TASK_VAR"] == '')
	$arParams["TASK_VAR"] = "task_id";
if ($arParams["BLOCK_VAR"] == '')
	$arParams["BLOCK_VAR"] = "block_id";
if ($arParams["BP_VAR"] == '')
	$arParams["BP_VAR"] = "bp_id";

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if ($arParams["PATH_TO_INDEX"] == '')
	$arParams["PATH_TO_INDEX"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index";

$arParams["PATH_TO_START"] = trim($arParams["PATH_TO_START"]);
if ($arParams["PATH_TO_START"] == '')
	$arParams["PATH_TO_START"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=start&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_START"] = $arParams["PATH_TO_START"].((mb_strpos($arParams["PATH_TO_START"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if ($arParams["PATH_TO_TASK"] == '')
	$arParams["PATH_TO_TASK"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["TASK_VAR"]."=#task_id#";
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((mb_strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"]);
if ($arParams["PATH_TO_LOG"] == '')
	$arParams["PATH_TO_LOG"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["BP_VAR"]."=#bp_id#";

$arParams["PATH_TO_VIEW"] = trim($arParams["PATH_TO_VIEW"]);
if ($arParams["PATH_TO_VIEW"] == '')
	$arParams["PATH_TO_VIEW"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=view&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["BP_VAR"]."=#bp_id#";

$arParams["PATH_TO_BP"] = trim($arParams["PATH_TO_BP"]);
if ($arParams["PATH_TO_BP"] == '')
	$arParams["PATH_TO_BP"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bp&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_BP"] = $arParams["PATH_TO_BP"].((mb_strpos($arParams["PATH_TO_BP"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_SETVAR"] = trim($arParams["PATH_TO_SETVAR"]);
if ($arParams["PATH_TO_SETVAR"] == '')
	$arParams["PATH_TO_SETVAR"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=setvar&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_SETVAR"] = $arParams["PATH_TO_SETVAR"].((mb_strpos($arParams["PATH_TO_SETVAR"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if ($arParams["IBLOCK_TYPE"] == '')
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_EMPTY_IBLOCK_TYPE").". ";

$arParams["BLOCK_ID"] = intval($arParams["BLOCK_ID"]);
if ($arParams["BLOCK_ID"] <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_EMPTY_IBLOCK").". ";

$arResult["BackUrl"] = urlencode(empty($_REQUEST["back_url"]) ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

$arResult["PATH_TO_INDEX"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
$arResult["PATH_TO_START"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_START"], array("block_id" => $arParams["BLOCK_ID"]));
$arResult["PATH_TO_BP"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BP"], array("block_id" => $arParams["BLOCK_ID"]));
$arResult["PATH_TO_SETVAR"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SETVAR"], array("block_id" => $arParams["BLOCK_ID"]));

$documentType = array("bizproc", "CBPVirtualDocument", "type_".$arParams["BLOCK_ID"]);

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_WRONG_IBLOCK_TYPE").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["Block"] = null;
	$db = CIBlock::GetList(array(), array("ID" => $arParams["BLOCK_ID"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
	if ($ar = $db->GetNext())
	{
		$arResult["Block"] = $ar;
		$arResult["Block"]["FILTERABLE_FIELDS"] = array();
		$arResult["Block"]["VISIBLE_FIELDS"] = array();

		if ($ar["~DESCRIPTION"] <> '' && mb_substr($ar["~DESCRIPTION"], 0, mb_strlen("v2:")) == "v2:")
		{
			$v1 = @unserialize(mb_substr($ar["~DESCRIPTION"], 3), ['allowed_classes' => false]);
			if (is_array($v1))
			{
				$arResult["Block"]["DESCRIPTION"] = htmlspecialcharsbx($v1["DESCRIPTION"]);
				$arResult["Block"]["FILTERABLE_FIELDS"] = $v1["FILTERABLE_FIELDS"];
				$arResult["Block"]["VISIBLE_FIELDS"] = $v1["VISIBLE_FIELDS"];
			}
		}
	}
	else
	{
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_WRONG_IBLOCK").". ";
	}
}

/*
function print_rrr($var)
{
	if(is_array($var))
	{
		if($var == array_values($var))
		{
			foreach($var as $key => $value)
			{
				$var[$key] = print_rrr($value);
			}
			return "Array(".implode(", ", $var).")";
		}

		$res = "\nArray(\n";
		$first = true;
		foreach($var as $key => $value)
		{
			if($first)
				$first = false;
			else
				$res .= ",\n";
			$res .= "'".AddSlashes($key)."' => ".print_rrr($value);
		}
		$res .= "\n)";

		return $res;
	}
	elseif(is_bool($var))
	{
		if($var === true)
			return 'true';
		else
			return 'false';
	}
	else
		return "'".str_replace("'", "\'", $var)."'";

}

$db = CBPWorkflowTemplateLoader::GetList(array(), array("DOCUMENT_TYPE" => $documentType));
if ($ar = $db->Fetch())
{
	$hFileTmp = fopen($_SERVER["DOCUMENT_ROOT"]."/+++++++2.+++", "a");  // DUMPING
	fwrite($hFileTmp, print_rrr($ar));
	fclose($hFileTmp);
}
*/

if ($arResult["FatalErrorMessage"] == '')
{
	$workflowTemplateId = 0;
	$db = CBPWorkflowTemplateLoader::GetList(array(), array("DOCUMENT_TYPE" => $documentType), false, false, array("ID"));
	if ($ar = $db->Fetch())
		$workflowTemplateId = intval($ar["ID"]);

	if ($workflowTemplateId <= 0)
	{
		$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BP"], array("block_id" => $arParams["BLOCK_ID"]));
		if ($_REQUEST["template_type"] == "statemachine")
			$redirectPath .= ((mb_strpos($redirectPath, "?") !== false) ? "&" : "?")."init=statemachine";

		LocalRedirect($redirectPath);
	}
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arWorkflowTemplate = CBPWorkflowTemplateLoader::GetTemplateState($workflowTemplateId);

	if (!is_array($arWorkflowTemplate["STATE_PERMISSIONS"]) || count($arWorkflowTemplate["STATE_PERMISSIONS"]) <= 0)
		$arWorkflowTemplate["STATE_PERMISSIONS"]["create"] = array("author");

	$arResult["AllowableOperations"] = CBPDocument::GetAllowableOperations($GLOBALS["USER"]->GetID(), $GLOBALS["USER"]->GetUserGroupArray(), array($arWorkflowTemplate));

	$arResult["AllowAdmin"] = ($GLOBALS["USER"]->IsAdmin() || (is_array($arParams["ADMIN_ACCESS"]) && count(array_intersect($arParams["ADMIN_ACCESS"], $GLOBALS["USER"]->GetUserGroupArray())) > 0));
	$arResult["AllowCreate"] = ($arResult["AllowAdmin"] || (is_array($arResult["AllowableOperations"]) && in_array("create", $arResult["AllowableOperations"]) || is_array($arWorkflowTemplate["STATE_PERMISSIONS"]["create"]) && in_array("author", $arWorkflowTemplate["STATE_PERMISSIONS"]["create"])));

	$arMessagesTmp = CIBlock::GetMessages($arResult["Block"]["ID"]);
	$arResult["CreateTitle"] = htmlspecialcharsbx(is_array($arMessagesTmp) && array_key_exists("ELEMENT_ADD", $arMessagesTmp) ? $arMessagesTmp["ELEMENT_ADD"] : "");
}

if ($arResult["FatalErrorMessage"] == '')
{
	if ($_SERVER["REQUEST_METHOD"] == "GET" && $_REQUEST["process_state_event"] <> '' && check_bitrix_sessid())
	{
		$bizprocId = trim($_REQUEST["bizproc_id"]);
		$bizprocEvent = trim($_REQUEST["bizproc_event"]);

		if ($bizprocEvent <> '')
		{
			$arState = CBPStateService::GetWorkflowState($bizprocId);
			if (count($arState) > 0)
			{
				list($dbRecordsList, $dbRecordsList1) = CBPVirtualDocument::GetList(
					array(),
					array("IBLOCK_ID" => $arParams["BLOCK_ID"], "ID" => $arState["DOCUMENT_ID"][2]),
					false,
					false,
					array("ID", "IBLOCK_ID", "CREATED_BY")
				);
				if ($arRecord = $dbRecordsList->Fetch())
				{
					$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
					if ("user_".$GLOBALS["USER"]->GetID() == $arRecord["CREATED_BY"])
						$arCurrentUserGroups[] = "author";

					$arErrorTmp = array();

					CBPDocument::SendExternalEvent(
						$bizprocId,
						$bizprocEvent,
						array("Groups" => $arCurrentUserGroups, "User" => $GLOBALS["USER"]->GetID()),
						$arErrorTmp
					);

					if (count($arErrorsTmp) > 0)
					{
						foreach ($arErrorsTmp as $e)
							$arResult["ErrorMessage"] .= $e["message"].". ";
					}
				}
				else
				{
					$arResult["ErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_DOCUMENT").". ";
				}
			}
			else
			{
				$arResult["ErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_BP").". ";
			}

			if ($arResult["ErrorMessage"] == '')
				LocalRedirect($APPLICATION->GetCurPageParam("", array("sessid", "stop_bizproc_id", "process_state_event", "bizproc_event", "bizproc_id", "delete_bizproc_id")));
		}
	}

	if ($_SERVER["REQUEST_METHOD"] == "GET" && $_REQUEST["stop_bizproc_id"] <> '' && check_bitrix_sessid() && $arResult["AllowAdmin"])
	{
		$arState = CBPStateService::GetWorkflowState($_REQUEST["stop_bizproc_id"]);
		if (count($arState) > 0)
		{
			CBPDocument::TerminateWorkflow(
				$_REQUEST["stop_bizproc_id"],
				$arState["DOCUMENT_ID"],
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				foreach ($arErrorsTmp as $e)
					$arResult["ErrorMessage"] .= $e["message"].". ";
			}
		}
		else
		{
			$arResult["ErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_BP").". ";
		}

		if ($arResult["ErrorMessage"] == '')
			LocalRedirect($APPLICATION->GetCurPageParam("", array("sessid", "stop_bizproc_id", "delete_bizproc_id")));
	}

	if ($_SERVER["REQUEST_METHOD"] == "GET" && $_REQUEST["delete_bizproc_id"] <> '' && check_bitrix_sessid() && $arResult["AllowAdmin"])
	{
		$arState = CBPStateService::GetWorkflowState($_REQUEST["delete_bizproc_id"]);
		if (count($arState) > 0)
		{
			$arErrorsTmp = array();
			CBPDocument::OnDocumentDelete($arState["DOCUMENT_ID"], $arErrorsTmp);
			if (count($arErrorsTmp) > 0)
			{
				foreach ($arErrorsTmp as $e)
					$arResult["ErrorMessage"] .= $e["message"].". ";
			}

			if ($arResult["ErrorMessage"] == '')
				CIBlockElement::Delete($arState["DOCUMENT_ID"][2]);
		}
		else
		{
			$arResult["ErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_BP").". ";
		}

		if ($arResult["ErrorMessage"] == '')
			LocalRedirect($APPLICATION->GetCurPageParam("", array("sessid", "stop_bizproc_id", "delete_bizproc_id")));
	}
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arTypeMap = array(
		"string" => "string",
		"text" => "text",
		"double" => "number",
		"select" => "list",
		"bool" => "checkbox",
		"datetime" => "date",
	);

	// $timeZoneOffset for datetime fields formatting
	$timeZoneOffset = CTimeZone::GetOffset();

	$arSelectFields = array("ID", "CREATED_BY");

	$arResult["GRID_ID"] = "bizproc_CBPVirtualDocument_".$arParams["BLOCK_ID"];

	$gridOptions = new CGridOptions($arResult["GRID_ID"]);
	$gridColumns = $gridOptions->GetVisibleColumns();
	$gridSort = $gridOptions->GetSorting(array("sort"=>array("ID" => "desc")));

	$arResult["HEADERS"] = array();
	$arResult["FILTER"] = array();

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");

	$arDocumentFields = $documentService->GetDocumentFields($documentType);
	foreach ($arDocumentFields as $key => $value)
	{
		if (count($arResult["Block"]["VISIBLE_FIELDS"]) <= 0 || in_array($key, $arResult["Block"]["VISIBLE_FIELDS"]))
		{
			$arResult["HEADERS"][] = array(
				"id" => $key,
				"name" => $value["Name"],
				"default" => in_array($key, array("CREATED_BY_PRINTABLE", "NAME")) ? true : false,
				"sort" => $value["Filterable"] ? $key : ""
			);
		}

		if ((count($gridColumns) <= 0 || in_array($key, $gridColumns)) && !in_array($key, $arSelectFields) && mb_strpos($key, 'PROPERTY_') === false)
			$arSelectFields[] = $key;

		if ($value["Filterable"] && (count($arResult["Block"]["FILTERABLE_FIELDS"]) <= 0 || in_array($key, $arResult["Block"]["FILTERABLE_FIELDS"])))
		{
			$ind = count($arResult["FILTER"]);
			$arResult["FILTER"][$ind] = array(
				"id" => $key,
				"name" => $value["Name"],
				"type" => array_key_exists($value["BaseType"], $arTypeMap) ? $arTypeMap[$value["BaseType"]] : "string",
//				"value" => $sections,
//				"filtered" => $arResult["SECTION_ID"] !== false,
			);
			if (array_key_exists("Options", $value) && is_array($value["Options"]))
			{
				$arResult["FILTER"][$ind]["items"] = "list";
				$arResult["FILTER"][$ind]["items"] = array_merge(array("" => GetMessage("BPWC_WLC_NOT_SET")), $value["Options"]);
			}
			if ($value["BaseType"] == "user")
			{
				$arResult["FILTER"][$ind]["type"] = "custom";
				$arResult["FILTER"][$ind]["enable_settings"] = false;
				$arResult["FILTER"][$ind]["value"] = $documentService->GetFieldInputControl(
					$documentType,
					$value,
					array("Form" => "filter_".$arResult["GRID_ID"], "Field" => $key),
					$_REQUEST[$key],
					false,
					true
				);
			}
		}
	}

	$arResult["HEADERS"][] = array("id"=>"STATE", "name"=>GetMessage("BPWC_WLCT_F_STATE"), "default"=>true);
	$arResult["HEADERS"][] = array("id"=>"TASKS", "name"=>GetMessage("BPWC_WLCT_F_TASKS"), "default"=>false);

	$arFilter = array("IBLOCK_ID" => $arParams["BLOCK_ID"], "CHECK_BP_VIRTUAL_PERMISSIONS" => "read");
	$gridFilter = $gridOptions->GetFilter($arResult["FILTER"]);
	foreach ($gridFilter as $key => $value)
	{
		if (mb_substr($key, -5) == "_from")
		{
			$op = ">=";
			$newKey = mb_substr($key, 0, -5);
		}
		elseif (mb_substr($key, -3) == "_to")
		{
			$op = "<=";
			$newKey = mb_substr($key, 0, -3);
		}
		else
		{
			$op = "";
			$newKey = $key;
		}

		if (array_key_exists($newKey, $arDocumentFields) && $arDocumentFields[$newKey]["Filterable"])
		{
			if ($arDocumentFields[$newKey]["BaseType"] == "select")
			{
				$db = CIBlockProperty::GetPropertyEnum(mb_substr($newKey, mb_strlen("PROPERTY_")), array(), array("XML_ID" => $value, "IBLOCK_ID" => $arParams["BLOCK_ID"]));
				while ($ar = $db->Fetch())
					$value = $ar["ID"];
			}
			elseif ($arDocumentFields[$newKey]["BaseType"] == "string" || $arDocumentFields[$newKey]["BaseType"] == "text")
			{
				if ($op == "")
					$op = "?";
			}
			elseif ($arDocumentFields[$newKey]["BaseType"] == "user")
			{
				$value = CBPHelper::UsersStringToArray($value, $documentType, $arErrors);
				if (is_array($value) && count($value) > 0)
					$value = $value[0];
				if (mb_substr($value, 0, mb_strlen("user_")) == "user_")
					$value = mb_substr($value, mb_strlen("user_"));
			}
			elseif ($arDocumentFields[$newKey]["BaseType"] == "datetime" && $value <> '' && CheckDateTime($value))
			{
				$isShort = mb_strlen(trim($value)) <= 10;
				$appendTime = $op == '<=' ? '23:59:59' : '00:00:00';
				if (mb_strpos($newKey, 'PROPERTY_') === 0)
				{
					if ($timeZoneOffset != 0)
					{
						$value = date("Y-m-d ".($isShort? $appendTime : 'H:i:s'), MakeTimeStamp($value, CLang::GetDateFormat("FULL")) - $timeZoneOffset);
					}
					else
					{
						$value = CDatabase::FormatDate($value, CLang::GetDateFormat("FULL"), "YYYY-MM-DD ".($isShort? $appendTime : 'HH:MI:SS'));
					}
				}
				elseif ($isShort)
				{
					$value .= ' '.$appendTime;
				}
			}

			if ($newKey == "ACTIVE_FROM")
				$newKey = "DATE_ACTIVE_FROM";
			if ($newKey == "ACTIVE_TO")
				$newKey = "DATE_ACTIVE_TO";

			$arFilter[$op.$newKey] = $value;
		}
	}

	$arResult["SORT"] = $gridSort["sort"];

	$arResult["ShowStateEvents"] = false;
	$arResult["ShowTasks"] = false;
	$arResult["RECORDS"] = array();

	list($dbRecordsList, $dbRecordsList1) = CBPVirtualDocument::GetList(
		$gridSort["sort"],
		$arFilter,
		false,
		$gridOptions->GetNavParams(),
		$arSelectFields
	);
	while ($arRecord = $dbRecordsList->GetNext())
	{
		$arKeys = array_keys($arRecord);
		foreach ($arKeys as $key)
		{
			if ($arDocumentFields[$key]["BaseType"] == "file")
			{
				$ar = $arRecord[$key];
				if (!is_array($ar))
					$ar = array($ar);
				$arRecord[$key] = "";
				foreach ($ar as $v)
				{
					if ($arRecord[$key] <> '')
						$arRecord[$key] .= " ";
					$arRecord[$key] .= CFile::ShowFile($v, 100000, 50, 50, true);
				}
			}
			elseif (mb_strpos($key, '_PRINTABLE') !== false && $arDocumentFields[str_replace('_PRINTABLE', '', $key)]["BaseType"] == "user" && is_string($arRecord[$key]))
			{
				//compatibility: do not need to escape chars there, delegate this to main.integface.grid
				$arRecord[$key] = htmlspecialcharsback($arRecord[$key]);
			}
			if (is_array($arRecord[$key]))
			{
				$ar = $arRecord[$key];
				$arRecord[$key] = "";
				foreach ($ar as $val)
				{
					if ($arRecord[$key] <> '')
						$arRecord[$key] .= ", ";
					$arRecord[$key] .= $val;
				}
			}
		}

		$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
		if ("user_".$GLOBALS["USER"]->GetID() == $arRecord["CREATED_BY"])
			$arCurrentUserGroups[] = "author";

		$documentId = array("bizproc", "CBPVirtualDocument", $arRecord["ID"]);
		$arDocumentStates = CBPDocument::GetDocumentStates($documentType, $documentId);

		foreach ($arDocumentStates as $arDocumentState)
		{
			if ($arDocumentState['WORKFLOW_STATUS'] == -1 && !empty($arRecord["DOCUMENT_STATE"]['ID']))
				continue;

			$arRecord["DOCUMENT_STATE"] = $arDocumentState;
			$arRecord["DOCUMENT_STATE_EVENTS"] = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arCurrentUserGroups, $arDocumentState);
			if (count($arRecord["DOCUMENT_STATE_EVENTS"]) > 0)
				$arResult["ShowStateEvents"] = true;

			$arRecord["DOCUMENT_STATE_TASKS"] = array();
			$ar = CBPDocument::GetUserTasksForWorkflow($GLOBALS["USER"]->GetID(), $arDocumentState["ID"]);
			foreach ($ar as $ar1)
			{
				$ar1["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASK"], array("task_id" => $ar1["ID"], "block_id" => $arParams["BLOCK_ID"]));
				$arRecord["DOCUMENT_STATE_TASKS"][] = $ar1;
			}
			if (count($arRecord["DOCUMENT_STATE_TASKS"]) > 0)
				$arResult["ShowTasks"] = true;

			$arRecord["CancelUrl"] = "";
			//$arOperations = CBPDocument::GetAllowableOperations($GLOBALS["USER"]->GetID(), $arCurrentUserGroups, $arDocumentState);
			if ($arResult["AllowAdmin"] && $arDocumentState["ID"] <> '' && $arDocumentState["WORKFLOW_STATUS"] <> '')
				$arRecord["CancelUrl"] = $APPLICATION->GetCurPageParam("stop_bizproc_id=".$arDocumentState["ID"]."&".bitrix_sessid_get(), array("sessid", "stop_bizproc_id"));
		}

		$aCols = array(
			"STATE" => "<a href=\"".CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array("bp_id" => $arRecord["ID"], "block_id" => $arParams["BLOCK_ID"]))."\" onclick=''>".($arRecord["DOCUMENT_STATE"]["STATE_TITLE"] <> '' ? $arRecord["DOCUMENT_STATE"]["STATE_TITLE"] : $arRecord["DOCUMENT_STATE"]["STATE_NAME"])."</a>",
		);

		$aActions = array(
			array("ICONCLASS"=>"edit", "DEFAULT" => true, "TEXT"=>GetMessage("BPWC_WLC_NOT_DETAIL"), "ONCLICK"=>"window.location='".CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_VIEW"], array("bp_id" => $arRecord["ID"], "block_id" => $arParams["BLOCK_ID"]))."';")
		);
		if (isset($arRecord["DOCUMENT_STATE_EVENTS"]) && count($arRecord["DOCUMENT_STATE_EVENTS"]) > 0)
		{
			foreach ($arRecord["DOCUMENT_STATE_EVENTS"] as $e)
				$aActions[] = array("ICONCLASS"=>"", "TEXT"=>htmlspecialcharsbx($e["TITLE"]), "ONCLICK"=>"window.location='".$APPLICATION->GetCurPageParam("bizproc_id=".$arRecord["DOCUMENT_STATE"]["ID"]."&process_state_event=Y&bizproc_event=".htmlspecialcharsbx($e["NAME"])."&".bitrix_sessid_get(), array("sessid", "stop_bizproc_id", "process_state_event", "bizproc_event", "bizproc_id"))."';");
		}
		if ($arResult["ShowTasks"])
		{
			$aCols["TASKS"] = '';
			if (count($arRecord["DOCUMENT_STATE_TASKS"]) > 0)
			{
				foreach ($arRecord["DOCUMENT_STATE_TASKS"] as $arTask)
					$aCols["TASKS"]  = '<a href="'.$arTask["URL"].'" onclick="" title="'.strip_tags($arTask["DESCRIPTION"]).'">'.$arTask["NAME"].'</a><br />';
			}
		}

		if ($arResult["AllowAdmin"] && $arRecord["CancelUrl"] <> '')
		{
			if (count($aActions) > 0)
				$aActions[] = array("SEPARATOR"=>true);

			$aActions[] = array("ICONCLASS"=>"delete", "TEXT"=>GetMessage("JHGFDC_STOP"), "ONCLICK"=>"if(confirm('".GetMessageJS("JHGFDC_STOP_ALT")."')) window.location='".$arRecord["CancelUrl"]."';");
		}
		if ($arResult["AllowAdmin"])
		{
			if (count($aActions) > 0 && $arRecord["CancelUrl"] == '')
				$aActions[] = array("SEPARATOR"=>true);

			$aActions[] = array("ICONCLASS"=>"delete", "TEXT"=>GetMessage("JHGFDC_STOP_DELETE"), "ONCLICK"=>"if(confirm('".GetMessageJS("JHGFDC_STOP_DELETE_ALT")."')) window.location='".$APPLICATION->GetCurPageParam("delete_bizproc_id=".$arRecord["DOCUMENT_STATE"]["ID"]."&".bitrix_sessid_get(), array("sessid", "stop_bizproc_id", "delete_bizproc_id", 'bxajaxid'))."';");
		}

		$arResult["RECORDS"][] = array("data" => $arRecord, "actions" => $aActions, "columns" => $aCols, "editable" => false);
	}

	foreach ($arResult["HEADERS"] as $key => $value)
	{
		if ($value["id"] == "TASKS")
			$arResult["HEADERS"][$key]["default"] = $arResult["ShowTasks"];
	}

	$arResult["ROWS_COUNT"] = $dbRecordsList1->SelectedRowsCount();
	$arResult["NAV_STRING"] = $dbRecordsList1->GetPageNavStringEx($navComponentObject, GetMessage("INTS_TASKS_NAV"), "", false);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["NAV_RESULT"] = $dbRecordsList1;
}

if ($arResult["FatalErrorMessage"] == '')
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["Block"]["NAME"]);
	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arResult["PATH_TO_INDEX"]);
		$APPLICATION->AddChainItem($arResult["Block"]["NAME"]);
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WLC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WLC_ERROR"));
}

$this->IncludeComponentTemplate();
?>