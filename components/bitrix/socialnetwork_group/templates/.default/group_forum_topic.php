<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

$pageId = "group_forum";
include("util_group_menu.php");
include("util_group_profile.php");
?>
<?$arInfo = $APPLICATION->IncludeComponent("bitrix:socialnetwork.forum.topic.read", "", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"] ?? null,
		"ACTION" => $arResult["VARIABLES"]["action"] ?? null,

		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"] ?? null,

		"URL_TEMPLATES_TOPIC_LIST"	=>	$arResult["~PATH_TO_GROUP_FORUM"],
		"URL_TEMPLATES_TOPIC"	=>	$arResult["~PATH_TO_GROUP_FORUM_TOPIC"],
		"URL_TEMPLATES_TOPIC_EDIT"	=>	$arResult["~PATH_TO_GROUP_FORUM_TOPIC_EDIT"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["~PATH_TO_USER"],

		"SHOW_VOTE" => $arParams["SHOW_VOTE"] ?? null,
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"] ?? null,
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"] ?? null,

		"PAGEN" => $arParams["PAGEN"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"PAGE_NAVIGATION_SHOW_ALL" =>  $arParams["PAGE_NAVIGATION_SHOW_ALL"],

		"MESSAGES_PER_PAGE"	=>	$arParams["MESSAGES_PER_PAGE"],

		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"],
		"IMAGE_SIZE"	=>	$arParams["IMAGE_SIZE"],
		"DATE_FORMAT"	=>	$arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"],
		"USER_FIELDS" => $arParams["USER_FIELDS_FORUM"] ?? null,

		"SHOW_RATING"	=>	$arParams["SHOW_RATING"],
		"RATING_ID"	=>	$arParams["RATING_ID"] ?? null,
		"RATING_TYPE"	=>	$arParams["RATING_TYPE"],
		"SET_TITLE"	=>	$arParams["SET_TITLE"],
		"AJAX_POST" => 	$arParams["FORUM_AJAX_POST"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	), 
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
if (
	!empty($arInfo)
	&& $arInfo["PERMISSION"] > "E"
	&& !($arInfo["HideArchiveLinks"] ?? null)
):
?><div class='forum_post_form'><?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.post_form", 
	"", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"] ?? null,
		"PAGE_NAME"	=>	"group_forum_message",
		"MESSAGE_TYPE"	=>	"REPLY",
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"] ?? null,
		"USER_ID" => $arResult["VARIABLES"]["user_id"] ?? null,
		
		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_GROUP_FORUM_TOPIC"],
		"URL_TEMPLATES_MESSAGE" => $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],

		"SHOW_VOTE" => $arParams["SHOW_VOTE"] ?? null,
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"] ?? null,
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"] ?? null,
		"USER_FIELDS" => $arParams["USER_FIELDS_FORUM"] ?? null,

		"MESSAGE" => $arInfo["MESSAGE"] ?? null,
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"] ?? null,
		
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		"AJAX_POST" => $arParams["FORUM_AJAX_POST"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"] ?? null
	),
	$component,
	array("HIDE_ICONS" => "Y"));
?></div><?
endif;
?>