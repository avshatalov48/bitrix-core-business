<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?><div class="feed-blog-post-list"><?

$pageId = "user_grat";


if(COption::GetOptionString("blog", "socNetNewPerms", "N") == "N" && $USER->IsAdmin() && !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrix24"))
{
	?>
	<div class="feed-add-error">
		<span class="feed-add-info-icon"></span><span class="feed-add-info-text"><?=GetMessage("BLG_SOCNET_REINDEX", Array("#url#" => $arResult["PATH_TO_USER_REINDEX"]))?></span>
	</div>
	<?
}

$livefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

$arLogParams = Array(
	"ENTITY_TYPE" => "",
	"USER_VAR" => $arParams["VARIABLE_ALIASES"]["user_id"] ?? null,
	"GROUP_VAR" => $arParams["VARIABLE_ALIASES"]["group_id"] ?? null,
	"PATH_TO_USER" => $arResult["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
	"SET_TITLE" => "N",
	"AUTH" => "Y",
	"SET_NAV_CHAIN" => "N",
	"PATH_TO_MESSAGES_CHAT" => $arParams["PM_URL"] ?? null,
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"] ?? null,
	"PATH_TO_USER_BLOG_POST_IMPORTANT" => $arResult["PATH_TO_USER_BLOG_POST_IMPORTANT"] ?? null,
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
	"PATH_TO_GROUP_PHOTO_SECTION" => $arParams["PARENT_COMPONENT_RESULT"]["PATH_TO_GROUP_PHOTO_SECTION"]  ?? null,
	"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"],
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
	"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arResult["DATE_TIME_FORMAT_WITHOUT_YEAR"],
	"SHOW_YEAR" => $arParams["SHOW_YEAR"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"SUBSCRIBE_ONLY" => "N",
	"SHOW_EVENT_ID_FILTER" => "N",
	"USE_COMMENTS" => "Y",
	"PHOTO_THUMBNAIL_SIZE" => "48",
	"PAGE_ISDESC" => "N",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "N",
	"AJAX_OPTION_HISTORY" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"CONTAINER_ID" => "log_external_container",
	"PAGE_SIZE" => 10,
	//"LOG_DATE_DAYS" => 365,
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"PAGETITLE_TARGET" => "pagetitle_log",
	"SHOW_SETTINGS_LINK" => "Y",
	"AVATAR_SIZE" => $arParams["LOG_THUMBNAIL_SIZE"],
	"AVATAR_SIZE_COMMENT" => $arParams["LOG_COMMENT_THUMBNAIL_SIZE"],
	"NEW_TEMPLATE" => $arParams["LOG_NEW_TEMPLATE"],
	"USER_ID" => 0,
	"GROUP_ID" => 0,
	"EVENT_ID" => $livefeedProvider->getEventId(),
	"SET_LOG_CACHE" => "Y",
	"BLOG_COMMENT_ALLOW_VIDEO"  => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
	"BLOG_COMMENT_ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
	"CURRENT_USER_ID" => $arResult["VARIABLES"]["user_id"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CHECK_COMMENTS_PERMS" => (isset($arParams["CHECK_COMMENTS_PERMS"]) && $arParams["CHECK_COMMENTS_PERMS"] == "Y" ? "Y" : "N"),
	"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"] ?? null,
	"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"] ?? null,
	"TOP_TABS_VISIBLE" => "N",
	"PAGE_ID" => $pageId,
);

?><div id="log_external_container"></div><?

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.log.ex",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => $arLogParams,
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
		'USE_PADDING' => false,
	)
);

?></div>