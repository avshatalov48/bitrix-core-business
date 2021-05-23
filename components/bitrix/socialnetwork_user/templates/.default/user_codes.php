<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user";
include("util_menu.php");

$APPLICATION->IncludeComponent("bitrix:security.user.recovery.codes", "", array());
?>