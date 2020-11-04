<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = intval($arParams["MESSAGE_COUNT"])>0 ? intval($arParams["MESSAGE_COUNT"]): 10;
$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
	
if(!is_array($arParams["PARAM_GROUP_ID"]))
	$arParams["PARAM_GROUP_ID"] = array($arParams["PARAM_GROUP_ID"]);
foreach($arParams["PARAM_GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["PARAM_GROUP_ID"][$k]);

if (mb_strtolower($arParams["TYPE"]) == "rss1")
	$arResult["TYPE"] = "RSS .92";
if (mb_strtolower($arParams["TYPE"]) == "rss2")
	$arResult["TYPE"] = "RSS 2.0";
if (mb_strtolower($arParams["TYPE"]) == "atom")
	$arResult["TYPE"] = "Atom .03";

if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams["IMAGE_MAX_WIDTH"] = intval($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = intval($arParams["IMAGE_MAX_HEIGHT"]);

$cache = new CPHPCache; 
$cache_id = "blog_rss_all_out_".$arParams["GROUP_ID"]."_".$arParams["MESSAGE_COUNT"]."_".$arResult["TYPE"]."_".intval($USER->GetID())."_".$arParams["PATH_TO_POST"]."_".$arParams["PATH_TO_USER"];
$cache_path = "/".SITE_ID."/blog/rss_all/";

$APPLICATION->RestartBuffer();
header("Content-Type: text/xml; charset=".LANG_CHARSET);
header("Pragma: no-cache");

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$cache->Output();
}
else
{
	$arPathTemplates = Array(
				"BLOG"		=>	$arParams["PATH_TO_BLOG"],
				"GROUP_BLOG_POST"	=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
				"BLOG_POST"		=>	$arParams["PATH_TO_POST"],
				"USER"		=>	$arParams["PATH_TO_USER"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"], 
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
		);
	if ($textRSS = CBlog::BuildRSSAll($arParams["GROUP_ID"], $arResult["TYPE"], $arParams["MESSAGE_COUNT"], SITE_ID, false, false, array(), $arPathTemplates, $arParams["PARAM_GROUP_ID"], $arParams["USE_SOCNET"]))
	{
		if ($arParams["CACHE_TIME"] > 0)
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

		echo $textRSS;

		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array());
	}
}
die();
?>