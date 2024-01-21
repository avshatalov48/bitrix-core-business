<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\EO_StoreBatchDocumentElement;
use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Result;

/**
 * Reduce amount for existed batch of products.
 */
class ReduceStoreBatchAmountAction implements Action
{
	use WriteOffAmountValidator;

	protected int $storeId;

	protected int $productId;
	protected ?EO_StoreDocumentElement $storeDocumentElement;
	public function __construct(int $documentElementId)
	{
		$this->storeDocumentElement = StoreDocumentElementTable::getList([
				'filter' => [
					'=ID' => $documentElementId,
				],
				'select' => ['*', 'DOCUMENT'],
				'limit' => 1
			])
			->fetchObject()
		;

		if ($this->storeDocumentElement)
		{
			$this->productId = $this->storeDocumentElement->getElementId();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		if ($this->storeDocumentElement)
		{
			$this->storeId = $this->storeDocumentElement->getStoreTo();
		}

		return $this->checkStoreAmount($this->storeDocumentElement);
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$storeBatches = StoreBatchTable::getList([
				'filter' => [
					'=S_BATCH_ELEMENT.DOCUMENT_ELEMENT_ID' => $this->storeDocumentElement->getId(),
					'>S_BATCH_ELEMENT.AMOUNT' => 0,
				],
				'runtime' => [
					new Reference(
						'S_BATCH_ELEMENT',
						StoreBatchDocumentElementTable::class,
						Join::on('this.ID', 'ref.PRODUCT_BATCH_ID')
					)
				],
				'select' => ['*', 'S_BATCH_ELEMENT'],
			])
			->fetchCollection()
		;

		$batchIds = $storeBatches->getIdList();
		if (empty($batchIds))
		{
			return new Result();
		}

		foreach ($storeBatches as $batch)
		{
			if (CostPriceCalculator::getMethod() === CostPriceCalculator::METHOD_FIFO)
			{
				if ($this->storeDocumentElement->getAmount() > $batch->getAvailableAmount())
				{
					$this->rebindWriteOffValues($batch, $batchIds);
				}

				$resultDelete = $batch->delete();
				if (!$resultDelete->isSuccess())
				{
					return $resultDelete;
				}
			}
			else
			{
				/** @var EO_StoreBatchDocumentElement $oldBatchElement */
				$oldBatchElement = $batch->get('S_BATCH_ELEMENT');
				$purchasingPrice = $oldBatchElement->getBatchPrice();
				if (
					$this->storeDocumentElement->getDocument()
					&& $this->storeDocumentElement->getDocument()->getCurrency() !== $batch->getPurchasingCurrency()
					&& Loader::includeModule('currency')
				)
				{
					$purchasingPrice = \CCurrencyRates::convertCurrency(
						$this->storeDocumentElement->getPurchasingPrice(),
						$this->storeDocumentElement->getDocument()->getCurrency(),
						$batch->getPurchasingCurrency()
					);
				}

				$newAmount = max($batch->getAvailableAmount() - $oldBatchElement->getAmount(), 0);
				if ($newAmount > 0)
				{
					$newSum = $batch->getAvailableAmount() * $batch->getPurchasingPrice() - $oldBatchElement->getAmount() * $purchasingPrice;
					$newPurchasingPrice = $newSum / $newAmount;
					$precision = (int)Option::get('sale', 'value_precision', 2);
					$newPurchasingPrice = round($newPurchasingPrice, $precision);
					$batch->setPurchasingPrice($newPurchasingPrice);
				}
				$batch->setAvailableAmount($newAmount);
				$batch->save();
			}
		}

		$bindings = StoreBatchDocumentElementTable::getList([
			'filter' => [
				'=PRODUCT_BATCH_ID' => $batchIds,
				'=DOCUMENT_ELEMENT_ID' => $this->storeDocumentElement->getId(),
				'>AMOUNT' => 0,
			]
		]);

		while ($binding = $bindings->fetch())
		{
			StoreBatchDocumentElementTable::delete($binding['ID']);
		}

		return new Result();
	}

	/**
	 * Rebind write off store bindings from old batch to available batch
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function rebindWriteOffValues(EO_StoreBatch $oldBatch, array $prohibitedBatchIds = []): void
	{
		$batchManager = new BatchManager($oldBatch->getElementId());
		$currentStoreCollection = $batchManager->getAvailableStoreCollection($oldBatch->getStoreId());
		$bindings = StoreBatchDocumentElementTable::getList([
			'filter' => [
				'PRODUCT_BATCH_ID' => $oldBatch->getId(),
				'<AMOUNT' => 0,
			],
		]);

		$prohibitedBatchIds[] = $oldBatch->getId();
		while ($binding = $bindings->fetch())
		{
			$amountBindingValue = abs($binding['AMOUNT']);
			$oldAmount = $amountBindingValue;
			foreach ($currentStoreCollection as $batchItem)
			{
				if (in_array($batchItem->getId(), $prohibitedBatchIds, true))
				{
					continue;
				}

				$storeAmount = $batchItem->getAvailableAmount();
				if ($oldAmount <= 0)
				{
					break;
				}

				$outStoreQuantity = ($storeAmount > $oldAmount) ? $oldAmount : $storeAmount;
				$newAvailableAmount = $storeAmount - $outStoreQuantity;
				$batchItem->setAvailableAmount($newAvailableAmount);
				if ($oldAmount === $amountBindingValue)
				{
					$updateFields = [
						'PRODUCT_BATCH_ID' => $batchItem->getId(),
						'AMOUNT' => -$outStoreQuantity,
					];

					StoreBatchDocumentElementTable::update($binding['ID'], $updateFields);
				}
				else
				{
					StoreBatchDocumentElementTable::add([
						'DOCUMENT_ELEMENT_ID' => $binding['DOCUMENT_ELEMENT_ID'],
						'SHIPMENT_ITEM_STORE_ID' => $binding['SHIPMENT_ITEM_STORE_ID'],
						'AMOUNT' => -$outStoreQuantity,
						'PRODUCT_BATCH_ID' => $batchItem->getId(),
						'BATCH_PRICE' => $binding['BATCH_PRICE'],
						'BATCH_CURRENCY' => $binding['BATCH_CURRENCY'],
					]);
				}

				$oldAmount -= $storeAmount;
			}
		}

		$currentStoreCollection->save();
	}
}