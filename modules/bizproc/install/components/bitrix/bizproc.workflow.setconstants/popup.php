<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.setconstants",'.default',
	array('ID' => (int)$_REQUEST['ID'], 'AJAX_RESPONSE' => 'Y'));