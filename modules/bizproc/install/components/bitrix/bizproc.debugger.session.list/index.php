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
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.debugger.session.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'documentSigned' => $request->get('documentSigned'),
			],
		]
	);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');