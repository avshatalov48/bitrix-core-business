<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule("idea"))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}

$arBlog = CBlog::GetByUrl($arParams["IDEA_URL"]);

$cache = new CPHPCache; 
$cache_id = "idea_rss_out_".serialize($arParams);
$cache_path = "/".SITE_ID."/idea/".$arBlog["ID"]."/rss_list/";

$arParams["RSS_CNT"] = intval($arParams["RSS_CNT"]);
if($arParams["RSS_CNT"] == 0)
	$arParams["RSS_CNT"] = 10;

if(!is_array($arParams["FILTER"]))
	$arParams["FILTER"] = array();

$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);

$arTemplateParams = array(
	"CUSTOM_TITLE" => $arParams["~CUSTOM_TITLE"],
	"PATH_TO_POST" => $arParams["PATH_TO_POST"],
	"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
	"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
	"USER" => $arParams["USER"],
	"INDEX" => $arParams["INDEX"],
	"BLOG_POST" => $arParams["PATH_TO_POST"],
	"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
);

$APPLICATION->RestartBuffer();

header("Content-Type: text/xml");
header("Pragma: no-cache");
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	$cache->Output();
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	echo CIdeaManagment::getInstance()->GetRSS(
		$arParams["IDEA_URL"],
		$arParams["RSS_TYPE"],
		$arParams["RSS_CNT"],
		SITE_ID,
		$arTemplateParams,
		$arParams["FILTER"]
	);

	if($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array());
}
die();
?>
