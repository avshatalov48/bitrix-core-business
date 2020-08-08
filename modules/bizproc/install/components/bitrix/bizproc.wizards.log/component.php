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

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arResult["BackUrl"] = urlencode(empty($_REQUEST["back_url"]) ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

$arResult["PATH_TO_INDEX"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
$arResult["PATH_TO_LIST"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"]));
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
		$arResult["Block"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_WRONG_IBLOCK").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$db = CIBlockElement::GetList(
		array(),
		array("ID" => $arParams["BP_ID"], "IBLOCK_ID" => $arParams["BLOCK_ID"], "CHECK_BP_VIRTUAL_PERMISSIONS" => "read"),
		false,
		false,
		array("ID", "NAME", "IBLOCK_ID", "CREATED_BY")
	);
	if ($ar = $db->GetNext())
		$arResult["BP"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WLC_WRONG_BP").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["GRID_ID"] = "bizproc_CBPVirtualDocument1_".$arParams["BLOCK_ID"];

	$gridOptions = new CGridOptions($arResult["GRID_ID"]);
	$gridColumns = $gridOptions->GetVisibleColumns();
	$gridSort = $gridOptions->GetSorting(array("sort"=>array("ID" => "desc")));

	$arResult["SORT"] = $gridSort["sort"];
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
			"name" => GetMessage("CBBWL_C_ADMIN_MODE"),
			"type" => "checkbox",
		),
	);

	$documentId = array("bizproc", "CBPVirtualDocument",$arResult["BP"]["ID"]);
	$arDocumentStates = CBPDocument::GetDocumentStates($documentType, $documentId);

	$documentStateId = "";
	foreach ($arDocumentStates as $arDocumentState)
	{
		$documentStateId = $arDocumentState["ID"];
		break;
	}

	$arResult["AdminMode"] = false;

	$arFilter = array("WORKFLOW_ID" => $documentStateId);
	$gridFilter = $gridOptions->GetFilter($arResult["FILTER"]);
	foreach ($gridFilter as $key => $value)
	{
		if ($key == "ADMIN_MODE")
		{
			$arResult["AdminMode"] = ($value == "Y" ? true : false);
			continue;
		}

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
		/*else
		{
			if ($arTrack["TYPE"] == CBPTrackingType::CloseActivity)
			{
				$level--;
				$prefix = str_repeat("&nbsp;&nbsp;", $level > 0 ? $level : 0);
			}
			elseif ($arTrack["TYPE"] == CBPTrackingType::ExecuteActivity)
			{
				$prefix = str_repeat("&nbsp;&nbsp;", $level > 0 ? $level : 0);
				$level++;
			}
			else
			{
				$prefix = str_repeat("&nbsp;&nbsp;", $level > 0 ? $level : 0);
			}
		}*/

		$date = $arTrack["MODIFIED"];

		if ($arResult["AdminMode"])
			$name = ($arTrack["ACTION_TITLE"] <> '' ? $prefix.$arTrack["ACTION_TITLE"]."<br/>".$prefix."(".$arTrack["ACTION_NAME"].")" : $prefix.$arTrack["ACTION_NAME"]);
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
				$status = GetMessage("BPABL_RES_6");
		}

		$note = CBPTrackingService::parseStringParameter($arTrack["ACTION_NOTE"], $documentType);

		$modified_by = "";
		if (intval($arTrack["MODIFIED_BY"]) > 0)
		{
			$dbUserTmp = CUser::GetByID($arTrack["MODIFIED_BY"]);
			$arUserTmp = $dbUserTmp->GetNext();
			$modified_by = CUser::FormatName($arParams["NAME_TEMPLATE"], $arUserTmp, true);
			$modified_by .= " [".$arTrack["MODIFIED_BY"]."]";
		}

		$aCols = array("date" => $date, "name" => $name, "type" => $type, "status" => $status, "result" => $result, "note" => $note, "modified_by" => $modified_by);
		$aActions = array();

		$arResult["RECORDS"][] = array("data" => $arTrack, "actions" => $aActions, "columns" => $aCols, "editable" => false);
	}
}

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

$this->IncludeComponentTemplate();