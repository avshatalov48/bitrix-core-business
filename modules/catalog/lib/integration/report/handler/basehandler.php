<?php

namespace Bitrix\Catalog\Integration\Report\Handler;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\Filter\StoreStockFilter;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockQuantity;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Web\Uri;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Helper\Filter;
use Bitrix\Report\VisualConstructor\IReportMultipleData;
use Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider;

abstract class BaseHandler extends BaseReport  implements IReportMultipleData
{
	protected const MAX_CHART_COLUMNS_COUNT = 5;
	protected const MAX_STORES_LIST_CHARS = 25;

	abstract public function prepare();

	public function getMultipleData()
	{
		return $this->getCalculatedData();
	}

	public function getMultipleDemoData()
	{
		return [];
	}

	protected function getStoreTotals(): array
	{
		$storeTotals = $this->getStoreProductData();
		if (!empty($storeTotals))
		{
			$receivedQuantities = $this->getReceivedQuantity();
			$soldAmounts = $this->getSoldAmounts();
			$outgoingQuantities = $this->getOutgoingQuantity();
			$receivedQuantitiesDifference = $this->getReceivedQuantityForDifference();
			$outgoingQuantitiesDifference = $this->getOutgoingQuantityForDifference();
			$soldAmountsDifference = $this->getSoldAmountsForDifference();

			$groupedStoreTotals = [];
			foreach ($storeTotals as $key => $storeTotal)
			{
				$storeId = (int)$storeTotal['TMP_STORE_ID'];
				$measureId = (int)$storeTotal['MEASURE_ID'] ?: \CCatalogMeasure::getDefaultMeasure(true)['ID'];
				if (isset($groupedStoreTotals[$storeId][$measureId]))
				{
					$groupedStoreTotals[$storeId][$measureId]['AMOUNT_SUM'] += (float)$storeTotal['AMOUNT_SUM'];
					$groupedStoreTotals[$storeId][$measureId]['QUANTITY_RESERVED_SUM'] += (float)$storeTotal['QUANTITY_RESERVED_SUM'];

					continue;
				}

				$groupedStoreTotals[$storeId][$measureId] = [
					'TITLE' => $storeTotal['TITLE'],
					'TMP_STORE_ID' => $storeId,
					'SORT' => $storeTotal['SORT'],
					'AMOUNT_SUM' => (float)$storeTotal['AMOUNT_SUM'],
					'QUANTITY_RESERVED_SUM' => (float)$storeTotal['QUANTITY_RESERVED_SUM'],
					'MEASURE_ID' => $measureId,
				];

				if (array_key_exists($storeId, $receivedQuantities))
				{
					$groupedStoreTotals[$storeId][$measureId]['RECEIVED_QUANTITIES'] = $receivedQuantities[$storeId];
				}
				if (array_key_exists($storeId, $receivedQuantitiesDifference))
				{
					$groupedStoreTotals[$storeId][$measureId]['RECEIVED_QUANTITIES_DIFFERENCE'] = $receivedQuantitiesDifference[$storeId];
				}

				if (array_key_exists($storeId, $outgoingQuantities))
				{
					$groupedStoreTotals[$storeId][$measureId]['OUTGOING_QUANTITIES'] = $outgoingQuantities[$storeId];
				}
				if (array_key_exists($storeId, $outgoingQuantitiesDifference))
				{
					$groupedStoreTotals[$storeId][$measureId]['OUTGOING_QUANTITIES_DIFFERENCE'] = $outgoingQuantitiesDifference[$storeId];
				}

				if (array_key_exists($storeId, $soldAmounts))
				{
					$groupedStoreTotals[$storeId][$measureId]['SOLD_AMOUNTS'] = $soldAmounts[$storeId];
				}
				if (array_key_exists($storeId, $soldAmountsDifference))
				{
					$groupedStoreTotals[$storeId][$measureId]['SOLD_AMOUNTS_DIFFERENCE'] = $soldAmountsDifference[$storeId];
				}

				ksort($groupedStoreTotals[$storeId]);
			}
			ksort($groupedStoreTotals);

			$storeTotals = $this->prepareStoreTotals($groupedStoreTotals);
		}

		return $storeTotals;
	}

	protected static function formChartSliderUrl(string $componentName, array $filter): string
	{
		$sliderUrl = \CComponentEngine::makeComponentPath($componentName);
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

	private function getStoreProductData(): array
	{
		$accessController = AccessController::getCurrent();
		if (!$accessController->check(ActionDictionary::ACTION_STORE_VIEW))
		{
			return [];
		}

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
				new ExpressionField('QUANTITY_RESERVED_SUM', 'SUM(%s)', ['QUANTITY_RESERVED']),
				(new Reference(
					'STORE_TMP',
					StoreTable::class,
					Join::on('this.STORE_ID', 'ref.ID')
				))->configureJoinType(Join::TYPE_RIGHT),
			],
		];

		$userFilterParameters = $this->getFilterParameters();

		if (isset($userFilterParameters['STORES']) && is_array($userFilterParameters['STORES']))
		{
			$storesList = $userFilterParameters['STORES'];
		}
		else
		{
			$storesList = null;
		}

		$filteredStoresList = self::getFilteredByRightsStoreList($storesList);

		if (is_array($filteredStoresList))
		{
			$queryParams['filter']['=TMP_STORE_ID'] = $filteredStoresList;
		}

		if (!empty($userFilterParameters['PRODUCTS']) && is_array($userFilterParameters['PRODUCTS']))
		{
			$queryParams['filter'][] = $this->getProductFilter($userFilterParameters['PRODUCTS']);
		}

		return StoreProductTable::getList($queryParams)->fetchAll();
	}

	protected function getProductFilter(array $productFilter): array
	{
		return [
			'=PRODUCT_ID' => StoreStockFilter::prepareProductFilter($productFilter),
			[
				'LOGIC' => 'OR',
				'!=AMOUNT' => 0,
				'!=QUANTITY_RESERVED' => 0,
			],
		];
	}

	private function prepareStoreTotals(array $storeTotals): array
	{
		$preparedTotals = [];

		foreach ($storeTotals as $storeId => $storeTotal)
		{
			foreach ($storeTotal as $measureId => $entry)
			{
				if (!isset($preparedTotals[$storeId]))
				{
					$preparedTotals[$storeId] = [
						'TITLE' => $entry['TITLE'],
						'STORE_ID' => $storeId,
						'TOTALS' => [],
					];
				}

				$soldAmountDifferenceData = $entry['SOLD_AMOUNTS_DIFFERENCE'] ?? [];
				$receivedQuantitiesDifferenceData = $entry['RECEIVED_QUANTITIES_DIFFERENCE'] ?? [];
				$outgoingQuantitiesDifferenceData = $entry['OUTGOING_QUANTITIES_DIFFERENCE'] ?? [];
				$amountSum =
					(float)$entry['AMOUNT_SUM']
					- ($receivedQuantitiesDifferenceData[$measureId] ?? 0.0)
					+ ($outgoingQuantitiesDifferenceData[$measureId] ?? 0.0)
					+ ($soldAmountDifferenceData[$measureId] ?? 0.0)
				;
				$quantityReservedSum = (float)$entry['QUANTITY_RESERVED_SUM'];

				$quantity = $amountSum - $quantityReservedSum;
				$productsSoldAmount = $entry['SOLD_AMOUNTS'] ?? [];
				$receivedQuantityData = $entry['RECEIVED_QUANTITIES'] ?? [];
				$outgoingQuantityData = $entry['OUTGOING_QUANTITIES'] ?? [];

				$startingQuantity =
					$amountSum
					- ($receivedQuantityData[$measureId] ?? 0.0)
					+ ($outgoingQuantityData[$measureId] ?? 0.0)
					+ ($productsSoldAmount[$measureId] ?? 0.0)
				;
				$receivedQuantity = ($receivedQuantityData[$measureId] ?? 0.0);
				$amountSold = $productsSoldAmount[$measureId] ?? 0.0;

				$isStoreEmpty = true;
				$values = [
					$startingQuantity, $receivedQuantity, $amountSum, $quantityReservedSum, $quantity, $amountSold,
				];
				foreach ($values as $value)
				{
					if ($value !== 0.0)
					{
						$isStoreEmpty = false;
						break;
					}
				}
				if ($isStoreEmpty)
				{
					continue;
				}

				if (!isset($preparedTotals[$storeId]['TOTALS'][$measureId]))
				{
					$preparedTotals[$storeId]['TOTALS'][$measureId] = [
						'STARTING_QUANTITY' => 0,
						'RECEIVED_QUANTITY' => 0,
						'AMOUNT_SUM' => 0,
						'QUANTITY_RESERVED_SUM' => 0,
						'QUANTITY' => 0,
						'AMOUNT_SOLD' => 0,
					];
				}

				$preparedTotals[$storeId]['TOTALS'][$measureId]['STARTING_QUANTITY'] += $startingQuantity;
				$preparedTotals[$storeId]['TOTALS'][$measureId]['RECEIVED_QUANTITY'] += $receivedQuantity;
				$preparedTotals[$storeId]['TOTALS'][$measureId]['AMOUNT_SUM'] += $amountSum;
				$preparedTotals[$storeId]['TOTALS'][$measureId]['QUANTITY_RESERVED_SUM'] += $quantityReservedSum;
				$preparedTotals[$storeId]['TOTALS'][$measureId]['QUANTITY'] += $quantity;
				$preparedTotals[$storeId]['TOTALS'][$measureId]['AMOUNT_SOLD'] += $amountSold;
			}
		}

		return $preparedTotals;
	}

	private function getFormattedFilterForDifference(): ?array
	{
		$formattedFilter = $this->getFormattedFilter();
		$differenceFilter = $formattedFilter;
		$currentTime = new DateTime();
		$filterTimeTo = new DateTime($differenceFilter['REPORT_INTERVAL']['TO']);
		if ($currentTime > $filterTimeTo)
		{
			$differenceFilter['REPORT_INTERVAL']['FROM'] = $differenceFilter['REPORT_INTERVAL']['TO'];
			\CTimeZone::Disable();
			$differenceFilter['REPORT_INTERVAL']['TO'] = $currentTime->toString();
			\CTimeZone::Enable();
		}
		else
		{
			return null;
		}

		return $differenceFilter;
	}

	private function getReceivedQuantityForDifference(): array
	{
		$formattedFilterForDifference = $this->getFormattedFilterForDifference();
		if (!$formattedFilterForDifference)
		{
			return [];
		}

		return StoreStockQuantity::getReceivedQuantityForStores($formattedFilterForDifference);
	}

	private function getOutgoingQuantityForDifference(): array
	{
		$formattedFilterForDifference = $this->getFormattedFilterForDifference();
		if (!$formattedFilterForDifference)
		{
			return [];
		}

		return StoreStockQuantity::getOutgoingQuantityForStores($formattedFilterForDifference);
	}

	private function getSoldAmountsForDifference(): array
	{
		$formattedFilterForDifference = $this->getFormattedFilterForDifference();
		if (!$formattedFilterForDifference)
		{
			return [];
		}

		return StoreStockSale::getProductsSoldAmountForStores($formattedFilterForDifference);
	}

	private function getReceivedQuantity(): array
	{
		$receivedQuantityFilter = $this->getFormattedFilter();

		return StoreStockQuantity::getReceivedQuantityForStores($receivedQuantityFilter);
	}

	private function getOutgoingQuantity(): array
	{
		$outgoingQuantityFilter = $this->getFormattedFilter();

		return StoreStockQuantity::getOutgoingQuantityForStores($outgoingQuantityFilter);
	}

	private function getSoldAmounts(): array
	{
		$filter = $this->getFormattedFilter();

		return StoreStockSale::getProductsSoldAmountForStores($filter);
	}

	/**
	 * @return array
	 */
	protected function getFormattedFilter(): array
	{
		$filter = $this->getFilterParameters();

		$formattedFilter = [];

		$storesList = (isset($filter['STORES']) && is_array($filter['STORES'])) ? $filter['STORES'] : null;
		$filteredStoresList = self::getFilteredByRightsStoreList($storesList);

		if (is_array($filteredStoresList))
		{
			$formattedFilter['STORES'] = $filteredStoresList;
		}

		if (!empty($filter['PRODUCTS']))
		{
			$formattedFilter['PRODUCTS'] = StoreStockFilter::prepareProductFilter($filter['PRODUCTS']);
		}

		if
		(
			!empty($filter['REPORT_INTERVAL_from'])
			&& !empty($filter['REPORT_INTERVAL_to'])
		)
		{
			$formattedFilter['REPORT_INTERVAL'] = [
				'FROM' => $filter['REPORT_INTERVAL_from'],
				'TO' => $filter['REPORT_INTERVAL_to'],
			];
		}
		return $formattedFilter;
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

	protected function prepareOverallTotals(array $storeTotals): array
	{
		$overallTotals = [];

		foreach ($storeTotals as $storeTotalEntry)
		{
			foreach ($storeTotalEntry['TOTALS'] as $measureId => $total)
			{
				$startingQuantitySum = (float)$total['STARTING_QUANTITY'];
				$amountSum = (float)$total['AMOUNT_SUM'];
				$receivedQuantity = (float)$total['RECEIVED_QUANTITY'];
				$quantityReservedSum = (float)$total['QUANTITY_RESERVED_SUM'];
				$amountSoldSum = (float)$total['AMOUNT_SOLD'];

				if (!isset($overallTotals[$measureId]))
				{
					$overallTotals[$measureId] = [
						'STARTING_QUANTITY' => 0,
						'RECEIVED_QUANTITY' => 0,
						'AMOUNT_SUM' => 0,
						'QUANTITY_RESERVED_SUM' => 0,
						'AMOUNT_SOLD' => 0,
						'QUANTITY' => 0,
					];
				}
				$overallTotals[$measureId]['STARTING_QUANTITY'] += $startingQuantitySum;
				$overallTotals[$measureId]['RECEIVED_QUANTITY'] += $receivedQuantity;
				$overallTotals[$measureId]['AMOUNT_SUM'] += $amountSum;
				$overallTotals[$measureId]['QUANTITY_RESERVED_SUM'] += $quantityReservedSum;
				$overallTotals[$measureId]['QUANTITY'] += $amountSum - $quantityReservedSum;
				$overallTotals[$measureId]['AMOUNT_SOLD'] += $amountSoldSum;
			}
		}

		return $overallTotals;
	}

	protected static function getAnalyticBoardByKey($key): ?AnalyticBoard
	{
		$boardProvider = new AnalyticBoardProvider();
		$boardProvider->addFilter('boardKey', $key);

		return $boardProvider->execute()->getFirstResult();
	}

	protected function getFilter(): Filter
	{
		static $filter;
		if ($filter)
		{
			return $filter;
		}

		$boardKey = $this->getWidgetHandler()->getWidget()->getBoardId();
		$board = self::getAnalyticBoardByKey($boardKey);
		if ($board)
		{
			$filter = $board->getFilter();
		}
		else
		{
			$filter = new Filter($boardKey);
		}

		return $filter;
	}

	protected function getFilterParameters(): array
	{
		static $filterParameters = [];

		$filter = $this->getFilter();
		$filterId = $filter->getFilterParameters()['FILTER_ID'];

		if (!$filterParameters[$filterId])
		{
			$options = new Options($filterId, $filter::getPresetsList());
			$fieldList = $filter::getFieldsList();
			$filterParameters[$filterId] = $options->getFilter($fieldList);
		}

		return $filterParameters[$filterId];
	}

	protected static function getNoAccessToStoresStub(): array
	{
		return [
			'title' => Loc::getMessage('BASE_HANDLER_EMPTY_PERMITTED_STORES_LIST_STUB_TITLE'),
			'description' => Loc::getMessage('BASE_HANDLER_EMPTY_PERMITTED_STORES_LIST_STUB_DESCRIPTION'),
		];
	}

	/**
	 * @param array|null $inputStoreList
	 * @return array|null
	 */
	private static function getFilteredByRightsStoreList(?array $inputStoreList = null): ?array
	{
		$accessController = AccessController::getCurrent();

		if (!$accessController->check(ActionDictionary::ACTION_STORE_VIEW))
		{
			return [];
		}

		if (!$accessController->checkCompleteRight(ActionDictionary::ACTION_STORE_VIEW))
		{
			$availableStores = $accessController->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW) ?? [];

			if (is_array($inputStoreList))
			{
				return array_values(array_intersect($availableStores, $inputStoreList));
			}

			return $availableStores;
		}

		return $inputStoreList;
	}
}
