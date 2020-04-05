<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("FMM_NO_MODULE"));
	return false;
endif;
if (!function_exists("__array_merge"))
{
	function __array_merge($arr1, $arr2, $deep = false)
	{
		$arResult = $arr1;
		static $ii = 0;
		$ii++;
		$deep = ($deep == false ? 0 : $deep);
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
			$deep++;
			$arResult[$key2] = __array_merge($arResult[$key2], $val2, $deep);
		}
		return $arResult;
	}
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$_REQUEST["search_template"] = trim($_REQUEST["search_template"]);
	$_REQUEST["search_field"] = trim(strtolower($_REQUEST["search_field"]));
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"topic_search" => "PAGE_NAME=topic_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["TOPICS_PER_PAGE"] = intVal(intVal($arParams["TOPICS_PER_PAGE"]) > 0 ? $arParams["TOPICS_PER_PAGE"] : 
		COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$arResult["TID"] = intVal($_REQUEST["TID"]);
$arResult["FID"] = intVal($_REQUEST["FID"]);
$arResult["SELF_CLOSE"] = ($arResult["TID"] > 0 ? "Y" : "N");
$arResult["TOPIC"] = array();
$arResult["TOPICS"] = array();
$arResult["FORUMS"] = array();
$arResult["FORUM"] = array("data" => array(), "active" => $arResult["FID"]);
$arResult["GROUPS_FORUMS"] = array(); // declared in result_modifier.php
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_SEARCH"], array());
$arResult["sessid"] = bitrix_sessid_post();
$arResult["SITE_CHARSET"] = SITE_CHARSET;

$arResult["SHOW_RESULT"] = "N";

$strErrorMessage = "";
$strOKMessage = "";
$arFilter = array();
$bVarsFromForm = false;
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Forums *********************************************/
$arFilter = array();
$arForums = array();
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
if (empty($arResult["FORUMS"])):
	ShowError(GetMessage("F_ERROR_FORUM_IS_LOST"));
	return false;
endif;
$arGroupsForums = array();
$arGroups = array();
foreach ($arResult["FORUMS"] as $key => $res)
{
	$arGroupsForums[$res["FORUM_GROUP_ID"]][$key] = $res;
}
foreach ($arGroupsForums as $PARENT_ID => $res)
{
	$bResult = true;
	$res = array("FORUMS" => $res);
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
/************** Topics *********************************************/
if ($arResult["TID"] > 0)
{
	$res = CForumTopic::GetByIDEx($arResult["TID"]);
	if (!empty($res) && $res["STATE"] != "L" && !empty($arResult["FORUMS"][$res["FORUM_ID"]]))
	{
		$arResult["TOPIC"] = $res;
		$arResult["FORUM"] = $arResult["FORUMS"][$res["FORUM_ID"]];
		
		$arResult["TOPIC"]["~TITLE"] = $arResult["TOPIC"]["TITLE"];
		$arResult["TOPIC"]["TITLE"] = Cutil::JSEscape($arResult["TOPIC"]["TITLE"]);
		$arResult["TOPIC"]["LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $arResult["FORUM"]["ID"], "TID" => $arResult["TOPIC"]["ID"], "TITLE_SEO" => $arResult["TOPIC"]["TITLE_SEO"], "MID" => "s"));
		$arResult["FORUM"]["LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
			array("FID" => $arResult["FORUM"]["ID"]));
	}
}
elseif (strlen($_REQUEST["search_template"]) > 0) 
{
	$arFilter = array("@FORUM_ID" => array_keys($arResult["FORUMS"]));
	if (intVal($_REQUEST["FID"]) > 0)
		$arFilter["FORUM_ID"] = intVal($_REQUEST["FID"]);
	if (($_REQUEST["search_field"] == "title") || ($_REQUEST["search_field"] == "description"))
		$arFilter[strToUpper($_REQUEST["search_field"])] = $_REQUEST["search_template"];
	else
		$arFilter["TITLE_ALL"] = $_REQUEST["search_template"];

	$db_res = CForumTopic::GetListEx(array("ID" => "DESC"), $arFilter, false, false,
		array(
			"bDescPageNumbering" => false,
			"nPageSize" => $arParams["TOPICS_PER_PAGE"],
			"bShowAll" => false,
			"sNameTemplate" => $arParams["NAME_TEMPLATE"]
		)
	);
	$db_res->NavStart($arParams["TOPICS_PER_PAGE"], false);
	$db_res->bShowAll = false;
	$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
	$arResult["NAV_RESULT"] = $db_res;
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, " ", $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do
		{
			$res["topic_id_search"] = ForumAddPageParams($arResult["CURRENT_PAGE"], array("TID" => $res["ID"]));
			$arResult["TOPICS"][] = $res;
		}while ($res = $db_res->GetNext());
	}
}
/************** For custom templates *******************************/
$arResult["FORUMS_LIST"] = array(
	"data" => $arResult["FORUMS"], 
	"active" => $arResult["FID"]);
if (!empty($arResult["TOPICS"])):
	$arResult["TOPIC"] = $arResult["TOPICS"]; 
	$arResult["SHOW_RESULT"] = "Y";
endif;
/********************************************************************
				/Data
********************************************************************/

$APPLICATION->RestartBuffer();
header("Pragma: no-cache");
$this->IncludeComponentTemplate();
die();
?>
