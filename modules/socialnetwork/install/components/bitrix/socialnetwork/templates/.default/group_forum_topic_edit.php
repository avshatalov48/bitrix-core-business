<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "group_forum";
include("util_group_menu.php");
include("util_group_profile.php");
?>
<?$arInfo = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.topic.new",
	"",
	array(
		"FID"	=>	$arParams["FORUM_ID"],
		"MID" => $arResult["VARIABLES"]["message_id"],
		"MESSAGE_TYPE" => $arResult["VARIABLES"]["action"],

		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],

		"SHOW_VOTE" => $arParams["SHOW_VOTE"],
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"],
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"],
		"VOTE_UNIQUE" => $arParams["VOTE_UNIQUE"],
		"VOTE_UNIQUE_IP_DELAY" => $arParams["VOTE_UNIQUE_IP_DELAY"],

		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_GROUP_FORUM"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["~PATH_TO_USER"],

		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],

		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"]
	),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
if (!empty($arInfo) && $arInfo["PERMISSION"] >= "I"):
?><?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.post_form", 
	"", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"PAGE_NAME"	=>	"group_forum_topic_edit",
		"MESSAGE_TYPE"	=>	$_REQUEST["MESSAGE_TYPE"],
		"bVarsFromForm" => $arInfo["bVarsFromForm"],

		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 

		"SHOW_VOTE" => $arParams["SHOW_VOTE"],
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"],
		"VOTE_TEMPLATE" => $arParams["VOTE_TEMPLATE"],
		"USER_FIELDS" => $arParams["USER_FIELDS_FORUM"],

		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_GROUP_FORUM_TOPIC"],
		"URL_TEMPLATES_MESSAGE" => $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],

		"MESSAGE" => $arInfo["MESSAGE"],
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"],

		"AJAX_TYPE" => "N",
		"AJAX_POST" => "N",

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"SHOW_TAGS" => $arParams["SHOW_TAGS"]),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
endif;
?>
