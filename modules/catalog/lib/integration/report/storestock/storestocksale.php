<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock;

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

class StoreStockSale
{
	protected const DEFAULT_DATE_INTERVAL = '-30D';

	protected static $defaultCurrency;

	protected static $productPrice;

	public static function getStoreStockSaleData(bool $isOneField, array $filter): array
	{
		$filter = self::prepareFilter($filter);
		$shippedData = self::getShippedData($filter);
		$reservedData = self::getReservedData($filter);

		$productIds = self::combineUniqueColumnElements([$shippedData, $reservedData], 'PRODUCT_ID');
		self::initProductPrice($productIds);

		$storeIds =
			$filter['STORES']
			?? self::combineUniqueColumnElements([$shippedData, $reservedData], 'STORE_ID')
		;

		$storesData = [];
		if ($isOneField)
		{
			$storesData = self::formField($shippedData, $reservedData);
			$storesData['STORE_IDS'] = $storeIds;
		}
		else
		{
			$storesPositionData = array_fill_keys(
				$storeIds,
				[
					'shippedData' => [],
					'reservedData' => [],
				]
			);

			foreach ($shippedData as $shippedPosition)
			{
				$storesPositionData[$shippedPosition['STORE_ID']]['shippedData'][] = $shippedPosition;
			}

			foreach ($reservedData as $reservedPosition)
			{
				$storesPositionData[$reservedPosition['STORE_ID']]['reservedData'][] = $reservedPosition;
			}

			foreach ($storesPositionData as $storeId => $fieldData)
			{
				$storesData[] = self::formField($fieldData['shippedData'], $fieldData['reservedData'], $storeId);
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

	public static function getProductPrice(int $productId): float
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
		if
		(
			!isset($filter['REPORT_INTERVAL'])
			|| !isset($filter['REPORT_INTERVAL']['FROM'])
			|| !isset($filter['REPORT_INTERVAL']['TO'])
		)
		{
			$filter['REPORT_INTERVAL'] = self::getDefaultReportInterval();
		}

		return $filter;
	}

	protected static function getShippedData(array $filter): array
	{
		$shipmentData = ShipmentItemTable::getList([
			'select' => [
				'QUANTITY' => 'QUANTITY',
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
		])->fetchAll();

		return $shipmentData;
	}

	private static function formShipmentDataFilter(array $filter): array
	{
		$formedFilter = [
			'=SHIPMENT.DEDUCTED' => 'Y',
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

	protected static function getReservedData(array $filter): array
	{
		$reservedData = StoreProductTable::getList([
			'select' => [
				'PRODUCT_ID',
				'STORE_ID',
				'QUANTITY' => 'AMOUNT',
			],
			'filter' => self::formReservedDataFilter($filter),
		])->fetchAll();

		return $reservedData;
	}

	private static function formReservedDataFilter(array $filter): array
	{
		$formedFilter = [];
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

	protected static function formField(array $storeSoldData, array $storeReservedData, int $storeId = null): array
	{
		$soldSum = 0.0;
		$storedSum = 0.0;

		foreach ($storeSoldData as $basketPosition)
		{
			$soldSum += self::getPositionPrice($basketPosition['PRODUCT_ID'], $basketPosition['QUANTITY']);
		}

		foreach ($storeReservedData as $storePosition)
		{
			$storedSum += self::getPositionPrice($storePosition['PRODUCT_ID'], $storePosition['QUANTITY']);
		}

		$storedPercent = ($storedSum + $soldSum) > 0 ? ($storedSum / ($storedSum + $soldSum)) * 100 : 0;
		$soldPercent = ($storedSum + $soldSum) > 0 ? ($soldSum / ($storedSum + $soldSum)) * 100 : 0;

		$result = [
			'SUM_STORED' => $storedSum,
			'SUM_STORED_PERCENT' => $storedPercent,

			'SUM_SOLD' => $soldSum,
			'SUM_SOLD_PERCENT' => $soldPercent,
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