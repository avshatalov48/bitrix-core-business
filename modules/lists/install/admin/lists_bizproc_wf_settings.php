<?
define("MODULE_ID", "lists");
if($_REQUEST['entity']=="BitrixListsBizprocDocumentLists")
	define("ENTITY", 'Bitrix\Lists\BizprocDocumentLists');
else
	define("ENTITY", "BizprocDocument");

$fp = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/admin/bizproc_wf_settings.php";
if (file_exists($fp))
	require($fp);