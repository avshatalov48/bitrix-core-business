<?
define("MODULE_ID", "lists");
define("ENTITY", $_REQUEST['entity']);

$fp = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/admin/bizproc_selector.php";
if(file_exists($fp))
	require($fp);