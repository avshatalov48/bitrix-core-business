<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.menu",
	"",
	Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
			"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
			"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
			"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
			"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
			"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
			"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
			"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
			"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],		
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
		),
	$component
);

$APPLICATION->IncludeComponent(
		"bitrix:blog.group.blog", 
		"", 
		Array(
				"BLOG_COUNT"				=> $arResult["BLOG_COUNT"],
				"SHOW_BLOG_WITHOUT_POSTS"	=> "N",
				"BLOG_VAR"					=> $arResult["ALIASES"]["blog"],
				"POST_VAR"					=> $arResult["ALIASES"]["post_id"],
				"USER_VAR"					=> $arResult["ALIASES"]["user_id"],
				"PAGE_VAR"					=> $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"				=> $arResult["PATH_TO_BLOG"],
				"PATH_TO_POST"				=> $arResult["PATH_TO_POST"],
				"PATH_TO_USER"				=> $arResult["PATH_TO_USER"],
				"ID"						=> $arResult["VARIABLES"]["group_id"],
				"CACHE_TYPE"				=> $arResult["CACHE_TYPE"],
				"CACHE_TIME"				=> $arResult["CACHE_TIME"],
				"SET_TITLE"					=> $arResult["SET_TITLE"],
				"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
				"NAV_TEMPLATE"	=> $arParams["NAV_TEMPLATE"],
				"GROUP_ID" 			=> $arParams["GROUP_ID"],
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
				"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
				"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
				"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
				"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
				"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
				"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
				"GROUP_ID"			=> $arResult["VARIABLES"]["group_id"],
				"PARAM_GROUP_ID" 			=> $arParams["GROUP_ID"],
				"MODE"				=> "G",
			),
		$component 
	);


?>
</div>