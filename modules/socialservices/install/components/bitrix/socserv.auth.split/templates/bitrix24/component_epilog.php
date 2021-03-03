<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arExt = array();
if($arParams["POPUP"])
	$arExt[] = "window";
CUtil::InitJSCore($arExt);
$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/js/socialservices/css/ss.css");
$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/socialservices/ss.js");

if (isset($arResult['AUTH_SERVICES_ICONS']['zoom']))
{
	$cacheId = 'zoom' . '|' . $GLOBALS['USER']->getId();
	$cache = \Bitrix\Main\Data\Cache::createInstance();
	$cache->clean($cacheId, \CZoomInterface::CACHE_DIR_CONNECT_INFO);
}