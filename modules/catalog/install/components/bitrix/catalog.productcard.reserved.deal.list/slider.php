<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

\Bitrix\Main\Loader::includeModule('catalog');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

/* @global $APPLICATION */
$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.productcard.reserved.deal.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'PRODUCT_ID' => $request->get('productId'),
			'STORE_ID' => $request->get('storeId'),
		],
		'USE_UI_TOOLBAR' => 'Y',
	]
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
