<?php

use Bitrix\Catalog\Integration\Report\Filter\StoreProfitFilter;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockQuantity;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogReportStoreProfitProductsGridComponent extends \Bitrix\Catalog\Component\ReportProductList
{

	protected function checkModules(): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('currency'))
		{
			$this->arResult['ERROR_MESSAGES'][] = 'Module Currency is not installed';

			return false;
		}

		return parent::checkModules();
	}

	protected function getGridId(): string
	{
		return 'catalog_report_store_profit_products_grid';
	}

	protected function getFilterId(): string
	{
		return 'catalog_report_store_profit_products_filter';
	}

	protected static function getStoreFilterContext(): string
	{
		return 'report_store_profit_filter_stores';
	}

	protected function prepareProductFilter(array $productIds): array
	{
		return StoreProfitFilter::prepareProductFilter($productIds);
	}

	protected function getFilterReportIntervalFieldName(): string
	{
		return StoreProfitFilter::REPORT_INTERVAL_FIELD_NAME;
	}

	protected function getProductFilterDialogContext(): string
	{
		return 'report_store_sale_products_filter_products';
	}

	protected function getFilterFields(): array
	{
		$filterFields = parent::getFilterFields();
		$filterFields[StoreProfitFilter::REPORT_INTERVAL_FIELD_NAME] = StoreProfitFilter::getReportIntervalField();

		if ($this->isAllStoresGrid())
		{
			$filterFields['STORE_ID'] = [
				'id' => 'STORE_ID',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_FILTER_STORES_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => static::getStoreFilterContext(),
						'entities' => [
							[
								'id' => 'store',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							]
						],
						'dropdownMode' => true
					],
				],
			];
		}

		return $filterFields;
	}

	protected static function getEmptyStub(): string
	{
		return Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_NO_PRODUCTS');
	}

	protected function prepareFilterIncomingData(array $incomingFilter): array
	{
		$filter = parent::prepareFilterIncomingData($incomingFilter);

		foreach ($incomingFilter as $key => $value)
		{
			if (preg_match('/^' . $this->getFilterReportIntervalFieldName() . '_[a-z]*$/', $key))
			{
				$filter[$key] = $value;
			}
		}

		if (isset($incomingFilter['STORES']))
		{
			$filter['STORE_ID'] = $incomingFilter['STORES'];
		}

		return $filter;
	}

	protected function getReceivedQuantityData(int $storeId, array $formattedFilter): array
	{
		if ($this->isAllStoresGrid())
		{
			return StoreStockQuantity::getReceivedQuantityForProducts($formattedFilter);
		}

		return StoreStockQuantity::getReceivedQuantityForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getOutgoingQuantityData(int $storeId, array $formattedFilter): array
	{
		if ($this->isAllStoresGrid())
		{
			return StoreStockQuantity::getOutgoingQuantityForProducts($formattedFilter);
		}

		return StoreStockQuantity::getOutgoingQuantityForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getPricesSoldData(int $storeId, array $formattedFilter): array
	{
		if ($this->isAllStoresGrid())
		{
			return StoreStockSale::getProductsSoldPricesForProducts($formattedFilter);
		}

		return StoreStockSale::getProductsSoldPricesForProductsOnStore($storeId, $formattedFilter);
	}

	protected function getAmountSoldData(int $storeId, array $formattedFilter): array
	{
		if ($this->isAllStoresGrid())
		{
			return StoreStockSale::getProductsSoldAmountForProducts($formattedFilter);
		}

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
				'id' => 'TOTAL_SOLD',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_TOTAL_SOLD_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_TOTAL_SOLD_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'COST_PRICE',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_COST_PRICE_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_COST_PRICE_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 300,
			],
			[
				'id' => 'TOTAL_COST_PRICE',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_TOTAL_COST_PRICE_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_TOTAL_COST_PRICE_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'PROFIT',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_PROFIT_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_PROFIT_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
			[
				'id' => 'PROFITABILITY',
				'name' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_PROFITABILITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_PRODUCTS_REPORT_GRID_PROFITABILITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'align' => 'right',
				'width' => 200,
			],
		];
	}

	protected function getGridRows(): ?array
	{
		$productData = $this->getProductData();
		if (!$productData)
		{
			return null;
		}

		$rows = [];

		$this->catalogData = $this->loadCatalog(array_column($productData, 'PRODUCT_ID'));

		$formattedFilter = $this->getFormattedFilter();
		$receivedQuantityData = $this->getReceivedQuantityData($this->storeId, $formattedFilter);
		$outgoingQuantityData = $this->getOutgoingQuantityData($this->storeId, $formattedFilter);
		$amountSoldData = $this->getAmountSoldData($this->storeId, $formattedFilter);
		$pricesSoldData = $this->getPricesSoldData($this->storeId, $formattedFilter);

		$formattedPricesSoldData = [];
		$baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
		foreach ($pricesSoldData as $productId => $currencyPrices)
		{
			$formattedPricesSoldData[$productId] ??= [
				'TOTAL_SOLD' => 0,
				'TOTAL_COST_PRICE' => 0,
				'PROFIT' => 0,
			];
			foreach ($currencyPrices as $currency => $prices)
			{
				$purchasingPrice = $prices['COST_PRICE'];
				$totalSold = $prices['TOTAL_SOLD'];
				if ($currency !== $baseCurrency)
				{
					$purchasingPrice = CCurrencyRates::ConvertCurrency($purchasingPrice, $currency, $baseCurrency);
					$totalSold = CCurrencyRates::ConvertCurrency($totalSold, $currency, $baseCurrency);
				}
				$profit = $totalSold - $purchasingPrice;
				$formattedPricesSoldData[$productId]['TOTAL_COST_PRICE'] += $purchasingPrice;
				$formattedPricesSoldData[$productId]['TOTAL_SOLD'] += $totalSold;
				$formattedPricesSoldData[$productId]['PROFIT'] += $profit;
			}
		}

		$receivedQuantityAmountDifferenceData = [];
		$outgoingQuantityAmountDifferenceData = [];
		$amountSoldAmountDifferenceData = [];

		if (!empty($formattedFilter['REPORT_INTERVAL']))
		{
			$differenceFilter = $formattedFilter;
			$currentTime = new DateTime();
			$filterTimeTo = new DateTime($differenceFilter['REPORT_INTERVAL']['TO']);
			if ($currentTime > $filterTimeTo)
			{
				$differenceFilter['REPORT_INTERVAL']['FROM'] = $differenceFilter['REPORT_INTERVAL']['TO'];
				$differenceFilter['REPORT_INTERVAL']['TO'] = (new DateTime())->toString();
				$receivedQuantityAmountDifferenceData = $this->getReceivedQuantityData($this->storeId, $differenceFilter);
				$outgoingQuantityAmountDifferenceData = $this->getOutgoingQuantityData($this->storeId, $differenceFilter);
				$amountSoldAmountDifferenceData = $this->getAmountSoldData($this->storeId, $differenceFilter);
			}
		}

		foreach ($productData as $item)
		{
			$receivedQuantityAmountDifference = (float)($receivedQuantityAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$outgoingQuantityAmountDifference = (float)($outgoingQuantityAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$amountSoldAmountDifference = (float)($amountSoldAmountDifferenceData[$item['PRODUCT_ID']] ?? 0);
			$item['AMOUNT'] =
				$item['AMOUNT']
				- $receivedQuantityAmountDifference
				+ $outgoingQuantityAmountDifference
				+ $amountSoldAmountDifference
			;

			$receivedQuantity = (float)($receivedQuantityData[$item['PRODUCT_ID']] ?? 0);
			$outgoingQuantity = (float)($outgoingQuantityData[$item['PRODUCT_ID']] ?? 0);
			$amountSold = (float)($amountSoldData[$item['PRODUCT_ID']] ?? 0);
			$item['STARTING_QUANTITY'] = $item['AMOUNT'] - $receivedQuantity + $outgoingQuantity + $amountSold;
			$item['RECEIVED_QUANTITY'] = (float)($receivedQuantityData[$item['PRODUCT_ID']] ?? 0);
			$item['AMOUNT_SOLD'] = (float)($amountSoldData[$item['PRODUCT_ID']] ?? 0);
			$item['QUANTITY'] = $item['AMOUNT'] - (float)$item['QUANTITY_RESERVED'];
			$productPrices = $formattedPricesSoldData[$item['PRODUCT_ID']] ?? [
				'TOTAL_SOLD' => 0,
				'TOTAL_COST_PRICE' => 0,
				'PROFIT' => 0,
			];
			$item += $productPrices;
			$profitability = null;
			if ($productPrices['TOTAL_SOLD'] > 0 && $productPrices['TOTAL_COST_PRICE'] > 0)
			{
				$profitability = round($productPrices['PROFIT'] / $productPrices['TOTAL_COST_PRICE'], 4) * 100;
			}
			$item['PROFITABILITY'] = $profitability;
			$item['COST_PRICE'] = 0;
			if ($item['TOTAL_COST_PRICE'] > 0 && $item['AMOUNT_SOLD'] > 0)
			{
				$item['COST_PRICE'] = $item['TOTAL_COST_PRICE'] / $item['AMOUNT_SOLD'];
			}

			$rows[] = [
				'id' => $item['ID'],
				'data' => $item,
				'columns' => $this->prepareItemColumn($item),
			];
		}

		return $rows;
	}


	protected function prepareItemColumn(array $item): array
	{
		$column = parent::prepareItemColumn($item);

		$moneyFields = ['TOTAL_SOLD', 'COST_PRICE', 'TOTAL_COST_PRICE', 'PROFIT'];
		$baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
		foreach ($moneyFields as $moneyField)
		{
			$column[$moneyField] = CCurrencyLang::CurrencyFormat($column[$moneyField], $baseCurrency);
		}

		$column['PROFITABILITY'] = $item['PROFITABILITY'] !== null ? "{$item['PROFITABILITY']}%" : '-';

		return $column;
	}
}
