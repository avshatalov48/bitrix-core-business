<?php

namespace Bitrix\Sale\Reservation;

/**
 * Calculation of balances based on the reservation history.
 * 
 * For example:
 * ```php
 * $calculator = new \Bitrix\Sale\Reservation\AvailableQuantityCalculator();
 * 
 * // load store balance
 * $rows = StoreProductTable::getList([...]);
 * foreach ($rows as $row)
 * {
 *     $calculator->setStoreQuantity($row['STORE_ID'], $row['PRODUCT_ID'], $row['AMOUNT']);
 * }
 * 
 * // load history
 * $rows = BasketReservationHistoryTable::getList([...]);
 * foreach ($rows as $row)
 * {
 *     $calculator->addReservationHistory(
 *         $row['STORE_ID'],
 *         $row['PRODUCT_ID'],
 *         $row['BASKET_ID'],
 *         $row['QUANTITY']
 *     );
 * }
 * 
 * // get count
 * $calculator->getQuantityForBatch([
 *     $basketId => $productId,
 *     $basketId => $productId,
 *     // ...
 * ]);
 * $calculator->getQuantityForItem($productId, $basketId, $storeId);
 * ```
 */
class AvailableQuantityCalculator
{
	/**
	 * Quantity product in stores.
	 *
	 * @var array
	 */
	private $storeProductQuantity = [];
	/**
	 * Reservation history (sort is important!).
	 *
	 * @var array
	 */
	private $reservationHistory = [];
	
	/**
	 * Set product store quantity.
	 *
	 * @param int $storeId
	 * @param int $productId
	 * @param float $quantity
	 * @return void
	 */
	public function setStoreQuantity(int $storeId, int $productId, float $quantity): void
	{
		$this->storeProductQuantity[$storeId][$productId] = $quantity;
	}
	
	/**
	 * Add an item reservation history.
	 * The order of addition is IMPORTANT!
	 *
	 * @param int $storeId
	 * @param int $productId
	 * @param int $basketId
	 * @param float $quantity
	 * @return void
	 */
	public function addReservationHistory(int $storeId, int $productId, int $basketId, float $quantity): void
	{
		$this->reservationHistory[] = compact(
			'storeId',
			'productId',
			'basketId',
			'quantity',
		);
	}
	
	/**
	 * Prepared reservation history.
	 * 
	 * Actions:
	 * 1. collapse negative reservations;
	 *
	 * @return array
	 */
	private function getPreparedReservationHistory(): array
	{
		$reverseHistory = array_reverse($this->reservationHistory);
		$negativeReservations = [];
		
		$tmp = [];
		foreach ($reverseHistory as $item)
		{
			$storeId = $item['storeId'];
			$productId = $item['productId'];
			$basketId = $item['basketId'];
			$quantity = $item['quantity'];
			
			$key = join("_", [
				$storeId,
				$productId,
				$basketId,
			]);
			
			if ($quantity < 0.0)
			{
				$negativeReservations[$key] = $negativeReservations[$key] ?? 0.0;
				$negativeReservations[$key] += abs($quantity);
				continue;
			}
			elseif (isset($negativeReservations[$key]))
			{
				$negativeQuantity = $negativeReservations[$key];
				if ($negativeQuantity >= $quantity)
				{
					$negativeQuantity -= $quantity;
					if ($negativeQuantity > 0)
					{
						$negativeReservations[$key] = $negativeQuantity;
					}
					else
					{
						unset($negativeReservations[$key]);
					}
					continue;
				}
				else
				{
					$item['quantity'] -= $negativeQuantity;
					unset($negativeReservations[$key]);
				}
			}
			
			$tmp[] = $item;
		}
		
		return array_reverse($tmp);
	}
	
	/**
	 * Get available for debit product quantity for store.
	 *
	 * @param int $productId
	 * @param int $basketId
	 * @param int $storeId
	 * @return float
	 */
	public function getQuantityForItem(int $productId, int $basketId, int $storeId): float
	{
		$basketItemsStoreQuantity = $this->getQuantityForBatch([
			$basketId => $productId,
		]);
		return $basketItemsStoreQuantity[$basketId][$storeId] ?? 0.0;
	}
	
	/**
	 * Get available for debit product quantity for batch with basket items.
	 *
	 * @param array $basket2productId in format ['basketId' => 'productId', 'basketId' => 'productId', ...]
	 * @return array in format ['basketId' => ['storeId' => 'avaiableQuantity']]
	 */
	public function getQuantityForBatch(array $basket2productId): array
	{
		$basketItemsStoreQuantity = [];
		$currentStoreProductQuantity = $this->storeProductQuantity;
		$preparedReservationHistory = $this->getPreparedReservationHistory();
		
		foreach ($preparedReservationHistory as $item)
		{
			$storeId = $item['storeId'];
			$productId = $item['productId'];
			$basketId = $item['basketId'];
			
			$reservationQuantity = $item['quantity'];
			$storeQuantity = $currentStoreProductQuantity[$storeId][$productId] ?? 0.0;
			
			$isNeedBasketReservation = isset($basket2productId[$basketId]);
			if ($isNeedBasketReservation)
			{
				if ($storeQuantity > 0)
				{
					$basketItemsStoreQuantity[$basketId][$storeId] += min($storeQuantity, $reservationQuantity);
				}
			}
			
			if (isset($currentStoreProductQuantity[$storeId][$productId]))
			{
				$currentStoreProductQuantity[$storeId][$productId] -= $reservationQuantity;	
			}
		}
		
		foreach ($basket2productId as $basketId => $productId)
		{
			foreach ($currentStoreProductQuantity as $storeId => $quantities)
			{
				$storeQuantity = $quantities[$productId] ?? 0.0;
				if ($storeQuantity > 0.0)
				{
					$basketItemsStoreQuantity[$basketId][$storeId] += $storeQuantity;
				}
			}
		}
		
		return $basketItemsStoreQuantity;
	}
}