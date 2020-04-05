<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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

/** @var CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["SORT"] = ($arParams["SORT"] == "CNT" ? "CNT" : "NAME");
$arParams["SORT_BY"] = ($arParams["SORT_BY"] == "ASC" ? "ASC" : "DESC");
$arParams["PAGE_ELEMENTS"] = ((intVal($arParams["PAGE_ELEMENTS"]) > 0) ? intVal($arParams["PAGE_ELEMENTS"]) : 1000);
$arParams["PERIOD"] = intVal($arParams["PERIOD"]);
$arParams["CHECK_DATES"] = ($arParams["CHECK_DATES"]=="Y" ? true : false);
$arParams["~TAGS"] = (empty($arParams["~TAGS"]) ? $_REQUEST["tags"] : $arParams["~TAGS"]);
$arParams["~TAGS"] = trim($arParams["~TAGS"]);
$arParams["TAGS"] = htmlspecialcharsex($arParams["~TAGS"]);
$arParams["SEARCH"] = (empty($arParams["SEARCH"]) ? $_REQUEST["q"] : $arParams["~SEARCH"]);
$arParams["~SEARCH"] = trim($arParams["SEARCH"]);
$arParams["SEARCH"] = htmlspecialcharsbx($arParams["~SEARCH"]);

if (!empty($arParams["URL_SEARCH"]))
{
	$arResult["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_SEARCH"], array("TAGS" => "tags=#TAGS#"));
	if (strpos($arResult["~URL"], "#TAGS#") === false)
	{
		if (strpos($arResult["~URL"], "?") === false)
			$arResult["~URL"] .= "?";
		else
			$arResult["~URL"] .= "&";
		$arResult["~URL"] .= "tags=#TAGS#";
	}
}
else
{
	$arResult["~URL"] = $APPLICATION->GetCurPageParam("tags=#TAGS#", array("tags"));
}
$arResult["URL"] = htmlspecialcharsbx($arResult["~URL"]);

if (!empty($arParams["~TAGS"]) || !empty($arParams["~SEARCH"]))
{
	$arParams["CACHE_TIME"] = 0;
}

if ($this->StartResultCache(false, array($USER->GetGroups())))
{

	if(!CModule::IncludeModule("search"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("BSF_C_MODULE_NOT_INSTALLED"));
		return;
	}

	if(defined("BX_COMP_MANAGED_CACHE"))
		$CACHE_MANAGER->registerTag("bitrix:search.tags.cloud");

	if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
		$arFILTERCustom = array();
	else
	{
		$arFILTERCustom = $GLOBALS[$arParams["FILTER_NAME"]];
		if(!is_array($arFILTERCustom))
			$arFILTERCustom = array();
	}

	$exFILTER = CSearchParameters::ConvertParamsToFilter($arParams, "arrFILTER");
	$exFILTER["LIMIT"] = $arParams["PAGE_ELEMENTS"];

	$arFilter = array(
		"SITE_ID" => SITE_ID,
		"QUERY" => $arParams["~SEARCH"],
		"TAGS" => $arParams["~TAGS"] ? $arParams["~TAGS"] : "",
	);
	if ($arParams["PERIOD"] > 0)
		$arFilter["DATE_CHANGE"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), time()-($arParams["PERIOD"]*24*3600)+CTimeZone::GetOffset());
	if($arParams["CHECK_DATES"])
		$arFilter["CHECK_DATES"]="Y";

	$arFilter = array_merge($arFILTERCustom, $arFilter);

	$obSearch = new CSearch();
	$obSearch->Search($arFilter, array("CNT" => "DESC"), $exFILTER, true);

	$arResult["ERROR_CODE"] = $obSearch->errorno;
	$arResult["ERROR_TEXT"] = $obSearch->error;

	$arResult["DATE"] = array();
	$arResult["SEARCH"] = array();
	if($obSearch->errorno==0)
	{
		$res = $obSearch->GetNext();
		if(!$res && ($arParams["RESTART"] == "Y") && $obSearch->Query->bStemming)
		{
			$exFILTER["STEMMING"] = false;
			$obSearch = new CSearch();
			$obSearch->Search($arFilter, array("CNT" => "DESC"), $exFILTER, true);

			$arResult["ERROR_CODE"] = $obSearch->errorno;
			$arResult["ERROR_TEXT"] = $obSearch->error;

			if($obSearch->errorno == 0)
			{
				$res = $obSearch->GetNext();
			}
		}

		if($res)
		{
			$arResult["CNT_MIN"] = $res["CNT"];
			$arResult["CNT_MAX"] = $res["CNT"];
			$res["TIME"] = MakeTimeStamp($res["FULL_DATE_CHANGE"]);
			$arResult["TIME_MIN"] = $res["TIME"];
			$arResult["TIME_MAX"] = $res["TIME"];

			$arTags = array();
			if (($arParams["TAGS_INHERIT"] != "N") && (strlen($arParams["TAGS"]) > 0))
			{
				$tmp = explode(",", $arParams["~TAGS"]);
				foreach($tmp as $tag)
				{
					$tag = trim($tag);
					if(strlen($tag) > 0)
						$arTags[$tag] = $tag;
				}
			}

			do
			{
				$arResult["CNT_ALL"] += $res["CNT"];
				if ($arResult["CNT_MIN"] > $res["CNT"])
					$arResult["CNT_MIN"] = $res["CNT"];
				elseif ($arResult["CNT_MAX"] < $res["CNT"])
					$arResult["CNT_MAX"] = $res["CNT"];

				$res["TIME"] = MakeTimeStamp($res["FULL_DATE_CHANGE"]);

				if ($arResult["TIME_MIN"] > $res["TIME"])
					$arResult["TIME_MIN"] = $res["TIME"];
				elseif ($arResult["TIME_MAX"] < $res["TIME"])
					$arResult["TIME_MAX"] = $res["TIME"];

				$tags = $res["~NAME"];
				if (count($arTags) > 0)
				{
					if(array_key_exists($tags, $arTags))
						$tags = implode(",", $arTags);
					else
						$tags .= ",".implode(",", $arTags);
				}

				$res["URL"] = str_replace("#TAGS#", urlencode($tags), $arResult["URL"]);

				$res["NAME_HTML"] = ToLower($res["NAME"]);

				$arResult["SEARCH"][] = $res;
				$arResult["CNT"][$res["NAME"]] = $res["CNT"];
				$arResult["DATE"][$res["NAME"]] = $res["TIME"];
			} while ($res = $obSearch->getNext());
		}
	}

	if ($arParams["SORT"] != "CNT")
	{
		\Bitrix\Main\Type\Collection::sortByColumn($arResult["SEARCH"], array(
			"NAME_HTML" => SORT_ASC,
			"CNT" => SORT_DESC,
		));
	}

	$arResult["TAGS_CHAIN"] = array();
	if ($arParams["~TAGS"])
	{
		$res = array_unique(explode(",", $arParams["~TAGS"]));
		$url = array();
		foreach ($res as $key => $tags)
		{
			$tags = trim($tags);
			if (!empty($tags))
			{
				$url_without = $res;
				unset($url_without[$key]);
				$url[$tags] = $tags;
				$result = array(
					"TAG_NAME" => htmlspecialcharsex($tags),
					"TAG_PATH" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url)), array("tags")),
					"TAG_WITHOUT" => $APPLICATION->GetCurPageParam((count($url_without) > 0 ? "tags=".urlencode(implode(",", $url_without)) : ""), array("tags")),
				);
				$arResult["TAGS_CHAIN"][] = $result;
			}
		}
	}
	$this->IncludeComponentTemplate();
	
	return count($arResult["SEARCH"]);
}
?>