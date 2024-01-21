<?php
namespace Bitrix\Catalog\Product\Store\DistributionStrategy;

use Bitrix\Catalog\EO_StoreBatchDocumentElement;
use Bitrix\Catalog\EO_StoreBatchDocumentElement_Collection;
use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

/**
 * Class Batch
 *
 * @package Bitrix\Catalog\Product\Price
 */
abstract class Base
{
	protected ?int $storeId;
	protected BatchManager $batchManager;

	public function __construct(BatchManager $batchManager, int $storeId)
	{
		$this->batchManager = $batchManager;
		$this->storeId = $storeId;
	}

	public function setStoreId(int $storeId): static
	{
		if ($storeId <= 0)
		{
			return $this;
		}

		$this->storeId = $storeId;

		return $this;
	}

	abstract protected function addRegistryItem(EO_StoreBatch $batchItem, float $amount): Result;
	abstract protected function getRegistryItems(): EO_StoreBatchDocumentElement_Collection;

	public function writeOff(float $quantity): Result
	{
		$storeCollection = $this->batchManager->getAvailableStoreCollection($this->storeId);

		foreach ($storeCollection as $batchItem)
		{
			$amount = $batchItem->getAvailableAmount();
			if ($quantity <= 0)
			{
				break;
			}

			$outStoreQuantity = ($amount > $quantity) ? $quantity : $amount;
			$newAvailableAmount = $amount - $outStoreQuantity;
			$batchItem->setAvailableAmount($newAvailableAmount);

			$this->addRegistryItem($batchItem, $outStoreQuantity);

			$quantity -= $amount;
		}

		return $storeCollection->save();
	}

	public function return(): Result
	{
		$result = new Result();
		$items = $this->getRegistryItems();

		if ($items->isEmpty())
		{
			$result->addError(new Error('Shipment item was not found'));

			return $result;
		}

		$storeCollection = $this->batchManager->getStoreCollection([
			'=ID' => $items->getProductBatchIdList(),
		]);

		foreach ($items as $item)
		{
			$batchItem = $storeCollection->getByPrimary($item->getProductBatchId());
			if ($batchItem === null)
			{
				continue;
			}

			if (CostPriceCalculator::getMethod() === CostPriceCalculator::METHOD_AVERAGE)
			{
				$newPurchasingPrice = $this->recalculateAveragePurchasingPrice($batchItem, $item);

				$batchItem->setPurchasingPrice($newPurchasingPrice);
			}

			$currentAmount = $batchItem->getAvailableAmount();
			$batchItem->setAvailableAmount($currentAmount + abs($item->getAmount()));
		}

		$result = $storeCollection->save();
		if ($result->isSuccess())
		{
			foreach ($items as $item)
			{
				StoreBatchDocumentElementTable::delete($item->getId());
			}
		}

		return $result;
	}

	private function recalculateAveragePurchasingPrice(
		EO_StoreBatch $batch,
		EO_StoreBatchDocumentElement $item
	): float
	{
		$itemBatchPrice = $item->getBatchPrice();
		if (
			$item->getBatchCurrency() !== $batch->getPurchasingCurrency()
			&& Loader::includeModule('currency')
		)
		{
			$itemBatchPrice = \CCurrencyRates::convertCurrency(
				$itemBatchPrice,
				$item->getBatchCurrency(),
				$batch->getPurchasingCurrency()
			);
		}

		$itemAmount = abs($item->getAmount());
		$sum = $itemBatchPrice * $itemAmount + $batch->getPurchasingPrice() * $batch->getAvailableAmount();
		$newPurchasingPrice = $sum / ($itemAmount + $batch->getAvailableAmount());
		$precision = (int)Option::get('sale', 'value_precision', 2);

		return round($newPurchasingPrice, $precision);
	}
}