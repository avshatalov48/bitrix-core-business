<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 20;

$arParams["CACHE_FOR_ADMIN"] = $arParams["CACHE_FOR_ADMIN"]!="N";

//Check if we can not cache
if(!$arParams["CACHE_FOR_ADMIN"] && $USER->IsAdmin())
	$arParams["CACHE_TIME"] = 0;
elseif($arParams["CACHE_TYPE"] == "N" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "N"))
	$arParams["CACHE_TIME"] = 0;

$arParams["IS_ADMIN"] = $USER->IsAdmin();

$obCache = new CPHPCache;
$cache_id = LANG;
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
if($this->startResultCache())
{
	if(!CModule::IncludeModule("statistic"))
	{
		$this->abortResultCache();
		return;
	}

	$arResult["STATISTIC"] = CTraffic::GetCommonValues(array(),true);
	if(!is_array($arResult["STATISTIC"]))
	{
		$this->abortResultCache();
		return;
	}

	$arResult["TODAY"] = GetTime(time(),"SHORT");
	$arResult["NOW"] = GetTime(time()+$tzOffset,"FULL");
	$arResult["IS_ADMIN"] = $arParams["IS_ADMIN"];

	$this->setResultCacheKeys(array());
	$this->includeComponentTemplate();
}
?>
