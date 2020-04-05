<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);

$arParams["POST_ID"] = trim($arParams["POST_ID"]);
$bIDbyCode = false;
if(!is_numeric($arParams["POST_ID"]) || strlen(IntVal($arParams["POST_ID"])) != strlen($arParams["POST_ID"]))
{
	$arParams["POST_ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["~POST_ID"]));
	$bIDbyCode = true;
}
else
	$arParams["POST_ID"] = IntVal($arParams["POST_ID"]);

if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

$bSoNet = false;
$bGroupMode = false;
if (CModule::IncludeModule("socialnetwork") && (IntVal($arParams["SOCNET_GROUP_ID"]) > 0 || IntVal($arParams["USER_ID"]) > 0))
{
	$bSoNet = true;

	if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
		$bGroupMode = true;
	
	if($bGroupMode)
	{
		if(!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog"))
		{
			return;
		}
	}
	else
	{
		if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		{
			return;
		}
	}
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 10;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

if (strtolower($arParams["TYPE"]) == "rss1")
	$arResult["TYPE"] = "RSS .92";
if (strtolower($arParams["TYPE"]) == "rss2")
	$arResult["TYPE"] = "RSS 2.0";
if (strtolower($arParams["TYPE"]) == "atom")
	$arResult["TYPE"] = "Atom .03";

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
if($arParams["MODE"] != "C")
	$arParams["MODE"] = "P";
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

if(is_numeric($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]))
{
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY"] = floatVal($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]);
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = "Y";
}

$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);

$arFeedParams = Array("ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"], "IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"], "IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"]);

$user_id = IntVal($USER->GetID());
if($bSoNet)
{
	$arFeedParams["USE_SOCNET"] = "Y";
	if($bGroupMode)
		$arFeedParams["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
	else
		$arFeedParams["USER_ID"] = $arParams["USER_ID"];
}
else
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
}

$cache = new CPHPCache; 
$cache_id = "blog_rss_out_".serialize($arParams);
if($bSoNet)
	$cache_id .= "_".$user_id;
$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/";

if (!empty($arBlog) || $bSoNet)
{
	if($arParams["MODE"] == "C")
	{
		if($bIDbyCode)
			$arParams["POST_ID"] = CBlogPost::GetID($arParams["POST_ID"], $arBlog["ID"]);

		$arPost = CBlogPost::GetByID($arParams["POST_ID"]);
		if(empty($arPost) && !$bIDbyCode)
		{
			$arParams["POST_ID"] = CBlogPost::GetID($arParams["POST_ID"], $arBlog["ID"]);
			$arPost = CBlogPost::GetByID($arParams["POST_ID"]);
		}
		if(!empty($arPost))
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
		$arFeedParams["PATH_TO_BLOG"] = $arParams["PATH_TO_BLOG"];
		$arFeedParams["PATH_TO_POST"] = $arParams["PATH_TO_POST"];
		$arFeedParams["PATH_TO_USER"] = $arParams["PATH_TO_USER"];
		$arFeedParams["NO_URL_IN_COMMENTS"] = $arParams["NO_URL_IN_COMMENTS"];
		$arFeedParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = $arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"];
		$arFeedParams["NO_URL_IN_COMMENTS_AUTHORITY"] = $arParams["NO_URL_IN_COMMENTS_AUTHORITY"];
	}
	if(($arParams["MODE"] == "C" && !empty($arPost)) || $arParams["MODE"] == "P")
	{
		$APPLICATION->RestartBuffer();
		header("Content-Type: text/xml; charset=".LANG_CHARSET);
		header("Pragma: no-cache");

		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$cache->Output();
		}
		else
		{
			if($arParams["MODE"] == "P")
				$textRSS = CBlog::BuildRSS($arBlog["ID"], $arResult["TYPE"], $arParams["MESSAGE_COUNT"], $arParams["PATH_TO_BLOG"], $arParams["PATH_TO_POST"], $arParams["PATH_TO_USER"], $bSoNet, $arFeedParams);
			else
				$textRSS = CBlogComment::BuildRSS($arPost["ID"], $arBlog["ID"], $arResult["TYPE"], $arParams["MESSAGE_COUNT"], $arFeedParams);
			
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

				echo $textRSS;

			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array());
		}
		die();
	}
	else
	{
		ShowError(GetMessage("BLOG_RSS_NO_BLOG_POST"));
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	ShowError(GetMessage("BLOG_RSS_NO_BLOG"));
	CHTTP::SetStatus("404 Not Found");
}
?>
