<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$pageId = "";
include("util_menu.php");

$APPLICATION->IncludeComponent(
	"bitrix:lists.user.processes",
	"",
	Array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
		"PATH_TO_PROCESSES" => $arResult["PATH_TO_PROCESSES"],
		"PATH_TO_LIST_ELEMENT" => $arResult["PATH_TO_LIST_ELEMENT_EDIT"],
		"SET_TITLE" => $arParams["SET_TITLE"]
	),
	$component, array("HIDE_ICONS" => "Y")
);