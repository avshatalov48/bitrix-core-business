<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var CDataBase $DB
 * @var CBitrixComponent $this
 *
 */
if (!IsModuleInstalled("blog")):
//	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
elseif (!CModule::IncludeModule("socialnetwork")) :
//	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
endif;
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

$arParams["FILTER"] = (is_array($arParams["FILTER"]) ? $arParams["FILTER"] : array());
$arParams["FILTER_NAME"] = (empty($arParams["FILTER_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/i", $arParams["FILTER_NAME"]) ? "" : $arParams["FILTER_NAME"]);
$arParams["YEAR"] = intval($arParams["YEAR"]);
$arParams["MONTH"] = intval($arParams["MONTH"]);
$arParams["DAY"] = intval($arParams["DAY"]);
$arParams["CATEGORY_ID"] = intval($arParams["CATEGORY_ID"]);
$arParams["~BLOG_GROUP_ID"] = (!empty($arParams["GROUP_ID"]) ? $arParams["GROUP_ID"] : $arParams["BLOG_GROUP_ID"]);
$arParams["~BLOG_GROUP_ID"] = (is_array($arParams["~BLOG_GROUP_ID"]) ? $arParams["~BLOG_GROUP_ID"] : array($arParams["~BLOG_GROUP_ID"]));
$arParams["BLOG_GROUP_ID"] = array();
foreach($arParams["~BLOG_GROUP_ID"] as $k => $val)
{
	$val = intval($val);
	if ($val > 0)
		$arParams["BLOG_GROUP_ID"][] = intval($val);
}
sort($arParams["BLOG_GROUP_ID"], SORT_NUMERIC);
$arParams["USER_ID"] = intval($arParams["USER_ID"]);
$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);

$arParams["SORT"] = (is_array($arParams["SORT"]) ? $arParams["SORT"] : array());
if (empty($arParams["SORT"])){
	$arParams["SORT_BY1"] = (empty($arParams["SORT_BY1"]) ? "DATE_PUBLISH" : $arParams["SORT_BY1"]);
	$arParams["SORT"][$arParams["SORT_BY1"]] = (empty($arParams["SORT_ORDER1"]) ? "DESC" : $arParams["SORT_ORDER1"]);
	$arParams["SORT_BY2"] = (empty($arParams["SORT_BY2"]) ? "ID" : $arParams["SORT_BY2"]);
	$arParams["SORT"][$arParams["SORT_BY2"]] = (empty($arParams["SORT_ORDER2"]) ? "DESC" : $arParams["SORT_ORDER2"]);
}
/************** Page settings **************************************/
$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");
$arParams["PAGE_SETTINGS"] = (!empty($arParams["PAGE_SETTINGS"]) ? $arParams["PAGE_SETTINGS"] :
	array("bDescPageNumbering" => true, "nPageSize" => $arParams["MESSAGE_COUNT"], "bShowAll" => false));
$arParams["PAGE_SETTINGS"]["bShowAll"] = false;
if (isset($arParams["PAGE_SETTINGS"]["bDescPageNumbering"]) && is_string($arParams["PAGE_SETTINGS"]["bDescPageNumbering"]))
	$arParams["PAGE_SETTINGS"]["bDescPageNumbering"] = ($arParams["PAGE_SETTINGS"]["bDescPageNumbering"] === "false" ? false :
		($arParams["PAGE_SETTINGS"]["bDescPageNumbering"] === "true" ? true : false));
/************** URL ************************************************/
$arParams["BLOG_VAR"] = (empty($arParams["BLOG_VAR"]) ? "blog" : $arParams["BLOG_VAR"]);
$arParams["PAGE_VAR"] = (empty($arParams["PAGE_VAR"]) ? "page" : $arParams["PAGE_VAR"]);
$arParams["USER_VAR"] = (empty($arParams["USER_VAR"]) ? "user_id" : $arParams["USER_VAR"]);
$arParams["POST_VAR"] = (empty($arParams["POST_VAR"]) ? "id" : $arParams["POST_VAR"]);
foreach(array(
	"BLOG" => $arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#",
	"BLOG_CATEGORY" => $arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#",
	"BLOG_POSTS" => $arParams["PAGE_VAR"]."=blog_posts&".$arParams["USER_VAR"]."=#user_id#",
	"POST" => $arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#",
	"POST_EDIT" => $arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#",
	"USER" => $arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#",
	"SMILE" => ""
) as $url => $val)
{
	$arParams["~PATH_TO_".$url] = (empty($arParams["PATH_TO_".$url]) ? $APPLICATION->GetCurPage()."?".$val : $arParams["PATH_TO_".$url]);
	$arParams["PATH_TO_".$url] = htmlspecialcharsbx($arParams["~PATH_TO_".$url]);
}
/************** ADDITIONAL *****************************************/
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["NAME_TEMPLATE"] = (empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat() : $arParams["NAME_TEMPLATE"]);
$arParams["SHOW_LOGIN"] = ($arParams["SHOW_LOGIN"] == "Y");
$arParams["AVATAR_SIZE"] = (isset($_REQUEST["avatar_size"]) && intval($_REQUEST["avatar_size"]) > 0 ? intval($_REQUEST["avatar_size"]) : intval($arParams["AVATAR_SIZE"]));
$arParams["SET_TITLE"] = "N";
CRatingsComponentsMain::GetShowRating($arParams); // $arParams["SHOW_RATING"]
$arParams["MESSAGE_LENGTH"] = ($arParams["MESSAGE_LENGTH"] > 0 ? $arParams["MESSAGE_LENGTH"] : 90);
/************** CACHE **********************************************/
if (!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600*24*7;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["CACHE_TAGS"] = (!empty($arParams["CACHE_TAGS"]) ? $arParams["CACHE_TAGS"] : array());
//$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
global $CACHE_MANAGER, $USER_FIELD_MANAGER;

$cache_path = CComponentEngine::MakeComponentPath("bitrix:socialnetwork.blog.blog");
$bGroupMode = ($arParams["SOCNET_GROUP_ID"] > 0);
$feature = "blog";
$user_id = intval($USER->GetID());

$arResult["ERROR_MESSAGE"] = Array();
$arResult["OK_MESSAGE"] = Array();
if (!(
	($bGroupMode && CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], $feature))
	|| CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], $feature)
))
{
	$arResult["ERROR_MESSAGE"][] = array(
		"id" => "SONET_MODULE_NOT_AVAIBLE",
		"text" => GetMessage("BLOG_SONET_MODULE_NOT_AVAIBLE"));
}
else if (!($arParams["USER_ID"] > 0 || $bGroupMode))
{
	$arResult["ERROR_MESSAGE"][] = array(
		"id" => "NO_BLOG",
		"text" => GetMessage("BLOG_BLOG_BLOG_NO_BLOG"));
	CHTTP::SetStatus("404 Not Found");
}
else
{
	$arResult["perms"] = BLOG_PERMS_DENY;
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
	if($bGroupMode)
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W")
			$arResult["perms"] = BLOG_PERMS_FULL;
		elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
			$arResult["perms"] = BLOG_PERMS_MODERATE;
		elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post", $bCurrentUserIsAdmin))
			$arResult["perms"] = BLOG_PERMS_WRITE;
		elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "premoderate_post", $bCurrentUserIsAdmin))
			$arResult["perms"] = BLOG_PERMS_PREMODERATE;
		elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post", $bCurrentUserIsAdmin))
			$arResult["perms"] = BLOG_PERMS_READ;
	}
	else
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "view_post", $bCurrentUserIsAdmin))
		{
			$arResult["perms"] = BLOG_PERMS_READ;
		}
	}
	if($arResult["perms"] < BLOG_PERMS_READ)
	{
		$arResult["MESSAGE"][] = array(
			"id" => "ACCESS_DENIED",
			"text" => GetMessage("BLOG_BLOG_BLOG_FRIENDS_ONLY")
		);
	}
}

if (!empty($arResult["ERROR_MESSAGE"]))
{
	$this->IncludeComponentTemplate();
	return;
}

/********************************************************************
				Actions
********************************************************************/
//Message delete
if ((intval($_GET["del_id"]) > 0 || intval($_GET["hide_id"]) > 0) && CModule::IncludeModule("blog"))
{
	$del_id = intval($_GET["del_id"]); $hide_id = intval($_GET["hide_id"]);
	if ($_GET["success"] == "Y")
	{
		$arResult["OK_MESSAGE"][] = (!!$_GET["del_id"] ?
			array(
				"id" => "deleted".$_GET["del_id"],
				"text" => GetMessage("BLOG_BLOG_BLOG_MES_DELED")) :
			array(
				"id" => "hided".$_GET["hide_id"],
				"text" => GetMessage("BLOG_BLOG_BLOG_MES_HIDED")));
	}
	elseif (!check_bitrix_sessid())
	{
		$arResult["ERROR_MESSAGE"][] = array(
			"id" => "sessid",
			"text" => GetMessage("BLOG_BLOG_SESSID_WRONG"));
	}
	elseif ($del_id > 0)
	{
		$bCanDelete = ($arResult["perms"] >= BLOG_PERMS_FULL ? true : (!$bGroupMode && CBlogPost::GetSocNetPostPerms($del_id, true) >= BLOG_PERMS_FULL));
		if (!$bCanDelete)
		{
			$arResult["ERROR_MESSAGE"][] = array(
				"id" => "ACCESS_DENIED",
				"text" => GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS"));
		}
		elseif(CBlogPost::GetByID($del_id))
		{
			CBlogPost::DeleteLog($del_id);
			if (CBlogPost::Delete($del_id))
			{
				if ($bGroupMode)
					CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);
				LocalRedirect($APPLICATION->GetCurPageParam("del_id=".$del_id."&success=Y", Array("del_id", "hide_id", "sessid", "success")));
			}
			else
			{
				$arResult["ERROR_MESSAGE"][] = array(
					"id" => "DELETE",
					"text" => GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR")
				);
			}
		}
	}
	else
	{
		$bCanHide = ($arResult["perms"] >= BLOG_PERMS_MODERATE ? true : (!$bGroupMode && CBlogPost::GetSocNetPostPerms($hide_id, true) >= BLOG_PERMS_MODERATE));
		if (!$bCanHide)
		{
			$arResult["ERROR_MESSAGE"][] = array(
				"id" => "ACCESS_DENIED",
				"text" => GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS"));
		}
		elseif(CBlogPost::GetByID($hide_id))
		{
			if(CBlogPost::Update($hide_id, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
			{
				CBlogPost::DeleteLog($hide_id);
				LocalRedirect($APPLICATION->GetCurPageParam("hide_id=".$hide_id."&success=Y", Array("del_id", "hide_id", "sessid", "success")));
			}
			else
			{
				$arResult["ERROR_MESSAGE"][] = array(
					"id" => "HIDE",
					"text" => GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR")
				);
			}
		}
	}
}
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["urlToPosts"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_BLOG_POSTS"], array("user_id" => $arParams["USER_ID"]));

CpageOption::SetOptionString("main", "nav_page_in_session", "N");
$arFilter = (($arParams["FILTER_NAME"] !== "" && is_array($GLOBALS[$arParams["FILTER_NAME"]])) ? $GLOBALS[$arParams["FILTER_NAME"]] : array());
$cache = new CPHPCache();

$arFilter["PUBLISH_STATUS"] = "P";
$arFilter["BLOG_USE_SOCNET"] = "Y";
$arFilter["BLOG_GROUP_ID"] = $arParams["BLOG_GROUP_ID"];
$arFilter["GROUP_SITE_ID"] = SITE_ID;
$arFilter["SOCNET_SITE_ID"] = array(SITE_ID, false);
if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
	$arFilter["SOCNET_SITE_ID"] = SITE_ID;

if($arParams["USER_ID"] > 0 && $arParams["USER_ID"] == $user_id) // in own profile
{
	if($arParams["4ME"] == "ALL")
	{
		$arFilter["FOR_USER"] = $user_id;
		$arFilter["FOR_USER_TYPE"] = "ALL";
	}
	elseif($arParams["4ME"] == "Y")
	{
		$arFilter["FOR_USER"] = $user_id;
		$arFilter["!AUTHOR_ID"] = $user_id;
		$arFilter["FOR_USER_TYPE"] = "SELF";
	}
	elseif($arParams["4ME"] == "DR")
	{
		$arFilter["FOR_USER"] = $user_id;
		$arFilter["!AUTHOR_ID"] = $user_id;
		$arFilter["FOR_USER_TYPE"] = "DR";
	}
	else
		$arFilter["FOR_USER"] = $user_id;
}
elseif($arParams["USER_ID"] > 0 && $arParams["USER_ID"] != $user_id) // in other user profile
{
	$arFilter["AUTHOR_ID"] = $arParams["USER_ID"];
	$arFilter["FOR_USER"] = IntVal($user_id);
}
elseif($arParams["SOCNET_GROUP_ID"]) // socialnetwork group
{
	$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
}

if ($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
{
	$from = mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]);
	$to = mktime(0, 0, 0, $arParams["MONTH"], ($arParams["DAY"]+1), $arParams["YEAR"]);
	if($to > ($t = time()+CTimeZone::GetOffset()))
		$to = $t;
	$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
	$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
}
elseif($arParams["YEAR"] && $arParams["MONTH"])
{
	$from = mktime(0, 0, 0, $arParams["MONTH"], 1, $arParams["YEAR"]);
	$to = mktime(0, 0, 0, ($arParams["MONTH"]+1), 1, $arParams["YEAR"]);
	if($to > ($t = time()+CTimeZone::GetOffset()))
		$to = $t;
	$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
	$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
}
elseif($arParams["YEAR"])
{
	$from = mktime(0, 0, 0, 1, 1, $arParams["YEAR"]);
	$to = mktime(0, 0, 0, 1, 1, ($arParams["YEAR"]+1));
	if($to > ($t = time()+CTimeZone::GetOffset()))
		$to = $t;
	$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
	$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
}
else
{
	$dfc = true;
	$arFilter["<=DATE_PUBLISH"] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
}

if($arParams["CATEGORY_ID"])
{
	$arFilter["CATEGORY_ID_F"] = $arParams["CATEGORY_ID"];
}
$arPostUserFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST");
if (isset($arPostUserFields['UF_IMPRTANT_DATE_END']))
{
	$arFilter[] = array(
		"LOGIC" => "OR",
		"=UF_IMPRTANT_DATE_END" => false,
		">=UF_IMPRTANT_DATE_END" => ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL"),
	);
}
$arResult["NAV_RESULT"] = "";
$arResult["NAV_STRING"] = "";
$arResult["POST"] = Array();
$arResult["IDS"] = Array();
$arResult["userCache"] = array();

$arParams["FILTER"] = array_merge($arParams["FILTER"], $arFilter);
$PAGEN=($GLOBALS["PAGEN_".($GLOBALS["NavNum"]+1)] || $arParams["PAGE_SETTINGS"]["iNumPage"]);
$arCacheID = array(
	"filter" => array_merge($arParams["FILTER"], ($dfc === true ? array("<=DATE_PUBLISH" => "") : array())),
	$arParams["SORT"],
	array_intersect_key($arParams["PAGE_SETTINGS"], array("bDescPageNumbering" => false, "nPageSize" => 10)),
	CTimeZone::GetOffset(),
	$USER->GetID()
);
$cache_id = "blog_blog_".md5(serialize($arCacheID));
/********************************************************************
				Actions
********************************************************************/
if(
	is_array($_REQUEST["options"])
	&& !empty($_REQUEST["options"])
	&& check_bitrix_sessid()
	&& $USER->IsAuthorized()
	&& CModule::IncludeModule("blog")
)
{
	foreach($_REQUEST["options"] as $val)
	{
		CBlogUserOptions::SetOption($val["post_id"], $val["name"], $val["value"], $USER->GetID());
	}
	if (defined("BX_COMP_MANAGED_CACHE"))
	{
		$CACHE_MANAGER->ClearByTag($val["name"].$val["post_id"]);
		$CACHE_MANAGER->ClearByTag($val["name"].$val["post_id"]."_".$USER->GetID());
		$CACHE_MANAGER->ClearByTag($val["name"]."_USER_".$USER->GetID());
	}
	else
	{
		$obCache = new CPHPCache;
		$obCache->Clean($cache_id, $cache_path);
	}

	$db_events = GetModuleEvents("socialnetwork", "OnAfterCBlogUserOptionsSet");
	while ($arEvent = $db_events->Fetch())
	{
		ExecuteModuleEventEx($arEvent, Array($_REQUEST["options"], $cache_id, $cache_path));
	}
}
/********************************************************************
				/Actions
********************************************************************/

if ($_REQUEST["return"] == "users")
{
	include(str_replace(array("\\", "//"), "/", __DIR__."/")."users.php");
}
elseif ($PAGEN == null && $arParams["CACHE_TIME"] > 0) // cache only the first page
{
	if ($cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$arRes = $cache->GetVars();
		$arResult["POST"] = $arRes["POST"];
		$arResult["IDS"] = $arRes["IDS"];
		$arResult["userCache"] = $arRes["userCache"];
		$arResult["NAV_RESULT"] = $arRes["NAV_RESULT"];
		$arResult["NAV_STRING"] = $arRes["NAV_STRING"];
		$arResult["USER"] = $arRes["USER"];
	}
}
if (empty($arResult["NAV_RESULT"]) && CModule::IncludeModule("blog"))
{
	$dbPost = CBlogPost::GetList(
		$arParams["SORT"],
		$arParams["FILTER"],
		false,
		$arParams["PAGE_SETTINGS"],
		array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID",
			"DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_PUBLISH", "PUBLISH_STATUS",
			"ENABLE_COMMENTS", "VIEWS", "NUM_COMMENTS", "CATEGORY_ID", "CODE", "BLOG_OWNER_ID", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "MICRO")
	);
	$arResult["NAV_RESULT"] = $dbPost;
	$arResult["NAV_STRING"] = $dbPost->GetPageNavString(GetMessage("MESSAGE_COUNT"), $arParams["NAV_TEMPLATE"]);
	$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
	$p->MaxStringLen = 20;
	$patt = array(); $repl = array();
	$patt[] = "#\[img\](.+?)\[/img\]#i"; $repl[] = "";
	$patt[] = "/\[document id=\d+([^\]]*)\]/is"; $repl[] = "";
	$patt[] = "#\[url(.+?)\](.*?)\[/url\]#is"; $repl[] = "\\2";
	$patt[] = "#\[video(.+?)\](.+?)\[/video\]#i"; $repl[] = "";
	$patt[] = "#^(.+?)<cut[\s]*(/>|>).*?$#is"; $repl[] = "\\1";
	$patt[] = "#^(.+?)\[cut[\s]*(/\]|\]).*?$#is"; $repl[] = "\\1";
	$patt[] = "#(\[|<)(/?)(b|u|i|list|code|quote|url|img|color|font|right|left|center|justify|/*)(.*?)(\]|>)#is"; $repl[] = " ";
	$patt[] = "#\s+#"; $repl[] = " ";
	$allow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N",
		"IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N",
		"LIST" => "N", "SMILES" => "N", "NL2BR" => "N");

	$arPostsAll = array();
	$arIdToGet = array();

	while ($arPost = $dbPost->GetNext())
	{
		if(IntVal($arPost["AUTHOR_ID"]) > 0)
		{
			$arIdToGet[] = $arPost["AUTHOR_ID"];
		}

		$arPostsAll[] = $arPost;
	}

	if (!empty($arIdToGet))
	{
		$arResult["userCache"] = CBlogUser::GetUserInfoArray($arIdToGet, $arParams["PATH_TO_USER"],array("AVATAR_SIZE" => $arParams["AVATAR_SIZE"]));

		foreach($arResult["userCache"] as $userId => $arUserCache)
		{
			$arUserCache["~AUTHOR_NAME"] = CUser::FormatName(
				$arParams["NAME_TEMPLATE"],
				array(
					"LAST_NAME" => $arUserCache["~LAST_NAME"],
					"NAME" => $arUserCache["~NAME"],
					"SECOND_NAME" => $arUserCache["~SECOND_NAME"],
					"LOGIN" => $arUserCache["~LOGIN"]
				),
				$arParams["SHOW_LOGIN"],
				false
			);
			$arUserCache["AUTHOR_NAME"] = htmlspecialcharsbx($arUserCache["~AUTHOR_NAME"]);
			$arResult["userCache"][$userId] = $arUserCache;
		}
	}

	$db_user = CUser::GetById($GLOBALS["USER"]->GetId());
	$arResult["USER"] = $db_user->Fetch();

	foreach ($arPostsAll as $arPost)
	{
		$text = preg_replace($patt, $repl, $arPost["~DETAIL_TEXT"]);
		$text = TruncateText($text, $arParams["MESSAGE_LENGTH"]);
		$text = CBlogTools::DeleteDoubleBR($p->convert($text, true, false, $allow));
		$arPost["~CLEAR_TEXT"] = $text;
		$arPost["CLEAR_TEXT"] = $p->wrap_long_words($text);

		$arPost["perms"] = $arResult["perms"];
		if(!$bGroupMode && $arParams["USER_ID"] == $user_id && (empty($arParams["4ME"]) || $arPost["AUTHOR_ID"] == $user_id))
			$arPost["perms"] = BLOG_PERMS_FULL;
		elseif((!$bGroupMode && $arParams["USER_ID"] != $user_id) || strlen($arParams["4ME"]) > 0)
			$arPost["perms"] = CBlogPost::GetSocNetPostPerms($arPost["ID"], true);

		$arUser = $arResult["userCache"][$arPost["AUTHOR_ID"]];

		$arPost["~AUTHOR_NAME"] = $arUser["~AUTHOR_NAME"];
		$arPost["AUTHOR_NAME"] = $arUser["AUTHOR_NAME"];
		$arPost["AUTHOR_AVATAR"] = $arUser["PERSONAL_PHOTO_resized"];

		$arPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_POST"],
			array("post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["BLOG_OWNER_ID"]));
		$arPost["urlToPosts"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_BLOG_POSTS"],
			array("user_id" => $arPost["BLOG_OWNER_ID"]));
		$arPost["urlToPostsImportant"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_POST_IMPORTANT"],
			array("user_id" => $arPost["BLOG_OWNER_ID"]));

		$arPost["urlToUser"] = $arPost["urlToAuthor"] = $arUser["url"];
		if($arPost["perms"] >= BLOG_PERMS_WRITE)
		{
			if($arPost["perms"] >= BLOG_PERMS_FULL || ($arPost["perms"] >= BLOG_PERMS_WRITE && $arPost["AUTHOR_ID"] == $user_id))
				$arPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("post_id"=>$arPost["ID"], "user_id" => $arPost["AUTHOR_ID"]));
			if($arPost["perms"] >= BLOG_PERMS_MODERATE)
				$arPost["urlToHide"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("hide_id=".$arPost["ID"]."&".bitrix_sessid_get(), Array("del_id", "sessid", "success", "hide_id")));
			if($arPost["perms"] >= BLOG_PERMS_FULL)
				$arPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arPost["ID"]."&".bitrix_sessid_get(), Array("del_id", "sessid", "success", "hide_id")));
		}
		$arResult["POST"][$arPost["ID"]] = $arPost;
		$arResult["IDS"][] = $arPost["ID"];
	}
/*******************************************************************
				CACHE
*******************************************************************/
	if ($PAGEN == null && $arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

		if (
			!empty($arResult["POST"])
			&& defined("BX_COMP_MANAGED_CACHE")
		)
		{
			$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
			foreach ($arParams["FILTER"] as $key => $val)
			{
				if (strpos($key, "POST_PARAM_") !== false)
				{
					$tag = substr($key, (strpos($key, "POST_PARAM_") + 11));
					foreach ($arResult["POST"] as $post_id => $arPost)
					{
						if ($val["USER_ID"] > 0)
						{
							$GLOBALS["CACHE_MANAGER"]->RegisterTag($tag.$post_id."_".$val["USER_ID"]);
							$GLOBALS["CACHE_MANAGER"]->RegisterTag($tag."_USER_".$val["USER_ID"]);
						}
						else
						{
							$GLOBALS["CACHE_MANAGER"]->RegisterTag($tag.$post_id);
						}
					}
				}
			}
			$GLOBALS["CACHE_MANAGER"]->EndTagCache();
		}

		$cache->EndDataCache(array(
			"POST" => $arResult["POST"],
			"IDS" => $arResult["IDS"],
			"userCache" => $arResult["userCache"],
			"NAV_RESULT" => $arResult["NAV_RESULT"],
			"NAV_STRING" => $arResult["NAV_STRING"],
			"USER" => $arResult["USER"]
		));
	}
/*******************************************************************
				/ CACHE
*******************************************************************/
}

if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
	$arResult["RATING"] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);
$this->IncludeComponentTemplate();
?>