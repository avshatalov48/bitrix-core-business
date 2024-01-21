<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Document\Type\StoreDocumentArrivalTable;
use Bitrix\Catalog\Document\Type\StoreDocumentDeductTable;
use Bitrix\Catalog\Document\Type\StoreDocumentMovingTable;
use Bitrix\Catalog\Document\Type\StoreDocumentStoreAdjustmentTable;
use Bitrix\Main\UserField\Types\FileType;

\CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.list');

if (empty($arResult['GRID']['ROWS']))
{
	return;
}

global $USER_FIELD_MANAGER;
global $APPLICATION;

$ufFields = [];

$mode = $arResult['MODE'];
if ($mode === \CatalogStoreDocumentListComponent::ARRIVAL_MODE)
{
	$ufFields = $USER_FIELD_MANAGER->getUserFields(StoreDocumentArrivalTable::getUfId(), 0, LANGUAGE_ID);
	$ufFields = array_merge(
		$ufFields,
		$USER_FIELD_MANAGER->getUserFields(StoreDocumentStoreAdjustmentTable::getUfId(), 0, LANGUAGE_ID)
	);
}
elseif ($mode === \CatalogStoreDocumentListComponent::MOVING_MODE)
{
	$ufFields = $USER_FIELD_MANAGER->getUserFields(StoreDocumentMovingTable::getUfId(), 0, LANGUAGE_ID);
}
elseif ($mode === \CatalogStoreDocumentListComponent::DEDUCT_MODE)
{
	$ufFields = $USER_FIELD_MANAGER->getUserFields(StoreDocumentDeductTable::getUfId(), 0, LANGUAGE_ID);
}

$ufCodes = array_keys($ufFields);

foreach ($arResult['GRID']['ROWS'] as $key => $row)
{
	foreach ($ufCodes as $ufCode)
	{
		if (!isset($row['columns'][$ufCode]))
		{
			continue;
		}

		ob_start();

		$userField = $ufFields[$ufCode];
		$userField['VALUE'] = $row['data'][$ufCode];

		$params = [
			"arUserField" => $userField,
			"TEMPLATE" => '',
			"LAZYLOAD" => 'N'
		];

		if ($userField['USER_TYPE_ID'] === FileType::USER_TYPE_ID)
		{
			$params['FILE_MAX_WIDTH'] = 70;
			$params['FILE_MAX_HEIGHT'] = 70;
			$params['FILE_SHOW_POPUP'] = 'Y';
		}

		$APPLICATION->includeComponent(
			"bitrix:system.field.view",
			$ufFields[$ufCode]['USER_TYPE']['USER_TYPE_ID'],
			$params,
			null,
			[ 'HIDE_ICONS' => 'Y' ]
		);

		$value = ob_get_clean();
		$arResult['GRID']['ROWS'][$key]['columns'][$ufCode] = $value;
	}
}
