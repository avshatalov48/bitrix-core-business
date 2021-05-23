<?
define("STOP_STATISTICS", true);

$SITE_ID = '';
if (isset($_REQUEST["site_id"]) && is_string($_REQUEST["site_id"]))
	$SITE_ID = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["site_id"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent("bitrix:bizproc.log",
	'.default',
	array(
		"COMPONENT_VERSION" => 2,
		"ID" => isset($_REQUEST['WORKFLOW_ID'])? (string)$_REQUEST['WORKFLOW_ID'] : null,
		"SET_TITLE" => "N",
		"INLINE_MODE" => "Y",
		"AJAX_MODE" => "Y",
	)
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();