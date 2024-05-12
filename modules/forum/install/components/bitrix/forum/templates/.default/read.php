<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent(
	"bitrix:forum.topic.read",
	"",
	array(
		"FID" => $arResult["FID"] ?? null,
		"TID" => $arResult["TID"] ?? null,
		"TITLE_SEO" => $arResult["TITLE_SEO"] ?? null,
		"MID" => $arResult["MID"] ?? null,
		"MESSAGES_PER_PAGE" => $arResult["MESSAGES_PER_PAGE"] ?? null,

		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_MESSAGE_MOVE" => $arResult["URL_TEMPLATES_MESSAGE_MOVE"] ?? null,
		"URL_TEMPLATES_TOPIC_NEW" => $arResult["URL_TEMPLATES_TOPIC_NEW"] ?? null,
		"URL_TEMPLATES_SUBSCR_LIST" => $arResult["URL_TEMPLATES_SUBSCR_LIST"] ?? null,
		"URL_TEMPLATES_TOPIC_MOVE" => $arResult["URL_TEMPLATES_TOPIC_MOVE"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"] ?? null,
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"] ?? null,

		"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"DATE_FORMAT" => $arResult["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" => $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
		"AJAX_TYPE" => $arParams["AJAX_TYPE"] ?? null,
		"AJAX_POST" => $arParams["AJAX_POST"] ?? null,

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"SET_DESCRIPTION" => $arParams["SET_DESCRIPTION"] ?? null,
		"SET_PAGE_PROPERTY" => $arResult["SET_PAGE_PROPERTY"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"] ?? null,
		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => $arParams["SEND_ICQ"] ?? null,
		"SHOW_RSS" => $arParams["USE_RSS"] ?? null,
		"SHOW_FIRST_POST" => $arParams["SHOW_FIRST_POST"] ?? null,
		"HIDE_USER_ACTION" => $arParams["HIDE_USER_ACTION"] ?? null,

		"SHOW_VOTE" => $arParams["SHOW_VOTE"] ?? null,
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,

		"SHOW_RATING" => $arParams["SHOW_RATING"] ?? null,
		"RATING_ID" => $arParams["RATING_ID"] ?? null,
		"RATING_TYPE" => $arParams["RATING_TYPE"] ?? null
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
		"FID"	=>	$arInfo["FID"] ?? null,
		"TID"	=>	$arInfo["TID"] ?? null,
		"MID"	=>	0,
		"PAGE_NAME"	=>	"read",
		"MESSAGE_TYPE"	=>	"REPLY",
		"FORUM" => $arInfo["FORUM"] ?? null,
		"bVarsFromForm" => $arInfo["bVarsFromForm"] ?? null,

		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"] ?? null,
		"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"] ?? null,

		"USER_FIELDS"	=>	$arParams["USER_FIELDS"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"AJAX_POST" => $arParams["AJAX_POST"] ?? null,
		"SHOW_VOTE" => "N",
		"VOTE_CHANNEL_ID" => "0",
		"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"] ?? null,
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"] ?? null,

		"AJAX_TYPE"	=>	"N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,

		"SHOW_TAGS" => $arParams["SHOW_TAGS"] ?? null
	),
	$component
);?></div><?
endif;

@include_once(str_replace(array("\\", "//"), "/", __DIR__."/footer.php"));
?>
