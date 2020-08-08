<?
define("STOP_STATISTICS", true);

$SITE_ID = '';
if (isset($_REQUEST["site_id"]) && is_string($_REQUEST["site_id"]))
	$SITE_ID = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["site_id"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent("bitrix:bizproc.task",
	'.default',
	array(
		'TASK_ID' => isset($_REQUEST['TASK_ID'])? (int)$_REQUEST['TASK_ID'] : 0,
		'USER_ID' => isset($_REQUEST['USER_ID'])? (int)$_REQUEST['USER_ID'] : 0,
		'POPUP' => 'Y',
		'IFRAME' => isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y' ? 'Y' : 'N'
	)
);