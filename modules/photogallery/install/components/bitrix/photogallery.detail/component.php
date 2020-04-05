<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
elseif (intval($arParams["ELEMENT_ID"]) <= 0)
{
	if ($arParams["SET_STATUS_404"] == "Y")
		CHTTP::SetStatus("404 Not Found");
	return ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
}
/********************************************************************
				For custom components
********************************************************************/
$arParams["COMMENTS_TYPE"] = strtoupper($arParams["COMMENTS_TYPE"]);
$arParams["ELEMENT_SORT_FIELD"] = strtoupper($arParams["ELEMENT_SORT_FIELD"]);
$arParams["ELEMENT_SORT_FIELD1"] = strtoupper($arParams["ELEMENT_SORT_FIELD1"]);
$arParams["PROPERTY_CODE"] = (!is_array($arParams["PROPERTY_CODE"]) ? array() : $arParams["PROPERTY_CODE"]);
//if ($arParams["SHOW_RATING"] == "Y")
{
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_count";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_RATING";
}
//if ($arParams["SHOW_COMMENTS"] == "Y")
{
	if ($arParams["COMMENTS_TYPE"] == "FORUM")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["COMMENTS_TYPE"] == "BLOG")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_BLOG_COMMENTS_CNT";
}
if (!empty($arParams["ELEMENT_SORT_FIELD"]))
{
	if ($arParams["ELEMENT_SORT_FIELD"] == "SHOWS"):
		$arParams["ELEMENT_SORT_FIELD"] = "SHOW_COUNTER";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "RATING"):
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_RATING";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM"):
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG"):
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_BLOG_COMMENTS_CNT";
	endif;
}

if (!empty($arParams["ELEMENT_SORT_FIELD1"]))
{
	if ($arParams["ELEMENT_SORT_FIELD1"] == "SHOWS"):
		$arParams["ELEMENT_SORT_FIELD1"] = "SHOW_COUNTER";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "RATING"):
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_RATING";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM"):
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG"):
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_BLOG_COMMENTS_CNT";
	endif;
}
/********************************************************************
				/For custom components
********************************************************************/

/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
	$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");

	$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
	$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"])!="DESC" ? "ASC" : "DESC");
	$arParams["ELEMENT_SORT_FIELD1"] = (empty($arParams["ELEMENT_SORT_FIELD1"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD1"]));
	$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER1"]) != "DESC" ? "ASC" : "DESC");
	$arParams["ELEMENT_SELECT_FIELDS"] = (is_array($arParams["ELEMENT_SELECT_FIELDS"]) ? $arParams["ELEMENT_SELECT_FIELDS"] : array());
	$arParams["PROPERTY_CODE"] = (!is_array($arParams["PROPERTY_CODE"]) ? array() : $arParams["PROPERTY_CODE"]);
	foreach($arParams["PROPERTY_CODE"] as $key => $val)
		if($val==="")
			unset($arParams["PROPERTY_CODE"][$key]);

//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"detail_edit" => "PAGE_NAME=detail_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#&ACTION=#ACTION#",
		"detail_slide_show" => "PAGE_NAME=detail_slide_show".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"search" => "PAGE_NAME=search",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#",
		"upload" => "PAGE_NAME=upload".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=upload");
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
	$arParams["COMMENTS_TYPE"] = strToUpper($arParams["COMMENTS_TYPE"]);
	$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"]=="Y" ? "Y" : "N");
	if(!is_array($arParams["GROUP_PERMISSIONS"]))
		$arParams["GROUP_PERMISSIONS"] = array(2);

	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] :
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
//***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;

	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] != "N" ? "Y" : "N"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default

/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Main Data
********************************************************************/
$arResult["ELEMENT"] = array();
$ELEMENT_ID = intVal($arParams["ELEMENT_ID"]);
$cache = new CPHPCache;
$cache_path = "/".SITE_ID."/photogallery/".$arParams["IBLOCK_ID"]."/section".$arParams["SECTION_ID"];

/************** ELEMENT ********************************************/
$cache_id = "element_".$arParams["ELEMENT_ID"];
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$arResult["ELEMENT"] = $cache->GetVars();
}
else
{
	CModule::IncludeModule("iblock");
	CModule::IncludeModule("photogallery");

	//SELECT
	$arSelect = array(
		"ID",
		"CODE",
		"ACTIVE",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"SECTION_PAGE_URL",
		"NAME",
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
		"PROPERTY_*"
	);
	foreach ($arParams["ELEMENT_SELECT_FIELDS"] as $val)
	{
		$val = strtoupper($val);
		if (strpos($val, "PROPERTY_") !== false && !in_array($val, $arParams["PROPERTY_CODE"]))
			$arParams["PROPERTY_CODE"][] = $val;
		elseif (strpos($val, "PROPERTY_") === false && !in_array($val, $arSelect))
			$arSelect[] = $val;
	}

	$arParams["PROPERTY_CODE"][] = "PROPERTY_REAL_PICTURE";
	//WHERE
	$arFilter = array(
		"IBLOCK_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CHECK_PERMISSIONS" => "Y",
		"ID" => $ELEMENT_ID);

	//EXECUTE
	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

	if (!($obElement = $rsElement->GetNextElement()) || !($arElement = $obElement->GetFields()))
	{
		ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
		if ($arParams["SET_STATUS_404"] == "Y")
			CHTTP::SetStatus("404 Not Found");
		return 0;
	}

	if ($arParams["SECTION_ID"] != $arElement["IBLOCK_SECTION_ID"] && intVal($arParams["SECTION_ID"]) > 0)
	{
		$url = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"],
				"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"],
				"ELEMENT_ID" => $arElement["ID"]
			)
		);
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}

	$arElement["PROPERTIES"] = $obElement->GetProperties();

	$arElement["DISPLAY_PROPERTIES"] = array();
	foreach ($arParams["PROPERTY_CODE"] as $pid)
	{
		$prop = &$arElement["PROPERTIES"][$pid];
		if ((is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0) || (!is_array($prop["VALUE"]) && strlen($prop["VALUE"]) > 0))
		{
			$arElement["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arElement, $prop, "news_out");
		}
	}

	$arElement["DETAIL_PICTURE"] = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
	$arElement["REAL_PICTURE"] = CFile::GetFileArray($arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);
	$arElement["PICTURE"] = $arElement["DETAIL_PICTURE"];
	if (empty($arElement["PICTURE"]))
	{
		$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
		$arElement["PICTURE"] = $arElement["PREVIEW_PICTURE"];
	}

	$arElement["TAGS_LIST"] = array();
	if ($arParams["SHOW_TAGS"] == "Y" && !empty($arElement["TAGS"]) && CModule::IncludeModule("search"))
	{
		$ar = tags_prepare($arElement["~TAGS"], SITE_ID);
		foreach ($ar as $name => $tags)
		{
			$ar["~TAGS_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()).
				(strpos($arParams["~SEARCH_URL"], "?") === false ? "?" : "&")."tags=";
			$ar["TAGS_URL"] = htmlspecialcharsbx($ar["~TAGS_URL"]).urlencode($tags);
			$ar["TAGS_NAME"] = htmlspecialcharsex($tags);
			$arElement["TAGS_LIST"][] = $ar;
		}
	}
	$arElement["DATE_CREATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arElement["DATE_CREATE"], CSite::GetDateFormat()));
	$arResult["ELEMENT"] = $arElement;

	if ($arParams["CACHE_TIME"] > 0 && !empty($arResult["ELEMENT"])):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache($arResult["ELEMENT"]);
	endif;
}
/************** GALLERY & PERMISSION *******************************/
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
$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);
if ($res > 400)
	return false;
elseif ($res == 301)
{
	$url = CComponentEngine::MakePathFromTemplate(
		$arParams["~SECTION_URL"],
		array(
			"USER_ALIAS" => $arGallery["CODE"],
			"SECTION_ID" => $arParams["SECTION_ID"]
		)
	);

	LocalRedirect($url, false, "301 Moved Permanently");
	return false;
}
elseif (!$oPhoto->CheckPermission($arParams["PERMISSION"], $arResult["SECTION"]))
	return false;
elseif ($arParams["PERMISSION"] < "U" && $arParams["USE_PERMISSIONS"] == "Y")
{
	$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GROUP_PERMISSIONS"]);
	if (empty($res)):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;
}
/************** ELEMENTS LISTS *************************************/
//WHERE
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"CHECK_PERMISSIONS" => "Y");
if ($arParams["PERMISSION"] < "U"):
	$arFilter["ACTIVE"] = "Y";
endif;
//ORDER BY
$arSort = array();
foreach (array($arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
	$arParams["ELEMENT_SORT_FIELD1"] => $arParams["ELEMENT_SORT_ORDER1"]) as $key => $val):
	if (empty($key))
		continue;
	$arSort[$key] = $val;
	$arParams["ELEMENT_SELECT_FIELDS"][] = $key;
endforeach;
if (!array_key_exists("ID", $arSort))
	$arSort["ID"] = "ASC";

$cache_id = "elementlist_".serialize(array(
	"ELEMENT_ID" => $arParams["ELEMENT_ID"],
	"FILTER" => $arFilter,
	"SORT" => $arSort
));

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$arResult["ELEMENTS_LIST"] = $cache->GetVars();
}
else
{
	CModule::IncludeModule("iblock");
	$arResult["ELEMENTS_LIST"] = array(
		"PREV_ELEMENT" => array(),
		"PREV_ELEMENT_COUNT" => 0,
		"NEXT_ELEMENT" => array()
	);
	$arSelect = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME");
	$db_res = CIBlockElement::GetList($arSort, $arFilter, false, array("nElementID" => $arParams["ELEMENT_ID"], "nPageSize" => 1), $arSelect);
	$bFounded = false;
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$res["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $res["ID"]));
			$res["DETAIL_PAGE_URL"] = htmlspecialcharsbx($res["~DETAIL_PAGE_URL"]);
			if ($res["ID"] == $arParams["ELEMENT_ID"])
			{
				$bFounded = true;
				$arResult["ELEMENTS_LIST"]["PREV_ELEMENT_COUNT"] = $res["RANK"];
			}
			elseif ($bFounded === false)
			{
				$arResult["ELEMENTS_LIST"]["PREV_ELEMENT"] = $res;
			}
			elseif ($bFounded === true)
			{
				$arResult["ELEMENTS_LIST"]["NEXT_ELEMENT"] = $res;
				break;
			}
		} while ($res = $db_res->Fetch());

		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache($arResult["ELEMENTS_LIST"]);
		}
	}
}
/********************************************************************
				/Main Data
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
/************** Custom Components **********************************/
$ii = $arResult["ELEMENTS_LIST_CURRENT_NUMBER"] = intVal($arResult["ELEMENTS_LIST"]["PREV_ELEMENT_COUNT"]);
$arResult["ELEMENTS_LIST"] = array(
	$ii - 2 => $arResult["ELEMENTS_LIST"]["PREV_ELEMENT"],
	$ii => $arResult["ELEMENTS_LIST"]["NEXT_ELEMENT"]
);
$arResult["ELEMENT"]["CURRENT"] = array(
	"NO" => $ii,
	"COUNT" => ($arParams["PERMISSION"] < "U" ? $arResult["SECTION"]["SECTION_ELEMENTS_CNT"] : $arResult["SECTION"]["SECTION_ELEMENTS_CNT_ALL"])
);
/************** Custom Components/**********************************/
$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
$arResult["SECTION"]["BACK_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~BACK_LINK"]);
if ($arParams["PERMISSION"] >= "U")
{
	$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
	$arResult["SECTION"]["UPLOAD_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~UPLOAD_LINK"]);

	$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
	$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlspecialcharsbx($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);

	$arResult["ELEMENT"]["~EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"], "ACTION" => "edit"));
	$arResult["ELEMENT"]["EDIT_URL"] = htmlspecialcharsbx($arResult["ELEMENT"]["~EDIT_URL"]);

	$arResult["ELEMENT"]["~DROP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"],
			"ELEMENT_ID" => $arResult["ELEMENT"]["ID"], "ACTION" => "drop")).
			(strpos($arParams["~DETAIL_EDIT_URL"], "?") === false ? "?" : "&").bitrix_sessid_get();
	$arResult["ELEMENT"]["DROP_URL"] = htmlspecialcharsbx($arResult["ELEMENT"]["~DROP_URL"]);
}

$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlspecialcharsbx($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);

$arResult["~SLIDE_SHOW"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"],
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
$arResult["SLIDE_SHOW"] = htmlspecialcharsbx($arResult["~SLIDE_SHOW"]);
/*************************************************************************
			/Data
*************************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/

if($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arResult["SECTION"]["NAME"].": ".$arResult["ELEMENT"]["NAME"]);

/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] != "N")
{
	$arResult["SECTION"]["PATH"] = (is_array($arResult["SECTION"]["PATH"]) ? $arResult["SECTION"]["PATH"] : array());
	$bFounded = ($arParams["BEHAVIOUR"] == "USER" ? false : true);
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFounded):
			$bFounded = $arResult["GALLERY"]["ID"] == $arPath["ID"];
			continue;
		endif;
		$APPLICATION->AddChainItem($arPath["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem($arResult["ELEMENT"]["NAME"]);
}

/************** Increment shows counter & Admin Panel **************/
if (CModule::IncludeModule("iblock"))
{
	CIBlockElement::CounterInc($arResult["ELEMENT"]["ID"]);
	// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// {
		// CIBlock::ShowPanel($arParams["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
	// }
}
/************** Returns ********************************************/
return $arResult["ELEMENT"]["ID"];
/********************************************************************
				/Standart
********************************************************************/
?>