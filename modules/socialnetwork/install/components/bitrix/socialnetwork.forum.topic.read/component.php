<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!CModule::IncludeModule("socialnetwork")):
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return false;
elseif (intVal($arParams["FID"]) <= 0):
	ShowError(GetMessage("F_FID_IS_EMPTY"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$GLOBALS["FID"] = $arParams["FID"] = intVal($arParams["FID"]);
	$arParams["TID"] = intVal((intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]));
	$arParams["MID_UNREAD"] = (strLen(trim($arParams["MID"])) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MID"] = (is_array($arParams["MID"]) ? 0 : intVal($arParams["MID"]));
	if (strtolower($arParams["MID_UNREAD"]) == "unread_mid")
		$arParams["MID"] = intVal(ForumGetFirstUnreadMessage($arParams["FID"], $arParams["TID"]));
	$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] == "Y" ? "Y" : "N");
	$arParams["ACTION"] = (!empty($arParams["ACTION"]) ? $arParams["ACTION"] : $_REQUEST["ACTION"]);
	$arParams["ACTION"] = (!empty($arParams["ACTION"]) ? $arParams["ACTION"] : ($_POST["MESSAGE_TYPE"]=="REPLY" ? "REPLY" : false));
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"topic_list" => "PAGE_NAME=topic_list",
		"topic" => "PAGE_NAME=topic&TID=#TID#",
		"topic_edit" => "PAGE_NAME=topic_edit&TID=#TID#&MID=#MID#",
		"message" => "PAGE_NAME=topic&TID=#TID#&MID=#MID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE,
				array("PAGE_NAME", "FID", "TID", "UID", "GID", "MID", "ACTION", "sessid", "SEF_APPLICATION_CUR_PAGE_URL",
					"AJAX_TYPE", "AJAX_CALL", BX_AJAX_PARAM_ID, "result", "order"));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["PAGE_NAVIGATION_SHOW_ALL"] = ($arParams["PAGE_NAVIGATION_SHOW_ALL"] == "Y" ? "Y" : "N");

	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : ($arParams["USER_FIELDS"] ? array($arParams["USER_FIELDS"]) : array()));
	if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
		$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";

	$arParams["MESSAGES_PER_PAGE"] = intVal(empty($arParams["MESSAGES_PER_PAGE"]) ?
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);

	$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = trim($arParams["PATH_TO_ICON"]);

	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 500);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat());

	// AJAX
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $_REQUEST["AJAX_CALL"] == "Y") ? "Y" : "N");
	$arParams["AUTOSAVE"] = CForumAutosave::GetInstance();
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params # 1
********************************************************************/
	$arResult["TOPIC"] = array();
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	$arParams["PERMISSION_ORIGINAL"] = ForumCurrUserPermissions($arParams["FID"]);
	$arParams["PERMISSION"] = "A";

	$arError = array();
	$arNote = array();
/********************************************************************
				/Default params #1
********************************************************************/

/********************************************************************
				Main Data & Permissions
********************************************************************/

	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	if (empty($arResult["FORUM"])):
		ShowError(GetMessage("F_FID_IS_LOST"));
		CHTTP::SetStatus("404 Not Found");
		return false;
	elseif (($arParams["MODE"] == "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")) ||
		($arParams["MODE"] != "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))):
		ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
		return false;
	else:
		$user_id = $USER->GetID();
		if ($arParams["MODE"] == "GROUP")
		{
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "full", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "Y";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "M";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "answer", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "I";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "view", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "E";
		}
		else
		{
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "full", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "Y";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "M";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "answer", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "I";
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "view", $bCurrentUserIsAdmin))
				$arParams["PERMISSION"] = "E";
		}
	endif;

	if ($arParams["SHOW_VOTE"] == "Y")
	{
// A - NO ACCESS		E - READ			I - ANSWER
// M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS
		$arResult["VOTE_PERMISSION"] = (($arParams['PERMISSION'] === 'A') ? 0 : (($arParams['PERMISSION'] === 'E') ? 1 : 2));
		$arParams["SHOW_VOTE"] = ($arResult["VOTE_PERMISSION"] <= 'A' ? "N" : "Y");
	}

	if (!CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray(), $arParams["PERMISSION"])):
		ShowError(GetMessage("FORUM_SONET_NO_ACCESS"));
		return false;
	endif;

	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC"],
		array("UID" => $arParams["USER_ID"], "TID" => $arParams["TID"], "GID" => $arParams["SOCNET_GROUP_ID"], "FID" => $arParams["FID"]));
	if ((intVal($_REQUEST["PAGEN_".$arParams["PAGEN"]]) > 1) && (intVal($arParams["MID"]) <= 0)):
		$arResult["CURRENT_PAGE"] = ForumAddPageParams($arResult["CURRENT_PAGE"],
			array("PAGEN_".$arParams["PAGEN"] => intVal($_REQUEST["PAGEN_".$arParams["PAGEN"]])));
	endif;
/************** Message ********************************************/
	if ($arParams["MID"] > 0):
		$res = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y"));
		if (!(is_array($res) && $res["FORUM_ID"] == $arParams["FID"]))
		{
			LocalRedirect($arResult["CURRENT_PAGE"]);
		}
		elseif (($arParams["MODE"] == "GROUP" && $res["TOPIC_INFO"]["SOCNET_GROUP_ID"] == $arParams["SOCNET_GROUP_ID"]) ||
			($arParams["MODE"] != "GROUP" && $res["TOPIC_INFO"]["OWNER_ID"] == $arParams["USER_ID"]))
		{
			$arResult["MESSAGE"] = $res;
			$arParams["TID"] = $res["TOPIC_INFO"]["ID"];
			if ($res["APPROVED"] != "Y" && $arParams["PERMISSION"] < "Q"):
				$arNote[] = array(
					"id" => "mid is not approved",
					"text" => GetMessage("F_MID_IS_NOT_APPROVED"));
			endif;
		}
	endif;
/************** Topic **********************************************/
	$arFilter = array(
//		"FORUM_ID" => $arParams["FID"], 
		"ID" => $arParams["TID"],
		"SOCNET_GROUP_ID" => false
	);

	if ($arParams["MODE"] == "GROUP"):
		$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
	else:
		$arFilter["OWNER_ID"] = $arParams["USER_ID"];
		$arFilter["FORUM_ID"] = $arParams["FID"];
	endif;

	$db_res = CForumTopic::GetList(array(), $arFilter);
	if (!($db_res && $res = $db_res->GetNext())):
		$res = CForumTopic::GetByID($arParams["TID"]);
		if (empty($res) || !is_array($res)):
			$arError[] = array(
				"id" => "topic is not found",
				"text" => GetMessage("F_TID_IS_LOST"));
		elseif ($arParams["MODE"] == "GROUP" && $res["SOCNET_GROUP_ID"] != $arParams["SOCNET_GROUP_ID"] ||
			$arParams["MODE"] != "GROUP" && $res["OWNER_ID"] != $arParams["USER_ID"]):
			$arError[] = array(
				"id" => "not correct socnet_object",
				"text" => str_replace("#SOCNET_OBJECT#", ($arParams["MODE"] == "GROUP" ?
				GetMessage("F_GROUPS") : GetMessage("F_USERS")), GetMessage("F_TID_IS_LOST_IN_OBJECT")));
		elseif ($res["FORUM_ID"] != $arParams["FID"]):
			$arError[] = array(
				"id" => "not correct forum_id",
				"text" => GetMessage("F_TID_IS_LOST_IN_FORUM"));
		endif;
	elseif ($res["STATE"] == "L"):
		$arError[] = array(
			"id" => "topic is topic-link",
			"text" => GetMessage("F_TID_IS_LINK"));
	elseif ($res["APPROVED"] != "Y" && $arParams["PERMISSION"] < "Q"):
		$arError[] = array(
			"id" => "topic is not approved",
			"text" => GetMessage("F_TID_IS_NOT_APPROVED"));
	else:
		$arResult["TOPIC"] = $res;
	endif;
	if (!empty($arError)):
		$e = new CAdminException($arError);
		$res = $e->GetString();
		ShowError($res);
		return false;
	endif;
/********************************************************************
				/Main Data & Permissions
********************************************************************/

/********************************************************************
				Default params # 2
********************************************************************/
	$arResult["MESSAGE_FIRST"] = array();
	$arResult["MESSAGE_LIST"] = array();
	$arResult["MESSAGE_VIEW"] = array();
	$arResult["VIEW"] = "N";
	$bVarsFromForm = false;
/************** Current User ***************************************/

	$arResult["USER"] = array(
		"INFO" => array(),
		"PERMISSION" => $arParams["PERMISSION"],
		"RIGHTS" => array(
			"ADD_TOPIC" => (CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arResult["FORUM"], $arParams["PERMISSION"]) ? "Y" : "N"),
			"MODERATE" => (CForumNew::CanUserModerateForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]) == true ? "Y" : "N"),
			"EDIT" => (CForumNew::CanUserEditForum($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]) ? "Y" : "N"),
			"ADD_MESSAGE" => (CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]) ? "Y" : "N")),
		"SUBSCRIBE" => array(),
		"SHOW_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"]);

	// to avoid forum module permissions extension for admin
	if ($arParams["PERMISSION"] <= "E")
	{
		$arResult["USER"]["RIGHTS"] = array(
			"ADD_TOPIC" => "N",
			"MODERATE" => "N",
			"EDIT" => "N",
			"ADD_MESSAGE" => "N",
		);
	}

if ($USER->IsAuthorized()) {
	$arResult["USER"]["INFO"] = CForumUser::GetByUSER_ID($USER->GetParam("USER_ID"));
	$arResult["USER"]["SHOW_NAME"] = $_SESSION["FORUM"]["SHOW_NAME"];
	$arResult["USER"]["RANK"] = CForumUser::GetUserRank($USER->GetParam("USER_ID"));
	$db_res = CForumSubscribe::GetList(
		array(),
		array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $arParams["FID"],
			"TOPIC_ID" => $arParams["TID"],
			"SITE_ID" => SITE_ID));
	if ($db_res && $res = $db_res->Fetch())
		$arResult["USER"]["SUBSCRIBE"][$res["ID"]] = $res;
}
/*******************************************************************/
$arResult["PANELS"] = array(
	"MODERATE" => $arResult["USER"]["RIGHTS"]["MODERATE"],
	"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
	"SUPPORT" => IsModuleInstalled("support") && $APPLICATION->GetGroupRight("forum") >= "W" ? "Y" : "N",
	"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"],
	"STATISTIC" => IsModuleInstalled("statistic") && $APPLICATION->GetGroupRight("statistic") > "D" ? "Y" : "N",
	"MAIN" => $APPLICATION->GetGroupRight("main") > "D" ? "Y" : "N",
	"MAIL" => ($APPLICATION->GetGroupRight("mail") > "R" ? "Y" : "N"));
/*******************************************************************/

$_SERVER["REQUEST_URI"] = $arResult["CURRENT_PAGE"];
unset($_GET["MID"]); unset($GLOBALS["HTTP_GET_VARS"]["MID"]);
unset($_GET["ACTION"]); unset($GLOBALS["HTTP_GET_VARS"]["ACTION"]);

$parser = new forumTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->image_params["width"] = $parser->image_params["height"] = $arParams["IMAGE_SIZE"];

$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();

if (is_set($_REQUEST, "result"))
{
	switch (strToLower($_REQUEST["result"]))
	{
		case "message_add":
		case "mid_add":
		case "reply":
				$arNote[] = array(
					"id" => "message_add",
					"text" => GetMessage("F_MESS_SUCCESS_ADD"));
		break;
	}
	unset($_GET["result"]);
	DeleteParam(array("result"));
}

$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);
/********************************************************************
				/Default params # 2
********************************************************************/

ForumSetLastVisit($arParams["FID"], $arParams["TID"]);
ForumSetReadTopic($arParams["FID"], $arParams["TID"]);

/********************************************************************
				Action
********************************************************************/
$dir = dirname(__FILE__);
include(str_replace(array("\\", "//"), "/", $dir."/")."action.php");
/********************************************************************
				/Action
********************************************************************/

if (!empty($arError)):
	$e = new CAdminException($arError);
	$arResult["ERROR_MESSAGE"] = $e->GetString();
endif;
if (!empty($arNote)):
	if (isset($arNote['title']))
	{
		$arResult['OK_MESSAGE'] = $arNote['title'];
	}
	else
	{
		$e = new CAdminException($arNote);
		$arResult["OK_MESSAGE"] = $e->GetString();
	}
endif;

/********************************************************************
				Data
********************************************************************/
/************** Message list ***************************************/
$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = $arResult["TOPIC"]["ABS_LAST_MESSAGE_ID"];
// Number current page
$iNumPage = 0;
if ($arParams["MID"] > 0):
	$iNumPage = CForumMessage::GetMessagePage(
		$arParams["MID"],
		$arParams["MESSAGES_PER_PAGE"],
		$USER->GetUserGroupArray(),
		$arParams["TID"],
		array("PERMISSION_EXTERNAL" => $arParams["PERMISSION"]));
endif;

$arFilter = array(
	"TOPIC_ID" => $arParams["TID"]
);

if ($arParams["MODE"] != "GROUP")
	$arFilter["FORUM_ID"] = $arParams["FID"];
if ($arResult["USER"]["RIGHTS"]["MODERATE"] != "Y")
	$arFilter["APPROVED"] = "Y";
if ($USER->IsAuthorized())
	$arFilter["POINTS_TO_AUTHOR_ID"] = $USER->GetID();

// Pagen
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$db_res = CForumMessage::GetListEx(array("ID" => "ASC"), $arFilter, false, false,
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
		"bShowAll" => ($arParams["PAGE_NAVIGATION_SHOW_ALL"] == "Y"),
		"iNumPage" => ($iNumPage > 0 ? $iNumPage : false),
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]));
$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, ($iNumPage > 0 ? $iNumPage : false));
$arResult["NAV_RESULT"] = $db_res;
$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$number = intVal($db_res->NavPageNomer - 1) * $arParams["MESSAGES_PER_PAGE"] + 1;
$arResult['PAGE_NUMBER'] = $db_res->NavPageNomer;
$UserInfo = array();
$bNeedFirstMessage = ($db_res->NavPageNomer > 1 && $arParams["SHOW_VOTE"] == "Y");
$bNeedLoop = true;

while ($bNeedLoop)
{
	if (!($res = $db_res->GetNext())):
		$bNeedLoop = false;
		if ($bNeedFirstMessage):
			$db_res = CForumMessage::GetListEx(array("ID"=>"ASC"), $arFilter, false, 1);
			$res = $db_res->GetNext();
			if (!($res["PARAM1"] == "VT" && !empty($res["PARAM2"])))
			{
				$bNeedFirstMessage = false;
				break;
			}
			$number = 1;
		else:
			break;
		endif;
	endif;
/************** Message info ***************************************/
	// number in topic
	$res["NUMBER"] = $number++;
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N")));
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();

/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intVal($res["AUTHOR_ID"]);
	$res["AUTHOR_NAME"] = $parser->wrap_long_words($res["AUTHOR_NAME"]);
	if ($res["AUTHOR_ID"] <= 0)
	{
		// Status
		list($res["AUTHOR_STATUS_CODE"], $res["AUTHOR_STATUS"]) = ForumGetUserForumStatus(0);
	}
	else
	{
		if (!array_key_exists($res["AUTHOR_ID"], $UserInfo))
		{
			$perm = CForumNew::GetUserPermission($res["FORUM_ID"], CUser::GetUserGroup($res["AUTHOR_ID"]));
			$arUser = array(
				"Perms" => $perm,
				"Rank" => ($perm <= "Q" ? CForumUser::GetUserRank($res["AUTHOR_ID"], LANGUAGE_ID) : ""),
				"Points" => (!empty($res["POINTS"]) ? array("POINTS" => $res["POINTS"], "DATE_UPDATE" => $res["DATE_UPDATE"]) : false));

			$arUData = array();

			// Status
			list($arUData["AUTHOR_STATUS_CODE"], $arUData["AUTHOR_STATUS"]) = ForumGetUserForumStatus($res["AUTHOR_ID"], $arUser["Perms"], $arUser);

			// Avatar
			if (!empty($res["AVATAR"])):
				$arUData["AVATAR"] = array("ID" => $res["~AVATAR"], "FILE" => CFile::GetFileArray($res["~AVATAR"]));
				$arUData["AVATAR"]["HTML"] = CFile::ShowImage($arUData["AVATAR"]["FILE"],
					COption::GetOptionString("forum", "avatar_max_width", 90),
					COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\"", "", true);
			endif;
			// Voting
			$arUData["VOTING"] = "N";
			if (COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $USER->IsAuthorized() &&
				($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W" || $USER->GetID() != $res["AUTHOR_ID"]))
			{
				$bUnVote = $arUser["Points"];
				$bVote = (!($arUser["Points"]) ? $arResult["USER"]["RANK"]["VOTES"] :
					intval($arUser["Points"]["POINTS"]) < intval($arResult["USER"]["RANK"]["VOTES"]));
				$bVote = ($bVote ? $bVote : $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W");
				$arUData["VOTING"] = ($bVote ? "VOTE" : ($bUnVote ? "UNVOTE" : "N"));
			}
			// data
			$arUData["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
			// Another data
			$arUData["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
			if (!empty($res["SIGNATURE"]))
				$arUData["SIGNATURE"] = $parser->convert($res["~SIGNATURE"], array_merge($arAllow, array("SMILES" => "N")));

			$UserInfo[$res["AUTHOR_ID"]] = $arUData;
		}
		$res = array_merge($res, $UserInfo[$res["AUTHOR_ID"]]);
	}

	$res["FOR_JS"]["AUTHOR_NAME"] = Cutil::JSEscape(htmlspecialcharsbx($res["~AUTHOR_NAME"]));
	$res["FOR_JS"]["POST_MESSAGE"] = Cutil::JSEscape(htmlspecialcharsbx($res["~POST_MESSAGE_TEXT"]));
/************** Author info/****************************************/
/************** Panels *********************************************/
	$res["PANELS"] = array(
		"MODERATE" => $arResult["PANELS"]["MODERATE"],
		"DELETE" => $arResult["PANELS"]["DELETE"],
		"SUPPORT" => $arResult["PANELS"]["SUPPORT"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N",
		"EDIT" => $arResult["PANELS"]["EDIT"],
		"STATISTIC" => $arResult["PANELS"]["STATISTIC"] == "Y" && intVal($res["GUEST_ID"]) > 0 ? "Y" : "N",
		"MAIN" => $arResult["PANELS"]["MAIN"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N",
		"MAIL" => $arResult["PANELS"]["MAIL"],
		"VOTES" => $res["VOTING"] != "N" ? "Y" : "N");

	// here should be a trigger for turning off edit right for the archive group even for message author

	if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y" && $res["PANELS"]["EDIT"] != "Y" &&
		$USER->IsAuthorized() && $res["AUTHOR_ID"] == $USER->GetId() &&
		(COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "N") == "Y" || $arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] == intVal($res["ID"])))
	{
		$res["PANELS"]["EDIT"] = "Y";
	}
	$res["SHOW_PANEL"] = in_array("Y", $res["PANELS"]) ? "Y" : "N";

	if ($arParams["PERMISSION_ORIGINAL"] >= "Q")
	{
		$bIP = (preg_match("/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$/", $res["~AUTHOR_IP"]) ? true : false);
		$res["AUTHOR_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_IP"], "") : $res["AUTHOR_IP"]);
		$bIP = (preg_match("/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$/", $res["~AUTHOR_REAL_IP"]) ? true : false);
		$res["AUTHOR_REAL_IP"] = ($bIP ? GetWhoisLink($res["~AUTHOR_REAL_IP"], "") : $res["AUTHOR_REAL_IP"]);
		$res["IP_IS_DIFFER"] = ($res["AUTHOR_IP"] <> $res["AUTHOR_REAL_IP"] ? "Y" : "N");
	}
/************** Panels/*********************************************/
/************** Urls ***********************************************/
	$res["URL"] = array(
		"~USER" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"])),
		"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"])),
		"~EDITOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["EDITOR_ID"])),
		"~MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
			array("UID" => $arParams["USER_ID"], "TID" => $arParams["TID"], "GID" => $arParams["SOCNET_GROUP_ID"], "MID" => $res["ID"])),
		"~MESSAGE_EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_EDIT"],
			array("UID" => $arParams["USER_ID"], "TID" => $arParams["TID"], "GID" => $arParams["SOCNET_GROUP_ID"], "MID" => $res["ID"])),
		"USER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"])),
		"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["AUTHOR_ID"])),
		"EDITOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["EDITOR_ID"])),
		"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
			array("UID" => $arParams["USER_ID"], "TID" => $arParams["TID"], "GID" => $arParams["SOCNET_GROUP_ID"], "MID" => $res["ID"])),
		"MESSAGE_EDIT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_EDIT"],
			array("UID" => $arParams["USER_ID"], "TID" => $arParams["TID"], "GID" => $arParams["SOCNET_GROUP_ID"], "MID" => $res["ID"])));
	$res["URL"]["MESSAGE_EDIT"] = ForumAddPageParams($res["URL"]["~MESSAGE_EDIT"],
		array("MID" => $res["ID"], "ACTION" => "EDIT", "MESSAGE_TYPE" => "EDIT"));
	$res["URL"]["MESSAGE_DELETE"] = ForumAddPageParams($res["URL"]["~MESSAGE"],
		array("MID" => $res["ID"], "ACTION" => "del", "MESSAGE_TYPE" => "EDIT"/*, "sessid" => bitrix_sessid()*/));
	$res["URL"]["MESSAGE_SHOW"] = ForumAddPageParams($res["URL"]["~MESSAGE"],
		array("MID" => $res["ID"], "ACTION" => ($res["APPROVED"] == "Y" ? "hide" : "show"), "MESSAGE_TYPE" => "EDIT"/*, "sessid" => bitrix_sessid()*/));
	$res["URL"]["MESSAGE_SUPPORT"] = ForumAddPageParams($res["URL"]["~MESSAGE"],
		array("MID" => $res["ID"], "ACTION" => "support", "MESSAGE_TYPE" => "EDIT", "sessid" => bitrix_sessid()));
	$res["URL"]["AUTHOR_VOTE"] = ForumAddPageParams($res["URL"]["MESSAGE"],
			array("UID" => $res["AUTHOR_ID"], "MID" => $res["ID"], "VOTES" => intVal($arResult["USER"]["RANK"]["VOTES"]),
				"VOTES_TYPE" => ($res["VOTING"] == "VOTE" ? "V" : "U"), "ACTION" => "VOTE4USER"))/*."&amp;".bitrix_sessid_get()*/;
	$res["URL"]["MESSAGE_SPAM"] = ForumAddPageParams($res["URL"]["~MESSAGE"],
		array("MID" => $res["ID"], "ACTION" => "spam", "MESSAGE_TYPE" => "EDIT"/*, "sessid" => bitrix_sessid()*/));
/************** Urls/***********************************************/

	if ($number == 2 && $bNeedFirstMessage):
		$arResult["MESSAGE_FIRST"] = $res;
	else:
		$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	endif;
}

/************** /Message list **************************************/
/************** Attach files ***************************************/
if (!empty($arResult["MESSAGE_LIST"]))
{
	$res = array_keys($arResult["MESSAGE_LIST"]);
	$arFilterProps = $arFilter;
	if ($res[0] > 1)
		$arFilterProps[">ID"] = $arFilter[">MESSAGE_ID"] = intVal($res[0]) - 1;
	$arFilterProps["<ID"] = $arFilter["<MESSAGE_ID"] = intVal($res[count($res) - 1]) + 1;

	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	$bNeedLoop = $bBreakLoop = false;

	if ($db_files && $res = $db_files->Fetch()):
		$bNeedLoop = true;
	elseif ($bNeedFirstMessage):
		$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"),
			array("MESSAGE_ID" => $arResult["MESSAGE_FIRST"]["ID"]));
		if ($db_files && $res = $db_files->Fetch()){
			$bNeedLoop = $bBreakLoop = true;}
	endif;

	while ($bNeedLoop)
	{
		do
		{
			$res["SRC"] = CFile::GetFileSRC($res);
			if ($arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
			{
			// attach for custom
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_FILE"] =
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
			}
			if ($arResult["MESSAGE_FIRST"]["ID"] == $res["MESSAGE_ID"]):
				$arResult["MESSAGE_FIRST"]["FILES"][$res["FILE_ID"]] = $res;
				if (!$bNeedFirstMessage):
					$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
				endif;
			else:
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			endif;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		} while ($res = $db_files->Fetch());

		$bNeedLoop = false;
		if ($bNeedFirstMessage && !$bBreakLoop)
		{
			$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"),
				array("MESSAGE_ID" => $arResult["MESSAGE_FIRST"]["ID"]));
			if ($db_files && $res = $db_files->Fetch()) {
				$bNeedLoop = $bBreakLoop = true;}
		}
	}

	if (!empty($arParams["USER_FIELDS"]))
	{
		$db_props = CForumMessage::GetList(array("ID" => "ASC"), $arFilterProps, false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
		while ($db_props && ($res = $db_props->Fetch())) {
			$arResult["MESSAGE_LIST"][$res["ID"]]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
		}

		if ($bNeedFirstMessage) {
			$db_props = CForumMessage::GetList(array("ID" => "ASC"), array("ID" => $arResult["MESSAGE_FIRST"]["ID"]), false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
			if ($db_props && ($res = $db_props->Fetch()))
				$arResult["MESSAGE_FIRST"]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
		}
	}
}
$parser->arFiles = $arResult["FILES"];
if (!empty($arResult["MESSAGE_FIRST"])):
	$parser->arUserfields = $arResult["MESSAGE_FIRST"]["PROPS"];
	$arResult["MESSAGE_FIRST"]["POST_MESSAGE_TEXT"] = $parser->convert($arResult["MESSAGE_FIRST"]["~POST_MESSAGE_TEXT"], $arResult["MESSAGE_FIRST"]["ALLOW"]);
	$arResult["MESSAGE_FIRST"]["FILES_PARSED"] = $parser->arFilesIDParsed;
endif;
foreach ($arResult["MESSAGE_LIST"] as $iID => $res):
	$parser->arUserfields = $res["PROPS"];
	$arResult["MESSAGE_LIST"][$iID]["POST_MESSAGE_TEXT"] = $parser->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);
	$arResult["MESSAGE_LIST"][$iID]["FILES_PARSED"] = $parser->arFilesIDParsed;
endforeach;
/************** Message List/***************************************/
/************** Paths **********************************************/
$arResult["URL"] = array(
	"TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_EDIT"],
		array("FID" => $arParams["FID"], "UID" => $arParams["USER_ID"], "TID" => "new", "GID" => $arParams["SOCNET_GROUP_ID"])),
	"TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"],
		array("FID" => $arResult["FORUM"]["ID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])));
/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				Standart Action
********************************************************************/
$APPLICATION->AddChainItem($arResult["TOPIC"]["TITLE"]);
if ($arParams["SET_TITLE"] != "N"):
	$APPLICATION->SetTitle($arResult["TOPIC"]["~TITLE"]);
endif;
/********************************************************************
				Standart Action
********************************************************************/
$this->IncludeComponentTemplate();
return array(
	"FORUM" => $arResult["FORUM"],
	"TOPIC" => $arResult["TOPIC"],
	"MESSAGE" => $arResult["MESSAGE_VIEW"],
	"bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
	"PERMISSION" => $arParams["PERMISSION"]);
?>