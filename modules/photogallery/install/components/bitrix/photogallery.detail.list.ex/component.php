<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")) // !important
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock")) // !important
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));

if (!isset($arParams["ACTION_URL"]))
	$arParams["ACTION_URL"] = htmlspecialcharsback(POST_FORM_ACTION_URI);
else
	$arParams["CHECK_ACTION_URL"] = "Y"; // Used to rerequest ACTION_URL from POST_FORM_ACTION_URI in GET request for make action_url work correct with no mod_rewrite installed

$arParams["ACTION_URL"] = CHTTP::urlDeleteParams($arParams["ACTION_URL"], array("clear_cache", "bitrix_include_areas", "bitrix_show_mode", "back_url_admin", "bx_photo_ajax", "change_view_mode_data", "sessid", "load_comments"));

/********************************************************************
				For custom components
********************************************************************/
$arParams["PROPERTY_CODE"] = (!is_array($arParams["PROPERTY_CODE"]) ? array() : $arParams["PROPERTY_CODE"]);
$arParams["ELEMENT_SORT_FIELD"] = strtoupper($arParams["ELEMENT_SORT_FIELD"]);
$arParams["ELEMENT_SORT_FIELD1"] = strtoupper($arParams["ELEMENT_SORT_FIELD1"]);
$arParams["COMMENTS_TYPE"] = strtoupper($arParams["COMMENTS_TYPE"]);
$arParams["IS_SOCNET"] = ($arParams["IS_SOCNET"] == "Y" ? "Y" : "N");

$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" || $arParams["SHOW_RATING"] == "Y") ? "Y" : "N";
$arParams["SHOW_TAGS"] = $arParams["SHOW_TAGS"] != "N" ? "Y" : "N";
$arParams["MODERATION"] = $arParams["MODERATION"] == "Y" ? "Y" : "N";

if (!isset($arParams["DISPLAY_AS_RATING"]) || !$arParams["DISPLAY_AS_RATING"])
	$arParams["DISPLAY_AS_RATING"] = 'rating_main';

//if ($arParams["SHOW_RATING"] == "Y")
{
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_count";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_rating";
}
if ($arParams["SHOW_COMMENTS"] == "Y")
{
	if ($arParams["COMMENTS_TYPE"] == "FORUM")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["COMMENTS_TYPE"] == "BLOG")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_BLOG_COMMENTS_CNT";
}
if (!empty($arParams["ELEMENT_SORT_FIELD"]))
{
	if ($arParams["ELEMENT_SORT_FIELD"] == "SHOWS")
		$arParams["ELEMENT_SORT_FIELD"] = "SHOW_COUNTER";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "RATING")
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_rating";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM")
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG")
		$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_BLOG_COMMENTS_CNT";
}
if (!empty($arParams["ELEMENT_SORT_FIELD1"]))
{
	if ($arParams["ELEMENT_SORT_FIELD1"] == "SHOWS")
		$arParams["ELEMENT_SORT_FIELD1"] = "SHOW_COUNTER";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "RATING")
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_rating";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM")
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG")
		$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_BLOG_COMMENTS_CNT";
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

if($_SERVER["REQUEST_METHOD"] == "POST")
	$ELEMENT_ID = $_POST["ELEMENT_ID"];
elseif(isset($_GET["ELEMENT_ID"]))
	$ELEMENT_ID = $_GET["ELEMENT_ID"];

$arParams["SELECTED_ELEMENT"] = ($ELEMENT_ID == 'page' || $ELEMENT_ID == 'first' || $ELEMENT_ID == 'last') ? $ELEMENT_ID : false;
$arParams["ELEMENT_ID"] = intVal($ELEMENT_ID > 0 ? $ELEMENT_ID : $arParams["ELEMENT_ID"]);

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
$arParams["MAX_SHOWED_PHOTOS"] = intVal($arParams["MAX_SHOWED_PHOTOS"]) <= 0 ? 800 : $arParams["MAX_SHOWED_PHOTOS"];

//
$arParams["CURRENT_ELEMENT_ID"] = intVal($arParams["CURRENT_ELEMENT_ID"]);

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
		// TODO: Warning here $arParams[strToUpper($URL)."_URL"] - can be array
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
// User settings
$arParams["~JSID"] = "bxph_list".$arParams['IBLOCK_ID'];
$arParams["USER_SETTINGS"] = CUserOptions::GetOption('photogallery', $arParams["~JSID"]);

if (!isset($GLOBALS['bxph_list_id']))
	$GLOBALS['bxph_list_id'] = 0;
$GLOBALS['bxph_list_id'] = intVal($GLOBALS['bxph_list_id']);

if (isset($_REQUEST["UCID"]) && substr($_REQUEST["UCID"], 0, strlen("bxfg_ucid_from_req_")) == "bxfg_ucid_from_req_")
{
	// include_subsection - used to include subsections by GET-params
	if ($_REQUEST["include_subsection"] === 'Y' || $arParams["INCLUDE_SUBSECTIONS"] !== "N")
		$arParams["INCLUDE_SUBSECTIONS"] = "Y";
	$arParams["~UNIQUE_COMPONENT_ID"] = preg_replace("/[^a-z0-9\_]+/is" , "", $_REQUEST["UCID"]);
}

if (!isset($arParams["~UNIQUE_COMPONENT_ID"]))
	$arParams["~UNIQUE_COMPONENT_ID"] = "bxph_list_".$GLOBALS['bxph_list_id'];

$GLOBALS['bxph_list_id']++;

// Used to exit from component when several components on page placed and we do some ajax action
if (isset($_REQUEST["UCID"]) && $_REQUEST["UCID"] != $arParams["~UNIQUE_COMPONENT_ID"])
	return;

if (isset($_REQUEST["UCID"]) && $_REQUEST["UCID"] == $arParams["~UNIQUE_COMPONENT_ID"])
{
	// Used to restore correct navNum for component
	foreach($_REQUEST as $key => $var)
	{
		if (preg_match("/PAGEN_\d+/i", $key))
		{
			$GLOBALS['NavNum'] = intVal(substr($key, strlen('PAGEN_'))) - 1;
			break;
		}
	}
}

$arParams["COMMENTS_COUNT"] = $arParams["COMMENTS_COUNT"] > 0 ? $arParams["COMMENTS_COUNT"] : 5;
$arParams["USE_COMMENTS"] = $arParams["USE_COMMENTS"] == "N" ? "N" : "Y";

if (
	$arParams["COMMENTS_TYPE"] == "FORUM"
	&& $arParams["IS_SOCNET"] == "Y"
)
{
	$cache = new CPHPCache;
	$cache_id = serialize(
		array(
			"TYPE" => $arParams["COMMENTS_TYPE"],
			"ELEMENT_ID" => $arParams["CURRENT_ELEMENT_ID"],
			"USER_ALIAS" => $arParams["USER_ALIAS"]
		)
	);

	if (
		$arParams["CACHE_TIME"] > 0
		&& $cache->InitCache(3600*24, $cache_id, $cache_path)
	)
	{
		$res = $cache->GetVars();
		if (intval($res["FORUM_ID"]) > 0)
		{
			$arParams["FORUM_ID"] = $res["FORUM_ID"];
		}
	}
	elseif (CModule::IncludeModule("iblock"))
	{
		//SELECT
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"PROPERTY_FORUM_TOPIC_ID",
		);

		//WHERE
		$arFilter = array(
			"ID" => $arParams["CURRENT_ELEMENT_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);

		//EXECUTE
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		if ($obElement = $rsElement->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			if (
				intval($arElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]) > 0
				&& CModule::IncludeModule("forum")
			)
			{
				if ($arForumTopic = CForumTopic::GetByID($arElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]))
				{
					$arParams["FORUM_ID"] = $arForumTopic["FORUM_ID"];
				}
			}
		}

		$cache->StartDataCache(3600*24, $cache_id, $cache_path);
		$cache->EndDataCache(array("FORUM_ID" => $arParams["FORUM_ID"]));
	}
}

if ($arParams["USE_COMMENTS"] == "Y" && $arParams["COMMENTS_TYPE"] == "FORUM" && !$arParams["FORUM_ID"])
{
	$arParams["USE_COMMENTS"] = "N";
	$arParams["SHOW_COMMENTS"] = "N";
}

if ($arParams["USE_COMMENTS"] == "Y")
{
	if (!isset($arParams["SHOW_COMMENTS"]))
		$arParams["SHOW_COMMENTS"] = "Y";
	$arParams["COMMENTS_PERM_VIEW"] = "Y";
	$arParams["COMMENTS_PERM_ADD"] = "Y";

	if ($arParams["COMMENTS_TYPE"] == "FORUM" && CModule::IncludeModule("forum"))
	{
		$forumPerm = ForumCurrUserPermissions($arParams["FORUM_ID"]);
		$arParams["COMMENTS_PERM_VIEW"] = $forumPerm >= "E" ? "Y" : "N";
		$arParams["COMMENTS_PERM_ADD"] = $forumPerm >= "I" ? "Y" : "N";
	}
	elseif (CModule::IncludeModule("blog"))
	{
		$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
		if(IntVal($arBlog["ID"]) > 0)
		{
			$blogComPerm = CBlog::GetBlogUserCommentPerms(IntVal($arBlog["ID"]), $USER->GetId());
			$arParams["COMMENTS_PERM_VIEW"] = $blogComPerm >= "I" ? "Y" : "N";
			$arParams["COMMENTS_PERM_ADD"] = $blogComPerm >= "P" ? "Y" : "N";
		}
	}

	if ($arParams["COMMENTS_PERM_VIEW"] == "N")
	{
		$arParams["USE_COMMENTS"] = "N";
		$arParams["SHOW_COMMENTS"] = "N";
	}
}

$arParams["SHOW_LOGIN"] = $arParams["SHOW_LOGIN"] == "N" ? "N" : "Y";
if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

//if (strlen($arParams["PATH_TO_USER"]) <= 0)
//	$arParams["PATH_TO_USER"] = '/company/personal/user/#USER_ID#/';

if (!is_array($arParams["USER_SETTINGS"]))
	$arParams["USER_SETTINGS"] = array();

$arResult["MORE_PHOTO_NAV"] = $arParams["MORE_PHOTO_NAV"] != "N" ? "Y" : "N";

$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] == "Y" ? "Y" : "N");
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");

$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
$arParams["PAGE_ELEMENTS"] = ($arParams["PAGE_ELEMENTS"] > 0 ? $arParams["PAGE_ELEMENTS"] : 50);
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

	if ($res > 400 || $arResult["SECTION"]["ACTIVE"] == "N")
	{
		return ShowError(GetMessage("ALBUM_NOT_FOUND_ERROR"));
	}
	elseif ($res == 301)
	{
		// $url = CComponentEngine::MakePathFromTemplate(
			// $arParams["~SECTION_URL"],
			// array("USER_ALIAS" => $arGallery["CODE"], "SECTION_ID" => $arParams["SECTION_ID"]));
		//if (!$url)
			return ShowError(GetMessage("ALBUM_NOT_FOUND_ERROR"));
		//return LocalRedirect($url, false, "301 Moved Permanently");
	}
	elseif (!$oPhoto->CheckPermission($arParams["PERMISSION"], $arResult["SECTION"]))
	{
		if (!$oPhoto->IsPassFormDisplayed($arResult["SECTION"]["ID"]))
			return ShowError(GetMessage("ALBUM_NOT_FOUND_ERROR"));
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
if (isset($_REQUEST["photo_list_action"]) && $_REQUEST["photo_list_action"] != "")
	include(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/action.php"));
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
	foreach ($arParams["PICTURES_INFO"] as $key => $val)
	{
		if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
			$arParams["PICTURES"][$val["code"]] = array(
				"size" => $arParams["PICTURES_INFO"][$key]["size"],
				"quality" => $arParams["PICTURES_INFO"][$key]["quality"],
				"title" => $arParams["PICTURES_INFO"][$key]["title"]
			);
	}

	if (empty($arParams["PICTURES_SIGHT"]) && !empty($arParams["PICTURES"]))
	{
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
	}
	elseif ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail")
	{
		$arParams["PICTURES_SIGHT"] = substr($arParams["PICTURES_SIGHT"], 5);
	}
}

if ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail")
	$arParams["PICTURES_SIGHT"] = (in_array($arParams["PICTURES_SIGHT"], array_keys($arParams["PICTURES"])) ? $arParams["PICTURES_SIGHT"] : "standart");
if ($arParams["THUMBNAIL_SIZE"] > 0)
	$arParams["PICTURES"]["standart"] = array("size" => $arParams["THUMBNAIL_SIZE"]);

//PROPERTIES
if (!in_array(strToUpper($arParams["PICTURES_SIGHT"]), array("DETAIL", "PREVIEW", "STANDART")))
	$arParams["PROPERTY_CODE"][] = "PROPERTY_".strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE";

//PAGENAVIGATION
$arNavParams = false;
$arNavigation = false;
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$arNavParams = array(
		"nPageSize" => $arParams["PAGE_ELEMENTS"],
		"bDescPageNumbering" => false,
		"bShowAll" => false
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}
// ACCESS
$arResult["USER_HAVE_ACCESS"] = "Y";
if ($arParams["PERMISSION"] < "U" && $arParams["USE_PERMISSIONS"] == "Y")
{
	$res = array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["GROUP_PERMISSIONS"]);
	$arResult["USER_HAVE_ACCESS"] = (empty($res) ? "N" : "Y");
}

if ($arParams["DRAG_SORT"] !== "N")
{
	$arParams["DRAG_SORT"] = ((!$arParams["ELEMENT_SORT_FIELD"] || $arParams["ELEMENT_SORT_FIELD"] == "SORT" || $arParams["ELEMENT_SORT_FIELD"] == "ID")) ? "Y" : "N";

	if ($arParams["DRAG_SORT"] == "Y" && empty($arParams["ELEMENT_SORT_FIELD1"]))
	{
		$arParams["ELEMENT_SORT_FIELD"] = "SORT";
		$arParams["ELEMENT_SORT_ORDER"] = "ASC";
		$arParams["ELEMENT_SORT_FIELD1"] = "ID";
		$arParams["ELEMENT_SORT_ORDER1"] = "ASC";
	}
}

if ($arParams["DRAG_SORT"] == "Y" && $arParams["PERMISSION"] < "U")
	$arParams["DRAG_SORT"] = "N";

//SORT
$arSort = array();
if (!empty($arParams["ELEMENT_SORT_FIELD"]))
{
	$arSort[$arParams["ELEMENT_SORT_FIELD"]] = $arParams["ELEMENT_SORT_ORDER"];
	$arParams["ELEMENT_SELECT_FIELDS"][] = $arParams["ELEMENT_SORT_FIELD"];
}

if (!empty($arParams["ELEMENT_SORT_FIELD1"]) && !array_key_exists($arParams["ELEMENT_SORT_FIELD1"], $arSort))
{
	$arSort[$arParams["ELEMENT_SORT_FIELD1"]] = $arParams["ELEMENT_SORT_ORDER1"];
	$arParams["ELEMENT_SELECT_FIELDS"][] = $arParams["ELEMENT_SORT_FIELD1"];
}

if (!array_key_exists("ID", $arSort))
	$arSort["ID"] = "ASC";

//SELECT
$arSelect = array(
	"ID",
	"CODE",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"NAME",
	"ACTIVE",
	"DETAIL_PICTURE",
	"PREVIEW_PICTURE",
	"PREVIEW_TEXT",
	"DETAIL_TEXT",
	"DATE_CREATE",
	"CREATED_BY",
	"SHOW_COUNTER",
	"SORT",
	"PROPERTY_*"
);

if ($arParams["SHOW_TAGS"])
	$arSelect[] = "TAGS";

foreach ($arParams["ELEMENT_SELECT_FIELDS"] as $val)
{
	$val = strtoupper($val);
	if (strpos($val, "PROPERTY_") !== false && !in_array($val, $arParams["PROPERTY_CODE"]))
		$arParams["PROPERTY_CODE"][] = $val;
	elseif (strpos($val, "PROPERTY_") === false && !in_array($val, $arSelect))
		$arSelect[] = $val;
}

//$arSelect = array_keys(array_flip(array_diff($arSelect, array_keys($arSort))));
//WHERE
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"CHECK_PERMISSIONS" => "Y",
	 array(
		"LOGIC" => "OR",
		"SECTION_ACTIVE" => "Y",
		"SECTION_ID" => "0"
	 )
);

if ($arParams["PERMISSION"] < "U")
	$arFilter["ACTIVE"] = "Y";

// Apply filter only for first loading in the list, but load all photos in the popup
if ($arParams['SHOWN_PHOTOS'])
{
	$arShown = array();
	foreach($arParams['SHOWN_PHOTOS'] as $id)
	{
		if ($id > 0)
			$arShown[] = $id;
	}

	if (count($arShown) > 0)
		$arParams['SHOWN_PHOTOS'] = $arShown;
	else
		return ShowError(GetMessage("PHOTOS_NOT_FOUND_ERROR"));

	if ($_REQUEST["return_array"] != "Y")
		$arFilter["ID"] = $arParams['SHOWN_PHOTOS'];
	$arResult["MIN_ID"] = $arParams['SHOWN_PHOTOS'][0];
}

$maxWidth = 1;
$maxHeight = 1;
$arElements = array();
$arElementsJS = array();
// PASSWORDS
if ($arParams["SECTION_ID"] > 0)
{
	$arFilter["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	if ($arParams["INCLUDE_SUBSECTIONS"] == 'Y')
		$arFilter["INCLUDE_SUBSECTIONS"] = 'Y';
}
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
	$db_res = CIBlockElement::GetList(array("ID" => "DESC"), $arFilter, false, array("nTopCount" => $arParams["ELEMENTS_LAST_COUNT"]), array("ID"));
	$iLastID = 0;

	// WTF?
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

if ($arNavParams && ($arParams["ELEMENT_ID"] > 0 || $arParams["SELECTED_ELEMENT"]))
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

if ($arParams["SELECTED_ELEMENT"])
{
	if ($arParams["SELECTED_ELEMENT"] == 'first')
		$arNavParams["iNumPage"] = 1;
	elseif($arParams["SELECTED_ELEMENT"] == 'last')
		$arNavParams["iNumPage"] = intVal($_REQUEST['last_page']);
	elseif($arParams["SELECTED_ELEMENT"] == 'page')
		$arNavParams["iNumPage"] = intVal($_REQUEST['page_num']);

	$arParams["PAGE_NAVIGATION_WINDOW"] = $arNavParams["iNumPage"];
}

$arParams["FILTER"] = $arFilter;
$arParams["SORTING"] = $arSort;

// EXECUTE
$cache_id = "detail_list_ex_".serialize(array(
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
	$arResult = array_merge($arResult, $cache->GetVars());

// In cache not found
if (!is_array($arResult["ELEMENTS_LIST"]) || empty($arResult["ELEMENTS_LIST"]))
{
	CModule::IncludeModule("iblock"); CModule::IncludeModule("photogallery");

	$bParseTags = false;
	if ($arParams["SHOW_TAGS"])
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
		$arResult["NAV_RESULT"] = $rsElements;
		$arGalleries = array();
		$arSections = array();

		$strFileId = ""; // Contains file's ids - which collected and selected in one query
		$arPicturesIndex = array();
		$arThumbsIndex = array();

		$arUsers = array();

		// Starting index of image on page
		$index = $rsElements->NavPageSize * ($rsElements->NavPageNomer - 1);
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

			if (empty($arSections[$arElement["IBLOCK_SECTION_ID"]])) // Get Section Info
			{
				$db_res = CIBlockSection::GetList(array(), array("IBLOCK_ID" => $arElement["IBLOCK_ID"],	"ID" => $arElement["IBLOCK_SECTION_ID"]), false, array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME", "UF_PASSWORD", "ACTIVE"));

				$res = $oPhoto->GetSection($arElement["IBLOCK_SECTION_ID"], $arSections[$arElement["IBLOCK_SECTION_ID"]]);
				//if ($db_res && $res = $db_res->Fetch())
				//	$arSections[$arElement["IBLOCK_SECTION_ID"]] = $res;
			}

			if (!$oPhoto->CheckPermission($arParams["PERMISSION"], $arSections[$arElement["IBLOCK_SECTION_ID"]], false))
				continue;

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
						array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME"));
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
							array("USER_ALIAS" => $res["CODE"],"user_alias" => $res["CODE"], "USER_ID" => $res["CREATED_BY"], "user_id" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"], "group_id" => $res["SOCNET_GROUP_ID"]));
						$res["URL"] = htmlspecialcharsbx($res["~URL"]);
						$arGalleries[$arElement["IBLOCK_SECTION_ID"]] = $res;
					}
				}
				$arGallery = $arElement["GALLERY"] = $arGalleries[$arElement["IBLOCK_SECTION_ID"]];
			}

			// USER
			if (!isset($arUsers[$arElement['CREATED_BY']]))
			{
				$dbUser = CUser::GetByID($arElement['CREATED_BY']);
				$arUsers[$arElement['CREATED_BY']] = $dbUser->Fetch();
			}
			//$arElement["~USER"] = $arUsers[$arElement['CREATED_BY']];

			// Thumbnail
			$strFileId .= ','.intVal($arElement["PREVIEW_PICTURE"]);
			$arThumbsIndex[$arElement["PREVIEW_PICTURE"]] = $arElement["ID"];

			// Real
			if ($arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"])
			{
				$strFileId .= ','.intVal($arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);
				$arPicturesIndex[$arElement["PROPERTIES"]["REAL_PICTURE"]["VALUE"]] = $arElement["ID"];
			}
			elseif($arElement["PREVIEW_PICTURE"])
			{
				$strFileId .= ','.$arElement["DETAIL_PICTURE"];
				$arPicturesIndex[$arElement["DETAIL_PICTURE"]] = $arElement["ID"];
			}

			//URL
			$arElement["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], array("USER_ALIAS" => $arGallery["CODE"], "SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $arElement["ID"], "USER_ID" => $arGallery["CREATED_BY"], "GROUP_ID" => $arGallery["SOCNET_GROUP_ID"], "user_alias" => $arGallery["CODE"], "section_id" => $arElement["IBLOCK_SECTION_ID"], "element_id" => $arElement["ID"], "user_id" => $arGallery["CREATED_BY"], "group_id" => $arGallery["SOCNET_GROUP_ID"]));
			$arElement["URL"] = htmlspecialcharsbx($arElement["~URL"]);

			//TAGS
			$arElement["TAGS_LIST"] = array();
			if ($arParams["SHOW_TAGS"] && !empty($arElement["TAGS"]) && $bParseTags)
			{
				$ar = tags_prepare($arElement["~TAGS"], SITE_ID);
				if (!empty($ar))
				{
					foreach ($ar as $name => $tags)
					{
						$arr = array(
							"TAG_NAME" => $tags,
							"TAG_URL" => CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array())
						);
						$arr["TAG_URL"] .= (strpos($arr["TAG_URL"], "?") === false ? "?" : "&")."tags=".$tags;
						$arElement["TAGS_LIST"][] = $arr;
					}
				}
			}

			if ($arElement["PREVIEW_TEXT"] == "" && $arElement["NAME"] != "" && !preg_match('/\d{3,}/', $arElement["NAME"]))
			{
				$arElement["~NAME"] = preg_replace(array('/\.jpg/i','/\.jpeg/i','/\.gif/i','/\.png/i','/\.bmp/i'), '', $arElement["~NAME"]);
				$arElement["~PREVIEW_TEXT"] = $arElement["~NAME"];
				$arElement["PREVIEW_TEXT"] = htmlspecialcharsbx($arElement["~PREVIEW_TEXT"]);
			}

			unset($arElement["DETAIL_PICTURE"]);

			if (is_array($arUsers[$arElement['CREATED_BY']]))
				$authorName = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUsers[$arElement['CREATED_BY']], $arParams["SHOW_LOGIN"] != 'N');
			else
				$authorName = GetMessage('UNKNOWN_AUTHOR');

			$arElements[$arElement["ID"]] = $arElement;
			$arElementsJS[$arElement["ID"]] = array(
				"id" => intVal($arElement["ID"]),
				"active" => $arElement["ACTIVE"] == "Y" ? "Y" : "N",
				"title" => $arElement["NAME"],
				"album_id" => $arElement["IBLOCK_SECTION_ID"],
				"album_name" => $arSections[$arElement["IBLOCK_SECTION_ID"]]["NAME"],
				"gallery_id" => $arGallery["CODE"],
				"description" => $arElement["~PREVIEW_TEXT"],
				"shows" => $arElement["SHOW_COUNTER"],
				"index" => $index,
				"author_id" => $arElement['CREATED_BY'],
				"date" => FormatDate('x', MakeTimeStamp($arElement["DATE_CREATE"], CSite::GetDateFormat()) - CTimeZone::GetOffset()),
				"author_name" => $authorName,
				"comments" => $arParams["USE_COMMENTS"] == "Y" ? intVal($arParams["COMMENTS_TYPE"] != "BLOG" ? $arElement["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] : $arElement["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"]) : "",
				"detail_url" => $arElement["~URL"]
			);

			if ($arParams['DRAG_SORT'] == "Y")
				$arElementsJS[$arElement["ID"]]['sort'] = $arElement["SORT"];

			if ($arParams["SHOW_TAGS"])
			{
				$arElementsJS[$arElement["ID"]]['tags'] = $arElement["TAGS"];
				if ($bParseTags)
					$arElementsJS[$arElement["ID"]]['tags_array'] = $arElement["TAGS_LIST"];
			}
			$index++;
		}

		$strFileId = trim($strFileId, " ,");
		if (strLen($strFileId) > 0)
		{
			$rsFile = CFile::GetList(array(), array("@ID" => $strFileId));
			$upload = COption::GetOptionString("main", "upload_dir", "upload");
			while ($obFile = $rsFile->Fetch())
			{
				$fileId = $obFile['ID'];
				$obFile["SRC"] = CFile::GetFileSRC($obFile);

				$io = CBXVirtualIo::GetInstance();
				$fName = $io->ExtractNameFromPath($obFile["SRC"]);
				$fPath = $io->ExtractPathFromPath($obFile["SRC"]);
				$obFile["SRC"] = $fPath.'/'.strtr($fName, array('%' => '%25', '#' => '%23', '?' => '%3F'));

				if($ind = $arThumbsIndex[$fileId])
				{
					$arElements[$ind]["PREVIEW_PICTURE"] = array(
						"SRC" => $obFile["SRC"],
						"WIDTH" => $obFile["WIDTH"],
						"HEIGHT" => $obFile["HEIGHT"]
					);
					$arElementsJS[$ind]["thumb_src"] = $obFile["SRC"];
					$arElementsJS[$ind]["thumb_width"] = $obFile["WIDTH"];
					$arElementsJS[$ind]["thumb_height"] = $obFile["HEIGHT"];
				}
				elseif ($ind = $arPicturesIndex[$fileId])
				{
					$arElements[$ind]["BIG_PICTURE"] = array(
						"SRC" => $obFile["SRC"],
						"WIDTH" => $obFile["WIDTH"],
						"HEIGHT" => $obFile["HEIGHT"]
					);
					$arElementsJS[$ind]["src"] = $obFile["SRC"];
					$arElementsJS[$ind]["width"] = $obFile["WIDTH"];
					$arElementsJS[$ind]["height"] = $obFile["HEIGHT"];
				}
			}
		}
	}

	$arResult["ELEMENTS_LIST"] = $arElements;
	$arResult["ELEMENTS_LIST_JS"] = $arElementsJS;
	$arResult["ELEMENTS_CNT"] = count($arElements);

	$arResult["ALL_ELEMENTS_CNT"] = $arResult["SECTION"]["ELEMENTS_CNT"];

	if ($arParams['INCLUDE_SUBSECTIONS'] == 'N')
		$arResult["ALL_ELEMENTS_CNT"] = $arResult["SECTION"]["SECTION_ELEMENTS_CNT"];
	if (!$arResult["ALL_ELEMENTS_CNT"])
		$arResult["ALL_ELEMENTS_CNT"] = $arResult["NAV_RESULT"]->NavRecordCount;

	if ($arParams['MAX_SHOWED_PHOTOS'] > 0 && $arResult["ALL_ELEMENTS_CNT"] > $arParams['MAX_SHOWED_PHOTOS'])
		$arResult["ALL_ELEMENTS_CNT"] = $arParams['MAX_SHOWED_PHOTOS'];

	if ($arResult["ALL_ELEMENTS_CNT"] <= $arResult["NAV_RESULT"]->NavPageSize)
		$arResult["MORE_PHOTO_NAV"] = "N";

	$arResult["NAV_RESULT_NavPageSize"] = $arResult["NAV_RESULT"]->NavPageSize;
	$arResult["NAV_RESULT_NavNum"] = $arResult["NAV_RESULT"]->NavNum;
	$arResult["NAV_RESULT_NavPageNomer"] = $arResult["NAV_RESULT"]->NavPageNomer;
	$arResult["NAV_RESULT_NavPageCount"] = $arResult["NAV_RESULT"]->NavPageCount;

	if ($arParams["RELOAD_ITEMS_ONLOAD"] == "Y" && count($arResult["ELEMENTS_LIST"]) > 0)
	{
		$cur = current($arResult["ELEMENTS_LIST"]);
		$arResult["MIN_ID"] = $cur['ID'];
	}

	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(
			array(
				"ELEMENTS_CNT" => $arResult["ELEMENTS_CNT"],
				"ALL_ELEMENTS_CNT" => $arResult["ALL_ELEMENTS_CNT"],
				"ELEMENTS_LIST" => $arResult["ELEMENTS_LIST"],
				"ELEMENTS_LIST_JS" => $arResult["ELEMENTS_LIST_JS"],
				"MORE_PHOTO_NAV" => $arResult["MORE_PHOTO_NAV"],
				"NAV_RESULT_NavPageSize" => $arResult["NAV_RESULT_NavPageSize"],
				"NAV_RESULT_NavNum" => $arResult["NAV_RESULT_NavNum"],
				"NAV_RESULT_NavPageNomer" => $arResult["NAV_RESULT_NavPageNomer"],
				"NAV_RESULT_NavPageCount" => $arResult["NAV_RESULT_NavPageCount"],
				"MIN_ID" => $arResult["MIN_ID"]
			)
		);
	}
}
else
{
	$GLOBALS['NavNum'] = intVal($GLOBALS['NavNum']) + 1;
}

if ($arResult["ELEMENTS_CNT"] <= 1)
	$arParams['DRAG_SORT'] = "N";

/************** URL ************************************************/
/********************************************************************
				/Data
********************************************************************/
if ($arParams["JUST_RETURN_DATA_JS"] == "Y")
	return $arResult["ELEMENTS_LIST_JS"];

unset($arParams["PICTURES"]["standart"]);

$arParams["DETAIL_ITEM_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], array("USER_ID" => $arGallery["CREATED_BY"], "user_id" => $arGallery["CREATED_BY"], "GROUP_ID" => $arGallery["SOCNET_GROUP_ID"], "group_id" => $arGallery["SOCNET_GROUP_ID"]));
$arParams["ALBUM_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ID" => $arGallery["CREATED_BY"], "user_id" => $arGallery["CREATED_BY"], "GROUP_ID" => $arGallery["SOCNET_GROUP_ID"], "group_id" => $arGallery["SOCNET_GROUP_ID"]));

$arResult["CHECK_PARAMS"] = array(
	"CUR_USER_ID" => $USER->GetId(),
	"USE_COMMENTS" => $arParams["USE_COMMENTS"],
	"PERMISSION" => $arParams["PERMISSION"],
	"USE_RATING" => $arParams["USE_RATING"],
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"READ_ONLY" => $arParams["READ_ONLY"]
);

$arResult["REQ_PARAMS"] = array(
	"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"PATH_TO_USER" => $arParams["PATH_TO_USER"],
	"MAX_VOTE" => $arParams["MAX_VOTE"],
	"VOTE_NAMES" => $arParams["VOTE_NAMES"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"]
);
$arResult["SIGN"] = CPGalleryInterface::GetSign($arResult["CHECK_PARAMS"]);

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("P_LIST_PHOTO"));
/************** Returns ********************************************/
if ($arParams["RETURN_FORMAT"] == "LIST")
{
	return $arResult["ELEMENTS_LIST"];
}
else
{
	$res = reset($arResult["ELEMENTS_LIST"]);
	return $res["ID"];
}
/********************************************************************
				/Standart
********************************************************************/
?>