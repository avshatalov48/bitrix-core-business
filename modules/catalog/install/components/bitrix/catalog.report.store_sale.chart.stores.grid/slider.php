<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

\Bitrix\Main\Loader::includeModule('catalog');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$componentParams = [
	'FILTER' => [
		'STORES' => $request->get('storeIds'),
		'PRODUCTS' => $request->get('productIds'),
	],
	'CURRENCY' => $request->get('currency'),
];

if ($request->get('reportFrom') && $request->get('reportTo'))
{
	$componentParams['FILTER']['REPORT_INTERVAL'] = [
		'FROM' => $request->get('reportFrom'),
		'TO' => $request->get('reportTo'),
	];
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.report.store_sale.chart.stores.grid',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParams,
		'USE_UI_TOOLBAR' => 'N',
	]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
