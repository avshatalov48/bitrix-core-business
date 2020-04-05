<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["COMMENT_COUNT"] = IntVal($arParams["COMMENT_COUNT"])>0 ? IntVal($arParams["COMMENT_COUNT"]): 6;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_CREATE");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["MESSAGE_LENGTH"] = (IntVal($arParams["MESSAGE_LENGTH"])>0)?$arParams["MESSAGE_LENGTH"]:100;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["USE_SOCNET"] = ($arParams["USE_SOCNET"] == "Y") ? "Y" : "N";
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["COMMENT_ID_VAR"])<=0)
	$arParams["COMMENT_ID_VAR"] = "commentId";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

if(is_numeric($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]))
{
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY"] = floatVal($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]);
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = "Y";
}

$UserGroupID = Array(1);
if($USER->IsAuthorized())
	$UserGroupID[] = 2;

$user_id = IntVal($USER->GetID());
$cache = new CPHPCache;
$cache_id = "blog_last_comments_".serialize($arParams)."_".serialize($UserGroupID)."_".$USER->IsAdmin();
if($arParams["USE_SOCNET"] == "Y")
	$cache_id .= "_".$user_id;
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
$cache_path = "/".SITE_ID."/blog/last_comments/";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;
	CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
	$cache->Output();
}
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	$arFilter = Array(
			"BLOG_ACTIVE" => "Y",
			"BLOG_GROUP_SITE_ID" => SITE_ID,
			">PERMS" => BLOG_PERMS_DENY,
			"BLOG_POST_PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"!BLOG_POST_MICRO" => "Y",
		);	
	if(strlen($arParams["BLOG_URL"]) > 0)
		$arFilter["BLOG_URL"] = $arParams["BLOG_URL"];
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["BLOG_GROUP_ID"] = $arParams["GROUP_ID"];
	if($USER->IsAdmin())
		unset($arFilter[">PERMS"]);

	$arSelectedFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "BLOG_URL", "DATE_CREATE", "BLOG_ACTIVE", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_OWNER_ID", "BLOG_SOCNET_GROUP_ID", "POST_CODE");

	if(CModule::IncludeModule("socialnetwork") && $arParams["USE_SOCNET"] == "Y")
	{
		unset($arFilter[">PERMS"]);
		unset($arFilter["!BLOG_POST_MICRO"]);
		if(IntVal($arParams["SOCNET_GROUP_ID"]) <= 0 && IntVal($arParams["USER_ID"]) <= 0)
		{
			$arFilter["FOR_USER"] = $user_id;
			$arFilter["BLOG_USE_SOCNET"] = "Y";
		}
		else
		{
			if(IntVal($arParams["USER_ID"]) > 0)
			{
				$arFilter["AUTHOR_ID"] = $arParams["USER_ID"];
				$arFilter["FOR_USER"] = $user_id;
			}
			elseif(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
			{
				$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
				$perms = BLOG_PERMS_DENY;
				if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("blog") >= "W")
					$perms = BLOG_PERMS_FULL;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
					$perms = BLOG_PERMS_WRITE;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post"))
					$perms = BLOG_PERMS_READ;
			}
		}
	}

	if($perms != BLOG_PERMS_DENY)
	{
		$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
		
		if($arParams["COMMENT_COUNT"]>0)
			$COUNT = Array("nTopCount" => $arParams["COMMENT_COUNT"]);
		else
			$COUNT = false;

		$arResult = Array();
		$dbComment = CBlogComment::GetList(
			$SORT,
			$arFilter,
			false,
			$COUNT,
			$arSelectedFields
		);

		$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
		$itemCnt = 0;
		
//		collect last comments authors
		$lastCommentsAuthorsIds = array();
		$dbLastComments = CBlogComment::GetList(
			$SORT,
			$arFilter,
			false,
			$COUNT,
			array('AUTHOR_ID', 'ID')
		);
		while($lastComment = $dbLastComments->Fetch())
			if($lastComment['AUTHOR_ID'])
				$lastCommentsAuthorsIds[$lastComment['AUTHOR_ID']] = $lastComment['AUTHOR_ID'];
		
		$blogUser = new \Bitrix\Blog\BlogUser($arParams["CACHE_TIME"]);
		$commentsUsers = $blogUser->getUsers($lastCommentsAuthorsIds);
		
		while ($arComment = $dbComment->GetNext())
		{
			$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N", "USER" => "N");
			$text = preg_replace("#\[img\](.+?)\[/img\]#is", "", $arComment["~POST_TEXT"]);
			$text = preg_replace("#\[user\](.+?)\[/user\]#is", "", $text);
			$text = preg_replace("#\[code\](.+?)\[/code\]#is", "", $text);
			$text = preg_replace("#\[quote\](.+?)\[/quote\]#is", "", $text);
			if($arResult["NO_URL_IN_COMMENTS"] == "L" || (IntVal($arComment["AUTHOR_ID"]) <= 0  && $arParams["NO_URL_IN_COMMENTS"] == "A"))
			{
				$arAllow["CUT_ANCHOR"] = "Y";
				$arAllow["ANCHOR"] = "Y";
			}
			elseif($arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && IntVal($arComment["AUTHOR_ID"]) > 0)
			{
				$authorityRatingId = CRatings::GetAuthorityRating();
				$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComment["AUTHOR_ID"]);
				if($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
				{
					$arAllow["ANCHOR"] = "Y";
					$arAllow["CUT_ANCHOR"] = "Y";
				}
			}
			else
			{
				$text = preg_replace("#\[url(.*?)\](.*?)\[/url\]#is", "\\2", $text);
			}

			$text = $p->convert($text, false, false, $arAllow);
			$text = preg_replace("#(\[|<)(/?)(b|u|i|list|code|quote|url|img|color|font|video|table|tr|td|align|user|/*)(.*?)(\]|>)#is", "", $text);
			$text = TruncateText($text, $arParams["MESSAGE_LENGTH"]);
			$arComment["TEXT_FORMATED"] = $text;
			
			if(IntVal($arComment["AUTHOR_ID"])>0)
			{
				if(empty($arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]))
				{
					$arUsrTmp = array();
					$arUsrTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["AUTHOR_ID"]));
					$arUsrTmp["Blog"] = CBlog::GetByOwnerID(IntVal($arComment["AUTHOR_ID"]), $arParams["GROUP_ID"]);
					if(!empty($arUsrTmp["Blog"]))
						$arUsrTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate(
							$arParams["PATH_TO_BLOG"],
							array("blog" => $arUsrTmp["Blog"]["URL"], "user_id" => $arComment["AUTHOR_ID"])
						);
					else
						$arUsrTmp["urlToBlog"] = $arUsrTmp["urlToAuthor"];
//
					$arResult["USER_CACHE"][$arComment["AUTHOR_ID"]] = $arUsrTmp;
				}
				$arComment["BlogUser"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"];
				$arComment["arUser"] = $commentsUsers[$arComment["AUTHOR_ID"]]["arUser"];
				$arComment["AuthorName"] = $commentsUsers[$arComment["AUTHOR_ID"]]["AUTHOR_NAME"];
				$arComment["AVATAR_file"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"]["AVATAR_file"];
				if ($arComment["AVATAR_file"] !== false)
					$arComment["AVATAR_img"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"]["AVATAR_img"]['30_30'];
//				from user cache
				$arComment["Blog"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["Blog"];
				$arComment["urlToAuthor"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["urlToAuthor"];
				$arComment["urlToBlog"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["urlToBlog"];
			}
			else
			{
				$arComment["AuthorName"]  = $arComment["AUTHOR_NAME"];
				$arComment["AuthorEmail"]  = $arComment["AUTHOR_EMAIL"];
			}
			
			if(IntVal($arComment["BLOG_SOCNET_GROUP_ID"]) > 0)
				$arComment["urlToComment"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_POST"], array("blog" => $arComment["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arComment["POST_ID"], $arComment["POST_CODE"], $arParams["ALLOW_POST_CODE"]), "group_id" => $arComment["BLOG_SOCNET_GROUP_ID"]));
			else
				$arComment["urlToComment"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arComment["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arComment["POST_ID"], $arComment["POST_CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arComment["BLOG_OWNER_ID"]));

			if(strpos($arComment["urlToComment"], "?") !== false)
				$arComment["urlToComment"] .= "&";
			else
				$arComment["urlToComment"] .= "?";
			$arComment["urlToComment"] .= $arParams["COMMENT_ID_VAR"]."=".$arComment["ID"]."#".$arComment["ID"];

			if(strlen($arComment["TITLE"])>0)
				$arComment["TitleFormated"] = $p->convert($arComment["~TITLE"], false);
			$arComment["DATE_CREATE_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arComment["DATE_CREATE"], CSite::GetDateFormat("FULL")));
			if($itemCnt==0)
				$arComment["FIRST"] = "Y";
				
			$itemCnt++;
			
			$arResult[] = $arComment;
		}
		
		unset($arResult["USER_CACHE"]);
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>