<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


$sbParams = array(
	"FB_USE",
	"TW_USE",
	"VK_USE"
);

foreach ($sbParams as $param)
{
	if(isset($arParams[$param]) && $arParams[$param] == "Y")
		$arResult[$param] = true;
	else
		$arResult[$param] = false;
}
$arResult["GP_USE"] = false;

if(isset($arParams["URL_TO_LIKE"]))
	$arResult["URL_TO_LIKE"] = $arParams["URL_TO_LIKE"];
else
{
	$protocol = (CMain::IsHTTPS()) ? "https://" : "http://";
	$arResult["URL_TO_LIKE"] = $protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
}

$arResult["URL_TO_LIKE_ENCODED"] = urlencode($arResult["URL_TO_LIKE"]);

if(isset($arParams["TITLE"]))
	$arResult["TITLE"] = $arParams["TITLE"];
else
	$arResult["TITLE"] = "";

if(isset($arParams["DESCRIPTION"]))
	$arResult["DESCRIPTION"] = $arParams["DESCRIPTION"];
else
	$arResult["DESCRIPTION"] = "";

if(isset($arParams["IMAGE"]))
	$arResult["IMAGE"] = $arParams["IMAGE"];
else
	$arResult["IMAGE"] = "";

/*
 *	Twitter spec params:
 */

if(isset($arParams["TW_VIA"]))
	$arResult["TW_VIA"] = $arParams["TW_VIA"];
else
	$arResult["TW_VIA"] = "";

if(isset($arParams["TW_HASHTAGS"]))
	$arResult["TW_HASHTAGS"] = $arParams["TW_HASHTAGS"];
else
	$arResult["TW_HASHTAGS"] = "";

if(isset($arParams["TW_RELATED"]))
	$arResult["TW_RELATED"] = $arParams["TW_RELATED"];
else
	$arResult["TW_RELATED"] = "";

$this->IncludeComponentTemplate();