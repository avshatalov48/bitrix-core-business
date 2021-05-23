<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule("bizproc"))
{
	ShowError(GetMessage("SONET_MODULE_BIZPROC_NOT_INSTALL"));
	return;
}

if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_BIZPROC"] = trim($arParams["PATH_TO_BIZPROC"]);
if ($arParams["PATH_TO_BIZPROC"] == '')
	$arParams["PATH_TO_BIZPROC"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bizproc");

$arParams["TASK_ID"] = intval($arParams["TASK_ID"]);

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_BIZPROC_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] != "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_BIZPROC_TITLE"), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BIZPROC"], array()));

	$arTask = false;
	if ($arParams["TASK_ID"] > 0)
	{
		$dbTask = CBPTaskService::GetList(
			array(),
			array("ID" => $arParams["TASK_ID"], "USER_ID" => $USER->GetID()),
			false,
			false,
			array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS")
		);
		$arTask = $dbTask->GetNext();
	}

	if (!$arTask)
	{
		$workflowId = trim($_REQUEST["workflow_id"]);

		if ($workflowId <> '')
		{
			$dbTask = CBPTaskService::GetList(
				array(),
				array("WORKFLOW_ID" => $workflowId, "USER_ID" => $USER->GetID()),
				false,
				false,
				array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS")
			);
			$arTask = $dbTask->GetNext();
		}
	}

	if (!empty($arTask))
	{
		$arResult["arTask"] = $arTask;
		$arResult["showType"] = "Form";
		if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "doTask" && check_bitrix_sessid())
		{
			$arErrorsTmp = array();
			if (CBPDocument::PostTaskForm($arResult["arTask"], $USER->GetID(), $_REQUEST, $arErrorsTmp, $USER->GetFullName()))
			{
				$arResult["showType"] = "Success";
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BIZPROC"], array()));
				die();
			}
			else
			{
				foreach ($arErrorsTmp as $e)
					$arResult["ErrorMessage"] .= $e["message"].".<br />";
			}
		}


		$APPLICATION->SetTitle(str_replace("#ID#", $arParams["TASK_ID"], GetMessage("BPAT_TITLE")));

		list($arResult["taskForm"], $arResult["taskFormButtons"]) = array("", "");
		if ($arResult["showType"] != "Success")
			list($arResult["taskForm"], $arResult["taskFormButtons"]) = CBPDocument::ShowTaskForm($arResult["arTask"], $USER->GetID());

	}
}
$this->IncludeComponentTemplate();
?>