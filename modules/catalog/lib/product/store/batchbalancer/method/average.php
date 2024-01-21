<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Method;

use Bitrix\Main\Result;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Product\Store\BatchBalancer\ElementBatchTree;
use Bitrix\Catalog\Product\Store\BatchBalancer\Entity;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Catalog\EO_StoreBatch;

/**
 * Class Average
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
final class Average extends Base
{
	public function fill(): Result
	{
		$result = new Result();

		$sortedItems = new ElementBatchTree();
		$negativeStoreItem = new ElementBatchTree();
		/** @var Entity\ElementBatchItem $entity */
		foreach ($this->elementBatchTree as $entity)
		{
			$currentCondition = $this->getStoreCondition($entity);
			$element = $entity->getElement();
			$batch = $currentCondition->getStoreBatch();
			if ($entity->isArrivalElement())
			{
				$availableStoreStoke = $batch->getAvailableAmount() + $element->getAmount();
				if (empty($batch->getPurchasingCurrency()))
				{
					$batch->setPurchasingCurrency($element->getBatchCurrency());
				}

				$documentPrice = $element->getBatchPrice();
				if (
					!empty($batch->getPurchasingCurrency())
					&& $element->getBatchCurrency() !== $batch->getPurchasingCurrency()
					&& Loader::includeModule('currency')
				)
				{
					$documentPrice = \CCurrencyRates::ConvertCurrency(
						$documentPrice,
						$element->getBatchCurrency(),
						$batch->getPurchasingCurrency()
					);
				}

				$newPrice = (($batch->getAvailableAmount() * $batch->getPurchasingPrice()) + ($element->getAmount() * $documentPrice)) / $availableStoreStoke;
				$batch->setAvailableAmount($availableStoreStoke);
				$batch->setPurchasingPrice($newPrice);
				$sortedItems->push($entity);

				if (!empty($negativeStoreItem))
				{
					/** @var Entity\ElementBatchItem $negativeItem */
					foreach ($negativeStoreItem->getIterator() as $key => $negativeItem)
					{
						if ($batch->getAvailableAmount() <= 0)
						{
							break;
						}

						$currentStock = $batch->getAvailableAmount() - $negativeItem->getAmount();
						$entity = clone($negativeItem);
						$newBindingElement = $entity->getElement();
						if ($currentStock >= 0)
						{
							$newBindingElement->setBatchPrice($batch->getPurchasingPrice());
							$newBindingElement->setBatchCurrency($batch->getPurchasingCurrency());
							$batch->setAvailableAmount($currentStock);
							$sortedItems->push($entity);
							unset($negativeStoreItem[$key]);
						}
						else
						{
							$newBindingElement->setBatchPrice($batch->getPurchasingPrice());
							$newBindingElement->setBatchCurrency($batch->getPurchasingCurrency());
							$newBindingElement->setAmount($batch->getAvailableAmount());
							$sortedItems->push($entity);

							$negativeItem->getElement()->setAmount(-$currentStock);
							$batch->setAvailableAmount(0);
						}
					}
				}
			}
			else
			{
				$currentStock = $batch->getAvailableAmount() - $entity->getAmount();
				if ($batch->getAvailableAmount() <= 0)
				{
					$negativeStoreItem[] = $entity;
				}
				elseif ($currentStock >= 0)
				{
					$element->setBatchPrice($batch->getPurchasingPrice());
					$element->setBatchCurrency($batch->getPurchasingCurrency());
					$sortedItems->push($entity);
				}
				else
				{
					$negativeItem = clone($entity);
					$element->setBatchPrice($batch->getPurchasingPrice());
					$element->setBatchCurrency($batch->getPurchasingCurrency());
					$element->setAmount(-$batch->getAvailableAmount());
					$sortedItems->push($entity);

					$negativeItem->getElement()->setAmount(-$currentStock);
					$negativeStoreItem->push($negativeItem);

					$batch->setAvailableAmount(0);
				}
			}
		}

		/** @var Entity\StoreItem $item */
		foreach ($this->storeConditions as $item)
		{
			if ($item->getStoreBatch()->hasId())
			{
				$oldBindings = StoreBatchDocumentElementTable::getList([
					'filter' => ['=PRODUCT_BATCH_ID' => $item->getStoreBatch()->getId()],
					'select' => ['ID'],
				]);

				while ($binding = $oldBindings->fetch())
				{
					StoreBatchDocumentElementTable::delete($binding['ID']);
				}
			}
			$result = $item->save();

			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		/** @var Entity\ElementBatchItem $item */
		foreach ($sortedItems as $item)
		{
			/** @var Entity\StoreItem $storeItem */
			$storeItem = $this->storeConditions[$item->getStoreId()];
			if (!empty($storeItem) && $storeItem->getStoreBatch())
			{
				$item->getElement()->setProductBatchId($storeItem->getStoreBatch()->getId());
				$result = $item->save();
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}

		return $result;
	}

	/**
	 * @param Entity\ElementBatchItem $entity
	 * @return Entity\StoreItem[]
	 */
	private function getStoreCondition(Entity\ElementBatchItem $entity): Entity\StoreItem
	{
		if (isset($this->storeConditions[$entity->getStoreId()]))
		{
			return $this->storeConditions[$entity->getStoreId()];
		}

		$batch = $this->loadBatch($entity->getStoreId());
		$batch->setAvailableAmount(0);
		$batch->setPurchasingPrice(0);
		$newStoreItem = new Entity\StoreItem(
			$batch
		);

		$this->storeConditions[$entity->getStoreId()] = $newStoreItem;

		return $this->storeConditions[$entity->getStoreId()];
	}

	private function loadBatch(int $storeId): EO_StoreBatch
	{
		$batchRaw = StoreBatchTable::getList([
				'filter' => [
					'=STORE_ID' => $storeId,
					'=ELEMENT_ID' => $this->balancer->getProductId(),
				],
				'select' => ['ID'],
				'limit' => 1,
			])
			->fetchObject()
		;

		if ($batchRaw)
		{
			return $batchRaw;
		}

		$newBatch = new EO_StoreBatch();
		$newBatch->setStoreId($storeId);
		$newBatch->setElementId($this->balancer->getProductId());
		$newBatch->setPurchasingPrice(0);

		return $newBatch;
	}
}