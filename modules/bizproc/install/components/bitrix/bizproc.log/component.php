<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (array_key_exists("COMPONENT_VERSION", $arParams) && $arParams["COMPONENT_VERSION"] == 2)
{
	if (!CModule::IncludeModule("bizproc"))
		return false;

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["APPLICATION"]->AuthForm("");
		die();
	}

	$arParams["ID"] = trim($arParams["ID"]);
	if (strlen($arParams["ID"]) <= 0)
		$arParams["ID"] = trim($_REQUEST["ID"]);
	if (strlen($arParams["ID"]) <= 0)
		$arParams["ID"] = trim($_REQUEST["id"]);

	if (strlen($arParams["ID"]) <= 0)
		$arResult["FatalErrorMessage"] .= GetMessage("BPABL_INVALID_WF").". ";

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["INLINE_MODE"] = ($arParams["INLINE_MODE"] == "Y" ? "Y" : "N");
	$arResult["AJAX_MODE"] = $arParams["AJAX_MODE"] = ($arParams["AJAX_MODE"] == "N" ? "N" : "Y"); //Backward compatibility

	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

	$arResult["FatalErrorMessage"] = "";
	$arResult["ErrorMessage"] = "";

	if (strlen($arResult["FatalErrorMessage"]) <= 0)
	{
		$arResult["WorkflowState"] = $arWorkflowState = CBPStateService::GetWorkflowState($arParams["ID"]);
		if (!is_array($arWorkflowState) || count($arWorkflowState) <= 0)
			$arResult["FatalErrorMessage"] .= GetMessage("BPABL_INVALID_WF").". ";
	}

	if (strlen($arResult["FatalErrorMessage"]) <= 0)
	{
		$documentId = $arWorkflowState["DOCUMENT_ID"];
		$bCanView = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ViewWorkflow,
			$GLOBALS["USER"]->GetID(),
			$documentId,
			array("WorkflowId" => $arParams["ID"])
		);
		if (!$bCanView)
			$arResult["FatalErrorMessage"] .= GetMessage("BPABL_NO_PERMS").". ";
	}

	if (strlen($arResult["FatalErrorMessage"]) <= 0)
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$documentType = $documentService->GetDocumentType($documentId);

		$arResult["GRID_ID"] = "bizproc_loggrid_".$arWorkflowState["WORKFLOW_TEMPLATE_ID"];

		$gridOptions = new CGridOptions($arResult["GRID_ID"]);
		$gridColumns = $gridOptions->GetVisibleColumns();
		$gridSort = $gridOptions->GetSorting(array("sort"=>array("ID" => "desc")));

		$arResult["SORT"] = $gridSort["sort"];

		$arResult["FILTER"] = array();
		if ($arParams["INLINE_MODE"] != "Y")
		{
			$arResult["FILTER"] = array(
				array(
					"id" => "MODIFIED",
					"name" => GetMessage("CBBWL_C_MODIFIED"),
					"type" => "date",
				),
				array(
					"id" => "TYPE",
					"name" => GetMessage("CBBWL_C_TYPE"),
					"type" => "list",
					"items" => array(
						"" => GetMessage("BPWC_WLC_NOT_SET"),
						6 => GetMessage("BPABL_TYPE_7"),
						5 => GetMessage("BPABL_TYPE_5"),
						1 => GetMessage("BPABL_TYPE_1"),
						2 => GetMessage("BPABL_TYPE_2"),
						3 => GetMessage("BPABL_TYPE_3"),
						4 => GetMessage("BPABL_TYPE_4"),
					),
				),
				array(
					"id" => "ADMIN_MODE",
					"name" => GetMessage("CBBWL_C_ADMIN_MODE_LABEL"),
					"type" => "list",
					"items" => array(
						'' => GetMessage("CBBWL_C_ADMIN_MODE_SIMPLE"),
						'Y' => GetMessage("CBBWL_C_ADMIN_MODE"),
					)
				),
			);
		}

		$arResult["AdminMode"] = false;

		$arFilter = array("WORKFLOW_ID" => $arWorkflowState["ID"]);
		$gridFilter = $gridOptions->GetFilter($arResult["FILTER"]);
		foreach ($gridFilter as $key => $value)
		{
			if ($key == "ADMIN_MODE")
			{
				$arResult["AdminMode"] = ($value == "Y" ? true : false);
				continue;
			}

			if (substr($key, -5) == "_from")
			{
				$op = ">=";
				$newKey = substr($key, 0, -5);
			}
			elseif (substr($key, -3) == "_to")
			{
				$op = "<=";
				$newKey = substr($key, 0, -3);
			}
			else
			{
				$op = "";
				$newKey = $key;
			}

			$arFilter[$op.$newKey] = $value;
		}

		$arResult["HEADERS"] = array(
			array("id"=>"date", "name"=>GetMessage("BPWC_WLCT_F_DATE"), "sort" => "ID", "default"=>true),
			array("id"=>"name", "name"=>GetMessage("BPWC_WLCT_F_NAME"), "default"=>true),
			array("id"=>"type", "name"=>GetMessage("BPWC_WLCT_F_TYPE"), "default"=>$arResult["AdminMode"]),
			array("id"=>"status", "name"=>GetMessage("BPWC_WLCT_F_STATUS"), "default"=>$arResult["AdminMode"]),
			array("id"=>"result", "name"=>GetMessage("BPWC_WLCT_F_RESULT"), "default"=>$arResult["AdminMode"]),
			array("id"=>"note", "name"=>GetMessage("BPWC_WLCT_F_NOTE"), "default"=>true),
			array("id"=>"modified_by", "name"=>GetMessage("BPWC_WLCT_F_MODIFIED_BY"), "default"=>false),
		);

		$arResult["RECORDS"] = array();
		$level = 0;

		$dbTrack = CBPTrackingService::GetList($gridSort["sort"], $arFilter);
		while ($arTrack = $dbTrack->GetNext())
		{
			$prefix = "";
			if (!$arResult["AdminMode"])
			{
				if ($arTrack["TYPE"] != CBPTrackingType::Custom
					&& $arTrack["TYPE"] != CBPTrackingType::FaultActivity
					&& $arTrack["TYPE"] != CBPTrackingType::Report
					&& $arTrack["TYPE"] != CBPTrackingType::Error
				)
					continue;
			}

			$date = $arTrack["MODIFIED"];

			if ($arResult["AdminMode"])
				$name = (strlen($arTrack["ACTION_TITLE"]) > 0 ? $prefix.$arTrack["ACTION_TITLE"]."<br/>".$prefix."(".$arTrack["ACTION_NAME"].")" : $prefix.$arTrack["ACTION_NAME"]);
			else
				$name = $arTrack["ACTION_TITLE"];

			switch ($arTrack["TYPE"])
			{
				case 1:
					$type = GetMessage("BPABL_TYPE_1");
					break;
				case 2:
					$type = GetMessage("BPABL_TYPE_2");
					break;
				case 3:
					$type = GetMessage("BPABL_TYPE_3");
					break;
				case 4:
					$type = GetMessage("BPABL_TYPE_4");
					break;
				case 5:
					$type = GetMessage("BPABL_TYPE_5");
					break;
				case 6:
					$type = GetMessage("BPABL_TYPE_7");
					break;
				default:
					$type = GetMessage("BPABL_TYPE_6");
			}

			switch ($arTrack["EXECUTION_STATUS"])
			{
				case CBPActivityExecutionStatus::Initialized:
					$status = GetMessage("BPABL_STATUS_1");
					break;
				case CBPActivityExecutionStatus::Executing:
					$status = GetMessage("BPABL_STATUS_2");
					break;
				case CBPActivityExecutionStatus::Canceling:
					$status = GetMessage("BPABL_STATUS_3");
					break;
				case CBPActivityExecutionStatus::Closed:
					$status = GetMessage("BPABL_STATUS_4");
					break;
				case CBPActivityExecutionStatus::Faulting:
					$status = GetMessage("BPABL_STATUS_5");
					break;
				default:
					$status = GetMessage("BPABL_STATUS_6");
			}

			switch ($arTrack["EXECUTION_RESULT"])
			{
				case CBPActivityExecutionResult::None:
					$result = GetMessage("BPABL_RES_1");
					break;
				case CBPActivityExecutionResult::Succeeded:
					$result = GetMessage("BPABL_RES_2");
					break;
				case CBPActivityExecutionResult::Canceled:
					$result = GetMessage("BPABL_RES_3");
					break;
				case CBPActivityExecutionResult::Faulted:
					$result = GetMessage("BPABL_RES_4");
					break;
				case CBPActivityExecutionResult::Uninitialized:
					$result = GetMessage("BPABL_RES_5");
					break;
				default:
					$result = GetMessage("BPABL_RES_6");
			}

			$note = $arTrack["ACTION_NOTE"];
			$note = CBPTrackingService::parseStringParameter($note, $documentType);

			$modified_by = "";
			if (intval($arTrack["MODIFIED_BY"]) > 0)
			{
				$dbUserTmp = CUser::GetByID($arTrack["MODIFIED_BY"]);
				$arUserTmp = $dbUserTmp->GetNext();
				$modified_by = CUser::FormatName($arParams["NAME_TEMPLATE"], $arUserTmp, true, false);
				$modified_by .= " [".$arTrack["MODIFIED_BY"]."]";
			}

			$aCols = array("date" => $date, "name" => $name, "type" => $type, "status" => $status, "result" => $result, "note" => $note, "modified_by" => $modified_by);
			$aActions = array();

			$arResult["RECORDS"][] = array("data" => $arTrack, "actions" => $aActions, "columns" => $aCols, "editable" => false);
		}
	}

	if (strlen($arResult["FatalErrorMessage"]) <= 0)
	{
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(GetMessage("BPABL_PAGE_TITLE").": ".$arResult["WorkflowState"]["TEMPLATE_NAME"]);
		if ($arParams["SET_NAV_CHAIN"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("BPABL_PAGE_TITLE").": ".$arResult["WorkflowState"]["TEMPLATE_NAME"]);
	}
	else
	{
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(GetMessage("BPWC_WLC_ERROR"));
		if ($arParams["SET_NAV_CHAIN"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("BPWC_WLC_ERROR"));
	}

	$this->IncludeComponentTemplate();
}
else
{
	if (!CModule::IncludeModule('bizproc')):
		return false;
	endif;

	/********************************************************************
					Input params
	********************************************************************/
	/***************** BASE ********************************************/
		$arParams["ID"] = trim($arParams["ID"]);
		if ($arParams["ID"] <= 0)
			$arParams["ID"] = trim($_REQUEST["ID"]);
		if ($arParams["ID"] <= 0)
			$arParams["ID"] = trim($_REQUEST["id"]);

		$arParams["USER_ID"] = intVal($GLOBALS["USER"]->GetID());
		$arParams["WORKFLOW_ID"] = (empty($arParams["WORKFLOW_ID"]) ? $_REQUEST["WORKFLOW_ID"] : $arParams["WORKFLOW_ID"]);
	//***************** URL ********************************************/
		$arResult["back_url"] = urlencode(empty($_REQUEST["back_url"]) ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);
	/***************** ADDITIONAL **************************************/
		$arParams["PAGE_ELEMENTS"] = intVal(intVal($arParams["PAGE_ELEMENTS"]) > 0 ? $arParams["PAGE_ELEMENTS"] : 50);
		$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	/***************** STANDART ****************************************/
		$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	/********************************************************************
					/Input params
	********************************************************************/

	$arError = array();

	if (strlen($arParams["ID"]) <= 0)
	{
		$arError[] = array(
			"id" => "error",
			"text" => GetMessage("BPABL_INVALID_WF"));

		$e = new CAdminException($arError);
		ShowError($e->GetString());
		return false;
	}

	$arResult["arWorkflowState"] = CBPStateService::GetWorkflowState($arParams["ID"]);
	$arParams["DOCUMENT_ID"] = $arResult["arWorkflowState"]["DOCUMENT_ID"];
	$arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();
	if (method_exists($arParams["DOCUMENT_ID"][1], "GetUserGroups"))
	{
		$arParams["USER_GROUPS"] = call_user_func_array(
			array($arParams["DOCUMENT_ID"][1], "GetUserGroups"),
			array(null, $arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()));
	}

	if (!is_array($arResult["arWorkflowState"]) || count($arResult["arWorkflowState"]) <= 0)
	{
		$arError[] = array(
			"id" => "error",
			"text" => GetMessage("BPABL_INVALID_WF"));
	}
	else
	{
		$bCanView = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ViewWorkflow,
			$GLOBALS["USER"]->GetID(),
			$arResult["arWorkflowState"]["DOCUMENT_ID"],
			array(
				"WorkflowId" => $arParams["ID"],
				"DocumentStates" => array($arParams["ID"] => $arResult["arWorkflowState"]),
				"UserGroups" => $arParams["USER_GROUPS"]));
		if (!$bCanView)
		{
			$arError[] = array(
				"id" => "access denied",
				"text" => GetMessage("BPABL_NO_PERMS"));
		}
	}

	if (!empty($arError)):
		$e = new CAdminException($arError);
		ShowError($e->GetString());
		return false;
	endif;

	/********************************************************************
					Data
	********************************************************************/
	$arResult["arWorkflowTrack"] = CBPTrackingService::DumpWorkflow($arParams["ID"]);
	/********************************************************************
					/Data
	********************************************************************/

	$this->IncludeComponentTemplate();

	/********************************************************************
					Standart operations
	********************************************************************/
	if($arParams["SET_TITLE"] == "Y")
	{
		$APPLICATION->SetTitle(str_replace("#ID#", $arParams["ID"], GetMessage("BPABL_TITLE")));
	}
	/********************************************************************
					/Standart operations
	********************************************************************/
}
?>