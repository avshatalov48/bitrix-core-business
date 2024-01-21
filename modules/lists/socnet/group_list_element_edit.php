<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php
$pageId = "group_group_lists";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");
?>
<?php $APPLICATION->IncludeComponent("bitrix:lists.element.navchain", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"ADD_NAVCHAIN_GROUP" => "Y",
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],
	"ADD_NAVCHAIN_LIST" => "N",
	"ADD_NAVCHAIN_SECTIONS" => "N",
	"ADD_NAVCHAIN_ELEMENT" => "N",
	),
	$component
);?>
<?php

$arResult['PATH_TO_GROUP_BIZPROC_WORKFLOW_DELETE'] = $arResult['PATH_TO_GROUP_BIZPROC_WORKFLOW_DELETE'] ?? '';

$p = mb_strpos($arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_DELETE"], "?");
if($p === false)
	$ch = "?";
else
	$ch = "&";
$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_DELETE"] .= $ch."action=del_bizproc";
$APPLICATION->IncludeComponent("bitrix:lists.element.edit", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"ELEMENT_ID" => $arResult["VARIABLES"]["element_id"],
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],
	"LIST_ELEMENT_URL" => $arResult["PATH_TO_GROUP_LIST_ELEMENT_EDIT"],
	"LIST_FILE_URL" => $arResult["PATH_TO_GROUP_LIST_FILE"],
	"BIZPROC_LOG_URL" => $arResult["PATH_TO_GROUP_BIZPROC_LOG"],
	"BIZPROC_WORKFLOW_START_URL" => $arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_START"],
	"BIZPROC_TASK_URL" => $arResult["PATH_TO_GROUP_BIZPROC_TASK"],
	"BIZPROC_WORKFLOW_DELETE_URL" => $arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_DELETE"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	),
	$component
);
?>
