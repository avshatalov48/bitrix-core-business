<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
	$arParams["GID"] = intVal($arParams["GID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#", 
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["FORUMS_PER_PAGE"] = intVal(intVal($arParams["FORUMS_PER_PAGE"]) > 0 ? $arParams["FORUMS_PER_PAGE"] : COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["FID_RANGE"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] != "Y" ? "N" : "Y");
	
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["SHOW_FORUMS_LIST"] = ($arParams["SHOW_FORUMS_LIST"] == "Y" ? "Y" : "N");
	$arParams["MINIMIZE_SQL"] = ($arParams["MINIMIZE_SQL"] == "Y" ? "Y" : "N");
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
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["GROUP"] = $arResult["GROUPS"][$arParams["GID"]];
if (empty($arResult["GROUP"]))
	$arParams["GID"] = 0; 
$arResult["GROUP_NAVIGATION"] = array();
$arResult["USER"] = array(
	"CAN_MODERATE" => "N", 
	"HIDDEN_GROUPS" => array(), 
	"HIDDEN_FORUMS" => array());
	
$arResult["URL"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"]),
	"~INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_INDEX"]),
	"RSS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")), 
	"~RSS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], 
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")), 
	"~RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], 
			array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")), 
	"RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
			array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")), 
);
$arGroupForum = array();
$parser = new forumTextParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];

$arResult["FORUMS_FOR_GUEST"] = array();
$arResult["FORUMS_LIST"] = array();
/*******************************************************************/
if ($GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$res = CUserOptions::GetOption("forum", "user_info", "");
	$res = (CheckSerializedData($res) ? @unserialize($res) : array());
	$arResult["USER"]["HIDDEN_GROUPS"] = (is_array($res["groups"]) ? $res["groups"] : array());
	$arResult["USER"]["HIDDEN_FORUMS"] = (is_array($res["forums"]) ? $res["forums"] : array());
}
foreach ($arParams["FID_RANGE"] as $key => $val)
{
	if (intVal($val) > 0)
		$res[] = $val;
}
$arParams["FID_RANGE"] = $res;
$cache = new CPHPCache();
global $NavNum;
$PAGEN_NAME="PAGEN_".($NavNum+1);
global ${$PAGEN_NAME};
$PAGEN = ${$PAGEN_NAME};

global $CACHE_MANAGER;
$cache_path = $CACHE_MANAGER->GetCompCachePath(CComponentEngine::MakeComponentPath($this->__name));
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
	$arFilter = array();
	if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || !CForumUser::IsAdmin())
		$arFilter["LID"] = SITE_ID;
	if (!empty($arParams["FID_RANGE"]) && (!CForumUser::IsAdmin() || $arParams["SHOW_FORUMS_LIST"] == "Y")):
		$arFilter["@ID"] = $arParams["FID_RANGE"];
	endif;
	if (!CForumUser::IsAdmin()):
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A'); 
		$arFilter["ACTIVE"] = "Y";
	endif;

	if ($arParams["GID"] > 0)
	{
		if (intVal($arResult["GROUP"]["RIGHT_MARGIN"]) - intVal($arResult["GROUP"]["LEFT_MARGIN"]) > 1)
		{
			reset($arResult["GROUPS"]);
			$val = array($arParams["GID"]);
			$res = current($arResult["GROUPS"]);
			$bFounded = false;
			do
			{
				if (!$bFounded && intVal($res["ID"]) != $arParams["GID"]):
					continue;
				elseif (intVal($res["ID"]) == $arParams["GID"]):
					$bFounded = true;
					continue;
				elseif ($res["LEFT_MARGIN"] > $arResult["GROUP"]["RIGHT_MARGIN"]):
					break;
				endif;
				$val[] = $res["ID"];
			} while ($res = next($arResult["GROUPS"]));
			$arFilter["@FORUM_GROUP_ID"] = $val;
		}
		else
		{
			$arFilter["FORUM_GROUP_ID"] = $arParams["GID"];
		}
	}
/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["ACTION"] == "SET_BE_READ"):
	if (!check_bitrix_sessid()):
	
	elseif ($arParams["GID"] <= 0):
		ForumSetReadForum(false);
	else:
		$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
		while ($res = $db_res->Fetch())
		{
			ForumSetReadForum($res["ID"]);
		}
	endif;
	LocalRedirect($APPLICATION->GetCurPageParam('', array('sessid', 'ACTION')));
endif;
/********************************************************************
				/Action
********************************************************************/
/************** Forums data ****************************************/
				
	CPageOption::SetOptionString("main", "nav_page_in_session", "N"); // reduce cache size
	$arFilterForum = $arFilter;
	if ($arParams["MINIMIZE_SQL"] == "Y" && $GLOBALS["USER"]->IsAuthorized()):
		$arFilterForum["RENEW"] = $GLOBALS["USER"]->GetID();
	endif;

	$arForumOrder = array(
		"FORUM_GROUP_SORT"=>"ASC",
		"FORUM_GROUP_ID"=>"ASC",
		"SORT"=>"ASC",
		"NAME"=>"ASC"
	);
	$arForumAddParams = array(
		'bDescPageNumbering' => ($arParams["USE_DESC_PAGE"] == "Y"),
		'nPageSize' => $arParams["FORUMS_PER_PAGE"],
		'bShowAll' => false,
		'sNameTemplate' => $arParams["NAME_TEMPLATE"]
	);

	if (!function_exists('__forumIndexGetPermissions'))
	{
		function __forumIndexGetPermissions(&$arRes, &$arNewMessage = null)
		{
			static $arNew = null;
			$result = false;

			if ($arNew === null && $arNewMessage !== null)
			{
				$arNew = $arNewMessage;
			}

			$arForums = array();
			if (isset($arRes['FORUMS']) && is_array($arRes['FORUMS']))
				$arForums =& $arRes['FORUMS'];
			elseif (isset($arRes['FORUM']) && is_array($arRes['FORUM']))
				$arForums =& $arRes['FORUM'];

			foreach ($arForums as &$res)
			{
				$res["PERMISSION"] = ForumCurrUserPermissions($res["ID"]);
				if ($res["PERMISSION"] >= "Q")
				{
					foreach(array("POSTER_ID", "POST_DATE", "POSTER_NAME", "MESSAGE_ID") as $key):
						$res["~LAST_".$key] = $res["~ABS_LAST_".$key];
						$res["LAST_".$key] = $res["ABS_LAST_".$key];
					endforeach;
					$res["TID"] = $res["ABS_TID"];
					$res["TITLE"] = $res["ABS_TITLE"];
					$result = true;
				}

				$res["~NewMessage"] = ((isset($arNew[$res['ID']])) ? intval($arNew[$res['ID']]) : 0);
				$res["NewMessage"] = ($res["~NewMessage"] > 0 ? "Y" : "N");
			}

			if (isset($arRes['GROUPS']) && is_array($arRes['GROUPS']))
				foreach($arRes['GROUPS'] as &$res1):
					$result = (__forumIndexGetPermissions($res1) || $result);
				endforeach;
			return $result;
		}
	}

	$arNavParams = array(
		"nPageSize" => $arParams["FORUMS_PER_PAGE"],
		"bShowAll" => false
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);

if ($this->StartResultCache($arParams["CACHE_TIME"], array($arFilterForum, $arForumAddParams, $arNavigation)))
{
	$arForumAddParams['nav_result'] = false;
	$dbForumNav = CForumNew::GetListEx(
		$arForumOrder,
		$arFilterForum,
		false,
		false,
		$arForumAddParams
	);
	$arForumAddParams['nav_result'] = $dbForumNav;
	$dbForum = CForumNew::GetListEx(
		$arForumOrder,
		$arFilterForum,
		false,
		false,
		$arForumAddParams
	);

	$arResult["NAV_RESULT"] = $dbForumNav;
	$arResult["NAV_STRING"] = $dbForumNav->GetPageNavStringEx($navComponentObject, GetMessage("F_FORUM"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["NAV_PAGE"] = $dbForumNav->NavNum.':'.$dbForumNav->NavPageNomer;

	$arForums = array();
	while ($res = $dbForum->GetNext())
	{
		$res["MODERATE"] = array("TOPICS" => 0, "POSTS" => intVal($res["POSTS_UNAPPROVED"]));
		$res["mCnt"] = $res["MODERATE"]["POSTS"];

		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
		$res["LAST_POST_DATE"] = (intval($res["LAST_MESSAGE_ID"]) > 0 ?
			CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat())) : "");
		$res["ABS_LAST_POST_DATE"] = (intval($res["ABS_LAST_MESSAGE_ID"]) > 0 ?
			CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["ABS_LAST_POST_DATE"], CSite::GetDateFormat())) : "");
		$res["URL"] = array(
			"MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"],
				array("FID" => $res["ID"], "TID" => "s")),
			"TOPICS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])),
			"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
				array("FID" => $res["ID"], "TID" => $res["TID"], "TITLE_SEO" => $res["TITLE_SEO"],
					"MID" => $res["LAST_MESSAGE_ID"]))."#message".$res["LAST_MESSAGE_ID"],
			"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"],
				array("UID" => $res["LAST_POSTER_ID"]))	);
/************** For custom template ********************************/
		$res["topic_list"] = $res["URL"]["TOPICS"];
		$res["message_appr"] = $res["URL"]["MODERATE_MESSAGE"];
		$res["message_list"] = $res["URL"]["MESSAGE"];
		$res["profile_view"] = $res["URL"]["AUTHOR"];
/*******************************************************************/
		$res["FORUM_GROUP_ID"] = intVal($res["FORUM_GROUP_ID"]);
		$arGroupForum[$res["FORUM_GROUP_ID"]]["FORUM"][] = $res;
		$arResult["FORUMS_LIST"][$res["ID"]] = $res["ID"];
		CForumCacheManager::SetTag($this->GetCachePath(), "forum_msg_count".$res["ID"]);
	}
	$arGroups = array();
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

	foreach ($arGroupForum as $PARENT_ID => $res)
	{
		$bResult = true;
		$res = array("FORUMS" => $res["FORUM"]);

		$count = 0;
		while (
			intval($PARENT_ID) > 0
			&&
			(
				$arParams["GID"] <= 0
					||
				(
					intVal($arResult["GROUPS"][$arParams["GID"]]["LEFT_MARGIN"]) <= intVal($arResult["GROUPS"][$PARENT_ID]["LEFT_MARGIN"])
						&&
					intVal($arResult["GROUPS"][$PARENT_ID]["RIGHT_MARGIN"]) <= intVal($arResult["GROUPS"][$arParams["GID"]]["RIGHT_MARGIN"])
				)
			)
		)
		{
			if (!array_key_exists("GROUP_".$PARENT_ID, $arResult["URL"]))
			{
				$arResult["URL"]["GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
				$arResult["URL"]["~GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
			}
			if (!array_key_exists($PARENT_ID, $arResult["GROUPS"]))
			{
				$bResult = false;
				$PARENT_ID = false;
				break;
			}
			$res = array($PARENT_ID => __array_merge($arResult["GROUPS"][$PARENT_ID], $res));
			$PARENT_ID = $arResult["GROUPS"][$PARENT_ID]["PARENT_ID"];

			$res = array("GROUPS" => $res);
			if ($PARENT_ID > $arParams["GID"])
				$res = __array_merge($arResult["GROUPS"][$PARENT_ID], $res);
		}
		if ($bResult == true)
			$arGroups = __array_merge($arGroups, $res);
	}

	foreach ($arGroupForum as $key => $val)
	{
		$key = intVal($key);
		if (array_key_exists($key, $arResult["GROUPS"]))
			$arGroupForum[$key] = array_merge($arResult["GROUPS"][$key], $val);
	}

	$arResult["FORUM"] = $arGroupForum; // out of date
	$arResult["FORUMS"] = $arGroups;
	$this->EndResultCache();
}

$arNew = array();
$dbNew = CForumNew::GetForumRenew(array(
	'FORUM_ID' => $arResult["FORUMS_LIST"]
));
if ($dbNew)
{
	while($arN = $dbNew->Fetch())
	{
		$arNew[$arN['FORUM_ID']] = $arN['TCRENEW'];
	}
}

$bCanModerate = ($arResult["USER"]["CAN_MODERATE"] == "Y");
$bCanModerate = ($bCanModerate || __forumIndexGetPermissions($arResult['FORUMS'], $arNew));

if (is_array($arResult["FORUMS"]['GROUPS']))
{
	foreach ($arResult["FORUMS"]['GROUPS'] as $groupID => $arGroup)
	{
		$bCanModerate = ($bCanModerate || __forumIndexGetPermissions($arResult["FORUMS"]['GROUPS'][$groupID]));
	}
}

if (is_array($arResult["FORUM"]))
{
	foreach ($arResult["FORUM"] as $groupID => $arGroup)
	{
		$bCanModerate = ($bCanModerate || __forumIndexGetPermissions($arResult["FORUM"][$groupID]));
	}
}

$arResult["USER"]["CAN_MODERATE"] = ($bCanModerate ? "Y" : "N");

/************** Navigation *****************************************/
	if ($arParams["GID"] > 0):
		$PARENT_ID = intVal($arResult["GROUP"]["PARENT_ID"]);
		while ($PARENT_ID > 0)
		{
			$arResult["GROUP_NAVIGATION"][] = $arResult["GROUPS"][$PARENT_ID];
			if (!array_key_exists("GROUP_".$PARENT_ID, $arResult["URL"]))
			{
				$arResult["URL"]["GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
				$arResult["URL"]["~GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
			}
			$PARENT_ID = intVal($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
		}
		$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
	endif;
/************** Forums for guest (RSS) *****************************/
unset($arFilter["APPROVED"]);
$arFilter["PERMS"] = array(2, 'A'); 
$arFilter["ACTIVE"] = "Y";
$arFilter["LID"] = SITE_ID;

$cache_id = "forums_for_guest_".serialize(array($arFilter));
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
$arForums = array();
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arForums"]))
		$arForums = $res["arForums"];
}
if (!is_array($arForums) || count($arForums) <= 0)
{
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	if (!defined('forum_index_11_5_0'))
	{
		if ($res = $db_res->GetNext())
			$arForums[$res["ID"]] = $res;
	}
	else
	{
		while ($res = $db_res->GetNext())
			$arForums[$res["ID"]] = $res;
	}

	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array(
			"arForums" => $arForums,
		));
	endif;
}
$arResult["FORUMS_FOR_GUEST"] = (is_array($arForums) ? $arForums : array());
/********************************************************************
				/Data
********************************************************************/

/************** For custom template ********************************/
$arResult["index"] = $arResult["URL"]["INDEX"]; 
$arResult["DrawAddColumn"] = ($arResult["USER"]["CAN_MODERATE"] == "Y" ? "Y" : "N");
$arResult["PARSER"] = $parser;
/************** For custom template/********************************/

/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/

if ($arParams["SET_TITLE"] != "N"):
	$sTitle = ($arParams["GID"] <= 0 ? GetMessage("F_TITLE") : $arResult["GROUP"]["NAME"]);
	$APPLICATION->SetTitle($sTitle);
endif;

if ($arParams["SET_NAVIGATION"] != "N" && $arParams["GID"] > 0):
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $arResult["URL"]["~GROUP_".$res["ID"]]);
	endforeach;
	$APPLICATION->AddChainItem($arResult["GROUP"]["NAME"]);
endif;


return $arResult["FORUMS_LIST"];
?>