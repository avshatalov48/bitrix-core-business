<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (!is_array($arParams["SITE_LIST"]) || count($arParams["SITE_LIST"]) == 0 || $arParams["SITE_LIST"][0] == "*all*") 
{
	$bSiteAll = true;
	$arParams["SITE_LIST"] = array();
}
else
{
	$bSiteAll = false;
}

$arParams["CACHE_TIME"] = is_set($arParams, "CACHE_TIME") ? intval($arParams["CACHE_TIME"]) : 86400;

$bCache = $arParams["CACHE_TIME"] > 0 && ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"));

if ($bCache)
{
	$arCacheParams = array();
	foreach ($arParams as $key => $value) if (substr($key, 0, 1) != "~") $arCacheParams[$key] = $value;
	$cache = new CPHPCache;

	$CACHE_ID = SITE_ID."|".$componentName."|".md5(serialize($arCacheParams))."|".$USER->GetGroups();
	$CACHE_PATH = "/".SITE_ID.CComponentEngine::MakeComponentPath($componentName);
}

if ($bCache && $cache->InitCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_PATH))
{
	$vars = $cache->GetVars();
	$arResult = $vars["arResult"];
}
else
{
	if ($bCache)
	{
		$cache->StartDataCache();
	}

	$extranetSiteId = (
		IsModuleInstalled('extranet')
			? COption::GetOptionString("extranet", "extranet_site")
			: false
	);

	$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
	$arResult["SITES"] = array();
	while ($arSite = $rsSite->GetNext())
	{
		if (
			(
				!$extranetSiteId
				|| $arSite["LID"] != $extranetSiteId
			)
			&& (
				$bSiteAll
				|| in_array($arSite["LID"], $arParams["SITE_LIST"])
			)
		)
		{
			if (strlen($arSite['DOMAINS']) > 0)
			{
				$arSite['DOMAINS'] = explode("\n", $arSite['DOMAINS']);
				foreach ($arSite['DOMAINS'] as $key => $domain)
				{
					$arSite['DOMAINS'][$key] = trim($domain);
				}
			}

			$arResult["SITES"][] = array(
				"LID" => $arSite["LID"],
				"NAME" => $arSite["NAME"],
				"LANG" => $arSite["LANGUAGE_ID"],
				"DIR" => $arSite["DIR"],
				"DOMAINS" => $arSite["DOMAINS"],
				"CURRENT" => $arSite["LID"] == SITE_ID ? "Y" : "N",
			);
		}
	}

	if ($bCache)
	{
		$cache->EndDataCache(
			array(
				"arResult" => $arResult,
			)
		);
	}	
}

$this->IncludeComponentTemplate();
?>