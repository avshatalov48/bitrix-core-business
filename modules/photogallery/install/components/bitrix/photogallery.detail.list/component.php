<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")): // !important
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")): // !important
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
endif;

//CUtil::InitJSCore(array('window', 'ajax'));

/********************************************************************
				For custom components
********************************************************************/
$arParams["PROPERTY_CODE"] = (!is_array($arParams["PROPERTY_CODE"]) ? array() : $arParams["PROPERTY_CODE"]);
$arParams["ELEMENT_SORT_FIELD"] = strtoupper($arParams["ELEMENT_SORT_FIELD"]);
$arParams["ELEMENT_SORT_FIELD1"] = strtoupper($arParams["ELEMENT_SORT_FIELD1"]);
$arParams["COMMENTS_TYPE"] = strtoupper($arParams["COMMENTS_TYPE"]);
//if ($arParams["SHOW_RATING"] == "Y")
{
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_count";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_rating";
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
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_rating";
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
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_rating";
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
$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"] > 0 ? $arParams["SECTION_ID"] : $_REQUEST["SECTION_ID"]);
$arParams["ELEMENT_ID"] = intVal($_REQUEST["ELEMENT_ID"] > 0 ? $_REQUEST["ELEMENT_ID"] : $arParams["ELEMENT_ID"]);
$arParams["SELECT_SURROUNDING"] = ($arParams["SELECT_SURROUNDING"] === "Y" ? "Y" : "N");

$arParams["ELEMENTS_LAST_COUNT"] = intVal($arParams["ELEMENTS_LAST_COUNT"]);
$arParams["ELEMENTS_LAST_TIME"] = intVal($arParams["ELEMENT_LAST_TIME"]);
$arParams["ELEMENTS_LAST_TIME_FROM"] = trim($arParams["ELEMENTS_LAST_TIME_FROM"]);
$arParams["ELEMENTS_LAST_TIME_TO"] = trim($arParams["ELEMENTS_LAST_TIME_TO"]);

$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENT_SORT_FIELD1"] = (empty($arParams["ELEMENT_SORT_FIELD1"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD1"]));
$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER1"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENT_FILTER"] = (is_array($arParams["ELEMENT_FILTER"]) ? $arParams["ELEMENT_FILTER"] : array()); // hidden params
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
		"detail_slide_show" => "PAGE_NAME=detail_slide_show".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"search" => "PAGE_NAME=search");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] == "Y" ? "Y" : "N");
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");
$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
//$arParams["PAGE_ELEMENTS"] = ($arParams["PAGE_ELEMENTS"] > 0 ? $arParams["PAGE_ELEMENTS"] : 10);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = (empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? "modern" : $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 5);
if (!empty($_REQUEST["direction"]))
	$arParams["PAGE_NAVIGATE"] = $_REQUEST["direction"];
$arParams["PAGE_NAVIGATE"] = strtolower(in_array(strtolower($arParams["PAGE_NAVIGATE"]), array("next", "prev")) ? $arParams["PAGE_NAVIGATE"] : "current");

$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] :
	$DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");

// Additional sights
$arParams["PICTURES"] = array();
$arParams["ADDITIONAL_SIGHTS"] = (is_array($arParams["ADDITIONAL_SIGHTS"]) ? $arParams["ADDITIONAL_SIGHTS"] : array()); // sights list from component params
$arParams["PICTURES_SIGHT"] = strToLower(is_array($arParams["PICTURES_SIGHT"]) ? '' : $arParams["PICTURES_SIGHT"]); // current sight
$arParams["GALLERY_SIZE"]  = intVal($arParams["GALLERY_SIZE"]);

// Socnet Hidden Params
$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");
$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ?  $arParams["GALLERY_AVATAR_SIZE"] : 50);

$arParams["PASSWORD_CHECKED"] = true;
//***************** STANDART ***************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
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
$arResult["SECTION"] = array();
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
				"SECTION_ID" => $arParams["SECTION_ID"]));
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}
	elseif (!$oPhoto->CheckPermission($arParams["PERMISSION"], $arResult["SECTION"]))
	{
		return false;
	}
}

/********************************************************************
				Main values
********************************************************************/
$arResult["ELEMENTS_LIST"] = array();
$cache = new CPHPCache;
/********************************************************************
				/Main values
********************************************************************/

/********************************************************************
				Actions
********************************************************************/
include_once(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/action.php"));
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** ELEMENTS LIST **************************************/
if (!empty($arParams["ADDITIONAL_SIGHTS"]))
{
	$_REQUEST["PICTURES_SIGHT"] = (empty($_REQUEST["PICTURES_SIGHT"]) && !empty($_REQUEST["picture_sight"]) ? $_REQUEST["picture_sight"] : $_REQUEST["PICTURES_SIGHT"]);
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
if ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail")
	$arParams["PICTURES_SIGHT"] = (in_array($arParams["PICTURES_SIGHT"], array_keys($arParams["PICTURES"])) ? $arParams["PICTURES_SIGHT"] : "standart");
if ($arParams["THUMBNAIL_SIZE"] > 0)
	$arParams["PICTURES"]["standart"] = array("size" => $arParams["THUMBNAIL_SIZE"]);
//PROPERTIES
if (!in_array(strToUpper($arParams["PICTURES_SIGHT"]), array("DETAIL", "PREVIEW", "STANDART"))):
	$arParams["PROPERTY_CODE"][] = "PROPERTY_".strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE";
endif;
//PAGENAVIGATION
$arNavParams = false; $arNavigation = false;
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$arNavParams = array("nPageSize"=>$arParams["PAGE_ELEMENTS"], "bDescPageNumbering"=>($arParams["USE_DESC_PAGE"] == "N" ? false : true), "bShowAll" => false);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}
// ACCESS
$arResult["USER_HAVE_ACCESS"] = "Y";
if ($arParams["PERMISSION"] < "U" && $arParams["USE_PERMISSIONS"] == "Y")
{
	$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GROUP_PERMISSIONS"]);
	$arResult["USER_HAVE_ACCESS"] = (empty($res) ? "N" : "Y");
}
//SORT
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

//SELECT
$arSelect = array(
	"ID",
	"CODE",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	//"SECTION_PAGE_URL",
	"NAME",
	"ACTIVE",
	"DETAIL_PICTURE",
	"PREVIEW_PICTURE",
	//"PREVIEW_TEXT",
	//"DETAIL_TEXT",
	//"DETAIL_PAGE_URL",
	"PREVIEW_TEXT",
	//"PREVIEW_TEXT_TYPE",
	//"DETAIL_TEXT_TYPE",
	"TAGS",
	"DATE_CREATE",
	"CREATED_BY",
	"SHOW_COUNTER",
	"PROPERTY_*");
foreach ($arParams["ELEMENT_SELECT_FIELDS"] as $val)
{
	$val = strtoupper($val);
	if (strpos($val, "PROPERTY_") !== false && !in_array($val, $arParams["PROPERTY_CODE"])):
		$arParams["PROPERTY_CODE"][] = $val;
	elseif (strpos($val, "PROPERTY_") === false && !in_array($val, $arSelect)):
		$arSelect[] = $val;
	endif;
}
//$arSelect = array_keys(array_flip(array_diff($arSelect, array_keys($arSort))));
//WHERE
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"CHECK_PERMISSIONS" => "Y");
if ($arParams["PERMISSION"] < "U"):
	$arFilter["ACTIVE"] = "Y";
endif;

$maxWidth = 1; $maxHeight = 1; $arElements = array(); $arElementsJS = array();
// PASSWORDS
if ($arParams["SECTION_ID"] > 0)
	$arFilter["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
else
{
	$arMargin = array();
	$arrFilter = $arFilter;
	$res = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION");
	if (is_array($res) && !empty($res["UF_PASSWORD"]))
	{
		CModule::IncludeModule("iblock");
		$arrFilter["!=UF_PASSWORD"] = "";
		$db_res = CIBlockSection::GetList(Array(), $arrFilter);
		if ($db_res && $res = $db_res->Fetch())
		{
			do
			{
				$arMargin[] = array($res["LEFT_MARGIN"], $res["RIGHT_MARGIN"]);
			}while ($res = $db_res->Fetch());
		}
		if (count($arMargin) > 0)
			$arFilter["!SUBSECTION"] = $arMargin;
	}
}

// ADDITIONAL FILTERS
if ($arParams["ELEMENT_LAST_TYPE"] == "count" && $arParams["ELEMENTS_LAST_COUNT"] > 0)
{
	CModule::IncludeModule("iblock");
	$db_res = CIBlockElement::GetList(array("ID" => "DESC"), $arFilter, false, array("nTopCount" => $arParams["ELEMENTS_LAST_COUNT"]), array("ID"));
	$iLastID = 0;

	while ($res = $db_res->Fetch())
		$arFilter[">=ID"] = intVal($res["ID"]);
}
elseif ($arParams["ELEMENT_LAST_TYPE"] == "time" && $arParams["ELEMENTS_LAST_TIME"] > 0)
{
	$arFilter[">=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), (time()-($arParams["ELEMENTS_LAST_TIME"]*3600*24)+CTimeZone::GetOffset()));
}
elseif ($arParams["ELEMENT_LAST_TYPE"] == "period" && (strLen($arParams["ELEMENTS_LAST_TIME_FROM"]) > 0 || strLen($arParams["ELEMENTS_LAST_TIME_TO"]) > 0))
{
	if (strLen($arParams["ELEMENTS_LAST_TIME_FROM"]) > 0)
		$arFilter[">=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), MakeTimeStamp($arParams["ELEMENTS_LAST_TIME_FROM"]));
	if (strLen($arParams["ELEMENTS_LAST_TIME_TO"]) > 0)
		$arFilter["<=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), MakeTimeStamp($arParams["ELEMENTS_LAST_TIME_TO"]));
}
if (!empty($arParams["ELEMENT_FILTER"]))
	$arFilter = array_merge($arParams["ELEMENT_FILTER"], $arFilter);

if ($arNavParams && $arParams["ELEMENT_ID"] > 0)
{
	CModule::IncludeModule("iblock");
	$db_res = CIBlockElement::GetList($arSort, $arFilter, false, array("nElementID" => $arParams["ELEMENT_ID"]), array("ID", "NAME"));
	if ($db_res && $res = $db_res->Fetch())
	{
		$number = $res["RANK"];
		if ($arParams["PAGE_NAVIGATE"] == "next")
			$number++;
		elseif ($arParams["PAGE_NAVIGATE"] == "prev")
			$number--;

		if (!$arNavParams["bDescPageNumbering"])
		{
			$arNavParams["iNumPage"] = ceil($number / $arNavParams["nPageSize"]);
		}
		else
		{
			$count = CIBlockElement::GetList($arSort, $arFilter, array());
			if ($number >= $count)
				$arNavParams["iNumPage"] = 1;
			elseif ($number <= ($count % $arNavParams["nPageSize"]))
				$arNavParams["iNumPage"] = ceil($count / $arNavParams["nPageSize"]);
			else
				$arNavParams["iNumPage"] = ceil(($count - $number + 1) / $arNavParams["nPageSize"]);
		}
	}
}
$arParams["FILTER"] = $arFilter;
$arParams["SORTING"] = $arSort;

// EXECUTE
$cache_id = "detail_list_".serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"SELECT_SURROUNDING" => $arParams["SELECT_SURROUNDING"],
	"PERMISSION" => $arParams["PERMISSION"],
	"PAGE_ELEMENTS" => $arParams["PAGE_ELEMENTS"],
	"FILTER" => $arFilter,
	"SELECT" => $arSelect,
	"PICTURES_SIGHT" => $arParams["PICTURES_SIGHT"],
	"ORDER" => $arOrder,
	"NAV1" => $arNavParams,
	"NAV2" => $arNavigation,
	"BEHAVIOUR" => $arParams["BEHAVIOUR"],
	"USER_ALIAS" => $arParams["USER_ALIAS"]
));
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;

$cache_path = "/".SITE_ID."/photogallery/".$arParams["IBLOCK_ID"]."/section".$arParams["SECTION_ID"];

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arResult = array_merge($arResult, $res);
	$arResult["ELEMENTS"]["MAX_WIDTH"] = $res["MAX_WIDTH"];
	$arResult["ELEMENTS"]["MAX_HEIGHT"] = $res["MAX_HEIGHT"];
	$arResult["ELEMENTS_CNT"] = $res["ELEMENTS_CNT"];

}
if (!is_array($arResult["ELEMENTS_LIST"]) || empty($arResult["ELEMENTS_LIST"]))
{
	CModule::IncludeModule("iblock"); CModule::IncludeModule("photogallery");
	$bParseTags = CModule::IncludeModule("search");
	if ($arParams["SELECT_SURROUNDING"] == "Y")
	{
		$arResult["ELEMENTS_CNT"] = CIBlockElement::GetList($arSort, $arFilter, array());
		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, array("nElementID" => $arParams["ELEMENT_ID"], "nPageSize" => $arParams["PAGE_ELEMENTS"]), $arSelect);
	}
	else
	{
		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
	}

	$rsElements->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
	if ($rsElements)
	{
		$arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx($navComponentObject, GetMessage("P_PHOTOS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$arResult["NAV_RESULT"] = $rsElements;
		$arGalleries = array();
		$arSections = array();

		$strFileId = "";
		$arFileIndex = array();
		$arRealPicIndex = array();

		while ($obElement = $rsElements->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			// PROPERIES
			$props = $obElement->GetProperties();
			$arElement["PROPERTIES"] = array();
			foreach ($props as $key => $value)
				$arElement["PROPERTIES"][$key] = array("VALUE" =>$value["VALUE"]);

			$arElement["DISPLAY_PROPERTIES"] = array();
			foreach ($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arElement["PROPERTIES"][$pid];
				if ((is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0) || (!is_array($prop["VALUE"]) && strlen($prop["VALUE"]) > 0))
					$arElement["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arElement, $prop, "news_out");
			}

			// GALLERY INFO IF NEED
			$arGallery = array();
			if ($arParams["BEHAVIOUR"] != "USER")
			{
				// empty block
			}
			elseif (!empty($arParams["USER_ALIAS"]))
			{
				$arGallery = $arResult["GALLERY"];
			}
			else
			{
				if (empty($arSections[$arElement["IBLOCK_SECTION_ID"]])) // Get Section Info
				{
					$db_res = CIBlockSection::GetList(array(), array("ID" => $arElement["IBLOCK_SECTION_ID"]), false,
						array("ID", "LEFT_MARGIN", "RIGHT_MARGIN"));
					if ($db_res && $res = $db_res->Fetch())
						$arSections[$arElement["IBLOCK_SECTION_ID"]] = $res;
				}
				if (empty($arGalleries[$arElement["IBLOCK_SECTION_ID"]])) // Get Gallery Info
				{
					$db_res = CIBlockSection::GetList(array(), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "SECTION_ID" => 0,
						"!LEFT_MARGIN" => $arSections[$arElement["IBLOCK_SECTION_ID"]]["LEFT_MARGIN"],
						"!RIGHT_MARGIN" => $arSections[$arElement["IBLOCK_SECTION_ID"]]["RIGHT_MARGIN"],
						"!ID" => $arElement["IBLOCK_SECTION_ID"]));
					if ($db_res && $res = $db_res->Fetch())
					{
						if (intVal($res["PICTURE"]) > 0)
						{
							$res["~PICTURE"] = $res["PICTURE"];
						}
						elseif ($arParams["SHOW_PHOTO_USER"] == "Y")
						{
							if (empty($arResult["USERS"][$res["CREATED_BY"]]))
							{
								$db_user = CUser::GetByID($res["CREATED_BY"]);
								$res_user = $db_user->Fetch();
								$arResult["USER"][$res_user["ID"]] = $res_user;
							}
							$res["~PICTURE"] = intVal($arResult["USER"][$res["CREATED_BY"]]["PERSONAL_PHOTO"]);
						}

						$res["PICTURE"] = CFile::GetFileArray($res["~PICTURE"]);

						if (!empty($res["PICTURE"]))
						{
							$image_resize = CFile::ResizeImageGet($res["PICTURE"], array(
								"width" => $arParams["GALLERY_AVATAR_SIZE"], "height" => $arParams["GALLERY_AVATAR_SIZE"]));
							$res["PICTURE"]["SRC"] = $image_resize["src"];
						}

						$res["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
							array("USER_ALIAS" => $res["CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"]));
						$res["URL"] = htmlspecialcharsbx($res["~URL"]);
						$arGalleries[$arElement["IBLOCK_SECTION_ID"]] = $res;
					}
				}
				$arGallery = $arElement["GALLERY"] = $arGalleries[$arElement["IBLOCK_SECTION_ID"]];
			}
			//PICTURE
			if (strToUpper($arParams["PICTURES_SIGHT"]) == "DETAIL" && !empty($arElement["DETAIL_PICTURE"]))
				$arElement["~PICTURE"] = $arElement["DETAIL_PICTURE"];
			elseif (!empty($arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"]))
				$arElement["~PICTURE"] = $arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"];
			else
				$arElement["~PICTURE"] = $arElement["PREVIEW_PICTURE"];

			$strFileId .= ','.intVal($arElement["~PICTURE"]);
			$arFileIndex[$arElement["~PICTURE"]] = $arElement["ID"];

			if (strToUpper($arParams["PICTURES_SIGHT"]) != "REAL" && !empty($arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]))
			{
				$strFileId .= ','.intVal($arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);
				$arRealPicIndex[$arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]] = $arElement["ID"];
			}

			//URL
			$arElement["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
				array("USER_ALIAS" => $arGallery["CODE"], "SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $arElement["ID"],
					"USER_ID" => $arGallery["CREATED_BY"], "GROUP_ID" => $arGallery["SOCNET_GROUP_ID"]));
			$arElement["URL"] = htmlspecialcharsbx($arElement["~URL"]);
			$arElement["~SLIDE_SHOW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"],
				array("USER_ALIAS" => $arGallery["CODE"], "SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $arElement["ID"],
					"USER_ID" => $arElement["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arElement["GALLERY"]["SOCNET_GROUP_ID"]));
			$arElement["SLIDE_SHOW_URL"] = htmlspecialcharsbx($arElement["~SLIDE_SHOW_URL"]);

			//TAGS
			$arElement["TAGS_LIST"] = array();
			if (!empty($arElement["TAGS"]) && $bParseTags)
			{
				$ar = tags_prepare($arElement["TAGS"], SITE_ID);
				if (!empty($ar))
				{
					foreach ($ar as $name => $tags)
					{
						$arr = array(
							"TAG_NAME" => $tags,
							"~TAGS_URL" => CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()));
						$arr["~TAGS_URL"] .= (strpos($arParams["~SEARCH_URL"], "?") === false ? "?" : "&")."tags=".$tags;
						$arr["TAGS_URL"] = htmlspecialcharsbx($arr["~TAGS_URL"]);
						$arr["TAGS_NAME"] = $tags;
						$arElement["TAGS_LIST"][] = $arr;
					}
				}
			}

			$arElement["DATE_CREATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arElement["DATE_CREATE"], CSite::GetDateFormat()));
			$arElements[$arElement["ID"]] = $arElement;

			$arElementsJS[$arElement["ID"]] = array(
				"id" => intVal($arElement["ID"]),
				"title" => $arElement["NAME"],
				"description" => $arElement["PREVIEW_TEXT"],
				"shows" => $arElement["SHOW_COUNTER"],
				"url" => $arElement["~URL"]
			);
		}

		if (strLen($strFileId) > 0)
		{
			$rsFile = CFile::GetList(array(), array("@ID" => $strFileId));
			while ($obFile = $rsFile->Fetch())
			{
				$fileId = $obFile['ID'];
				$obFile["SRC"] = CFile::GetFileSRC($obFile);

				$io = CBXVirtualIo::GetInstance();
				$fName = $io->ExtractNameFromPath($obFile["SRC"]);
				$fPath = $io->ExtractPathFromPath($obFile["SRC"]);
				$obFile["SRC"] = $fPath.'/'.urlencode($fName);

				if ($ind = $arFileIndex[$fileId])
				{
					$arElements[$ind]["PICTURE"] = $obFile;

					if (!empty($arParams["PICTURES_SIGHT"]) && $obFile[$arParams["PICTURES_SIGHT"]])
					{
						$size = intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]);
						$w = $arElements[$ind]["PICTURE"]["WIDTH"];
						$h = $arElements[$ind]["PICTURE"]["HEIGHT"];
						if ($size > 0 && ($w > $size || $h > $size))
						{
							$koeff = min($size / $w, $size / $h);
							$arElements[$ind]["PICTURE"]["WIDTH"] = intVal($w * $koeff);
							$arElements[$ind]["PICTURE"]["HEIGHT"] = intVal($h * $koeff);
						}
					}

					if (empty($arElements[$ind]['REAL_PICTURE']))
						$arElements[$ind]['REAL_PICTURE'] = $obFile;

					// Update js array
					$arElementsJS[$ind]["src"] = $arElements[$ind]["PICTURE"]["SRC"];
					$arElementsJS[$ind]["width"] = $arElements[$ind]["PICTURE"]["WIDTH"];
					$arElementsJS[$ind]["height"] = $arElements[$ind]["PICTURE"]["HEIGHT"];

					// Update max width and max height
					$maxWidth = max($maxWidth, $arElements[$ind]["PICTURE"]["WIDTH"]);
					$maxHeight = max($maxHeight, $arElements[$ind]["PICTURE"]["HEIGHT"]);
				}
				elseif ($ind = $arRealPicIndex[$fileId])
				{
					$arElements[$ind]["REAL_PICTURE"] = $obFile;
				}
			}
		}
	}

	$arResult["ELEMENTS_LIST"] = $arElements;
	$arResult["ELEMENTS_LIST_JS"] = $arElementsJS;
	$arResult["ELEMENTS"]["MAX_WIDTH"] = $maxWidth;
	$arResult["ELEMENTS"]["MAX_HEIGHT"] = $maxHeight;
	$arResult["ELEMENTS_CNT"] = $arResult["ELEMENTS_CNT"];

	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

		$cache->EndDataCache(
			array(
				"ELEMENTS_CNT" => $arResult["ELEMENTS_CNT"],
				"ELEMENTS_LIST" => $arResult["ELEMENTS_LIST"],
				"ELEMENTS_LIST_JS" => $arResult["ELEMENTS_LIST_JS"],
				"MAX_WIDTH" => $arResult["ELEMENTS"]["MAX_WIDTH"],
				"MAX_HEIGHT" => $arResult["ELEMENTS"]["MAX_HEIGHT"],
				"NAV_STRING" => $arResult["NAV_STRING"],
				"NAV_RESULT" => $arResult["NAV_RESULT"]
			)
		);
	}
}
else
{
	$GLOBALS['NavNum'] = intVal($GLOBALS['NavNum']) + 1;
}

/************** URL ************************************************/
$arResult["~SLIDE_SHOW"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"], array(
	"USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => 0,
	"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])).
	(strpos($arParams["~DETAIL_SLIDE_SHOW_URL"], "?") === false ? "?" : "&").
	"BACK_URL=".urlencode($GLOBALS['APPLICATION']->GetCurPageParam());
$arResult["SLIDE_SHOW"] = htmlspecialcharsbx($arResult["~SLIDE_SHOW"]);
/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

unset($arParams["PICTURES"]["standart"]);
// for custom templates
if (in_array($this->getTemplateName(), array("table", "ascetic")) &&
	(!($this->__parent && is_dir($_SERVER['DOCUMENT_ROOT'].$this->__parent->__template->__folder."/bitrix/photogallery.detail.list/".$this->getTemplateName()))))
{
	$arParams["TEMPLATE"] = $this->getTemplateName();
	$this->setTemplateName(".default");
	$this->IncludeComponentTemplate();
}
else
{
	$this->IncludeComponentTemplate();
}
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y"):
	$APPLICATION->SetTitle(GetMessage("P_LIST_PHOTO"));
endif;
/************** Returns ********************************************/
if ($arParams["RETURN_FORMAT"] == "LIST"):
	return $arResult["ELEMENTS_LIST"];
else:
	$res = reset($arResult["ELEMENTS_LIST"]);
	return $res["ID"];
endif;
/********************************************************************
				/Standart
********************************************************************/
?>