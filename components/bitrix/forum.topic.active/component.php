<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if (!function_exists("CheckLastTopicsFilter"))
{
	function CheckLastTopicsFilter()
	{
		global $DB, $strError, $FilterArr, $MESS;
		foreach ($FilterArr as $s) global ${$s};
		$str = "";
		if ($find_date1 <> '' && !$DB->IsDate($find_date1)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
		elseif ($find_date2 <> '' && !$DB->IsDate($find_date2)) $str .= GetMessage("FL_INCORRECT_LAST_MESSAGE_DATE")."<br>";
		$strError .= $str;
		return (empty($str) ? true : false);
	}
}
if (!function_exists("__array_merge"))
{
	function __array_merge($arr1, $arr2)
	{
		$arResult = $arr1;
		foreach ($arr2 as $key2 => $val2)
		{
			if (!array_key_exists($key2, $arResult))
			{
				$arResult[$key2] = $val2;
				continue;
			}
			elseif ($val2 == $arResult[$key2])
				continue;
			elseif (!is_array($arResult[$key2]))
				$arResult[$key2] = array($arResult[$key2]);
			$arResult[$key2] = __array_merge($arResult[$key2], $val2);
		}
		return $arResult;
	}
}

global $by, $order, $FilterArr, $strError, $find_date1, $find_date1_DAYS_TO_BACK, $find_date2;
$strError = ""; 
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval((intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = (intval($arParams["PAGEN"]) <= 0 ? 1 : intval($arParams["PAGEN"]));
	$arParams["MESSAGES_PER_PAGE"] = intval(empty($arParams["MESSAGES_PER_PAGE"]) ? COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10") : $arParams["MESSAGES_PER_PAGE"]);
	$arParams["TOPICS_PER_PAGE"] = intval(empty($arParams["TOPICS_PER_PAGE"]) ? COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : $arParams["TOPICS_PER_PAGE"]);
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intval(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
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

/********************************************************************
				Default values
********************************************************************/
$arResult["FORUM"] = array();
$arResult["FORUMS"] = array();
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["GROUPS_FORUMS"] = array();
$arResult["TOPICS"] = array();
$arResult["SHOW_RESULT"] = "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["SortingEx"] = array(
	"TITLE" => SortingEx("TITLE"), 
	"FORUM_ID" => SortingEx("FORUM_ID"), 
	"USER_START_NAME" => SortingEx("USER_START_NAME"), 
	"POSTS" => SortingEx("POSTS"), 
	"VIEWS" => SortingEx("VIEWS"), 
	"LAST_POST_DATE" => SortingEx("LAST_POST_DATE"));

$parser = new forumTextParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
$arFilter = array();
$arForums = array();

$by = (is_set($arResult["SortingEx"], $by) ? $by : "LAST_POST_DATE");
$order = ($order != "asc" ? "desc" : "asc");
/************** Filter *********************************************/
$FilterArr = array("find_date1", "find_date2", "find_forum");
$set_default = (!is_set($_REQUEST, "find_forum") ? (empty($_SESSION["SESS_ADMIN"]["LAST_TOPICS_LIST"]) ? "Y" : "N") : "N");
$set_filter = (is_set($_REQUEST, "set_filter") || $set_default == "Y" ? "set" : "get");
$find_date1 = $_REQUEST["find_date1"];
$find_date2 = $_REQUEST["find_date2"];
$find_forum = intval($_REQUEST["find_forum"]);
$find_date1_DAYS_TO_BACK = intval($set_default == "Y" ? 2 : $find_date1_DAYS_TO_BACK);
InitFilterEx($FilterArr, "LAST_TOPICS_LIST", $set_filter, true);
if (!empty($_REQUEST["del_filter"])): 
	DelFilterEx($FilterArr, "LAST_TOPICS_LIST", true);
endif;
$find_date1 = $GLOBALS["find_date1"];
$find_date2 = $GLOBALS["find_date2"];
$find_forum = $GLOBALS["find_forum"] = intval($GLOBALS["find_forum"]);
$find_date1_DAYS_TO_BACK = $GLOBALS["find_date1_DAYS_TO_BACK"];
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
/************** Forums *********************************************/
$arFilter = array();
if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin())
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID_RANGE"]))
	$arFilter["@ID"] = $arParams["FID_RANGE"];
if (!CForumUser::IsAdmin()):
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
	$arFilter["ACTIVE"] = "Y";
endif;

$cache_id = "forum_forums_".serialize($arFilter);
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
$cache_path = $cache_path_main."forums";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arForums = CForumCacheManager::Expand($res["arForums"]);
}
$arForums = (is_array($arForums) ? $arForums : array());
if (empty($arForums))
{
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$arForums[$res["ID"]] = $res;
		} while ($res = $db_res->GetNext());
	}
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arForums" => CForumCacheManager::Compress($arForums)));
	endif;
}

$arForumsID = array_keys($arForums);
$arResult["FORUMS"] = $arForums;
if (empty($arResult["FORUMS"])):
	ShowError(GetMessage("F_NO_FORUMS"));
	return false;
endif;

$arGroupForum = array();
$arGroups = array();
foreach ($arForums as $res)
	$arGroupForum[intval($res["FORUM_GROUP_ID"])]["FORUM"][] = $res;
foreach ($arGroupForum as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res["FORUM"]);
	while ($PARENT_ID > 0) 
	{
		if (!array_key_exists($PARENT_ID, $arResult["GROUPS"]))
		{
			$bResult = false;
			$PARENT_ID = false;
			break;
		}
		$res = array($PARENT_ID => __array_merge($arResult["GROUPS"][$PARENT_ID], $res));
		$PARENT_ID = $arResult["GROUPS"][$PARENT_ID]["PARENT_ID"];
		$res = array("GROUPS" => $res);
		if ($PARENT_ID > 0)
			$res = __array_merge($arResult["GROUPS"][$PARENT_ID], $res);
	}
	if ($bResult == true)
		$arGroups = __array_merge($arGroups, $res);
}
$arResult["GROUPS_FORUMS"] = $arGroups;	
/************** Topic list of forum ********************************/
$arFilter = array("@FORUM_ID" => array_keys($arResult["FORUMS"]));
if (!CForumUser::IsAdmin()):
	$arFilter["PERMISSION"] = $USER->GetGroups();
endif;
$find_forum = (!empty($arResult["FORUMS"][$find_forum]) ? $find_forum : 0);
if (intval($find_forum) > 0):
	$arFilter["FORUM_ID"] = intval($find_forum);
endif;
if (CheckLastTopicsFilter()):
	if (intval($find_date1) > 0)
		$arFilter[">=LAST_POST_DATE"] = $find_date1;
	if (intval($find_date2) > 0)
		$arFilter["<=LAST_POST_DATE"] = GetTime(AddToTimeStamp(array("DD"=>1), CTimeZone::GetOffset()+MakeTimeStamp($find_date2, CLang::GetDateFormat('FULL', false)))); 
endif;
if ($USER->IsAuthorized()):
	$arFilter["RENEW"] = $USER->GetID();
else: 
	//604800 = 7*24*60*60;
	$arFilter[">LAST_POST_DATE"] = ConvertTimeStamp((time()-604800+CTimeZone::GetOffset()), "FULL");
	
/*foreach ($arForums as $key => $val)
{
	$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]);
	if ($arForums[$key]["LAST_VISIT"] < intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]))
		$arForums[$key]["LAST_VISIT"] = intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".intVal($key)]);
}
*/
	
endif;

/********************************************************************
				Action
********************************************************************/
if ($_REQUEST["ACTION"] == "SET_BE_READ")
{
	if (!$GLOBALS["USER"]->IsAuthorized()):
	elseif (!check_bitrix_sessid()):
	elseif ($_REQUEST["FID"] == "all"):
		ForumSetReadForum(false);
	elseif (intval($_REQUEST["FID"]) > 0 && $_REQUEST["FID"] == $find_forum):
		ForumSetReadForum($_REQUEST["FID"]);
	elseif (!empty($_REQUEST["TID"])):
		$arFilterAction = $arFilter;
		$arFilterAction["@ID"] = $_REQUEST["TID"];
		$db_res = CForumTopic::GetListEx(array($by => $order, "POSTS" => "DESC"), $arFilterAction, false, 0, array('NoFilter' => true));
		if ($db_res && $res = $db_res->Fetch()):
			do 
			{
				$GLOBALS["FORUM_CACHE"]["TOPIC"][$res["ID"]] = $res;
				CForumTopic::SetReadLabelsNew($res["ID"], false, false, array("UPDATE_TOPIC_VIEWS" => "N"));
			} while ($res = $db_res->Fetch());
		endif;
		$url = $APPLICATION->GetCurPageParam("", array("ACTION", "sessid", "TID", "find_forum", "find_date1", "find_date1_DAYS_TO_BACK", "find_date2", 
			"set_filter", "del_filter"));
		LocalRedirect($url);
	endif;
}
/********************************************************************
				/Action
********************************************************************/

/*******************************************************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if (!$USER->IsAuthorized()):
	$rsTopics = CForumTopic::GetListEx(
		array(
			$by => $order,
			"POSTS" => "DESC"),
		$arFilter,
		false,
		500,
		array(
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]
		)
	);
	while ($arTopic = $rsTopics->Fetch())
	{
		if (!NewMessageTopic($arTopic["FORUM_ID"], $arTopic["ID"], $arTopic["LAST_POST_DATE"], false))
			continue; 
		$arrTOPICS[] = $arTopic;
	}
	$rsTopics = new CDBResult;
	$rsTopics->InitFromArray($arrTOPICS);
else:
	$rsTopics = CForumTopic::GetListEx(
		array(
			$by => $order,
			"POSTS" => "DESC"),
		$arFilter,
		false,
		0,
		array(
			"bDescPageNumbering" => false,
			"nPageSize" => $arParams["TOPICS_PER_PAGE"],
			"bShowAll" => false,
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]
		)
	);
endif;
$rsTopics->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$rsTopics->NavStart($arParams["TOPICS_PER_PAGE"], false);
$arResult["NAV_RESULT"] = $rsTopics;
$arResult["NAV_STRING"] = $rsTopics->GetPageNavStringEx($navComponentObject, GetMessage("FL_TOPIC_LIST"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
while ($res = $rsTopics->GetNext())
{
	if (!$USER->IsAuthorized()):
		$res["PERMISSION"] = ForumCurrUserPermissions($res["FORUM_ID"]);
//	elseif ($res["PERMISSION"] >= "Q"):
	endif;
/*******************************************************************/
	$res["URL"] = array(
		"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
		"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"],  "MID" => "s")),
		"LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))),
		"~LAST_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intval($res["LAST_MESSAGE_ID"]))),
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
			array("UID" => $res["LAST_POSTER_ID"])));
	$res["TopicStatus"] = "NEW";
/*******************************************************************/
	if($res["PERMISSION"] >= "Q"):
		$res["LAST_POSTER_ID"] = $res["ABS_LAST_POSTER_ID"];
		$res["LAST_POST_DATE"] = $res["ABS_LAST_POST_DATE"];
		$res["LAST_POSTER_NAME"] = $res["ABS_LAST_POSTER_NAME"];
		$res["LAST_MESSAGE_ID"] = $res["ABS_LAST_MESSAGE_ID"];
		$res["mCnt"] = intval($res["POSTS_UNAPPROVED"]);
		$res["numMessages"] = $res["POSTS"] + $res["mCnt"];
		$res["mCntURL"] = $res["URL"]["MODERATE_MESSAGE"];
	else:
		$res["numMessages"] = $res["POSTS"];
	endif;
/*******************************************************************/
	$res["numMessages"] = $res["numMessages"] + 1;
/*******************************************************************/
	$res["PAGES_COUNT"] = intval(ceil($res["numMessages"]/$arParams["MESSAGES_PER_PAGE"]));
/*******************************************************************/
	$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
	$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
	$res["USER_START_NAME"] = $parser->wrap_long_words($res["USER_START_NAME"]);
	$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
	$res["LAST_POST_DATE_FORMATED"] = $res["LAST_POST_DATE"];
	$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
	$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));
/************** For custom template ********************************/
	if ($res["APPROVED"] != "Y")
		$res["Status"] = "NA";
	$res["LAST_POSTER_HREF"] = $res["URL"]["LAST_POSTER"];
	$res["USER_START_HREF"] = $res["URL"]["USER_START"];
	$res["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["FORUM_ID"]));
	$res["read"] = $res["URL"]["TOPIC"];
	$res["read_unread"] = $res["URL"]["MESSAGE_UNREAD"];
	$res["read_last_message"] = $res["URL"]["LAST_MESSAGE"]; 
	$res["UserPermission"] = $res["PERMISSION"];
	$res["image_prefix"] = ($res["STATE"]!="Y") ? "closed_" : "";
	$res["ForumShowTopicPages"] = ForumShowTopicPages($res["numMessages"], $res["read"], "PAGEN_".$arParams["PAGEN"], 
		intval($arParams["MESSAGES_PER_PAGE"]));
/************** For custom template/********************************/
	$arResult["TOPICS"][$res["ID"]] = $res;
}
/*******************************************************************/
$arResult["PAGE_NAME"] = "active";
$arResult["find_forum"]["data"] = $arForums;
$arResult["find_forum"]["active"] = $find_forum;
$arResult["find_date1"] = CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y", "", "");
/*******************************************************************/
$arResult["ERROR_MESSAGE"] = $strError;
/*******************************************************************/
$arResult["SHOW_RESULT"] = (empty($arResult["TOPICS"]) ? "N" : "Y");
/********************************************************************
				/Data
********************************************************************/
/*******************************************************************/
	$this->IncludeComponentTemplate();
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem(GetMessage("F_TITLE"));
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("F_TITLE"));
?>
