<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "";
include("util_group_menu.php");
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.reindex",
	"",
	Array(
		"TYPE" => array("groups"),
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],

		"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_BLOG_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"],
		"PATH_TO_GROUP_BLOG_COMMENT" => $arResult["PATH_TO_GROUP_BLOG_POST"]."?commentId=#comment_id##com#comment_id#",
		"PATH_TO_USER_BLOG" => "",
		"PATH_TO_USER_BLOG_POST" => "",
		"PATH_TO_USER_BLOG_COMMENT" => "",

		"FORUM_ID" => $arParams["FORUM_ID"],
		"PATH_TO_GROUP_FORUM_MESSAGE" => $arResult["PATH_TO_GROUP_FORUM_MESSAGE"],
		"PATH_TO_USER_FORUM_MESSAGE" => "",

		"PHOTO_GROUP_IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"PATH_TO_GROUP_PHOTO_ELEMENT" => $arResult["PATH_TO_GROUP_PHOTO_ELEMENT"],
		"PHOTO_USER_IBLOCK_ID" => false,
		"PATH_TO_USER_PHOTO_ELEMENT" => "",
		"PHOTO_FORUM_ID" => $arParams["PHOTO_FORUM_ID"],

		"CALENDAR_GROUP_IBLOCK_ID" => $arParams["CALENDAR_GROUP_IBLOCK_ID"],
		"PATH_TO_GROUP_CALENDAR_ELEMENT" => $arResult["PATH_TO_GROUP_CALENDAR"]."?EVENT_ID=#element_id#",

		"PATH_TO_GROUP_TASK_ELEMENT" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
		"PATH_TO_USER_TASK_ELEMENT" => "",
		"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],

		"FILES_PROPERTY_CODE" => $arParams["NAME_FILE_PROPERTY"],
		"FILES_FORUM_ID" => $arParams["FILES_FORUM_ID"],
		"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
		"PATH_TO_GROUP_FILES_ELEMENT" => $arResult["PATH_TO_GROUP_FILES_ELEMENT"],
		"FILES_USER_IBLOCK_ID" => false,
		"PATH_TO_USER_FILES_ELEMENT" => "",
	),
	$component
);
?>
