<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

global $APPLICATION;
if ($request->get('IFRAME') === 'Y' && $request->get('IFRAME_TYPE') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'USE_UI_TOOLBAR' => 'Y',
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.workflow.start.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'signedDocumentType' => $request->get('signedDocumentType'),
				'signedDocumentId' => $request->get('signedDocumentId'),
			],
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		]
	);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');