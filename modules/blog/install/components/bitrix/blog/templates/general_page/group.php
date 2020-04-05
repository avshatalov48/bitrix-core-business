<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.group.blog", 
		"", 
		Array(
				"BLOG_COUNT"				=> $arParams["BLOG_COUNT"],
				"SHOW_BLOG_WITHOUT_POSTS"	=> "N",
				"BLOG_VAR"					=> $arParams["VARIABLE_ALIASES"]["blog"],
				"POST_VAR"					=> $arParams["VARIABLE_ALIASES"]["post_id"],
				"USER_VAR"					=> $arParams["VARIABLE_ALIASES"]["user_id"],
				"PAGE_VAR"					=> $arParams["VARIABLE_ALIASES"]["page"],
				"PATH_TO_BLOG"				=> $arParams["PATH_TO_BLOG"],
				"PATH_TO_POST"				=> $arParams["PATH_TO_POST"],
				"PATH_TO_GROUP_BLOG"				=> $arParams["PATH_TO_GROUP_BLOG"],
				"PATH_TO_GROUP_BLOG_POST"				=> $arParams["PATH_TO_GROUP_BLOG_POST"],
				"PATH_TO_USER"				=> $arParams["PATH_TO_USER"],
				"ID"						=> $arResult["VARIABLES"]["group_id"],
				"CACHE_TYPE"				=> $arParams["CACHE_TYPE"],
				"CACHE_TIME"				=> $arParams["CACHE_TIME"],
				"SET_TITLE" => $arResult["SET_TITLE"],
				"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
				"NAV_TEMPLATE"	=> $arParams["NAV_TEMPLATE"],
				"USE_SOCNET" => $arParams["USE_SOCNET"],
				"GROUP_ID" => $arParams["GROUP_ID"],
				"SEO_USER"			=> $arParams["SEO_USER"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
				"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],

			),
		$component 
	);
$APPLICATION->IncludeComponent(
		"bitrix:blog.rss.link",
		"group",
		Array(
				"RSS1"				=> "Y",
				"RSS2"				=> "Y",
				"ATOM"				=> "Y",
				"BLOG_VAR"			=> $arParams["VARIABLE_ALIASES"]["blog"],
				"POST_VAR"			=> $arParams["VARIABLE_ALIASES"]["post_id"],
				"GROUP_VAR"			=> $arParams["VARIABLE_ALIASES"]["group_id"],
				"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
				"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
				"BLOG_URL"			=> $arParams["VARIABLES"]["blog"],
				"GROUP_ID"			=> $arResult["VARIABLES"]["group_id"],
				"MODE"				=> "G",
				"PARAM_GROUP_ID" => $arParams["GROUP_ID"],
			),
		$component 
	);


?>
</div>