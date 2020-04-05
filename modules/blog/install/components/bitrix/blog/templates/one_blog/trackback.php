<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
		"bitrix:blog.post.trackback.get", 
		"", 
		Array(
				"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
				"POST_VAR"		=> $arResult["ALIASES"]["post_id"],
				"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
				"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
				"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
				"ID"			=> $arResult["VARIABLES"]["post_id"],
			),
		$component 
	);
?>