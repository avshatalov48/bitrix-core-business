<?php
namespace Bitrix\Catalog\Product\Store;

use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\EO_StoreBatch_Collection;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Class Batch
 *
 * @package Bitrix\Catalog\Product\Store
 */
class BatchManager
{
	private int $productId;

	public function __construct(int $productId)
	{
		$this->productId = $productId;
	}

	/**
	 * @return int
	 */
	public function getProductId(): int
	{
		return $this->productId;
	}

	/**
	 * Get store product batches collection.
	 *
	 * @param array|null $filter
	 * @return EO_StoreBatch_Collection
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getStoreCollection(array $filter = null): EO_StoreBatch_Collection
	{
		$filter['=ELEMENT_ID'] = $this->getProductId();

		return StoreBatchTable::getList([
				'filter' => $filter,
				'order' => ['ID' => 'ASC'],
			])
			->fetchCollection()
		;
	}

	/**
	 * Get current available store product batches collection.
	 *
	 * @param int $storeId
	 * @return EO_StoreBatch_Collection
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getAvailableStoreCollection(int $storeId): EO_StoreBatch_Collection
	{
		return $this->getStoreCollection([
			'>AVAILABLE_AMOUNT' => 0,
			'=STORE_ID' => $storeId,
		]);
	}

	/**
	 * Calculate product cost price by quantity.
	 *
	 * @param float $quantity
	 * @param int $storeId
	 * @param string|null $currency
	 * @return float
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function calculateCostPrice(float $quantity, int $storeId, string $currency = null): float
	{
		if (empty($currency) && Loader::includeModule('currency'))
		{
			$currency = CurrencyManager::getBaseCurrency();
		}

		return (new CostPriceCalculator($this))->calculate($quantity, $storeId, $currency);
	}
}