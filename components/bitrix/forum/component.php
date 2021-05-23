<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!IsModuleInstalled("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/***************** URL *********************************************/
/***************** TAGS ********************************************/
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$componentPage = "index";
$arResult = array();

$arParams["SHOW_FORUM_USERS"] = ($arParams["SHOW_FORUM_USERS"] == "N" ? "N" : "Y");

$arDefaultUrlTemplates404 = array(
	"active" => "topic/new/",
	"forums" => "group#GID#/",
	"help" => "help/",
	"index" => "index.php",
	"list" => "forum#FID#/",
	"message" => "messages/forum#FID#/topic#TID#/message#MID#/",
	"message_small" => "forum#FID#/topic#TID#/message#MID#/",
	"message_appr" => "messages/approve/forum#FID#/topic#TID#/",
	"message_move" => "messages/move/forum#FID#/topic#TID#/message#MID#/",
	"message_send" => "user/#UID#/send/#TYPE#/",
	"pm_list" => "pm/folder#FID#/",
	"pm_edit" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
	"pm_read" => "pm/folder#FID#/message#MID#/",
	"pm_search" => "pm/search/",
	"pm_folder" => "pm/folders/",
	"profile" => "user/#UID#/edit/",
	"profile_view" => "user/#UID#/",
	"read" => "forum#FID#/topic#TID#/",
	"rules" => "rules.php",
	"rss" => "rss/#TYPE#/#MODE#/#IID#/",
	"search" => "search/",
	"subscr_list" => "subscribe/",
	"topic_move" => "topic/move/forum#FID#/topic#TID#/",
	"topic_new" => "topic/add/forum#FID#/",
	"topic_search" => "topic/search/",
	"user_list" => "users/",
	"user_post" => "user/#UID#/post/#mode#/",
);

$arDefaultVariableAliasesForPages = Array(
	"active" => array("PAGE_NAME" => "PAGE_NAME"),
	"forums" => array("PAGE_NAME" => "PAGE_NAME", "GID" => "GID"),
	"help" => array("PAGE_NAME" => "PAGE_NAME"),
	"index" => array("PAGE_NAME" => "PAGE_NAME"),
	"list" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"message" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "TITLE_SEO" => "TITLE_SEO", "MID" => "MID"),
	"message_small" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "TITLE_SEO" => "TITLE_SEO", "MID" => "MID"),
	"message_appr" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID"),
	"message_move" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "MID" => "MID"),
	"message_send" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID", "TYPE" => "TYPE"),
	"pm_list" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"pm_edit" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "MID" => "MID", "UID" => "UID", "mode" => "mode"),
	"pm_read" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "MID" => "MID"),
	"pm_search" => array("PAGE_NAME" => "PAGE_NAME"),
	"pm_folder" => array("PAGE_NAME" => "PAGE_NAME"),
	"profile" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID"),
	"profile_view" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID"),
	"read" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "TITLE_SEO" => "TITLE_SEO"),
	"rules" => array("PAGE_NAME" => "PAGE_NAME"),
	"rss" => array("PAGE_NAME" => "PAGE_NAME", "IDD" => "IID", "TYPE" => "TYPE", "MODE" => "MODE"),
	"search" => array("PAGE_NAME" => "PAGE_NAME"),
	"subscr_list" => array("PAGE_NAME" => "PAGE_NAME"),
	"topic_move" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID"),
	"topic_new" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"topic_search" => array("PAGE_NAME" => "PAGE_NAME"),
	"user_list" => array("PAGE_NAME" => "PAGE_NAME"),
	"user_post" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID", "mode" => "mode")
);

$arDefaultVariableAliases404 = Array();
$arDefaultVariableAliases = Array(
	"ACTION" => "ACTION",
	"COUNT" => "COUNT",
	"FID" => "FID",
	"FORUM_RANGE" => "FORUM_RANGE",
	"GID" => "GID", // Group forums ID
	"IDD" => "IID",
	"MID" => "MID",
	"mode" => "mode",
	"MODE" => "MODE",
	"PAGE_NAME" => "PAGE_NAME",
	"TID" => "TID",
	"TITLE_SEO" => "TITLE_SEO",
	"TYPE" => "TYPE",
	"UID" => "UID");
$arComponentVariables = Array(
	"ACTION",
	"COUNT",
	"FID",
	"FORUM_RANGE",
	"GID",
	"IID",
	"MID",
	"mode",
	"MODE",
	"PAGE_NAME",
	"TID",
	"TITLE_SEO",
	"TYPE",
	"UID");
$arVariables = array();
/********************************************************************
				Default params
********************************************************************/

$arAuthPageParams = array("login", "logout", "register", "forgot_password", "change_password", "auth");
if (($_REQUEST["auth"]=="yes" || $_REQUEST["register"] == "yes" ||  $_REQUEST["login"] == "yes") &&
	$USER->IsAuthorized() || $_REQUEST["logout"] == "yes")
{
	LocalRedirect($APPLICATION->GetCurPageParam("", $arAuthPageParams));
}
elseif ($arParams["SHOW_AUTH_FORM"] != "N")
{
	foreach ($arAuthPageParams as $key):
		if (is_set($_REQUEST, $key)):
			$this->IncludeComponentTemplate("auth");
			return false;
		endif;
	endforeach;
}

/********************************************************************
				Data
********************************************************************/
if ($arParams["SEF_MODE"] == "Y")
{
	if (!function_exists("CheckPathParams")):
		function CheckPathParams($url, $params, $Aliases)
		{
			$params = (is_array($params) ? $params : array());
			foreach ($params as $val):
				if ($val == "PAGE_NAME")
				{
					continue;
				}
				if (in_array("TITLE_SEO", $params) && ($val == "TID" || $val == "TITLE_SEO"))
				{
					if (mb_strpos($url, "#TID#") === false && mb_strpos($url, "#TITLE_SEO#") === false)
					{
						return false;
					}
					continue;
				}
				$val = (!empty($Aliases[$val]) ? $Aliases[$val] : $val);
				if (mb_strpos($url, "#".$val."#") === false):
					return false;
				endif;
			endforeach;
			return true;
		}
	endif;
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	if ($arParams["CHECK_CORRECT_TEMPLATES"] != "N"):
		foreach ($arUrlTemplates as $url => $value)
		{
			if (!CheckPathParams($arUrlTemplates[$url], $arDefaultVariableAliasesForPages[$url], $arVariableAliases[$url]))
				$arUrlTemplates[$url] = $arDefaultUrlTemplates404[$url];
		}
	endif;
	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);
	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	foreach ($arUrlTemplates as $url => $value)
	{
		if (empty($arUrlTemplates[$url]))
		{
			$arResult["URL_TEMPLATES_".mb_strtoupper($url)] = $arParams["SEF_FOLDER"].$arDefaultUrlTemplates404[$url];
		}
		elseif (mb_substr($arUrlTemplates[$url], 0, 1) == "/" || mb_substr($arUrlTemplates[$url], 0, 4) == "http")
			$arResult["URL_TEMPLATES_".mb_strtoupper($url)] = $arUrlTemplates[$url];
		else
			$arResult["URL_TEMPLATES_".mb_strtoupper($url)] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
	}

	if ($arParams["SEF_MODE_NSEF"] == "Y" && (empty($componentPage) || $componentPage == "index") && !empty($_REQUEST["PAGE_NAME"]))
	{
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, array());
		CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	foreach ($arDefaultVariableAliasesForPages as $url => $value)
	{
		$arURL = array("PAGE_NAME" => $url); unset($value["PAGE_NAME"]);
		foreach($value as $k => $v)
		{
			$arURL[$arVariableAliases[$k]] = "#".$v."#";
		}
		$arResult["URL_TEMPLATES_".mb_strtoupper($url)] = $APPLICATION->GetCurPageParam(str_replace("%23", "#", http_build_query($arURL)),
			array_merge($arVariableAliases, array("sessid", "result", "MESSAGE_TYPE", "PAGEN_".($GLOBALS["NavNum"] + 1))));
	}
}

if (!empty($arVariables["PAGE_NAME"]))
{
	$componentPage = mb_strtolower($arVariables["PAGE_NAME"]);
}

$bFounded = false;
if (in_array($componentPage, array("message", "message_small"))):
	$componentPage = "read";
	$bFounded = true;
elseif (($componentPage == 'user_list') && ($arParams['SHOW_FORUM_USERS'] !== 'Y')):
	$componentPage = "index";
	$bFounded = true;
elseif (in_array($componentPage, array("forums"))):
	$componentPage = "index";
	$bFounded = true;
elseif ($componentPage && array_key_exists($componentPage, $arDefaultUrlTemplates404)):
	$bFounded = true;
else:
	$componentPage = "index";
endif;
$arVariables["PAGE_NAME"] = $componentPage;

if (!$bFounded)
{
	$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
	if ($folder404 != "/")
		$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
	if (mb_substr($folder404, -1) == "/")
		$folder404 .= "index.php";

	if($folder404 != $APPLICATION->GetCurPage(true))
		CHTTP::SetStatus("404 Not Found");
}

$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases,
		"PAGE_NAME" => $arVariables["PAGE_NAME"],
		"FID" => ($arVariables["PAGE_NAME"] == "index") ? $arParams["FID"] : $arVariables["FID"],
		"GID" => $arVariables["GID"],
		"TID" => $arVariables["TID"],
		"TITLE_SEO" => $arVariables["TITLE_SEO"],
		"MID" => $arVariables["MID"],
		"UID" => $arVariables["UID"],
		"IID" => $arVariables["IID"],
		"ACTION" => $arVariables["ACTION"],
		"TYPE" => $arVariables["TYPE"],
		"mode" => $arVariables["mode"],
		"MODE" => $arVariables["MODE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_PAGE_PROPERTY" => $arParams["SET_PAGE_PROPERTY"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DATE_FORMAT" => $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"FORUMS_PER_PAGE" => $arParams["FORUMS_PER_PAGE"],
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"PATH_TO_AUTH_FORM" => $arParams["PATH_TO_AUTH_FORM"],
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"],
		"SHOW_FORUMS_LIST" => $arParams["SHOW_FORUMS_LIST"],
		"HELP_CONTENT" => $arParams["HELP_CONTENT"],
		"RULES_CONTENT" => $arParams["RULES_CONTENT"],
		),
	$arResult);
// BASE
$arParams["FID"] = (is_array($arParams["FID"]) ? $arParams["FID"] : array());
if ($arResult["TID"] <= 0 && $arResult["TITLE_SEO"] <> '')
	$arResult["TID"] = intval(strtok($arResult["TITLE_SEO"], "-"));
//$arParams["TID"] - topic id
//$arParams["MID"] - message id || message id (pm)
//$arParams["UID"] - user id
//$arParams["HELP_CONTENT"]
//$arParams["RULES_CONTENT"]
$arParams["TIME_INTERVAL_FOR_USER_STAT"] = intval($arParams["TIME_INTERVAL_FOR_USER_STAT"]);
$arParams["TIME_INTERVAL_FOR_USER_STAT"] = ($arParams["TIME_INTERVAL_FOR_USER_STAT"] > 0 ? $arParams["TIME_INTERVAL_FOR_USER_STAT"] : 60) / 60;
$arParams["USE_DESC_PAGE_TOPIC"] = ($arParams["USE_DESC_PAGE_TOPIC"] == "N" ? "N" : "Y");
$arParams["RSS_FID_RANGE"] = (!is_array($arParams["RSS_FID_RANGE"]) ? array() : $arParams["RSS_FID_RANGE"]);
$arParams["RSS_FID_RANGE"] = (empty($arParams["RSS_FID_RANGE"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
//
// URL
//$arParams["SEF_MODE"]
//$arParams["SEF_FOLDER"]

// ADDITIONAL
// Serch page
//$arParams["CHECK_DATES"]
//$arParams["TAGS_SORT"]
//$arParams["TAGS_INHERIT"]
//$arParams["FONT_MAX"]
//$arParams["FONT_MIN"]
//$arParams["COLOR_NEW"]
//$arParams["COLOR_OLD"]
//$arParams["PERIOD_NEW_TAGS"]
//$arParams["SHOW_CHAIN"]
//$arParams["COLOR_TYPE"]
//$arParams["WIDTH"]
//$arParams["RESTART"]

$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] == "Y" ? "Y" : "N");


//$arParams["DATE_FORMAT"],
//$arParams["DATE_TIME_FORMAT"],
//$arParams["FORUMS_PER_PAGE"],
//$arParams["TOPICS_PER_PAGE"],
//$arParams["MESSAGES_PER_PAGE"],

if (!isset($arParams["ATTACH_MODE"]))
{
	if (intval($arParams["IMAGE_SIZE"]) > 0)
	{
		$arParams["ATTACH_MODE"] = array("THUMB", "NAME");
		$arParams["ATTACH_SIZE"] = $arParams["IMAGE_SIZE"];
	}
	else
	{
		$arParams["ATTACH_MODE"] = array("NAME");
		$arParams["ATTACH_SIZE"] = 0;
	}
}
$arParams["IMAGE_SIZE"] = intval(intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 500);
$arParams["ATTACH_MODE"] = (is_array($arParams["ATTACH_MODE"]) ? $arParams["ATTACH_MODE"] : array("NAME"));
$arParams["ATTACH_MODE"] = (!in_array("NAME", $arParams["ATTACH_MODE"]) && !in_array("THUMB", $arParams["ATTACH_MODE"]) ? array("NAME") : $arParams["ATTACH_MODE"]);
$arParams["ATTACH_SIZE"] = intval(intVal($arParams["ATTACH_SIZE"]) > 0 ? $arParams["ATTACH_SIZE"] : 90);
if (!array_key_exists("USER_FIELDS", $arParams))
{
	$arParams["USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", 0, LANGUAGE_ID);
	$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? array_keys($arParams["USER_FIELDS"]) : array());
	$arParams["USER_FIELDS"] = array_intersect(["UF_FORUM_MESSAGE_DOC", "UF_FORUM_MESSAGE_VER"], $arParams["USER_FIELDS"]);
}
//$arParams["PATH_TO_AUTH_FORM"]
$arParams["MINIMIZE_SQL"] = "N";

//$arParams["USER_PROPERTY"] - user property
//$arParams["SHOW_FORUM_ANOTHER_SITE"]
//$arParams["SHOW_FORUMS_LIST"]
$arParams["SHOW_TAGS"] = (is_set($arParams["SHOW_TAGS"]) ? $arParams["SHOW_TAGS"] : "Y");


$arParams["SEND_MAIL"] = (in_array($arParams["SEND_MAIL"], array("A", "E", "U", "Y")) ? $arParams["SEND_MAIL"] : "E");
$arParams["SEND_ICQ"] = "A";

//$arParams["SHOW_FORUM_ANOTHER_SITE"]

//$arParams["SHOW_FORUMS_LIST"]
//$arParams["SHOW_USER_STATUS"]
//$arParams["FORUMS_ANOTHER"]

$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y"); // add items into chain item
// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); // add buttons unto top panel
if (!array_key_exists("CACHE_TIME_USER_STAT", $arParams))
	$arParams["CACHE_TIME_USER_STAT"] = 60;

$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");

$arParams["USE_RSS"] = ($arParams["USE_RSS"] == "N" ? "N" : "Y");
$arParams["AJAX_MODE"] = ($arParams["AJAX_MODE"] == "Y" ? "Y" : "N");
$arParams["AJAX_TYPE"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_MODE"] == "N") ? "Y" : "N");
// CACHE & TITLE
//$arParams["CACHE_TIME"]
//$arParams["CACHE_TYPE"]
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_PAGE_PROPERTY"] = ($arParams["SET_PAGE_PROPERTY"] == "N" ? "N" : "Y");
$arParams["SET_DESCRIPTION"] = ($arParams["SET_DESCRIPTION"] == "Y" ? "Y" : "N");

$arParams["USE_NAME_TEMPLATE"] = ($arParams["USE_NAME_TEMPLATE"] == "Y" ? "Y" : "N");
if ($arParams["USE_NAME_TEMPLATE"] == "Y")
{
	$arParams["NAME_TEMPLATE"] = str_replace(
		array("#NOBR#", "#/NOBR#"),
		"",
		!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat());
}
else
{
	$arParams["NAME_TEMPLATE"] = false;
}
$arParams["SHOW_ADD_MENU"] = ($arParams["TMPLT_SHOW_BOTTOM"] == "SET_BE_READ" ? "N" : "Y");
if (!$GLOBALS["USER"]->IsAuthorized() && COption::GetOptionString("forum", "USE_COOKIE", "N") == "N")
{
	$arParams["SHOW_ADD_MENU"] = "N";
	$arParams["TMPLT_SHOW_BOTTOM"] = "";
}

$arParams["VOTE_CHANNEL_ID"] = intval($arParams["VOTE_CHANNEL_ID"]);
$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && $arParams["VOTE_CHANNEL_ID"] > 0 && IsModuleInstalled("vote") ? "Y" : "N");
if ($arParams["SHOW_VOTE"] == "Y"):
	$arParams["VOTE_GROUP_ID"] = (!is_array($arParams["VOTE_GROUP_ID"]) || empty($arParams["VOTE_GROUP_ID"]) ? array() : $arParams["VOTE_GROUP_ID"]);
	$arParams["VOTE_TEMPLATE"] = (trim($arParams["VOTE_TEMPLATE"]) <> '' ? trim($arParams["VOTE_TEMPLATE"]) : "light");
endif;

$arParams["RATING_ID"] = $arParams["RATING_ID"];
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

if ($arVariables["PAGE_NAME"] !== "rss" && CModule::IncludeModule("forum"))
	ForumSetLastVisit((mb_strpos($arVariables["PAGE_NAME"], "pm_") !== 0 ? $arResult["FID"] : 0), $arResult["TID"]);

$this->IncludeComponentTemplate($arVariables["PAGE_NAME"]);
?>