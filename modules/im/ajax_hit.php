<?
if($_SERVER["REQUEST_METHOD"] == "POST" && array_key_exists("IM_AJAX_CALL", $_REQUEST) && $_REQUEST["IM_AJAX_CALL"] === "Y")
{
	$arResult = array();
	global $USER, $APPLICATION, $DB;
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/im.messenger/im.ajax.php");
	die();
}
?>
