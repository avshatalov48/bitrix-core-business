<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.warehouse.master.clear',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'MODE' => $request->get('mode')
		],
		'PLAIN_VIEW' => true,
		"USE_PADDING" => false,
	]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
