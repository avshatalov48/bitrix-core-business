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
// *****************************************************************************************
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
	$arParams["version"] = intval(COption::GetOptionString("forum", "UsePMVersion", "2"));
	$arParams["FID"] = intval(intVal($arParams["FID"]) > 0 ? $arParams["FID"] : $_REQUEST["FID"]);
	if ($arParams["version"] == 2 && $arParams["FID"] == 2)
		$arParams["FID"] = 3;
	$arParams["MID"] = intval(intVal($arParams["MID"]) > 0 ? $arParams["MID"] : $_REQUEST["MID"]);
	$arParams["UID"] = intval($USER->GetID());
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
		"pm_folder" => "PAGE_NAME=pm_folder",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", "MID", "action", "mode", "sessid", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		if (!empty($by) && !in_array($URL, array("profile_view", "pm_read", "pm_edit")))
		{
			$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = ForumAddPageParams($arParams["URL_TEMPLATES_".mb_strtoupper($URL)],
				array("by" => $by, "order" => $order), false, false);
		}

		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "",
		(!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetDefaultNameFormat()));
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$result = isset($_REQUEST["result"]) ? mb_strtolower($_REQUEST["result"]) : null;

	$arResult["ERROR_MESSAGE"] = "";
	$arResult["OK_MESSAGE"] = "";
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate(
		$arParams["URL_TEMPLATES_PM_READ"],
		array("FID" => $arParams["FID"], "MID" => $arParams["MID"]));
	$arResult["MESSAGE"] = array();
	$arResult["MESSAGE_PREV"] = array();
	$arResult["MESSAGE_NEXT"] = array();
	$parser = new forumTextParser(LANGUAGE_ID);
	$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
	$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

/********************************************************************
				/Default values
********************************************************************/
if ($arParams["MID"] <= 0)
{
	LocalRedirect(ForumAddPageParams(
		CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_PM_LIST"],
			array("FID" => $arParams["FID"])),
		array("result" => "no_mid")));
}
$db_res = CForumPrivateMessage::GetListEx(
	array(),
	array("ID" => $arParams["MID"]),
	false,
	0,
	array("sNameTemplate" => $arParams["NAME_TEMPLATE"]));
if(!($db_res && ($res = $db_res->GetNext())))
{
	LocalRedirect(
		ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_PM_LIST"],
				array("FID" => $arParams["FID"])),
			array("result" => "no_mid")));
}
elseif (!CForumPrivateMessage::CheckPermissions($arParams["MID"]))
{
	LocalRedirect(
		ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_PM_LIST"],
				array("FID" => $arParams["FID"])),
			array("result" => "no_perm")));
	die();
}
$arParams["FID"] = ($arParams["FID"] != 2 ? intval($res["FOLDER_ID"]) : $arParams["FID"]);
$arResult["MESSAGE"] = $res;
/********************************************************************
				Action
********************************************************************/
if($res["IS_READ"] != "Y" && $arParams["FID"] != 2)
{
	CForumPrivateMessage::MakeRead($arParams["MID"]);
	BXClearCache(true, "/bitrix/forum/user/".$USER->GetId()."/");
	$arComponentPath = array("bitrix:forum");
	foreach ($arComponentPath as $path)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($path);
		$arComponentDescription = CComponentUtil::GetComponentDescr($path);
		if ($componentRelativePath == '' || !is_array($arComponentDescription)):
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
}
if (!empty($_REQUEST["action"]))
{
	$action = mb_strtolower($_REQUEST["action"]);
	$arError = array();
	$arOK = array();
	$APPLICATION->ResetException();
	$arNotification = array();
	$message = array($arParams["MID"]);

	$next = array("ID" => $arParams["MID"]);
	if ($action != "send_notification")
	{
		$arFilter = array(
			"USER_ID"=>$arParams["UID"],
			"FOLDER_ID"=>$arParams["FID"]);
		if ($arParams["FID"] == 2) //If this is outbox folder
			$arFilter = array("OWNER_ID" => $arParams["UID"]);
		$db_res = CForumPrivateMessage::GetListEx(
			array($by=>$order),
			$arFilter,
			false,
			0,
			array("sNameTemplate" => $arParams["NAME_TEMPLATE"]));
		if($db_res && ($res = $db_res->Fetch()))
		{
			$bFound = false;
			do
			{
				if ($bFound)
				{
					$next = $res;
					break;
				}
				if ($res["ID"] == $arParams["MID"])
					$bFound = true;

			}while ($res = $db_res->Fetch());
		}
	}

	if (!check_bitrix_sessid()):
		$arError[] = array("id" => "bad_sessid", "text" => GetMessage("F_ERR_SESS_FINISH"));
	elseif (!(is_array($message) && !empty($message))):
		$arError[] = array("id" => "bad_data", "text" => GetMessage("PM_ERR_NO_DATA"));
	elseif ($action == "edit"):
		$arResult["pm_edit"] = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_PM_EDIT"],
			array(
				"FID" => $arParams["FID"],
				"mode" => "edit",
				"MID" => $arParams["MID"],
				"UID" => $arResult["MESSAGE"]["RECIPIENT_ID"]));
		LocalRedirect($arResult["pm_edit"]);
	elseif ($action == "reply"):
		$arResult["pm_reply"] = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_PM_EDIT"],
			array(
				"FID" => $arParams["FID"],
				"mode" => "reply",
				"MID" => $arParams["MID"],
				"UID" => $arResult["MESSAGE"]["AUTHOR_ID"]));
		LocalRedirect($arResult["pm_reply"]);
	elseif ($action == "delete"):
		foreach ($message as $MID)
		{
			if (!CForumPrivateMessage::CheckPermissions($MID)):
				$arError[] = array("id" => "bad_permission_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE_NO_PERM")));
			elseif(!CForumPrivateMessage::Delete($MID, array("FOLDER_ID"=>4))):
				$arError[] = array("id" => "not_delete_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_ERR_DELETE")));
			else:
				$arOk[] = array("id" => "delete_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_OK_DELETE")));
			endif;
		}
	elseif (($action == "copy" || $action == "move") && intval($_REQUEST["folder_id"]) <= 0):
		$arError[] = array("id" => "empty_folder_id_", "text" => GetMessage("PM_ERR_MOVE_NO_FOLDER"));
	elseif ($action == "copy" || $action == "move"):
		$folder_id = intval($_REQUEST["folder_id"]);
		$arrVars = array(
			"FOLDER_ID" => intval($folder_id),
			"USER_ID" => $USER->GetId(),
			"IS_READ" => "Y");
		foreach ($message as $MID)
		{
			if (!CForumPrivateMessage::CheckPermissions($MID)):
				$arError[] = array("id" => "bad_permission_".$MID, "text" => str_replace("#MID#", intval($MID), GetMessage("PM_ERR_MOVE_NO_PERM")));
			elseif (($action == "move" && !CForumPrivateMessage::Update($MID, $arrVars)) ||
				($action == "copy" && !CForumPrivateMessage::Copy($MID, $arrVars))):
				$err = $APPLICATION->GetException();
				if ($err):
					$arError[] = array("id" => "bad_".$action."_".$MID, "text" => $err->GetString());
				endif;
			else:
				$arOk[] = array("id" => $action."_".$MID, "text" => str_replace("#MID#", $MID, GetMessage("PM_OK_MOVE")));
			endif;
		}
	elseif ($action == "send_notification" && $arParams["version"] == 2 && $arResult["MESSAGE"]["REQUEST_IS_READ"] == "Y"):
		$arNotification["POST_SUBJ"] = GetMessage("SYSTEM_POST_SUBJ");
		$arNotification["POST_MESSAGE"] = GetMessage("SYSTEM_POST_MESSAGE");
		$arNotification["FIELDS"] = array(
				"USER_NAME" => $arResult["MESSAGE"]["~RECIPIENT_NAME"],
				"USER_ID" => $arResult["MESSAGE"]["RECIPIENT_ID"],
				"USER_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
					array("UID" => $arResult["MESSAGE"]["RECIPIENT_ID"])),
				"SUBJECT" => $arResult["MESSAGE"]["~POST_SUBJ"],
				"MESSAGE" => $arResult["MESSAGE"]["~POST_MESSAGE"],
				"MESSAGE_DATE" => $arResult["MESSAGE"]["POST_DATE"],
				"MESSAGE_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_READ"],
					array("FID" => "1", "MID" => $arResult["MESSAGE"]["ID"])),
				"SERVER_NAME" => SITE_SERVER_NAME);
		foreach ($arNotification["FIELDS"] as $key => $val)
			$arNotification["POST_MESSAGE"] = str_replace("#".$key."#", $val, $arNotification["POST_MESSAGE"]);

		$arFields = array(
			"AUTHOR_ID" => $USER->GetID(),
			"USER_ID" => $arResult["MESSAGE"]["AUTHOR_ID"],
			"POST_SUBJ" => $arNotification["POST_SUBJ"],
			"POST_MESSAGE" => $arNotification["POST_MESSAGE"],
			"USE_SMILES" => "Y");
		if($newMID = CForumPrivateMessage::Send($arFields))
		{
			BXClearCache(true, "/bitrix/forum/user/".$arResult["MESSAGE"]["AUTHOR_ID"]."/");
			$arComponentPath = array("bitris:forum");
			foreach ($arComponentPath as $path)
			{
				$componentRelativePath = CComponentEngine::MakeComponentPath($path);
				$arComponentDescription = CComponentUtil::GetComponentDescr($path);
				if ($componentRelativePath == '' || !is_array($arComponentDescription)):
					continue;
				elseif (!array_key_exists("CACHE_PATH", $arComponentDescription)):
					continue;
				endif;
				$path = str_replace("//", "/", $componentRelativePath."/user".$arResult["MESSAGE"]["AUTHOR_ID"]);
				if ($arComponentDescription["CACHE_PATH"] == "Y")
					$path = "/".SITE_ID.$path;
				if (!empty($path))
					BXClearCache(true, $path);
			}
			if (!empty($arResult["MESSAGE"]["AUTHOR_EMAIL"]))
			{
				$event = new CEvent;
				$arSiteInfo = $event->GetSiteFieldsArray(SITE_ID);
				$arFields = Array(
					"FROM_NAME" => $arResult["MESSAGE"]["~RECIPIENT_NAME"],
					"FROM_USER_ID" => $USER->GetId(),
					"FROM_EMAIL" => $arSiteInfo["DEFAULT_EMAIL_FROM"],
					"TO_NAME" => $arResult["MESSAGE"]["~AUTHOR_NAME"],
					"TO_USER_ID" => $arResult["MESSAGE"]["AUTHOR_ID"],
					"TO_EMAIL" => $arResult["MESSAGE"]["AUTHOR_EMAIL"],
					"SUBJECT" => $arNotification["POST_SUBJ"],
					"MESSAGE" => $parser->convert4mail($arNotification["POST_MESSAGE"]),
					"MESSAGE_DATE" => date("d.m.Y H:i:s"),
					"MESSAGE_LINK" => "http://".SITE_SERVER_NAME.CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_READ"], array("FID" => "1", "MID" => $newMID))."\n",
				);
				if ($event->Send("NEW_FORUM_PRIVATE_MESSAGE", SITE_ID, $arFields))
				{
					$arOK[] = array("id" => "send", "text" => GetMessage("PM_NOTIFICATION_SEND"));
					$arrVars = array("REQUEST_IS_READ" => "N");
					CForumPrivateMessage::Update($arResult["MESSAGE"]["ID"], $arrVars);
				}
			}
		}
	endif;

	if (empty($arError))
	{
		if (!empty($next))
		{
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_READ"],
				array("FID" => $arParams["FID"], "MID" => $next["ID"])));
		}
		else
		{
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"],
				array("FID" => $arParams["FID"])));
		}
	}

	if (!empty($arError))
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] .= $err->GetString();
	}
	if (!empty($arOk))
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["OK_MESSAGE"] .= $err->GetString();
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["MESSAGE"]["POST_MESSAGE"] = $parser->convert(
	$arResult["MESSAGE"]["~POST_MESSAGE"],
	array(
		"HTML" => "N",
		"ANCHOR" => "Y",
		"BIU" => "Y",
		"IMG" => "Y",
		"VIDEO" => "Y",
		"LIST" => "Y",
		"QUOTE" => "Y",
		"CODE" => "Y",
		"FONT" => "Y",
		"SMILES" => $arResult["MESSAGE"]["USE_SMILES"],
		"UPLOAD" => "N",
		"NL2BR" => "N",
		"TABLE" => "Y",
		"ALIGN" => "Y"
	));
$arResult["MESSAGE"]["RECIPIENT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
	array("UID" => $arResult["MESSAGE"]["RECIPIENT_ID"]));
$arResult["MESSAGE"]["AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
	array("UID" => $arResult["MESSAGE"]["AUTHOR_ID"]));
$arResult["MESSAGE"]["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"],
	MakeTimeStamp($arResult["MESSAGE"]["POST_DATE"], CSite::GetDateFormat()));
// ************************* Pagen *********************************************************************
$arFilter = array(
	"USER_ID"=>$arParams["UID"],
	"FOLDER_ID"=>$arParams["FID"]);
if ($arParams["FID"] == 2) //If this is outbox folder
	$arFilter = array("OWNER_ID" => $arParams["UID"]);
$db_res = CForumPrivateMessage::GetListEx(
	array($by => $order),
	$arFilter,
	false,
	0,
	array("sNameTemplate" => $arParams["NAME_TEMPLATE"])
);
$prev = array();
$next = array();
$bFound = false;
if($db_res && ($res = $db_res->Fetch()))
{
	do
	{
		if ($bFound)
		{
			$next = $res;
			break;
		}
		if ($res["ID"] == $arParams["MID"])
			$bFound = true;
		if (!$bFound)
			$prev = $res;

	}while ($res = $db_res->Fetch());
}

if (!empty($next))
{
	$arResult["MESSAGE_NEXT"] = $next + array("MESSAGE_LINK" => CComponentEngine::MakePathFromTemplate(
		$arParams["URL_TEMPLATES_PM_READ"],
		array("FID" => $arParams["FID"], "MID" => $next["ID"])));
}
if (!empty($prev))
{
	$arResult["MESSAGE_PREV"] = $prev + array("MESSAGE_LINK" => CComponentEngine::MakePathFromTemplate(
		$arParams["URL_TEMPLATES_PM_READ"],
		array("FID" => $arParams["FID"], "MID" => $prev["ID"])));
}

$arResult["pm_edit"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"],
	array("FID"=>$arParams["FID"], "mode" => "edit", "MID" => $arParams["MID"], "UID" => $arResult["MESSAGE"]["RECIPIENT_ID"]));
$arResult["pm_reply"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"],
	array("FID"=>$arParams["FID"], "mode" => "reply", "MID" => $arParams["MID"], "UID" => $arResult["MESSAGE"]["AUTHOR_ID"]));
$arResult["pm_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_LIST"],
	array("FID" => $arParams["FID"]));
$arResult["SystemFolder"] = FORUM_SystemFolder;

$resFolder = CForumPMFolder::GetList(array(), array("USER_ID" => $USER->GetID()));
$arResult["UserFolder"] = "N";
if (($resFolder) && ($resF = $resFolder->GetNext()))
{
	$arResult["UserFolder"] = array();
	do
	{
		$arResult["UserFolder"][$resF["ID"]] = $resF;
	}
	while ($resF = $resFolder->GetNext());
}
$arResult["count"] = CForumPrivateMessage::PMSize($USER->GetID(), COption::GetOptionInt("forum", "MaxPrivateMessages", 100));
$arResult["count"] = round($arResult["count"]*100);

$arResult["FolderName"] = ($arParams["FID"] <= $arResult["SystemFolder"]) ? GetMessage("PM_FOLDER_ID_".$arParams["FID"]) :
	$arResult["UserFolder"][$arParams["FID"]]["TITLE"];
// *************************/Page **********************************************************************

// ************************* Only for custom components ************************************************
$arResult["sessid"] = bitrix_sessid_post();
$arResult["FID"] = $arParams["FID"];
$arResult["MID"] = $arParams["MID"];
if ((intval($arResult["FID"]) > 1) && (intval($arResult["FID"]) <=3))
{
	$arResult["StatusUser"] = "RECIPIENT";
	$arResult["InputOutput"] = "RECIPIENT_ID";
	$arResult["recipient"]["name"] = $arResult["MESSAGE"]["RECIPIENT_NAME"];
	$arResult["recipient"]["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
		array("UID" => $arResult["MESSAGE"]["RECIPIENT_ID"]));
}
else
{
	$arResult["StatusUser"] = "SENDER";
	$arResult["InputOutput"] = "AUTHOR_ID";
	$arResult["recipient"]["name"] = $arResult["MESSAGE"]["AUTHOR_NAME"];
	$arResult["recipient"]["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
		array("UID" => $arResult["MESSAGE"]["AUTHOR_ID"]));
}
$arResult["NameUser"] = $arResult["recipient"]["name"];
// *************************/Only for custom components ************************************************

if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem(GetMessage("PM_TITLE_NAV"), CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_FOLDER"],
		array()));
	$APPLICATION->AddChainItem($arResult["FolderName"], CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PM_LIST"],
		array("FID" => $arParams["FID"])));
	$APPLICATION->AddChainItem($arResult["MESSAGE"]["POST_SUBJ"]);
}
	// GetMessage("PM_FOLDER_ID_0");
	// GetMessage("PM_FOLDER_ID_1");
	// GetMessage("PM_FOLDER_ID_2");
	// GetMessage("PM_FOLDER_ID_3");
	// GetMessage("PM_FOLDER_ID_4");
if ($arParams["SET_TITLE"] != "N")
{
	$APPLICATION->SetTitle(str_replace("#SUBJECT#", $arResult["MESSAGE"]["POST_SUBJ"], GetMessage("PM_TITLE")));
}

$this->IncludeComponentTemplate();

?>
