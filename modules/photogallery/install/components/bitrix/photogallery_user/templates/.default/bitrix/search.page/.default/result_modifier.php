<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
$res = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBNAIL_SIZE"]));
list($res["WIDTH"], $res["HEIGHT"]) = explode("/", $res["STRING"]);
$arParams["THUMBNAIL_SIZE"] = (intVal($res["WIDTH"]) > 0 ? intVal($res["WIDTH"]) : 120);


$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] == "Y");
$arParams["GROUP_PERMISSIONS"] = (!is_array($arParams["GROUP_PERMISSIONS"]) ? array(1) : $arParams["GROUP_PERMISSIONS"]);

//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"detail" => "PAGE_NAME=detail&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
//***************** STANDART ***************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
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

$arParams["ADDITIONAL_SIGHTS"] = $arParams["~ADDITIONAL_SIGHTS"];
$arParams["ADDITIONAL_SIGHTS"] = (is_array($arParams["~ADDITIONAL_SIGHTS"]) ? $arParams["~ADDITIONAL_SIGHTS"] : array()); // sights list from component params
$arParams["PICTURES"] = array();
if (!empty($arParams["ADDITIONAL_SIGHTS"]))
{
	$arParams["PICTURES_INFO"] = @unserialize(COption::GetOptionString("photogallery", "pictures"));
	$arParams["PICTURES_INFO"] = (is_array($arParams["PICTURES_INFO"]) ? $arParams["PICTURES_INFO"] : array());
	foreach ($arParams["PICTURES_INFO"] as $key => $val):
		if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
			$arParams["PICTURES"][$val["code"]] = array(
				"size" => $arParams["PICTURES_INFO"][$key]["size"],
				"quality" => $arParams["PICTURES_INFO"][$key]["quality"],
				"title" => $arParams["PICTURES_INFO"][$key]["title"]);
	endforeach;

	if (empty($arParams["PICTURES_SIGHT"]) && !empty($arParams["PICTURES"])):
		if ($GLOBALS["USER"]->IsAuthorized())
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
			$arTemplateParams = CUserOptions::GetOption('photogallery', 'template');
			$arTemplateParams = (!is_array($arTemplateParams) ? array() : $arTemplateParams);
			$arParams["PICTURES_SIGHT"] = $arTemplateParams['sight'];
			if ($_REQUEST["PICTURES_SIGHT"] && check_bitrix_sessid() && $arTemplateParams["sight"] != $_REQUEST["PICTURES_SIGHT"]):
				$arTemplateParams['sight'] = $arParams["PICTURES_SIGHT"] = $_REQUEST["PICTURES_SIGHT"];
				CUserOptions::SetOption('photogallery', 'template', $arTemplateParams);
			endif;
		}
		else
		{
			if (!empty($_SESSION['photogallery']['sight']))
				$arParams["PICTURES_SIGHT"] = $_SESSION['photogallery']['sight'];
			if (!empty($_REQUEST["PICTURES_SIGHT"]))
				$_SESSION['photogallery']['sight'] = $arParams["PICTURES_SIGHT"] = $_REQUEST["PICTURES_SIGHT"];
		}
	elseif ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail"):
		$arParams["PICTURES_SIGHT"]	= substr($arParams["PICTURES_SIGHT"], 5);
	endif;
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
		"SHOW_COUNTER",
		"PROPERTY_*");
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

		if (strToUpper($arParams["PICTURES_SIGHT"]) == "DETAIL" && !empty($arElement["DETAIL_PICTURE"]))
			$arElement["~PICTURE"] = $arElement["DETAIL_PICTURE"];
		elseif (!empty($arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"]))
			$arElement["~PICTURE"] = $arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"];
		else
			$arElement["~PICTURE"] = $arElement["PREVIEW_PICTURE"];

		$arElement["PICTURE"] = CFile::GetFileArray($arElement["~PICTURE"]);
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
	$arResult["ELEMENTS_LIST"][$arItem["ITEM_ID"]] = $arElement;
}
$arResult["ELEMENTS"] = array("MAX_WIDTH" => $maxWidth, "MAX_HEIGHT" => $maxHeight);
?>