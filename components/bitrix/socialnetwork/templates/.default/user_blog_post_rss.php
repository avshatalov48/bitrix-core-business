<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.rss",
	"",
	Array(
		"MESSAGE_COUNT" => "10", 
		"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"], 
		"PATH_TO_POST" => $arResult["PATH_TO_USER_BLOG_POST"], 
		"PATH_TO_USER" => $arResult["PATH_TO_USER"], 
		"TYPE" => $arResult["VARIABLES"]["type"], 
		"CACHE_TYPE" => $arResult["CACHE_TYPE"], 
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"USE_SOCNET" => "Y",
		"POST_ID" => $arResult["VARIABLES"]["post_id"],
		"MODE" => "C",
		"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
		"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
		"COMMENT_ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
		"NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
		"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
		"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
	),
	$component 
);
?>