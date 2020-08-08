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

if ($arParams["TASK_VAR"] == '')
	$arParams["TASK_VAR"] = "task_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_BIZPROC_EDIT"] = trim($arParams["PATH_TO_BIZPROC_EDIT"]);
if ($arParams["PATH_TO_BIZPROC_EDIT"] == '')
	$arParams["PATH_TO_BIZPROC_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bizproc_edit&".$arParams["TASK_VAR"]."=#task_id#");

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_BIZPROC_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] != "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_BIZPROC_TITLE"));
		
	$dbResultList = CBPTaskService::GetList(
		array("MODIFIED" => "DESC"),
		array("USER_ID" => $USER->GetID()),
		false,
		false,
		array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS")
	);

	while ($arResultItem = $dbResultList->GetNext())
	{
		if (mb_strlen($arResultItem["DESCRIPTION"]) > 100)
			$arResultItem["DESCRIPTION"] = mb_substr($arResultItem["DESCRIPTION"], 0, 97)."...";
		$arResultItem["EditUrl"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BIZPROC_EDIT"], array("task_id" => $arResultItem["ID"]));
		$arResult["TASKS"][] = $arResultItem;
	}

	$dbTracking = CBPTrackingService::GetList(Array("MODIFIED" => "DESC"), Array("MODIFIED_BY" => $USER->GetID()));
	while($arTracking = $dbTracking->GetNext())
	{
		if ($arTracking["WORKFLOW_ID"] <> '')
		{
			$arTracking["STATE"] = CBPStateService::GetWorkflowState($arTracking["WORKFLOW_ID"]);
			$arTracking["STATE"]["Url"] = CBPDocument::GetDocumentAdminPage($arTracking["STATE"]["DOCUMENT_ID"]);
		}
		$arResult["TRACKING"][] = $arTracking;
	}
}
$this->IncludeComponentTemplate();
?>