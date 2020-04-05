<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["TASK_VAR"]) <= 0)
	$arParams["TASK_VAR"] = "task_id";
if (strlen($arParams["BLOCK_VAR"]) <= 0)
	$arParams["BLOCK_VAR"] = "block_id";

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if (strlen($arParams["PATH_TO_INDEX"]) <= 0)
	$arParams["PATH_TO_INDEX"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index";

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if (strlen($arParams["PATH_TO_LIST"]) <= 0)
	$arParams["PATH_TO_LIST"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#";

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if (strlen($arParams["PATH_TO_TASK"]) <= 0)
	$arParams["PATH_TO_TASK"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["TASK_VAR"]."=#task_id#";
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if (strlen($arParams["IBLOCK_TYPE"]) <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WTC_EMPTY_IBLOCK_TYPE").". ";

$arParams["BLOCK_ID"] = intval($arParams["BLOCK_ID"]);
if ($arParams["BLOCK_ID"] <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WTC_EMPTY_IBLOCK").". ";

$arResult["PATH_TO_INDEX"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
$arResult["PATH_TO_LIST"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"]));

$arResult["BackUrl"] = empty($_REQUEST["back_url"]) ? $arResult["PATH_TO_LIST"] : $_REQUEST["back_url"];

if (!check_bitrix_sessid())
	$arResult["FatalErrorMessage"] .= str_replace("#URL#", $arResult["PATH_TO_LIST"], GetMessage("BPWC_WTC_PERMS_ERROR")).". ";

$taskId = $arParams["TASK_ID"] = IntVal($arParams["TASK_ID"]);

$arResult["Task"] = false;

if ($taskId > 0)
{
	$dbTask = CBPTaskService::GetList(
		array(),
		array("ID" => $taskId, "USER_ID" => $USER->GetID(), 'STATUS' => CBPTaskStatus::Running, 'USER_STATUS' => CBPTaskUserStatus::Waiting),
		false,
		false,
		array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS")
	);
	$arResult["Task"] = $dbTask->GetNext();
}

if (!$arResult["Task"] && !empty($_REQUEST["workflow_id"]))
{
	$workflowId = trim($_REQUEST["workflow_id"]);

	if (strlen($workflowId) > 0)
	{
		$dbTask = CBPTaskService::GetList(
			array(),
			array("WORKFLOW_ID" => $workflowId, "USER_ID" => $USER->GetID(), 'STATUS' => CBPTaskStatus::Running, 'USER_STATUS' => CBPTaskUserStatus::Waiting),
			false,
			false,
			array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS")
		);
		$arResult["Task"] = $dbTask->GetNext();
	}
}

if (!$arResult["Task"])
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WTC_WRONG_TASK").". ";

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WTC_WRONG_IBLOCK_TYPE").". ";
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["Block"] = null;
	$db = CIBlock::GetList(array(), array("ID" => $arParams["BLOCK_ID"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
	if ($ar = $db->GetNext())
		$arResult["Block"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WTC_WRONG_IBLOCK").". ";
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["ShowType"] = "Form";

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "doTask" && check_bitrix_sessid())
	{
		$arErrorsTmp = array();
		if (CBPDocument::PostTaskForm($arResult["Task"], $USER->GetID(), $_REQUEST + $_FILES, $arErrorsTmp, $USER->GetFormattedName(false)))
		{
			$arResult["ShowType"] = "Success";

			$d = CBPTaskService::GetList(
				array(),
				array("WORKFLOW_ID" => $arResult["Task"]['WORKFLOW_ID'], "USER_ID" => (int)$GLOBALS["USER"]->GetID(), 'STATUS' => CBPTaskStatus::Running, 'USER_STATUS' => CBPTaskUserStatus::Waiting),
				false,
				false,
				array("ID")
			);
			if ($r = $d->Fetch())
				$backUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASK"], array("task_id" => $r["ID"], "block_id" => $arParams["BLOCK_ID"]));
			else
				$backUrl = $arResult["BackUrl"];

			if (strlen($backUrl) > 0)
			{
				LocalRedirect($backUrl);
				die();
			}
		}
		else
		{
			foreach ($arErrorsTmp as $e)
				$arResult["ErrorMessage"] .= $e["message"].".<br />";
		}
	}
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	list($taskForm, $taskFormButtons) = array("", "");
	if ($arResult["ShowType"] != "Success")
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$documentType = $documentService->GetDocumentType($arResult["Task"]["PARAMETERS"]["DOCUMENT_ID"]);
		if (!array_key_exists("BP_AddShowParameterInit_".$documentType[0]."_".$documentType[1]."_".$documentType[2], $GLOBALS))
		{
			$GLOBALS["BP_AddShowParameterInit_".$documentType[0]."_".$documentType[1]."_".$documentType[2]] = 1;
			CBPDocument::AddShowParameterInit($documentType[0], "only_users", $documentType[2], $documentType[1]);
		}

		list($taskForm, $taskFormButtons) = CBPDocument::ShowTaskForm(
			$arResult["Task"],
			$USER->GetID(),
			"",
			($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "doTask") ? $_REQUEST : null
		);
	}
	$arResult["TaskForm"] = $taskForm;
	$arResult["TaskFormButtons"] = $taskFormButtons;
}

$this->IncludeComponentTemplate();


if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["Task"]["NAME"], GetMessage("BPWC_WTC_PAGE_TITLE")));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arResult["PATH_TO_INDEX"]);
		$APPLICATION->AddChainItem($arResult["Block"]["NAME"], $arResult["PATH_TO_LIST"]);
		$APPLICATION->AddChainItem(str_replace("#NAME#", $arResult["Task"]["NAME"], GetMessage("BPWC_WTC_PAGE_NAV_CHAIN")));
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WTC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WTC_ERROR"));
}
?>