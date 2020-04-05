<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["TB_LENGTH"] = Intval($arParams["TB_LENGTH"]);
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_TRACKBACK"] = trim($arParams["PATH_TO_TRACKBACK"]);
if(strlen($arParams["PATH_TO_TRACKBACK"])<=0)
	$arParams["PATH_TO_TRACKBACK"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=trackback&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if(COption::GetOptionString("blog","enable_trackback", "Y") == "Y")
{
	if($arPost = CBlogPost::GetByID($arParams["ID"]))
	{
		$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
		$arResult["Post"] = $arPost;
		$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
		$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
		$arResult["USER_ID"] = $USER->GetID();
		$arResult["PostPerm"] = CBlogPost::GetBlogUserPostPerms($arParams["ID"], $arResult["USER_ID"]);

		$arResult["Blog"] = $arBlog;
		$arBlogUrl = CBlog::GetByUrl($arParams["BLOG_URL"]);
		if(!empty($arBlogUrl) && $arBlogUrl["URL"] == $arBlog["URL"])
		{
			if($arPost["ENABLE_TRACKBACK"]=="Y")
			{
				if($arResult["PostPerm"] > BLOG_PERMS_DENY)
				{
					if(check_bitrix_sessid() && IntVal($_GET["delete_trackback_id"])>0 && CBlogPost::CanUserDeletePost(IntVal($arPost["ID"]), IntVal($USER->GetID())))
					{
						CBlogTrackback::Delete(IntVal($_GET["delete_trackback_id"]));
						
						if (intval($arBlog["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule("socialnetwork") && method_exists("CSocNetGroup", "GetSite"))
						{
							$arSites = array();
							$rsGroupSite = CSocNetGroup::GetSite($arBlog["SOCNET_GROUP_ID"]);
							while($arGroupSite = $rsGroupSite->Fetch())
								$arSites[] = $arGroupSite["LID"];
						}
						else
							$arSites = array(SITE_ID);

						foreach ($arSites as $site_id_tmp)
							BXClearCache(True, "/".$site_id_tmp."/blog/".$arBlog["URL"]."/trackback/".$arPost["ID"]."/");
					}
					$cache = new CPHPCache;
					$cache_id = "blog_trackback_".serialize($arParams)."_".$arResult["PostPerm"];
					if(($tzOffset = CTimeZone::GetOffset()) <> 0)
						$cache_id .= "_".$tzOffset;
					$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$arPost["ID"]."/";

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

						$arResult["TrackBack"] = Array();
						$dbTrack = CBlogTrackback::GetList(Array("POST_DATE" => "DESC"), Array("BLOG_ID"=>$arPost["BLOG_ID"], "POST_ID"=>$arPost["ID"]));
						while($arTrack = $dbTrack->GetNext())
						{
							if($arParams["TB_LENGTH"])
								$arTrack["PREVIEW_TEXT"] = TruncateText($arTrack["PREVIEW_TEXT"], $arParams["TB_LENGTH"]);
							$arTrack["urlToDelete"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("delete_trackback_id=".$arTrack["ID"], Array("sessid", "delete_trackback_id")));
							$arTrack["POST_DATE_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTrack["POST_DATE"], CSite::GetDateFormat("FULL")));
							$arResult["TrackBack"][] = $arTrack;
						}

						$serverName = "";
						$dbSite = CSite::GetList(($b = "sort"), ($o = "asc"), array("LID" => SITE_ID));
						if ($arSite = $dbSite->Fetch())
							$serverName = $arSite["SERVER_NAME"];

						if (strlen($serverName) <= 0)
						{
							if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
								$serverName = SITE_SERVER_NAME;
							else
								$serverName = COption::GetOptionString("main", "server_name", "");
						}
						$serverName = \Bitrix\Main\Text\HtmlFilter::encode($serverName);

						$arResult["urlToTrackback"] = "http://".$serverName.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TRACKBACK"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"]));
						if ($arParams["CACHE_TIME"] > 0)
							$cache->EndDataCache(array("templateCachedData"=>$this-> GetTemplateCachedData(), "arResult"=>$arResult));
					}
				}
			}
		}
	}
}
//else
	//$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_POST");
	
$this->IncludeComponentTemplate();
?>