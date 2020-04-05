<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_groups", 
	"",
	Array(
		"THUMBNAIL_SIZE" => $arParams["GROUP_THUMBNAIL_SIZE"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"COLUMNS_COUNT" => 3,
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"PAGE" => ($arResult["VARIABLES"]["subject_id"] == -1 ? "groups_list" : "groups_subject"),
		"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
		"SUBJECT_ID" => $arResult["VARIABLES"]["subject_id"],
		"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
	),
	$component
);
?>
