<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock;

\Bitrix\Main\Loader::includeModule('sale');

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\StoreStock\Entity\ProductInfo;
use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreInfo;
use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreWithProductsInfo;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;
use Bitrix\Sale\Internals\ShipmentItemTable;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/** @internal - use at your own risk */
final class StoreStockSale
{
	protected const DEFAULT_DATE_INTERVAL = '-30D';

	protected static $defaultCurrency;

	protected static $productPrice;

	public static function getProductsSoldAmountForStores($filter = []): array
	{
		$soldProductsDbResult = self::getProductsSoldAmountFromShipmentsList($filter);
		$result = [];
		while ($soldProduct = $soldProductsDbResult->fetch())
		{
			$storeId = (int)$soldProduct['STORE_ID'];
			if (!isset($result[$storeId]))
			{
				$result[$storeId] = [];
			}

			$measureId = (int)$soldProduct['MEASURE_ID'] ?: \CCatalogMeasure::getDefaultMeasure(true)['ID'];
			if (!isset($result[$storeId][$measureId]))
			{
				$result[$storeId][$measureId] = 0.0;
			}

			$result[$storeId][$measureId] += (float)$soldProduct['QUANTITY_SUM'];
		}

		return $result;
	}

	public static function getProductsSoldAmountForProductsOnStore(int $storeId, $filter = []): array
	{
		$filter['STORES'] = $storeId;

		$shipmentsDbResult = self::getProductsSoldAmountFromShipmentsList($filter);
		$result = [];

		while ($row = $shipmentsDbResult->fetch())
		{
			$result[$row['PRODUCT_ID']] = (float)$row['QUANTITY_SUM'];
		}

		return $result;
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\ORM\Query\Result
	 */
	private static function getProductsSoldAmountFromShipmentsList(array $filter = []): \Bitrix\Main\ORM\Query\Result
	{
		$getListParameters = self::getShippedDataListParameters($filter);

		$getListParameters['select']['MEASURE_ID'] = 'BASKET.PRODUCT.MEASURE';
		$getListParameters['select'][] = 'QUANTITY_SUM';
		$getListParameters['group'] = ['BASKET.PRODUCT_ID', 'S_BARCODE.STORE_ID'];
		$getListParameters['runtime'][] = new ExpressionField(
			'QUANTITY_SUM',
			'SUM(%s)',
			['QUANTITY']
		);

		return ShipmentItemTable::getList($getListParameters);
	}

	public static function getStoreStockSaleData(bool $isOneField, array $filter): array
	{
		$filter = self::prepareFilter($filter);
		$reservedData = self::getReservedData($filter);

		$productIds = array_column($reservedData, 'PRODUCT_ID');
		self::initProductPrice($productIds);

		$storeIds =
			$filter['STORES']
			?? array_column($reservedData, 'STORE_ID')
		;

		$storesData = [];
		if ($isOneField)
		{
			$storesData = self::formField($reservedData);
			$storesData['STORE_IDS'] = $storeIds;
		}
		else
		{
			$storesPositionData = array_fill_keys(
				$storeIds,
				[
					'reservedData' => [],
				]
			);

			foreach ($reservedData as $reservedPosition)
			{
				$storesPositionData[$reservedPosition['STORE_ID']]['reservedData'][] = $reservedPosition;
			}

			foreach ($storesPositionData as $storeId => $fieldData)
			{
				$storesData[] = self::formField($fieldData['reservedData'], $storeId);
			}
		}

		return $storesData;
	}

	public static function getDefaultReportInterval(): array
	{
		$currentDate = new DateTime();
		$intervalStartDate = new DateTime();
		$intervalStartDate->add(self::DEFAULT_DATE_INTERVAL);

		return [
			'FROM' => $intervalStartDate->toString(),
			'TO' => $currentDate->toString(),
		];
	}

	private static function getProductPrice(int $productId): float
	{
		if (!isset(self::$productPrice[$productId]))
		{
			self::initProductPrice([$productId]);
			self::$productPrice[$productId] ??= 0;
		}

		return self::$productPrice[$productId];
	}

	private static function prepareFilter(array $filter): array
	{
		if (isset($filter['REPORT_INTERVAL_from']) && isset($filter['REPORT_INTERVAL_to']))
		{
			$filter['REPORT_INTERVAL'] = [
				'FROM' => $filter['REPORT_INTERVAL_from'],
				'TO' => $filter['REPORT_INTERVAL_to'],
			];
		}

		$accessController = AccessController::getCurrent();
		if (!$accessController->checkCompleteRight(ActionDictionary::ACTION_STORE_VIEW))
		{
			$availableStores = $accessController->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW) ?? [];

			if (isset($filter['STORES']) && is_array($filter['STORES']))
			{
				$filter['STORES'] = array_values(array_intersect($availableStores, $filter['STORES']));
			}
			else
			{
				$filter['STORES'] = $availableStores;
			}
		}

		if
		(
			!isset($filter['REPORT_INTERVAL'])
			|| !isset($filter['REPORT_INTERVAL']['FROM'])
			|| !isset($filter['REPORT_INTERVAL']['TO'])
		)
		{
			$filter['REPORT_INTERVAL'] = self::getDefaultReportInterval();
		}

		$filter['INNER_MOVEMENT'] = (bool)($filter['INNER_MOVEMENT'] ?? true);

		return $filter;
	}


	/**
	 * Return array of stores that was involved in realization documents and match by filter <b>$filter</b>
	 * @param array $filter
	 * @return array <b>array</b> of instances <b>StoreInfo</b>
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getShippedData(array $filter): array
	{
		$getListParameters = self::getShippedDataListParameters($filter);
		$getListParameters['select']['QUANTITY'] = 'QUANTITY';

		return self::formStoresListFromStoresData($filter, ShipmentItemTable::getList($getListParameters)->fetchAll());
	}

	protected static function formStoresListFromStoresData(array $filter, array $storesData): array
	{
		ProductInfo::initBasePrice(...array_column($storesData, 'PRODUCT_ID'));
		StoreInfo::loadStoreName(...array_column($storesData, 'STORE_ID'));

		$storesInfo = [];

		if (isset($filter['STORES']))
		{
			$storesInfo = array_fill_keys($filter['STORES'], []);
		}
		foreach ($storesData as $shipmentItem)
		{
			$storeId = $shipmentItem['STORE_ID'];
			$productId = $shipmentItem['PRODUCT_ID'];
			if (!isset($storesInfo[$storeId]))
			{
				$storesInfo[$storeId] = [];
			}

			if (!isset($storesInfo[$storeId][$productId]))
			{
				$storesInfo[$storeId][$productId] = (float)$shipmentItem['QUANTITY'];
			}
			else
			{
				$storesInfo[$storeId][$productId] += (float)$shipmentItem['QUANTITY'];
			}
		}

		$stores = [];
		foreach ($storesInfo as $storeId => $storeInfo)
		{
			$store = new StoreWithProductsInfo($storeId);
			foreach ($storeInfo as $productId => $quantity)
			{
				$store->addProduct(new ProductInfo($productId, $quantity));
			}
			$stores[] = $store;
		}

		return $stores;
	}

	protected static function getShippedDataListParameters(array $filter)
	{
		$filter = self::prepareFilter($filter);

		return [
			'select' => [
				'STORE_ID' => 'S_BARCODE.STORE_ID',
				'PRODUCT_ID' => 'BASKET.PRODUCT_ID',
			],

			'filter' => self::formShipmentDataFilter($filter),

			'runtime' => [
				(new Reference(
					'S_BARCODE',
					ShipmentItemStoreTable::class,
					Join::on('this.ID', 'ref.ORDER_DELIVERY_BASKET_ID')
				))->configureJoinType(Join::TYPE_LEFT),

				(new Reference(
					'SHIPMENT',
					ShipmentTable::class,
					Join::on('this.ORDER_DELIVERY_ID', 'ref.ID')
				))->configureJoinType(Join::TYPE_LEFT),

				(new Reference(
					'BASKET',
					BasketTable::class,
					Join::on('this.BASKET_ID', 'ref.ID')
				))->configureJoinType(Join::TYPE_LEFT),
			],
		];
	}

	private static function formShipmentDataFilter(array $filter): array
	{
		$formedFilter = [
			'=SHIPMENT.DEDUCTED' => 'Y',
			'>S_BARCODE.STORE_ID' => 0,
		];

		if (isset($filter['STORES']))
		{
			$formedFilter['=S_BARCODE.STORE_ID'] = $filter['STORES'];
		}

		if (isset($filter['PRODUCTS']))
		{
			$formedFilter['=BASKET.PRODUCT_ID'] = $filter['PRODUCTS'];
		}

		if (isset($filter['REPORT_INTERVAL']))
		{
			$formedFilter['>=SHIPMENT.DATE_DEDUCTED'] = new DateTime($filter['REPORT_INTERVAL']['FROM']);
			$formedFilter['<=SHIPMENT.DATE_DEDUCTED'] = new DateTime($filter['REPORT_INTERVAL']['TO']);
		}

		return $formedFilter;
	}

	/**
	 * Return array of stores that was involved in arrived documents and match by filter <b>$filter</b>
	 * @param array $filter
	 * @return array <b>array</b> of instances <b>ProductStorage</b>
	 * @see \Bitrix\Catalog\Integration\Report\StoreStock\Entity\ProductStorage
	 */
	public static function getArrivedData(array $filter): array
	{
		$getListParameters = self::getArrivedDataListParameters($filter);
		return self::formStoresListFromStoresData($filter, StoreDocumentTable::getList($getListParameters)->fetchAll());
	}

	/**
	 * Return computed percent of sold products from store
	 *
	 * @param float $shippedSum
	 * @param float $arrivedSum
	 * @param int $precision
	 * @return float
	 */
	public static function computeSoldPercent(float $shippedSum, float $arrivedSum, int $precision = 2): float
	{

		if ($shippedSum === 0.0)
		{
			$soldPercent = 0;
		}
		elseif ($arrivedSum === 0.0)
		{
			$soldPercent = 100;
		}
		else
		{
			$soldPercent = ($shippedSum / $arrivedSum) * 100;
		}

		return round($soldPercent, $precision);
	}

	protected static function getArrivedDataListParameters(array $filter): array
	{
		$filter = self::prepareFilter($filter);

		return [
			'select' => [
				'PRODUCT_ID' => 'ELEMENTS.ELEMENT_ID',
				'QUANTITY' => 'ELEMENTS.AMOUNT',
				'STORE_ID' => 'ELEMENTS.STORE_TO',
			],

			'filter' => self::formArrivedDataFilter($filter),
		];
	}

	private static function formArrivedDataFilter(array $filter): array
	{
		$docTypes = [StoreDocumentTable::TYPE_ARRIVAL, StoreDocumentTable::TYPE_STORE_ADJUSTMENT];
		if ($filter['INNER_MOVEMENT'])
		{
			$docTypes[] = StoreDocumentTable::TYPE_MOVING;
		}
		$formedFilter = [
			'=DOC_TYPE' => $docTypes,
			'=STATUS' => 'Y',
			'>ELEMENTS.ELEMENT_ID' => 0,
		];

		if (isset($filter['STORES']))
		{
			$formedFilter['=ELEMENTS.STORE_TO'] = $filter['STORES'];
		}

		if (isset($filter['PRODUCTS']))
		{
			$formedFilter['=ELEMENTS.ELEMENT_ID'] = $filter['PRODUCTS'];
		}

		if (isset($filter['REPORT_INTERVAL']))
		{
			$formedFilter['>=DATE_STATUS'] = new DateTime($filter['REPORT_INTERVAL']['FROM']);
			$formedFilter['<=DATE_STATUS'] = new DateTime($filter['REPORT_INTERVAL']['TO']);
		}

		return $formedFilter;
	}

	public static function getReservedData(array $filter): array
	{
		$reservedData = StoreProductTable::getList([
			'select' => [
				'PRODUCT_ID',
				'STORE_ID',
				'QUANTITY' => 'AMOUNT',
			],
			'filter' => self::formReservedDataFilter($filter),
		])->fetchAll();

		return self::formStoresListFromStoresData($filter, $reservedData);
	}

	private static function formReservedDataFilter(array $filter): array
	{
		$formedFilter = [
			'>STORE_ID' => 0,
		];

		$filter = self::prepareFilter($filter);
		if (isset($filter['STORES']))
		{
			$formedFilter['=STORE_ID'] = $filter['STORES'];
		}
		else
		{
			$formedFilter['!=QUANTITY'] = 0;
		}

		if (isset($filter['PRODUCTS']))
		{
			$formedFilter['=PRODUCT_ID'] = $filter['PRODUCTS'];
		}

		return $formedFilter;
	}

	private static function combineUniqueColumnElements(array $arraysList, string $columnKey): array
	{
		$combineColumnElements = [];
		foreach ($arraysList as $item)
		{
			$columnElements = array_column($item, $columnKey);
			array_push($combineColumnElements, ...$columnElements);
		}

		return array_unique($combineColumnElements);
	}

	protected static function formField(array $storeReservedData, int $storeId = null): array
	{
		$storedSum = 0.0;
		foreach ($storeReservedData as $storePosition)
		{
			$storedSum += self::getPositionPrice($storePosition['PRODUCT_ID'], $storePosition['QUANTITY']);
		}

		$result = [
			'SUM_STORED' => $storedSum,
		];

		if ($storeId !== null)
		{
			$result['STORE_ID'] = $storeId;
		}

		return $result;
	}

	protected static function getPositionPrice(int $productId, float $productCount): float
	{
		return self::getProductPrice($productId) * $productCount;
	}

	protected static function initProductPrice(array $productIds): void
	{
		$defaultCurrency = CurrencyManager::getBaseCurrency();
		$productsData = ProductTable::getList([
			'select' => [
				'ID',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
				'PURCHASING_CURRENCY_AMOUNT' => 'CURRENCY_TABLE.CURRENT_BASE_RATE',
			],
			'filter' => [
				'=ID' => $productIds,
			],
			'runtime' => [
				(new Reference(
					'CURRENCY_TABLE',
					CurrencyTable::class,
					Join::on('this.PURCHASING_CURRENCY', 'ref.CURRENCY')
				))->configureJoinType(Join::TYPE_LEFT),
			],
		])->fetchAll();

		foreach ($productsData as $product)
		{
			self::$productPrice[$product['ID']] = (float)$product['PURCHASING_PRICE'];
			if ($product['PURCHASING_CURRENCY'] !== $defaultCurrency)
			{
				$defaultCurrencyAmount = (float)\CCurrency::getCurrency($defaultCurrency)['CURRENT_BASE_RATE'];
				$currentCurrencyAmount = (float)$product['PURCHASING_CURRENCY_AMOUNT'];

				self::$productPrice[$product['ID']] *= $currentCurrencyAmount;
				self::$productPrice[$product['ID']] /= $defaultCurrencyAmount;
			}
		}
	}
}
