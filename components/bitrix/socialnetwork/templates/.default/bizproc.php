<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "";
include("util_menu.php");
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:bizproc.task.list", 
	"", 
	Array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		"WORKFLOW_ID" => "", 
		"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
		"PAGE_ELEMENTS" => "20", 
		"PAGE_NAVIGATION_TEMPLATE" => "", 
		"SHOW_TRACKING" => "N", 
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>