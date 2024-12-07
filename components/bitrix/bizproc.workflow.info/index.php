<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

/** @var CMain $APPLICATION */

$cmpParams = [
	'WORKFLOW_ID' => $_GET['workflow'] ?? null,
	'TASK_ID' => $_GET['task'] ?? null,
	'USER_ID' => $_GET['user'] ?? null,
	'SET_TITLE' => 'Y',
];

if (($_REQUEST['IFRAME'] ?? '') === 'Y' && ($_REQUEST['IFRAME_TYPE'] ?? '') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		array(
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.workflow.info',
			'POPUP_COMPONENT_TEMPLATE_NAME' => 'slider',
			'POPUP_COMPONENT_PARAMS' => $cmpParams,
			'PLAIN_VIEW' => true,
			'USE_PADDING' => false,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.workflow.info', 'slider', $cmpParams);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');