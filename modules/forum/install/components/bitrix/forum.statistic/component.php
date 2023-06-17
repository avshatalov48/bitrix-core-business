<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
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
	$arParams["FID"] = (intval($arParams["FID"]) <= 0 ? false : intval($arParams["FID"]));
	$arParams["TID"] = (intval($arParams["TID"]) <= 0 ? false : intval($arParams["TID"]));
	$arParams["TITLE_SEO"] = trim($arParams["TITLE_SEO"] ?? ($_REQUEST["TITLE_SEO"] ?? ''));
	if ($arParams["TID"] <= 0 && $arParams["TITLE_SEO"] <> '')
		$arParams["TID"] = intval(strtok($arParams["TITLE_SEO"], "-"));
	$arParams["PERIOD"] = (intval($arParams["PERIOD"]) <= 0 ? 10 : intval($arParams["PERIOD"])); // input params in minuts
	$arParams["PERIOD"] *= 60;
	$arParams["SHOW"] = (is_array($arParams["SHOW"]) ? $arParams["SHOW"] : array("BIRTHDAY", "USERS_ONLINE", "STATISTIC"));
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["FORUM_ID"] = (is_array($arParams["FORUM_ID"]) ? $arParams["FORUM_ID"] : array());
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["WORD_LENGTH"] = intval($arParams["WORD_LENGTH"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	{
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
		$arParams["CACHE_TIME_USER_STAT"] = intval($arParams["CACHE_TIME_USER_STAT"]);
		$arParams["CACHE_TIME_FOR_FORUM_STAT"] = intval($arParams["CACHE_TIME_FOR_FORUM_STAT"]);
	}
	else
	{
		$arParams["CACHE_TIME"] = 0;
		$arParams["CACHE_TIME_USER_STAT"] = 0;
		$arParams["CACHE_TIME_FOR_FORUM_STAT"] = 0;
	}
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$parser = new forumTextParser(LANGUAGE_ID, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$arResult["STATISTIC"] = array(
	"FORUMS" => 0,
	"TOPICS" => 0,
	"MESSAGES" => 0,
	"USERS" => 0,
	"USERS_ON_FORUM" => 0,
	"USERS_ON_FORUM_ACTIVE" => 0);
$arResult["USERS_BIRTHDAY"] = array();
$arResult["USERS"] = array();
$arResult["USERS_HIDDEN"] = array();
$arResult["GUEST"] = 0;
$arResult["REGISTER"] = 0;
$arResult["ALL"] = 0;
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
/********************************************************************
				/Default values
********************************************************************/
	
/********************************************************************
				Data
********************************************************************/
/************** Users Online ***************************************/
if (in_array("USERS_ONLINE", $arParams["SHOW"]))
{
	$UserHideOnLine = 0;
	$Guest = 0;
	$arFields = array();
	if ($arParams["FID"] && !$arParams["TID"]) 
		$arFields["FORUM_ID"] = $arParams["FID"];
	elseif ($arParams["TID"]) 
		$arFields["TOPIC_ID"] = $arParams["TID"];
	else
		$arFields["SITE_ID"] = SITE_ID;
	$arFields += array("ACTIVE" => "Y", "<=PERIOD" => $arParams["PERIOD"], "COUNT_GUEST" => true);

	$cache_id = "forum_user_online_".serialize(array($arFields, $arParams["URL_TEMPLATES_PROFILE_VIEW"]));
	$cache_path = $cache_path_main."user_online/";

/*	if (!$arParams["TID"] && $arParams["CACHE_TIME_USER_STAT"] > 0 &&
		$cache->InitCache($arParams["CACHE_TIME_USER_STAT"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["arUser"]))
			$arUser = $res["arUser"];
	}
	else
*/	{
		$arUser = array(
			"USERS" => array(), "USERS_HIDDEN" => array(),
			"GUEST" => 0, "REGISTER" => 0, "ALL" => 0);
		$db_res = CForumStat::GetListEx(
			array("USER_ID" => "DESC"),
			$arFields,
			array("sNameTemplate" => $arParams["NAME_TEMPLATE"]));
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do 
			{
				if ($res["USER_ID"] > 0)
				{
					$res["SHOW_NAME"] = $parser->wrap_long_words($res["SHOW_NAME"]);
					$res["profile_view"] = CComponentEngine::MakePathFromTemplate(
						$arParams["URL_TEMPLATES_PROFILE_VIEW"],
						array("UID" => $res["USER_ID"]));
					if ($res["HIDE_FROM_ONLINE"] != "Y")
						$arUser["USERS"][] = $res;
					else
						$arUser["USERS_HIDDEN"][] = $res;
				}
				else
					$Guest = intval($res["COUNT_USER"]);
			}while ($res = $db_res->GetNext());
			
			$arUser["GUEST"] = $Guest;
			$arUser["REGISTER"] = count($arUser["USERS"]) + count($arUser["USERS_HIDDEN"]);
			$arUser["ALL"] = $arUser["REGISTER"] + $Guest;
		}
		if (!$arParams["TID"] && $arParams["CACHE_TIME_USER_STAT"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME_USER_STAT"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arUser" => $arUser));
		}
	}
	
	$arResult["USERS"] = $arUser["USERS"];
	$arResult["USERS_HIDDEN"] = $arUser["USERS_HIDDEN"];
	$arResult["GUEST"] = $arUser["GUEST"];
	$arResult["REGISTER"] = $arUser["REGISTER"];
	$arResult["ALL"] = $arUser["ALL"];
}
/************** Birthday *******************************************/
if (in_array("BIRTHDAY", $arParams["SHOW"]))
{
	$arUserBirthday = false;
	$cache_id = md5(serialize(array(
		"forum_userbirthday_".preg_replace("/\s.,;:!?\#\-\*\|\[\]\(\)\//is", "_", $arParams["URL_TEMPLATES_PROFILE_VIEW"]),
		date("m-d"), CTimeZone::GetOffset())));
	$cache_path = $cache_path_main."birthday/";

	$BIRTHDAY_CACHE_TIME = 60*60*24;

	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($BIRTHDAY_CACHE_TIME, $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arTTL = getdate();
		$TTL = mktime(0, 0, 0, $arTTL['mon'], $arTTL['mday'], $arTTL['year']);
		if (is_array($res["arUserBirthday"]) && isset($res['timestamp']) && $TTL < ($res['timestamp']+$BIRTHDAY_CACHE_TIME))
			$arUserBirthday = $res["arUserBirthday"];
	}
	else
	{
		$db_res = CForumUser::GetList(array(), array(
			"ACTIVE" => "Y",
			"PERSONAL_BIRTHDAY_DATE" => Date("m-d"),
			">=USER_ID" => 1,
			"SHOW_ABC" => ""),
			array("sNameTemplate" => $arParams["NAME_TEMPLATE"]));
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do
			{
				$res["SHOW_NAME"] = $parser->wrap_long_words($res["SHOW_ABC"]);
				$date_birthday = ParseDateTime($res["PERSONAL_BIRTHDAY"]);
				$res["AGE"] = intval(date("Y")) - intval($date_birthday["YYYY"]);
				$res["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"]));
				$arUserBirthday[] = $res;
			} while($res = $db_res->GetNext());
		}

		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($BIRTHDAY_CACHE_TIME, $cache_id, $cache_path);
			$arTTL = getdate();
			$TTL = mktime(0, 0, 0, $arTTL['mon'], $arTTL['mday'], $arTTL['year']);
			$cache->EndDataCache(array(
				"arUserBirthday" => $arUserBirthday,
				"timestamp" => $TTL
			));
		}
	}
	if (is_array($arUserBirthday))
		$arResult["USERS_BIRTHDAY"] = $arUserBirthday;
}
/************** Forum stats ****************************************/
if (in_array("STATISTIC", $arParams["SHOW"]))
{
	$cache_id = serialize(array("forum_user_stat_0", $USER->GetGroups(), $arParams["SHOW_FORUM_ANOTHER_SITE"], $arParams["FORUM_ID"], $arParams["FID"]));
	$cache_path = $cache_path_main."forums/";
	if ($arParams["CACHE_TIME_FOR_FORUM_STAT"] > 0 && $cache->InitCache($arParams["CACHE_TIME_FOR_FORUM_STAT"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["STATISTIC"]))
			$arResult["STATISTIC"] = $res["STATISTIC"];
	}
	else
	{
		$arFilter = array();
		if (!CForumUser::IsAdmin())
		{
			$arFilter = array(
				"LID" => SITE_ID, 
				"PERMS" => array($USER->GetGroups(), 'A'), 
				"ACTIVE" => "Y");
		}
		elseif ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y") 
		{
			$arFilter["LID"] = SITE_ID;
		}
		
		if (is_array($arParams["FORUM_ID"]) && !empty($arParams["FORUM_ID"]))
		{
			$arFilter["@ID"] = $arParams["FORUM_ID"];
		}
		if (!empty($arParams["FID"]))
			$arFilter["ID"] = $arParams["FID"];
		else 
		{
			$arResult["STATISTIC"]["USERS"] = CUser::GetCount();
			$arResult["STATISTIC"]["USERS_ON_FORUM"] = CForumUser::CountUsers(false, array("ACTIVE" => "Y"));
			$arResult["STATISTIC"]["USERS_ON_FORUM_ACTIVE"] = CForumUser::CountUsers(true, array("ACTIVE" => "Y"));
		}
		$db_res = CForumNew::GetListEx(array(), $arFilter);
		if ($db_res && $res = $db_res->GetNext())
		{
			do 
			{
				$arResult["STATISTIC"]["FORUMS"]++;
				$arResult["STATISTIC"]["TOPICS"] += intval($res["TOPICS"] ?? 0);
				$arResult["STATISTIC"]["POSTS"] += intval($res["POSTS"] ?? 0);
			} while ($res = $db_res->GetNext());
		}
		
		if ($arParams["CACHE_TIME_FOR_FORUM_STAT"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME_FOR_FORUM_STAT"], $cache_id, $cache_path);
			$cache->EndDataCache(array("STATISTIC" => $arResult["STATISTIC"]));
		}
	}
}
/********************************************************************
				Data/
********************************************************************/
	$this->IncludeComponentTemplate();

?>