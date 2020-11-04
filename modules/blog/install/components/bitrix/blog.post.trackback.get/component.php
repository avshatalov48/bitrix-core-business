<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = intval($arParams["ID"]);
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");

if (mb_strtoupper($_SERVER["REQUEST_METHOD"]) != "POST")
{
	LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arParams["BLOG_URL"], "post_id"=>$arParams["ID"])));
}

$arResult = array();
if (isset($_REQUEST))
{
	if (isset($_REQUEST["title"]))
		$arResult["title"] = $_REQUEST["title"];
	if (isset($_REQUEST["url"]))
		$arResult["url"] = $_REQUEST["url"];
	if (isset($_REQUEST["excerpt"]))
		$arResult["excerpt"] = $_REQUEST["excerpt"];
	if (isset($_REQUEST["blog_name"]))
		$arResult["blog_name"] = $_REQUEST["blog_name"];
}

$APPLICATION->RestartBuffer();
header("Pragma: no-cache");
CBlogTrackback::GetPing($arParams["BLOG_URL"], $arParams["ID"], $arResult);
die();
?>