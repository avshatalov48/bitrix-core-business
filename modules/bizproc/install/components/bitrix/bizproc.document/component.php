<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule('bizproc'))
{
	return false;
}

/********************************************************************
 * Input params
 ********************************************************************/
/***************** BASE ********************************************/
$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] ?? '' : $arParams["DOCUMENT_ID"]);

if (isset($arParams["TASK_ID"]))
{
	$arTask = CBPStateService::GetWorkflowState($arParams["TASK_ID"]);
	if (!empty($arTask))
	{
		$arResult["TASK"] = $arTask;
	}
	else
	{
		if (isset($arParams["TASK_LIST_URL"]))
		{
			LocalRedirect($arParams["TASK_LIST_URL"]);
		}
	}
}
//***************** URL ********************************************/
$arParams["back_url"] = (!empty($arParams["back_url"]) ? $arParams["back_url"] : (!empty($_REQUEST["back_url"]) ? urldecode($_REQUEST["back_url"]) : ""));

$URL_NAME_DEFAULT = [
	"workflow_start" => "PAGE_NAME=start&ID=#ID#",
	"workflow_log" => "PAGE_NAME=log&ID=#ID#",
	"task_edit" => "PAGE_NAME=task_edit&ID=#ID#",
];

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[mb_strtoupper($URL) . "_URL"] = trim($arParams[mb_strtoupper($URL) . "_URL"] ?? '');
	if (empty($arParams[mb_strtoupper($URL) . "_URL"])):
		$arParams[mb_strtoupper($URL) . "_URL"] = $APPLICATION->GetCurPage();
	endif;
	$arParams["~" . mb_strtoupper($URL) . "_URL"] = $arParams[mb_strtoupper($URL) . "_URL"];
	$arParams[mb_strtoupper($URL) . "_URL"] = htmlspecialcharsbx($arParams["~" . mb_strtoupper($URL) . "_URL"]);
}
/***************** ADDITIONAL **************************************/
/***************** STANDART ****************************************/
$arParams["SET_TITLE"] = (isset($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] === "N" ? "N" : "Y");
/********************************************************************
 * /Input params
 ********************************************************************/

/********************************************************************
 * Main data
 ********************************************************************/
$arError = [];
if ($arParams["MODULE_ID"] == '')
{
	$arError[] = [
		"id" => "empty_module_id",
		"text" => GetMessage("BPATT_NO_MODULE_ID"),
	];
}
if ($arParams["ENTITY"] == '')
{
	$arError[] = [
		"id" => "empty_entity",
		"text" => GetMessage("BPABS_EMPTY_ENTITY"),
	];
}
if ($arParams["DOCUMENT_TYPE"] == '')
{
	$arError[] = [
		"id" => "empty_document_type",
		"text" => GetMessage("BPABS_EMPTY_DOC_TYPE"),
	];
}
if ($arParams["DOCUMENT_ID"] == '')
{
	$arError[] = [
		"id" => "empty_document_id",
		"text" => GetMessage("BPABS_EMPTY_DOC_ID"),
	];
}

$arParams["DOCUMENT_TYPE"] = [$arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_TYPE"]];
$arParams["DOCUMENT_ID"] = [$arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_ID"]];

$isLazyLoad = isset($arParams['LAZYLOAD']) && $arParams['LAZYLOAD'] === 'Y';

if (empty($arError))
{
	$arDocumentStates = $isLazyLoad
		? CBPDocument::getActiveStates($arParams["DOCUMENT_ID"])
		: CBPDocument::GetDocumentStates($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"])
	;

	$arResult['DOCUMENT_STATES'] = $arDocumentStates;

	if (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		["DocumentStates" => $arDocumentStates]
	))
	{
		$arError[] = [
			"id" => "access_denied",
			"text" => GetMessage("BPADH_NO_PERMS"),
		];
	}
}
if (!empty($arError))
{
	$e = new CAdminException($arError);
	ShowError($e->GetString());

	return false;
}

if (method_exists($arParams["DOCUMENT_TYPE"][1], "GetUserGroups"))
{
	$arParams["USER_GROUPS"] = call_user_func_array(
		[$arParams["DOCUMENT_TYPE"][1], "GetUserGroups"],
		[$arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()]
	);
}
else
{
	$arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();
}

/********************************************************************
 * /Main data
 ********************************************************************/

$arResult["ERROR_MESSAGE"] = "";
$arParams["StartWorkflowPermission"] = (CBPDocument::CanUserOperateDocument(
	CBPCanUserOperateOperation::StartWorkflow,
	$USER->GetID(),
	$arParams["DOCUMENT_ID"],
	["DocumentStates" => $arDocumentStates]
) ? "Y" : "N");
$arParams["StopWorkflowPermission"] = $arParams["StartWorkflowPermission"];
$arParams["DropWorkflowPermission"] = (CBPDocument::CanUserOperateDocument(
	CBPCanUserOperateOperation::CreateWorkflow,
	$GLOBALS["USER"]->GetID(),
	$arParams["DOCUMENT_ID"],
	["DocumentStates" => $arDocumentStates]) ? "Y" : "N"
);

/********************************************************************
 * Action
 ********************************************************************/
if (!((!empty($_REQUEST["action"]) || $_SERVER['REQUEST_METHOD'] == "POST") && check_bitrix_sessid()))
{
}
elseif (!empty($_POST["cancel"]) && !empty($arParams["back_url"]))
{
	LocalRedirect($arParams["back_url"]);
}
else
{
	if (isset($_REQUEST["action"]) && $_REQUEST["action"] === "stop_bizproc")
	{
		if ($arParams["StopWorkflowPermission"] != "Y")
		{
			$arError[] = [
				"id" => "access_denied",
				"text" => GetMessage("BPADH_NO_PERMS"),
			];
		}
		else
		{
			CBPDocument::TerminateWorkflow(
				$_REQUEST["id"],
				$arParams["DOCUMENT_ID"],
				$ar
			);

			if ($ar)
			{
				$str = "";
				foreach ($ar as $a)
					$str .= $a["message"];
				$arError[] = [
					"id" => "stop_bizproc",
					"text" => $str];
			}
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] === "del_bizproc")
	{
		if ($arParams["DropWorkflowPermission"] != "Y")
		{
			$arError[] = [
				"id" => "access_denied",
				"text" => GetMessage("BPADH_NO_PERMS")];
		}
		else
		{
			$terminateWorkflow = isset($arDocumentStates[$_REQUEST['id']]['WORKFLOW_STATUS']) && $arDocumentStates[$_REQUEST['id']]['WORKFLOW_STATUS'] !== null;
			$ar = CBPDocument::killWorkflow($_REQUEST["id"], $terminateWorkflow, $arParams["DOCUMENT_ID"]);

			if (count($ar) > 0)
			{
				$str = "";
				foreach ($ar as $a)
					$str .= $a["message"];
				$arError[] = [
					"id" => "stop_bizproc",
					"text" => $str];
			}
		}
	}
	elseif (
		$_SERVER['REQUEST_METHOD'] === "POST"
		&& isset($_REQUEST["bizproc_index"])
		&& intval($_REQUEST["bizproc_index"]) > 0
	)
	{
		$arBizProcWorkflowId = [];
		$bizprocIndex = intval($_REQUEST["bizproc_index"]);
		$needUpdateStatesList = false;
		for ($i = 1; $i <= $bizprocIndex; $i++)
		{
			$bpId = trim($_REQUEST["bizproc_id_" . $i] ?? '');
			$bpTemplateId = intval($_REQUEST["bizproc_template_id_" . $i] ?? 0);
			$bpEvent = trim($_REQUEST["bizproc_event_" . $i] ?? '');

			if ($bpEvent <> '')
			{
				if ($bpId <> '')
				{
					if (!array_key_exists($bpId, $arDocumentStates))
						continue;
				}
				else
				{
					if (!array_key_exists($bpTemplateId, $arDocumentStates))
						continue;
					$bpId = $arBizProcWorkflowId[$bpTemplateId];
				}

				$needUpdateStatesList = true;
				$arErrorTmp = [];
				CBPDocument::SendExternalEvent(
					$bpId,
					$bpEvent,
					["Groups" => $arParams["USER_GROUPS"], "User" => $GLOBALS["USER"]->GetID()],
					$arErrorTmp
				);

				if ($arErrorTmp)
				{
					$arError[] = [
						"id" => "update_workfow",
						"text" => implode(', ', array_column($arErrorTmp, 'message')),
					];
				}
			}
		}
		if ($needUpdateStatesList && empty($arError))
		{
			$arResult['DOCUMENT_STATES'] = CBPDocument::GetDocumentStates($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"]);
		}
	}

	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	}
	else
	{
		$url = (!empty($arParams["back_url"]) ? $arParams["back_url"] : $APPLICATION->GetCurPageParam("", ["action", "id", "sessid"]));
		$url = (empty($_POST["apply"]) ? $url : $APPLICATION->GetCurPageParam("", ["action", "id", "sessid"]));
		if (!empty($_REQUEST['action']))
		{
			LocalRedirect($url);
		}
	}
}
/********************************************************************
 * /Action
 ********************************************************************/

/********************************************************************
 * Data
 ********************************************************************/

/********************************************************************
 * /Data
 ********************************************************************/

$this->IncludeComponentTemplate();

/********************************************************************
 * Standart operations
 ********************************************************************/
if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("BPADH_TITLE"));
}
/********************************************************************
 * /Standart operations
 ********************************************************************/
