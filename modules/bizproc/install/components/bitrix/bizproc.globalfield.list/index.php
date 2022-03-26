<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

global $APPLICATION;

$componentParams = [
	'DOCUMENT_TYPE_SIGNED' => $_GET['documentType'] ?? null,
	'MODE' => $_GET['mode'] ?? null,
	'SET_TITLE' => 'Y',
];

if ($_REQUEST['IFRAME'] === 'Y' && $_REQUEST['IFRAME_TYPE'] === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'USE_UI_TOOLBAR' => 'Y',
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.globalfield.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParams
		]
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.globalfield.list', '', $componentParams);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');