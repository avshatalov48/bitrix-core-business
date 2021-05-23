<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.metaweblog", 
		"", 
		Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
		),
		$component 
	);
?>
</div>