<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent(
	"bitrix:forum.topic.read",
	"",
	array(
		"FID" => $arResult["FID"],
		"TID" => $arResult["TID"],
		"TITLE_SEO" => $arResult["TITLE_SEO"],
		"MID" => $arResult["MID"],
		"MESSAGES_PER_PAGE" => $arResult["MESSAGES_PER_PAGE"],
		
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_MESSAGE_MOVE" => $arResult["URL_TEMPLATES_MESSAGE_MOVE"],
		"URL_TEMPLATES_TOPIC_NEW" => $arResult["URL_TEMPLATES_TOPIC_NEW"],
		"URL_TEMPLATES_SUBSCR_LIST" => $arResult["URL_TEMPLATES_SUBSCR_LIST"],
		"URL_TEMPLATES_TOPIC_MOVE" => $arResult["URL_TEMPLATES_TOPIC_MOVE"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"],
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],

		"USER_FIELDS" => $arParams["USER_FIELDS"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		"AJAX_POST" => $arParams["AJAX_POST"],

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"SET_DESCRIPTION" => $arParams["SET_DESCRIPTION"],
		"SET_PAGE_PROPERTY" => $arResult["SET_PAGE_PROPERTY"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"],
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => $arParams["SEND_ICQ"],
		"SHOW_RSS" => $arParams["USE_RSS"],
		"SHOW_FIRST_POST" => $arParams["SHOW_FIRST_POST"], 
		"HIDE_USER_ACTION" => $arParams["HIDE_USER_ACTION"], 
		
		"SHOW_VOTE" => $arParams["SHOW_VOTE"], 
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"], 
		"SEO_USER" => $arParams["SEO_USER"],
		
		"SHOW_RATING" => $arParams["SHOW_RATING"], 
		"RATING_ID" => $arParams["RATING_ID"],
		"RATING_TYPE" => $arParams["RATING_TYPE"]
	),
	$component
);
?><?
if (in_array("USERS_ONLINE", $arParams["SHOW_STATISTIC_BLOCK"])):
?><?$APPLICATION->IncludeComponent("bitrix:forum.statistic", "", 
	Array(
		"FID"	=>	($arInfo ? $arInfo["FID"] : $arResult["FID"]),
		"TID"	=>	($arInfo ? $arInfo["TID"] : $arResult["TID"]),
		"TITLE_SEO" => $arResult["TITLE_SEO"],
		"PERIOD"	=>	$arParams["TIME_INTERVAL_FOR_USER_STAT"],
		"SHOW"	=>	array("USERS_ONLINE"),
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TIME_USER_STAT" => $arParams["CACHE_TIME_USER_STAT"], 
		
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"], 
		"SEO_USER" => $arParams["SEO_USER"]
	), $component
);?><?
endif;
if ($arInfo != false):
?><div class='forum_post_form'><?$APPLICATION->IncludeComponent("bitrix:forum.post_form", "", 
	Array(
		"FID"	=>	$arInfo["FID"],
		"TID"	=>	$arInfo["TID"],
		"MID"	=>	0,
		"PAGE_NAME"	=>	"read",
		"MESSAGE_TYPE"	=>	"REPLY",
		"FORUM" => $arInfo["FORUM"],
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"],
		"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"],
		
		"USER_FIELDS"	=>	$arParams["USER_FIELDS"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"AJAX_POST" => $arParams["AJAX_POST"],
		"SHOW_VOTE" => "N", 
		"VOTE_CHANNEL_ID" => "0", 
		"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"],
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"],
		
		"AJAX_TYPE"	=>	"N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"]
	),
	$component
);?></div><?
endif;

@include_once(str_replace(array("\\", "//"), "/", __DIR__."/footer.php"));
?>