<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var CDataBase $DB
 */
if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
	return;
}

$arParams["IBLOCK_CATEGORIES"] = intval($arParams["IBLOCK_CATEGORIES"] ? $arParams["IBLOCK_CATEGORIES"] : $arParams["IBLOCK_CATOGORIES"]);
if($arParams["IBLOCK_CATEGORIES"]<=0)
{
	ShowError(GetMessage("IDEA_CATEGORY_IBLOCK_NOT_CHOSEN"));
	return;
}
if(empty($arParams["BLOG_URL"]))
{
	ShowError(GetMessage("BLOG_BLOG_BLOG_NO_BLOG"));
	CHTTP::SetStatus("404 Not Found");
	return;
}

//@::Idea
$arParams['RATING_TEMPLATE'] = ($arParams['RATING_TEMPLATE'] == "like" ? "like" : "standart");

$arResult["IDEA_MODERATOR"] = ((is_array($arParams["POST_BIND_USER"]) && array_intersect($USER->GetUserGroupArray(), $arParams["POST_BIND_USER"])) || $USER->IsAdmin());

//prepare ExtFilter - Parent ::@Idea
$arParams["EXT_FILTER"] = (is_array($arParams["EXT_FILTER"]) ? $arParams["EXT_FILTER"] : array());

if(array_key_exists("IDEA_PARENT_CATEGORY_CODE", $arParams["EXT_FILTER"]) && strlen($arParams["EXT_FILTER"]["IDEA_PARENT_CATEGORY_CODE"])>0)
{
	$arCategory = CIdeaManagment::getInstance()->Idea()->GetSubCategoryList($arParams["EXT_FILTER"]["IDEA_PARENT_CATEGORY_CODE"]);
	if(is_array($arCategory["ID"]) && !empty($arCategory["ID"]))
	{
		//UF value for filter
		$arParams["EXT_FILTER"][CIdeaManagment::UFCategroryCodeField] = $arCategory["CODE"];
	}
	else
	{
		CHTTP::SetStatus("404 Not Found");
		ShowError(GetMessage("IDEA_CATEGORY_NOT_EXISTS"));
		return;
	}
	unset($arParams["EXT_FILTER"]["IDEA_PARENT_CATEGORY_CODE"]);
}
elseif(array_key_exists("IDEA_CATEGORY_CODE", $arParams["EXT_FILTER"]) && strlen($arParams["EXT_FILTER"]["IDEA_CATEGORY_CODE"])>0)
	$arParams["EXT_FILTER"][CIdeaManagment::UFCategroryCodeField] = $arParams["EXT_FILTER"]["IDEA_CATEGORY_CODE"];
//end prepare ExtFilter
//prepare ExtFilter - Child|Total ::@Idea
if(array_key_exists("IDEA_STATUS", $arParams["EXT_FILTER"]) && strlen($arParams["EXT_FILTER"]["IDEA_STATUS"])>0)
{ 
	if($arStatusField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => CIdeaManagment::UFStatusField, "XML_ID" => $arParams["EXT_FILTER"]["IDEA_STATUS"]))->Fetch())
		//UF value for filter
		$arParams["EXT_FILTER"][CIdeaManagment::UFStatusField] = $arStatusField["ID"];
	else
	{
		CHTTP::SetStatus("404 Not Found");
		ShowError(GetMessage("IDEA_STATUS_NOT_EXISTS"));
		return;
	}
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (IntVal($arParams["YEAR"])>0 ? IntVal($arParams["YEAR"]) : false);
$arParams["MONTH"] = (IntVal($arParams["MONTH"])>0 ? IntVal($arParams["MONTH"]) : false);
$arParams["DAY"] = (IntVal($arParams["DAY"])>0 ? IntVal($arParams["DAY"]) : false);
$arParams["CATEGORY_ID"] = (IntVal($arParams["CATEGORY_ID"])>0 ? IntVal($arParams["CATEGORY_ID"]) : false);
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	$arParams["CACHE_TIME_LONG"] = intval($arParams["CACHE_TIME_LONG"]);
	if(IntVal($arParams["CACHE_TIME_LONG"]) <= 0 && IntVal($arParams["CACHE_TIME"]) > 0)
		$arParams["CACHE_TIME_LONG"] = $arParams["CACHE_TIME"];

}
else
{
	$arParams["CACHE_TIME"] = 0;
	$arParams["CACHE_TIME_LONG"] = 0;
}
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_BLOG_BLOG_TITLE"));

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
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

/********************************************************************
				Default params
********************************************************************/
$cache = new CPHPCache;
$cache_id = "blog_blog_".serialize(array($arParams["BLOG_URL"], $arParams["GROUP_ID"]));
$cache_path = "/".SITE_ID."/idea/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	$arResult["BLOG"] = $cache->GetVars();
if (empty($arResult["BLOG"]))
{
	$arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache($arResult["BLOG"]);
	endif;
}
if(empty($arResult["BLOG"]))
{
	ShowError(GetMessage("BLOG_BLOG_BLOG_NO_BLOG"));
	CHTTP::SetStatus("404 Not Found");
	return;
}
$arBlog = $arResult["BLOG"];

$tmpVal = COption::GetOptionInt("idea", "blog_group_id", false, SITE_ID);
if (
	intval($arBlog["GROUP_ID"]) > 0
	&& (
		!$tmpVal
		|| $tmpVal != intval($arBlog["GROUP_ID"])
	)
)
{
	COption::SetOptionInt("idea", "blog_group_id", $arBlog["GROUP_ID"], false, SITE_ID);
}

$arFilter = (is_string($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/i", $arParams["FILTER_NAME"]) ? $GLOBALS[$arParams["FILTER_NAME"]] : array());
$arFilter = (is_array($arFilter) ? $arFilter : array());
$arResult["ERROR_MESSAGE"] = Array();
$arResultNFCache = array("OK_MESSAGE" => array(), "ERROR_MESSAGE" => array());

$user_id = intval($USER->GetID());
$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arResult["BLOG"]["ID"], $user_id);
$arUserGroups = ($GLOBALS["USER"]->IsAuthorized() ? CBlogUser::GetUserGroups($user_id, $arParams["BLOG_URL"], "Y", BLOG_BY_USER_ID, "URL") : array(1));
sort($arUserGroups);
$cache_id = "blog_blog_message_".serialize( array(
		$arParams["BLOG_URL"],
		$arParams["RATING_TEMPLATE"],
		$arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
		$arParams["SORT_BY2"] => $arParams["SORT_ORDER2"],
		$arParams["IBLOCK_CATEGORIES"],
		$arParams["EXT_FILTER"],
		$arParams["PATH_TO_BLOG"],
		$arParams["POST_PROPERTY_LIST"],
		$arParams["DATE_TIME_FORMAT"],
		$arParams["NAV_TEMPLATE"],
		$arParams["GROUP_ID"],
		$arParams["NAME_TEMPLATE"],
		$arParams["SHOW_LOGIN"],
		$arParams["IMAGE_MAX_WIDTH"],
		$arParams["IMAGE_MAX_HEIGHT"],
		$arParams["ALLOW_POST_CODE"],
		$arParams["CATEGORY_ID"],
		CDBResult::NavStringForCache($arParams["MESSAGE_COUNT"]),
		$arUserGroups,
		$arResult["PostPerm"],
		$arResult["IDEA_MODERATOR"]
	));
if(!isset($_GET["PAGEN_1"]) || IntVal($_GET["PAGEN_1"])<1)
{
	$CACHE_TIME = $arParams["CACHE_TIME"];
	$cache_path = "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/";
}
else
{
	$CACHE_TIME = $arParams["CACHE_TIME_LONG"];
	$cache_path = "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/".IntVal($_GET["PAGEN_1"])."/";
}
/********************************************************************
				/Default params
********************************************************************/


/********************************************************************
				Actions
********************************************************************/
$postId = ($_GET["del_id"] > 0 ? $_GET["del_id"] : ($_GET["hide_id"] > 0 ? $_GET["hide_id"] : $_GET["show_id"]));
if ($arResult["IDEA_MODERATOR"] && $postId > 0)
{
	if ($_GET["success"] == "Y")
	{
		$arResultNFCache["OK_MESSAGE"][] = ($_GET["del_id"] > 0 ?
			GetMessage("BLOG_BLOG_BLOG_MES_DELED") : ( $_GET["hide_id"] > 0 ?
			GetMessage("BLOG_BLOG_BLOG_MES_HIDED") :
			GetMessage("IDEA_BLOG_BLOG_MES_SHOWED")
		));
	}
	else if (!check_bitrix_sessid())
		$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_SESSID_WRONG");
	else if (CBlogPost::GetByID($postId))
	{
		if ($_GET["del_id"] > 0)
		{
			if (!CBlogPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id))
				$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS");
			else if (!CBlogPost::Delete($postId))
				$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR");
			else
				CIdeaManagment::getInstance()->Notification(array("TYPE" => "IDEA", "ID" => $postId))->getSonetNotify()->Remove();
		}
		else if ($arResult["PostPerm"] < BLOG_PERMS_MODERATE)
			$arResultNFCache["ERROR_MESSAGE"][] = ($_GET["hide_id"] > 0 ? GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS") : GetMessage("IDEA_BLOG_BLOG_MES_SHOW_NO_RIGHTS"));
		elseif (!CBlogPost::Update($postId, Array("PUBLISH_STATUS" => ($_GET["hide_id"] > 0 ? BLOG_PUBLISH_STATUS_READY : BLOG_PUBLISH_STATUS_PUBLISH))))
			$arResultNFCache["ERROR_MESSAGE"][] = ($_GET["hide_id"] > 0 ? GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR") : GetMessage("BLOG_BLOG_BLOG_MES_SHOW_ERROR"));

		if (empty($arResultNFCache["ERROR_MESSAGE"]))
		{
			if (
				intval($_GET["hide_id"]) > 0
				|| intval($_GET["show_id"]) > 0
			)
			{
				$Notify = CIdeaManagment::getInstance()->Notification(array("ID" => $postId));

				if (intval($_GET["hide_id"]) > 0)
				{
					$Notify->getSonetNotify()->HideMessage();
				}
				else
				{
					$Notify->getSonetNotify()->ShowMessage();
				}
			}

			BXClearCache(True, "/".SITE_ID."/idea/".$arResult["BLOG"]["ID"]."/first_page/");
			BXClearCache(True, "/".SITE_ID."/idea/".$arResult["BLOG"]["ID"]."/pages/");
			BXClearCache(True, "/".SITE_ID."/idea/".$arResult["BLOG"]["ID"]."/post/".$postId."/");
			BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
			BXClearCache(True, '/'.SITE_ID.'/idea/tags/');
			//RSS
			BXClearCache(True, "/".SITE_ID."/idea/".$arResult["BLOG"]["ID"]."/rss_list");

			LocalRedirect($APPLICATION->GetCurPageParam("success=Y", Array("sessid", "success")));
		}
	}
}
/********************************************************************
				/Actions
********************************************************************/


/********************************************************************
				Data
********************************************************************/
if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;
	CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);
	$cache->Output();
}
else
{
	if ($CACHE_TIME > 0)
		$cache->StartDataCache($CACHE_TIME, $cache_id, $cache_path);

	$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
	if($arGroup["SITE_ID"] == SITE_ID)
	{
		$arResult["BLOG"]["Group"] = $arGroup;
		if($arResult["PostPerm"] >= BLOG_PERMS_READ)
		{
			$arResult["enable_trackback"] = COption::GetOptionString("blog","enable_trackback", "Y");
			//@::Idea| If moderator the display hidden posts
			if($arResult["IDEA_MODERATOR"])
				$arFilter["PUBLISH_STATUS"] = array(
					BLOG_PUBLISH_STATUS_PUBLISH,
					BLOG_PUBLISH_STATUS_READY,
					BLOG_PUBLISH_STATUS_DRAFT,
				);
			else
				$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;

			$arFilter["BLOG_ID"] = $arBlog["ID"];

			if(IntVal($arParams["CATEGORY_ID"])>0)
			{
				$arFilter["CATEGORY_ID_F"] = $arParams["CATEGORY_ID"];
				if($arParams["SET_TITLE"] == "Y")
				{
					$arCat = CBlogCategory::GetByID($arFilter["CATEGORY_ID_F"]);
					$arResult["title"]["category"] = CBlogTools::htmlspecialcharsExArray($arCat);
				}
			}

			$arResult["filter"] = $arFilter;

			$dbPost = CBlogPost::GetList(
				$SORT,
				array_merge($arParams["EXT_FILTER"], $arFilter),
				false,
				array("bDescPageNumbering"=>true, "nPageSize"=>$arParams["MESSAGE_COUNT"], "bShowAll" => false),
				array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_CREATE", "DATE_PUBLISH", "KEYWORDS", "PUBLISH_STATUS", "ATRIBUTE", "ATTACH_IMG", "ENABLE_TRACKBACK", "ENABLE_COMMENTS", "VIEWS", "NUM_COMMENTS", "CODE", "MICRO", "CATEGORY_ID")
			);

			$arResult["NAV_STRING"] = $dbPost->GetPageNavStringEx($navComponentObject,
				GetMessage("MESSAGE_COUNT"), $arParams["NAV_TEMPLATE"],
				false, ($this->__component->__parent ? $this->__component->__parent : $this->__component));
			$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();

			$arResult["POST"] = Array();
			$arResult["IDS"] = Array();
			$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
			$arParserParams = Array(
				"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
				"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
			);

			while($CurPost = $dbPost->GetNext())
			{
				$CurPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>CBlogPost::GetPostID($CurPost["ID"], $CurPost["CODE"], $arParams["ALLOW_POST_CODE"])));
				$CurPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $CurPost["AUTHOR_ID"]));

				$arImages = array();
				$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$CurPost['ID'], "BLOG_ID"=>$arBlog['ID']));
				while ($arImage = $res->Fetch())
					$arImages[$arImage['ID']] = $arImage['FILE_ID'];

				if($CurPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
				{
					$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
					if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
						$arAllow["VIDEO"] = "N";
					$CurPost["TEXT_FORMATED"] = $p->convert($CurPost["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
				}
				else
				{
					$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
					if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
						$arAllow["VIDEO"] = "N";
					$CurPost["TEXT_FORMATED"] = $p->convert($CurPost["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
				}
				$CurPost["IMAGES"] = $arImages;

				$CurPost["BlogUser"] = CBlogUser::GetByID($CurPost["AUTHOR_ID"], BLOG_BY_USER_ID);
				$CurPost["BlogUser"] = CBlogTools::htmlspecialcharsExArray($CurPost["BlogUser"]);
				$CurPost["BlogUser"]["AVATAR_file"] = CFile::GetFileArray($CurPost["BlogUser"]["AVATAR"]);
				if ($CurPost["BlogUser"]["AVATAR_file"] !== false)
					$CurPost["BlogUser"]["AVATAR_img"] = CFile::ShowImage($CurPost["BlogUser"]["AVATAR_file"]["SRC"], 150, 150, "border=0 align='right'");

				$dbUser = CUser::GetByID($CurPost["AUTHOR_ID"]);
				$CurPost["arUser"] = $dbUser->GetNext();
				$CurPost["AuthorName"] = CBlogUser::GetUserName($CurPost["BlogUser"]["ALIAS"], $CurPost["arUser"]["NAME"], $CurPost["arUser"]["LAST_NAME"], $CurPost["arUser"]["LOGIN"], $CurPost["arUser"]["SECOND_NAME"]);

				if(($arResult["PostPerm"]>=BLOG_PERMS_FULL && $arResult["IDEA_MODERATOR"]) || ($arResult["PostPerm"]>=BLOG_PERMS_WRITE && $CurPost["AUTHOR_ID"] == $user_id))
					$CurPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$CurPost["ID"]));

				if($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"]>=BLOG_PERMS_MODERATE && $CurPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
					$CurPost["urlToHide"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("hide_id=".$CurPost["ID"], Array("del_id", "sessid", "success", "hide_id", "show_id")));
				elseif($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"]>=BLOG_PERMS_MODERATE && $CurPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
					$CurPost["urlToShow"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("show_id=".$CurPost["ID"], Array("del_id", "sessid", "success", "show_id", "hide_id")));

				if($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"] >= BLOG_PERMS_FULL)
					$CurPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$CurPost["ID"], Array("del_id", "sessid", "success", "hide_id")));

				if (preg_match("/(\[CUT\])/i",$CurPost['DETAIL_TEXT']) || preg_match("/(<CUT>)/i",$CurPost['DETAIL_TEXT']))
					$CurPost["CUT"] = "Y";

				if(strlen($CurPost["CATEGORY_ID"])>0)
				{
					$arCategory = explode(",",$CurPost["CATEGORY_ID"]);
					foreach($arCategory as $v)
					{
						if(IntVal($v)>0)
						{
							$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
							$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v));
							$CurPost["CATEGORY"][] = $arCatTmp;
						}
					}
				}
				$CurPost["POST_PROPERTIES"] = array("SHOW" => "N");

				if (!empty($arParams["POST_PROPERTY_LIST"]))
				{
					$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $CurPost["ID"], LANGUAGE_ID);

					if (count($arParams["POST_PROPERTY_LIST"]) > 0)
					{
						foreach ($arPostFields as $FIELD_NAME => $arPostField)
						{
							if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
								continue;
							$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
							$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
							$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
							$CurPost["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
						}
					}
					if (!empty($CurPost["POST_PROPERTIES"]["DATA"]))
						$CurPost["POST_PROPERTIES"]["SHOW"] = "Y";
				}
				$CurPost["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($CurPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
				$CurPost["DATE_PUBLISH_DATE"] = ConvertDateTime($CurPost["DATE_PUBLISH"], FORMAT_DATE);
				$CurPost["DATE_PUBLISH_TIME"] = ConvertDateTime($CurPost["DATE_PUBLISH"], "HH:MI");
				$CurPost["DATE_PUBLISH_D"] = ConvertDateTime($CurPost["DATE_PUBLISH"], "DD");
				$CurPost["DATE_PUBLISH_M"] = ConvertDateTime($CurPost["DATE_PUBLISH"], "MM");
				$CurPost["DATE_PUBLISH_Y"] = ConvertDateTime($CurPost["DATE_PUBLISH"], "YYYY");
				$arResult["POST"][] = $CurPost;
				$arResult["IDS"][] = $CurPost["ID"];
			}
		}
	}
	else
	{
		$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		CHTTP::SetStatus("404 Not Found");
	}

	if ($CACHE_TIME > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}

if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
	$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);

$arResult["ERROR_MESSAGE"] = array_unique(array_merge($arResult["ERROR_MESSAGE"], $arResultNFCache["ERROR_MESSAGE"]));
$arResult["OK_MESSAGE"] = $arResultNFCache["OK_MESSAGE"];

$this->IncludeComponentTemplate();
$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);
?>