<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$cmpParams = [
	'SCRIPT_ID' => $_GET['scriptId'] ?? 0,
	'DOCUMENT_TYPE_SIGNED' => $_GET['documentType'] ?? null,
	'PLACEMENT' => $_GET['placement'] ?? null,
	'SET_TITLE' => 'Y',
];

if ($_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'USE_UI_TOOLBAR' => 'Y',
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.script.edit',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $cmpParams,
			'USE_PADDING' => false,
			//'PLAIN_VIEW' => true,
		]
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.script.edit', '', $cmpParams);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');