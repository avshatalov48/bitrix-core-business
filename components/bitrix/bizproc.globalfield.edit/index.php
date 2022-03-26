<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;

$componentParams = [
	'MODE' => $_GET['mode'] ?? null,
	'FIELD_ID' => $_GET['fieldId'] ?? null,
	'DOCUMENT_TYPE_SIGNED' => $_GET['documentType'] ?? null,
	'SET_TITLE' => 'Y',
	'NAME' => $_GET['name'] ?? null,
	'VISIBILITY' => $_GET['visibility'] ?? null,
	'TYPES' => $_GET['availableTypes'] ?? [],
];

if ($_REQUEST['IFRAME'] === 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.globalfield.edit',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParams,
		]
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.globalfield.edit', '', $componentParams);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
