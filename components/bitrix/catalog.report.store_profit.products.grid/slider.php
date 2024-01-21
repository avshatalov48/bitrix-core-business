<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

\Bitrix\Main\Loader::includeModule('catalog');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.report.store_profit.products.grid',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'STORE_ID' => (int)($request->get('storeId') ?? 0),
			'INCOMING_FILTER' => $request->getPost('filter') ?? [],
			'OPENED_FROM_REPORT' => (bool)$request->getPost('openedFromReport'),
			'BUILDER_CONTEXT' => \Bitrix\Catalog\Url\InventoryBuilder::TYPE_ID,
		],
		'USE_UI_TOOLBAR' => 'N',
	]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
