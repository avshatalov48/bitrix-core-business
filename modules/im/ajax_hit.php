<?php

if (
	isset($_SERVER["REQUEST_METHOD"], $_REQUEST["IM_AJAX_CALL"])
	&& $_SERVER["REQUEST_METHOD"] === "POST"
	&& $_REQUEST["IM_AJAX_CALL"] === "Y"
)
{
	$arResult = [];
	global $USER, $APPLICATION, $DB;
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/im.messenger/im.ajax.php");
	die();
}
?>
