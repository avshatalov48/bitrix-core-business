<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.rss.all", 
		"", 
		Array(
				"BLOG_VAR" => $arResult["ALIASES"]["blog"],
				"POST_VAR" => $arResult["ALIASES"]["post_id"],
				"USER_VAR" => $arResult["ALIASES"]["user_id"],
				"PAGE_VAR" => $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
				"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
				"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
				"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
				"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
				"TYPE" => $arResult["VARIABLES"]["type"],
				"CACHE_TYPE" => $arResult["CACHE_TYPE"],
				"CACHE_TIME" => $arResult["CACHE_TIME"],
				"PARAM_GROUP_ID" => $arParams["GROUP_ID"],
				"USE_SOCNET" => $arParams["USE_SOCNET"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
			),
		$component 
	);
?>
</div>