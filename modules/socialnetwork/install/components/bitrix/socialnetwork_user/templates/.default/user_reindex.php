<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_menu",
	"",
	Array(
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PAGE_ID" => "",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.reindex",
	"",
	Array(
		"TYPE" => array("users"),

		"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"PATH_TO_GROUP_BLOG" => "",
		"PATH_TO_GROUP_BLOG_POST" => "",
		"PATH_TO_GROUP_BLOG_COMMENT" => "",
		"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"PATH_TO_USER_BLOG_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
		"PATH_TO_USER_BLOG_COMMENT" => $arResult["PATH_TO_USER_BLOG_POST"]."?commentId=#comment_id##com#comment_id#",

		"FORUM_ID" => $arParams["FORUM_ID"],
		"PATH_TO_GROUP_FORUM_MESSAGE" => "",
		"PATH_TO_USER_FORUM_MESSAGE" => $arResult["PATH_TO_USER_FORUM_MESSAGE"],

		"PHOTO_USE_COMMENTS" => $arParams["PHOTO_USE_COMMENTS"],
		"PHOTO_GROUP_IBLOCK_ID" => false,
		"PATH_TO_GROUP_PHOTO_ELEMENT" => "",
		"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
		"PATH_TO_USER_PHOTO_ELEMENT" => $arResult["PATH_TO_USER_PHOTO_ELEMENT"],
		"PHOTO_FORUM_ID" => $arParams["PHOTO_FORUM_ID"],

		"CALENDAR_GROUP_IBLOCK_ID" => false,
		"PATH_TO_GROUP_CALENDAR_ELEMENT" => "",

		"PATH_TO_GROUP_TASK_ELEMENT" => "",
		"PATH_TO_USER_TASK_ELEMENT" => $arResult["PATH_TO_USER_TASKS_TASK"],
		"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],

		"FILES_USE_COMMENTS" => $arParams["FILES_USE_COMMENTS"],
		"FILES_PROPERTY_CODE" => $arParams["NAME_FILE_PROPERTY"],
		"FILES_FORUM_ID" => $arParams["FILES_FORUM_ID"],
		"FILES_GROUP_IBLOCK_ID" => false,
		"PATH_TO_GROUP_FILES_ELEMENT" => "",
		"FILES_USER_IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
		"PATH_TO_USER_FILES_ELEMENT" => $arResult["PATH_TO_USER_FILES_ELEMENT"],
	),
	$component
);
?>
