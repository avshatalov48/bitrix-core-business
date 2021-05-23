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
$cache_path = "/".SITE_ID."/blog/new_blogs/";

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
				"ACTIVE" => "Y",
				"GROUP_SITE_ID"=>SITE_ID
			);	
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["GROUP_ID"] = $arParams["GROUP_ID"];

	$arSelectedFields = array("ID", "NAME", "DESCRIPTION", "URL", "OWNER_ID", "OWNER_NAME", "OWNER_LAST_NAME", "OWNER_SECOND_NAME", "OWNER_LOGIN", "BLOG_USER_ALIAS", "GROUP_ID", "SOCNET_GROUP_ID", "LAST_POST_ID");
	
	if(CModule::IncludeModule("socialnetwork") && $arParams["USE_SOCNET"] == "Y")
	{
		unset($arFilter[">PERMS"]);
		$arSelectedFields[] = "SOCNET_BLOG_READ";
		$arFilter["USE_SOCNET"] = "Y";
	}
	
	if($arParams["BLOG_COUNT"]>0)
		$COUNT = Array("nTopCount" => $arParams["BLOG_COUNT"]);
	else
		$COUNT = false;

	$arResult = Array();
	$dbBlogs = CBlog::GetList(
		$SORT,
		$arFilter,
		false,
		$COUNT,
		$arSelectedFields
	);
	$i=0;
	
	$authors = array();
	while ($arBlog = $dbBlogs->GetNext())
	{
		unset($arBlog["~ID"], $arBlog["~URL"], $arBlog["~OWNER_ID"], $arBlog["~GROUP_ID"], $arBlog["~SOCNET_GROUP_ID"], $arBlog["~LAST_POST_ID"]);
//		save authors params in hit cache var
		if(!array_key_exists($arBlog["OWNER_LOGIN"], $authors))
			$authors[$arBlog["OWNER_LOGIN"]] = CBlogUser::GetUserName($arBlog["BLOG_USER_ALIAS"], $arBlog["OWNER_NAME"], $arBlog["OWNER_LAST_NAME"], $arBlog["OWNER_LOGIN"]);
		$arBlog["AuthorName"] = $authors[$arBlog["OWNER_LOGIN"]];
	
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
		$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
		
		if($i==0)
			$arBlog["FIRST_BLOG"] = "Y";
		$i++;
		$arResult[] = $arBlog;
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>
