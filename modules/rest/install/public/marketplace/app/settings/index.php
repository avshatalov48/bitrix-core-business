<?php

use Bitrix\Main\UI\Extension;
use Bitrix\Rest\FormConfig\ConfigProvider;
use Bitrix\Rest\FormConfig\ConfigStoreRequest;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Extension::load(['sidepanel']);
$configProvider = new ConfigProvider(new ConfigStoreRequest('config'));

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.app.settings',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'CONFIG' => $configProvider->get(),
		],
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'USE_BACKGROUND_CONTENT' => false,
	]
);


require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');