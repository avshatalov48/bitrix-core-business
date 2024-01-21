<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Method;

use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\BatchBalancer\ElementBatchTree;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Main\Result;
use Bitrix\Catalog\Product\Store\BatchBalancer\Entity;

/**
 * Class Fifo
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
final class Fifo extends Base
{
	public function fill(): Result
	{
		$result = new Result();

		$sortedItems = new ElementBatchTree();
		$negativeStoreItems = [];
		/** @var Entity\ElementBatchItem $entity */
		foreach ($this->elementBatchTree as $entity)
		{
			$element = $entity->getElement();
			$storeConditions = $this->getStoreConditions($entity);
			if ($entity->isArrivalElement())
			{
				$newBatch = new EO_StoreBatch();
				$newBatch->setStoreId($entity->getStoreId());
				$newBatch->setAvailableAmount($entity->getAmount());
				$newBatch->setElementId($this->balancer->getProductId());
				$newBatch->setPurchasingPrice($element->getBatchPrice());
				$newBatch->setPurchasingCurrency($element->getBatchCurrency());
				$newStoreItem = new Entity\StoreItem($newBatch);
				$storeConditions[$newStoreItem->getHash()] = $newStoreItem;
				$entity->setStoreItemHash($newStoreItem->getHash());
				$sortedItems->push($entity);

				$negativeStoreItem = $negativeStoreItems[$entity->getStoreId()] ?? null;
				if (!empty($negativeStoreItem))
				{
					/** @var Entity\ElementBatchItem $negativeItem */
					foreach ($negativeStoreItem as $key => $negativeItem)
					{
						if ($negativeItem->getStoreId() !== $entity->getStoreId())
						{
							continue;
						}

						foreach ($storeConditions as $storeCondition)
						{
							$currentStock = $storeCondition->getStoreBatch()->getAvailableAmount() - $negativeItem->getAmount();
							$resortedEntity = new Entity\ElementBatchItem(
								clone($negativeItem->getElement()),
								$negativeItem->getStoreId()
							);
							$element = $resortedEntity->getElement();
							$element->setBatchPrice($storeCondition->getStoreBatch()->getPurchasingPrice());
							$element->setBatchCurrency($storeCondition->getStoreBatch()->getPurchasingCurrency());
							$resortedEntity->setStoreItemHash($storeCondition->getHash());
							if ($currentStock >= 0)
							{
								$sortedItems->push($resortedEntity);
								$storeCondition->getStoreBatch()->setAvailableAmount($currentStock);

								unset($negativeStoreItem[$key]);
							}
							else
							{
								$element->setAmount($storeCondition->getStoreBatch()->getAvailableAmount());
								$sortedItems->push($resortedEntity);

								$storeCondition->getStoreBatch()->setAvailableAmount(0);

								$negativeItem->getElement()->setAmount(-$currentStock);
							}
						}
					}
				}
			}
			else
			{
				$fullCompleted = false;
				/** @var Entity\StoreItem $storeCondition */
				foreach ($storeConditions as $storeCondition)
				{
					$batch = $storeCondition->getStoreBatch();
					if ($batch->getAvailableAmount() <= 0)
					{
						continue;
					}

					$currentStock = $batch->getAvailableAmount() - $entity->getAmount();
					if ($currentStock >= 0)
					{
						$batch->setAvailableAmount($currentStock);
						$entity->setStoreItemHash($storeCondition->getHash());
						$element->setBatchPrice($storeCondition->getStoreBatch()->getPurchasingPrice());
						$element->setBatchCurrency($storeCondition->getStoreBatch()->getPurchasingCurrency());
						$sortedItems->push($entity);
						$fullCompleted = true;

						break;
					}
					else
					{
						$newBinding = new Entity\ElementBatchItem(clone($element), $entity->getStoreId());
						$newBinding->setStoreItemHash($storeCondition->getHash());
						$newElement = $newBinding->getElement();
						$newElement->setAmount(-$batch->getAvailableAmount());
						$newElement->setBatchPrice($storeCondition->getStoreBatch()->getPurchasingPrice());
						$newElement->setBatchCurrency($storeCondition->getStoreBatch()->getPurchasingCurrency());
						$sortedItems->push($newBinding);
						$batch->setAvailableAmount(0);
						$element->setAmount($currentStock);
					}
				}

				if (!$fullCompleted)
				{
					$negativeItem = clone($entity);
					$negativeStoreItems[$negativeItem->getStoreId()] ??= [];
					$negativeStoreItems[$negativeItem->getStoreId()] = $negativeItem;
				}
			}

			$this->setStoreConditions($entity->getStoreId(), $storeConditions);
		}

		$oldBatchIds = [];
		$oldBatches= StoreBatchTable::getList([
			'filter' => ['=ELEMENT_ID' => $this->balancer->getProductId()],
			'select' => ['ID'],
		]);

		while ($batch = $oldBatches->fetch())
		{
			$oldBatchIds[] = $batch['ID'];
			StoreBatchTable::delete($batch['ID']);
		}

		$oldBindings = StoreBatchDocumentElementTable::getList([
			'filter' => ['=PRODUCT_BATCH_ID' => $oldBatchIds],
			'select' => ['ID'],
		]);

		while ($binding = $oldBindings->fetch())
		{
			StoreBatchDocumentElementTable::delete($binding['ID']);
		}

		foreach ($this->storeConditions as $storeCondition)
		{
			foreach ($storeCondition as $item)
			{
				$result = $item->save();
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}

		/** @var Entity\ElementBatchItem $item */
		foreach ($sortedItems as $item)
		{
			if (isset($this->storeConditions[$item->getStoreId()][$item->getStoreItemHash()]))
			{
				/** @var Entity\StoreItem $storeItem */
				$storeItem = $this->storeConditions[$item->getStoreId()][$item->getStoreItemHash()];
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

	private function getStoreConditions(Entity\ElementBatchItem $entity): array
	{
		if (isset($this->storeConditions[$entity->getStoreId()]))
		{
			return $this->storeConditions[$entity->getStoreId()];
		}

		$this->storeConditions[$entity->getStoreId()] = [];

		return $this->storeConditions[$entity->getStoreId()];
	}

	private function setStoreConditions(int $storeId, array $storeConditions): void
	{
		$this->storeConditions[$storeId] ??= [];
		$this->storeConditions[$storeId] = $storeConditions;
	}
}