<?php

use Bitrix\Catalog\Integration\Report\Filter\StoreSaleFilter;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockQuantity;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('catalog'))
{
	ShowError('Module Catalog is not installed');

	die();
}

class CatalogReportStoreSaleProductsGridComponent extends \Bitrix\Catalog\Component\ReportProductList
{
	protected function getGridId(): string
	{
		return 'catalog_report_store_sale_products_grid';
	}

	protected function getFilterId(): string
	{
		return 'catalog_report_store_sale_products_filter';
	}

	protected function prepareProductFilter(array $productIds): array
	{
		return StoreSaleFilter::prepareProductFilter($productIds);
	}

	protected function getFilterReportIntervalFieldName(): string
	{
		return StoreSaleFilter::REPORT_INTERVAL_FIELD_NAME;
	}

	protected function getProductFilterDialogContext(): string
	{
		return 'report_store_sale_products_filter_products';
	}

	protected function getFilterFields(): array
	{
		$filterFields = parent::getFilterFields();
		$filterFields[StoreSaleFilter::REPORT_INTERVAL_FIELD_NAME] = StoreSaleFilter::getReportIntervalField();

		return $filterFields;
	}

	protected static function getEmptyStub(): string
	{
		return Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_NO_PRODUCTS');
	}

	protected function initFilterFromIncomingData(array $incomingFilter): void
	{
		$filterFields = [];
		if (isset($incomingFilter['PRODUCTS'], $incomingFilter['PRODUCTS_label']))
		{
			$filterFields['PRODUCTS'] = $incomingFilter['PRODUCTS'];
			$filterFields['PRODUCTS_label'] = $incomingFilter['PRODUCTS_label'];
		}

		foreach ($incomingFilter as $key => $value)
		{
			if (preg_match('/^' . $this->getFilterReportIntervalFieldName() . '_[a-z]*$/', $key))
			{
				$filterFields[$key] = $value;
			}
		}

		if (count($filterFields) > 0)
		{
			$this->setFilterFields($filterFields);
		}
	}

	protected function getReceivedQuantityData(int $storeId, array $formattedFilter): array
	{
		return StoreStockQuantity::getReceivedQuantityForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getOutgoingQuantityData(int $storeId, array $formattedFilter): array
	{
		return StoreStockQuantity::getOutgoingQuantityForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getAmountSoldData(int $storeId, array $formattedFilter): array
	{
		return StoreStockSale::getProductsSoldAmountForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getGridColumns(): array
	{
		return [
			[
				'id' => 'PRODUCT_ID',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_PRODUCT_COLUMN_MSGVER_1'),
				'sort' => 'PRODUCT_ID',
				'default' => true,
				'type' => 'custom',
				'sticked' => true,
				'width' => 400,
				'resizeable' => false,
			],
			[
				'id' => 'STARTING_QUANTITY',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_STARTING_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_STARTING_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'RECEIVED_QUANTITY',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_RECEIVED_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_RECEIVED_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'AMOUNT_SOLD',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_AMOUNT_SOLD_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_AMOUNT_SOLD_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'AMOUNT',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_AMOUNT_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_AMOUNT_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
		];
	}
}
