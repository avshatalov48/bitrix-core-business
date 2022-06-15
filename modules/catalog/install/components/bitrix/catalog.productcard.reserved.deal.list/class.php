<?php

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class ReservedDealListComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['PRODUCT_ID'] = (int)$arParams['PRODUCT_ID'];
		$arParams['STORE_ID'] = (int)$arParams['STORE_ID'];
		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if (!\Bitrix\Main\Loader::includeModule('crm'))
		{
			ShowError(Loc::getMessage('CRM_MODULE_NOT_INSTALLED'));
			return;
		}

		$productId = $this->arParams['PRODUCT_ID'];
		$productFields = ProductTable::getRow([
			'select' => ['ID', 'TYPE', 'NAME' => 'IBLOCK_ELEMENT.NAME'],
			'filter' => ['=ID' => $productId],
		]);
		if (!$productFields)
		{
			ShowError(Loc::getMessage('PRODUCT_NOT_FOUND'));
			return;
		}

		$dealsFilter = [
			'=IS_PRODUCT_RESERVED' => 'Y',
		];

		$productType = (int)$productFields['TYPE'];
		if ($productType === ProductTable::TYPE_SKU)
		{
			$offerIds = \CCatalogSku::getOffersList($productId, 0, [], ['ID']);
			$offerIds = array_column($offerIds[$productId], 'ID');
			if (!empty($offerIds))
			{
				$dealsFilter['=PRODUCT_ROW_PRODUCT_ID'] = $offerIds;
			}
		}
		else
		{
			$dealsFilter['=PRODUCT_ROW_PRODUCT_ID'] = $productId;
		}

		if ($this->arParams['STORE_ID'] > 0)
		{
			$dealsFilter['=RESERVE_STORE_ID'] = $this->arParams['STORE_ID'];
		}

		$this->arResult['DEALS_FILTER'] = $dealsFilter;
		$this->arResult['PRODUCT_NAME'] = $productFields['NAME'];

		$this->includeComponentTemplate();
	}
}
