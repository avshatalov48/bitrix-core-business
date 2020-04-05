<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
elseif (intval(COption::GetOptionString("forum", "UsePMVersion", "2")) <= 0):
	ShowError(GetMessage("F_NO_PM"));
	CHTTP::SetStatus("404 Not Found");
	return 0;
endif;

if(!function_exists("GetUserName"))
{
	function GetUserName($USER_ID, $sNameTemplate = "")
	{
		$sNameTemplate = str_replace(array("#NOBR#","#/NOBR#"), "", (!empty($sNameTemplate) ? $sNameTemplate : CSite::GetDefaultNameFormat()));
		if (intval($USER_ID) <= 0)
		{
			$db_res = CUser::GetByLogin($USER_ID);
			$ar_res = $db_res->Fetch();
			$USER_ID = $ar_res["ID"];
		}
		return CForumUser::GetFormattedNameByUserID($USER_ID, $sNameTemplate);
	}
}

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["pm_version"] = intVal(COption::GetOptionString("forum", "UsePMVersion", "2"));
	
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? 1 : $arParams["FID"]);
	if ($arParams["pm_version"] == 2 && ($arParams["FID"] > 1 && $arParams["FID"] < 4))
		$arParams["FID"] = 3;
	$arParams["UID"] = intVal($USER->GetId());
/***************** Sorting *****************************************/
	InitSorting($GLOBALS["APPLICATION"]->GetCurPage()."?PAGE_NAME=pm_list&FID=".$arParams["FID"]);
	global $by, $order;
	if (empty($by))
	{
		$by = "post_date";
		$order = "desc";
	}
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_read" => "PAGE_NAME=pm_read&FID=#FID#&MID=#MID#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&mode=#mode#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"pm_folder" => "PAGE_NAME=pm_folder");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		if (!empty($by) && !in_array($URL, array("profile_view", "pm_read", "pm_edit")))
		{
			$arParams["~URL_TEMPLATES_".strToUpper($URL)] = ForumAddPageParams($arParams["URL_TEMPLATES_".strToUpper($URL)], 
				array("by" => $by, "order" => $order), false, false);
		}
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["PM_PER_PAGE"] = intVal($arParams["PM_PER_PAGE"] > 0 ? $arParams["PM_PER_PAGE"] : 20);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "",
		(!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetDefaultNameFormat()));
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
if (!empty($_REQUEST["result"])):
	switch (strToLower($_REQUEST["result"]))
	{
		case "delete":
			$arResult["OK_MESSAGE"] = GetMessage("PM_OK_ALL_DELETE");
			break;
		case "move":
			$arResult["OK_MESSAGE"] = GetMessage("PM_OK_ALL_MOVE");
			break;
		case "copy":
			$arResult["OK_MESSAGE"] = GetMessage("PM_OK_ALL_COPY");
			break;
		case "no_mid":
			$arResult["ERROR_MESSAGE"] = GetMessage("PM_ERR_NO_MID");
			break;
		case "no_perm":
			$arResult["ERROR_MESSAGE"] = GetMessage("PM_ERR_NO_PERM");
			break;
	}
endif;
$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
$arResult["count"] = round($arResult["count"]*100);
$arResult["sessid"] = bitrix_sessid_post();
$arResult["FID"] = $arParams["FID"];
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], array("FID" => $arParams["FID"]));
$arResult["version"] = $arParams["pm_version"];
$arResult["MESSAGE"] = array();
$arResult["SystemFolder"] = FORUM_SystemFolder;
$arResult["UserFolder"] = array();

$message = (is_array($_REQUEST["message"]) && !empty($_REQUEST["message"]) ? $_REQUEST["message"] : array());
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Action
********************************************************************/
$arResult["action"] = strToLower($_REQUEST["action"]);
if (!empty($arResult["action"]))
{
	$arError = array();
	$strOK = "";
	$APPLICATION->ResetException();
	$APPLICATION->ThrowException(" ");
	$folder_id = 0;
	if (!check_bitrix_sessid()):
		$arError[] = array("id" => "BAD_SESSID", "text" => GetMessage("F_ERR_SESS_FINISH"));
	elseif (empty($message)):
		$arError[] = array("id" => "BAD_DATA", "text" => GetMessage("PM_ERR_NO_DATA"));
	elseif ($arResult["action"] == "delete"):
		$folder_id = 4;
		foreach ($message as $MID):
			if (!CForumPrivateMessage::CheckPermissions($MID))
				$arError[] = array("id" => "BAD_PERMISSION_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE_NO_PERM")));
			elseif(!CForumPrivateMessage::Delete($MID, array("FOLDER_ID"=>4)))
				$arError[] = array("id" => "BAD_DELETE_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE")));
			else 
				$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_DELETE"));
		endforeach;
	elseif (($arResult["action"] == "copy" || $arResult["action"] == "move") && intVal($_REQUEST["folder_id"]) <= 0):
		$arError[] = array("id" => "BAD_DATA", "text" => GetMessage("PM_ERR_MOVE_NO_FOLDER"));
	elseif ($arResult["action"] == "copy" || $arResult["action"] == "move"):
		$folder_id = intVal($_REQUEST["folder_id"]);
		foreach ($message as $MID) 
		{
			$arrVars = array(
				"FOLDER_ID" => intVal($folder_id),
				"USER_ID" => $USER->GetId());
			if ($folder_id == 4 || $arResult["action"] != "move")
				$arrVars["IS_READ"] = "Y";
			if (!CForumPrivateMessage::CheckPermissions($MID)):
				$arError[] = array("id" => "BAD_PERMISSION_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_ERR_MOVE_NO_PERM")));
			elseif ($arResult["action"] == "move"):
				if (!CForumPrivateMessage::Update($MID, $arrVars)):
					$err = $APPLICATION->GetException();
					$arError[] = array("id" => "BAD_MOVE_".$MID, "text" => $err->GetString());
				else:
					$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_MOVE"))."\n";
				endif;
			else:
				if (!CForumPrivateMessage::Copy($MID, $arrVars)):
					$err = $APPLICATION->GetException();
					$arError[] = array("id" => "BAD_MOVE_".$MID, "text" => $err->GetString());
				else:
					$strOK .= str_replace("#MID#", $MID, GetMessage("PM_OK_COPY"))."\n";
				endif;
			endif;
		}
	endif;
	BXClearCache(true, "/bitrix/forum/user/".intVal($USER->GetID())."/");
	$arComponentPath = array("bitrix:forum");
	foreach ($arComponentPath as $path)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($path);
		$arComponentDescription = CComponentUtil::GetComponentDescr($path);
		if (strLen($componentRelativePath) <= 0 || !is_array($arComponentDescription)):
			continue;
		elseif (!array_key_exists("CACHE_PATH", $arComponentDescription)):
			continue;
		endif;
		$path = str_replace("//", "/", $componentRelativePath."/user".$USER->GetID());
		if ($arComponentDescription["CACHE_PATH"] == "Y")
			$path = "/".SITE_ID.$path;
		if (!empty($path))
			BXClearCache(true, $path);
	}
	if (empty($arError))
	{
		LocalRedirect(ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"], 
			array("FID" => $arParams["FID"])), array("result" => $arResult["action"])));
	}
	else 
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] .= $err->GetString();
		$arResult["OK_MESSAGE"] .= $strOK;
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["StatusUser"] = "AUTHOR";
$arResult["InputOutput"] = "AUTHOR_ID";
$SortingField = "AUTHOR_NAME";
if ($arParams["FID"] <= 1)
{
	$arResult["StatusUser"] = "SENDER";
	$arResult["InputOutput"] = "AUTHOR_ID";
	$SortingField = "AUTHOR_NAME";
}
elseif (1 < $arParams["FID"] && $arParams["FID"] <= 3)
{
	$arResult["StatusUser"] = "RECIPIENT";
	$arResult["InputOutput"] = "RECIPIENT_ID";
	$SortingField = "RECIPIENT_NAME";
}
$arResult["SortingEx"]["POST_SUBJ"] = SortingEx("post_subj");
$arResult["SortingEx"]["AUTHOR_NAME"] = SortingEx(strToLower($SortingField));
$arResult["SortingEx"]["POST_DATE"] = SortingEx("post_date");

$arFilter = array("USER_ID"=>$arParams["UID"], "FOLDER_ID"=>$arParams["FID"]);
if ($arParams["FID"] == 2) //If this is outbox folder
	$arFilter = array("OWNER_ID" => $arParams["UID"]);

$dbrMessages = CForumPrivateMessage::GetListEx(array($by => $order), $arFilter,
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["PM_PER_PAGE"],
		"bShowAll" => false,
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]));
$dbrMessages->NavStart($arParams["PM_PER_PAGE"]);
$dbrMessages->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$arResult["NAV_RESULT"] = $dbrMessages;
$arResult["NAV_STRING"] = $dbrMessages->GetPageNavStringEx($navComponentObject, GetMessage("PM_TITLE_PAGES"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
if($dbrMessages && $arMsg = $dbrMessages->GetNext())
{
	do
	{
		$arMsg["POST_SUBJ"] = wordwrap($arMsg["POST_SUBJ"], 100, " ", 1);
		$arMsg["~SHOW_NAME"] = $arMsg["~".$SortingField];
		$arMsg["SHOW_NAME"] = $arMsg[$SortingField];
		$arMsg["URL"] = array(
			"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_READ"], 
				array("FID" => $arParams["FID"], "MID" => $arMsg["ID"])), 
			"MESSAGE_EDIT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], 
				array("FID" => $arParams["FID"], "mode" => "new", "MID" => 0, "UID" => $arMsg[$arResult["InputOutput"]])), 
			"RECIPIENT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
				array("UID" => $arMsg["RECIPIENT_ID"])), 
			"SENDER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
				array("UID" => $arMsg["AUTHOR_ID"])));
				
		$arMsg["pm_read"] = $arMsg["URL"]["MESSAGE"];
		$arMsg["pm_edit"] = $arMsg["URL"]["MESSAGE_EDIT"];
		$arMsg["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
			array("UID" => $arMsg[$arResult["InputOutput"]]));
		$arMsg["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arMsg["POST_DATE"], CSite::GetDateFormat()));
		$arMsg["checked"] = "";
		if (in_array($arMsg["ID"], $message))
			$arMsg["checked"] = " checked ";
		$arResult["MESSAGE"][$arMsg["ID"]] = $arMsg;
	}while($arMsg = $dbrMessages->GetNext());
}
/************** Folders ********************************************/
$resFolder = CForumPMFolder::GetList(array(), array("USER_ID" => $USER->GetID()));
if ($resFolder && $resF = $resFolder->GetNext())
{
	do
	{
		$arResult["UserFolder"][intVal($resF["ID"])] = $resF;
	}
	while ($resF = $resFolder->GetNext());
}
if ($arParams["FID"] > 4 && empty($arResult["UserFolder"][$arParams["FID"]]))
{
	ShowError(GetMessage("PM_FOLDER_IS_NOT_EXISTS"));
	return false;
}
$arResult["FolderName"] = ($arParams["FID"] > 4) ? $arResult["UserFolder"][$arParams["FID"]]["TITLE"] : GetMessage("PM_FOLDER_ID_".$arParams["FID"]);
/*******************************************************************/
$arResult["pm_folder"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_FOLDER"], array());
if ($arParams["FID"] > 4)
{
	$title = $arResult["UserFolder"][$arParams["FID"]]["TITLE"];
}
else 
{
	$title = GetMessage("PM_FOLDER_ID_".$arParams["FID"]);
}

// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel(0, 0, false);
// GetMessage("PM_FOLDER_ID_1");
// GetMessage("PM_FOLDER_ID_2");
// GetMessage("PM_FOLDER_ID_3");
// GetMessage("PM_FOLDER_ID_4");
/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NAV"), CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_FOLDER"], array()));
	$APPLICATION->AddChainItem($title);
}
/*******************************************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(str_replace("#TITLE#", $title, GetMessage("PM_TITLE")));
?>