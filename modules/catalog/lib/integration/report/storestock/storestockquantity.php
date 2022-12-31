<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock;

use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Type\DateTime;

/** @internal - use at your own risk */
final class StoreStockQuantity
{
	protected const DEFAULT_DATE_INTERVAL = '-30D';

	/**
	 * Returns the overall outgoing quantity for each store
	 * For the filter format, @see StoreStockQuantity::getIncomingOutgoingQuantitiesFromDocuments
	 * @param array $userFilter
	 * @return array
	 */
	public static function getOutgoingQuantityForStores(array $userFilter = []): array
	{
		$receivedQuantity = self::getIncomingOutgoingQuantitiesFromDocuments($userFilter);

		$reduceCallback = static function($result, $current)
		{
			$measureId = (int)$current['MEASURE_ID'] ?: \CCatalogMeasure::getDefaultMeasure(true)['ID'];
			if (!array_key_exists($measureId, $result))
			{
				$result[$measureId] = 0.0;
			}

			$result[$measureId] += $current['AMOUNT']['OUTGOING'];
			return $result;
		};

		foreach ($receivedQuantity as $storeId => $productEntry)
		{
			$receivedQuantity[$storeId] = array_reduce($productEntry, $reduceCallback, []);
		}

		return $receivedQuantity;
	}

	/**
	 * Returns the overall outgoing quantity for each product in the store
	 * For the filter format, @see StoreStockQuantity::getIncomingOutgoingQuantitiesFromDocuments
	 * @param int $storeId
	 * @param array $userFilter
	 * @return array
	 */
	public static function getOutgoingQuantityForProductsOnStore(int $storeId, array $userFilter = []): array
	{
		$userFilter['STORES'] = $storeId;

		$outgoingQuantity = self::getIncomingOutgoingQuantitiesFromDocuments($userFilter)[$storeId] ?? [];

		$mapCallback = static function ($entry)
		{
			return $entry['AMOUNT']['OUTGOING'];
		};

		return array_map($mapCallback, $outgoingQuantity);
	}

	/**
	 * Returns the overall received quantity for each store
	 * For the filter format, @see StoreStockQuantity::getIncomingOutgoingQuantitiesFromDocuments
	 * @param array $userFilter
	 * @return array
	 */
	public static function getReceivedQuantityForStores(array $userFilter = []): array
	{
		$receivedQuantity = self::getIncomingOutgoingQuantitiesFromDocuments($userFilter);

		$reduceCallback = static function($result, $current)
		{
			$measureId = (int)$current['MEASURE_ID'] ?: \CCatalogMeasure::getDefaultMeasure(true)['ID'];
			if (!array_key_exists($measureId, $result))
			{
				$result[$measureId] = 0.0;
			}

			$result[$measureId] += $current['AMOUNT']['INCOMING'];
			return $result;
		};

		foreach ($receivedQuantity as $storeId => $productEntry)
		{
			$receivedQuantity[$storeId] = array_reduce($productEntry, $reduceCallback, []);
		}

		return $receivedQuantity;
	}

	/**
	 * Returns the received quantity for each product in the store
	 * For the filter format, @see StoreStockQuantity::getIncomingOutgoingQuantitiesFromDocuments
	 *
	 * @param int $storeId
	 * @param array $userFilter
	 * @return array
	 */
	public static function getReceivedQuantityForProductsOnStore(int $storeId, array $userFilter = []): array
	{
		$userFilter['STORES'] = $storeId;

		$receivedQuantity = self::getIncomingOutgoingQuantitiesFromDocuments($userFilter)[$storeId] ?? [];

		$mapCallback = static function ($entry)
		{
			return $entry['AMOUNT']['INCOMING'];
		};

		return array_map($mapCallback, $receivedQuantity);
	}

	/**
	 * Calculates the received quantity for products in stores.
	 * $userFilter can have the following keys:
	 * * PRODUCTS - an id of a product or an array of ids
	 * * STORES - an id of a store or an array of ids
	 * * REPORT_INTERVAL[FROM], REPORT_INTERVAL[FROM] - sets the calculation date interval
	 * @param array $userFilter
	 * @return array
	 */
	private static function getIncomingOutgoingQuantitiesFromDocuments(array $userFilter = []): array
	{
		$userFilter = self::prepareFilter($userFilter);

		$productsDataList = self::getDocumentProductsDataList($userFilter);

		$result = [];

		while ($entry = $productsDataList->fetch())
		{
			$storeFromId = (int)$entry['STORE_FROM'];
			$storeToId = (int)$entry['STORE_TO'];

			if (!isset($result[$storeFromId]))
			{
				$result[$storeFromId] = [];
			}
			if (!isset($result[$storeToId]))
			{
				$result[$storeToId] = [];
			}

			$productId = $entry['ELEMENT_ID'];
			if (!array_key_exists($productId, $result[$storeToId]))
			{
				$result[$storeToId][$productId] = [
					'MEASURE_ID' => (int)$entry['MEASURE_ID'] ?: \CCatalogMeasure::getDefaultMeasure(true)['ID'],
					'AMOUNT' => [
						'INCOMING' => 0.0,
						'OUTGOING' => 0.0,
					],
				];
			}

			if ($storeFromId > 0)
			{
				$result[$storeFromId][$productId]['AMOUNT']['OUTGOING'] += (float)$entry['AMOUNT_SUM'];
			}

			if ($storeToId > 0)
			{
				$result[$storeToId][$productId]['AMOUNT']['INCOMING'] += (float)$entry['AMOUNT_SUM'];
			}
		}

		return $result;
	}

	/**
	 * @param array $userFilter
	 * @return Result
	 */
	private static function getDocumentProductsDataList(array $userFilter = []): Result
	{
		$receivedQuantityQuery = new Query(StoreDocumentElementTable::getEntity());
		$receivedQuantityQuery->setSelect([
			'DOCUMENT.DOC_TYPE',
			'STORE_FROM',
			'STORE_TO',
			'ELEMENT_ID',
			'AMOUNT_SUM',
			'MEASURE_ID' => 'PRODUCT.MEASURE'
		]);

		$filter = [
			'=DOCUMENT.STATUS' => 'Y',
		];

		if (isset($userFilter['PRODUCTS']))
		{
			$filter['=ELEMENT_ID'] = $userFilter['PRODUCTS'];
		}

		if (isset($userFilter['STORES']))
		{
			$filter[] = [
				'LOGIC' => 'OR',
				'=STORE_TO' => $userFilter['STORES'],
				'=STORE_FROM' => $userFilter['STORES'],
			];
		}

		if (isset($userFilter['REPORT_INTERVAL']))
		{
			$filter['>=DOCUMENT.DATE_STATUS'] = new DateTime($userFilter['REPORT_INTERVAL']['FROM']);
			$filter['<=DOCUMENT.DATE_STATUS'] = new DateTime($userFilter['REPORT_INTERVAL']['TO']);
		}

		$receivedQuantityQuery->setFilter($filter);
		$receivedQuantityQuery->setGroup(['STORE_FROM', 'STORE_TO', 'ELEMENT_ID']);
		$receivedQuantityQuery->registerRuntimeField(
			new ExpressionField('AMOUNT_SUM', 'SUM(de.AMOUNT)')
		);
		$receivedQuantityQuery->setCustomBaseTableAlias('de');

		return $receivedQuantityQuery->exec();
	}

	/**
	 * @return array
	 */
	private static function getDefaultReportInterval(): array
	{
		$currentDate = new DateTime();
		$intervalStartDate = new DateTime();
		$intervalStartDate->add(self::DEFAULT_DATE_INTERVAL);

		return [
			'FROM' => $intervalStartDate->toString(),
			'TO' => $currentDate->toString(),
		];
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	private static function prepareFilter(array $filter): array
	{
		if (!isset($filter['REPORT_INTERVAL']['FROM'], $filter['REPORT_INTERVAL']['TO']))
		{
			$filter['REPORT_INTERVAL'] = self::getDefaultReportInterval();
		}

		return $filter;
	}
}
