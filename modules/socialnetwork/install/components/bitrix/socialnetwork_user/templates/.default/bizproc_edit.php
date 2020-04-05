<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "";
include("util_menu.php");
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:bizproc.task", 
	"", 
	Array(
	"TASK_ID" => $arResult["VARIABLES"]["task_id"],
	"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
	"USER_ID" => 0, 
	"WORKFLOW_ID" => "", 
	"DOCUMENT_URL" => "", 
	"SET_TITLE" => $arParams["SET_TITLE"],
	"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>