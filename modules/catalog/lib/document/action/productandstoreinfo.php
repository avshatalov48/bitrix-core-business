<?php

namespace Bitrix\Catalog\Document\Action;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

/**
 * Trait with helpers methods for product and store
 */
trait ProductAndStoreInfo
{
	/**
	 * @var array|null
	 */
	private $productRow;

	/**
	 * @var array|null
	 */
	private $storeProductRow;

	/**
	 * Store.
	 *
	 * @return int|null
	 */
	public function getStoreId(): ?int
	{
		return $this->storeId ?? null;
	}

	/**
	 * Product.
	 *
	 * @return int|null
	 */
	public function getProductId(): ?int
	{
		return $this->productId ?? null;
	}

	/**
	 * Store name if exists.
	 *
	 * @return string|null
	 */
	protected function getStoreName(): ?string
	{
		static $cache = [];

		$storeId = $this->getStoreId();
		if (!$storeId)
		{
			return '';
		}

		if (!array_key_exists($storeId, $cache))
		{
			$cache[$storeId] = null;

			$row = StoreTable::getRow([
				'select' => [
					'TITLE',
					'ADDRESS',
				],
				'filter' => [
					'=ID' => $storeId,
				],
			]);
			if ($row)
			{
				$cache[$storeId] = (string)($row['TITLE'] ?: $row['ADDRESS']);
			}
		}

		return $cache[$storeId];
	}

	/**
	 * Get product row.
	 *
	 * @return array if product info is not found in store, returns an empty array.
	 */
	protected function getStoreProductRow(): array
	{
		if (!isset($this->storeProductRow))
		{
			if ($this->getProductId() && $this->getStoreId())
			{
				$this->storeProductRow = StoreProductTable::getRow([
					'select' => [
						'AMOUNT',
						'QUANTITY_RESERVED',
					],
					'filter' => [
						'=PRODUCT_ID' => $this->getProductId(),
						'=STORE_ID' => $this->getStoreId(),
					],
				]) ?: [];
			}
			else
			{
				$this->storeProductRow = [];
			}
		}

		return $this->storeProductRow;
	}

	/**
	 * The amount of the product at the moment.
	 *
	 * @return float
	 */
	protected function getStoreProductAmount(): float
	{
		return (float)($this->getStoreProductRow()['AMOUNT'] ?? 0.0);
	}

	/**
	 * Get product reserved quantity on store.
	 *
	 * @return float
	 */
	protected function getStoreReservedQuantity(): float
	{
		return (float)($this->getStoreProductRow()['QUANTITY_RESERVED'] ?? 0.0);
	}

	/**
	 * Product name if exists.
	 *
	 * @return string|null
	 */
	protected function getProductName(): ?string
	{
		static $cache = [];

		$productId = $this->getProductId();
		if (!$productId)
		{
			return null;
		}

		if (!array_key_exists($productId, $cache))
		{
			Loader::includeModule('iblock');

			$row = ElementTable::getRow([
				'select' => [
					'NAME',
				],
				'filter' => [
					'=ID' => $productId,
				],
			]);
			$cache[$productId] = $row ? (string)$row['NAME'] : null;
		}

		return $cache[$productId];
	}

	/**
	 * Get product row.
	 *
	 * @return array|null
	 */
	protected function getProductRow(): ?array
	{
		if (!$this->productRow)
		{
			if ($this->getProductId())
			{
				$this->productRow = ProductTable::getRow([
					'select' => [
						'QUANTITY',
						'QUANTITY_RESERVED',
					],
					'filter' => [
						'=ID' => $this->getProductId(),
					],
				]) ?: null;
			}
			else
			{
				$this->productRow = null;
			}
		}
		return $this->productRow;
	}

	/**
	 * Get product total quantity.
	 *
	 * @return float
	 */
	protected function getProductTotalQuantity(): float
	{
		return (float)($this->getProductRow()['QUANTITY'] ?? 0.0);
	}

	/**
	 * Get product total reserved quantity.
	 *
	 * @return float
	 */
	protected function getProductTotalReservedQuantity(): float
	{
		return (float)($this->getProductRow()['QUANTITY_RESERVED'] ?? 0.0);
	}
}
