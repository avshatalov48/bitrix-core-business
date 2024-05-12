<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule('bizproc')):
	return false;
endif;

if (!CModule::IncludeModule("webdav")):
	ShowError(GetMessage("W_WEBDAV_IS_NOT_INSTALLED_MSGVER_1"));
	return 0;
endif;
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/webdav/functions.php");

CUtil::InitJSCore(array('window'));
if (!function_exists("BPWSInitParam"))
{
	function BPWSInitParam(&$arParams, $name)
	{
		$arParams[$name] = trim($arParams[$name]);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[$name]);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[mb_strtolower($name)]);
	}
}

global $order, $by;
if (empty($_REQUEST["by"])): 
	$by = "modified";
	$order = "desc";
endif;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
	$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
	$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
	$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"document" => "PAGE_NAME=bizproc_document&ID=#ID#");

	$arResult["back_url"] = urlencode(empty($_REQUEST["back_url"]) ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[mb_strtoupper($URL)."_URL"] = trim($arParams[mb_strtoupper($URL)."_URL"]);
		if (empty($arParams[mb_strtoupper($URL)."_URL"])):
			$arParams[mb_strtoupper($URL)."_URL"] = $APPLICATION->GetCurPage();
		endif;
		$arParams["~".mb_strtoupper($URL)."_URL"] = $arParams[mb_strtoupper($URL)."_URL"];
		$arParams[mb_strtoupper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".mb_strtoupper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGE_ELEMENTS"] = intval(intVal($arParams["PAGE_ELEMENTS"]) > 0 ? $arParams["PAGE_ELEMENTS"] : 50);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["GRID_ID"] = "bizproc_history_".$arParams["DOCUMENT_ID"][2]; 
    $arParams["ACTION"] = (isset($_REQUEST["action"])? mb_strtolower($_REQUEST["action"]) : '');
    $arParams["ACTION"] = (in_array($arParams["ACTION"], array("recover", "delete")) ? $arParams["ACTION"] : '');
    $arResult["NOTIFICATIONS"] = array(
        "recover" => GetMessage("BPADH_RECOVERY_OK"), 
        "delete" => GetMessage("BPADH_DELETE_OK"), 
    );
    if (!empty($_REQUEST["result"]) && array_key_exists($_REQUEST["result"], $arResult["NOTIFICATIONS"])) 
        $arResult["OK_MESSAGE"] = $arResult["NOTIFICATIONS"][$_REQUEST["result"]];
    if (isset($arParams["OBJECT"]) && (! is_object($arParams["OBJECT"])))
        unset($arParams["OBJECT"]);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Main data
********************************************************************/
$arError = array();
if ($arParams["MODULE_ID"] == '')
	$arError[] = array(
		"id" => "empty_module_id",
		"text" => GetMessage("BPATT_NO_MODULE_ID"));
if ($arParams["ENTITY"] == '')
	$arError[] = array(
		"id" => "empty_entity",
		"text" => GetMessage("BPABS_EMPTY_ENTITY"));
if ($arParams["DOCUMENT_TYPE"] == '')
	$arError[] = array(
		"id" => "empty_document_type",
		"text" => GetMessage("BPABS_EMPTY_DOC_TYPE"));
if ($arParams["DOCUMENT_ID"] == '')
	$arError[] = array(
		"id" => "empty_document_id",
		"text" => GetMessage("BPABS_EMPTY_DOC_ID"));

$arParams["DOCUMENT_TYPE"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_TYPE"]);
$arParams["DOCUMENT_ID"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_ID"]);

if (empty($arError))
{
	if (!CBPDocument::CanUserOperateDocument(
		CBPWebDavCanUserOperateOperation::WriteDocument, 
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray()))):
		$arError[] = array(
			"id" => "access_denied",
			"text" => GetMessage("BPADH_NO_PERMS"));
	endif;
}
if (!empty($arError))
{
	$e = new CAdminException($arError);
	ShowError($e->GetString());
	return false;
}
/********************************************************************
				/Main data
********************************************************************/

$arResult["VERSIONS"] = array();
$arResult["GRID_VERSIONS"] = array(); 
$arResult["ERROR_MESSAGE"] = "";

/********************************************************************
				Action
********************************************************************/
if (!empty($arParams['ACTION']) && !empty($_REQUEST["ID"]) && check_bitrix_sessid())
{
	$arError = array();
	$ID = $_REQUEST["ID"];
	switch ($arParams['ACTION'])
	{
		case "delete":
            if ($arParams["MODULE_ID"] == "webdav")
            {
                if (CBPDocument::CanUserOperateDocument(
                    CBPWebDavCanUserOperateOperation::DeleteDocument, 
                    $GLOBALS["USER"]->GetID(),
                    $arParams["DOCUMENT_ID"],
                    array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray()))):
                        CBPHistoryService::Delete($ID, $arParams["DOCUMENT_ID"]);
                else:
                    $arError[] = array(
                        "id" => "access_denied",
                        "text" => GetMessage("BPADH_NO_PERMS"));
                endif;
            } 
            else
            {
                CBPHistoryService::Delete($ID, $arParams["DOCUMENT_ID"]);
            }
			break;
		case "recover":
			if ($arParams["MODULE_ID"] == "webdav" && isset($arParams["OBJECT"]))
			{
				$arParams['OBJECT']->IsDir(array('element_id' => $arParams["DOCUMENT_ID"][2]));
				if (($arParams["OBJECT"]->workflow == 'bizproc' || $arParams["OBJECT"]->workflow == 'bizproc_limited') && 
					$arParams['OBJECT']->arParams['not_found'] == false)
				{
					CBPDocument::AddDocumentToHistory($arParams['DOCUMENT_ID'], $arParams['OBJECT']->arParams["element_name"], $GLOBALS["USER"]->GetID());
					if (method_exists('CIBlockDocumentWebdav', 'TruncateHistory'))
						CIBlockDocumentWebdav::TruncateHistory($arParams['OBJECT']->wfParams['DOCUMENT_TYPE'], $arParams["DOCUMENT_ID"][2]);
				}
			}
			try
			{
				if (!CBPHistoryService::RecoverDocumentFromHistory($ID))
				{
					$arError[] = array(
						"id" => "not recover", 
						"text" => GetMessage("BPADH_RECOVERY_ERROR"));
				}
			}
			catch (Exception $e)
			{
				$arError[] = array(
					"id" => "not recover", 
					"text" => $e->getMessage());
			}
			break;
	}
	if (!empty($arError)):
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	else:
		LocalRedirect($APPLICATION->GetCurPageParam("result=".$arParams["ACTION"], array("action", "ID", "sessid", "result")));
	endif;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$history = new CBPHistoryService();
$db_res = $history->GetHistoryList(
	array(mb_strtoupper($by) => mb_strtoupper($order)),
	array("DOCUMENT_ID" => $arParams["DOCUMENT_ID"]),
	false,
	false,
	array("ID", "DOCUMENT_ID", "NAME", "MODIFIED", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "DOCUMENT", "USER_SECOND_NAME")
);
if ($db_res)
{
	$db_res->NavStart($arParams["PAGE_ELEMENTS"], false);
    $arResult["NAV_RESULT"] = $db_res;
    $arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("BPADH_NAV_TITLE"), $arParams["PAGE_NAVIGATION_TEMPLATE"], true);


    if ($this->__parent)
        $this->__parent->arResult["HISTORY_LENGTH"] = $db_res->NavRecordCount;

	while ($res = $db_res->GetNext())
	{
        if ($arParams['MODULE_ID'] == 'webdav' && isset($arParams['OBJECT']))
        {
			if (isset($res["DOCUMENT"]["PROPERTIES"]['WEBDAV_SIZE']['VALUE']))
				$res['FILE_SIZE'] = CFile::FormatSize($res['DOCUMENT']['PROPERTIES']['WEBDAV_SIZE']['VALUE']);
        }

		$res["USER"] = CUser::FormatName($arParams["NAME_TEMPLATE"], array("NAME" => $res["USER_NAME"], "LAST_NAME" => $res["USER_LAST_NAME"], "SECOND_NAME" => $res["USER_SECOND_NAME"], "LOGIN" => $res["USER_LOGIN"]), true);
		$res["URL"] = array(
			"VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~DOCUMENT_URL"], $res),
			"RECOVER" => $APPLICATION->GetCurPageParam("ID=".$res["ID"]."&action=recover&".bitrix_sessid_get(), array("ID", "action", "sessid", "result")),
			"DELETE" => $APPLICATION->GetCurPageParam("ID=".$res["ID"]."&action=delete&".bitrix_sessid_get(), array("ID", "action", "sessid", "result")));
		foreach ($res["URL"] as $key => $val):
			$res["URL"]["~".$key] = $val;
			$res["URL"][$key] = htmlspecialcharsbx($val);
		endforeach;
		$arResult["VERSIONS"][$res["ID"]] = $res;
        $hintLink = __make_hint($res["DOCUMENT"]["FIELDS"]);
		$arResult["GRID_VERSIONS"][$res["ID"]] = array(
				"id" => $res["ID"],
				"data" => $res,
				"actions" => array(
					array(
						"ICONCLASS" => "bizproc-history-action-recover",
						"TEXT" => GetMessage("BPADH_RECOVERY_DOC"),
						"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($res["URL"]["~RECOVER"])."');"),
					array(
						"ICONCLASS" => "bizproc-history-action-delete",
						"TEXT" => GetMessage("BPADH_DELETE_DOC"),
						"ONCLICK" => 'if (confirm(\''.
							CUtil::JSEscape(GetMessage("BPADH_DELETE_DOC_CONFIRM")).'\')) {jsUtils.Redirect([], \''.CUtil::JSEscape($res["URL"]["~DELETE"]).'\');}')),
				"columns" => array(
                    "NAME" => $res["DOCUMENT"]["FIELDS"]["HINT"].'<a href="'.$res["URL"]["VIEW"].'" '.$hintLink.'>'.$res["NAME"].'</a>',
                    //"NAME" => $res["DOCUMENT"]["FIELDS"]["HINT"].'<a href="'.$res["URL"]["VIEW"].'" title="'.GetMessage("BPADH_VIEW_DOC").'" '.$hintLink.'>'.$res["NAME"].'</a>',
                    //"TAGS" => $res["DOCUMENT"]["FIELDS"]["TAGS"],
                    //"DESCRIPTION" => $res["DOCUMENT"]["FIELDS"]["PREVIEW_TEXT"],
                ), 
				"editable" => false); 
	}
}

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
