<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$file_name = basename($APPLICATION->GetCurPage(),".php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/".$file_name.".php");
InitSorting();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_1_1.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_1_2.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_1_3.php");
$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_2_1.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_2_2.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".$file_name."_2_3.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
