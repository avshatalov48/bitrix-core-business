<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (isset($request['documentSigned']))
{
	$unsignedDocument = CBPDocument::unSignParameters($request['documentSigned']);
}

global $APPLICATION;
if ($request['IFRAME'] === 'Y' && $request['IFRAME_TYPE'] === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.automation.scheme',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'documentType' => $unsignedDocument[0] ?? null,
				'documentCategory' => $unsignedDocument[1] ?? null,
				'templateStatus' => $request['templateStatus'],
				'action' => $request['action'],
				'robotNames' => $request['selectedRobots'],
				'triggerNames' => $request['selectedTriggers'],
			],
		]
	);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
