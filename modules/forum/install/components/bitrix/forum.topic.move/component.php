<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif(!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FM_AUTH"));
endif;
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
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	if ($_SERVER['REQUEST_METHOD'] == "POST")
		$arParams["TID"] = $_POST["TID"];
	else 
		$arParams["TID"] = (empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["newFID"] = intVal($_REQUEST["newFID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"topic_move" => "PAGE_NAME=MOVE&FID=#FID#&TID=#TID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
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
$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
$topics = ForumMessageExistInArray($arParams["TID"]);

if (!$arResult["FORUM"]):
	ShowError(GetMessage("F_ERROR_FORUM_IS_LOST"));
	return false;
elseif (ForumCurrUserPermissions($arResult["FORUM"]["ID"]) < "Q"):
	$APPLICATION->AuthForm(GetMessage("FM_NO_FPERMS"));
elseif (empty($topics)):
	ShowError(GetMessage("F_ERROR_TOPICS_IS_EMPTY"));
	return false;
endif;

/********************************************************************
				Default values
********************************************************************/
$GLOBALS['APPLICATION']->ResetException();
$arResult["TOPICS"] = array();
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["GROUP_NAVIGATION"] = array();
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["sessid"] = bitrix_sessid_post();
$arResult["arForum"] = array("data" => array(), "active" => $arParams["newFID"]);
$bVarsFromForm = false;
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_MOVE"], 
	array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
$arResult["URL"] = array(
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
	array("FID" => $arParams["FID"])), 
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], 
	array("FID" => $arParams["FID"])));
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if (strToUpper($_REQUEST["action"])=="MOVE" && check_bitrix_sessid())
{
	$strErrorMessage = "";
	$strOKMessage = "";
	$result = false;
	if (intVal($arParams["newFID"])<=0)
		$strErrorMessage = GetMessage("FM_EMPTY_DEST_FORUM").". \n";
	else 
	{
		$arResult["FORUM_NEW"] = CForumNew::GetByID($arParams["newFID"]);
		if (ForumCurrUserPermissions($arParams["newFID"]) < "Q" && ($arResult["FORUM_NEW"]["ALLOW_MOVE_TOPIC"]!="Y"))
			$strErrorMessage = GetMessage("FM_NO_DEST_FPERMS").". \n";
		else 
			$result = CForumTopic::MoveTopic2Forum($topics, $arParams["newFID"], $_REQUEST["leaveLink"]);
	}
	
	if (!$result)
	{
		if ($GLOBALS['APPLICATION']->GetException())
		{
			$arErr = $GLOBALS['APPLICATION']->ERROR_STACK;
			if (is_array($arErr) && count($arErr) > 0)
			{
				foreach ($arErr as $res)
					$strErrorMessage .= $res["msg"]."\n";
			}
			$err = $GLOBALS['APPLICATION']->GetException();
			$strErrorMessage .= $err->GetString();
		}
		$bVarsFromForm = true;
	}
	else
	{
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arResult["FORUM_NEW"]["ID"])));
	}
	$arResult["ERROR_MESSAGE"] = $strErrorMessage;
	$arResult["OK_MESSAGE"] = $strOKMessage;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Topic for move *************************************/
$arFilter = array("@ID" => implode(",", $topics), "FORUM_ID" => $arParams["FID"]);
if (!CForumUser::IsAdmin())
	$arFilter["PERMISSION_STRONG"] = true;
$db_res = CForumTopic::GetListEx(array(), $arFilter);
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
		$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s"));
		$res["read_last_message"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => intVal($res["LAST_MESSAGE_ID"])))."#message".$res["LAST_MESSAGE_ID"];
		$res["USER_START_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => intVal($res["USER_START_ID"])));
		$res["LAST_POSTER_HREF"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => intVal($res["LAST_POSTER_ID"])));
		
		$arResult["TOPICS"][$res["ID"]] = $res;
	}while ($res = $db_res->GetNext());
}
$arParams["TID"] = implode(",", array_keys($arResult["TOPICS"]));
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
$cache_path = $cache_path_main."forums";
$arForums = false;
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
$arResult["FORUMS"] = $arForums;
/*******************************************************************/
$arGroupForum = array();
foreach ($arForums as $res):
	if ($res["ID"] == $arParams["FID"])
		continue;
	$arGroupForum[intVal($res["FORUM_GROUP_ID"])]["FORUMS"][] = $res;
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
/************** Group Navigation ***********************************/
if ($arResult["FORUM"]["FORUM_GROUP_ID"] > 0):
	$PARENT_ID = intVal($arResult["FORUM"]["FORUM_GROUP_ID"]);
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
/************** Custom components **********************************/
$arResult["arForum"]["data"] = $arResult["FORUMS"];
$arResult["list"] = $arResult["URL"]["LIST"];
$arResult["TOPIC"] = $arResult["TOPICS"]; 
/********************************************************************
				/Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
{
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["~NAME"], $arResult["URL"]["~GROUP_".$res["ID"]]);
	endforeach;
	$APPLICATION->AddChainItem($arResult["FORUM"]["NAME"], $arResult["URL"]["~LIST"]);
	$APPLICATION->AddChainItem(GetMessage("FM_TITLE"));
}
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FM_TITLE"));
/*******************************************************************/
$this->IncludeComponentTemplate();
?>