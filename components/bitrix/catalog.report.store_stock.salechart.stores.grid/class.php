<?php

use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Currency\CurrencyManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

final class CatalogReportStoreStockSaleChartStoresGrid
	extends CBitrixComponent
	implements Errorable
{

	private const GRID_ID = 'catalog_report_store_stock_sale_stores_grid';

	private $currency;

	use ErrorableImplementation;

	public function executeComponent()
	{
		if ($this->checkModules())
		{
			$this->initGrid();
			$this->includeComponentTemplate();
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}
	}

	private function checkModules(): bool
	{
		$validateResult = true;

		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');
			$validateResult = false;
		}

		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "sale" is not installed.');
			$validateResult = false;
		}

		if (!Loader::includeModule('currency'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "sale" is not installed.');
			$validateResult = false;
		}


		return $validateResult;
	}

	private function initGrid(): void
	{
		$grid = [];

		$this->initCurrency();

		$grid['ID'] = self::GRID_ID;
		$grid['COLUMNS'] = $this->getGridColumns();
		$grid['ROWS'] = $this->getGridRows();

		$this->arResult['GRID'] = $grid;
	}

	private function initCurrency(): void
	{
		if ($this->arParams['CURRENCY'])
		{
			if (CurrencyManager::isCurrencyExist($this->arParams['CURRENCY']))
			{
				$this->currency = $this->arParams['CURRENCY'];
			}
		}

		if (!isset($this->currency))
		{
			$this->currency = CurrencyManager::getBaseCurrency();
		}
	}

	private function getGridColumns(): array
	{
		$columns = [
			[
				'id' => 'STORE_NAME',
				'name' => Loc::getMessage('STORE_STOCK_SALECHART_STORES_GRID_COLUMN_STORE_NAME'),
				'sort' => 'STORE_NAME',
				'default' => true,
			],

			[
				'id' => 'SUM_STORED',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('STORE_STOCK_SALECHART_STORES_GRID_COLUMN_SUM_STORED'),
				'sort' => 'SUM_STORED',
				'default' => true,
			],

			[
				'id' => 'SUM_SOLD',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('STORE_STOCK_SALECHART_STORES_GRID_COLUMN_SUM_SOLD'),
				'sort' => 'SUM_SOLD',
				'default' => true,
			],

			[
				'id' => 'SUM_SOLD_PERCENT',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('STORE_STOCK_SALECHART_STORES_GRID_COLUMN_SUM_SOLD_PERCENT'),
				'sort' => 'SUM_SOLD_PERCENT',
				'default' => true,
			],

		];

		return $columns;
	}

	private function getGridRows(): array
	{
		$storesData = StoreStockSale::getStoreStockSaleData(false, $this->arParams['FILTER']);
		$rows = [];

		$storeIds = array_column($storesData, 'STORE_ID');
		$storeNames = $this->getStoreNames($storeIds);

		foreach ($storesData as $storeData)
		{
			$storeData['STORE_NAME'] = $storeNames[$storeData['STORE_ID']];
			if (empty($storeData['STORE_NAME']))
			{
				$storeData['STORE_NAME'] = Loc::getMessage('STORE_STOCK_SALECHART_STORES_GRID_DEFAULT_STORE_NAME');
			}

			$rows[] = [
				'data' => $this->prepareRow($storeData),
			];
		}

		return $rows;
	}

	private function prepareRow($columnData)
	{
		return [
			'STORE_NAME' => htmlspecialcharsbx($columnData['STORE_NAME']),
			'SUM_STORED' => \CCurrencyLang::CurrencyFormat($columnData['SUM_STORED'], $this->currency, true),
			'SUM_SOLD' => \CCurrencyLang::CurrencyFormat($columnData['SUM_SOLD'], $this->currency, true),
			'SUM_SOLD_PERCENT' => round($columnData['SUM_SOLD_PERCENT'], 2) . '%',
		];
	}

	private function getStoreNames($storeIds): array
	{
		$storesInfo = StoreTable::getList([
			'select' => ['ID', 'TITLE'],
			'filter' => [
				'=ID' => $storeIds,
				'ACTIVE' => 'Y',
			],
		])->fetchAll();

		return array_column($storesInfo, 'TITLE', 'ID');
	}

	/**
	 * Show all errors from errorCollection
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}
}