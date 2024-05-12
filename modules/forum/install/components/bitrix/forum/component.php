<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

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

$arParams["SHOW_FORUM_USERS"] = ($arParams["SHOW_FORUM_USERS"] ?? 'N') === 'Y' ? 'Y' : 'N';

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
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if (($request->get('auth') === 'yes' || $request->get('register') === 'yes' || $request->get('login') === 'yes') &&
	$USER->IsAuthorized() || $request->get('logout') === 'yes')
{
	LocalRedirect($APPLICATION->GetCurPageParam("", $arAuthPageParams));
}
elseif (!isset($arParams["SHOW_AUTH_FORM"]) || $arParams["SHOW_AUTH_FORM"] != "N")
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
if (isset($arParams["SEF_MODE"]) && $arParams["SEF_MODE"] == "Y")
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
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"] ?? null);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"] ?? null);
	if (!isset($arParams["CHECK_CORRECT_TEMPLATES"]) || $arParams["CHECK_CORRECT_TEMPLATES"] != "N"):
		foreach ($arUrlTemplates as $url => $value)
		{
			if (!CheckPathParams($arUrlTemplates[$url], $arDefaultVariableAliasesForPages[$url], $arVariableAliases[$url] ?? null))
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

	if (isset($arParams["SEF_MODE_NSEF"]) && $arParams["SEF_MODE_NSEF"] == "Y" && (empty($componentPage) || $componentPage == "index") && !empty($_REQUEST["PAGE_NAME"]))
	{
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, array());
		CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	}
}
else
{
	if (!isset($arParams["VARIABLE_ALIASES"]))
	{
		$arParams["VARIABLE_ALIASES"] = [];
	}

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
/*else
{
	$arVariableAliases = array();
}*/

if (!empty($arVariables["PAGE_NAME"]))
{
	$componentPage = mb_strtolower($arVariables["PAGE_NAME"]);
}

$bFounded = false;
if (in_array($componentPage, array("message", "message_small"))):
	$componentPage = "read";
	$bFounded = true;
elseif ($componentPage == 'user_list' && $arParams['SHOW_FORUM_USERS'] !== 'Y'):
	$componentPage = "index";
	$bFounded = true;
elseif ($componentPage === "forums"):
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

$arVariables['FID'] = $arVariables['FID'] ?? null;
$arVariables['GID'] = $arVariables['GID'] ?? null;
$arVariables['TID'] = $arVariables['TID'] ?? null;
$arVariables['TITLE_SEO'] = $arVariables['TITLE_SEO'] ?? null;
$arVariables['MID'] = $arVariables['MID'] ?? null;
$arVariables['UID'] = $arVariables['UID'] ?? null;
$arVariables['IID'] = $arVariables['IID'] ?? null;
$arVariables['ACTION'] = $arVariables['ACTION'] ?? null;
$arVariables['TYPE'] = $arVariables['TYPE'] ?? null;
$arVariables['mode'] = $arVariables['mode'] ?? null;
$arVariables['MODE'] = $arVariables['MODE'] ?? null;
if (empty($arVariables["TID"]) && !empty($arVariables["TITLE_SEO"]))
{
	$arVariables["TID"] = intval(strtok($arVariables["TITLE_SEO"], "-"));
}

// BASE
$arParams["SEF_MODE"] = $arParams["SEF_MODE"] ?? 'N';
$arParams["SEF_FOLDER"] = $arParams["SEF_FOLDER"] ?? '';
$arParams["SET_TITLE"] = $arParams["SET_TITLE"] ?? 'Y';
$arParams["SET_PAGE_PROPERTY"] = $arParams["SET_PAGE_PROPERTY"] ?? 'Y';
$arParams["CACHE_TIME"] = $arParams["CACHE_TIME"] ?? 3600;
$arParams["CACHE_TYPE"] = $arParams["CACHE_TYPE"] ?? 'A';
$arParams["SET_NAVIGATION"] = $arParams["SET_NAVIGATION"] ?? 'Y';
$arParams["DATE_FORMAT"] = $arParams["DATE_FORMAT"] ?? null;
$arParams["DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"] ?? null;
$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ?? null;
$arParams["FORUMS_PER_PAGE"] = $arParams["FORUMS_PER_PAGE"] ?? null;
$arParams["TOPICS_PER_PAGE"] = $arParams["TOPICS_PER_PAGE"] ?? null;
$arParams["MESSAGES_PER_PAGE"] = $arParams["MESSAGES_PER_PAGE"] ?? null;
$arParams["PATH_TO_AUTH_FORM"] = $arParams["PATH_TO_AUTH_FORM"] ?? '';
$arParams["SHOW_FORUM_ANOTHER_SITE"] = $arParams["SHOW_FORUM_ANOTHER_SITE"] ?? 'Y';
$arParams["SHOW_FORUMS_LIST"] = $arParams["SHOW_FORUMS_LIST"] ?? 'Y';
$arParams["HELP_CONTENT"] = $arParams["HELP_CONTENT"] ?? '';
$arParams["RULES_CONTENT"] = $arParams["RULES_CONTENT"] ?? '';
$arParams["TIME_INTERVAL_FOR_USER_STAT"] = (isset($arParams["TIME_INTERVAL_FOR_USER_STAT"]) && ctype_digit(strval($arParams["TIME_INTERVAL_FOR_USER_STAT"])) ? intval($arParams["TIME_INTERVAL_FOR_USER_STAT"]) : 60) / 60;
$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] ?? "Y");
$arParams["USE_DESC_PAGE_TOPIC"] = ($arParams["USE_DESC_PAGE_TOPIC"] ?? "Y");
$arParams["FID"] = (isset($arParams["FID"]) && is_array($arParams["FID"]) ? $arParams["FID"] : array());
$arParams["RSS_FID_RANGE"] = (!empty($arParams["RSS_FID_RANGE"]) ? $arParams["RSS_FID_RANGE"] : (!empty($arParams["FID"]) ? $arParams["FID"] : []));
$arParams['RSS_TYPE_RANGE'] = !empty($arParams['RSS_TYPE_RANGE']) ? $arParams['RSS_TYPE_RANGE'] : [];
$arParams["RSS_YANDEX"] = ($arParams["RSS_YANDEX"] ?? '');
$arParams["RSS_TN_TITLE"] = ($arParams["RSS_TN_TITLE"] ?? '');
$arParams["RSS_TN_DESCRIPTION"] = ($arParams["RSS_TN_DESCRIPTION"] ?? '');
$arParams["RSS_TN_TEMPLATE"] = ($arParams["RSS_TN_TEMPLATE"] ?? '');
if (empty($arResult["TID"]) && !empty($arResult["TITLE_SEO"]))
	$arResult["TID"] = intval(strtok($arResult["TITLE_SEO"], "-"));
$arParams['AJAX_POST'] = ($arParams["AJAX_POST"] ?? 'N') === 'Y' ? 'Y' : 'N';
$arParams['DISPLAY_PANEL'] = ($arParams["DISPLAY_PANEL"] ?? 'N') === 'Y' ? 'Y' : 'N';

//$arParams["TID"] - topic id
//$arParams["MID"] - message id || message id (pm)
//$arParams["UID"] - user id
//$arParams["HELP_CONTENT"]
//$arParams["RULES_CONTENT"]

$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"] ?? 'N',
		"SEF_FOLDER" => $arParams["SEF_FOLDER"] ?? '',
		"URL_TEMPLATES" => $arUrlTemplates ?? [],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases,
		"PAGE_NAME" => $arVariables["PAGE_NAME"],
		"FID" => $arVariables["PAGE_NAME"] == "index" ? $arParams["FID"] : $arVariables["FID"],
		"GID" => $arVariables["GID"] ?? null,
		"TID" => $arVariables["TID"] ?? null,
		"TITLE_SEO" => $arVariables["TITLE_SEO"] ?? null,
		"MID" => $arVariables["MID"] ?? null,
		"UID" => $arVariables["UID"] ?? null,
		"IID" => $arVariables["IID"] ?? null,
		"ACTION" => $arVariables["ACTION"] ?? null,
		"TYPE" => $arVariables["TYPE"] ?? null,
		"mode" => $arVariables["mode"] ?? null,
		"MODE" => $arVariables["MODE"]?? null,
		"SET_TITLE" => $arParams["SET_TITLE"] ?? 'Y',
		"SET_PAGE_PROPERTY" => $arParams["SET_PAGE_PROPERTY"] ?? 'Y',
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? 3600,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? 'A',
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? 'Y',
		"DATE_FORMAT" => $arParams["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"FORUMS_PER_PAGE" => $arParams["FORUMS_PER_PAGE"] ?? null,
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"] ?? null,
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"] ?? null,
		"PATH_TO_AUTH_FORM" => $arParams["PATH_TO_AUTH_FORM"] ?? '',
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"] ?? 'Y',
		"SHOW_FORUMS_LIST" => $arParams["SHOW_FORUMS_LIST"] ?? 'Y',
		"HELP_CONTENT" => $arParams["HELP_CONTENT"] ?? '',
		"RULES_CONTENT" => $arParams["RULES_CONTENT"] ?? '',
		),
	$arResult);
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
//$arParams["DATE_FORMAT"],
//$arParams["DATE_TIME_FORMAT"],
//$arParams["FORUMS_PER_PAGE"],
//$arParams["TOPICS_PER_PAGE"],
//$arParams["MESSAGES_PER_PAGE"],

if (!isset($arParams["ATTACH_MODE"]))
{
	if (isset($arParams["IMAGE_SIZE"]) && intval($arParams["IMAGE_SIZE"]) > 0)
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
$arParams["IMAGE_SIZE"] = isset($arParams["IMAGE_SIZE"]) ? intval($arParams["IMAGE_SIZE"]) : 500;
$arParams["ATTACH_MODE"] = (is_array($arParams["ATTACH_MODE"]) ? $arParams["ATTACH_MODE"] : array("NAME"));
$arParams["ATTACH_MODE"] = (!in_array("NAME", $arParams["ATTACH_MODE"]) && !in_array("THUMB", $arParams["ATTACH_MODE"]) ? array("NAME") : $arParams["ATTACH_MODE"]);
$arParams["ATTACH_SIZE"] = intval($arParams["ATTACH_SIZE"] ?: 90);
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
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] ?? "Y");
$arParams["SEND_MAIL"] = (isset($arParams["SEND_MAIL"]) && in_array(($arParams["SEND_MAIL"] ?? 'E'), array("A", "E", "U", "Y")) ? $arParams["SEND_MAIL"] : "E");
$arParams["SEND_ICQ"] = "A";

//$arParams["SHOW_FORUM_ANOTHER_SITE"]

//$arParams["SHOW_FORUMS_LIST"]
//$arParams["SHOW_USER_STATUS"]
//$arParams["FORUMS_ANOTHER"]

$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] ?? "Y") === 'N' ? 'N' : 'Y'; // add items into chain item
// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); // add buttons unto top panel
if (!array_key_exists("CACHE_TIME_USER_STAT", $arParams))
	$arParams["CACHE_TIME_USER_STAT"] = 60;

$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] ?? "N") === 'Y' ? 'Y' : 'N';
$arParams["USE_RSS"] = ($arParams["USE_RSS"] ?? 'Y') == "N" ? "N" : "Y";
$arParams["AJAX_MODE"] = (($arParams["AJAX_MODE"] ?? 'N') == "Y" ? "Y" : "N");
$arParams["AJAX_TYPE"] = ((($arParams["AJAX_TYPE"] ?? 'N') == "Y" && $arParams["AJAX_MODE"] == "N") ? "Y" : "N");
// CACHE & TITLE
//$arParams["CACHE_TIME"]
//$arParams["CACHE_TYPE"]
$arParams["SET_TITLE"] = (($arParams["SET_TITLE"] ?? 'Y') == "N" ? "N" : "Y");
$arParams["SET_PAGE_PROPERTY"] = (($arParams["SET_PAGE_PROPERTY"] ?? 'Y') == "N" ? "N" : "Y");
$arParams["SET_DESCRIPTION"] = (($arParams["SET_DESCRIPTION"] ?? 'N') == "Y" ? "Y" : "N");

$arParams["USE_NAME_TEMPLATE"] = (($arParams["USE_NAME_TEMPLATE"] ?? 'N') == "Y" ? "Y" : "N");
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
$arParams["SHOW_ADD_MENU"] = (($arParams["TMPLT_SHOW_BOTTOM"] ?? '') == "SET_BE_READ" ? "N" : "Y");
if (!$GLOBALS["USER"]->IsAuthorized() && COption::GetOptionString("forum", "USE_COOKIE", "N") == "N")
{
	$arParams["SHOW_ADD_MENU"] = "N";
	$arParams["TMPLT_SHOW_BOTTOM"] = "";
}

$arParams["VOTE_CHANNEL_ID"] = intval($arParams["VOTE_CHANNEL_ID"] ?? 0);
$arParams["SHOW_VOTE"] = (($arParams["SHOW_VOTE"] ?? 'N') == "Y" && $arParams["VOTE_CHANNEL_ID"] > 0 && IsModuleInstalled("vote") ? "Y" : "N");
if ($arParams["SHOW_VOTE"] == "Y"):
	$arParams["VOTE_GROUP_ID"] = (!is_array($arParams["VOTE_GROUP_ID"]) || empty($arParams["VOTE_GROUP_ID"]) ? array() : $arParams["VOTE_GROUP_ID"]);
	$arParams["VOTE_TEMPLATE"] = (trim($arParams["VOTE_TEMPLATE"]) <> '' ? trim($arParams["VOTE_TEMPLATE"]) : "light");
endif;

$arParams["RATING_ID"] = ($arParams["RATING_ID"] ?? 0);
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

if ($arVariables["PAGE_NAME"] !== "rss" && CModule::IncludeModule("forum"))
	ForumSetLastVisit((mb_strpos($arVariables["PAGE_NAME"], "pm_") !== 0 ? $arResult["FID"] : 0), $arResult["TID"]);

$this->IncludeComponentTemplate($arVariables["PAGE_NAME"]);
