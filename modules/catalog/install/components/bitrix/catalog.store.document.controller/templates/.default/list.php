<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreDocumentTable;

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

$documentType = $arResult['VARIABLES']['DOCUMENT_TYPE'] ?? null;

if (!$documentType)
{
	$accessController = AccessController::getCurrent();
	if (
		$accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::TYPE_ARRIVAL
		)
		|| $accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT
		)
	)
	{
		$documentType = 'receipt_adjustment';
	}
	elseif (
		$accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::TYPE_MOVING
		)
	)
	{
		$documentType = 'moving';
	}
	elseif (
		$accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::TYPE_DEDUCT
		)
	)
	{
		$documentType = 'deduct';
	}
	elseif (
		$accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			StoreDocumentTable::TYPE_SALES_ORDERS
		)
	)
	{
		$documentType = 'sales_order';
	}
}

$componentParams = [
	'MODE' => $documentType,
	'PATH_TO' => $arResult['PATH_TO'],
];

if ($documentType === 'sales_order')
{
	if (!Main\Loader::includeModule('crm'))
	{
		ShowError(Main\Localization\Loc::getMessage('CATALOG_STORE_DOCUMENT_CONTROLLER_LIST_CRM_NOT_FOUND'));
		return;
	}

	if ($component->isIframeMode())
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:crm.store.document.list',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => $componentParams,
				'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
				'USE_PADDING' => true,
				'USE_UI_TOOLBAR' => 'Y',
			]
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			'bitrix:crm.store.document.list',
			'',
			$componentParams
		);
	}
}
else
{
	echo $arResult['CONTRACTORS_MIGRATION_PROGRESS'];

	if ($component->isIframeMode())
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.document.list',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => $componentParams,
				'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
				'USE_PADDING' => true,
				'USE_UI_TOOLBAR' => 'Y',
			]
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.store.document.list',
			'',
			$componentParams
		);
	}
}
