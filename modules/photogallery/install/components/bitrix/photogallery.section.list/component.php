<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"]))
	return ShowError(GetMessage("P_GALLERY_EMPTY"));

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
/********************************************************************
				Input params
********************************************************************/

//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["SORT_BY"] = trim($arParams["SORT_BY"]);
	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");

	$arParams["PHOTO_LIST_MODE"] = $arParams["PHOTO_LIST_MODE"] == "N" ? "N" : "Y";
	if ($arParams["PHOTO_LIST_MODE"] == "Y")
	{
		$arParams["SHOWN_ITEMS_COUNT"] = intVal($arParams["SHOWN_ITEMS_COUNT"]) > 0 ? intVal($arParams["SHOWN_ITEMS_COUNT"]) : 6;
		$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? "SORT" : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
		$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
		$arParams["ELEMENT_SORT_FIELD1"] = (empty($arParams["ELEMENT_SORT_FIELD1"]) ? "ID" : strToUpper($arParams["ELEMENT_SORT_FIELD1"]));
		$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER1"]) != "DESC" ? "ASC" : "DESC");
	}

	//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_edit_icon" => "PAGE_NAME=section_edit_icon".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"detail_edit" => "PAGE_NAME=detail_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"upload" => "PAGE_NAME=upload".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ACTION=upload"
	);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITIONAL **************************************/
	$arParams["PASSWORD_CHECKED"] = true;

	$arParams["ALBUM_PHOTO_SIZE"] = (intVal($arParams["ALBUM_PHOTO_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_SIZE"]) : 150);
	$arParams["ALBUM_PHOTO_THUMBS_SIZE"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) : 70);
	$arParams["SECTION_LIST_THUMBNAIL_SIZE"] = (intVal($arParams["SECTION_LIST_THUMBNAIL_SIZE"]) > 0 ? intVal($arParams["SECTION_LIST_THUMBNAIL_SIZE"]) : 70);

	$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 5);

	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] :
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
//***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Main data
********************************************************************/
$arResult["SECTION"] = array();
$arResult["SECTIONS"] = array();
$arResult["SECTIONS_CNT"] = 0;

$cache = new CPHPCache;
$oPhoto = new CPGalleryInterface(
	array(
		"IBlockID" => $arParams["IBLOCK_ID"],
		"GalleryID" => $arParams["USER_ALIAS"],
		"Permission" => $arParams["PERMISSION_EXTERNAL"]),
	array(
		"cache_time" => $arParams["CACHE_TIME"],
		"set_404" => $arParams["SET_STATUS_404"]
		)
	);

if (!$oPhoto)
	return false;

$arResult["GALLERY"] = $oPhoto->Gallery;
$arParams["PERMISSION"] = $oPhoto->User["Permission"];
/************** SECTION *************************************************/
if ($arParams["SECTION_ID"] > 0)
{
	$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);

	if ($res > 400)
	{
		return false;
	}
	elseif ($res == 301)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["~SECTION_URL"],
			array(
				"USER_ALIAS" => $arGallery["CODE"],
				"SECTION_ID" => $arParams["SECTION_ID"])
			);
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}
}
elseif (!empty($arResult["GALLERY"]))
{
	$arParams["SECTION_ID"] = $arResult["GALLERY"]["ID"];
}
/********************************************************************
				/Main data
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** SECTIONS LIST **************************************/
//PAGENAVIGATION
$arNavParams = false; $arNavigation = false;
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	$arNavParams = array("nPageSize" => $arParams["PAGE_ELEMENTS"], "bShowAll" => false);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}

//CACHE
$cache_id = "sections".serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"BEHAVIOUR" => $arParams["BEHAVIOUR"],
	"GALLERY" => $arResult["GALLERY"]["ID"],
	"PERMISSION" => ($arParams["PERMISSION"] >= "U" ? "Y" : "N"),
	"NAV1" => $arNavParams,
	"NAV2" => $arNavigation
));
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;

$cache_path = "/".SITE_ID."/photogallery/".$arParams["IBLOCK_ID"]."/section".$arParams["SECTION_ID"];

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arResult["SECTIONS"] = $res["SECTIONS"];
	$arResult["NAV_STRING"] = $res["NAV_STRING"];
	$arResult["NAV_RESULT"] = $res["NAV_RESULT"];

	$GLOBALS['NavNum'] = intVal($GLOBALS['NavNum']) + 1;
}
else
{
	CModule::IncludeModule("iblock");
	$arFilter = array(
		"ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"SECTION_ID" => intVal($arParams["SECTION_ID"])
	);

	// GALLERY INFO
	if ($arParams["BEHAVIOUR"] == "USER" && ($arFilter["SECTION_ID"] != $arResult["GALLERY"]["ID"]))
	{
		$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
		$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
	}

	if ($arParams["SORT_BY"] == 'ELEMENTS_CNT')
		$arParams["SORT_BY"] = 'ELEMENT_CNT';
	$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), $arFilter, ($arParams["SORT_BY"] === 'ELEMENT_CNT'), array("UF_DATE", "UF_PASSWORD"));

	if ($db_res)
	{
		if ($arParams["PAGE_ELEMENTS"] > 0)
		{
			$db_res->NavStart($arParams["PAGE_ELEMENTS"], false);
			$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
			$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("P_ALBUMS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
			$arResult["NAV_RESULT"] = $db_res;
		}

		while ($res = $db_res->GetNext())
		{
			$res["DATE"] = $res["UF_DATE"];
			$res["~DATE"] = $res["~UF_DATE"];
			if (!empty($res["~DATE"]))
				$res["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~DATE"], CSite::GetDateFormat()));
			$res["PASSWORD"] = $res["UF_PASSWORD"];
			$res["~PASSWORD"] = $res["~UF_PASSWORD"];

			$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
			$res["DETAIL_PICTURE"] = CFile::GetFileArray($res["DETAIL_PICTURE"]);

			$res["SECTIONS_CNT"] = intVal(CIBlockSection::GetCount(array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "SECTION_ID" => $res["ID"])));

			if (isset($res["ELEMENT_CNT"]))
				$res["ELEMENTS_CNT"] = intVal($res["ELEMENT_CNT"]);
			else
				$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ACTIVE" => "Y")));

			if ($arParams["PERMISSION"] >= "U")
				$res["ELEMENTS_CNT_ALL"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ALL"=>"Y")));

			if ($arParams["PERMISSION"] < "U" && $res["ELEMENTS_CNT"] <= 0)
				continue;

			$res["~LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"]));
			$res["LINK"] = htmlspecialcharsbx($res["~LINK"]);
			if ($arParams["PERMISSION"] >= "W")
			{
				$res["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "new"));
				$res["NEW_LINK"] = htmlspecialcharsbx($res["~NEW_LINK"]);
				$res["~EDIT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
				$res["EDIT_LINK"] = htmlspecialcharsbx($res["~EDIT_LINK"]);
				if ($res["ELEMENTS_CNT_ALL"] > 0)
				{
					$res["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_ICON_URL"],
						array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
					$res["EDIT_ICON_LINK"] = htmlspecialcharsbx($res["~EDIT_ICON_LINK"]);
				}
				$res["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "drop")).
					(strpos($arParams["~SECTION_EDIT_URL"], "?") === false ? "?" : "&")."edit=Y";
				$res["DROP_LINK"] = htmlspecialcharsbx($res["~DROP_LINK"]);
			}
			$arResult["SECTIONS"][$res["ID"]] = $res;
		}

		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("SECTIONS" => $arResult["SECTIONS"], "NAV_STRING" => $arResult["NAV_STRING"], "NAV_RESULT" => $arResult["NAV_RESULT"]));
		}
	}
}

/************** URLS SECTION ***************************************/
$arResult["SECTIONS"] = (!is_array($arResult["SECTIONS"]) ? array() : $arResult["SECTIONS"]);
/************** URL ************************************************/
if ($arParams["PERMISSION"] >= "U")
{
	$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"],
			"SECTION_ID" => ($arParams["SECTION_ID"] == $arResult["GALLERY"]["ID"] ? 0 : $arParams["SECTION_ID"])));
	$arResult["SECTION"]["UPLOAD_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~UPLOAD_LINK"]);
	$arResult["SECTION"]["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"],
			"SECTION_ID" => ($arParams["SECTION_ID"] == $arResult["GALLERY"]["ID"] ? 0 : $arParams["SECTION_ID"]), "ACTION" => "new"));
	$arResult["SECTION"]["NEW_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~NEW_LINK"]);

}
if ($arParams["SECTION_ID"] > 0 && $arResult["GALLERY"]["ID"] != $arParams["SECTION_ID"])
{
	$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
	$arResult["SECTION"]["BACK_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~BACK_LINK"]);
}
/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				For custom templates
********************************************************************/
if ($arParams["PERMISSION"] >= "U")
{
	foreach ($arResult["SECTIONS"] as $key => $res)
	{
		$res["ELEMENTS_CNT_APPROVED"] = $res["ELEMENTS_CNT"];
		$res["ELEMENTS_CNT"] = $res["ELEMENTS_CNT_ALL"];
		$arResult["SECTIONS"][$key] = $res;
	}
}

// Compatibility with old and custom templates
if ($this->getTemplateName() !== '.default' && $this->getTemplateName() !== '' && $arParams["PERMISSION"] >= "W")
{
	foreach ($arResult["SECTIONS"] as $key => $res)
	{
		$res['~NEW_LINK'] .= '&'.bitrix_sessid_get();
		$res['NEW_LINK'] .= '&'.bitrix_sessid_get();
		$res['EDIT_LINK'] .= '&'.bitrix_sessid_get();
		$res['~EDIT_LINK'] .= '&'.bitrix_sessid_get();
		$res['EDIT_ICON_LINK'] .= '&'.bitrix_sessid_get();
		$res['~EDIT_ICON_LINK'] .= '&'.bitrix_sessid_get();
		$res['~DROP_LINK'] .= '&'.bitrix_sessid_get();
		$res['DROP_LINK'] .= '&'.bitrix_sessid_get();
		$arResult["SECTIONS"][$key] = $res;
	}
}

/********************************************************************
				/For custom templates
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y"):
	$title = (!empty($arResult["SECTION"]["NAME"]) ? $arResult["SECTION"]["NAME"] : $arResult["GALLERY"]["NAME"]);
	$title = (!empty($title) ? $title : GetMessage("P_ALBUMS"));
	$APPLICATION->SetTitle($title);
endif;
/************** Admin panel ****************************************/
// if ($arParams["DISPLAY_PANEL"] == "Y" && $GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("iblock"))
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
/********************************************************************
				/Standart
********************************************************************/
return $arResult;
?>