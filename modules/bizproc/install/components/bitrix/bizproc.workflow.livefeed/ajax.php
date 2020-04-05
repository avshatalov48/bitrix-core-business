<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

global $APPLICATION;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$componentResult = $APPLICATION->includeComponent(
	'bitrix:bizproc.workflow.livefeed',
	'',
	array(
		'WORKFLOW_ID' => $_REQUEST['WORKFLOW_ID'],
		'NOWRAP' => 'Y'
	),
	null,
	array('HIDE_ICONS' => 'Y')
);
echo $componentResult['MESSAGE'];
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');