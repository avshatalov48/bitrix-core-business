<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_PER_PAGE"] = IntVal($arParams["MESSAGE_PER_PAGE"])>0 ? IntVal($arParams["MESSAGE_PER_PAGE"]): 15;
$arParams["PREVIEW_WIDTH"] = IntVal($arParams["PREVIEW_WIDTH"])>0 ? IntVal($arParams["PREVIEW_WIDTH"]): 100;
$arParams["PREVIEW_HEIGHT"] = IntVal($arParams["PREVIEW_HEIGHT"])>0 ? IntVal($arParams["PREVIEW_HEIGHT"]): 100;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["MESSAGE_LENGTH"] = (IntVal($arParams["MESSAGE_LENGTH"])>0)?$arParams["MESSAGE_LENGTH"]:100;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["USE_SOCNET"] = ($arParams["USE_SOCNET"] == "Y") ? "Y" : "N";
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
{
	if(strlen($arParams["PATH_TO_BLOG"]) > 0)
		$arParams["PATH_TO_BLOG_CATEGORY"] = $arParams["PATH_TO_BLOG"]."?category=#category_id#";
	if(strlen($arParams["PATH_TO_GROUP_BLOG"]) > 0)
		$arParams["PATH_TO_GROUP_BLOG_CATEGORY"] = $arParams["PATH_TO_GROUP_BLOG"]."?category=#category_id#";

	if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
		$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#&category=#category_id#");
}

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["SHOW_LOGIN"] = $arParams["SHOW_LOGIN"] != "N" ? "Y" : "N";
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

if(!is_array($arParams["POST_PROPERTY_LIST"]))
{
	if(CModule::IncludeModule("webdav") || CModule::IncludeModule("disk"))
		$arParams["POST_PROPERTY_LIST"] = array("UF_BLOG_POST_FILE");
	else
		$arParams["POST_PROPERTY_LIST"] = array("UF_BLOG_POST_DOC");
}
else
{
	if(CModule::IncludeModule("webdav") || CModule::IncludeModule("disk"))
		$arParams["POST_PROPERTY_LIST"][] = "UF_BLOG_POST_FILE";
	else
		$arParams["POST_PROPERTY_LIST"][] = "UF_BLOG_POST_DOC";
}

$UserGroupID = Array(1);
if($USER->IsAuthorized())
	$UserGroupID[] = 2;

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BNPL_TITLE"));

$user_id = IntVal($USER->GetID());
$cache = new CPHPCache;
$cache_id = "blog_last_messages_".serialize($arParams)."_".serialize($UserGroupID)."_".$USER->IsAdmin()."_".CDBResult::NavStringForCache($arParams["BLOG_COUNT"]);
if(($tzOffset = CTimeZone::GetOffset()) <> 0)
	$cache_id .= "_".$tzOffset;
if($arParams["USE_SOCNET"] == "Y")
	$cache_id .= "_".$user_id;
$cache_path = "/".SITE_ID."/blog/last_messages_list/";

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
			"<=DATE_PUBLISH" => ConvertTimeStamp(time()+$tzOffset, "FULL", false),
			"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"BLOG_ACTIVE" => "Y",
			"BLOG_GROUP_SITE_ID" => SITE_ID,
			">PERMS" => BLOG_PERMS_DENY
		);
	if(strlen($arParams["BLOG_URL"]) > 0)
		$arFilter["BLOG_URL"] = $arParams["BLOG_URL"];
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["BLOG_GROUP_ID"] = $arParams["GROUP_ID"];
	if($USER->IsAdmin())
		unset($arFilter[">PERMS"]);

	$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "BLOG_ACTIVE", "BLOG_URL", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "AUTHOR_SECOND_NAME", "BLOG_USER_ALIAS", "BLOG_OWNER_ID", "VIEWS", "NUM_COMMENTS", "ATTACH_IMG", "BLOG_SOCNET_GROUP_ID", "DETAIL_TEXT_TYPE", "CATEGORY_ID", "CODE");

	if(CModule::IncludeModule("socialnetwork") && $arParams["USE_SOCNET"] == "Y")
	{
		unset($arFilter[">PERMS"]);
		$arSelectedFields[] = "SOCNET_BLOG_READ";
		$arFilter["BLOG_USE_SOCNET"] = "Y";
		$arFilter["FOR_USER"] = $user_id;
	}

	$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

	if($arParams["MESSAGE_PER_PAGE"])
		$COUNT = array("nPageSize"=>$arParams["MESSAGE_PER_PAGE"], "bShowAll" => false);
	else
		$COUNT = false;

	$arResult = Array();
	$dbPosts = CBlogPost::GetList(
		$SORT,
		$arFilter,
		false,
		$COUNT,
		$arSelectedFields
	);
	$arResult["NAV_STRING"] = $dbPosts->GetPageNavString(GetMessage("B_B_GR_TITLE"), $arParams["NAV_TEMPLATE"], false, $component);
	$arResult["IDS"] = Array();

	$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
	$arParserParams = Array(
		"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
		"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
	);
	
//	get all Users for new posts
	$blogUser = new \Bitrix\Blog\BlogUser($arParams["CACHE_TIME"]);
	$blogUser->setBlogId($arPost["BLOG_ID"]);
	$blogUsers = $blogUser->getUsers(\Bitrix\Blog\BlogUser::getPostAuthorsIdsByDbFilter($arFilter));

	while ($arPost = $dbPosts->GetNext())
	{
		$arResult["IDS"][] = $arPost["ID"];
		$arTmp = $arPost;

		if($arTmp["AUTHOR_ID"] == $arTmp["BLOG_OWNER_ID"])
		{
			$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
		}
		else
		{
			$arOwnerBlog = CBlog::GetByOwnerID($arTmp["AUTHOR_ID"], $arParams["GROUP_ID"]);
			if(!empty($arOwnerBlog))
				$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"], "user_id" => $arOwnerBlog["OWNER_ID"]));
			else
				$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
		}

		if(IntVal($arPost["BLOG_SOCNET_GROUP_ID"]) > 0)
			$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "group_id" => $arPost["BLOG_SOCNET_GROUP_ID"]));
		else
			$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["BLOG_OWNER_ID"]));

		$arTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));

		$arTmp["AuthorName"] = CBlogUser::GetUserName($arPost["BLOG_USER_ALIAS"], $arPost["AUTHOR_NAME"], $arPost["AUTHOR_LAST_NAME"], $arPost["AUTHOR_LOGIN"]);

		$arImages = array();
		$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "IS_COMMENT" => "N"));
		while ($arImage = $res->Fetch())
		{
			$arImages[$arImage['ID']] = $arImage['FILE_ID'];
			$arTmp["arImages"][$arImage['ID']] = Array(
								"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=70&height=70&type=square",
								"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=1000&height=1000"
							);
		}

		if (preg_match("/(\[CUT\])/i",$arTmp['DETAIL_TEXT']) || preg_match("/(<CUT>)/i",$arTmp['DETAIL_TEXT']))
			$arTmp["CUT"] = "Y";

		if($arTmp["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
		{
			$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
			if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
				$arAllow["VIDEO"] = "N";
			$arTmp["TEXT_FORMATED"] = $p->convert($arTmp["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
		}
		else
		{
			$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
			if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
				$arAllow["VIDEO"] = "N";
			$arTmp["TEXT_FORMATED"] = $p->convert($arTmp["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
		}
		$arTmp["IMAGES"] = $arImages;
		if(!empty($p->showedImages))
		{
			foreach($p->showedImages as $val)
			{
				if(!empty($arTmp["arImages"][$val]))
					unset($arTmp["arImages"][$val]);
			}
		}

		$arTmp["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTmp["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
		$arTmp["DATE_PUBLISH_DATE"] = ConvertDateTime($arTmp["DATE_PUBLISH"], FORMAT_DATE);
		$arTmp["DATE_PUBLISH_TIME"] = ConvertDateTime($arTmp["DATE_PUBLISH"], "HH:MI");
		$arTmp["DATE_PUBLISH_D"] = ConvertDateTime($arTmp["DATE_PUBLISH"], "DD");
		$arTmp["DATE_PUBLISH_M"] = ConvertDateTime($arTmp["DATE_PUBLISH"], "MM");
		$arTmp["DATE_PUBLISH_Y"] = ConvertDateTime($arTmp["DATE_PUBLISH"], "YYYY");

		if(strlen($arTmp["CATEGORY_ID"])>0)
		{
			$arCategory = explode(",",$arTmp["CATEGORY_ID"]);
			foreach($arCategory as $v)
			{
				if(IntVal($v)>0)
				{
					$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
					if(IntVal($arPost["BLOG_SOCNET_GROUP_ID"]) > 0)
						$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_CATEGORY"], array("blog" => $arTmp["BLOG_URL"], "category_id" => $v, "user_id" => $arPost["BLOG_OWNER_ID"], "group_id" => $arPost["BLOG_SOCNET_GROUP_ID"]));
					else
						$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arTmp["BLOG_URL"], "category_id" => $v, "user_id" => $arPost["BLOG_OWNER_ID"], "group_id" => $arPost["BLOG_SOCNET_GROUP_ID"]));
					$arTmp["CATEGORY"][] = $arCatTmp;
				}
			}
		}

		$arTmp["POST_PROPERTIES"] = array("SHOW" => "N");
		if (!empty($arParams["POST_PROPERTY_LIST"]))
		{
			$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arTmp["ID"], LANGUAGE_ID);

			if (count($arParams["POST_PROPERTY_LIST"]) > 0)
			{
				foreach ($arPostFields as $FIELD_NAME => $arPostField)
				{
					if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
						continue;
					$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
					$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
					$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
					$arTmp["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
				}
			}
			if (!empty($arTmp["POST_PROPERTIES"]["DATA"]))
				$arTmp["POST_PROPERTIES"]["SHOW"] = "Y";
			
			
		}
		$arTmp["BlogUser"]["AVATAR_file"] = $blogUsers[$arTmp["AUTHOR_ID"]]["BlogUser"]["AVATAR_file"];
		if($arTmp["BlogUser"]["AVATAR_file"] !== false)
		{
//			get only size for post
			$arTmp["BlogUser"]["Avatar_resized"] = $blogUsers[$arTmp["AUTHOR_ID"]]["BlogUser"]["Avatar_resized"]["100_100"];
			$arTmp["BlogUser"]["AVATAR_img"] = $blogUsers[$arTmp["AUTHOR_ID"]]["BlogUser"]["AVATAR_img"]["100_100"];
		}
		
		$arResult["POSTS"][] = $arTmp;
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}

if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
	$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);

$this->IncludeComponentTemplate();
?>