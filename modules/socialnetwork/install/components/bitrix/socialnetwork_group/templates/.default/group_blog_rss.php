<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.rss",
	"",
	Array(
		"MESSAGE_COUNT" => "10", 
		"PATH_TO_BLOG" => $arResult["PATH_TO_GROUP_BLOG"], 
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"], 
		"PATH_TO_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"], 
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"TYPE" => $arResult["VARIABLES"]["type"], 
		"CACHE_TYPE" => $arResult["CACHE_TYPE"], 
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PARAM_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"USE_SOCNET" => "Y",
		"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
		"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
		"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
	),
	$component 
);
?>