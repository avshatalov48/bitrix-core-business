<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$params = [
	'MODULE_ID' => $request->get('moduleId'),
	'ENTITY' => $request->get('entity'),
	'DOCUMENT_TYPE' => $request->get('documentType'),
	'DOCUMENT_ID' => $request->get('documentId'),
	'TEMPLATE_ID' => is_numeric($request->get('templateId')) ? $request->get('templateId') : null,
	'AUTO_EXECUTE_TYPE' => is_numeric($request->get('autoExecuteType')) ? $request->get('autoExecuteType') : null,
	'SIGNED_DOCUMENT_TYPE' => $request->get('signedDocumentType'),
	'SIGNED_DOCUMENT_ID' => $request->get('signedDocumentId'),
	'SET_TITLE' => 'N',
];

$templateName = 'slider';

if ($request->get('IFRAME') === 'Y' && $request->get('IFRAME_TYPE') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.workflow.start',
			'POPUP_COMPONENT_TEMPLATE_NAME' => $templateName,
			'POPUP_COMPONENT_PARAMS' => $params,
			'PLAIN_VIEW' => true,
			'USE_BACKGROUND_CONTENT' => false,
		]
	);
}
else
{
	$APPLICATION->IncludeComponent('bitrix:bizproc.workflow.start', $templateName, $params);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');


