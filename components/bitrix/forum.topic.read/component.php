<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param string $componentName
 * @param CBitrixComponent $this
 */
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/functions.php");
include_once($path);
$arUserGroups = $USER->GetUserGroupArray();
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["VERSION"] = intval($arParams["VERSION"]);
	$arParams["FID"] = intval((intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
	$GLOBALS["FID"] = $arParams["FID"]; // for top panel
	$arParams["TID"] = intval((intVal($arParams["TID"]) <= 0 ? $_REQUEST["TID"] : $arParams["TID"]));
	$arParams["TITLE"] = trim($arParams["TITLE"]);
	$arParams["TITLE"] = $arParams["TITLE"] <> '' ? $arParams["TITLE"] : trim($_REQUEST["TITLE"]);
	if ($arParams["TID"] <= 0 && $arParams["TITLE"] <> '')
		$arParams["TID"] = intval(strtok($arParams["TITLE"], "-"));
	$arParams["MID_UNREAD"] = (trim($arParams["MID"]) == '' ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MID"] = (is_array($arParams["MID"]) ? 0 : intval($arParams["MID"]));
	$arParams["MID"] = intval((($arParams["MID"] <= 0) && ($_REQUEST["MID"] > 0) ? $_REQUEST["MID"] : $arParams["MID"]));
	$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] == "Y" ? "Y" : "N");
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" || $arResult["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["AUTOSAVE"] = CForumAutosave::GetInstance();
	if (mb_strtolower($arParams["MID_UNREAD"]) == "unread_mid")
		$arParams["MID"] = intval(ForumGetFirstUnreadMessage($arParams["FID"], $arParams["TID"]));
	$arParams["MESSAGES_PER_PAGE"] = intval(empty($arParams["MESSAGES_PER_PAGE"]) ?
		COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
			"message_move" => "PAGE_NAME=message_move&FID=#FID#&TID=#TID#&MID=#MID#",
			"topic_new" => "PAGE_NAME=topic_new&FID=#FID#",
			"topic_move" => "PAGE_NAME=topic_move&FID=#FID#&TID=#TID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#",
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#");

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
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : (empty($arParams["USER_FIELDS"]) ? array() : array($arParams["USER_FIELDS"])));
	if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
		$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";

	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intval(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["PAGE_NAVIGATION_SHOW_ALL"] = ($arParams["PAGE_NAVIGATION_SHOW_ALL"] == "Y" ? "Y" : "N");

	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);

	$arParams["PATH_TO_SMILE"] = "";
	$arParams["PATH_TO_ICON"] = "";

	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	// Data and data-time format
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	// AJAX
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $_REQUEST["AJAX_CALL"] == "Y") ? "Y" : "N");
	$arParams["SHOW_FIRST_POST"] = ($arParams["SHOW_FIRST_POST"] == "Y" ? "Y" : "N");

	$arParams["SHOW_ICQ"] = ((COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : (($arParams["SEND_ICQ"] <= "A" || ($arParams["SEND_ICQ"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y"));
/***************** RATING ******************************************/
	$arParams["RATING_ID"] = (is_array($arParams["RATING_ID"]) ? $arParams["RATING_ID"] : array());
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_DESCRIPTION"] = ($arParams["SET_DESCRIPTION"] == "Y" ? "Y" : "N");
	$arParams["SET_PAGE_PROPERTY"] = ($arParams["SET_PAGE_PROPERTY"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
	$arMessage = array();
	$arResult["TOPIC"] = array();
	$arResult["FORUM"] = array();
	$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
	$arResult["MESSAGE_LIST"] = array();
	$arResult["MESSAGE_VIEW"] = array();
	$arResult["MESSAGE_FIRST"] = array();
	$arResult["USER"] = array(
		"INFO" => array(),
		"PERMISSION" => $arParams["PERMISSION"],
		"RIGHTS" => array(),
		"SUBSCRIBE" => array());

	$UserInfo = array();

	$arOk = array();
	$action = false;
	$s_action = false;
	if (!empty($_REQUEST["ACTION"]))
		$action = $_REQUEST["ACTION"];
	elseif ($_POST["MESSAGE_TYPE"]=="REPLY")
		$action = "REPLY";
	if (($_REQUEST["TOPIC_SUBSCRIBE"] == "Y") || ($_REQUEST["FORUM_SUBSCRIBE"] == "Y"))
		$s_action = "SUBSCRIBE";
	elseif (($_REQUEST["TOPIC_UNSUBSCRIBE"] == "Y") || ($_REQUEST["FORUM_UNSUBSCRIBE"] == "Y"))
		$s_action = "UNSUBSCRIBE";
	$arParams['ACTION'] = $action;
	$number = 1;
	$strErrorMessage = "";
	$strOKMessage = "";
	$View = false;
	$arResult["VIEW"] = "N";
	$bVarsFromForm = false;
	$arError = array();
	$arNote = array();
	$_REQUEST["result"] = ($_SERVER['REQUEST_METHOD'] == 'GET' ? $_REQUEST["result"] : '');
switch(mb_strtolower($_REQUEST["result"]))
{
	case "message_add":
	case "mid_add":
	case "reply":
		$strOKMessage = GetMessage("F_MESS_SUCCESS_ADD");
		break;

	case "show":
		$strOKMessage = GetMessage("F_MESS_SUCCESS_SHOW");
		break;
	case "hide":
		$strOKMessage = GetMessage("F_MESS_SUCCESS_HIDE");
		break;
	case "del":
		$strOKMessage = GetMessage("F_MESS_SUCCESS_DEL");
		break;

	case "top":
		$strOKMessage = GetMessage("F_TOPIC_SUCCESS_TOP");
		break;
	case "ordinary":
		$strOKMessage = GetMessage("F_TOPIC_SUCCESS_ORD");
		break;
	case "open":
		$strOKMessage = GetMessage("F_TOPIC_SUCCESS_OPEN");
		break;
	case "close":
		$strOKMessage = GetMessage("F_TOPIC_SUCCESS_CLOSE");
		break;

	case "VOTE4USER":
		$arFields = array(
			"UID" => $_GET["UID"],
			"VOTES" => $_GET["VOTES"],
			"VOTE" => (($_GET["VOTES_TYPE"] == "U")? True : False));
		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_MESSAGE"],
			array("FID" => $arParams["FID"],
				"TID" => $arParams["TID"],
				"TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"],
				"MID" => (intval($_REQUEST["MID"]) > 0? $_REQUEST["MID"] : "s")
			));
		break;
	case "FORUM_UNSUBSCRIBE":
	case "TOPIC_UNSUBSCRIBE":
	case "FORUM_SUBSCRIBE":
	case "TOPIC_SUBSCRIBE":
	case "FORUM_SUBSCRIBE_TOPICS":
		$arFields = array(
			"FID" => $arParams["FID"],
			"TID" => (($action == "FORUM_SUBSCRIBE" || $action == "FORUM_UNSUBSCRIBE")? 0 : $arParams["TID"]),
			"NEW_TOPIC_ONLY" => (($action == "FORUM_SUBSCRIBE_TOPICS")? "Y" : "N"));
		$url = ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_SUBSCR_LIST"],
				array()
			),
			array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
		break;
	case "mid_for_move_is_empty":
		$strErrorMessage = "mid_for_move_is_empty";
		break;
}
	unset($_GET["result"]);
	DeleteParam(array("result", "MID", "ACTION"));
	unset($_GET["MID"]); unset($GLOBALS["HTTP_GET_VARS"]["MID"]);
	unset($_GET["ACTION"]); unset($GLOBALS["HTTP_GET_VARS"]["ACTION"]);

	$parser = new forumTextParser(LANGUAGE_ID);
	$parser->MaxStringLen = $arParams["WORD_LENGTH"];
	$parser->imageWidth = $arParams["IMAGE_SIZE"];
	$parser->imageHeight = $arParams["IMAGE_SIZE"];
	$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
	$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

	$arResult["GROUP_NAVIGATION"] = array();
	$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);

	$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
	$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
/********************************************************************
				/Default params
********************************************************************/


/********************************************************************
				Main Data & Permissions
********************************************************************/
	if (isset($_REQUEST['MESSAGE_TYPE']) && $_REQUEST['MESSAGE_TYPE']=='REPLY')
		$arParams['MID'] = 0;

	if ($arParams["MID"] > 0 && ($res = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y", "GET_FORUM_INFO" => "Y"))) && !empty($res)):
		$arResult["TOPIC"] = $res["TOPIC_INFO"];
		$arResult["FORUM"] = $res["FORUM_INFO"];
		if ($arParams["PERMISSION"] < "Q" && $res["APPROVED"] != "Y"):
			$strOKMessage = GetMessage("F_MESS_SUCCESS_ADD_MODERATE");
		endif;
	else:
		$res = CForumTopic::GetByIDEx($arParams["TID"], array("GET_FORUM_INFO" => "Y"));
		if (empty($res)):
			$arError = array(
				"code" => "404",
				"title" => GetMessage("F_ERROR_TID_IS_LOST"));
		else:
			$arResult["TOPIC"] = $res;
			$arResult["FORUM"] = $res["FORUM_INFO"];
		endif;
	endif;

	if ($arParams["FID"] > 0)
	{
		$arValidSites = CForumNew::GetSites($arParams["FID"]);
		if (!isset($arValidSites[SITE_ID]) && ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin()))
		{
			$arError = array(
				"code" => "404",
				"title" => GetMessage("F_ERROR_TID_IS_LOST"));
		}
	}
	if (empty($arResult["TOPIC"])):
	elseif ($arResult["TOPIC"]["STATE"] == "L" && intval($arResult["TOPIC"]["TOPIC_ID"]) > 0):
		$res = CForumTopic::GetByIDEx($arResult["TOPIC"]["TOPIC_ID"], array("GET_FORUM_INFO" => "Y"));
		if (empty($res)):
			$arError = array(
				"code" => "404",
				"title" => GetMessage("F_ERROR_TID_IS_LOST"));
		else:
			$arResult["TOPIC"] = $res;
			$arResult["FORUM"] = $res["FORUM_INFO"];
		endif;
	elseif (!CForumNew::CanUserViewForum($arResult["FORUM"]["ID"], $arUserGroups)):
		$APPLICATION->AuthForm(GetMessage("F_FPERMS"));
	elseif (!CForumTopic::CanUserViewTopic($arResult["TOPIC"]["ID"], $arUserGroups)):
	// Topic is approve? For moderation forum.
		$arError = array(
			"code" => "tid_not_approved",
			"title" => GetMessage("F_ERROR_TID_NOT_APPROVED"),
			"link" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"],
				array("FID" => $arParams["FID"])));
	endif;

/********************************************************************
				/Main Data & Permissions
********************************************************************/
if ($arError["code"] == "404"):
	CHTTP::SetStatus("404 Not Found");
	ShowError($arError["title"]);
	return false;
elseif (!empty($arError["link"])):
	$url = ForumAddPageParams($arError["link"], array("error" => $arError["code"]));
	LocalRedirect($url);
	return false;
elseif ($arResult["TOPIC"]["ID"] != $arParams["TID"] || $arResult["FORUM"]["ID"] != $arParams["FID"]):
	$url = CComponentEngine::MakePathFromTemplate(($arParams["MID"] > 0 ? $arParams["~URL_TEMPLATES_MESSAGE"] : $arParams["~URL_TEMPLATES_READ"]),
				array("FID" => $arResult["FORUM"]["ID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => (intval($arParams["MID"]) > 0 ? $arParams["MID"] : "s")));
	LocalRedirect($url, false, "301 Moved Permanently");
	return false;
endif;

ForumSetReadTopic($arParams["FID"], $arParams["TID"]);

/********************************************************************
				Action
********************************************************************/
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/action.php");
include($path);
if ($arParams["AJAX_CALL"] == "Y")
{
	$APPLICATION->RestartBuffer();
	?><?=CUtil::PhpToJSObject(
		array(
		"error" => array(
			"code" => $action,
			"title" => $strErrorMessage),
		"note" => $arNote));
	die();
}
elseif (!empty($arNote["link"]) && !($arParams['AJAX_POST'] == 'Y' && $action == 'reply'))
{
	LocalRedirect(ForumAddPageParams($arNote["link"], array("result" => $arNote["code"]), true, false).
		(!empty($arParams["MID"]) ? "#message".$arParams["MID"] : ""));
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Topic **********************************************/
foreach ($arResult["TOPIC"] as $key => $val):
	$arResult["TOPIC"]["~".$key] = $val;
	if (is_string($val))
		$arResult["TOPIC"][$key] = $parser->wrap_long_words(htmlspecialcharsbx($val));
endforeach;
$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = "";
/************** Forum **********************************************/
foreach ($arResult["FORUM"] as $key => $val):
	$arResult["FORUM"]["~".$key] = $val;
	$arResult["FORUM"][$key] = htmlspecialcharsbx($val);
endforeach;
if ($arParams["SHOW_FIRST_POST"] == "N"):
	$arParams["SHOW_FIRST_POST"] = ($arResult["FORUM"]["ALLOW_TOPIC_TITLED"] == "Y" ? "Y" : "N");
endif;
/************** Current User ***************************************/
$arResult["USER"]["SHOW_NAME"] = $GLOBALS["FORUM_STATUS_NAME"]["guest"];
$arResult["USER"]["RIGHTS"] = array(
	"ADD_TOPIC" => CForumTopic::CanUserAddTopic($arParams["FID"], $arUserGroups, $USER->GetID(), $arResult["FORUM"]) ? "Y" : "N",
	"MODERATE" => (CForumNew::CanUserModerateForum($arParams["FID"], $arUserGroups, $USER->GetID()) == true ? "Y" : "N"),
	"EDIT" => CForumNew::CanUserEditForum($arParams["FID"], $arUserGroups, $USER->GetID()) ? "Y" : "N",
	"ADD_MESSAGE" => CForumMessage::CanUserAddMessage($arParams["TID"], $arUserGroups, $USER->GetID()) ? "Y" : "N");
if ($USER->IsAuthorized()):
	$arResult["USER"]["INFO"] = CForumUser::GetByUSER_ID($USER->GetParam("USER_ID"));
	$arResult["USER"]["SHOW_NAME"] = $_SESSION["FORUM"]["SHOW_NAME"];
	$arResult["USER"]["RANK"] = CForumUser::GetUserRank($USER->GetParam("USER_ID"), LANGUAGE_ID);
	$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => $arParams["TID"], "SITE_ID" => SITE_ID);
	$db_res = CForumSubscribe::GetList(array(), $arFields);
	if ($db_res && $res = $db_res->Fetch())
		$arResult["USER"]["SUBSCRIBE"][$res["ID"]] = $res;
	$arResult["USER"]["RIGHTS"]["EDIT_MESSAGE"] = ($arResult["USER"]["RIGHTS"]["EDIT"] != "Y" ? $arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] : "N");
else:
	$arResult["USER"]["RIGHTS"]["EDIT_MESSAGE"] = "N";
endif;
$arResult["USER"]["RIGHTS"]["EDIT_OWN_POST"] = COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y");
/************** Edit panels info ***********************************/
$arResult["PANELS"] = array(
	"MODERATE" => $arResult["USER"]["RIGHTS"]["MODERATE"],
	"DELETE" => $arResult["USER"]["RIGHTS"]["EDIT"],
	"SUPPORT" => IsModuleInstalled("support") && CForumUser::IsAdmin() ? "Y" : "N",
	"EDIT" => $arResult["USER"]["RIGHTS"]["EDIT"],
	"STATISTIC" => IsModuleInstalled("statistic") && $APPLICATION->GetGroupRight("statistic") > "D" ? "Y" : "N",
	"MAIN" => $APPLICATION->GetGroupRight("main") > "D" ? "Y" : "N");
/************** Urls ***********************************************/
$arResult["~CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
	array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => "s"));
$_SERVER["REQUEST_URI"] = $arResult["CURRENT_PAGE"] = htmlspecialcharsbx($arResult["~CURRENT_PAGE"]);

$arResult["URL"] = array(
	"~TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])),
	"TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])),
	"~TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])),
	"TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])),
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()),
	"~RSS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "topic", "IID" => $arParams["TID"])),
	"RSS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "topic", "IID" => $arParams["TID"])),
	"~RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], array("TYPE" => "rss2", "MODE" => "topic", "IID" => $arParams["TID"])),
	"RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], array("TYPE" => "rss2", "MODE" => "topic", "IID" => $arParams["TID"])));

$arResult["ERROR_MESSAGE"] = $strErrorMessage;
$arResult["OK_MESSAGE"] = $strOKMessage;
$arResult["PARSER"] = $parser;
$arResult["FILES"] = array();
$arResult["MESSAGE_FILES"] = array();
/************** Message List ***************************************/
$arAllow = forumTextParser::GetFeatures($arResult["FORUM"]);

// LAST MESSAGE
$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = 0;
if ($arResult["USER"]["RIGHTS"]["EDIT"] != "Y" && $USER->IsAuthorized() && COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y") != "Y"):
	if ($arResult["FORUM"]["MODERATION"] == "Y"):
		$db_res = CForumMessage::GetList(array("ID" => "DESC"), array("TOPIC_ID" => $arParams["TID"], "APPROVED" => "N",
			">ID" => $arResult["TOPIC"]["LAST_MESSAGE_ID"]), false, 1);
		if ($db_res && $res = $db_res->Fetch()):
			$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = intval($res["ID"]);
		endif;
	endif;
	if ($arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] <= 0):
		$arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] = $arResult["TOPIC"]["LAST_MESSAGE_ID"];
	endif;
endif;
// NUMBER CURRENT PAGE
$iNumPage = ($arParams["MID"] > 0 ? CForumMessage::GetMessagePage($arParams["MID"], $arParams["MESSAGES_PER_PAGE"], $arUserGroups, $arParams["TID"]) : 0);
// Create filter and additional fields for message select
$arFilter = array("TOPIC_ID" => $arParams["TID"]);
if ($arResult["USER"]["RIGHTS"]["MODERATE"] != "Y")
	$arFilter["APPROVED"] = "Y";
if ($USER->IsAuthorized())
	$arFilter["POINTS_TO_AUTHOR_ID"] = $USER->GetID();
/*******************************************************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
$db_res = CForumMessage::GetListEx(array("ID"=>"ASC"), $arFilter, false, false,
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
		"bShowAll" => ($arParams["PAGE_NAVIGATION_SHOW_ALL"] == "Y"),
		"iNumPage" => ($iNumPage > 0 ? $iNumPage : false),
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]));
$db_res->NavStart($arParams["MESSAGES_PER_PAGE"], false, ($iNumPage > 0 ? $iNumPage : false));
$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];

/*******************************************************************/
$arResult["NAV_RESULT"] = $db_res;
$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TITLE_NAV"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$number = intval($db_res->NavPageNomer-1)*$arParams["MESSAGES_PER_PAGE"] + 1;
$arResult['PAGE_NUMBER'] = $db_res->NavPageNomer;
$bNeedFirstMessage = ($arParams["SHOW_FIRST_POST"] == "Y" && $number != 1 ? true : false);
$bNeedLoop = true;

while ($bNeedLoop)
{
	if (!($res = $db_res->GetNext())):
		$bNeedLoop = false;
		if ($bNeedFirstMessage):
			$db_res = CForumMessage::GetListEx(array("ID"=>"ASC"), $arFilter, false, 1);
			$res = $db_res->GetNext();
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
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
	/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
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
				"Points" => (intval($res["POINTS"]) > 0 ? array("POINTS" => $res["POINTS"], "DATE_UPDATE" => $res["DATE_UPDATE"]) : false));

			$arUData = array();

			// Status
			list($arUData["AUTHOR_STATUS_CODE"], $arUData["AUTHOR_STATUS"]) = ForumGetUserForumStatus($res["AUTHOR_ID"], $arUser["Perms"], $arUser);

			// Avatar
			if (!empty($res["AVATAR"])):
				$arUData["AVATAR"] = array("ID" => $res["~AVATAR"], "FILE" => CFile::GetFileArray($res["~AVATAR"]));
				$arUData["AVATAR"]["HTML"] = CFile::ShowImage($arUData["AVATAR"]["FILE"],
					COption::GetOptionString("forum", "avatar_max_width", 100),
					COption::GetOptionString("forum", "avatar_max_height", 100), "border=\"0\"", "", true);
			endif;
			// Voting
			$arUData["VOTING"] = "N";
			if (COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $USER->IsAuthorized() &&
				(CForumUser::IsAdmin() || $USER->GetID() != $res["AUTHOR_ID"]))
			{
				$bUnVote = $arUser["Points"];
				$bVote = (!($arUser["Points"]) ? $arResult["USER"]["RANK"]["VOTES"] :
					intval($arUser["Points"]["POINTS"]) < intval($arResult["USER"]["RANK"]["VOTES"]));
				$arUData["VOTING"] = ($bVote ? "VOTE" : ($bUnVote ? "UNVOTE" : $res["VOTING"]));
			}
			// data
			$arUData["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
			// Another data
			$arUData["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
			$arUData["SIGNATURE"] = "";
			if ($arResult["FORUM"]["ALLOW_SIGNATURE"] == "Y" && !empty($res["~SIGNATURE"]))
				$arUData["SIGNATURE"] = $parser->convert($res["~SIGNATURE"], array_merge($arAllow, array("SMILES" => "N")));

			$UserInfo[$res["AUTHOR_ID"]] = $arUData;
		}
		$res = array_merge($res, $UserInfo[$res["AUTHOR_ID"]]);
	}
	// Another data
	$res["FOR_JS"]["AUTHOR_NAME"] = Cutil::JSEscape(htmlspecialcharsbx($res["~AUTHOR_NAME"]));
	$res["FOR_JS"]["POST_MESSAGE"] = Cutil::JSEscape(htmlspecialcharsbx($res["~POST_MESSAGE_TEXT"]));
/************** Author info/****************************************/
/************** Panels *********************************************/
	$res["PANELS"] = array(
		"MODERATE" => $arResult["PANELS"]["MODERATE"],
		"DELETE" => $arResult["PANELS"]["DELETE"],
		"SUPPORT" => $arResult["PANELS"]["SUPPORT"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N",
		"EDIT" => $arResult["PANELS"]["EDIT"],
		"STATISTIC" => $arResult["PANELS"]["STATISTIC"] == "Y" && intval($res["GUEST_ID"]) > 0 ? "Y" : "N",
		"MAIN" => $arResult["PANELS"]["MAIN"] == "Y" && $res["AUTHOR_ID"] > 0 ? "Y" : "N",
		"VOTES" => $res["VOTING"] != "N" ? "Y" : "N");

	if ($arResult["USER"]["RIGHTS"]["EDIT_MESSAGE"] == "Y" && $res["AUTHOR_ID"] == $USER->GetId() &&
		($arResult["USER"]["RIGHTS"]["EDIT_OWN_POST"] == "Y" || $arResult["TOPIC"]["iLAST_TOPIC_MESSAGE"] == intval($res["ID"])))
			$res["PANELS"]["EDIT"] = "Y";
	$res["SHOW_PANEL"] = in_array("Y", $res["PANELS"]) ? "Y" : "N";

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
		"~MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => $res["ID"])),
		"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => $res["ID"])),
		"EDITOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
			array("UID" => $res["EDITOR_ID"])),
		"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
			array("UID" => $res["AUTHOR_ID"])),
		"~AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
			array("UID" => $res["AUTHOR_ID"])),
		"AUTHOR_EMAIL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"],
			array("UID" => $res["AUTHOR_ID"], "TYPE" => "email")),
		"AUTHOR_ICQ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"],
			array("UID" => $res["AUTHOR_ID"], "TYPE" => "icq")),
		"AUTHOR_PM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"],
			array("FID" => 0, "MID" => 0, "UID" => $res["AUTHOR_ID"], "mode" => "new")),
		"AUTHOR_POSTS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"],
			array("UID" => $res["AUTHOR_ID"], "mode" => "all")));
	$res["URL"]["~AUTHOR_VOTE"] = ForumAddPageParams($res["URL"]["MESSAGE"],
			array("UID" => $res["AUTHOR_ID"], "MID" => $res["ID"], "VOTES" => intval($arResult["USER"]["RANK"]["VOTES"]),
				"VOTES_TYPE" => ($res["VOTING"] == "VOTE" ? "V" : "U"), "ACTION" => "VOTE4USER"));
	$res["URL"]["AUTHOR_VOTE"] = $res["URL"]["~AUTHOR_VOTE"]."&amp;".bitrix_sessid_get();

	if ($res["SHOW_PANEL"] == "Y")
	{
		$res["URL"]["~MODERATE"] = ForumAddPageParams(
			$res["URL"]["~MESSAGE"],
			array("MID" => $res["ID"], "ACTION" => $res["APPROVED"]=="Y" ? "HIDE" : "SHOW"),
			false, false );
		$res["URL"]["MODERATE"] = htmlspecialcharsbx($res["URL"]["~MODERATE"])."&amp;".bitrix_sessid_get();
		$res["URL"]["~DELETE"] = ForumAddPageParams(
			$res["URL"]["~MESSAGE"],
			array("MID" => $res["ID"], "ACTION" => "DEL"),
			false, false);
		$res["URL"]["DELETE"] = htmlspecialcharsbx($res["URL"]["~DELETE"])."&amp;".bitrix_sessid_get();
		$res["URL"]["~SUPPORT"] = ForumAddPageParams(
			$res["URL"]["~MESSAGE"],
			array("MID" => $res["ID"], "ACTION" => "FORUM_MESSAGE2SUPPORT"),
			false, false);
		$res["URL"]["SUPPORT"] = htmlspecialcharsbx($res["URL"]["~SUPPORT"])."&amp;".bitrix_sessid_get();
		$res["URL"]["~EDIT"] = ForumAddPageParams(
			CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"],
			array("FID" => $arParams["FID"])), array("TID" => $arParams["TID"], "MID" => $res["ID"], "MESSAGE_TYPE" => "EDIT"),
			false, false);
		$res["URL"]["EDIT"] = htmlspecialcharsbx($res["URL"]["~EDIT"]);
	}
/************** For custom templates *******************************/
	if ($arParams["VERSION"] < 1)
	{
		$res["MESSAGE_ANCHOR"] = $res["URL"]["MESSAGE"];
		$res["message_link"] = $res["URL"]["MESSAGE"];
		$res["profile_view"] = $res["URL"]["AUTHOR"];
		$res["email"] = $res["URL"]["AUTHOR_EMAIL"];
		$res["icq"] = $res["URL"]["AUTHOR_ICQ"];
		$res["pm_edit"] = $res["URL"]["AUTHOR_PM"];

		if ($res["SHOW_PANEL"] == "Y")
		{
			$res["SHOW_HIDE"] = array(
				"ACTION" => $res["PANELS"]["MODERATE"] == "Y" ? ($res["APPROVED"]=="Y" ? "HIDE" : "SHOW") : "N",
				"link" => $res["URL"]["MODERATE"]);
			$res["MESSAGE_DELETE"] = array(
				"ACTION" => ($res["PANELS"]["DELETE"] == "Y" ? "DELETE" : "N"),
				"link" => $res["URL"]["DELETE"]);
			$res["MESSAGE_SUPPORT"] = array(
				"ACTION" => ($res["PANELS"]["SUPPORT"] == "Y" ? "SUPPORT" : "N"),
				"link" => $res["URL"]["SUPPORT"]);
			$res["MESSAGE_EDIT"] = array(
				"ACTION" => ($res["PANELS"]["EDIT"] == "Y" ? "EDIT" : "N"),
				"link" => $res["URL"]["EDIT"]);
			$res["VOTES"] = array(
				"ACTION" => $res["PANELS"]["VOTES"] == "Y" ? $res["VOTING"] : "N",
				"link" => $res["URL"]["AUTHOR_VOTE"]);
			$res["SHOW_STATISTIC"] = $arResult["PANELS"]["STATISTIC"];
			$res["SHOW_AUTHOR_ID"] = $arResult["PANELS"]["MAIN"];
		}
	}
/************** For custom templates/*******************************/
	if ($number == 2 && $arParams["SHOW_FIRST_POST"] == "Y"):
		$arResult["MESSAGE_FIRST"] = $res;
		if (!$bNeedFirstMessage):
			$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
		endif;
	else:
		$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	endif;
}
/************** Attach files ***************************************/

if (!empty($arResult["MESSAGE_LIST"]))
{
	$res = array_keys($arResult["MESSAGE_LIST"]);
	$arFilterProps = $arFilter;
	if ($res[0] > 1)
		$arFilterProps[">ID"] = $arFilter[">MESSAGE_ID"] = intval($res[0]) - 1;
	$arFilterProps["<ID"] = $arFilter["<MESSAGE_ID"] = intval($res[count($res) - 1]) + 1;

	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	$bBreakLoop = false;
	$bNeedLoop = false;

	if ($db_files && $res = $db_files->Fetch()):
		$bNeedLoop = true;
	elseif ($bNeedFirstMessage):
		$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), array("MESSAGE_ID" => $arResult["MESSAGE_FIRST"]["ID"]));
		if ($db_files && $res = $db_files->Fetch()):
			$bNeedLoop = true;
			$bBreakLoop = true;
		endif;
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
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["ATTACH_IMG"];
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
			$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), array("MESSAGE_ID" => $arResult["MESSAGE_FIRST"]["ID"]));
			if ($db_files && $res = $db_files->Fetch()):
				$bNeedLoop = true;
				$bBreakLoop = true;
			endif;
		}
	}
	if (!empty($arParams["USER_FIELDS"]))
	{
		$db_props = CForumMessage::GetList(array("ID" => "ASC"), $arFilterProps, false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
		while ($db_props && ($res = $db_props->Fetch()))
		{
			$arResult["MESSAGE_LIST"][$res["ID"]]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
		}

		if ($bNeedFirstMessage)
		{
			$db_props = CForumMessage::GetList(array("ID" => "ASC"), array("ID" => $arResult["MESSAGE_FIRST"]["ID"]), false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
			if ($db_props && ($res = $db_props->Fetch()))
				$arResult["MESSAGE_FIRST"]["PROPS"] = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
		}
	}
}

/************** Message info ***************************************/
$parser->arFiles = $arResult["FILES"];
if (!empty($arResult["MESSAGE_FIRST"])):
	$arResult["MESSAGE_FIRST"]["POST_MESSAGE_TEXT"] = $parser->convert(
		$arResult["MESSAGE_FIRST"]["~POST_MESSAGE_TEXT"],
		array_merge($arResult["MESSAGE_FIRST"]["ALLOW"], array("USERFIELDS" => $arResult["MESSAGE_FIRST"]["PROPS"])));
	$arResult["MESSAGE_FIRST"]["FILES_PARSED"] = $parser->arFilesIDParsed;
endif;
foreach ($arResult["MESSAGE_LIST"] as $iID => $res):
	$arResult["MESSAGE_LIST"][$iID]["POST_MESSAGE_TEXT"] = $parser->convert(
		$res["~POST_MESSAGE_TEXT"],
		array_merge($res["ALLOW"], array("USERFIELDS" => $res["PROPS"])));
	$arResult["MESSAGE_LIST"][$iID]["FILES_PARSED"] = $parser->arFilesIDParsed;
endforeach;
/************** Message List/***************************************/
/************** Navigation *****************************************/
if (intval($arResult["FORUM"]["FORUM_GROUP_ID"]) > 0):
	$PARENT_ID = intval($arResult["FORUM"]["FORUM_GROUP_ID"]);
	while ($PARENT_ID > 0)
	{
		$res = $arResult["GROUPS"][$PARENT_ID];
		$res["URL"] = array(
			"GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)),
			"~GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)));
		$arResult["GROUP_NAVIGATION"][] = $res;
		$PARENT_ID = intval($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
	}
	$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
endif;
/************** Navigation/*****************************************/
/************** For custom templates *******************************/
if ($arParams["VERSION"] < 1)
{
	$arResult["topic_new"] = $arResult["URL"]["TOPIC_NEW"];
	$arResult["list"] = $arResult["URL"]["TOPIC_LIST"];
	$arResult["UserPermission"] = $arParams["PERMISSION"];
	$res = ShowActiveUser(array("PERIOD" => 600, "TITLE" => "", "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => $arParams["TID"]));
	$res["SHOW_USER"] = "N";
	if ($res["NONE"] != "Y")
	{
		$arUser = array();
		if (is_array($res["USER"]) && count($res["USER"]) > 0)
		{
			foreach ($res["USER"] as $r)
			{
				$r["SHOW_NAME"] = $parser->wrap_long_words($r["SHOW_NAME"]);
				$r["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $r["UID"]));
				$arUser[] = $r;
			}
			if (count($arUser) > 0)
			{
				$res["SHOW_USER"] = "Y";
			}
			$res["USER"] = $arUser;
		}
	}
	$arResult["UserOnLine"] = $res;
	$arResult["bVarsFromForm"] = $bVarsFromForm;
	$arResult["CanUserAddTopic"] = $arResult["USER"]["RIGHTS"]["ADD_TOPIC"] == "Y";
}
/************** For custom templates/*******************************/

$this->IncludeComponentTemplate();

if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(htmlspecialcharsbx($arResult["TOPIC"]["~TITLE"]));

if ($arParams["SET_DESCRIPTION"] != "N")
{
	$description = '';

	$cache = new CPHPCache();
	$cache_path = $GLOBALS['CACHE_MANAGER']->GetCompCachePath(CComponentEngine::MakeComponentPath($this->__name));
	$arCacheID = array($arParams['FID'], $arParams['TID']);
	$cache_id = "forum_topic_desc_".md5(serialize($arCacheID));

	if ($cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$descRes = $cache->GetVars();
		$description = $descRes["Description"];
	}
	if ($description == '')
	{
		$db_res = CForumMessage::GetListEx(array("ID" => "ASC"), array("TOPIC_ID" => $arParams["TID"]), 0, 1);
		if ($db_res && $arRes = $db_res->GetNext())
		{
			$description = HTMLToTxt($parser->convert($arRes['POST_MESSAGE'], $arAllow),'', array(
				"/(<img\s.*?src\s*=\s*)([\"']?)(\\/.*?)(\\2)(\s.+?>|\s*>)/is",  // from HTMLToTxt
				"/(<img\s.*?src\s*=\s*)([\"']?)(.*?)(\\2)(\s.+?>|\s*>)/is",
				"/(<a\s.*?href\s*=\s*)([\"']?)(\\/.*?)(\\2)(.*?>)(.*?)<\\/a>/is",
				"/(<a\s.*?href\s*=\s*)([\"']?)(.*?)(\\2)(.*?>)(.*?)<\\/a>/is",
			));
			$description = str_replace(array("\r", "\n"), "", $description);
			if (mb_strlen($description) > 512)
			{
				$description = mb_substr($description, 0, 512);
				$rSpace = mb_strrpos($description, ' ');
				if ($rSpace !== false)
					$description = mb_substr($description, 0, $rSpace).'...';
			}
		}

		if (($description != '') && ($arParams["CACHE_TIME"] > 0))
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			CForumCacheManager::SetTag($cache_path, "forum_topic_".$arParams['TID']);
			$cache->EndDataCache(array("Description" => $description));
		}
	}

	if ($description != '')
		$APPLICATION->SetPageProperty("description", $description);
}

// if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);

if ($arParams["SET_NAVIGATION"] != "N")
{
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $res["URL"]["~GROUP"]);
	endforeach;
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arResult["FORUM"]["~NAME"]), $arResult["URL"]["~TOPIC_LIST"]);
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arResult["TOPIC"]["~TITLE"]));
}
if (($arParams["SET_PAGE_PROPERTY"] == "Y") && ($arParams["SET_DESCRIPTION"] == "N")):
	if (!empty($arResult["TOPIC"]["~TAGS"])):
		$APPLICATION->SetPageProperty("keywords", str_replace(",", " ", $arResult["TOPIC"]["~TAGS"]));
	endif;
	if (!empty($arResult["TOPIC"]["~DESCRIPTION"])):
		$APPLICATION->SetPageProperty("description", str_replace(",", " ", $arResult["TOPIC"]["~DESCRIPTION"]));
	endif;
endif;

return array("FORUM" => $arResult["FORUM"], "bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
	"TID" => $arParams["TID"], "FID" => $arParams["FID"], "arFormParams" => $arResult);
?>