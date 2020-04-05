<?
define("MODULE_ID", "lists");
if($_REQUEST['entity']=="Bitrix\\Lists\\BizprocDocumentLists")
	define("ENTITY", 'Bitrix\Lists\BizprocDocumentLists');
else
	define("ENTITY", "BizprocDocument");

$fp = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/admin/bizproc_activity_settings.php";
if(file_exists($fp))
	require($fp);