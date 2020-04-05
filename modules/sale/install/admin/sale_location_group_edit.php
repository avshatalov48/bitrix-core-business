<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('sale');

if(CSaleLocation::isLocationProEnabled())
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/location_group_edit.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/location_group_edit_old.php");
?>