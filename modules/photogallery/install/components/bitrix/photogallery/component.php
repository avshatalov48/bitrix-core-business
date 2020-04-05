<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));

$arParams["FILTER_NAME"] = "";
$arParams["SET_STATUS_404"] = "Y";
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
$arDefaultUrlTemplates404 = array(
	"section" => "#SECTION_ID#/",
	"section_edit" => "#SECTION_ID#/action/#ACTION#/",
	"section_edit_icon" => "#SECTION_ID#/icon/action/#ACTION#/",
	"index" => "",
	"search" => "search/",
	"detail" => "#SECTION_ID#/#ELEMENT_ID#/",
	"detail_edit" => "#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
	"detail_list" => "#SECTION_ID#/#ELEMENT_ID#/list/",
	"detail_slide_show" => "#SECTION_ID#/#ELEMENT_ID#/slide_show/",
	"upload" => "#SECTION_ID#/action/upload/",
);

$arDefaultUrlTemplatesN404 = array(
	"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	"detail_edit" => "PAGE_NAME=detail_edit&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#&ACTION=#ACTION#",
	"detail_list" => "PAGE_NAME=detail_list&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	"detail_slide_show" => "PAGE_NAME=detail_slide_show&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
	"search" => "PAGE_NAME=search",
	"section" => "PAGE_NAME=section&SECTION_ID=#SECTION_ID#",
	"section_edit" => "PAGE_NAME=section_edit&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
	"section_edit_icon" => "PAGE_NAME=section_edit_icon&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
	"index" => "",
	"upload" => "PAGE_NAME=upload&SECTION_ID=#SECTION_ID#&ACTION=upload",
);

$arDefaultVariableAliases404 = Array(
	"detail"=>array("ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"detail_edit"=>array("ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"detail_list"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"detail_slide_show"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ELEMENT_ID"=>"ELEMENT_ID","ELEMENT_CODE"=>"ELEMENT_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"search" => array("PAGE_NAME" => "PAGE_NAME"),
	"section"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "PAGE_NAME" => "PAGE_NAME"),
	"section_edit"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"section_edit_icon"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"index"=>array(),
	"upload"=>array("SECTION_ID" => "SECTION_ID","SECTION_CODE" => "SECTION_CODE", "PAGE_NAME" => "PAGE_NAME"),
);

$arComponentVariables = Array(
	"SECTION_ID","SECTION_CODE",
	"ELEMENT_ID","ELEMENT_CODE",
	"ACTION", "PAGE_NAME"
);

$arDefaultVariableAliases = Array(
	"SECTION_ID" => "SECTION_ID",
	"ELEMENT_ID" => "ELEMENT_ID",
	"ACTION" => "ACTION",
	"PAGE_NAME" => "PAGE_NAME"
);

if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$requestURL = $APPLICATION->GetCurPage(true);
	if (strpos($requestURL, "#photo") !== false)
		$requestURL = rtrim(substr($requestURL, 0, strpos($requestURL, "#")), "/")."/index.php";
	else
		$requestURL = false;

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables,
		$requestURL
	);

	if(!$componentPage)
		$componentPage = "index";
	elseif ($arVariables["ACTION"] == "upload")
		$componentPage = "upload";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"~URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases,
	);
	foreach ($arDefaultUrlTemplates404 as $url => $value)
	{
		if (empty($arUrlTemplates[$url]))
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arDefaultUrlTemplates404[$url];
		elseif (substr($arUrlTemplates[$url], 0, 1) == "/")
			$arResult["URL_TEMPLATES"][$url] = $arUrlTemplates[$url];
		else
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	$arParams["VARIABLE_ALIASES"] = (!is_array($arParams["VARIABLE_ALIASES"]) ? array() : $arParams["VARIABLE_ALIASES"]);
	$arResult["URL_TEMPLATES"] = array();

	if (!empty($arDefaultUrlTemplatesN404) && !empty($arParams["VARIABLE_ALIASES"]))
	{
		foreach ($arDefaultUrlTemplatesN404 as $url => $value)
		{
			$pattern = array(); $replace = array();
			foreach ($arParams["VARIABLE_ALIASES"] as $key => $res)
			{
				if ($key != $res && !empty($res))
				{
					$pattern[] = "/(^|([&?]+))".preg_quote($key, "/")."\=/is";
					$replace[] = "$1".$res."=";
				}
			}
			if (!empty($pattern))
			{
				$value = preg_replace($pattern, $replace, $value);
				$arDefaultUrlTemplatesN404[$url] = $value;
			}
		}
	}
	foreach ($arDefaultUrlTemplatesN404 as $url => $value)
	{
		$arParamsKill = array_merge($arComponentVariables, $arParams["VARIABLE_ALIASES"], array("return_array", "current", "direction", "AJAX_CALL"));
		$arResult["URL_TEMPLATES"][$url] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arParamsKill);
	}

	$componentPage = "";
	if (!empty($arVariables["PAGE_NAME"]))
		$componentPage = $arVariables["PAGE_NAME"];
	else
		$componentPage = "index";
}
if (!in_array($componentPage, array_keys($arDefaultUrlTemplates404)))
	$componentPage = "index";

if ($componentPage == "index" && $arParams["SET_STATUS_404"] == "Y" && $arParams["SEF_MODE"] == "Y")
{
	$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
	if ($folder404 != "/")
		$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
	if (substr($folder404, -1) == "/")
		$folder404 .= "index.php";

	if($folder404 != $APPLICATION->GetCurPage(true))
	{
		@define("ERROR_404","Y");
		CHTTP::SetStatus("404 Not Found");
	}
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && (intVal($arVariables["ELEMENT_ID"]) > 0 || strLen($arVariables["ELEMENT_CODE"]) > 0) && intVal($arResult["VARIABLES"]["SECTION_ID"]) <= 0)
{
	CModule::IncludeModule("iblock");
	if (intVal($arVariables["ELEMENT_ID"]) > 0)
		$rsElement = CIBlockElement::GetList(array(), array("ID" => intVal($arVariables["ELEMENT_ID"])));
	else
		$rsElement = CIBlockElement::GetList(array(), array("CODE" => intVal($arVariables["ELEMENT_CODE"])));

	if($arElement = $rsElement->Fetch())
	{
		$arVariables["ELEMENT_ID"] = $arElement["ID"];
		$arVariables["ELEMENT_CODE"] = $arElement["CODE"];
		$arVariables["SECTION_ID"] = $arElement["IBLOCK_SECTION_ID"];
	}
}

// TEMPLATE TABLE
$arParams["CELL_COUNT"] = intVal($arParams["CELL_COUNT"]);

$arResult["URL_TEMPLATES"]["sections_top"] = $arResult["URL_TEMPLATES"]["index"];
$arResult = array(
		"~URL_TEMPLATES" =>  $arUrlTemplates,
		"URL_TEMPLATES" => $arResult["URL_TEMPLATES"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases);
?><?

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
//$arParams["IBLOCK_TYPE"]
//$arParams["IBLOCK_ID"]
//$arParams["SECTION_ID"]
//$arParams["SECTION_CODE"]
//$arParams["ELEMENT_ID"]
//$arParams["ELEMENT_CODE"]
//$arParams["USER_ALIAS"]
//$arParams["BEHAVIOUR"]
//$arParams["GALLERY_ID"]
//$arParams["USER_ID"]

$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y"); // only one gallery for user
$arParams["SHOW_NAVIGATION"] = $arParams["SHOW_NAVIGATION"] == "Y" ? "Y" : "N";
//$arParams["SHOW_NAVIGATION"] = "Y";
//$arParams["GALLERY_GROUPS"] - user groups who can create gallery

//$arParams["ACTION"]
//$arParams["AJAX_CALL"]
// Page
//$arParams["ELEMENTS_USE_DESC_PAGE"] => $arParams["USE_DESC_PAGE"]
//$arParams["SECTION_PAGE_ELEMENTS"] => $arParams["PAGE_ELEMENTS"]
//$arParams["ELEMENTS_PAGE_ELEMENTS"] => $arParams["PAGE_ELEMENTS"]
$arParams["PAGE_NAVIGATION_TEMPLATE"] = (!empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? $arParams["PAGE_NAVIGATION_TEMPLATE"] : "modern");

// SECTION
$arParams["SECTION_PAGE_ELEMENTS"] = (is_set($arParams["SECTION_PAGE_ELEMENTS"]) ? intVal($arParams["SECTION_PAGE_ELEMENTS"]) : 10);
$arParams["SECTION_SORT_BY"] = (is_set($arParams["SECTION_SORT_BY"]) ? $arParams["SECTION_SORT_BY"] : "UF_DATE");
$arParams["SECTION_SORT_ORD"] = (strToUpper($arParams["SECTION_SORT_ORD"]) != "DESC" ? "ASC" : "DESC");

// ELEMENTS
$arParams["ELEMENTS_PAGE_ELEMENTS"] = (is_set($arParams["ELEMENTS_PAGE_ELEMENTS"]) ? intVal($arParams["ELEMENTS_PAGE_ELEMENTS"]) : 100);
$arParams["ELEMENT_SORT_FIELD"] = (is_set($arParams["ELEMENT_SORT_FIELD"]) ? $arParams["ELEMENT_SORT_FIELD"] : "SORT");
$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENT_SORT_FIELD1"] = (is_set($arParams["ELEMENT_SORT_FIELD"]) ? $arParams["ELEMENT_SORT_FIELD"] : "");
$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENTS_USE_DESC_PAGE"] = ($arParams["ELEMENTS_USE_DESC_PAGE"] == "N" ? "N" : "Y");

//$arParams["ELEMENTS_LAST_COUNT"]
//$arParams["ELEMENT_LAST_TIME"]
//$arParams["ELEMENT_FILTER"]
//$arParams["ELEMENTS_LAST_TYPE"]
//$arParams["ELEMENTS_LAST_TIME"]
//$arParams["ELEMENTS_LAST_TIME_FROM"]
//$arParams["ELEMENTS_LAST_TIME_TO"]
//$arParams["ELEMENT_LAST_TYPE"]

/****************** URL ********************************************/
//$arParams["GALLERIES_URL"]
//$arParams["GALLERY_URL"]
//$arParams["INDEX_URL"]
$arParams["SECTIONS_TOP_URL"] = $arParams["INDEX_URL"];
//$arParams["GALLERY_EDIT_URL"]
//$arParams["SECTION_URL"]
//$arParams["INDEX_URL"]
/****************** ADDITIONAL *************************************/
// Permissions
$arParams["USE_PERMISSIONS"] = "N";
$arParams["GROUP_PERMISSIONS"] = array();
//$arParams["PERMISSION"] // in component
//$arParams["PASSWORD_CHECKED"] // in component

// Visual
//$arParams["DATE_TIME_FORMAT_DETAIL"] => $arParams["DATE_TIME_FORMAT"]
//$arParams["DATE_TIME_FORMAT_SECTION"] => $arParams["DATE_TIME_FORMAT"]

//$arParams["THUMBS_SIZE"] // thumbs
//$arParams["PREVIEW_SIZE"] // detail
$arParams["ALBUM_PHOTO_SIZE"] = intval(intval($arParams["ALBUM_PHOTO_SIZE"]) > 0 ? $arParams["ALBUM_PHOTO_SIZE"] : 100);
// Deprecated
$arParams["ALBUM_PHOTO_THUMBS_SIZE"] = intval(intval($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) > 0 ? $arParams["ALBUM_PHOTO_THUMBS_SIZE"] : 100);

$arParams["THUMBNAIL_SIZE"] = intval($arParams["THUMBNAIL_SIZE"]) > 0 ? intval($arParams["THUMBNAIL_SIZE"]) : 90;
// For displaying albums list
$arParams["PHOTO_LIST_MODE"] = $arParams["PHOTO_LIST_MODE"] == "N" ? "N" : "Y";
$arParams["SHOWN_ITEMS_COUNT"] = intVal($arParams["SHOWN_ITEMS_COUNT"]) > 0 ? intVal($arParams["SHOWN_ITEMS_COUNT"]) : 6;

//$arParams["ADDITIONAL_SIGHTS"]
//$arParams["PICTURES_SIGHT"]
//$arParams["PICTURES_INFO"]
//$arParams["PICTURES"]
//$arParams["SHOW_TAGS"]

// Comments
$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" ? "Y" : "N");

$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "forum" || $arParams["COMMENTS_TYPE"] == "blog" ?
	$arParams["COMMENTS_TYPE"] : "none");
if ($arParams["USE_COMMENTS"] == "Y" && (
	($arParams["COMMENTS_TYPE"] == "forum" && (!IsModuleInstalled("forum") || !$arParams["FORUM_ID"])) ||
	($arParams["COMMENTS_TYPE"] == "blog" && (!IsModuleInstalled("blog") || !$arParams["BLOG_URL"]))
))
{
	$arParams["USE_COMMENTS"] = "N";
}

//$arParams["BLOG_URL"]
//$arParams["COMMENTS_COUNT"]
//$arParams["PATH_TO_BLOG"]
//$arParams["PATH_TO_USER"]
//$arParams["USE_CAPTCHA"]
//$arParams["PREORDER"]
//$arParams["FORUM_ID"]
//$arParams["PATH_TO_SMILE"]
//$arParams["URL_TEMPLATES_READ"]
//$arParams["SHOW_LINK_TO_FORUM"]

// Rating
//$arParams["USE_RATING"]
//$arParams["MAX_VOTE"]
//$arParams["VOTE_NAMES"]
//$arParams["DISPLAY_AS_RATING"]


// Gallery
//$arParams["GET_GALLERY_INFO"] - need info about gallery - used only in photogallery.detail.list
/****************** STANDART ***************************************/
//$arParams["CACHE_TYPE"]
//$arParams["CACHE_TIME"]
// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
$arParams["USE_PHOTO_TITLE"] = ($arParams["USE_PHOTO_TITLE"] == "Y" ? "Y" : "N");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] == "Y" ? "Y" : "N");
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["WATERMARK"] = ($arParams["WATERMARK"] == "N" ? "N" : "Y");
//
/****************** COMPONENTS *************************************/
// Upload
$arParams["UPLOADER_TYPE"] = "form";
//$arParams["UPLOAD_MAX_FILE"]
//$arParams["UPLOAD_MAX_FILE_SIZE"]
//$arParams["JPEG_QUALITY1"]
//$arParams["JPEG_QUALITY2"]
//$arParams["JPEG_QUALITY"]
//$arParams["WATERMARK"]
//$arParams["WATERMARK_MIN_PICTURE_SIZE"]
//$arParams["WATERMARK_COLORS"]

// Tags cloud
//$arParams["TAGS_PAGE_ELEMENTS"]
//$arParams["TAGS_PERIOD"]
//$arParams["TAGS_INHERIT"]
//$arParams["FONT_MAX"]
//$arParams["FONT_MIN"]
//$arParams["COLOR_NEW"]
//$arParams["COLOR_OLD"]
//$arParams["TAGS_SHOW_CHAIN"]
//$arParams["TEMPLATE_LIST"]
$arParams["ELEMENTS_PAGE_ELEMENTS"] = intVal($arParams["ELEMENTS_PAGE_ELEMENTS"]);
$arParams["ELEMENTS_PAGE_ELEMENTS"] = ($arParams["ELEMENTS_PAGE_ELEMENTS"] > 0 ? $arParams["ELEMENTS_PAGE_ELEMENTS"] : 50);

/****************** TEMPLATES **************************************/
//$arParams["SHOW_CONTROLS"]
//$arParams["DetailListViewMode"]
//$arParams["SHOW_PAGE_NAVIGATION"]
//$arParams["SQUARE"]
//$arParams["PERCENT"]
$arParams["SLIDER_COUNT_CELL"] = intVal($arParams["SLIDER_COUNT_CELL"]);
$arParams["SLIDER_COUNT_CELL"] = ($arParams["SLIDER_COUNT_CELL"] > 0 ? $arParams["SLIDER_COUNT_CELL"] : 3);
//$arParams["B_ACTIVE_IS_FINED"]
//$arParams["SHOW_DESCRIPTION"]
//$arParams["DETAIL_URL_FOR_JS"]
//$arParams["BACK_URL"]
//$arParams["CELL_COUNT"]
//$arParams["WORD_LENGTH"]
// Main
$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" ? "Y" : "N");
$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "N" ? "N" : "Y");

// SEARCH
$arParams["FONT_MAX"] = (empty($arParams["TAGS_FONT_MAX"]) ? "35" : $arParams["TAGS_FONT_MAX"]);
$arParams["FONT_MIN"] = (empty($arParams["TAGS_FONT_MIN"]) ? "10" : $arParams["TAGS_FONT_MIN"]);
$arParams["COLOR_NEW"] = (empty($arParams["TAGS_COLOR_NEW"]) ? "3E74E6" : $arParams["TAGS_COLOR_NEW"]);
$arParams["COLOR_OLD"] = (empty($arParams["TAGS_COLOR_OLD"]) ? "C0C0C0" : $arParams["TAGS_COLOR_OLD"]);
$arParams["PAGE_RESULT_COUNT"] = (intVal($arParams["ELEMENTS_PAGE_ELEMENTS"]) > 0 ? $arParams["ELEMENTS_PAGE_ELEMENTS"] : 50);

//MAIN PAGE
$arParams["SHOW_LINK_ON_MAIN_PAGE"] = (isset($arParams["SHOW_LINK_ON_MAIN_PAGE"]) ? $arParams["SHOW_LINK_ON_MAIN_PAGE"] : array("id", "rating", "comments", "shows"));
$arParams["SHOW_ON_MAIN_PAGE"] = (in_array($arParams["SHOW_ON_MAIN_PAGE"], array("rating", "id", "comments", "shows")) ? $arParams["SHOW_ON_MAIN_PAGE"] : "none");
$arParams["SHOW_ON_MAIN_PAGE_POSITION"] = ($arParams["SHOW_ON_MAIN_PAGE_POSITION"] == "right" ? "right" : "left");
$arParams["SHOW_ON_MAIN_PAGE_TYPE"] = (in_array($arParams["SHOW_ON_MAIN_PAGE_TYPE"], array("count", "time")) ? $arParams["SHOW_ON_MAIN_PAGE_TYPE"] : "none");
$arParams["SHOW_ON_MAIN_PAGE_COUNT"] = intVal($arParams["SHOW_ON_MAIN_PAGE_COUNT"]);
$arParams["SHOW_PHOTO_ON_DETAIL_LIST"] = (in_array($arParams["SHOW_PHOTO_ON_DETAIL_LIST"], array("none", "show_period", "show_count", "show_time")) ? $arParams["SHOW_PHOTO_ON_DETAIL_LIST"] : "show_count");
$arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"] = intVal($arParams["SHOW_PHOTO_ON_DETAIL_LIST_COUNT"]);

// VOTE
$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" ? "Y" : "N");
if ($arParams["USE_RATING"] == "Y")
{
	$arParams["VOTE_NAMES"] = (isset($arParams["VOTE_NAMES"]) ? $arParams["VOTE_NAMES"] : array("1", "2", "3", "4", "5"));
	$arParams["MAX_VOTE"] = (isset($arParams["MAX_VOTE"]) ? $arParams["MAX_VOTE"] : 5);
	$arParams["DISPLAY_AS_RATING"] = trim($arParams["DISPLAY_AS_RATING"]);
}
else
{
	$arParams["SHOW_RATING"] = "N";
}

$arResult["PAGE_NAME"] = $componentPage;

$oPhoto = new CPGalleryInterface(
	array("IBlockID" => $arParams["IBLOCK_ID"]),
	array(
		"cache_time" => $arParams["CACHE_TIME"],
		"set_404" => $arParams["SET_STATUS_404"]
		)
	);
$arParams["PERMISSION"] = $oPhoto->User["Permission"];
/********************************************************************
				/Input params
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));
$this->IncludeComponentTemplate($componentPage);
?>