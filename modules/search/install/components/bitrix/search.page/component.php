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
$this->setFrameMode(false);

if(!CModule::IncludeModule("search"))
{
	ShowError(GetMessage("SEARCH_MODULE_UNAVAILABLE"));
	return;
}
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["SHOW_WHEN"] = $arParams["SHOW_WHEN"]=="Y";
$arParams["SHOW_WHERE"] = $arParams["SHOW_WHERE"]!="N";
if(!is_array($arParams["arrWHERE"]))
	$arParams["arrWHERE"] = array();
$arParams["PAGE_RESULT_COUNT"] = intval($arParams["PAGE_RESULT_COUNT"]);
if($arParams["PAGE_RESULT_COUNT"]<=0)
	$arParams["PAGE_RESULT_COUNT"] = 50;

$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
if(strlen($arParams["PAGER_TITLE"]) <= 0)
	$arParams["PAGER_TITLE"] = GetMessage("SEARCH_RESULTS");
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["USE_TITLE_RANK"] = $arParams["USE_TITLE_RANK"]=="Y";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);

if($arParams["DEFAULT_SORT"] !== "date")
	$arParams["DEFAULT_SORT"] = "rank";

if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
	$arFILTERCustom = array();
else
{
	$arFILTERCustom = $GLOBALS[$arParams["FILTER_NAME"]];
	if(!is_array($arFILTERCustom))
		$arFILTERCustom = array();
}

$exFILTER = CSearchParameters::ConvertParamsToFilter($arParams, "arrFILTER");

$arParams["CHECK_DATES"]=$arParams["CHECK_DATES"]=="Y";

//options
if(isset($_REQUEST["tags"]))
	$tags = trim($_REQUEST["tags"]);
else
	$tags = false;
if(isset($_REQUEST["q"]))
	$q = trim($_REQUEST["q"]);
else
	$q = false;

if(
	$arParams["SHOW_WHEN"]
	&& isset($_REQUEST["from"])
	&& is_string($_REQUEST["from"])
	&& strlen($_REQUEST["from"])
	&& CheckDateTime($_REQUEST["from"])
)
	$from = $_REQUEST["from"];
else
	$from = "";

if(
	$arParams["SHOW_WHEN"]
	&& isset($_REQUEST["to"])
	&& is_string($_REQUEST["to"])
	&& strlen($_REQUEST["to"])
	&& CheckDateTime($_REQUEST["to"])
)
	$to = $_REQUEST["to"];
else
	$to = "";

$where = $arParams["SHOW_WHERE"]? trim($_REQUEST["where"]): "";

$how = trim($_REQUEST["how"]);
if($how == "d")
	$how = "d";
elseif($how == "r")
	$how = "";
elseif($arParams["DEFAULT_SORT"] == "date")
	$how = "d";
else
	$how = "";

if($arParams["USE_TITLE_RANK"])
{
	if($how=="d")
		$aSort=array("DATE_CHANGE"=>"DESC", "CUSTOM_RANK"=>"DESC", "TITLE_RANK"=>"DESC", "RANK"=>"DESC");
	else
		$aSort=array("CUSTOM_RANK"=>"DESC", "TITLE_RANK"=>"DESC", "RANK"=>"DESC", "DATE_CHANGE"=>"DESC");
}
else
{
	if($how=="d")
		$aSort=array("DATE_CHANGE"=>"DESC", "CUSTOM_RANK"=>"DESC", "RANK"=>"DESC");
	else
		$aSort=array("CUSTOM_RANK"=>"DESC", "RANK"=>"DESC", "DATE_CHANGE"=>"DESC");
}
/*************************************************************************
			Operations with cache
*************************************************************************/
$arrDropdown = array();

$obCache = new CPHPCache;

if(
	$arParams["CACHE_TYPE"] == "N"
	|| (
		$arParams["CACHE_TYPE"] == "A"
		&& COption::GetOptionString("main", "component_cache_on", "Y") == "N"
	)
)
	$arParams["CACHE_TIME"] = 0;

if($obCache->StartDataCache($arParams["CACHE_TIME"], $this->GetCacheID(), "/".SITE_ID.$this->GetRelativePath()))
{
	// Getting of the Information block types
	$arIBlockTypes = array();
	if(CModule::IncludeModule("iblock"))
	{
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while($arIBlockType = $rsIBlockType->Fetch())
		{
			if($ar = CIBlockType::GetByIDLang($arIBlockType["ID"], LANGUAGE_ID))
				$arIBlockTypes[$arIBlockType["ID"]] = $ar["~NAME"];
		}
	}

	// Creating of an array for drop-down list
	foreach($arParams["arrWHERE"] as $code)
	{
		list($module_id, $part_id) = explode("_", $code, 2);
		if(strlen($module_id)>0)
		{
			if(strlen($part_id)<=0)
			{
				switch($module_id)
				{
					case "forum":
						$arrDropdown[$code] = GetMessage("SEARCH_FORUM");
						break;
					case "blog":
						$arrDropdown[$code] = GetMessage("SEARCH_BLOG");
						break;
					case "socialnetwork":
						$arrDropdown[$code] = GetMessage("SEARCH_SOCIALNETWORK");
						break;
					case "intranet":
						$arrDropdown[$code] = GetMessage("SEARCH_INTRANET");
						break;
					case "crm":
						$arrDropdown[$code] = GetMessage("SEARCH_CRM");
						break;
					case "disk":
						$arrDropdown[$code] = GetMessage("SEARCH_DISK");
						break;
				}
			}
			else
			{
				// if there is additional information specified besides ID then
				switch($module_id)
				{
					case "iblock":
						if(isset($arIBlockTypes[$part_id]))
							$arrDropdown[$code] = $arIBlockTypes[$part_id];
						break;
				}
			}
		}
	}
	$obCache->EndDataCache($arrDropdown);
}
else
{
	$arrDropdown = $obCache->GetVars();
}

$arResult["DROPDOWN"] = htmlspecialcharsex($arrDropdown);
$arResult["REQUEST"]["HOW"] = htmlspecialcharsbx($how);
$arResult["REQUEST"]["~FROM"] = $from;
$arResult["REQUEST"]["FROM"] = htmlspecialcharsbx($from);
$arResult["REQUEST"]["~TO"] = $to;
$arResult["REQUEST"]["TO"] = htmlspecialcharsbx($to);

if($q!==false)
{
	if($arParams["USE_LANGUAGE_GUESS"] == "N" || isset($_REQUEST["spell"]))
	{
		$arResult["REQUEST"]["~QUERY"] = $q;
		$arResult["REQUEST"]["QUERY"] = htmlspecialcharsex($q);
	}
	else
	{
		$arLang = CSearchLanguage::GuessLanguage($q);
		if(is_array($arLang) && $arLang["from"] != $arLang["to"])
		{
			$arResult["REQUEST"]["~ORIGINAL_QUERY"] = $q;
			$arResult["REQUEST"]["ORIGINAL_QUERY"] = htmlspecialcharsex($q);

			$arResult["REQUEST"]["~QUERY"] = CSearchLanguage::ConvertKeyboardLayout($arResult["REQUEST"]["~ORIGINAL_QUERY"], $arLang["from"], $arLang["to"]);
			$arResult["REQUEST"]["QUERY"] = htmlspecialcharsex($arResult["REQUEST"]["~QUERY"]);
		}
		else
		{
			$arResult["REQUEST"]["~QUERY"] = $q;
			$arResult["REQUEST"]["QUERY"] = htmlspecialcharsex($q);
		}
	}

}
else
{
	$arResult["REQUEST"]["~QUERY"] = false;
	$arResult["REQUEST"]["QUERY"] = false;
}

if($tags!==false)
{
	$arResult["REQUEST"]["~TAGS_ARRAY"] = array();
	$arTags = explode(",", $tags);
	foreach($arTags as $tag)
	{
		$tag = trim($tag);
		if(strlen($tag) > 0)
			$arResult["REQUEST"]["~TAGS_ARRAY"][$tag] = $tag;
	}
	$arResult["REQUEST"]["TAGS_ARRAY"] = htmlspecialcharsex($arResult["REQUEST"]["~TAGS_ARRAY"]);
	$arResult["REQUEST"]["~TAGS"] = implode(",", $arResult["REQUEST"]["~TAGS_ARRAY"]);
	$arResult["REQUEST"]["TAGS"] = htmlspecialcharsex($arResult["REQUEST"]["~TAGS"]);
}
else
{
	$arResult["REQUEST"]["~TAGS_ARRAY"] = array();
	$arResult["REQUEST"]["TAGS_ARRAY"] = array();
	$arResult["REQUEST"]["~TAGS"] = false;
	$arResult["REQUEST"]["TAGS"] = false;
}
$arResult["REQUEST"]["WHERE"] = htmlspecialcharsbx($where);

$arResult["URL"] = $APPLICATION->GetCurPage()
	."?q=".urlencode($q)
	.(isset($_REQUEST["spell"])? "&amp;spell=1": "")
	."&amp;where=".urlencode($where)
	.($tags!==false? "&amp;tags=".urlencode($tags): "")
;

if(isset($arResult["REQUEST"]["~ORIGINAL_QUERY"]))
{
	$arResult["ORIGINAL_QUERY_URL"] = $APPLICATION->GetCurPage()
		."?q=".urlencode($arResult["REQUEST"]["~ORIGINAL_QUERY"])
		."&amp;spell=1"
		."&amp;where=".urlencode($arResult["REQUEST"]["WHERE"])
		.($arResult["REQUEST"]["HOW"]=="d"? "&amp;how=d": "")
		.($arResult["REQUEST"]["FROM"]? '&amp;from='.urlencode($arResult["REQUEST"]["~FROM"]): "")
		.($arResult["REQUEST"]["TO"]? '&amp;to='.urlencode($arResult["REQUEST"]["~TO"]): "")
		.($tags!==false? "&amp;tags=".urlencode($tags): "")
	;
}

$templatePage = "";
$arReturn = false;
if($this->InitComponentTemplate($templatePage))
{
	$template = &$this->GetTemplate();
	$arResult["FOLDER_PATH"] = $folderPath = $template->GetFolder();

	if(strlen($folderPath) > 0)
	{
		$arFilter = array(
			"SITE_ID" => SITE_ID,
			"QUERY" => $arResult["REQUEST"]["~QUERY"],
			"TAGS" => $arResult["REQUEST"]["~TAGS"],
		);
		$arFilter = array_merge($arFILTERCustom, $arFilter);
		if(strlen($where)>0)
		{
			list($module_id, $part_id) = explode("_",$where,2);
			$arFilter["MODULE_ID"] = $module_id;
			if(strlen($part_id)>0) $arFilter["PARAM1"] = $part_id;
		}
		if($arParams["CHECK_DATES"])
			$arFilter["CHECK_DATES"]="Y";
		if($from)
			$arFilter[">=DATE_CHANGE"] = $from;
		if($to)
			$arFilter["<=DATE_CHANGE"] = $to;

		$obSearch = new CSearch();

		//When restart option is set we will ignore error on query with only stop words
		$obSearch->SetOptions(array(
			"ERROR_ON_EMPTY_STEM" => $arParams["RESTART"] != "Y",
			"NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"] == "Y",
		));

		$obSearch->Search($arFilter, $aSort, $exFILTER);

		$arResult["ERROR_CODE"] = $obSearch->errorno;
		$arResult["ERROR_TEXT"] = $obSearch->error;

		$arResult["SEARCH"] = array();
		if($obSearch->errorno==0)
		{
			$obSearch->NavStart($arParams["PAGE_RESULT_COUNT"], false);
			$ar = $obSearch->GetNext();
			//Search restart
			if(!$ar && ($arParams["RESTART"] == "Y") && $obSearch->Query->bStemming)
			{
				$exFILTER["STEMMING"] = false;
				$obSearch = new CSearch();
				$obSearch->Search($arFilter, $aSort, $exFILTER);

				$arResult["ERROR_CODE"] = $obSearch->errorno;
				$arResult["ERROR_TEXT"] = $obSearch->error;

				if($obSearch->errorno == 0)
				{
					$obSearch->NavStart($arParams["PAGE_RESULT_COUNT"], false);
					$ar = $obSearch->GetNext();
				}
			}

			$arReturn = array();
			while($ar)
			{
				$arReturn[$ar["ID"]] = $ar["ITEM_ID"];
				$ar["CHAIN_PATH"] = $APPLICATION->GetNavChain($ar["URL"], 0, $folderPath."/chain_template.php", true, false);
				$ar["URL"] = htmlspecialcharsbx($ar["URL"]);
				$ar["TAGS"] = array();
				if (!empty($ar["~TAGS_FORMATED"]))
				{
					foreach ($ar["~TAGS_FORMATED"] as $name => $tag)
					{
						if($arParams["TAGS_INHERIT"] == "Y")
						{
							$arTags = $arResult["REQUEST"]["~TAGS_ARRAY"];
							$arTags[$tag] = $tag;
							$tags = implode("," , $arTags);
						}
						else
						{
							$tags = $tag;
						}
						$ar["TAGS"][] = array(
							"URL" => $APPLICATION->GetCurPageParam("tags=".urlencode($tags), array("tags")),
							"TAG_NAME" => htmlspecialcharsex($name),
						);
					}
				}
				$arResult["SEARCH"][]=$ar;
				$ar = $obSearch->GetNext();
			}

			$navComponentObject = null;
			$arResult["NAV_STRING"] = $obSearch->GetPageNavStringEx($navComponentObject,  $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
			$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
			$arResult["NAV_RESULT"] = $obSearch;
		}

		$arResult["TAGS_CHAIN"] = array();
		$url = array();
		foreach ($arResult["REQUEST"]["~TAGS_ARRAY"] as $key => $tag)
		{
			$url_without = $arResult["REQUEST"]["~TAGS_ARRAY"];
			unset($url_without[$key]);
			$url[$tag] = $tag;
			$result = array(
				"TAG_NAME" => $tag,
				"TAG_PATH" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url)), array("tags")),
				"TAG_WITHOUT" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url_without)), array("tags")),
			);
			$arResult["TAGS_CHAIN"][] = $result;
		}

		$this->ShowComponentTemplate();
	}
}
else
{
	$this->__ShowError(str_replace("#PAGE#", $templatePage, str_replace("#NAME#", $this->__templateName, "Can not find '#NAME#' template with page '#PAGE#'")));
}
return $arReturn;
?>
