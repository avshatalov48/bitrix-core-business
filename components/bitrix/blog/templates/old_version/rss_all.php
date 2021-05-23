<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
		"bitrix:blog.rss.all", 
		"", 
		Array(
				"BLOG_VAR" => $arResult["ALIASES"]["blog"],
				"POST_VAR" => $arResult["ALIASES"]["post_id"],
				"USER_VAR" => $arResult["ALIASES"]["user_id"],
				"PAGE_VAR" => $arResult["ALIASES"]["page"],
				"PATH_TO_POST" => $arResult["PATH_TO_POST"],
				"PATH_TO_USER" => $arResult["PATH_TO_USER"],
				"GROUP_ID" => (intval($arParams["GROUP_ID"]) > 0) ?  $arParams["GROUP_ID"] : $arResult["VARIABLES"]["group_id"],
				"TYPE" => $arResult["VARIABLES"]["type"],
				"CACHE_TYPE" => $arResult["CACHE_TYPE"],
				"CACHE_TIME" => $arResult["CACHE_TIME"],
			),
		$component 
	);
?>