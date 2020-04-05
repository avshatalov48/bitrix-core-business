<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule('bizproc')):
	return false;
endif;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
	$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
	$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
	$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);

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
	
	$URL_NAME_DEFAULT = array(
		"workflow_start" => "PAGE_NAME=start&ID=#ID#", 
		"workflow_log" => "PAGE_NAME=log&ID=#ID#", 
		"task_edit" => "PAGE_NAME=task_edit&ID=#ID#" 
		);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"])):
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage();
		endif;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Main data
********************************************************************/
$arError = array();
if (strlen($arParams["MODULE_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_module_id",
		"text" => GetMessage("BPATT_NO_MODULE_ID"));
if (strlen($arParams["ENTITY"]) <= 0)
	$arError[] = array(
		"id" => "empty_entity",
		"text" => GetMessage("BPABS_EMPTY_ENTITY"));
if (strlen($arParams["DOCUMENT_TYPE"]) <= 0)
	$arError[] = array(
		"id" => "empty_document_type",
		"text" => GetMessage("BPABS_EMPTY_DOC_TYPE"));
if (strlen($arParams["DOCUMENT_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_document_id",
		"text" => GetMessage("BPABS_EMPTY_DOC_ID"));

$arParams["DOCUMENT_TYPE"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_TYPE"]);
$arParams["DOCUMENT_ID"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_ID"]);

if (empty($arError))
{
	$arDocumentStates = CBPDocument::GetDocumentStates(
		$arParams["DOCUMENT_TYPE"],
		$arParams["DOCUMENT_ID"]);

	$arResult['DOCUMENT_STATES'] = $arDocumentStates;

	if (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array("DocumentStates" => $arDocumentStates)
	))
	{
		$arError[] = array(
			"id"   => "access_denied",
			"text" => GetMessage("BPADH_NO_PERMS")
		);
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
		array($arParams["DOCUMENT_TYPE"][1], "GetUserGroups"), 
		array($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()));
}
else 
{
	$arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();
}

/********************************************************************
				/Main data
********************************************************************/

$arResult["ERROR_MESSAGE"] = "";
$arParams["StartWorkflowPermission"] = (CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::StartWorkflow,
		$USER->GetID(),
		$arParams["DOCUMENT_ID"],
		array("DocumentStates" => $arDocumentStates)
	) ? "Y" : "N");
$arParams["StopWorkflowPermission"] = $arParams["StartWorkflowPermission"];
$arParams["DropWorkflowPermission"] = (CBPDocument::CanUserOperateDocument(
	CBPCanUserOperateOperation::CreateWorkflow,
	$GLOBALS["USER"]->GetID(),
	$arParams["DOCUMENT_ID"],
	array("DocumentStates" => $arDocumentStates)) ? "Y" : "N");

/********************************************************************
				Action
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
	if ($_REQUEST["action"] == "stop_bizproc")
	{
		if ($arParams["StopWorkflowPermission"] != "Y")
		{
			$arError[] = array(
				"id" => "access_denied",
				"text" => GetMessage("BPADH_NO_PERMS"));
		}
		else 
		{
			CBPDocument::TerminateWorkflow(
				$_REQUEST["id"],
				$arParams["DOCUMENT_ID"],
				$ar
			);
	
			if (count($ar) > 0)
			{
				$str = "";
				foreach ($ar as $a)
					$str .= $a["message"];
				$arError[] = array(
					"id" => "stop_bizproc", 
					"text" => $str);
			}
		}
	}
	elseif ($_REQUEST["action"] == "del_bizproc")
	{
		if ($arParams["DropWorkflowPermission"] != "Y")
		{
			$arError[] = array(
				"id" => "access_denied",
				"text" => GetMessage("BPADH_NO_PERMS"));
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
				$arError[] = array(
					"id" => "stop_bizproc",
					"text" => $str);
			}
		}
	}
	elseif ($_SERVER['REQUEST_METHOD'] == "POST" && intval($_REQUEST["bizproc_index"]) > 0)
	{
		$arBizProcWorkflowId = array();
		$bizprocIndex = intval($_REQUEST["bizproc_index"]);
		$needUpdateStatesList = false;
		for ($i = 1; $i <= $bizprocIndex; $i++)
		{
			$bpId = trim($_REQUEST["bizproc_id_".$i]);
			$bpTemplateId = intval($_REQUEST["bizproc_template_id_".$i]);
			$bpEvent = trim($_REQUEST["bizproc_event_".$i]);

			if (strlen($bpEvent) > 0)
			{
				if (strlen($bpId) > 0)
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
				$arErrorTmp = array();
				CBPDocument::SendExternalEvent(
					$bpId,
					$bpEvent,
					array("Groups" => $arParams["USER_GROUPS"], "User" => $GLOBALS["USER"]->GetID()),
					$arErrorTmp
				);

				if (count($arErrorsTmp) > 0)
				{
					foreach ($arErrorsTmp as $e)
						$strWarning .= $e["message"]."<br />";
					$arError[] = array(
						"id" => "update_workfow", 
						"text" => $strWarning);
				}
			}
		}
		if ($needUpdateStatesList && empty($arError))
			$arResult['DOCUMENT_STATES'] = CBPDocument::GetDocumentStates($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"]);

	}
		
	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	}
	else
	{
		$url = (!empty($arParams["back_url"]) ? $arParams["back_url"] : $APPLICATION->GetCurPageParam("", array("action", "id", "sessid")));
		$url = (empty($_POST["apply"]) ? $url : $APPLICATION->GetCurPageParam("", array("action", "id", "sessid")));
		if (isset($_REQUEST['action']))
			LocalRedirect($url);
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/

/********************************************************************
				/Data
********************************************************************/

$this->IncludeComponentTemplate();

/********************************************************************
				Standart operations
********************************************************************/
if($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("BPADH_TITLE"));
}
/********************************************************************
				/Standart operations
********************************************************************/
?>
