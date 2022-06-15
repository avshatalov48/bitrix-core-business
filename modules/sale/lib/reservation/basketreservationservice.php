<?php

namespace Bitrix\Sale\Reservation;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Sale\Reservation\Internals\BasketReservationTable;

/**
 * Service for working with basket reserves
 */
class BasketReservationService
{
	/**
	 * @var BasketReservationHistoryService
	 */
	protected $historyService;

	/**
	 * @param BasketReservationHistoryService $historyService
	 */
	public function __construct(
		BasketReservationHistoryService $historyService
	)
	{
		$this->historyService = $historyService;
	}

	/**
	 * Service instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		return ServiceLocator::getInstance()->get('sale.basketReservation');
	}

	/**
	 * Add reservation row
	 *
	 * @param array $fields
	 * @return Result
	 */
	public function add(array $fields): Result
	{
		$result = BasketReservationTable::add($fields);

		if ($result->isSuccess())
		{
			$historyResult = $this->historyService->addByReservation($result->getId());
			foreach ($historyResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}

		return $result;
	}

	/**
	 * Update reservation row
	 *
	 * @param int $id
	 * @param array $fields
	 * @return Result
	 */
	public function update(int $id, array $fields): Result
	{
		$result = BasketReservationTable::update($id, $fields);

		if ($result->isSuccess())
		{
			$historyResult = $this->historyService->updateByReservation($id);
			foreach ($historyResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}

		return $result;
	}

	/**
	 * Delete reservation row
	 *
	 * @param int $id
	 * @return Result
	 */
	public function delete(int $id): Result
	{
		$result = BasketReservationTable::delete($id);

		if ($result->isSuccess())
		{
			$historyResult = $this->historyService->deleteByReservation($id);
			foreach ($historyResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}

		return $result;
	}

	/**
	 * The available amount to be debited based on the reservation history.
	 *
	 * @see BasketReservationHistoryService::getAvailableCountForOrder
	 *
	 * @param int $orderId
	 * @return array
	 */
	public function getAvailableCountForOrder(int $orderId): array
	{
		return $this->historyService->getAvailableCountForOrder($orderId);
	}

	/**
	 * The available amount to be debited based on the reservation history.
	 *
	 * @see BasketReservationHistoryService::getAvailableCountForBasketItem
	 *
	 * @param int $basketId
	 * @param int $storeId
	 * @return float
	 */
	public function getAvailableCountForBasketItem(int $basketId, int $storeId): float
	{
		return $this->historyService->getAvailableCountForBasketItem($basketId, $storeId);
	}
}