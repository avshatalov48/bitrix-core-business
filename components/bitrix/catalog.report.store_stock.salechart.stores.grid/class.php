<?php

use Bitrix\Catalog\Integration\Report\Handler\Chart\ChartStoreInfo;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Catalog\Integration\Report\Handler\Chart\StoreInfoCombiner\StoreWithProductsInfoCombiner;
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
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
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
			$this->errorCollection->add([new \Bitrix\Main\Error('Module "catalog" is not installed.')]);
			$validateResult = false;
		}

		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection->add([new \Bitrix\Main\Error('Module "sale" is not installed.')]);
			$validateResult = false;
		}

		if (!Loader::includeModule('currency'))
		{
			$this->errorCollection->add([new \Bitrix\Main\Error('Module "currency" is not installed.')]);
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
		];

		return $columns;
	}

	private function getGridRows(): array
	{
		$storeInfo = new ChartStoreInfo(new StoreWithProductsInfoCombiner());
		$storeInfo->accumulate('SUM_STORED', ...StoreStockSale::getReservedData($this->arParams['FILTER']));

		$rows = [];
		foreach ($storeInfo->getCalculatedColumns() as $store)
		{
			$rows[] = [
				'data' => $this->prepareRow($store),
			];
		}

		return $rows;
	}

	private function prepareRow($columnData)
	{
		return [
			'STORE_NAME' => htmlspecialcharsbx($columnData['TITLE']),
			'SUM_STORED' => \CCurrencyLang::CurrencyFormat($columnData['SUM_STORED'], $this->currency, true),
		];
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