<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

$request = Main\Context::getCurrent()->getRequest();

$documentId = (int)($arResult['VARIABLES']['DOCUMENT_ID'] ?? 0);
$preselectedProductId = $request->get('preselectedProductId') ? (int)$request->get('preselectedProductId') : null;

global $APPLICATION;

Main\UI\Extension::load('ui.notification');

if (!Main\Loader::includeModule('crm'))
{
	ShowError(Main\Localization\Loc::getMessage('CATALOG_STORE_DOCUMENT_CONTROLLER_SHIPMENT_CRM_NOT_FOUND'));
	return;
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:crm.store.document.detail',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'DOCUMENT_ID' => $documentId,
			'DOCUMENT_TYPE' => $request->get('DOCUMENT_TYPE'),
			'PATH_TO' => $arResult['PATH_TO'],
			'CONTEXT' => $request->get('context') ?? [],
			'PRESELECTED_PRODUCT_ID' => $preselectedProductId,
		],
		'RELOAD_GRID_AFTER_SAVE' => ($request->get('context') || $request->get('documentId')) ? false : 'all',
		'USE_UI_TOOLBAR' => 'Y',
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => '/shop/documents/sales_order/',
		'CLOSE_AFTER_SAVE' => $request->get('closeOnSave') && $request->get('closeOnSave') === 'Y',
	]
);
