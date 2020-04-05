<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$res = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($res["WIDTH"], $res["HEIGHT"]) = explode("/", $res["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($res["WIDTH"]) > 0 ? intVal($res["WIDTH"]) : 120);


$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"]=="Y");
$arParams["GROUP_PERMISSIONS"] = (!is_array($arParams["GROUP_PERMISSIONS"]) ? array(1) : $arParams["GROUP_PERMISSIONS"]);

$URL_NAME_DEFAULT = array(
		"sections_top" => "",
		"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	);

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "tags"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
}
$arParams["ITEM_URL"] = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_URL"], array("SECTION_ID" => 0, "ELEMENT_ID" => "#ITEM_ID#"));
$arResult["SECTION_TOP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["SECTIONS_TOP_URL"], array());
$arResult["SEARCH_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam("", array("SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "how"), false);
if (empty($_REQUEST["tags"]))
	LocalRedirect($arResult["SECTION_TOP_LINK"]);

if (is_array($arResult["SEARCH"]))
{
	$arCacheParams = array(
		"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
		"IBLOCK_ID" => $arParams["IBLOCK_ID"]);
	$arParams["PERMISSION"] = "";
	$cache = new CPHPCache;
	$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]."/");
	$cache_id = "section_".serialize($arCacheParams);
	$cache_path = $cache_path_main."search";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (!empty($res["PERMISSION"]))
			$arParams["PERMISSION"] = $res["PERMISSION"];
	}
	if (empty($arParams["PERMISSION"]))
		$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(
			array(
				"PERMISSION" => $arParams["PERMISSION"]));
	}
	$bUSER_HAVE_ACCESS = (!$arParams["USE_PERMISSIONS"]);
	if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
	{
		$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
		foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
		{
			if(in_array($PERM, $arUserGroupArray))
			{
				$bUSER_HAVE_ACCESS = true;
				break;
			}
		}
	}
	if ($arParams["PERMISSION"] >= "W")
		$arResult["USER_HAVE_ACCESS"] = true;


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
			}while ($res = $db_res->Fetch());
		}
	}

	foreach($arResult["SEARCH"] as $key => $arItem):
		// WHAT
		$arSelect = array(	"ID",			"CODE",
							"IBLOCK_ID",	"IBLOCK_SECTION_ID",
							"NAME",			"PREVIEW_PICTURE",
							"DETAIL_TEXT");
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
		if($obElement = $rsElement->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			if(intVal($arElement["PREVIEW_PICTURE"]) > 0)
				$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
			$arElement["URL"] = str_replace("#ITEM_ID#", $arItem["ITEM_ID"], $arParams["ITEM_URL"]);
			$arResult["SEARCH"][$key]["ELEMENT"] = $arElement;
		}
		else
		{
			$arResult["SEARCH"][$key] = array();
		}
	endforeach;
}
?>