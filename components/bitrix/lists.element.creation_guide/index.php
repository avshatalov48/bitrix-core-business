<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$iBlockTypeId = $request->get('iBlockTypeId');
$iBlockId = $request->get('iBlockId');
$fillConstantsUrl = $request->get('fillConstantsUrl');

$params = [
	'IBLOCK_TYPE_ID' => is_string($iBlockTypeId) ? $iBlockTypeId : '',
	'IBLOCK_ID' => is_numeric($iBlockId) ? (int)$iBlockId : 0,
	'FILL_CONSTANTS_URL' => is_string($fillConstantsUrl) ? $fillConstantsUrl : '',
];

if ($request->get('IFRAME') === 'Y' && $request->get('IFRAME_TYPE') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:lists.element.creation_guide',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $params,
			'PLAIN_VIEW' => true,
			'USE_BACKGROUND_CONTENT' => false,
		],
	);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
