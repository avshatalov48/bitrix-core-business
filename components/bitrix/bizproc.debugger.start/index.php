<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$params = [
	'DOCUMENT_SIGNED' => $request->get('documentSigned'),
	'SET_TITLE' => 'N',
];

if ($request->get('IFRAME') === 'Y' && $request->get('IFRAME_TYPE') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.debugger.start',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $params,
			'PLAIN_VIEW' => true,
			'USE_BACKGROUND_CONTENT' => false,
		]
	);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');