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
$this->setFrameMode(false);

if(!CModule::IncludeModule("iblock"))
{
	return;
}

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

unset($arParams["IBLOCK_TYPE"]); //was used only for IBLOCK_ID setup with Editor
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
$arParams["NUM_DAYS"] = intval($arParams["NUM_DAYS"]);
$arParams["NUM_NEWS"] = intval($arParams["NUM_NEWS"]);

if(!array_key_exists("RSS_TTL", $arParams))
	$arParams["RSS_TTL"] = 60;
$arParams["RSS_TTL"] = intval($arParams["RSS_TTL"]);

$arParams["YANDEX"] = $arParams["YANDEX"]=="Y";

$arParams["CHECK_DATES"] = $arParams["CHECK_DATES"]!="N";

$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if(strlen($arParams["SORT_BY1"])<=0)
	$arParams["SORT_BY1"] = "ACTIVE_FROM";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
	$arParams["SORT_ORDER1"]="DESC";

if(strlen($arParams["SORT_BY2"])<=0)
	$arParams["SORT_BY2"] = "SORT";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"]))
	$arParams["SORT_ORDER2"]="ASC";

if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	$arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
	if(!is_array($arrFilter))
		$arrFilter = array();
}

$arParams["CACHE_FILTER"] = $arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

$bDesignMode = $APPLICATION->GetShowIncludeAreas() && is_object($USER) && $USER->IsAdmin();

if(!$bDesignMode)
{
	$APPLICATION->RestartBuffer();
	header("Content-Type: application/rss+xml; charset=".LANG_CHARSET);
	header("Pragma: no-cache");
}
else
{
	ob_start();
}
/*************************************************************************
	Start caching
*************************************************************************/

if($this->StartResultCache(false, array($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups(), $arrFilter)))
{
	$rsResult = CIBlock::GetList(array(), array(
		"ACTIVE" => "Y",
		"SITE_ID" => SITE_ID,
		"ID" => $arParams["IBLOCK_ID"],
	));
	$arResult = $rsResult->Fetch();
	if(!$arResult)
	{
		$this->AbortResultCache();
		if($bDesignMode)
		{
			ob_end_flush();
			ShowError(GetMessage("CT_RO_IBLOCK_NOT_FOUND"));
			return;
		}
		else
			die();
	}
	else
	{
		foreach($arResult as $k => $v)
		{
			if(substr($k, 0, 1)!=="~")
			{
				$arResult["~".$k] = $v;
				$arResult[$k] = htmlspecialcharsbx($v);
			}
		}
	}

	$arResult["RSS_TTL"] = $arParams["RSS_TTL"];

	if($arParams["SECTION_ID"] > 0 || strlen($arParams["SECTION_CODE"]) > 0)
	{
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
		);
		if($arParams["SECTION_ID"] > 0)
			$arFilter["ID"] = $arParams["SECTION_ID"];
		elseif(strlen($arParams["SECTION_CODE"]) > 0)
			$arFilter["=CODE"] = $arParams["SECTION_CODE"];

		$rsResult = CIBlockSection::GetList(array(), $arFilter);
		$arResult["SECTION"] = $rsResult->Fetch();
		if(!$arResult["SECTION"])
		{
			$this->AbortResultCache();
			if($bDesignMode)
			{
				ob_end_flush();
				ShowError(GetMessage("CT_RO_SECTION_NOT_FOUND"));
				return;
			}
			else
				die();
		}
		else
		{
			foreach($arResult["SECTION"] as $k => $v)
			{
				if(substr($k, 0, 1)!=="~")
				{
					$arResult["SECTION"]["~".$k] = $v;
					$arResult["SECTION"][$k] = htmlspecialcharsbx($v);
				}
			}
		}
	}

	if(strlen($arResult["SERVER_NAME"])<=0 && defined("SITE_SERVER_NAME"))
	{
		$arResult["SERVER_NAME"] = SITE_SERVER_NAME;
	}
	if(strlen($arResult["SERVER_NAME"])<=0 && defined("SITE_SERVER_NAME"))
	{
		$b = "sort";
		$o = "asc";
		$rsSite = CSite::GetList($b, $o, array("LID" => $arResult["LID"]));
		if($arSite = $rsSite->Fetch())
			$arResult["SERVER_NAME"] = $arSite["SERVER_NAME"];
	}
	if(strlen($arResult["SERVER_NAME"])<=0)
	{
		$arResult["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
	}

	$arResult["PICTURE"] = CFile::GetFileArray($arResult["PICTURE"]);

	$arResult["NODES"] = CIBlockRSS::GetNodeList($arResult["ID"]);

	$arSelect = array(
		"ID",
		"CODE",
		"XML_ID",
		"IBLOCK_ID",
		"NAME",
		"SORT",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT",
		"DETAIL_TEXT_TYPE",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"IBLOCK_SECTION_ID",
		"DATE_ACTIVE_FROM",
		"ACTIVE_FROM",
		"DATE_ACTIVE_TO",
		"ACTIVE_TO",
		"SHOW_COUNTER",
		"SHOW_COUNTER_START",
		"IBLOCK_TYPE_ID",
		"IBLOCK_CODE",
		"IBLOCK_EXTERNAL_ID",
		"DATE_CREATE",
		"CREATED_BY",
		"TIMESTAMP_X",
		"MODIFIED_BY",
		"PROPERTY_*",
	);
	$arFilter = array (
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);

	if($arParams["CHECK_DATES"])
		$arFilter["ACTIVE_DATE"] = "Y";

	if(array_key_exists("SECTION", $arResult))
	{
		$arFilter["SECTION_ID"] = $arResult["SECTION"]["ID"];
		if($arParams["INCLUDE_SUBSECTIONS"])
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}
	else
	{
		$arFilter["IBLOCK_ID"] = $arResult["ID"];
	}

	if($arParams["NUM_DAYS"] > 0)
		$arFilter["ACTIVE_FROM"] = date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL")), mktime(date("H"), date("i"), date("s"), date("m"), date("d")-IntVal($arParams["NUM_DAYS"]), date("Y")));

	$arSort = array(
		$arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
		$arParams["SORT_BY2"] => $arParams["SORT_ORDER2"],
	);
	if(!array_key_exists("ID", $arSort))
		$arSort["ID"] = "DESC";

	if($arParams["NUM_NEWS"]>0)
		$limit = array("nTopCount"=>$arParams["NUM_NEWS"]);
	else
		$limit = false;

	$arResult["ITEMS"]=array();

	CTimeZone::Disable();
	$rsElements = CIBlockElement::GetList($arSort, array_merge($arFilter, $arrFilter), false, $limit, $arSelect);
	CTimeZone::Enable();

	$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
	while($obElement = $rsElements->GetNextElement())
	{
		$arElement = $obElement->GetFields();
		$arProperties = $obElement->GetProperties();

		$arNodesElement = array();
		foreach($arElement as $code => $value)
			$arNodesElement["#".$code."#"] = $value;
		$arNodesElement["#PREVIEW_TEXT#"] = htmlspecialcharsbx($arNodesElement["#PREVIEW_TEXT#"]);
		$arNodesElement["#DETAIL_TEXT#"] = htmlspecialcharsbx($arNodesElement["#DETAIL_TEXT#"]);
		foreach($arProperties as $code=>$arProperty)
			$arNodesElement["#".$code."#"] = $arProperty["VALUE"];
		$arNodesSearch = array_keys($arNodesElement);
		$arNodesReplace = array_values($arNodesElement);

		$arElement["arr_PREVIEW_PICTURE"] = $arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
		if(is_array($arElement["arr_PREVIEW_PICTURE"]))
			$arElement["PREVIEW_PICTURE"] = CHTTP::URN2URI($arElement["arr_PREVIEW_PICTURE"]["SRC"], $arResult["SERVER_NAME"]);
		$arElement["arr_DETAIL_PICTURE"] = $arElement["DETAIL_PICTURE"] = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
		if(is_array($arElement["arr_DETAIL_PICTURE"]))
			$arElement["DETAIL_PICTURE"] = CHTTP::URN2URI($arElement["arr_DETAIL_PICTURE"]["SRC"], $arResult["SERVER_NAME"]);

		if(strlen($arResult["NODES"]["title"])>0)
			$arItem["title"] = str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["title"]);
		else
			$arItem["title"] = $arElement["NAME"];
		$arItem["title"] = htmlspecialcharsbx(htmlspecialcharsback($arItem["title"]));

		if(strlen($arResult["NODES"]["link"])>0)
			$arItem["link"] = str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["link"]);
		elseif($arProperties["DOC_LINK"]["VALUE"])
			$arItem["link"] = CHTTP::URN2URI($arProperties["DOC_LINK"]["VALUE"], $arResult["SERVER_NAME"]);
		else
			$arItem["link"] = CHTTP::URN2URI($arElement["DETAIL_PAGE_URL"], $arResult["SERVER_NAME"]);

		if(strlen($arResult["NODES"]["description"])>0)
			$arItem["description"] = str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["description"]);
		else
			$arItem["description"]=htmlspecialcharsbx(($arElement["PREVIEW_TEXT"] || $arParams["YANDEX"]) ? $arElement["PREVIEW_TEXT"] : $arElement["DETAIL_TEXT"]);

		if(strlen($arResult["NODES"]["enclosure"])>0)
		{
			$arItem["enclosure"] = array(
				"url" => str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["enclosure"]),
				"length" => str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["enclosure_length"]),
				"type" => str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["enclosure_type"]),
			);
		}
		elseif(is_array($arElement["arr_PREVIEW_PICTURE"]))
		{
			$arItem["enclosure"] = array(
				"url" => CHTTP::URN2URI($arElement["arr_PREVIEW_PICTURE"]["SRC"], $arResult["SERVER_NAME"]),
				"length" => $arElement["arr_PREVIEW_PICTURE"]["FILE_SIZE"],
				"type" => $arElement["arr_PREVIEW_PICTURE"]["CONTENT_TYPE"],
			);
		}
		else
		{
			$arItem["enclosure"]=false;
		}

		if(strlen($arResult["NODES"]["category"])>0)
		{
			$arItem["category"] = str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["category"]);
		}
		else
		{
			$arItem["category"] = "";
			$rsNavChain = CIBlockSection::GetNavChain($arResult["ID"], $arElement["IBLOCK_SECTION_ID"]);
			while($arNavChain = $rsNavChain->Fetch())
			{
				if ($arItem["category"])
					$arItem["category"] .= "/";
				$arItem["category"] .= htmlspecialcharsbx($arNavChain["NAME"]);
			}
		}

		if($arParams["YANDEX"])
		{
			$arItem["full-text"] = htmlspecialcharsbx(htmlspecialcharsback($arElement["DETAIL_TEXT"]));
		}

		if(strlen($arResult["NODES"]["pubDate"])>0)
		{
			$arItem["pubDate"] = str_replace($arNodesSearch, $arNodesReplace, $arResult["NODES"]["pubDate"]);
		}
		elseif(strlen($arElement["ACTIVE_FROM"])>0)
		{
			$arItem["pubDate"] = date("r", MkDateTime($DB->FormatDate($arElement["ACTIVE_FROM"], Clang::GetDateFormat("FULL"), "DD.MM.YYYY H:I:S"), "d.m.Y H:i:s"));
		}
		elseif(strlen($arElement["DATE_CREATE"])>0)
		{
			$arItem["pubDate"] = date("r", MkDateTime($DB->FormatDate($arElement["DATE_CREATE"], Clang::GetDateFormat("FULL"), "DD.MM.YYYY H:I:S"), "d.m.Y H:i:s"));
		}
		else
		{
			$arItem["pubDate"] = date("r");
		}

		$arItem["ELEMENT"] = $arElement;
		$arItem["PROPERTIES"] = $arProperties;
		$arResult["ITEMS"][]=$arItem;
	}

	$this->IncludeComponentTemplate();
}

if(!$bDesignMode)
{
	$r = $APPLICATION->EndBufferContentMan();
	echo $r;
	die();
}
else
{
	$contents = ob_get_contents();
	ob_end_clean();
	echo "<pre>",htmlspecialcharsbx($contents),"</pre>";
}
?>
