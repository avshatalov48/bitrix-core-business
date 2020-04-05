<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
$res = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($res["WIDTH"], $res["HEIGHT"]) = explode("/", $res["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($res["WIDTH"]) > 0 ? intVal($res["WIDTH"]) : 120);

$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"]=="Y");
$arParams["GROUP_PERMISSIONS"] = (!is_array($arParams["GROUP_PERMISSIONS"]) ? array(1) : $arParams["GROUP_PERMISSIONS"]);

$URL_NAME_DEFAULT = array(
		"sections_top" => "",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "tags"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}
$arResult["SECTION_TOP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["SECTIONS_TOP_URL"], array());
$arResult["SEARCH_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam("", array("SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "how"), false);

if (empty($_REQUEST["tags"]))
	LocalRedirect($arResult["SECTION_TOP_LINK"]);
/********************************************************************
				/Input params
********************************************************************/
$arParams["ITEM_URL"] = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_URL"], array("SECTION_ID" => 0, "ELEMENT_ID" => "#ITEM_ID#"));
$arResult["SEARCH_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam("", array("SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "how"), false);

if (empty($arResult["SEARCH"]))
{
	return true;
}

$cache = new CPHPCache;
$arResult["ELEMENTS_LIST"] = array();
$arResult["ELEMENTS"] = array("MAX_WIDTH" => 0, "MAX_HEIGHT" => $maxHeight);
$arParams["PERMISSION"] = "D";

$maxWidth = 0; $maxHeight = 0;

// Permission
$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]."/search");
$cache_id = "permission_".serialize(array("USER_GROUP" => $GLOBALS["USER"]->GetGroups(), "IBLOCK_ID" => $arParams["IBLOCK_ID"]));
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path)):
	$arParams["PERMISSION"] = $cache->GetVars();
else:
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache($arParams["PERMISSION"]);
	endif;
endif;
if ($arParams["PERMISSION"] <= "D"):
	return false;
endif;
// Additional Permission
$bUSER_HAVE_ACCESS = (!$arParams["USE_PERMISSIONS"] || $arParams["PERMISSION"] >= "U");
if (!$bUSER_HAVE_ACCESS)
{
	$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GROUP_PERMISSIONS"]);
	$bUSER_HAVE_ACCESS = (empty($res) ? false : true);
}
$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;
// Select Sections With Parols
$arMargin = array();
if ($arParams["PERMISSION"] < "W")
{
	$db_res = CIBlockSection::GetList(Array(), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y"), false, array("UF_PASSWORD"));
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			if (!empty($res["UF_PASSWORD"]) && ($res["UF_PASSWORD"] != $_SESSION['PHOTOGALLERY']['SECTION'][$res["ID"]]))
			{
				$arMargin[] = array($res["LEFT_MARGIN"], $res["RIGHT_MARGIN"]);
			}
		} while ($res = $db_res->Fetch());
	}
}

foreach($arResult["SEARCH"] as $key => $arItem)
{
	// WHAT
	$arSelect = array(
		"ID",
		"CODE",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"SECTION_PAGE_URL",
		"NAME",
		"ACTIVE",
		"DETAIL_PICTURE",
		"PREVIEW_PICTURE",
		"PREVIEW_TEXT",
		"DETAIL_TEXT",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT_TYPE",
		"TAGS",
		"DATE_CREATE",
		"CREATED_BY",
		"SHOW_COUNTER");
	//WHERE
	$arFilter = array(
		"ID" => $arItem["ITEM_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"IBLOCK_ID" => $arItem["PARAM2"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y");

	if (!empty($arMargin))
		$arFilter["!SUBSECTION"] = $arMargin;

	//EXECUTE
	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
	$arElement = array();
	$arSections = array();
	$arGalleries = array();
	if($obElement = $rsElement->GetNextElement())
	{
		$arElement = $obElement->GetFields();

		$arElement["~PREVIEW_PICTURE"] = $arElement["PREVIEW_PICTURE"];
		$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["~PREVIEW_PICTURE"]);
		if (!empty($arParams["PICTURES_SIGHT"]) && $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]])
		{
			$size = intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]);
			if ($size > 0 && ($arElement["PICTURE"]["WIDTH"] > $size || $arElement["PICTURE"]["HEIGHT"] > $size))
			{
				$koeff = min($size / $arElement["PICTURE"]["WIDTH"], $size / $arElement["PICTURE"]["HEIGHT"]);
				$arElement["PICTURE"]["WIDTH"] = intVal($arElement["PICTURE"]["WIDTH"] * $koeff);
				$arElement["PICTURE"]["HEIGHT"] = intVal($arElement["PICTURE"]["HEIGHT"] * $koeff);
			}
		}

		$maxWidth = max($maxWidth, $arElement["PICTURE"]["WIDTH"]);
		$maxHeight = max($maxHeight, $arElement["PICTURE"]["HEIGHT"]);

		if (empty($arGalleries[$arElement["IBLOCK_SECTION_ID"]])) // Get Gallery Info
		{
			if (empty($arSections[$arElement["IBLOCK_SECTION_ID"]])) // Get Section Info
			{
				$db_res = CIBlockSection::GetList(array(), array("ID" => $arElement["IBLOCK_SECTION_ID"]), false,
					array("ID", "LEFT_MARGIN", "RIGHT_MARGIN"));
				$arSections[$arElement["IBLOCK_SECTION_ID"]] = $db_res->Fetch();
			}
			$arSection = $arSections[$arElement["IBLOCK_SECTION_ID"]];

			$db_res = CIBlockSection::GetList(
				array(),
				array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"SECTION_ID" => 0,
					"!LEFT_MARGIN" => $arSection["LEFT_MARGIN"],
					"!RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"],
					"!ID" => $arElement["IBLOCK_SECTION_ID"]),
				false,
				array("ID", "CODE"));
			$arGalleries[$arElement["IBLOCK_SECTION_ID"]] = $db_res->Fetch();
		}
		$arGallery = $arGalleries[$arElement["IBLOCK_SECTION_ID"]];
		$arElement["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
			array("USER_ALIAS" => $arGallery["CODE"], "SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $arElement["ID"]));
		$arElement["URL"] = htmlspecialcharsbx($arElement["~URL"]);
	}
	$arResult["SEARCH"][$key]["ELEMENT"] = $arElement;
}
$arResult["ELEMENTS"] = array("MAX_WIDTH" => $maxWidth, "MAX_HEIGHT" => $maxHeight);
?>