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
if ($arParams["ELEM_VAR"] == '')
	$arParams["BP_VAR"] = "bp_id";

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if ($arParams["PATH_TO_INDEX"] == '')
	$arParams["PATH_TO_INDEX"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index";

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if ($arParams["PATH_TO_LIST"] == '')
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#");

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if ($arParams["PATH_TO_TASK"] == '')
	$arParams["PATH_TO_TASK"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["TASK_VAR"]."=#task_id#";
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((mb_strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

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

$arParams["BP_ID"] = intval($arParams["BP_ID"]);
if ($arParams["BP_ID"] <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_EMPTY_BPID").". ";

$arResult["BackUrl"] = urlencode(empty($_REQUEST["back_url"]) ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

$arResult["PATH_TO_INDEX"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
$arResult["PATH_TO_LIST"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"]));
$arResult["PATH_TO_BP"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BP"], array("block_id" => $arParams["BLOCK_ID"]));
$arResult["PATH_TO_SETVAR"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SETVAR"], array("block_id" => $arParams["BLOCK_ID"]));
$arResult["PATH_TO_LOG"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array("bp_id" => $arParams["BP_ID"], "block_id" => $arParams["BLOCK_ID"]));

$documentType = array("bizproc", "CBPVirtualDocument", "type_".$arParams["BLOCK_ID"]);
$arResult["DocumentType"] = $documentType;

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
if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$workflowTemplateId = 0;
	$db = CBPWorkflowTemplateLoader::GetList(array(), array("DOCUMENT_TYPE" => $documentType), false, false, array("ID"));
	if ($ar = $db->Fetch())
		$workflowTemplateId = intval($ar["ID"]);

	if ($workflowTemplateId <= 0)
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"])));
}
*/

if ($arResult["FatalErrorMessage"] == '')
{
/*	$arWorkflowTemplate = CBPWorkflowTemplateLoader::GetTemplateState($workflowTemplateId);

	if (!is_array($arWorkflowTemplate["STATE_PERMISSIONS"]) || count($arWorkflowTemplate["STATE_PERMISSIONS"]) <= 0)
		$arWorkflowTemplate["STATE_PERMISSIONS"]["create"] = array("author");

	$arResult["AllowableOperations"] = CBPDocument::GetAllowableOperations($GLOBALS["USER"]->GetID(), $GLOBALS["USER"]->GetUserGroupArray(), array($arWorkflowTemplate));
*/
	$arResult["AllowAdmin"] = ($GLOBALS["USER"]->IsAdmin() || (is_array($arParams["ADMIN_ACCESS"]) && count(array_intersect($arParams["ADMIN_ACCESS"], $GLOBALS["USER"]->GetUserGroupArray())) > 0));
/*	$arResult["AllowCreate"] = ($arResult["AllowAdmin"] || (is_array($arResult["AllowableOperations"]) && in_array("create", $arResult["AllowableOperations"]) || is_array($arWorkflowTemplate["STATE_PERMISSIONS"]["create"]) && in_array("author", $arWorkflowTemplate["STATE_PERMISSIONS"]["create"])));*/
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
							$arResult["FatalErrorMessage"] .= $e["message"].". ";
					}
				}
				else
				{
					$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_DOCUMENT").". ";
				}
			}
			else
			{
				$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_MISSING_DOCUMENT").". ";
			}

			if ($arResult["FatalErrorMessage"] == '')
				LocalRedirect($APPLICATION->GetCurPageParam("", array("sessid", "stop_bizproc_id", "process_state_event", "bizproc_event", "bizproc_id", "delete_bizproc_id")));
		}
	}
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arSelectFields = array();

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");

	$arResult["DocumentFields"] = $arDocumentFields = $documentService->GetDocumentFields($documentType);
	foreach ($arDocumentFields as $key => $value)
	{
		if (mb_strpos($key, 'PROPERTY_') === false)
			$arSelectFields[] = $key;
	}

	/*$db = CIBlockElement::GetList(
		array(),
		array("ID" => $arParams["BP_ID"], "IBLOCK_ID" => $arParams["BLOCK_ID"], "CHECK_BP_VIRTUAL_PERMISSIONS" => "read"),
		false,
		false,
		$arSelectFields
	);*/
	list($dbRecordsList, $dbRecordsList1) = CBPVirtualDocument::GetList(
		array(),
		array("ID" => $arParams["BP_ID"], "IBLOCK_ID" => $arParams["BLOCK_ID"], "CHECK_BP_VIRTUAL_PERMISSIONS" => "read"),
		false,
		false,
		$arSelectFields
	);
	if ($arRecord = $dbRecordsList->GetNext())
	{
		$arKeys = array_keys($arRecord);
		foreach ($arKeys as $key)
		{
			if ($arDocumentFields[$key]["BaseType"] == "file")
			{
				$ar = array_filter((array)$arRecord[$key]);
				$arRecord[$key] = '';
				if (sizeof($ar) > 0)
				{
					$fileIterator = CFile::getList(array('ID' => 'ASC'), array('@ID' => $ar));
					while ($file = $fileIterator->fetch())
					{
						if ($arRecord[$key] != '')
							$arRecord[$key] .= ' ';
						$arRecord[$key] .= '<a href="/bitrix/tools/bizproc_show_file.php?bp_id=' . $arParams['BP_ID'] . '&iblock_id=' . $arParams['BLOCK_ID'] . '&f=' . urlencode($key) . '&i=' . $file['ID'] . '">' . htmlspecialcharsbx($file['ORIGINAL_NAME']) . '</a>';
					}
				}
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
			if (CheckDateTime($arRecord[$key]))
			{
				$arRecord[$key] = FormatDateFromDB($arRecord[$key]);
			}
		}

		$arResult["BP"] = $arRecord;
	}
	else
	{
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_WRONG_BP").". ";
	}
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
	if ("user_".$GLOBALS["USER"]->GetID() == $arResult["BP"]["CREATED_BY"])
		$arCurrentUserGroups[] = "author";

	$documentId = array("bizproc", "CBPVirtualDocument", $arResult["BP"]["ID"]);
	$arDocumentStates = CBPDocument::GetDocumentStates($documentType, $documentId);

	foreach ($arDocumentStates as $arDocumentState)
	{
		$arResult["BP"]["DOCUMENT_STATE"] = $arDocumentState;
		$ar = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arCurrentUserGroups, $arDocumentState);
		foreach ($ar as $ar1)
		{
			$ar1["URL"] = $APPLICATION->GetCurPageParam("bizproc_id=".$arDocumentState["ID"]."&process_state_event=Y&bizproc_event=".htmlspecialcharsbx($ar1["NAME"])."&".bitrix_sessid_get(), array("sessid", "stop_bizproc_id", "process_state_event", "bizproc_event", "bizproc_id"));
			$arResult["BP"]["DOCUMENT_STATE_EVENTS"][] = $ar1;
		}
		if (
			isset($arResult["BP"]["DOCUMENT_STATE_EVENTS"])
			&& count($arResult["BP"]["DOCUMENT_STATE_EVENTS"]) > 0
		)
		{
			$arResult["ShowStateEvents"] = true;
		}

		$arResult["BP"]["DOCUMENT_STATE_TASKS"] = array();
		$ar = CBPDocument::GetUserTasksForWorkflow($GLOBALS["USER"]->GetID(), $arDocumentState["ID"]);
		foreach ($ar as $ar1)
		{
			$ar1["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASK"], array("task_id" => $ar1["ID"], "block_id" => $arParams["BLOCK_ID"]));
			$arResult["BP"]["DOCUMENT_STATE_TASKS"][] = $ar1;
		}
		if (count($arResult["BP"]["DOCUMENT_STATE_TASKS"]) > 0)
			$arResult["ShowTasks"] = true;

		$arResult["BP"]["CancelUrl"] = "";
		if ($arResult["AllowAdmin"] && $arDocumentState["ID"] <> '' && $arDocumentState["WORKFLOW_STATUS"] <> '')
			$arResult["BP"]["CancelUrl"] = $APPLICATION->GetCurPageParam("stop_bizproc_id=".$arDocumentState["ID"]."&".bitrix_sessid_get(), array("sessid", "stop_bizproc_id"));
	}
}

$this->IncludeComponentTemplate();

if ($arResult["FatalErrorMessage"] == '')
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPABL_PAGE_TITLE").": ".$arResult["BP"]["NAME"]);
	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arResult["PATH_TO_INDEX"]);
		$APPLICATION->AddChainItem($arResult["Block"]["NAME"], $arResult["PATH_TO_LIST"]);
		$APPLICATION->AddChainItem(GetMessage("BPABL_PAGE_TITLE").": ".$arResult["BP"]["NAME"]);
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WLC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WLC_ERROR"));
}
?>