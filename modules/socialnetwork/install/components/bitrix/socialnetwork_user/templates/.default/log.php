<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "user_log";
include("util_menu.php");

?><div id="log_external_container"></div><?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.log.ex", 
	"", 
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_LOG_ENTRY" => $arResult["PATH_TO_LOG_ENTRY"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
		"PATH_TO_USER_BLOG_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
		"PATH_TO_USER_BLOG_POST_IMPORTANT" => $arResult["PATH_TO_USER_BLOG_POST_IMPORTANT"],
		"PATH_TO_GROUP_BLOG_POST" => $arParams["PATH_TO_GROUP_POST"],
		"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"PAGE_SIZE" => $arParams["ITEM_DETAIL_COUNT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arResult["DATE_TIME_FORMAT_WITHOUT_YEAR"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"SHOW_EVENT_ID_FILTER" => "Y",
		"SHOW_SETTINGS_LINK" => "Y",
		"SET_LOG_CACHE" => "Y",
		"USE_COMMENTS" => "Y",
		"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
		"PAGER_DESC_NUMBERING" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_SHADOW" => "N",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CONTAINER_ID" => "log_external_container",
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		"AVATAR_SIZE" => $arParams["LOG_THUMBNAIL_SIZE"],
		"AVATAR_SIZE_COMMENT" => $arParams["LOG_COMMENT_THUMBNAIL_SIZE"],
		"NEW_TEMPLATE" => $arParams["LOG_NEW_TEMPLATE"],
		"AUTH" => $arParams["LOG_AUTH"],
		"CHECK_COMMENTS_PERMS" => (isset($arParams["CHECK_COMMENTS_PERMS"]) && $arParams["CHECK_COMMENTS_PERMS"] == "Y" ? "Y" : "N"),
		"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
		"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
	),
	$this->getComponent()
);
?>