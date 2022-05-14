<?php

namespace Bitrix\Sale\Reservation;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\Result;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\StoreProductTable;
use Bitrix\Sale\Reservation\Internals\BasketReservationHistoryTable;
use Bitrix\Sale\Reservation\Internals\BasketReservationTable;
use Exception;

/**
 * Service for working with the history of basket reserves
 */
class BasketReservationHistoryService
{
	public function __construct()
	{
		Loader::includeModule('catalog');
	}
	
	/**
	 * Rounding to the required accuracy within the service
	 *
	 * @param float $quantity
	 * @return float
	 */
	private function roundQuantity(float $quantity): float
	{
		$precision = 6;
		return round($quantity, $precision, PHP_ROUND_HALF_DOWN);
	}
	
	/**
	 * Total reserved quantity by reservation history
	 *
	 * @param int $reservationId
	 * @return float
	 */
	public function getQuantityByReservation(int $reservationId): float
	{
		$total = 0.0;
		
		$rows = BasketReservationHistoryTable::getList([
			'select' => [
				'QUANTITY',
			],
			'filter' => [
				'=RESERVATION_ID' => $reservationId,
			],
		]);
		foreach ($rows as $row)
		{
			$total += (float)$row['QUANTITY'];
		}
		
		return $total;
	}
	
	/**
	 * The available amount to be debited based on the reservation history.
	 * 
	 * @see example in `getAvailableCountForBasketItem` method
	 *
	 * @param int $orderId
	 * @return array in format `$ret[$productId][$storeId]; // avaiableQuantity`
	 */
	public function getAvailableCountForOrder(int $orderId): array
	{		
		$basketItems =
			BasketTable::getList([
				'select' => [
					'ID',
					'PRODUCT_ID',
				],
				'filter' => [
					'=ORDER_ID' => $orderId,
				],
			])
			->fetchAll()
		;
		
		$basket2productIds = array_column($basketItems, 'PRODUCT_ID', 'ID');
		if (empty($basket2productIds))
		{
			return [];
		}
		
		$calculator = new AvailableQuantityCalculator();

		$rows = StoreProductTable::getList([
			'select' => [
				'PRODUCT_ID',
				'STORE_ID',
				'AMOUNT',
			],
			'filter' => [
				'=PRODUCT_ID' => $basket2productIds,
			],
		]);
		foreach ($rows as $row)
		{
			$calculator->setStoreQuantity($row['STORE_ID'], $row['PRODUCT_ID'], $row['AMOUNT']);
		}
		
		$reservationsRows = BasketReservationHistoryTable::getList([
			'select' => [
				'RESERVATION_ID',
				'QUANTITY',
				'STORE_ID' => 'RESERVATION.STORE_ID',
				'BASKET_ID' => 'RESERVATION.BASKET_ID',
				'PRODUCT_ID' => 'RESERVATION.BASKET.PRODUCT_ID',
			],
			'filter' => [
				'=RESERVATION.BASKET.PRODUCT_ID' => $basket2productIds,
			],
			'order' => [
				'DATE_RESERVE' => 'ASC',
			],
		]);
		
		foreach ($reservationsRows as $row)
		{
			$calculator->addReservationHistory(
				$row['STORE_ID'],
				$row['PRODUCT_ID'],
				$row['BASKET_ID'],
				$row['QUANTITY']
			);
		}
		
		return $calculator->getQuantityForBatch($basket2productIds);
	}
	
	/**
	 * The available amount to be debited based on the reservation history.
	 * 
	 * Example 1, there are 100pcs of product A in stock, then:
	 * 1. Deal #1 - 80pcs reserved;
	 * 2. Deal #2 - 40pcs reserved;
	 * 3. Deal #1 - the reserve has been changed from 80pcs to 90pcs - in this situation,
	 * another record with a reserve of 10pcs is added to the history.
	 * 
	 * Thus, deal #1 can write off only 80pcs (because 10pcs were reserved after deal #2),
	 * and deal #2 only 20pcs (because they were reserved after deal #1).
	 *
	 * Example 2, there are 100pcs of product A in stock, then:
	 * 1. Deal #1 - 40pcs reserved;
	 * 2. Deal #2 - 50pcs reserved;
	 * 
	 * Thus, deal #1 can write off 50pcs (40 reserved + 10 non-reserved store balance),
	 * and deal #2 60pcs (50 reserved + 10 non-reserved store balance).
	 *
	 * @param int $basketId
	 * @param int $storeId
	 * @return float avaiable quantity
	 */
	public function getAvailableCountForBasketItem(int $basketId, int $storeId): float
	{
		$basketItem = BasketTable::getRow([
			'select' => [
				'PRODUCT_ID',
			],
			'filter' => [
				'=ID' => $basketId,
			],
		]);
		if (!$basketItem || !$basketItem['PRODUCT_ID'])
		{
			return 0.0;
		}
		
		$productId = (int)$basketItem['PRODUCT_ID'];
		$storeQuantityRow = StoreProductTable::getRow([
			'select' => [
				'AMOUNT',
			],
			'filter' => [
				'=STORE_ID' => $storeId,
				'=PRODUCT_ID' => $productId,
			],
		]);
		if (!$storeQuantityRow)
		{
			return 0.0;
		}
		
		$calculator = new AvailableQuantityCalculator();
		$calculator->setStoreQuantity($storeId, $productId, $storeQuantityRow['AMOUNT']);
		
		$reservationsRows = BasketReservationHistoryTable::getList([
			'select' => [
				'RESERVATION_ID',
				'QUANTITY',
				'BASKET_ID' => 'RESERVATION.BASKET_ID',
			],
			'filter' => [
				'=RESERVATION.STORE_ID' => $storeId,
				'=RESERVATION.BASKET.PRODUCT_ID' => $productId,
			],
			'order' => [
				'DATE_RESERVE' => 'ASC',
			],
		]);
		foreach ($reservationsRows as $row)
		{
			$calculator->addReservationHistory(
				$storeId,
				$productId,
				$row['BASKET_ID'],
				$row['QUANTITY']
			);
		}
		
		return $calculator->getQuantityForItem($productId, $basketId, $storeId);
	}
	
	/**
	 * Add history row
	 *
	 * @param array $fields 
	 * @return Result
	 */
	public function add(array $fields): Result
	{
		return BasketReservationHistoryTable::add($fields);
	}
	
	/**
	 * Add quantity to reservations history.
	 *
	 * @param int $reservationId
	 * @param float $quantity
	 * @return Result
	 */
	private function addQuantity(int $reservationId, float $quantity): Result
	{
		return $this->add([
			'RESERVATION_ID' => $reservationId,
			'DATE_RESERVE' => new DateTime(),
			'QUANTITY' => $quantity,
		]);
	}
	
	/**
	 * Add history row by reservation
	 *
	 * @param int $reservationId
	 * @return Result
	 */
	public function addByReservation(int $reservationId): Result
	{
		$reservation = BasketReservationTable::getRowById($reservationId);
		if (!$reservation)
		{
			throw new Exception('Reservation not found');
		}
		
		return $this->addQuantity($reservationId, (float)$reservation['QUANTITY']);
	}
	
	/**
	 * Update history row
	 *
	 * @param int $id
	 * @param array $fields
	 * @return Result
	 */
	public function update(int $id, array $fields): Result
	{
		return BasketReservationHistoryTable::update($id, $fields);
	}
	
	/**
	 * Update history row by reservation
	 *
	 * @param int $reservationId
	 * @return Result
	 */
	public function updateByReservation(int $reservationId): Result
	{
		$reservation = BasketReservationTable::getRowById($reservationId);
		if (!$reservation)
		{
			throw new Exception('Reservation not found');
		}
		
		$reservationQuantity = $this->roundQuantity($reservation['QUANTITY']);
		$historyQuantity = $this->roundQuantity($this->getQuantityByReservation($reservationId));
		
		if ($reservationQuantity !== $historyQuantity)
		{
			return $this->addQuantity($reservationId, $reservationQuantity - $historyQuantity);
		}
		
		return new Result();
	}
	
	/**
	 * Delete history row
	 *
	 * @param int $id
	 * @return Result
	 */
	public function delete(int $id): Result
	{
		return BasketReservationHistoryTable::delete($id);
	}
	
	/**
	 * Delete history rows by reservation.
	 * 
	 * All related rows will be deleted!
	 *
	 * @param int $reservationId
	 * @return Result
	 */
	public function deleteByReservation(int $reservationId): Result
	{
		$result = new DeleteResult();
		
		$rows = BasketReservationHistoryTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=RESERVATION_ID' => $reservationId,
			],
		]);
		foreach ($rows as $row)
		{
			$deleteResult = BasketReservationHistoryTable::delete($row['ID']);
			foreach ($deleteResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}
		
		return $result;
	}
}