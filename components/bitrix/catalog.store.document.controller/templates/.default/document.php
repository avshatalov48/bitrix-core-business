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

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.document.detail',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'DOCUMENT_ID' => $documentId,
			'DOCUMENT_TYPE' => $request->get('DOCUMENT_TYPE'),
			'PATH_TO' => $arResult['PATH_TO'],
			'PRESELECTED_PRODUCT_ID' => $preselectedProductId,
		],
		'RELOAD_GRID_AFTER_SAVE' => 'all',
		'USE_UI_TOOLBAR' => 'Y',
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => '/shop/documents/',
		'CLOSE_AFTER_SAVE' => $request->get('closeOnSave') && $request->get('closeOnSave') === 'Y',
	]
);
