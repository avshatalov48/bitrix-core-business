<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @params \CMain $APPLICATION
 * @params \CUser $USER
 */
if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}
if (IsModuleInstalled('statistic') && isset($_SESSION["SESS_SEARCHER_ID"]) && intval($_SESSION["SESS_SEARCHER_ID"]) > 0)
{
	return 0;
}

$arParams["USE_RSS"] = ($arParams["USE_RSS"] ?? 'Y');

if ($arParams["USE_RSS"] === "N") // out-of-date params
{
	return 0;
}

if (!function_exists("__create_uuid"))
{
	function __create_uuid($params)
	{
		$uuid = md5($params);
		return mb_substr($uuid, 0, 8).'-'.
			mb_substr($uuid, 8, 4).'-'.
			mb_substr($uuid, 12, 4).'-'.
			mb_substr($uuid, 16, 4).'-'.
			mb_substr($uuid, 20);
	}
}

$arResult['TYPE_RSS'] = ['RSS1' => 'RSS .92', 'RSS2' => 'RSS 2.0', 'ATOM' => 'Atom .3'];
$arResult['FORUMS'] = [];
$arResult['TOPIC'] = [];
$arResult['SERVER_NAME'] = (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : \COption::GetOptionString('main', 'server_name');

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$defaultRss = array_keys($arResult['TYPE_RSS']);
$arParams["TYPE_RANGE"] = is_array($arParams["TYPE_RANGE"]) ? array_intersect($arParams["TYPE_RANGE"], $defaultRss) : $defaultRss;

$arParams["TYPE_DEFAULT"] = in_array(($arParams["TYPE_DEFAULT"] ?? ''), $arParams["TYPE_RANGE"]) ? $arParams["TYPE_DEFAULT"] : reset($arParams["TYPE_RANGE"]);
$activeType = mb_strtoupper($arParams["TYPE"] ?? 'DEFAULT');
$activeType = $activeType === 'DEFAULT' ? $arParams["TYPE_DEFAULT"] : $activeType;
$arParams["TYPE"] = in_array($activeType, $defaultRss) ? $activeType : reset($defaultRss);

$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());

$arParams["MODE"] = mb_strtolower($arParams["MODE"] ?? 'link');
$arParams["MODE"] = (in_array($arParams["MODE"], ["forum", "topic"]) ? $arParams["MODE"] : 'link');
$arParams["MODE_DATA"] = trim($arParams["MODE_DATA"] ?? $arParams["MODE"]);
$arParams["MODE_DATA"] = $arParams["MODE_DATA"] == "topic" ? "topic" : "forum";

$arParams["IID"] = intval($arParams["IID"] ?? $_REQUEST["IID"]);
$arParams["FID"] = ($arParams["MODE_DATA"] == "forum" ? $arParams["IID"] : 0);
$arParams["TID"] = ($arParams["MODE_DATA"] == "topic" ? $arParams["IID"] : 0);

/***************** URL *********************************************/
	$URL_NAME_DEFAULT = [
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"
	];
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
	$arParams["COUNT"] = intval($arParams["COUNT"] ?: ($arParams["MODE_DATA"] == "forum" ?
		COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")));
	$arParams["COUNT"] = ($arParams["COUNT"] > 0 ? $arParams["COUNT"] : 10);
	$arParams["MAX_FILE_SIZE"] = intval($arParams["MAX_FILE_SIZE"] ?? 10) * 1024 * 1024;
	$arParams["DATE_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT"));
	$arParams["DATE_TIME_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["DESIGN_MODE"] = ($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && CForumUser::IsAdmin() ? "Y" : "N");
	$arParams["TEMPLATES_TITLE_FORUMS"] = ($arParams["TEMPLATES_TITLE_FORUMS"] ?? GetMessage("F_TEMPLATES_TITLE_FORUMS"));
	$arParams["TEMPLATES_TITLE_FORUM"] = ($arParams["TEMPLATES_TITLE_FORUM"] ?? GetMessage("F_TEMPLATES_TITLE_FORUM"));
	$arParams["TEMPLATES_TITLE_TOPIC"] = ($arParams["TEMPLATES_TITLE_TOPIC"] ?? GetMessage("F_TEMPLATES_TITLE_TOPIC"));

	$arParams["TEMPLATES_DESCRIPTION_FORUMS"] = ($arParams["TEMPLATES_DESCRIPTION_FORUMS"] ?? GetMessage("F_TEMPLATES_DESCRIPTION_FORUMS"));
	$arParams["TEMPLATES_DESCRIPTION_FORUM"] = ($arParams["TEMPLATES_DESCRIPTION_FORUM"] ?? GetMessage("F_TEMPLATES_DESCRIPTION_FORUM"));
	$arParams["TEMPLATES_DESCRIPTION_TOPIC"] = ($arParams["TEMPLATES_DESCRIPTION_TOPIC"] ?? GetMessage("F_TEMPLATES_DESCRIPTION_TOPIC"));

/***************** CACHE *******************************************/
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
if (empty($arParams["TYPE_RANGE"]))
{
	ShowError(GetMessage("F_EMPTY_TYPE"));
	return 0;
}

$arFilter = (!empty($arParams["FID_RANGE"]) ? [
	"@ID" => $arParams["FID_RANGE"]
	] : []) + [
	"LID" => SITE_ID,
	"PERMS" => [$USER->GetGroups(), 'A'],
	"ACTIVE" => "Y"
	]
;

$db_res = CForumNew::GetListEx(
	["FORUM_GROUP_SORT" =>"ASC", "FORUM_GROUP_ID" =>"ASC", "SORT" =>"ASC", "NAME" =>"ASC"],
	$arFilter,
	false, 0,
	["sNameTemplate" => $arParams["NAME_TEMPLATE"]]
);

if ($db_res && ($res = $db_res->Fetch()))
{
	do
	{
		foreach ($res as $key => $val)
		{
			$res["~".$key] = $val;
			$res[$key] = htmlspecialcharsbx($val);
		}

		$res["ALLOW"] = forumTextParser::GetFeatures($res);
		$res["~FORUM_DESCRIPTION"] = $res["~DESCRIPTION"];
		$res["FORUM_DESCRIPTION"] = $res["DESCRIPTION"];
		$res["~FORUM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
		$res["FORUM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
		$res["~URL"] = "http://".$arResult["SERVER_NAME"].$res["~FORUM_LINK"];
		$res["URL"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).$res["FORUM_LINK"];
		$arResult["FORUMS"][$res["ID"]] = $res;
	} while ($res = $db_res->Fetch());
}

if (empty($arResult["FORUMS"]))
{
	ShowError(GetMessage("F_EMPTY_FORUMS"));
	CHTTP::SetStatus("404 Not Found");
	return false;
}
if ($arParams["MODE_DATA"] == "forum" && $arParams["FID"] > 0 && !isset($arResult["FORUMS"][$arParams["FID"]]))
{
	if ($arParams["MODE"] != "link"):
		ShowError(GetMessage("F_ERR_BAD_FORUM"));
		CHTTP::SetStatus("404 Not Found");
	endif;
	return false;
}

if ($arParams["MODE_DATA"] == "topic")
{
	if ($arParams["TID"] <= 0)
	{
		ShowError(GetMessage("F_EMPTY_TOPIC_ID"));
		CHTTP::SetStatus("404 Not Found");
		return false;
	}

	if (!($topic = CForumTopic::GetList(array(), array(
		"ID" => $arParams["TID"],
		'@FORUM_ID' => array_keys($arResult["FORUMS"])
	))->fetch()))
	{
		ShowError(GetMessage("F_EMPTY_TOPIC"));
		CHTTP::SetStatus("404 Not Found");
		return false;
	}
	$arResult["TOPIC"] = [];
	foreach ($topic as $key => $val)
	{
		$arResult["TOPIC"]["~".$key] = $val;
		$arResult["TOPIC"][$key] = htmlspecialcharsbx($val);
	}
	$arParams["FID"] = $arResult["TOPIC"]["FORUM_ID"];
}

/********************************************************************
				Data 1
********************************************************************/
if ($arParams["MODE"] == "link"):
	$arResult["rss_link"] = array();
	foreach ($arParams["TYPE_RANGE"] as $key)
	{
		$rss = mb_strtolower($key);
		$arResult["rss_link"][$rss] = array(
			"type" => $rss,
			"name" => $arResult["TYPE_RSS"][$key],
			"link" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"],
				array("TYPE" => $rss, "MODE" => $arParams["MODE_DATA"], "IID" => $arParams["IID"])));
	}
	$this->IncludeComponentTemplate();
	return false;
endif;
/********************************************************************
				/Data 1
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arFilter = array();
$arItems = array();
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["CHARSET"] = (defined("SITE_CHARSET") && SITE_CHARSET <> '') ? SITE_CHARSET : "windows-1251";
$arResult["NOW"] = ($arParams["TYPE"] != "ATOM") ? date("r") : date("Y-m-d H:i:s").mb_substr(date("O"), 0, 3).":".mb_substr(date("O"), -2, 2);
$arResult["TEMPLATE_ELEMENTS"] = array("AUTHOR_NAME", "AUTHOR_LINK", "SIGNATURE", "DATE_REG", "AVATAR", "POST_MESSAGE", "POST_LINK",
	"POST_DATE", "ATTACH_IMG", "TITLE", "TOPIC_LINK",
	"TOPIC_DATE", "TOPIC_DESCRIPTION", "NAME", "FORUM_LINK", "FORUM_DESCRIPTION");
$parser = new forumTextParser(LANGUAGE_ID);
$parser->MaxStringLen = 0;
$parser->userPath = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
$parser->userNameTemplate = $arParams["NAME_TEMPLATE"];

$arResult["SITE"] = array();
$db_res = CSite::GetByID(SITE_ID);
if ($db_res && $res = $db_res->GetNext())
	$arResult["SITE"] = $res;

$replacements = [
	"#FORUM_TITLE#" => isset($arResult["FORUMS"][$arParams["FID"]]) ? $arResult["FORUMS"][$arParams["FID"]]["~NAME"] : '',
	"#FORUM_DESCRIPTION#" => isset($arResult["FORUMS"][$arParams["FID"]]) ? $arResult["FORUMS"][$arParams["FID"]]["~DESCRIPTION"] : '',
	"#TOPIC_TITLE#" => '',
	"#TOPIC_DESCRIPTION#" => '',
	"#SITE_NAME#" => $arResult["SITE"]["SITE_NAME"],
	"#SERVER_NAME#" => $arResult["SERVER_NAME"],
];
$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_FORUMS"];
$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_FORUMS"];
if ($arParams["MODE_DATA"] == "forum" && $arParams["IID"] > 0)
{
	$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_FORUM"];
	$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_FORUM"];
}
elseif ($arParams["MODE_DATA"] == "topic")
{
	$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_TOPIC"];
	$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_TOPIC"];
	$replacements["#TOPIC_TITLE#"] = $arResult["TOPIC"]["~TITLE"];
	$replacements["#TOPIC_DESCRIPTION#"] = $arResult["TOPIC"]["~DESCRIPTION"];
}

$arResult["~TITLE"] = str_replace(array_keys($replacements), array_values($replacements), $arResult["~TITLE"]);
$arResult["~DESCRIPTION"] = str_replace(array_keys($replacements), array_values($replacements), $arResult["~DESCRIPTION"]);
$arResult["TITLE"] = htmlspecialcharsbx($arResult["~TITLE"]);
$arResult["DESCRIPTION"] = htmlspecialcharsbx($arResult["~DESCRIPTION"]);

$arResult["URL"] = array(
	"~ALTERNATE" => "http://".$arResult["SERVER_NAME"],
	"ALTERNATE" => htmlspecialcharsbx("http://".$arResult["SERVER_NAME"]),
	"~REAL" => "http://".$arResult["SERVER_NAME"].$APPLICATION->GetCurPageParam(),
	"REAL" => htmlspecialcharsbx("http://".$arResult["SERVER_NAME"].$APPLICATION->GetCurPageParam()));

$arResult["MESSAGE_LIST"] = $arResult["FILES"] = array();
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data 2
********************************************************************/
$cache_id_array = array("MODE" => $arParams["MODE_DATA"], "IID" => $arParams["IID"], "TYPE" => $arParams["TYPE"], "COUNT" => $arParams["COUNT"],
	"FID_RANGE" => $arParams["FID_RANGE"], "USER_GROUP" => $GLOBALS["USER"]->GetUserGroupArray(), "LANGUAGE" => $arResult["LANGUAGE_ID"],
	"SERVER_NAME" => $arResult["SERVER_NAME"], "CHARSET" => $arResult["CHARSET"]);

if ($arParams["DESIGN_MODE"] != "Y")
{
	$APPLICATION->RestartBuffer();
	header("Content-Type: text/xml");
	header("Pragma: no-cache");
}
if($this->StartResultCache($arParams["CACHE_TIME"], array($cache_id_array, $arParams["DESIGN_MODE"]), "/".SITE_ID."/forum/rss/".$arParams["TYPE"]."/".$arParams["MODE_DATA"]."/"))
{
	$arFilter = array(
		"TOPIC_ID" => $arParams["TID"],
		"APPROVED" => "Y",
		"@FORUM_ID" => implode(",", array_keys($arResult["FORUMS"])),
		"TOPIC" => "GET_TOPIC_INFO");
	if ($arParams["MODE_DATA"] != "topic")
	{
		$arFilter = array();
		if ($arParams["FID"] > 0)
		{
			$arFilter["FORUM_ID"] = $arParams["FID"];
		}
		else
		{
			$arFilter["@FORUM_ID"] = implode(",", array_keys($arResult["FORUMS"]));
		}
		$arFilter["APPROVED"] = "Y";
		$arFilter["NEW_TOPIC"] = "Y";
		$arFilter["TOPIC"] = "GET_TOPIC_INFO";
	}

	CTimeZone::Disable();
	$db_res = CForumMessage::GetListEx(
		array("ID" => "DESC"),
		$arFilter, 0,
		$arParams["COUNT"],
		array("sNameTemplate" => $arParams["NAME_TEMPLATE"]));
	CTimeZone::Enable();

	if ($db_res && ($res = $db_res->Fetch()))
	{
		do
		{
			foreach ($res as $key => $val)
			{
				$res["~".$key] = $val;
				$res[$key] = htmlspecialcharsbx($val);
			}
			/************** Message info ***************************************/
			// data
			$arDate = ParseDateTime($res["POST_DATE"], false);
			$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
			if ($arParams["TYPE"] == "ATOM")
			{
				$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
				$date = date("Y-m-d\TH:i:s", $timeISO).mb_substr(date("O", $timeISO), 0, 3).":".mb_substr(date("O", $timeISO), -2, 2);
			}
			$res["POST_DATE"] = $date;
			$res["POST_DATE_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~POST_DATE"], CSite::GetDateFormat())+CTimeZone::GetOffset());
			// text
			$arAllow = $arResult["FORUMS"][$res["FORUM_ID"]]["ALLOW"];
			$res["ALLOW"] = array_merge($arAllow, array("SMILES" => ($res["USE_SMILES"] == "Y" ? $arAllow["SMILES"] : "N")));
			$res["~POST_MESSAGE"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
			// attach
			$res["ATTACH_IMG"] = ""; $res["FILES"] = $res["~ATTACH_FILE"] = $res["ATTACH_FILE"] = array();
			/************** Message info/***************************************/
			/************** Author info ****************************************/
			// Avatar
			if ($res["AVATAR"] <> ''):
				$res["~AVATAR"] = array("ID" => $res["AVATAR"]);
				$res["~AVATAR"]["FILE"] = CFile::GetFileArray($res["~AVATAR"]["ID"]);
				$res["AVATAR"] = CFile::ShowImage($res["~AVATAR"]["FILE"], COption::GetOptionString("forum", "avatar_max_width", 100),
					COption::GetOptionString("forum", "avatar_max_height", 100), "border=\"0\"", "", true);
			endif;
			// data
			$res["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
			// Another data
			$res["SIGNATURE"] = "";
			if ($arResult["FORUMS"][$res["FORUM_ID"]]["ALLOW_SIGNATURE"] == "Y" && $res["~SIGNATURE"] <> '')
			{
				$res["SIGNATURE"] = $parser->convert_to_rss($res["~SIGNATURE"], array_merge($arAllow, array("SMILES" => "N")));
			}
			/************** Author info/****************************************/
			$res["~AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => intval($res["AUTHOR_ID"])));
			$res["AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => intval($res["AUTHOR_ID"])));
			$res["~POST_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => $res["ID"]));
			$res["POST_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => $res["ID"]));

			$res["~AUTHOR_URL"] = "http://".$arResult["SERVER_NAME"].$res["~AUTHOR_LINK"];
			$res["AUTHOR_URL"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).$res["AUTHOR_LINK"];
			$res["~URL"] = "http://".$arResult["SERVER_NAME"].$res["~POST_LINK"];
			$res["URL"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).$res["POST_LINK"];

			$res["~URL_RSS"] = "http://".$arResult["SERVER_NAME"].CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"],
				array("TYPE" => mb_strtolower($arParams["TYPE"]), "MODE" => "topic", "IID" => $res["TOPIC_ID"]));
			$res["URL_RSS"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).
				CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"],
				array("TYPE" => mb_strtolower($arParams["TYPE"]), "MODE" => "topic", "IID" => $res["TOPIC_ID"]));
			$res["UUID"] = __create_uuid($res["~URL"]);

			// TOPIC DATA
			$arDate = ParseDateTime($res["START_DATE"], false);
			$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
			if ($arParams["TYPE"] == "ATOM")
			{
				$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
				$date = date("Y-m-d\TH:i:s", $timeISO).mb_substr(date("O", $timeISO), 0, 3).":".mb_substr(date("O", $timeISO), -2, 2);
			}
			$topic = array(
				"ID" => $res["TOPIC_ID"],
				"TITLE" => $res["TITLE"],
				"~TITLE" => $res["~TITLE"],
				"DESCRIPTION" => $res["TOPIC_DESCRIPTION"],
				"~DESCRIPTION" => $res["~TOPIC_DESCRIPTION"],
				"TOPIC_DESCRIPTION" => $res["TOPIC_DESCRIPTION"],
				"~TOPIC_DESCRIPTION" => $res["~TOPIC_DESCRIPTION"],
				"START_DATE" => $date,
				"~START_DATE" => $res["~START_DATE"],
				"START_DATE_FORMATED" => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~START_DATE"],
					CSite::GetDateFormat())),
				"AUTHOR_NAME" => $res["USER_START_NAME"],
				"~AUTHOR_NAME" => $res["~USER_START_NAME"],
				"AUTHOR_ID" => $res["USER_START_ID"],
				"~AUTHOR_ID" => $res["~USER_START_ID"],
				"~AUTHOR_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
					array("UID" => intval($res["~USER_START_ID"]))),
				"AUTHOR_LINK" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
					array("UID" => intval($res["~USER_START_ID"]))),
				"~TOPIC_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"],
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
				"TOPIC_LINK" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"],
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s")),
				"MESSAGES" => array()
			);

			$topic["~AUTHOR_URL"] = "http://".$arResult["SERVER_NAME"].$topic["~AUTHOR_LINK"];
			$topic["AUTHOR_URL"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).$topic["AUTHOR_LINK"];
			$topic["~URL"] = "http://".$arResult["SERVER_NAME"].$topic["~TOPIC_LINK"];
			$topic["URL"] = "http://".htmlspecialcharsbx($arResult["SERVER_NAME"]).$topic["TOPIC_LINK"];

			if (empty($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]))
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]] = $topic;

			unset($res["TITLE"]);
			unset($res["DESCRIPTION"]);
			$res["TEMPLATE"] = '';
			if (!empty($arParams["TEMPLATE"]))
			{
				$text = $arParams["TEMPLATE"];
				foreach ($arParams["TEMPLATE_ELEMENTS"] as $element)
				{
					$replace = array($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]][$element],
						$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["~".$element]);
					if ($res[$element] <> '')
						$replace = array($res[$element], $res["~".$element]);
					$text = str_replace(array("#".$res."#", "#~".$res."#"), $replace, $text);
				}
				$res["TEMPLATE"] = $text;
			}
			$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
			$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
		}
		while ($res = $db_res->Fetch());
	}
	if (is_array($arItems) && (count($arItems) > 0))
	{
		foreach ($arItems as $key => $val)
			$arItems[$key] = array_merge($arResult["FORUMS"][$key], $val);
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
			$arResult["FILES"][$res["FILE_ID"]] = $res;
			$res["SRC"] = str_replace("#FILE_ID#", $res["FILE_ID"], $src);
			$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
	$parser->arFiles = $arResult["FILES"];
	foreach ($arResult["MESSAGE_LIST"] as $iID => $res)
	{
		$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["POST_MESSAGE"] = $parser->convert_to_rss($res["~POST_MESSAGE"], array(), $res["ALLOW"]);
		$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["FILES_PARSED"] = $parser->arFilesIDParsed;

		$arFiles = $arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["FILES"];
		foreach ($arFiles as $key => $val)
		{
			if (in_array($key, $arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["FILES_PARSED"])):
				unset ($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["FILES"][$val["FILE_ID"]]);
				continue;
			endif;
			$val["HTML"] = $GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:forum.interface",
				"show_file",
				Array(
					"FILE" => $val,
					"SHOW_MODE" => "RSS",
					"WIDTH" => $parser->image_params["width"],
					"HEIGHT" => $parser->image_params["height"],
					"CONVERT" => "N",
					"FAMILY" => "FORUM",
					"SINGLE" => "Y",
					"RETURN" => "Y"),
				null,
				array("HIDE_ICONS" => "Y"));
			$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["FILES"][$val["FILE_ID"]]["HTML"] = $val["HTML"];
			if ($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["~ATTACH_IMG"] == $val["FILE_ID"]) // attach for custom
			{
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["ATTACH_IMG"] = $val["HTML"];
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]]["~ATTACH_IMG"] = array_merge($val, array("ID" => $val["FILE_ID"]));
			}
		}
	}
}

/************** Message List/***************************************/
$arResult["DATA"] = $arItems;
$arParams["TYPE"] = mb_strtolower($arParams["TYPE"]);
if ($arParams["DESIGN_MODE"] != "Y")
{
	$this->IncludeComponentTemplate();
}
else
{
	ob_start();
	$this->IncludeComponentTemplate();
	$contents = ob_get_clean();
	echo "<pre>",htmlspecialcharsbx($contents),"</pre>";
}
}
if ($arParams["DESIGN_MODE"] != "Y")
	die();
/********************************************************************
				/Data 2
********************************************************************/
