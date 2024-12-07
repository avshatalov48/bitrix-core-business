<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

global $APPLICATION;
if ($request['IFRAME'] === 'Y' && $request['IFRAME_TYPE'] === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.workflow.timeline.slider',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'workflowId' => $request->getQuery('workflowId'),
				'taskId' => $request->getQuery('taskId'),
			],
		]
	);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
