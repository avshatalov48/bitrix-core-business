<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUser $DB
 * @param array $arParams
 * @param array $arResult
 * @param string $componentName
 * @param CBitrixComponent $this
 */
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $arParams["DEFAULT_FID"] : $arParams["FID"]);
	$GLOBALS["FID"] = $arParams["FID"];
	$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"topic_new" => "PAGE_NAME=topic_new&FID=#FID#",
			"subscr_list" => "PAGE_NAME=subscr_list&FID=#FID#",
			"topic_move" => "PAGE_NAME=topic_move&FID=#FID#&TID=#TID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = (intVal($arParams["PAGEN"]) <= 0 ? 1 : intVal($arParams["PAGEN"]));
	$arParams["TOPICS_PER_PAGE"] = intVal($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	$arParams["MESSAGES_PER_PAGE"] = intVal($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");

	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/

$arResult["FORUM"]= CForumNew::GetByID($arParams["FID"]);
if (empty($arResult["FORUM"])):
	CHTTP::SetStatus("404 Not Found");
	ShowError(GetMessage("F_ERROR_FORUM_NOT_EXISTS"));
	return false;
elseif (!CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray())):
	$APPLICATION->AuthForm(GetMessage("F_NO_FPERMS"));
	return false;
elseif ((!array_key_exists(SITE_ID, CForumNew::GetSites($arParams["FID"]))) && ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin()) ):
	CHTTP::SetStatus("404 Not Found");
	ShowError(GetMessage("F_ERROR_FORUM_NOT_EXISTS"));
	return false;
endif;

/********************************************************************
				Default values
********************************************************************/
$arParams["PERMISSION"] = $arResult["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arResult["Topics"] = array();
$arResult["TOPICS"] = array();
$arResult["URL"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()),
	"~INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_INDEX"], array()),
	"TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])),
	"~TOPIC_LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])),
	"TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])),
	"~TOPIC_NEW" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])),
	"RSS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "forum", "IID" => $arParams["FID"])),
	"~RSS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], array("TYPE" => "default", "MODE" => "forum", "IID" => $arParams["FID"])),
	"RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], array("TYPE" => "rss2", "MODE" => "forum", "IID" => $arParams["FID"])),
	"~RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], array("TYPE" => "rss2", "MODE" => "forum", "IID" => $arParams["FID"])));
$arResult["CanUserAddTopic"] = CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arResult["FORUM"]);
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$parser = new forumTextParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
if ($_SERVER['REQUEST_METHOD'] == "POST"):
	$arResult["TID"] = (empty($_POST["TID_ARRAY"]) ? $_POST["TID"] : $_POST["TID_ARRAY"]);
endif;
if (empty($arResult["TID"]))
	$arResult["TID"] = (empty($_REQUEST["TID_ARRAY"]) ? $_REQUEST["TID"] : $_REQUEST["TID_ARRAY"]);
$ACTION = $_REQUEST["ACTION"];
$arResult["NOTIFICATIONS"] = array(
	"not_approve" => GetMessage("F_TOPIC_NOT_APPROVED"),
	"tid_not_approved" => GetMessage("F_TOPIC_NOT_APPROVED"),
	"tid_is_lost" => GetMessage("F_TOPIC_IS_LOST"),
	"del_topic" => GetMessage("F_TOPIC_IS_DEL"),
	"delele" => GetMessage("F_TOPICS_IS_DEL"),
	"stick" => GetMessage("F_TOPICS_IS_PINNED"),
	"unstick" => GetMessage("F_TOPICS_IS_UNPINNED"),
	"open" => GetMessage("F_TOPICS_IS_OPENED"),
	"close" => GetMessage("F_TOPICS_IS_CLOSED"));
if (!empty($_REQUEST["result"]) && array_key_exists($_REQUEST["result"], $arResult["NOTIFICATIONS"])):
	$arResult["OK_MESSAGE"] = $arResult["NOTIFICATIONS"][$_REQUEST["result"]];
endif;
if (!empty($_REQUEST["error"]) && array_key_exists($_REQUEST["error"], $arResult["NOTIFICATIONS"])):
	$arResult["OK_MESSAGE"] = $arResult["NOTIFICATIONS"][$_REQUEST["error"]];
endif;
$arResult["GROUP_NAVIGATION"] = array();
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$strErrorMessage = ""; $strOkMessage = "";
$arResult["USER"] = array(
	"INFO" => array(),
	"RIGHTS" => array(
		"CAN_ADD_TOPIC" => $arResult["CanUserAddTopic"] ? "Y" : "N"),
	"PERMISSION" => $arResult["PERMISSION"],
	"SUBSCRIBE" => array());
/********************************************************************
				/Default values
********************************************************************/

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

/********************************************************************
				Actions
********************************************************************/
if (check_bitrix_sessid() && (strLen($ACTION) > 0))
{
	$aMsg = array();
	switch ($ACTION)
	{
		case "FORUM_SUBSCRIBE":
		case "FORUM_SUBSCRIBE_TOPICS":
			if (ForumSubscribeNewMessagesEx($arParams["FID"], 0, (($ACTION=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"), $strErrorMessage, $strOkMessage)):
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("FID" => $arParams["FID"])));
				return true;
			endif;
		break;
		case "SET_BE_READ":
			ForumSetReadForum($arParams["FID"]);
			LocalRedirect($APPLICATION->GetCurPageParam('', array('sessid', 'ACTION')));
		break;
		case "SET_ORDINARY":
		case "SET_TOP":
			$ACTION = ($ACTION == "SET_ORDINARY" ? "ORDINARY" : "TOP");
			if (ForumTopOrdinaryTopic($arResult["TID"], $ACTION, $strErrorMessage, $strOkMessage)):
				LocalRedirect(ForumAddPageParams($arResult["URL"]["~TOPIC_LIST"], array("result" => ($ACTION == "ORDINARY" ? "unstick" : "stick"))));
				return true;
			endif;
			break;
		case "MOVE_TOPIC":
			$topic_id = (is_array($arResult["TID"]) ? implode(",", $arResult["TID"]) : $arResult["TID"]);
			if (!empty($topic_id)):
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_MOVE"],
					array("FID" => $arParams["FID"], "TID" => $topic_id)));
				return true;
			endif;
		break;
		case "DEL_TOPIC":
			if (ForumDeleteTopic($arResult["TID"], $strErrorMessage, $strOkMessage)):
				if (isset($_REQUEST['NAV_PAGE']) && strpos($_REQUEST['NAV_PAGE'], ':') !== false)
				{
					list($NavNum, $NavPageNomer) = explode(":", $_REQUEST['NAV_PAGE']);
					LocalRedirect(ForumAddPageParams($arResult["URL"]["~TOPIC_LIST"], array("result" => "delele", "PAGEN_".intval($NavNum) => intval($NavPageNomer))));
					return true;
				}
				LocalRedirect(ForumAddPageParams($arResult["URL"]["~TOPIC_LIST"], array("result" => "delele")));
				return true;
			endif;
		break;
		case "STATE_Y":
		case "STATE_N":
			$ACTION = ($ACTION == "STATE_Y" ? "OPEN" : "CLOSE");
			$state = ($ACTION == "STATE_Y" ? "Y" : "N");
			if (ForumOpenCloseTopic($arResult["TID"], $ACTION, $strErrorMessage, $strOkMessage)):
				LocalRedirect(ForumAddPageParams($arResult["URL"]["~TOPIC_LIST"], array("result" => ($ACTION == "OPEN" ? "open" : "close"))));
				return true;
			endif;
		break;
	}
}
elseif (!check_bitrix_sessid() && (strLen($ACTION) > 0))
{
	$strErrorMessage .= GetMessage("F_ERR_SESS_FINISH").".\n";
}
//*******************************************************************
$arResult["ERROR_MESSAGE"] .= trim($strErrorMessage);
if (!empty($strErrorMessage))
	$arResult["OK_MESSAGE"] = trim($strOkMessage);
else
	$arResult["OK_MESSAGE"] .= trim($strOkMessage);
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["SortingEx"] = array("TITLE", "POSTS", "VIEWS", "USER_START_NAME", "LAST_POST_DATE");
global $by, $order;
InitSorting($APPLICATION->GetCurPage()."?PAGE_NAME=list&FID=".$arParams["FID"]);
if (!in_array($by, $arResult["SortingEx"])):
	ForumGetTopicSort($by, $order, $arResult["FORUM"]);
endif;

$by = ($by == "ABS_LAST_POST_DATE" ? "LAST_POST_DATE" : $by);
if ($by == "LAST_POST_DATE" && $arResult["PERMISSION"] >= "Q"):
	$by = "ABS_LAST_POST_DATE";
endif;
$arResult["SortingEx"] = array_flip($arResult["SortingEx"]);
foreach ($arResult["SortingEx"] as $key => $val):
	$arResult["SortingEx"][$key] = SortingEx($key);
endforeach;

$arFilter = array("FORUM_ID" => $arParams["FID"]);
if ($USER->IsAuthorized())
	$arFilter["USER_ID"] = $USER->GetID();
if ($arResult["PERMISSION"] < "Q")
	$arFilter["APPROVED"] = "Y";
/*******************************************************************
				CACHE
*******************************************************************/
$cache = new CPHPCache();
global $NavNum;
$PAGEN_NAME="PAGEN_".($NavNum+1);
global ${$PAGEN_NAME};
$PAGEN = ${$PAGEN_NAME};

global $CACHE_MANAGER;
$cache_path = $CACHE_MANAGER->GetCompCachePath(CComponentEngine::MakeComponentPath($this->__name));
$arCacheID = array(
	($arResult["PERMISSION"] < "Q"),
	$arParams['FID'],
	CTimeZone::GetOffset(),
	$by => $order
);
$cache_id = "forum_topics_".md5(serialize($arCacheID));
if (($PAGEN == null) && ($arParams["CACHE_TIME"] > 0) && defined("BX_COMP_MANAGED_CACHE")) // cache only the first page
{
	if ($cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["Topics"] = $res["Topics"];
		$arResult["NAV_STRING"] = $res['NAV_STRING'];
		$arResult["NAV_PAGE"] = $res['NAV_PAGE'];

		// get last_visit dates
		$arTopics = array();
		foreach ($arResult['Topics'] as $arTopic)
			$arTopics[] = $arTopic['ID'];

		$arVisit = CForumUser::GetUserTopicVisits($arParams["FID"], $arTopics);

		if (!empty($arVisit))
		{
			foreach ($arResult['Topics'] as $topicIndex => $arTopic)
			{
				if (isset($arVisit[$arTopic['ID']]))
				{
					$arResult['Topics'][$topicIndex]['LAST_VISIT'] = $arVisit[$arTopic['ID']];
				}
			}
		}
	}
}
/*******************************************************************
				/ CACHE
*******************************************************************/
if (empty($arResult["Topics"])) // cache miss or PAGE > 1
{
	/*******************************************************************/
	$db_res = CForumTopic::GetListEx(array("SORT"=>"ASC", $by=>$order), $arFilter, false, false,
		array(
			"bDescPageNumbering" => ($arParams["USE_DESC_PAGE"] == "Y"),
			"nPageSize" => $arParams["TOPICS_PER_PAGE"],
			"bShowAll" => false,
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]
		)
	);
	$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
	$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["NAV_PAGE"] = $db_res->NavNum.':'.$db_res->NavPageNomer;
	$topicLinks = array();
	/*******************************************************************/
	while ($res = $db_res->GetNext())
	{
		$res["URL"] = array(
			"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
			"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
			"LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intVal($res["LAST_MESSAGE_ID"]))),
			"~LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intVal($res["LAST_MESSAGE_ID"]))),
			"MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "unread_mid")),
			"~MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "unread_mid")),
			"USER_START" => CComponentEngine::MakePathFromTemplate(	$arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["USER_START_ID"])),
			"~USER_START" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["USER_START_ID"])),
			"LAST_POSTER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["LAST_POSTER_ID"])),
			"~LAST_POSTER" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["LAST_POSTER_ID"])),
			"MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"])),
			"~MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE_APPR"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["ID"])));

		$res["TopicStatus"] = "OLD";
		if ($res["APPROVED"] != "Y")
		{
			$res["TopicStatus"] = "NA";
		}
		elseif ($res["STATE"] == "L")
		{
			$res["TopicStatus"] = "MOVED";
			$topicLinks[count($arResult["Topics"])] = $res["TOPIC_ID"];
		}
		/*******************************************************************/
		if($arResult["PERMISSION"] >= "Q")
		{
			$res["LAST_POSTER_ID"] = $res["ABS_LAST_POSTER_ID"];
			$res["LAST_POST_DATE"] = $res["ABS_LAST_POST_DATE"];
			$res["LAST_POSTER_NAME"] = $res["ABS_LAST_POSTER_NAME"];
			$res["LAST_MESSAGE_ID"] = $res["ABS_LAST_MESSAGE_ID"];
			$res["mCnt"] = intVal($res["POSTS_UNAPPROVED"]);
			$res["numMessages"] = $res["POSTS"] + $res["mCnt"];
			$res["mCntURL"] = $res["URL"]["MODERATE_MESSAGE"];
		}
		else
		{
			$res["numMessages"] = $res["POSTS"];
		}
		/*******************************************************************/
		$res["numMessages"] = $res["numMessages"] + 1;
		/*******************************************************************/
		$res["pages"] = ForumShowTopicPages($res["numMessages"], $res["URL"]["TOPIC"],
			"PAGEN_".$arParams["PAGEN"], intVal($arParams["MESSAGES_PER_PAGE"]));
		$res["PAGES_COUNT"] = intVal(ceil($res["numMessages"]/$arParams["MESSAGES_PER_PAGE"]));
		/*******************************************************************/
		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		$res["USER_START_NAME"] = $parser->wrap_long_words($res["USER_START_NAME"]);
		$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
		$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
		$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));

		/************** For custom template ********************************/
		$res["read"] = $res["URL"]["TOPIC"];
		$res["read_last_unread"] = $res["URL"]["MESSAGE_UNREAD"];
		$res["read_last_message"] = $res["URL"]["LAST_MESSAGE"];
		$res["USER_START_HREF"] = $res["URL"]["USER_START"];
		$res["LAST_POSTER_HREF"] = $res["URL"]["LAST_POSTER_HREF"];
		$res["author_profile"] = $res["URL"]["LAST_POSTER_HREF"];
		/************** For custom template/********************************/
		$arResult["Topics"][] = $res;
	}
	if (count($topicLinks) > 0)
	{
		$db_res1 = CForumTopic::GetListEx(array("SORT"=>"ASC"), array("@ID" => $topicLinks));
		$topicLinks1 = array();
		while ($res = $db_res1->GetNext())
		{
			$key = array_search($res["ID"], $topicLinks);
			if (array_key_exists($key, $arResult["Topics"]))
			{
				$arResult["Topics"][$key]["URL"] = array_merge($arResult["Topics"][$key]["URL"], array(
					"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
					"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
					"LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intVal($res["LAST_MESSAGE_ID"]))),
					"~LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intVal($res["LAST_MESSAGE_ID"]))),
					"MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "unread_mid")),
					"~MESSAGE_UNREAD" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
						array("FID" => $res["FORUM_ID"], "TID" =>  $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "unread_mid"))
				));
			}
		}
	}
/*******************************************************************
				CACHE
*******************************************************************/
	if (($PAGEN == null) && ($arParams["CACHE_TIME"] > 0) && defined("BX_COMP_MANAGED_CACHE"))
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		CForumCacheManager::SetTag($cache_path, "forum_msg_count".$arParams['FID']);
		CForumCacheManager::SetTag($cache_path, "forum_".$arParams['FID']);
		$cache->EndDataCache(array(
			"Topics" => $arResult["Topics"],
			"NAV_STRING" => $arResult['NAV_STRING'],
			"NAV_PAGE" => $arResult['NAV_PAGE'],
		));
	}
/*******************************************************************
				/ CACHE
*******************************************************************/
}

$topicCount = sizeof($arResult['Topics']);
for ($topicID = 0; $topicID < $topicCount; $topicID++)
{
	$tres = &$arResult['Topics'][$topicID];
	if (
			($tres["TopicStatus"] == "OLD") &&
			NewMessageTopic(
				$tres["FORUM_ID"],
				$tres["ID"],
				($arResult["PERMISSION"] < "Q" ? $tres["LAST_POST_DATE"] : $tres["ABS_LAST_POST_DATE"]),
				$tres["LAST_VISIT"]
			)
		)
	{
		$tres["TopicStatus"] = "NEW";
	}
}

$arResult["TOPICS"] = $arResult["Topics"];
/************** Navigation *****************************************/
if (intVal($arResult["FORUM"]["FORUM_GROUP_ID"]) > 0):
	$PARENT_ID = intVal($arResult["FORUM"]["FORUM_GROUP_ID"]);
	while ($PARENT_ID > 0)
	{
		$res = $arResult["GROUPS"][$PARENT_ID];
		$res["URL"] = array(
			"GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)),
			"~GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)));
		$arResult["GROUP_NAVIGATION"][] = $res;
		$PARENT_ID = intVal($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
	}
	$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
endif;
/************** User info ******************************************/
if ($USER->IsAuthorized()):
	$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "TOPIC_ID" => 0, "SITE_ID" => SITE_ID);
	$db_res = CForumSubscribe::GetList(array(), $arFields);
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arResult["USER"]["SUBSCRIBE"][$res["ID"]] = $res;
		} while ($res = $db_res->Fetch());
	}
endif;
/********************************************************************
				/Data
********************************************************************/
/************** For custom template ********************************/
	$arResult["CURRENT_PAGE"] = $arResult["URL"]["TOPIC_LIST"];
	$arResult["index"] = $arResult["URL"]["INDEX"];
	$arResult["topic_new"] = $arResult["URL"]["TOPIC_NEW"];
	$arResult["UserPermission"] = $arResult["PERMISSION"];
	$arParams["IsAdmin"] = CForumUser::IsAdmin() ? "Y" : "N";
	$arResult["sessid"] = bitrix_sessid_get();
/************** For custom template/********************************/
	$this->IncludeComponentTemplate();

if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["FORUM"]["NAME"]);

if ($arParams["SET_NAVIGATION"] != "N"):
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $res["URL"]["~GROUP"]);
	endforeach;
	$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"]);
endif;

?>