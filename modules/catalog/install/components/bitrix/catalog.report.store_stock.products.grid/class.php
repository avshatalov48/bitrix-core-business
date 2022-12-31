<?php

use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
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

class CatalogReportStoreStockProductsGridComponent extends \Bitrix\Catalog\Component\ReportProductList
{
	protected string $reportFilterClass = \Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter::class;

	protected function getGridId(): string
	{
		return 'catalog_report_store_stock_products_grid';
	}

	protected function getFilterId(): string
	{
		return 'catalog_report_store_stock_products_filter';
	}

	protected function prepareProductFilter(array $productIds): array
	{
		return StoreStockFilter::prepareProductFilter($productIds);
	}

	protected function getProductFilterDialogContext(): string
	{
		return 'report_store_stock_products_filter_products';
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
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_PRODUCT_COLUMN'),
				'sort' => 'PRODUCT_ID',
				'default' => true,
				'type' => 'custom',
			],
			[
				'id' => 'AMOUNT',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_AMOUNT_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_AMOUNT_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'QUANTITY_RESERVED',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_RESERVED_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_RESERVED_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'QUANTITY',
				'name' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_PRODUCTS_REPORT_GRID_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
		];
	}
}
