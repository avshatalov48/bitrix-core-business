<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.rss", 
		"", 
		Array(
				"BLOG_VAR" => $arResult["ALIASES"]["blog"],
				"POST_VAR" => $arResult["ALIASES"]["post_id"],
				"USER_VAR" => $arResult["ALIASES"]["user_id"],
				"PAGE_VAR" => $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG" => $arResult["PATH_TO_BLOG"],
				"PATH_TO_POST" => $arResult["PATH_TO_POST"],
				"PATH_TO_USER" => $arResult["PATH_TO_USER"],
				"BLOG_URL" => $arResult["VARIABLES"]["blog"],
				"POST_ID" => $arResult["VARIABLES"]["post_id"],
				"TYPE" => $arResult["VARIABLES"]["type"],
				"MODE" => "C",
				"CACHE_TYPE" => $arResult["CACHE_TYPE"],
				"CACHE_TIME" => $arResult["CACHE_TIME"],
				"GROUP_ID" => $arParams["GROUP_ID"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"NO_URL_IN_COMMENTS" => $arParams["NO_URL_IN_COMMENTS"],
				"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["NO_URL_IN_COMMENTS_AUTHORITY"],
			),
		$component 
	);
?>