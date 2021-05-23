<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$pageId = "user";
include("util_menu.php");

$APPLICATION->IncludeComponent("bitrix:dav.synchronize_settings", "", array());
?>