<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["BLOG_COUNT"] = intval($arParams["BLOG_COUNT"]);
if(intval($arParams["BLOG_COUNT"])<=0)
	$arParams["BLOG_COUNT"] = 6;
if(intval($arParams["PERIOD_DAYS"])<=0)
	$arParams["PERIOD_DAYS"] = 30;

$arParams["SORT_BY1"] = ($arParams["SORT_BY1"] <> '' ? $arParams["SORT_BY1"] : "DATE_CREATE");
$arParams["SORT_ORDER1"] = ($arParams["SORT_ORDER1"] <> '' ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = ($arParams["SORT_BY2"] <> '' ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = ($arParams["SORT_ORDER2"] <> '' ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["SHOW_DESCRIPTION"] = ($arParams["SHOW_DESCRIPTION"]=="N") ? "N" : "Y";
$arParams["USE_SOCNET"] = ($arParams["USE_SOCNET"] == "Y") ? "Y" : "N";
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
		
$arParams["BLOG_VAR"] = trim($arParams["BLOG_VAR"]);
$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
$arParams["USER_VAR"] = trim($arParams["USER_VAR"]);

if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

$cache = new CPHPCache;
$cache_id = "blog_new_blogs_".serialize($arParams);
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
$cache_path = "/".SITE_ID."/blog/popular_blogs/";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;
	CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
	$cache->Output();
}
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	$arFilter = Array(
		"<=DATE_PUBLISH" => ConvertTimeStamp(time()+$tzOffset, "FULL", false),
		">=DATE_PUBLISH" => ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".$arParams["PERIOD_DAYS"]))+$tzOffset, "FULL", false),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"BLOG_ACTIVE" => "Y",
		"BLOG_GROUP_SITE_ID" => SITE_ID,
		">PERMS" => BLOG_PERMS_DENY,
	);	
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["BLOG_GROUP_ID"] = $arParams["GROUP_ID"];

	$arSelectedFields = array("ID", "BLOG_ID", "PERMS", "NUM_COMMENTS", "VIEWS");
	if(CModule::IncludeModule("socialnetwork") && $arParams["USE_SOCNET"] == "Y")
	{
		unset($arFilter[">PERMS"]);
		$arSelectedFields[] = "SOCNET_BLOG_READ";
		$arFilter["BLOG_USE_SOCNET"] = "Y";
	}

	$dbItem = CBlogPost::GetList(Array("VIEWS" => "DESC", "NUM_COMMENTS" => "DESC"), $arFilter, false, false, $arSelectedFields);
	while($arItem = $dbItem->Fetch())
	{
		$arBlogs[$arItem["BLOG_ID"]]["VIEWS"] += $arItem["VIEWS"];
		$arBlogs[$arItem["BLOG_ID"]]["NUM_COMMENTS"] += $arItem["NUM_COMMENTS"];
	}

	if(!empty($arBlogs))
	{
		uasort($arBlogs, create_function('$a, $b', 'if($a["VIEWS"] == $b["VIEWS"]) { if($a["NUM_COMMENTS"] < $b["NUM_COMMENTS"]) return 1; elseif($a["NUM_COMMENTS"] > $b["NUM_COMMENTS"]) return -1; else return 0;} return ($a["VIEWS"] < $b["VIEWS"])? 1 : -1;'));

		$i = 0;
		foreach($arBlogs as $blogID => $info)
		{
			if($i >= $arParams["BLOG_COUNT"] && intval($arParams["BLOG_COUNT"]) > 0)
				continue;
			$arBlog = CBlog::GetByID($blogID);
			$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
			$arBlog["BlogUser"] = CBlogUser::GetByID($arBlog["OWNER_ID"], BLOG_BY_USER_ID); 
			$arBlog["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arBlog["BlogUser"]);
			$dbUser = CUser::GetByID($arBlog["OWNER_ID"]);
			$arBlog["arUser"] = $dbUser->GetNext();
			$arBlog["AuthorName"] = CBlogUser::GetUserName($arBlog["BlogUser"]["ALIAS"], $arBlog["arUser"]["NAME"], $arBlog["arUser"]["LAST_NAME"], $arBlog["arUser"]["LOGIN"]);

			if(intval($arBlog["SOCNET_GROUP_ID"]) > 0)
			{
				$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
				$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arBlog["SOCNET_GROUP_ID"]));
			}
			else
			{
				$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
				$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
			}
			
			if($i==0)
				$arBlog["FIRST_BLOG"] = "Y";
			
			$i++;
			$arResult[] = $arBlog;
		}
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>
