<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_COUNT"] = intval($arParams["BLOG_COUNT"])>0 ? intval($arParams["BLOG_COUNT"]): 20;
$arParams["SORT_BY1"] = ($arParams["SORT_BY1"] <> '' ? $arParams["SORT_BY1"] : "LAST_POST_DATE");
$arParams["SORT_ORDER1"] = ($arParams["SORT_ORDER1"] <> '' ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = ($arParams["SORT_BY2"] <> '' ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = ($arParams["SORT_ORDER2"] <> '' ? $arParams["SORT_ORDER2"] : "DESC");
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$bShowAll = false;
if($arParams["ID"] == "all")
	$bShowAll = true;
$arParams["ID"] = intval($arParams["~ID"]);
if($arParams["ID"] > 0 && $arParams["ID"]."" != $arParams["~ID"])
{
	ShowError(GetMessage("B_B_GR_NO_GROUP"));
	@define("ERROR_404", "Y");
	CHTTP::SetStatus("404 Not Found");
	return;
}
$arParams["SHOW_BLOG_WITHOUT_POSTS"] = ($arParams["SHOW_BLOG_WITHOUT_POSTS"] == "Y")? "Y" : "N";
$arParams["NAV_TEMPLATE"] = ($arParams["NAV_TEMPLATE"] <> '' ? $arParams["NAV_TEMPLATE"] : "");
$arParams["USE_SOCNET"] = ($arParams["USE_SOCNET"] == "Y") ? "Y" : "N";
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if(empty($arParams["GROUP_ID"]) || (!empty($arParams["GROUP_ID"]) && in_array($arParams["ID"], $arParams["GROUP_ID"])) || $bShowAll)
{
	$arGroup = CBlogGroup::GetByID($arParams["ID"]);
	if(!empty($arGroup) || $bShowAll)
	{
		$arGroup = CBlogTools::htmlspecialcharsExArray($arGroup);
		$arResult["GROUP"] = $arGroup;
		if($arParams["SET_TITLE"]=="Y")
		{
			if($bShowAll)
				$APPLICATION->SetTitle(GetMessage("B_B_GR_TITLE"));
			else
				$APPLICATION->SetTitle(GetMessage("B_B_GR_TITLE_NAME", array("#group#" => $arGroup["NAME"])));
		}
		
		$cache = new CPHPCache;
		$cache_id = "blog_groups_".serialize($arParams)."_".CDBResult::NavStringForCache($arParams["BLOG_COUNT"]);
		if(($tzOffset = CTimeZone::GetOffset()) <> 0)
			$cache_id .= "_".$tzOffset;
				
		$cache_path = "/".SITE_ID."/blog/groups/".$arParams["ID"]."/";

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
				
			$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
			
			$arFilter = Array("GROUP_SITE_ID"=>SITE_ID, "ACTIVE"=>"Y");
			if(!$bShowAll)
				$arFilter["GROUP_ID"] = $arParams["ID"];
			elseif(!empty($arParams["GROUP_ID"]))
				$arFilter["GROUP_ID"] = $arParams["GROUP_ID"];
				
			$arSelectFields = Array("ID", "NAME", "DESCRIPTION", "URL", "SITE_ID", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "OWNER_LOGIN", "OWNER_NAME", "OWNER_LAST_NAME", "OWNER_SECOND_NAME", "LAST_POST_DATE", "LAST_POST_ID", "BLOG_USER_AVATAR", "BLOG_USER_ALIAS", "SOCNET_GROUP_ID");
			
			if(CModule::IncludeModule("socialnetwork") && $arParams["USE_SOCNET"] == "Y")
			{
				unset($arFilter[">PERMS"]);
				$arSelectFields[] = "SOCNET_BLOG_READ";
				$arFilter["USE_SOCNET"] = "Y";
			}
			if($arParams["SHOW_BLOG_WITHOUT_POSTS"] != "Y")
				$arFilter[">LAST_POST_ID"] = 0;

			$dbBlog = CBlog::GetList(
					$SORT,
					$arFilter,
					false,
					array("nPageSize"=>$arParams["BLOG_COUNT"], "bShowAll" => false),
					$arSelectFields
				);
			$arResult["NAV_STRING"] = $dbBlog->GetPageNavString(GetMessage("B_B_GR_TITLE"), $arParams["NAV_TEMPLATE"], false, $component);
			$arResult["BLOG"] = Array();
			while($arBlog = $dbBlog->GetNext())
			{
				$arBlog["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arBlog["LAST_POST_ID"], "user_id" => $arBlog["OWNER_ID"]));
				$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));

				$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
				$arBlog["AuthorName"] = CBlogUser::GetUserName($arBlog["BLOG_USER_ALIAS"], $arBlog["OWNER_NAME"], $arBlog["OWNER_LAST_NAME"], $arBlog["OWNER_LOGIN"]);
				$arBlog["BLOG_USER_AVATAR_ARRAY"] = CFile::GetFileArray($arBlog["BLOG_USER_AVATAR"]);
				if ($arBlog["BLOG_USER_AVATAR_ARRAY"] !== false)
				{
					$arBlog["Avatar_resized"] = CFile::ResizeImageGet(
								$arBlog["BLOG_USER_AVATAR_ARRAY"],
								array("width" => 100, "height" => 100),
								BX_RESIZE_IMAGE_EXACT,
								false
							);

					$arBlog["BLOG_USER_AVATAR_IMG"] = CFile::ShowImage($arBlog["Avatar_resized"]["src"], 100, 100, 'align="right"'); 
				}

				$arBlog["LAST_POST_DATE_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arBlog["LAST_POST_DATE"], CSite::GetDateFormat("FULL")));
				$arResult["BLOG"][] = $arBlog;
			}
				
			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
		}
	}
	else
	{
		$arResult["FATAL_ERROR"] = GetMessage("B_B_GR_NO_GROUP");	
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	$arResult["FATAL_ERROR"] = GetMessage("B_B_GR_NO_GROUP");
	CHTTP::SetStatus("404 Not Found");
}
$this->IncludeComponentTemplate();
?>