<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("F_AUTH"));
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intval(intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]);
$arParams["action"] = mb_strtoupper(trim($_REQUEST["ACTION"]));
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#",
		"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"topic_new" => "PAGE_NAME=topic_new&FID=#FID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : array($arParams["USER_FIELDS"]));
	if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
		$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";
	$arParams["MESSAGES_PER_PAGE"] = intval(intVal($arParams["MESSAGES_PER_PAGE"]) > 0 ? $arParams["MESSAGES_PER_PAGE"] :
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intval(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);

	$arParams["PATH_TO_SMILE"] = "";

	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
/***************** CACHE *******************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);

if ($arParams["FID"] <= 0):
	ShowError(GetMessage("F_ERRROR_FORUM_EMPTY"));
	return false;
elseif (empty($arResult["FORUM"])):
	ShowError(GetMessage("F_ERRROR_FORUM_NOT_FOUND"));
	return false;
elseif (ForumCurrUserPermissions($arParams["FID"]) < "Q"):
	$APPLICATION->AuthForm(GetMessage("F_NO_PERMS"));
	return false;
endif;
/********************************************************************
				Default params
********************************************************************/
$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arResult["USER"] = array(
	"INFO" => array(),
	"PERMISSION" => $arParams["PERMISSION"],
	"RIGHTS" => array(
		"EDIT" => CForumNew::CanUserEditForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()) ? "Y" : "N"),
	"SUBSCRIBE" => array());

$arResult["TOPIC"] = array();
$arResult["MESSAGE_LIST"] = array();
$arResult["MESSAGE"] = array(); // out of date
$arResult["SHOW_RESULT"] = "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
	array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arParams["TID"], "MID" => "s"));
$arResult["URL"] = array(
	"LIST" => $arResult["list"],
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])),
	"READ" => $arResult["read"],
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arParams["TID"], "MID" => "s")),
	"MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"])),
	"~MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE_APPR"], array("FID" => $arParams["FID"], "TID" => $arParams["TID"])),
);

$parser = new forumTextParser(LANGUAGE_ID);
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->imageWidth = $arParams["IMAGE_SIZE"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);

if ($arParams["TID"] > 0):
	$res = CForumTopic::GetByID($arParams["TID"]);
	if ($res)
		$arResult["TOPIC"] = $res;
	else
		$arParams["TID"] = 0;
endif;
/********************************************************************
				Action
********************************************************************/
if (check_bitrix_sessid())
{
	$arError = array();
	$strOKMessage = "";

	if ($_SERVER['REQUEST_METHOD'] == "POST"):
		$message = (empty($_POST["MID_ARRAY"]) ? $_POST["MID"] : $_POST["MID_ARRAY"]);
		$message = (empty($message) ? $_POST["message_id"] : $message);
		$action = mb_strtoupper($_POST["ACTION"]);
	else:
		$message = (empty($_GET["MID_ARRAY"]) ? $_GET["MID"] : $_GET["MID_ARRAY"]);
		$message = (empty($message) ? $_GET["message_id"] : $message);
		$action = mb_strtoupper($_GET["ACTION"]);
	endif;
	if (!is_array($message))
		$message = explode(",", $message);
	$message = ForumMessageExistInArray($message);

	if (!$message)
		$arError[] = array("id" => "bad_data", "text" => GetMessage("F_NO_MESSAGE"));
	if (!in_array($action, array("DEL", "SHOW", "HIDE")))
		$arError[] = array("id" => "bad_action", "text" => GetMessage("F_NO_ACTION"));
	if (empty($arError))
	{
		$strErrorMessage = "";
		switch ($action)
		{
			case "DEL":
				ForumDeleteMessageArray($message, $strErrorMessage, $strOKMessage);
			break;
			case "SHOW":
			case "HIDE":
				ForumModerateMessageArray($message, $action, $strErrorMessage, $strOKMessage);
			break;
		}
		if (empty($strErrorMessage))
		{
			$res = CForumMessage::GetList(array("ID" => "ASC"), array("APPROVED" => "N"));
			if ($res <= 0)
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
			else
				LocalRedirect($arResult["URL"]["MODERATE_MESSAGE"]);
		}
		else
			$arError[] = array("id" => "bad_action", "text" => $strErrorMessage);
	}
	if (!empty($arError)):
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] .= $err->GetString();
	endif;
	$arResult["OK_MESSAGE"] = $strOKMessage;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arFilter = array("APPROVED" => "N", "FORUM_ID" => $arParams["FID"]);
if ($arParams["TID"] > 0)
	$arFilter["TOPIC_ID"] = $arParams["TID"];
$db_Message = CForumMessage::GetListEx(
	array("ID" => "ASC"),
	$arFilter,
	false, false,
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
		"bShowAll" => false,
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]
));
$db_Message->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$db_Message->NavStart($arParams["MESSAGES_PER_PAGE"], false);
$arResult["NAV_RESULT"] = $db_Message;
$arResult["NAV_STRING"] = $db_Message->GetPageNavStringEx($navComponentObject, GetMessage("F_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);

if ($db_Message && ($res = $db_Message->GetNext()))
{
	$iCount = 1;
	$arResult["SHOW_RESULT"] = "Y";
	do
	{
		$res["NUMBER"] = $iCount++;
		// data
		$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
		$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
		// text
		$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N")));
		$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
		// Avatar
		if ($res["AVATAR"] <> ''):
			$res["AVATAR"] = array("ID" => $res["AVATAR"]);
			$res["AVATAR"]["FILE"] = CFile::GetFileArray($res["AVATAR"]["ID"]);
			$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"], COption::GetOptionString("forum", "avatar_max_width", 100),
				COption::GetOptionString("forum", "avatar_max_height", 100), "border=\"0\"", "", true);
		endif;
		// data
		$res["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
		// Another data
		$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
		$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);

		$res["SIGNATURE"] = "";
		if ($arResult["FORUM"]["ALLOW_SIGNATURE"] == "Y" && $res["~SIGNATURE"] <> '')
		{
			$arAllow["SMILES"] = "N";
			$res["SIGNATURE"] = $parser->convert($res["~SIGNATURE"], $arAllow);
		}
		$res["ATTACH_IMG"] = ""; $res["FILES"] = $res["~ATTACH_FILE"] = $res["ATTACH_FILE"] = array();
/************** Panels *********************************************/
		$res["PANELS"] = array(
			"MODERATE" => "Y",
			"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
			"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"],
			"STATISTIC" => IsModuleInstalled("statistic") && $APPLICATION->GetGroupRight("statistic") > "D" ? "Y" : "N",
			"MAIN" => $APPLICATION->GetGroupRight("main") > "D" ? "Y" : "N");
		if ($res["PANELS"]["EDIT"] != "Y" && $USER->IsAuthorized() && $res["AUTHOR_ID"] == $USER->GetId()):
			if (COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y") == "Y"):
				$res["PANELS"]["EDIT"] = "Y";
			else:
				// get last message in topic
				// $arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] == intVal($res["ID"])
			endif;
		endif;
		if ($arResult["USER"]["PERMISSION"] >= "Q")
		{
			$bIP = (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $res["~AUTHOR_IP"]) ? true : false);
			$res["AUTHOR_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_IP"], "") : $res["AUTHOR_IP"]);
			$bIP = (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $res["~AUTHOR_REAL_IP"]) ? true : false);
			$res["AUTHOR_REAL_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_REAL_IP"], "") : $res["AUTHOR_REAL_IP"]);
			$res["IP_IS_DIFFER"] = ($res["AUTHOR_IP"] <> $res["AUTHOR_REAL_IP"] ? "Y" : "N");
		}
/************** Panels/*********************************************/
/************** Urls ***********************************************/
		$res["URL"] = array(
			"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TOPIC_ID"], "MID" => $res["ID"])),
			"EDITOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["EDITOR_ID"])),
			"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["AUTHOR_ID"])),
			"AUTHOR_EMAIL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"],
				array("UID" => $res["AUTHOR_ID"], "TYPE" => "email")),
			"AUTHOR_ICQ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"],
				array("UID" => $res["AUTHOR_ID"], "TYPE" => "icq")),
			"AUTHOR_PM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"],
				array("FID" => 0, "MID" => 0, "UID" => $res["AUTHOR_ID"], "mode" => "new")),
			"~MODERATE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"],
				array("MID" => $res["ID"], "ACTION" => "SHOW"), false, false),
			"MODERATE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"],
				array("MID" => $res["ID"], "ACTION" => "SHOW"))."&amp;".bitrix_sessid_get(),
			"~DELETE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"],
				array("MID" => $res["ID"], "ACTION" => "DEL"), false, false),
			"DELETE" => ForumAddPageParams($arResult["URL"]["~MODERATE_MESSAGE"],
				array("MID" => $res["ID"], "ACTION" => "DEL"))."&amp;".bitrix_sessid_get(),
			"~EDIT" => ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"],
				array("FID" => $arParams["FID"])), array("TID" => $res["TOPIC_ID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT"), false, false),
			"EDIT" => ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"],
				array("FID" => $arParams["FID"])), array("TID" => $res["TOPIC_ID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT"))."&amp;".bitrix_sessid_get()
			);
		$res["profile_view"] = $res["URL"]["AUTHOR"];
		$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	}while ($res = $db_Message->GetNext());
}
/************** Attach files ***************************************/
if (!empty($arResult["MESSAGE_LIST"]))
{
	$arFilter = array("@FILE_MESSAGE_ID" => array_keys($arResult["MESSAGE_LIST"]));
	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	if ($db_files && $res = $db_files->Fetch())
	{
		do
		{
			$res["SRC"] = CFile::GetFileSRC($res);
			if ($arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
			{
			// attach for custom
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"];
			}
			$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
	if (!empty($arParams["USER_FIELDS"]))
	{
		$db_props = CForumMessage::GetList(array("ID" => "ASC"), array("@ID" => array_keys($arResult["MESSAGE_LIST"])), false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
		while ($db_props && ($res = $db_props->Fetch()))
		{
			$arResult["MESSAGE_LIST"][$res["ID"]]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
			$arResult["MESSAGE_LIST"][$res["ID"]]["ALLOW"] = array_merge(
				$arResult["MESSAGE_LIST"][$res["ID"]]["ALLOW"],
				array("USERFIELDS" => $arResult["MESSAGE_LIST"][$res["ID"]]["PROPS"])
			);
		}
	}
/************** Message info ***************************************/
	$parser->arFiles = $arResult["FILES"];
	$arResult["MESSAGE"] = $arResult["MESSAGE_LIST"]; // For custom templates
	foreach ($arResult["MESSAGE_LIST"] as $iID => $res):
		$arResult["MESSAGE"][$iID]["POST_MESSAGE_TEXT"] = $arResult["MESSAGE_LIST"][$iID]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);
		$arResult["MESSAGE"][$iID]["FILES_PARSED"] = $arResult["MESSAGE_LIST"][$iID]["FILES_PARSED"] = $parser->arFilesIDParsed;
		if (isset($res["AVATAR"]["HTML"])) // For custom templates
			$arResult["MESSAGE"][$iID]["AVATAR"] = $res["AVATAR"]["HTML"]; // For custom templates
	endforeach;
}
/************** Message List/***************************************/
$arResult["sessid"] = bitrix_sessid_post();
$arResult["PARSER"] = $parser;
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], $arResult["URL"]["~LIST"]);
	if ($arParams["TID"] > 0):
		$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"], $arResult["URL"]["~READ"]);
	endif;
}
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("F_TITLE"));
/*******************************************************************/
	$this->IncludeComponentTemplate();
/*******************************************************************/
?>