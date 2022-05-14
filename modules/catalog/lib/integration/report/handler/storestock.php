<?php

namespace Bitrix\Catalog\Integration\Report\Handler;

use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Report\VisualConstructor\IReportMultipleData;

class StoreStock extends BaseHandler implements IReportMultipleData
{

	private const MAX_CHART_COLUMNS_COUNT = 5;
	private const MAX_STORES_LIST_CHARS = 25;

	public function prepare()
	{
		// adding a new runtime reference field with right join
		// in order to select all the stores and not just the
		// ones that have corresponding entries in b_catalog_store_product
		$queryParams = [
			'select' => [
				'TITLE' => 'STORE_TMP.TITLE',
				'TMP_STORE_ID' => 'STORE_TMP.ID',
				'SORT' => 'STORE_TMP.SORT',
				'AMOUNT_SUM',
				'QUANTITY_RESERVED_SUM',
				'MEASURE_ID' => 'PRODUCT.MEASURE',
			],
			'filter' => [
				'=STORE_TMP.ACTIVE' => 'Y',
			],
			'group' => ['TMP_STORE_ID', 'MEASURE_ID'],
			'order' => ['SORT'],
			'runtime' => [
				new ExpressionField('AMOUNT_SUM', 'SUM(AMOUNT)'),
				new ExpressionField('QUANTITY_RESERVED_SUM', 'SUM(catalog_store_product.QUANTITY_RESERVED)'),
				(new Reference(
					'STORE_TMP',
					StoreTable::class,
					Join::on('this.STORE_ID', 'ref.ID')
				))->configureJoinType(Join::TYPE_RIGHT),
			],
		];

		$userFilterParameters = $this->getFilterParameters();
		$chartFilters = [];

		if (!empty($userFilterParameters['STORES']) && is_array($userFilterParameters['STORES']))
		{
			$queryParams['filter']['=TMP_STORE_ID'] = $userFilterParameters['STORES'];

			$chartFilters['STORES'] = $userFilterParameters['STORES'];
		}

		if (!empty($userFilterParameters['PRODUCTS']) && is_array($userFilterParameters['PRODUCTS']))
		{
			$queryParams['filter']['=PRODUCT_ID'] = StoreStockFilter::prepareProductFilter($userFilterParameters['PRODUCTS']);
			$queryParams['filter'][] = [
				'LOGIC' => 'OR',
				'!=AMOUNT' => 0,
				'!=QUANTITY_RESERVED' => 0,
			];

			$chartFilters['PRODUCTS'] = $userFilterParameters['PRODUCTS'];
		}

		if
		(
			!empty($userFilterParameters['REPORT_INTERVAL_from'])
			&&!empty($userFilterParameters['REPORT_INTERVAL_to'])
		)
		{
			$chartFilters['REPORT_INTERVAL'] = [
				'FROM' => $userFilterParameters['REPORT_INTERVAL_from'],
				'TO' => $userFilterParameters['REPORT_INTERVAL_to'],
			];
		}

		$reportData = [
			'chart' => $this->prepareChartData($chartFilters),
		];

		$storeTotals = StoreProductTable::getList($queryParams)->fetchAll();
		if (!empty($storeTotals))
		{
			$storeTotals = $this->prepareStoreTotals($storeTotals);
			$reportData['items'] = $storeTotals;
			$reportData['overall'] = $this->prepareOverallTotals($storeTotals);
		}

		return $reportData;
	}

	private function prepareStoreTotals(array $storeTotals): array
	{
		$preparedTotals = $this->initializePreparedTotals($storeTotals);

		foreach ($storeTotals as $key => $entry)
		{
			$amountSum = (float)$entry['AMOUNT_SUM'];
			$quantityReservedSum = (float)$entry['QUANTITY_RESERVED_SUM'];

			if ($amountSum === 0.0 && $quantityReservedSum === 0.0)
			{
				continue;
			}

			$entry['QUANTITY'] = $amountSum - $quantityReservedSum;

			$storeId = $entry['TMP_STORE_ID'];
			$measureId = $entry['MEASURE_ID'];

			if (!$measureId)
			{
				$measureId = $this->getDefaultMeasure();
			}

			if (!isset($preparedTotals[$storeId]['TOTALS'][$measureId]))
			{
				$preparedTotals[$storeId]['TOTALS'][$measureId] = [
					'AMOUNT_SUM' => 0,
					'QUANTITY_RESERVED_SUM' => 0,
					'QUANTITY' => 0,
				];
			}

			$preparedTotals[$storeId]['TOTALS'][$measureId]['AMOUNT_SUM'] += $amountSum;
			$preparedTotals[$storeId]['TOTALS'][$measureId]['QUANTITY_RESERVED_SUM'] += $quantityReservedSum;
			$preparedTotals[$storeId]['TOTALS'][$measureId]['QUANTITY'] += $entry['QUANTITY'];
		}

		return $preparedTotals;
	}

	private function initializePreparedTotals(array $storeTotals): array
	{
		$preparedTotals = [];
		foreach ($storeTotals as $entry)
		{
			$storeId = $entry['TMP_STORE_ID'];
			if (isset($preparedTotals[$storeId]))
			{
				continue;
			}

			$preparedTotals[$storeId] = [
				'TITLE' => $entry['TITLE'],
				'STORE_ID' => $storeId,
				'TOTALS' => []
			];
		}

		return $preparedTotals;
	}

	private function getDefaultMeasure(): int
	{
		static $defaultMeasure = 0;

		if (empty($defaultMeasure))
		{
			$fetchedMeasure = \CCatalogMeasure::getList([], ['=IS_DEFAULT' => 'Y'])->Fetch();
			if ($fetchedMeasure)
			{
				$defaultMeasure = (int)$fetchedMeasure['ID'];
			}
			else
			{
				$defaultMeasure = 0;
			}
		}

		return $defaultMeasure;
	}

	public function getMultipleData()
	{
		return $this->getCalculatedData();
	}

	public function getMultipleDemoData()
	{
		return [];
	}

	private function prepareOverallTotals(array $storeTotals): array
	{
		$overallTotals = [];

		foreach ($storeTotals as $storeTotalEntry)
		{
			foreach ($storeTotalEntry['TOTALS'] as $measureId => $total)
			{
				$amountSum = (float)$total['AMOUNT_SUM'];
				$quantityReservedSum = (float)$total['QUANTITY_RESERVED_SUM'];

				if ($amountSum === 0.0 && $quantityReservedSum === 0.0)
				{
					continue;
				}
				if (!isset($overallTotals[$measureId]))
				{
					$overallTotals[$measureId] = [
						'AMOUNT_SUM' => 0,
						'QUANTITY_RESERVED_SUM' => 0,
						'QUANTITY' => 0,
					];
				}
				$overallTotals[$measureId]['AMOUNT_SUM'] += $amountSum;
				$overallTotals[$measureId]['QUANTITY_RESERVED_SUM'] += $quantityReservedSum;
				$overallTotals[$measureId]['QUANTITY'] += $amountSum - $quantityReservedSum;
			}
		}

		return $overallTotals;
	}

	private function prepareChartData(array $filter): array
	{
		if
		(
			isset($filter['STORES'])
			&& count($filter['STORES']) <= self::MAX_CHART_COLUMNS_COUNT
		)
		{
			$chartData = [
				'data' => StoreStockSale::getStoreStockSaleData(false, $filter),
				'isOneColumn' => false,
			];

			$storeNames = $this->getStoreNames($filter['STORES']);
			foreach ($chartData['data'] as &$storeDataField)
			{
				$storeName = $storeNames[$storeDataField['STORE_ID']];
				if (empty($storeName))
				{
					$storeName = Loc::getMessage('STORE_STOCK_HANDLER_DEFAULT_STORE_NAME');
				}

				$storeDataField['STORE_NAME'] = $storeName;
			}
		}
		else
		{
			$storesData = StoreStockSale::getStoreStockSaleData(true, $filter);
			$storesData['STORE_NAME'] = '';
			$storesCount = count($storesData['STORE_IDS']);

			$chartData = [
				'data' => [$storesData],
				'isOneColumn' => true,
				'storesInfo' => [
					'storeCount' => $storesCount,
					'cropStoreNamesList' => $this->formStoreNamesList($this->getStoreNames($storesData['STORE_IDS'])),
				],
			];

			if ($storesCount > 0)
			{
				$chartData['sliderUrl'] = $this->formChartSliderUrl($filter);
			}
		}

		$chartData['currency'] = CurrencyManager::getBaseCurrency();

		return $chartData;
	}

	private function formChartSliderUrl(array $filter): string
	{
		$sliderUrl = \CComponentEngine::makeComponentPath('bitrix:catalog.report.store_stock.salechart.stores.grid');
		$sliderUrl = getLocalPath('components'.$sliderUrl.'/slider.php');

		$uri = new Uri($sliderUrl);

		if (isset($filter['STORES']))
		{
			$uri->addParams(['storeIds' => $filter['STORES']]);
		}
		if (isset($filter['PRODUCTS']))
		{
			$uri->addParams(['productIds' => $filter['PRODUCTS']]);
		}
		if (isset($filter['REPORT_INTERVAL']))
		{
			$uri->addParams([
				'reportFrom' => $filter['REPORT_INTERVAL']['FROM'],
				'reportTo' => $filter['REPORT_INTERVAL']['TO'],
			]);
		}
		else
		{
			$defaultInterval = StoreStockSale::getDefaultReportInterval();
			$uri->addParams([
				'reportFrom' => $defaultInterval['FROM'],
				'reportTo' => $defaultInterval['TO'],
			]);
		}

		return $uri->getUri();
	}

	private function getStoreNames(array $storeIds): array
	{
		$storeNamesData = StoreTable::getList([
			'select' => ['ID', 'TITLE'],
			'filter' => ['=ID' => $storeIds],
		])->fetchAll();

		return array_column($storeNamesData, 'TITLE', 'ID');
	}

	private function formStoreNamesList(array $storeNames): string
	{
		if (empty($storeNames))
		{
			return '';
		}

		$storeNamesList = '';
		foreach ($storeNames as $storeName)
		{
			if (empty($storeName))
			{
				$storeName = Loc::getMessage('STORE_STOCK_HANDLER_DEFAULT_STORE_NAME');
			}

			$storeNamesList .= ', ';
			$storeNamesList .= $storeName;
			if (mb_strlen($storeNamesList) >= self::MAX_STORES_LIST_CHARS)
			{
				break;
			}
		}

		$storeNamesList = mb_substr($storeNamesList, 2);

		return $storeNamesList;
	}
}
