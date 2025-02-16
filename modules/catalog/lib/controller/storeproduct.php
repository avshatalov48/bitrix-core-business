<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Catalog\Store\EnableWizard\Manager;

final class StoreProduct extends Controller
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check

	private const BULK_SAVE_CHUNK_SIZE = 10000;
	private const BULK_SAVE_PRODUCTS_CHUNK_SIZE = 1000;

	//region Actions
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

	public function bulkSaveAction(array $items)
	{
		if (!State::isUsedInventoryManagement())
		{
			$this->addError(
				new Error(
					'Inventory management is not enabled',
					200040400010
				)
			);

			return null;
		}

		if (!Manager::isOnecMode())
		{
			$this->addError(
				new Error(
					'Inventory management is not in 1C mode',
					200040400020
				)
			);

			return null;
		}

		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$storeIds = array_flip(
			array_map(
				static fn($row) => (int)$row['ID'],
				StoreTable::getList([
					'select' => ['ID'],
					'cache' => ['ttl' => 86400],
				])->fetchAll()
			)
		);

		foreach (array_chunk($items, self::BULK_SAVE_CHUNK_SIZE) as $chunkItems)
		{
			$chunkProductIds = array_unique(
				array_map(
					\Closure::fromCallable('intval'),
					array_column($chunkItems, 'productId')
				)
			);
			$productIds = [];
			if ($chunkProductIds)
			{
				$productIds = array_flip(
					array_map(
						static fn($row) => (int)$row['ID'],
						ProductTable::query()
							->setSelect(['ID'])
							->whereIn('ID', $chunkProductIds)
							->fetchAll()
					)
				);
			}

			$insertRows = [];
			foreach ($chunkItems as $item)
			{
				$productId = isset($item['productId']) ? (int)$item['productId'] : 0;
				if (!isset($productIds[$productId]))
				{
					continue;
				}

				$storeId = isset($item['storeId']) ? (int)$item['storeId'] : 0;
				if (!isset($storeIds[$storeId]))
				{
					continue;
				}

				$amount = isset($item['value']['amount']) ? (float)$item['value']['amount'] : 0;
				$quantityReserved = isset($item['value']['quantityReserved'])
					? (float)$item['value']['quantityReserved']
					: 0
				;

				$insertRows[] = [
					'PRODUCT_ID' => $productId,
					'STORE_ID' => $storeId,
					'AMOUNT' => $amount,
					'QUANTITY_RESERVED' => $quantityReserved,
				];
			}

			if (!$insertRows)
			{
				continue;
			}

			$sqls = $sqlHelper->prepareMergeMultiple(
				StoreProductTable::getTableName(),
				[
					'PRODUCT_ID',
					'STORE_ID',
				],
				$insertRows
			);
			foreach ($sqls as $sql)
			{
				$connection->query($sql);
			}
		}

		$allProductIds = array_unique(
			array_map(
				\Closure::fromCallable('intval'),
				array_column($items, 'productId')
			)
		);
		foreach (array_chunk($allProductIds, self::BULK_SAVE_PRODUCTS_CHUNK_SIZE) as $chunkProductIds)
		{
			Application::getInstance()->addBackgroundJob(function() use ($chunkProductIds) {
				\CCatalogStore::recalculateProductsBalances($chunkProductIds);
			});
		}

		return true;
	}
	//endregion

	protected function getEntityTable()
	{
		return new StoreProductTable();
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!(
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
		))
		{
			$r->addError($this->getErrorReadAccessDenied());
		}
		return $r;
	}

	protected function checkPermissionEntity($name, $arguments = [])
	{
		$name = mb_strtolower($name); //for ajax mode

		if ($name == 'bulksave')
		{
			return $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}

		return $r;
	}
}
