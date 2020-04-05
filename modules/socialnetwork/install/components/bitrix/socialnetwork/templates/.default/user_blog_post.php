<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?><div class="feed-blog-post-list feed-blog-post-detail"><?

$pageId = "user_blog";
include("util_menu.php");
include("util_profile.php");

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.menu",
	"",
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
		"PATH_TO_DRAFT" => $arResult["PATH_TO_USER_BLOG_DRAFT"],
		"PATH_TO_TAGS" => $arResult["PATH_TO_USER_BLOG_TAGS"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_MODERATION" => $arResult["PATH_TO_USER_BLOG_MODERATION"],
		"SET_TITLE" => "Y",
		"PAGE_ID" => $pageId,
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE']
	),
	$this->getComponent()
);

if(strlen($arResult["PATH_TO_USER_BLOG_CATEGORY"]) <= 0)
{
	$arResult["PATH_TO_USER_BLOG_CATEGORY"] = $arResult["PATH_TO_USER_BLOG"].(strpos("?", $arResult["PATH_TO_USER_BLOG"]) === false ? "?" : "&")."category=#category_id#";
}

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.post",
	"",
	Array(
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
		"PATH_TO_POST_IMPORTANT" => $arResult["PATH_TO_USER_BLOG_POST_IMPORTANT"],
		"PATH_TO_BLOG_CATEGORY" => $arResult["PATH_TO_USER_BLOG_CATEGORY"],
		"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"],
		"ID" => $arResult["VARIABLES"]["post_id"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"SET_NAV_CHAIN" => "N",
		"SET_TITLE" => "N",
		"POST_PROPERTY" => $arParams["POST_PROPERTY"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"USE_SOCNET" => "Y",
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
		"USE_SHARE" => $arParams["USE_SHARE"],
		"SHARE_HIDE" => $arParams["SHARE_HIDE"],
		"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"],
		"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"],
		"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
		"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
		"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
		"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
		"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
		"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
		"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
	),
	$this->getComponent()
);

?></div>
