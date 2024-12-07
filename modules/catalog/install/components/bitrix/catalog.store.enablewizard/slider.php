<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetPageProperty('BodyClass', 'catalog-warehouse-master-clear__wrapper');

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.enablewizard',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [],
		'PLAIN_VIEW' => true,
		'USE_PADDING' => true,
		'USE_BACKGROUND_CONTENT' => false,
	]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
