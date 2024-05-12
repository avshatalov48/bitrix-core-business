<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("FM_NO_MODULE"));
	return 0;
elseif (!$GLOBALS["USER"]->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FM_AUTH"));
	return 0;
endif;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intval(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"];
	$arParams["newTID"] = isset($_REQUEST["newTID"]) ? intval($_REQUEST["newTID"]) : null;
	$arParams["action"] = isset($_REQUEST["ACTION"]) ? mb_strtoupper($_REQUEST["ACTION"]) : null;
	$arParams["newFID"] = isset($_REQUEST["newFID"]) ? intval($_REQUEST["newFID"]) : null;
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"list" => "PAGE_NAME=list&FID=#FID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
		"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"topic_new" => "PAGE_NAME=topic_new&FID=#FID#",
		"topic_search" => "PAGE_NAME=topic_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (!isset($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) || trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
		{
			$arParams["URL_TEMPLATES_" . mb_strtoupper($URL)] = $APPLICATION->GetCurPage() . "?" . $URL_VALUE;
		}
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : array($arParams["USER_FIELDS"]));
	if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
		$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";
	$arParams["PATH_TO_SMILE"] = "";
	$arParams["PATH_TO_ICON"] = "";

	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
if (ForumCurrUserPermissions($arParams["FID"]) < "Q"):
	$APPLICATION->AuthForm(GetMessage("FM_NO_FPERMS"));
endif;

$arResult["TOPIC"] = array();
$arResult["FORUM"] = array();
$arResult["MESSAGE_LIST"] = array();
$res = CForumTopic::GetByIDEx($arParams["TID"], array("GET_FORUM_INFO" => "Y"));
if (!empty($res))
{
	$arResult["TOPIC"] = $res["TOPIC_INFO"];
	$arResult["FORUM"] = $res["FORUM_INFO"];
	$arParams["FID"] = $arResult["FORUM"]["ID"];
}
if (empty($arResult["TOPIC"])):
	ShowError(GetMessage("F_ERROR_TOPIC_NOT_FOUND"));
	return false;
endif;

$message = ForumDataToArray($arParams["MID"]);
if ($message)
{
	$db_res = CForumMessage::GetListEx(
		array("ID"=>"ASC"),
		array("@ID" => implode(", ", $message), "TOPIC_ID" => $arParams["TID"]),
		false, 0,
		array('sNameTemplate' => $arParams["NAME_TEMPLATE"]));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}
}
if (count($arResult["MESSAGE_LIST"]) <= 0):
	ShowError(GetMessage("F_ERROR_MESSAGES_NOT_FOUND"));
	return false;
endif;

foreach ($arResult["TOPIC"] as $key => $val):
	$arResult["TOPIC"]["~".$key] = $val;
	$arResult["TOPIC"][$key] = htmlspecialcharsEx($val);
endforeach;
foreach ($arResult["FORUM"] as $key => $val):
	$arResult["FORUM"]["~".$key] = $val;
	$arResult["FORUM"][$key] = htmlspecialcharsEx($val);
endforeach;
/********************************************************************
				Default values
********************************************************************/
$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arResult["USER"] = array(
	"INFO" => array(),
	"PERMISSION" => $arParams["PERMISSION"],
	"RIGHTS" => array(
		"EDIT" => CForumNew::CanUserEditForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID()) ? "Y" : "N",
		"MODERATE" => "Y"),
	"SUBSCRIBE" => array());
$arResult["MESSAGE"] = array();
$arResult["NEW_TOPIC"] = array(
	"TOPIC" => array(),
	"FORUM" => array());
$arResult["VALUES"] = array();
$bVarsFromForm = false;
$arResult["TOPIC"]["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
	array("FID" => $arResult["FORUM"]["ID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => "s"));
$arResult["FORUM"]["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"],
	array("FID" => $arResult["FORUM"]["ID"]));
$arResult["topic_search"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_SEARCH"], array());
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["sessid"] = bitrix_sessid_post();
$arResult["ForumPrintIconsList"] = ForumPrintIconsList(7, $_POST["ICON"] ?? null);

$parser = new forumTextParser(LANGUAGE_ID);
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->imageWidth = $arParams["IMAGE_SIZE"];
$parser->imageHeight = $arParams["IMAGE_SIZE"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];


$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);
$arResult["PARSER"] = $parser;
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if (isset($_REQUEST["step"]) && intval($_REQUEST["step"]) == 1)
{
	$message = array_keys($arResult["MESSAGE_LIST"]);
	$arError = array();
	$strErrorMessage = ""; $strOKMessage = "";
	if (!check_bitrix_sessid()):
		$arError[] = array("id" => "bad_sessid", "text" => GetMessage("F_ERR_SESS_FINISH"));
	elseif ($arParams["action"] == "MOVE_TO_TOPIC"):
		if (ForumMoveMessage($arParams["FID"], $arParams["TID"], $message, $arParams["newTID"], array(), $strErrorMessage, $strOKMessage)):
			$topic = CForumTopic::GetByID($arParams["newTID"]);
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
				array("FID" => $arResult["FORUM"]["ID"], "TID" => $topic["ID"], "TITLE_SEO" => $topic["TITLE_SEO"], "MID" => "s")));
		else:
			$arError[] = array("id" => "bad_move", "text" => $strErrorMessage);
		endif;
	elseif ($arParams["action"] == "MOVE_TO_NEW" && trim($_REQUEST["TITLE"]) == ''):
		$arError[] = array("id" => "bad_move", "text" => GetMessage('FM_ERR_NO_DATA'));
	elseif ($arParams["action"] == "MOVE_TO_NEW"):
		$arFields = array(
			"TITLE"=> isset($_REQUEST["TITLE"]) ? trim($_REQUEST["TITLE"]) : null,
			"TITLE_SEO"=> isset($_REQUEST["TITLE_SEO"]) ? trim($_REQUEST["TITLE_SEO"]) : null,
			"DESCRIPTION"=> isset($_REQUEST["DESCRIPTION"]) ? trim($_REQUEST["DESCRIPTION"]) : null,
			"ICON"=> isset($_REQUEST["ICON"]) ? intval($_REQUEST["ICON"]) : null,
			"TAGS" => isset($_REQUEST["TAGS"]) ? $_REQUEST["TAGS"] : null);
		if (ForumMoveMessage($arParams["FID"], $arParams["TID"], $message, 0, $arFields, $strErrorMessage, $strOKMessage)):
			$res = CForumMessage::GetByIDEx($message[0], array("GET_TOPIC_INFO" => "Y"));
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
				array("FID" => $arResult["FORUM"]["ID"], "TID" => $res["TOPIC_INFO"]["ID"], "TITLE_SEO" => $res["TOPIC_INFO"]["TITLE_SEO"], "MID" => "s")));
		else:
			$arError[] = array("id" => "bad_move", "text" => $strErrorMessage);
		endif;
	endif;

	if (!empty($arError)):
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] .= $err->GetString();
		if (!empty($arParams["newTID"]))
		{
			$res = CForumTopic::GetByIDEx($arParams["newTID"]);
			$arResult["NEW_TOPIC"] = array(
				"TOPIC" => $res["TOPIC_INFO"],
				"FORUM" => $res["FORUM_INFO"]);
		}
		$arResult["VALUES"]["TITLE"] = isset($_REQUEST["TITLE"]) ? htmlspecialcharsEx($_REQUEST["TITLE"]) : null;
		$arResult["VALUES"]["DESCRIPTION"] = isset($_REQUEST["DESCRIPTION"]) ? htmlspecialcharsEx($_REQUEST["DESCRIPTION"]) : null;
		$arResult["VALUES"]["ICON"] = isset($_REQUEST["ICON"]) ? intval($_REQUEST["ICON"]) : null;
	endif;
	$arResult["OK_MESSAGE"] .= $strOKMessage;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arMessage = array();
$iCount = 1;
foreach ($arResult["MESSAGE_LIST"] as $key => $res)
{
/************** Message info ***************************************/
	$res["NUMBER"] = $iCount++;
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N")));
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
/************** Message info/***************************************/

/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
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
/************** Author info/****************************************/
/************** Urls ***********************************************/
	$res["URL"] = array(
		"~MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => $res["ID"])),
		"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => $res["ID"])),
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
		);
/************** Panels *********************************************/
	$res["PANELS"] = array(
		"MODERATE" => "Y",
		"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
		"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"]);
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

	$res["URL"]["~MODERATE"] = ForumAddPageParams($res["URL"]["~MESSAGE"], array("MID" => $res["ID"], "ACTION" => $res["APPROVED"]=="Y" ? "HIDE" : "SHOW"), false, false);
	$res["URL"]["MODERATE"] = htmlspecialcharsbx($res["URL"]["~MODERATE"])."&amp;".bitrix_sessid_get();
	$res["URL"]["~DELETE"] = ForumAddPageParams($res["URL"]["~MESSAGE"],array("MID" => $res["ID"], "ACTION" => "DEL"), false, false);
	$res["URL"]["DELETE"] = htmlspecialcharsbx($res["URL"]["~DELETE"])."&amp;".bitrix_sessid_get();
	$res["URL"]["~EDIT"] = ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"],
			array("FID" => $arParams["FID"])), array("TID" => $arResult["TOPIC"]["ID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT"),
			false, false);
	$res["URL"]["EDIT"] = htmlspecialcharsbx($res["URL"]["~EDIT"]);
/************** Panels/*********************************************/
/************** For custom templates *******************************/
	$res["profile_view"] = $res["URL"]["AUTHOR"];

	$res["MESSAGE_ANCHOR"] = $res["URL"]["MESSAGE"];
	$res["message_link"] = $res["URL"]["MESSAGE"];
	$res["email"] = $res["URL"]["AUTHOR_EMAIL"];
	$res["icq"] = $res["URL"]["AUTHOR_ICQ"];
	$res["pm_edit"] = $res["URL"]["AUTHOR_PM"];
/************** For custom templates/*******************************/
	$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
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
	$parser->arFiles = $arResult["FILES"] ?? null;
	foreach ($arResult["MESSAGE_LIST"] as $iID => $res):
		$arResult["MESSAGE_LIST"][$iID]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);
		$arResult["MESSAGE_LIST"][$iID]["FILES_PARSED"] = $parser->arFilesIDParsed;
	endforeach;
}
/************** Message List/***************************************/
$arResult["MESSAGE"] = $arResult["MESSAGE_LIST"];
/********************************************************************
				/Data
********************************************************************/
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"],
		CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])));
	$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"], $arResult["TOPIC"]["read"]);
}
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FM_TITLE_PAGE"));

$this->IncludeComponentTemplate();
?>
