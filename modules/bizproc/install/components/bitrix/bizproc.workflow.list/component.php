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
	$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);
	$arParams["CREATE_DEFAULT_TEMPLATE"] = isset($arParams["CREATE_DEFAULT_TEMPLATE"]) ? $arParams["CREATE_DEFAULT_TEMPLATE"] : "Y";
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"edit" => "PAGE_NAME=edit&ID=#ID#&ACTION=#ACTION#");
	
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
/********************************************************************
				/Input params
********************************************************************/

$arError = array();

if (strlen($arParams["MODULE_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_module_id",
		"text" => GetMessage("BPATT_NO_MODULE_ID"));
if (strlen($arParams["ENTITY"]) <= 0)
	$arError[] = array(
		"id" => "empty_entity",
		"text" => GetMessage("BPATT_NO_ENTITY"));
if (strlen($arParams["DOCUMENT_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_document_id",
		"text" => GetMessage("BPATT_NO_DOCUMENT_TYPE"));
$documentType = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_ID"]);
$arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();
if (method_exists($arParams["DOCUMENT_TYPE"][1], "GetUserGroups"))
{
	$arParams["USER_GROUPS"] = call_user_func_array(
		array($arParams["ENTITY"], "GetUserGroups"), 
		array($documentType, $arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()));
}

if (empty($arError))
{
	if (!CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$GLOBALS["USER"]->GetID(),
		$documentType,
		array("UserGroups" => $arParams["USER_GROUPS"])
	))
	{
		$arError[] = array(
			"id" => "access_denied",
			"text" => GetMessage("BPATT_NO_PERMS"));
	}
}
if (!empty($arError))
{
	$e = new CAdminException($arError);
	ShowError($e->GetString());
	return false;
}
elseif (!empty($_REQUEST['action']) && !check_bitrix_sessid())
{
}
elseif ($_REQUEST['action'] == 'create_default')
{
	CBPDocument::AddDefaultWorkflowTemplates($documentType);
	LocalRedirect($APPLICATION->GetCurPageParam("", array("action", "sessid")));
}
elseif ($_REQUEST['ID'] <= 0)
{
}
elseif ($_REQUEST['action'] == 'delete')
{
	$arErrorsTmp = array();
	CBPDocument::DeleteWorkflowTemplate($_REQUEST['ID'], $documentType, $arErrorsTmp);
	if (empty($arErrorsTmp))
	{	
		$url = (!empty($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : $APPLICATION->GetCurPageParam("", array("action", "sessid", "ID")));
		LocalRedirect($url);
	}
	elseif (!empty($arErrorsTmp))
	{
		foreach ($arErrorsTmp as $e)
			$arError[] = array(
				"id" => "delete_error",
				"text" => $e["message"]);
		$e = new CAdminException($arError);
		ShowError($e->GetString());
	}
}
elseif (strpos($_REQUEST['action'], "autoload_") !== false)
{
	$db_res = CBPWorkflowTemplateLoader::GetList(
		array('ID' => 'DESC'),
		array("DOCUMENT_TYPE" => $documentType, "ID" => $_REQUEST["ID"]),
		false,
		false,
		array("ID", "NAME", "AUTO_EXECUTE"));
	if ($db_res && $res = $db_res-> Fetch())
	{
		$arFields = array("AUTO_EXECUTE" => $res["AUTO_EXECUTE"]);
		$tmp = false; 
		if (strpos($_REQUEST['action'], "create") !== false)
			$tmp = CBPDocumentEventType::Create;
		elseif (strpos($_REQUEST['action'], "edit") !== false)
			$tmp = CBPDocumentEventType::Edit;
		elseif (strpos($_REQUEST['action'], "delete") !== false)
			$tmp = CBPDocumentEventType::Delete;

		if ($tmp != false)
		{
			if (strpos($_REQUEST['action'], "_n") !== false)
				$arFields["AUTO_EXECUTE"] = ((($arFields["AUTO_EXECUTE"] & $tmp) != 0) ? $arFields["AUTO_EXECUTE"] ^ $tmp : $arFields["AUTO_EXECUTE"]);
			else 
				$arFields["AUTO_EXECUTE"] = ((($arFields["AUTO_EXECUTE"] & $tmp) == 0) ? $arFields["AUTO_EXECUTE"] ^ $tmp : $arFields["AUTO_EXECUTE"]);
		}
		
		if ($arFields["AUTO_EXECUTE"] != $res["AUTO_EXECUTE"])
			CBPWorkflowTemplateLoader::Update($_REQUEST["ID"], $arFields); 
	}
	$url = (!empty($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : $APPLICATION->GetCurPageParam("", array("action", "sessid", "ID")));
	LocalRedirect($url);
}
/********************************************************************
				Default falues
********************************************************************/
$arResult["NAV_STRING"] = "";
$arResult["NAV_RESULT"] = "";
$arResult["TEMPLATES"] = array();
$arResult["GRID_TEMPLATES"] = array();
/********************************************************************
				/Default falues
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["GRID_ID"] = "bizproc_wflist_".$arParams["MODULE_ID"];

$gridOptions = new CGridOptions($arResult["GRID_ID"]);
$gridSort = $gridOptions->GetSorting(array("sort" => array("NAME" => "ASC")));

$db_res = CBPWorkflowTemplateLoader::GetList(
	$gridSort["sort"],
	array(
		"DOCUMENT_TYPE" => $documentType,
		'!AUTO_EXECUTE' => CBPDocumentEventType::Automation
	),
	false,
	false,
	array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "AUTO_EXECUTE", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "ACTIVE", "USER_SECOND_NAME"));
if ($db_res)
{
	$db_res->NavStart(25, false);
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("BPATT_NAV"), "");

	$adminPage = $APPLICATION->GetCurPageParam(
		'back_url='.urlencode($back_url).'&action=delete&'.bitrix_sessid_get(),
		array('back_url', 'action', 'ID', 'sessid'));

	while ($res = $db_res->GetNext())
	{
		$res["URL"] = array(
			"EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~EDIT_URL"], 
							array("ID" => $res["ID"], "MODULE_ID" => $arParams["MODULE_ID"], 
								"ENTITY" => $arParams["ENTITY"], "DOCUMENT_ID" => $arParams["DOCUMENT_ID"])),
			"DELETE" => $adminPage."&ID=".$res["ID"]);
		if (isset($arParams["~EDIT_VARS_URL"]) && strlen($arParams["~EDIT_VARS_URL"]) > 0)
			$res["URL"]["VARS"] = CComponentEngine::MakePathFromTemplate($arParams["~EDIT_VARS_URL"], 
							array("ID" => $res["ID"], "MODULE_ID" => $arParams["MODULE_ID"], 
								"ENTITY" => $arParams["ENTITY"], "DOCUMENT_ID" => $arParams["DOCUMENT_ID"]));
		if (isset($arParams["~EDIT_CONSTANTS_URL"]) && strlen($arParams["~EDIT_CONSTANTS_URL"]) > 0)
			$res["URL"]["CONSTANTS"] = CComponentEngine::MakePathFromTemplate($arParams["~EDIT_CONSTANTS_URL"],
				array("ID" => $res["ID"], "MODULE_ID" => $arParams["MODULE_ID"],
					"ENTITY" => $arParams["ENTITY"], "DOCUMENT_ID" => $arParams["DOCUMENT_ID"]));

		foreach ($res["URL"] as $key => $val):
			$res["URL"]["~".$key] = $val;
			$res["URL"][$key] = htmlspecialcharsbx($val);
		endforeach;
		$res["USER"] = CUser::FormatName($arParams["NAME_TEMPLATE"], array("NAME" => $res["~USER_NAME"], "LAST_NAME" => $res["~USER_LAST_NAME"], "SECOND_NAME" => $res["~USER_SECOND_NAME"], "LOGIN" => $res["~USER_LOGIN"]), true);

		$autoExecuteText = array();
		if ($res["AUTO_EXECUTE"] == CBPDocumentEventType::None)
			$autoExecuteText[] = GetMessage("BPATT_AE_NONE");
		if (($res["AUTO_EXECUTE"] & CBPDocumentEventType::Create) != 0)
			$autoExecuteText[] = GetMessage("BPATT_AE_CREATE");
		if (($res["AUTO_EXECUTE"] & CBPDocumentEventType::Edit) != 0)
			$autoExecuteText[] = GetMessage("BPATT_AE_EDIT");
		if (($res["AUTO_EXECUTE"] & CBPDocumentEventType::Delete) != 0)
			$autoExecuteText[] = GetMessage("BPATT_AE_DELETE");
		$res["AUTO_EXECUTE"] = $autoExecuteText;
		$arResult["TEMPLATES"][$res["ID"]] = $res;

		$b = (($res["~AUTO_EXECUTE"] & CBPDocumentEventType::Create) != 0);
		$url = $APPLICATION->GetCurPageParam('ID='.$res["ID"].'&action=autoload_create'.($b ? '_n' : '').'&'.bitrix_sessid_get(), 
			array('back_url', 'action', 'ID', 'sessid'));
		$b1 = (($res["~AUTO_EXECUTE"] & CBPDocumentEventType::Edit) != 0);
		$url1 = $APPLICATION->GetCurPageParam('ID='.$res["ID"].'&action=autoload_edit'.($b1 ? '_n' : '').'&'.bitrix_sessid_get(), 
			array('back_url', 'action', 'ID', 'sessid'));

		$arActions = array(
			array(
				"ICONCLASS" => "",
				"TITLE" => ($b ? GetMessage("BPATT_DO_N_LOAD_CREATE_TITLE") : GetMessage("BPATT_DO_LOAD_CREATE_TITLE")),
				"TEXT" => ($b ? GetMessage("BPATT_DO_N_LOAD_CREATE") : GetMessage("BPATT_DO_LOAD_CREATE")),
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."');"), 
			array(
				"ICONCLASS" => "",
				"TITLE" => ($b1 ? GetMessage("BPATT_DO_N_LOAD_EDIT_TITLE") : GetMessage("BPATT_DO_LOAD_EDIT_TITLE")),
				"TEXT" => ($b1 ? GetMessage("BPATT_DO_N_LOAD_EDIT") : GetMessage("BPATT_DO_LOAD_EDIT")),
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url1)."');")); 
		$arActions[] = array("SEPARATOR" => true);
		if (isset($res["URL"]["VARS"]))
		{
			$arActions[] = array(
				"ICONCLASS" => "edit",
				"TITLE" => GetMessage("BPATT_DO_EDIT_VARS"),
				"TEXT" => GetMessage("BPATT_DO_EDIT_VARS1"),
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($res["URL"]["~VARS"])."');", 
				"DEFAULT" => false);
		}
		if (isset($res["URL"]["CONSTANTS"]))
		{
			$arActions[] = array(
				"ICONCLASS" => "edit",
				"TITLE" => GetMessage("BPATT_DO_EDIT_CONSTANTS"),
				"TEXT" => GetMessage("BPATT_DO_EDIT_CONSTANTS1"),
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($res["URL"]["~CONSTANTS"])."');",
				"DEFAULT" => false);
		}
		if (IsModuleInstalled("bizprocdesigner"))
		{
			$arActions[] = array(
				"ICONCLASS" => "edit",
				"TITLE" => GetMessage("BPATT_DO_EDIT1"),
				"TEXT" => GetMessage("BPATT_DO_EDIT1"),
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($res["URL"]["~EDIT"])."');", 
				"DEFAULT" => true);
		}
		$arActions[] = array(
			"ICONCLASS" => "delete",
			"TITLE" => GetMessage("BPATT_DO_DELETE1"),
			"TEXT" => GetMessage("BPATT_DO_DELETE1"),
			"ONCLICK" => "if(confirm('".CUtil::JSEscape(GetMessage("BPATT_DO_DELETE1_CONFIRM"))."')){jsUtils.Redirect([], '".CUtil::JSEscape($res["URL"]["~DELETE"])."')};");

		$arResult["GRID_TEMPLATES"][$res["ID"]] = array(
				"id" => $res["ID"], 
				"data" => $res, 
				"actions" => $arActions, 
				"columns" => array(
					"NAME" => (IsModuleInstalled("bizprocdesigner") ? '<a href="'.$res["URL"]["EDIT"].'">'.$res["NAME"].'</a>' : $res["NAME"]), 
					"AUTO_EXECUTE" => implode("<br />", $res["AUTO_EXECUTE"])), 
				"editable" => false);

		$arResult['SORT'] = $gridSort["sort"];
	}
}
$this->IncludeComponentTemplate();

/********************************************************************
				Standart operations
********************************************************************/
if($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("BPATT_TITLE"));
}
/********************************************************************
				/Standart operations
********************************************************************/
?>