<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
	global $APPLICATION, $DB, $date_create_DAYS_TO_BACK, $date_create, $date_create1;
$APPLICATION->ResetException();
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
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["UID"] = intval(intVal($arParams["UID"]) > 0 ? $arParams["UID"] : $_REQUEST["UID"]);
$arParams["mode"] = mb_strtolower(($arParams["mode"] == '')? $_REQUEST["mode"] : $arParams["mode"]);
	$arParams["mode"] = (in_array($arParams["mode"], array("all", "lt", "lta")) ? $arParams["mode"] : "all");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"user_list" => "user_list.php");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (!isset($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]))
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : array($arParams["USER_FIELDS"]));
	if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
		$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["MESSAGES_PER_PAGE"] = intval((intVal($arParams["MESSAGES_PER_PAGE"]) > 0) ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intval(isset($arParams["PAGE_NAVIGATION_WINDOW"]) && intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["PATH_TO_SMILE"] = "";
	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (isset($arParams["IMAGE_SIZE"]) && intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = (isset($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = (isset($arParams["SET_NAVIGATION"]) && $arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

if ($arParams["UID"] <= 0):
	ShowError(GetMessage("F_ERROR_USER_IS_EMPTY"));
	return false;
endif;

$db_res = CForumUser::GetList(
	array(),
	array(
		"USER_ID" => $arParams["UID"],
		"SHOW_ABC" => ""),
	array(
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]));
$arResult["USER"] = ($db_res && ($res = $db_res->GetNext()) ? $res : false);

if (empty($arResult["USER"])):
	CHTTP::SetStatus("404 Not Found");
	ShowError(GetMessage("F_ERROR_USER_IS_LOST"));
	return false;
endif;

$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");

$arFilter = array(); $arForums = array();
if (isset($arParams["SHOW_FORUM_ANOTHER_SITE"]) && $arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin())
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID_RANGE"]))
	$arFilter["@ID"] = $arParams["FID_RANGE"];
if (!CForumUser::IsAdmin()):
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
	$arFilter["ACTIVE"] = "Y";
endif;

$cache_id = "forum_forums_".serialize($arFilter).(($tzOffset = CTimeZone::GetOffset()) <> 0 ? "_".$tzOffset : "");
$cache_path = $cache_path_main."forums";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arForums = CForumCacheManager::Expand($res["arForums"]);
}
$arForums = (is_array($arForums) ? $arForums : array());
if (empty($arForums))
{
	$db_res = CForumNew::GetListEx(
		array(
			"FORUM_GROUP_SORT"=>"ASC",
			"FORUM_GROUP_ID"=>"ASC",
			"SORT"=>"ASC",
			"NAME"=>"ASC"),
		$arFilter);
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
if (empty($arForums)):
	ShowError(GetMessage("F_ERROR_FORUMS_IS_LOST"));
	return false;
endif;
$arResult["FORUMS_ALL"] = $arForums;
/********************************************************************
				Default params
********************************************************************/
$arResult["user_list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_LIST"], array());
$arResult["SHOW_RESULT"] = "N";
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);

$arResult["USER"]["URL"] = array(
	"PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"~PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])));
if (!empty($arResult["USER"]["AVATAR"])):
	$arResult["USER"]["~AVATAR"] = array(
		"ID" => $arResult["USER"]["AVATAR"],
		"FILE" => CFile::GetFileArray($arResult["USER"]["AVATAR"]));
	$arResult["USER"]["~AVATAR"]["HTML"] = CFile::ShowImage($arResult["USER"]["~AVATAR"]["FILE"],
		COption::GetOptionString("forum", "avatar_max_width", 100),
		COption::GetOptionString("forum", "avatar_max_height", 100), "border=\"0\"", "", true);
	$arResult["USER"]["AVATAR"] = $arResult["USER"]["~AVATAR"]["HTML"];
endif;

$arResult["USER"]["DATE_REG"] = (!empty($arResult["USER"]["DATE_REG"]) ?
	CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($arResult["USER"]["DATE_REG"], CSite::GetDateFormat())) : $arResult["USER"]["DATE_REG"]);

$arResult["USER"]["GROUPS"] = CUser::GetUserGroup($arParams["UID"]);
$arResult["USER"]["RANK"] = CForumUser::GetUserRank($arParams["UID"], LANGUAGE_ID);

$arResult["PARSER"] = new forumTextParser(LANGUAGE_ID);
$arResult["PARSER"]->MaxStringLen = $arParams["WORD_LENGTH"];
$arResult["PARSER"]->image_params["width"] = $arResult["PARSER"]->image_params["height"] = $arParams["IMAGE_SIZE"];
$arResult["PARSER"]->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$arResult["PARSER"]->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arTopics = array();
$arTopicNeeded = array();
$forums = array(); $topics = array();
$arFilterFromForm = array();
$FilterMess = array();
$FilterMessLast = array();
$arForum_posts = array();
$arResult["MESSAGE_LIST"] = array();
$arResult["FORUMS"] = array();
$arResult["FILES"] = array();
$arResult["GROUPS_FORUMS"] = array();
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Filter *********************************************/
if (!is_set($_REQUEST, "set_filter") && !is_set($_REQUEST, "del_filter")):
	$_REQUEST["set_filter"] = "Y";
	$_REQUEST["sort"] = "message";
endif;
if (!empty($_REQUEST["set_filter"]))
{
	InitFilterEx(array("date_create", "date_create1"), "USER_LIST", "set", false);
	if (isset($_REQUEST["fid"]) && intval($_REQUEST["fid"]) > 0)
	{
		if (!empty($arResult["FORUMS_ALL"][$_REQUEST["fid"]]))
			$arFilterFromForm["fid"] = $_REQUEST["fid"];
		else
		{
			$res = reset($arResult["FORUMS_ALL"]);
			$arFilterFromForm["fid"] = $_REQUEST["fid"] = $res["ID"];
			$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_FORUM_ID"), "BAD_FORUM_ID");
		}
	}

	if (!empty($date_create) && $DB->IsDate($date_create))
		$arFilterFromForm["date_create"] = $date_create;
	elseif (!empty($date_create))
		$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_FROM");

	if (!empty($date_create1) && $DB->IsDate($date_create1))
		$arFilterFromForm["date_create1"] = $date_create1;
	elseif (!empty($date_create1))
		$APPLICATION->ThrowException(GetMessage("LU_INCORRECT_LAST_MESSAGE_DATE"), "BAD_DATE_TO");

	if (!empty($_REQUEST["topic"]))
		$arFilterFromForm["topic"] = $_REQUEST["topic"];
	if (!empty($_REQUEST["message"]))
		$arFilterFromForm["message"] = $_REQUEST["message"];
	$arFilterFromForm["sort"] = (isset($_REQUEST["sort"]) && $_REQUEST["sort"] == "topic" ? "topic" : "message");
}
elseif (!empty($_REQUEST["del_filter"]))
{
	DelFilterEx(array("date_create", "date_create1"), "USER_LIST",false);
	unset($_REQUEST["fid"]);
	unset($_REQUEST["topic"]);
	unset($_REQUEST["message"]);
}
else
	InitFilterEx(array("date_create", "date_create1"), "USER_LIST","get",false);
/*******************************************************************/
$arGroupForum = array();
foreach ($arResult["FORUMS_ALL"] as $res):
	$arGroupForum[intval($res["FORUM_GROUP_ID"])]["FORUMS"][] = $res;
endforeach;
/*******************************************************************/
$arGroups = array();
foreach ($arGroupForum as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res["FORUMS"]);
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

/*******************************************************************/
foreach ($arResult["FORUMS_ALL"] as $key => $res)
{
	$arResult["FORUMS_ALL"][$key]["ALLOW"] = forumTextParser::GetFeatures($res);
	$arResult["FORUMS_ALL"][$key]["URL"] = array(
		"FORUM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])),
		"~FORUM" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"])));
	$arResult["FORUMS_ALL"][$key]["list"] = $arResult["FORUMS_ALL"][$key]["URL"]["FORUM"];
	$arResult["FORUMS_ALL"][$key]["~list"] = $arResult["FORUMS_ALL"][$key]["URL"]["~FORUM"];
}
/************** getting list topics ********************************/
CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if ($arParams["mode"] == "lta" || $arParams["mode"] == "lt")
{
	$arFilter = array("AUTHOR_ID" => $arParams["UID"], "APPROVED" => "Y");
	$arOrder = array("FIRST_POST" => "DESC");

	if (is_set($arFilterFromForm, "fid"))
		$arFilter["FORUM_ID"] = $arFilterFromForm["fid"];
	else
		$arFilter["@FORUM_ID"] = array_keys($arResult["FORUMS_ALL"]);

	if ($arParams["mode"] == "lta"):
		$arFilter["USER_START_ID"] = $arParams["UID"];
	else:
		$arOrder = array("LAST_POST" => "DESC");
	endif;
/*******************************************************************/
	// set filters
	if (is_set($arFilterFromForm, "date_create"))
		$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
	if (is_set($arFilterFromForm, "date_create1"))
		$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
	if (is_set($arFilterFromForm, "topic"))
		$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
	if (is_set($arFilterFromForm, "message"))
		$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];

	/*$arNavParams = array(
		"bShowAll" => false,
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
	$arResult["NAV_RESULT"] = CForumUser::UserAddInfo($arOrder, $arFilter, "topics", false, false, $arNavigation);*/

	$arResult["NAV_RESULT"] = CForumUser::UserAddInfo(
		$arOrder,
		$arFilter,
		"topics",
		false,
		false,
		array(
			"bShowAll" => false,
			"bDescPageNumbering" => false,
			"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]
		));
	$arResult["NAV_RESULT"]->NavStart($arParams["MESSAGES_PER_PAGE"]);
	$arResult["NAV_RESULT"]->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];

	$arResult["NAV_STRING"] = $arResult["NAV_RESULT"]->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	if ($arResult["NAV_RESULT"] && $res = $arResult["NAV_RESULT"]->GetNext())
	{
		do
		{
			if (!isset($arForum_posts[$res["FORUM_ID"]]))
			{
				$arForum_posts[$res["FORUM_ID"]] = 0;
			}
			$arForum_posts[$res["FORUM_ID"]] += intval($res["COUNT_MESSAGE"]);
			$res = array_merge(
				$res, array(
					"ID" => $res["TOPIC_ID"],
					"URL" => array(
						"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
							array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE" => $res["TITLE_SEO"], "MID" => "s")),
						"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
							array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE" => $res["TITLE_SEO"], "MID" => "s")))
				)
			);
			$res["read"] = $res["URL"]["TOPIC"];
			$arTopics[$res["TOPIC_ID"]] = $res;
			$FilterMess[] = $res["FIRST_POST"];
			$FilterMessLast[] = $res["LAST_POST"];
		}while ($res = $arResult["NAV_RESULT"]->GetNext());
	}
}
$bEmptyResult = false;
$arFilter = array("AUTHOR_ID" => $arParams["UID"], "APPROVED" => "Y");
if ($arParams["mode"] == "lta")
{
	if (empty($FilterMess)):
		$bEmptyResult = true;
	else:
		$arFilter["@ID"] = implode(", ", $FilterMess);
	endif;
}
elseif ($arParams["mode"] == "lt")
{
	if (empty($FilterMessLast)):
		$bEmptyResult = true;
	else:
		$arFilter["@ID"] = implode(", ", $FilterMessLast);
	endif;
}
else
{
	if (is_set($arFilterFromForm, "fid"))
		$arFilter["FORUM_ID"] = $arFilterFromForm["fid"];
	else
		$arFilter["@FORUM_ID"] = array_keys($arResult["FORUMS_ALL"]);
	if (is_set($arFilterFromForm, "date_create"))
		$arFilter[">=POST_DATE"] = $arFilterFromForm["date_create"];
	if (is_set($arFilterFromForm, "date_create1"))
		$arFilter["<=POST_DATE"] = $arFilterFromForm["date_create1"];
	if (is_set($arFilterFromForm, "topic"))
		$arFilter["%TOPIC_TITLE"] = $arFilterFromForm["topic"];
	if (is_set($arFilterFromForm, "message"))
		$arFilter["%POST_MESSAGE"] = $arFilterFromForm["message"];
}
// set filter
$arSort = array("POST_DATE" => "DESC");
if (empty($arResult["NAV_RESULT"]) && $arFilterFromForm["sort"] == "topic")
	$arSort = array("TOPIC_ID" => "DESC", "POST_DATE" => "DESC");
if (!$bEmptyResult):
$db_res = CForumMessage::GetListEx(
	$arSort,
	$arFilter,
	false,
	false,
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["MESSAGES_PER_PAGE"],
		"bShowAll" => false,
		"sNameTemplate" => $arParams["NAME_TEMPLATE"]
	)
);
$db_res->NavStart($arParams["MESSAGES_PER_PAGE"],false);
$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
if (empty($arResult["NAV_RESULT"]))
{
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("LU_TITLE_POSTS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
}

if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
/************** Message info ***************************************/
	// data
	$res["POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
	$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
	// text
	$res["ALLOW"] = array_merge(
		$arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW"],
		array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW_SMILES"] : "N")));
	$res["~POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
	// attach
	$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
	$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
/************** Message info/***************************************/
/************** Author info ****************************************/
	$res["AUTHOR_ID"] = intval($res["AUTHOR_ID"]);
	$res["AVATAR"] = $arResult["USER"]["AVATAR"];
	$res["~AVATAR"] = $arResult["USER"]["~AVATAR"];
	// data
	$res["DATE_REG"] = $arResult["USER"]["DATE_REG"];
	// Another data
	$res["AUTHOR_NAME"] = $arResult["PARSER"]->wrap_long_words($res["AUTHOR_NAME"]);
	$res["DESCRIPTION"] = $arResult["PARSER"]->wrap_long_words($res["DESCRIPTION"]);
	$res["SIGNATURE"] = "";
	if ($arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW_SIGNATURE"] == "Y" && $res["~SIGNATURE"] <> '')
		$res["SIGNATURE"] = $arResult["PARSER"]->convert($res["~SIGNATURE"], array_merge($arResult["FORUMS_ALL"][$res["FORUM_ID"]]["ALLOW"], array("SMILES" => "N")));
	$res["FOR_JS"] = array(
		"AUTHOR_NAME" => Cutil::JSEscape(htmlspecialcharsbx($res["~AUTHOR_NAME"])),
		"POST_MESSAGE" => Cutil::JSEscape(htmlspecialcharsbx($res["~POST_MESSAGE_TEXT"])));
/************** Author info/****************************************/
	if (empty($arTopics[$res["TOPIC_ID"]]))
		$arTopicNeeded[$res["TOPIC_ID"]] = $res["TOPIC_ID"];
	$topics[$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
	$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
	}while ($res = $db_res->GetNext());
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
				$res["TOPIC_ID"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["TOPIC_ID"];
				$res["FORUM_ID"] = $arResult["MESSAGE_LIST"][$res["MESSAGE_ID"]]["FORUM_ID"];
			// attach for custom
				$topics[$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["ATTACH_IMG"] =  CFile::ShowFile($res["FILE_ID"], 0,
					$arParams["IMAGE_SIZE"], $arParams["IMAGE_SIZE"], true, "border=0", false);
				$topics[$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
			}
			$topics[$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
			$arResult["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
	if (!empty($arParams["USER_FIELDS"]))
	{
		$db_props = CForumMessage::GetList(array("ID" => "ASC"), array("@ID" => array_keys($arResult["MESSAGE_LIST"])), false, 0, array("SELECT" => $arParams["USER_FIELDS"]));
		while ($db_props && ($res = $db_props->Fetch()))
		{
			$props = array_intersect_key($res, array_flip($arParams["USER_FIELDS"]));
			$arResult["MESSAGE_LIST"][$res["ID"]]["PROPS"] = $topics[$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["PROPS"] = $props;
			$arResult["MESSAGE_LIST"][$res["ID"]]["ALLOW"] = array_merge( $arResult["MESSAGE_LIST"][$res["ID"]]["ALLOW"], array("USERFIELDS" => $props) );
		}
	}
	/************** Message info ***************************************/
/* This is needed for parsing attachments in text such as [file=ID]*/
/* And second loop whith messages array is more economy-way ********/
	$arResult["PARSER"]->arFiles = $arResult["FILES"];
	foreach ($arResult["MESSAGE_LIST"] as $iID => $res)
	{
		$topics[$res["TOPIC_ID"]]["MESSAGES"][$iID]["POST_MESSAGE_TEXT"] = $arResult["MESSAGE_LIST"][$iID]["POST_MESSAGE_TEXT"] =
			$arResult["PARSER"]->convert($res["~POST_MESSAGE_TEXT"], $res["ALLOW"]);
	}
}

/************** Message List/***************************************/
if (!empty($arTopicNeeded))
{
	$db_res = CForumUser::UserAddInfo(array(), array("@TOPIC_ID" => implode(",", $arTopicNeeded), "AUTHOR_ID" => $arParams["UID"]), false, false, false);
	if ($db_res && $res = $db_res->GetNext())
	{
		do
		{
			$arTopics[$res["TOPIC_ID"]] = $res;
		}while ($res = $db_res->GetNext());
	}
}
foreach ($topics as $topic_id => $res)
{
	$forum_id = intval($arTopics[$topic_id]["FORUM_ID"]);
	if (!array_key_exists($forum_id, $forums))
	{
		$UserPermStr = ""; $UserPermCode = "";
		$UserPerm = CForumNew::GetUserPermission($forum_id, $arResult["USER"]["GROUPS"]);
		list($UserPermCode, $UserPermStr) = ForumGetUserForumStatus($arParams["UID"], $UserPerm, $arResult["USER"]["RANK"]);
		$forums[$forum_id] = array_merge(
			$arResult["FORUMS_ALL"][$forum_id],
			array(
				"NUM_POSTS_ALL" => isset($arForum_posts[$forum_id]) ? $arForum_posts[$forum_id] : null,
				"PERMISSION" => $UserPerm, "USER_PERM" => $UserPerm,
				"AUTHOR_STATUS" => $UserPermStr, "USER_PERM_STR" => $UserPermStr,
				"AUTHOR_STATUS_CODE" => $UserPermCode,
				"TOPICS" => array() ) );
	}
	$arTopics[$topic_id]["TITLE"] = $arResult["PARSER"]->wrap_long_words($arTopics[$topic_id]["TITLE"]);
	$arTopics[$topic_id]["DESCRIPTION"] = $arResult["PARSER"]->wrap_long_words($arTopics[$topic_id]["DESCRIPTION"]);
	$arTopics[$topic_id]["URL"] = array(
		"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
				array("FID" => $arTopics[$topic_id]["FORUM_ID"], "TID" => $arTopics[$topic_id]["TOPIC_ID"], "TITLE_SEO" => $arTopics[$topic_id]["TOPIC_ID"], "MID" => "s")),
		"~TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
				array("FID" => $arTopics[$topic_id]["FORUM_ID"], "TID" => $arTopics[$topic_id]["TOPIC_ID"], "TITLE_SEO" => $arTopics[$topic_id]["TOPIC_ID"], "MID" => "s")),
	);

	/************** For custom templates *******************************/
	$arTopics[$topic_id]["read"] = $arTopics[$topic_id]["URL"]["TOPIC"];
	/************** For custom templates *******************************/
	$forums[$forum_id]["TOPICS"][$topic_id] = $topics[$topic_id] = array_merge($arTopics[$topic_id], $res);
}
/*******************************************************************/
/************** Urls ***********************************************/
	foreach ($arResult["MESSAGE_LIST"] as $iID => $res)
	{
		$topic = $arTopics[$topic_id];
		$res["URL"] = array(
			"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $topic["TOPIC_ID"], "TITLE_SEO" => $topic["TITLE_SEO"], "MID" => $res["ID"])),
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
			$res["URL"]["~AUTHOR_VOTE"] = ForumAddPageParams(
				$res["URL"]["MESSAGE"],
				array(
					"UID" => $res["AUTHOR_ID"], "MID" => $res["ID"],
					"VOTES" => $arResult["USER"]["RANK"] ? intval($arResult["USER"]["RANK"]["VOTES"]) : 0,
					"VOTES_TYPE" => (isset($res["VOTING"]) && $res["VOTING"] == "VOTE" ? "V" : "U"),
					"ACTION" => "VOTE4USER"
				)
			);
			$res["URL"]["AUTHOR_VOTE"] = $res["URL"]["~AUTHOR_VOTE"]."&amp;".bitrix_sessid_get();
	/************** For custom templates *******************************/
		$topics[$res["TOPIC_ID"]]["MESSAGES"][$iID]["URL"] = $arResult["MESSAGE_LIST"][$iID]["URL"] = $res["URL"];
		$topics[$res["TOPIC_ID"]]["MESSAGES"][$iID]["read"] = $arResult["MESSAGE_LIST"][$iID]["read"] = $res["URL"]["MESSAGE"];
	}
endif;

if ($APPLICATION->GetException())
{
	$err = $APPLICATION->GetException();
	$arResult["ERROR_MESSAGE"] .= $err->GetString();
}

$arResult["SHOW_RESULT"] = (!empty($forums) ? "Y" : "N");
$arResult["FORUMS"] = $forums; $arResult["TOPICS"] = $topics;
$arResult["USER"]["profile_view"] = $arResult["USER"]["URL"]["PROFILE"];
$arResult["USER"]["~profile_view"] = $arResult["USER"]["URL"]["~PROFILE"];
/********************************************************************
				/Data
********************************************************************/
	$this->IncludeComponentTemplate();
if (mb_strtolower($arParams["mode"]) == "lta")
	$Title = GetMessage("LU_TITLE_LTA");
elseif (mb_strtolower($arParams["mode"]) == "lt")
	$Title = GetMessage("LU_TITLE_LT");
else
	$Title = GetMessage("LU_TITLE_ALL");

if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem($arResult["USER"]["SHOW_ABC"], $arResult["USER"]["~profile_view"]);
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arResult["USER"]["SHOW_ABC"]." (".$Title.")");
/*******************************************************************/
?>
