<?php

namespace Bitrix\Sale;

use Bitrix\Sale;

/**
 * Class SaleProviderBase
 * @package Bitrix\Sale
 */
abstract class SaleProviderBase
{
	public const EMPTY_STORE_ID = 0;

	public const SUMMMARY_PRODUCT_LIST = 'PRODUCT_DATA_LIST';

	public const FLAT_PRICE_LIST = 'PRICE_LIST';
	public const FLAT_AVAILABLE_QUANTITY_LIST = 'AVAILABLE_QUANTITY_LIST';
	public const FLAT_RESERVED_QUANTITY_LIST = 'RESERVED_QUANTITY_LIST';
	public const FLAT_QUANTITY_LIST = 'QUANTITY_LIST';

	public const STORE_AVAILABLE_QUANTITY_LIST = 'AVAILABLE_QUANTITY_LIST_BY_STORE';
	public const STORE_RESERVED_QUANTITY_LIST = 'RESERVED_QUANTITY_LIST_BY_STORE';
	public const STORE_QUANTITY_LIST = 'QUANTITY_LIST_BY_STORE';


	protected $context = [];

	/**
	 * SaleProviderBase constructor.
	 *
	 * @param array $context
	 */
	public function __construct(array $context = array())
	{
		if (!empty($context))
		{
			$this->context = $context;
		}
	}

	/**
	 * @return array
	 */
	protected function getContext()
	{
		return $this->context;
	}

	/**
	 * @param array $products
	 *
	 * @return Result
	 */
	abstract public function getProductData(array $products);

	/**
	 * @param array $products
	 *
	 * @return Result
	 */
	abstract public function getCatalogData(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function tryShip(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	public function isNeedShip(array $products)
	{
		$result = new Sale\Result();
		$result->setData([
			'IS_NEED_SHIP' => [],
		]);
		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function tryUnship(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function ship(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function unship(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getBundleItems(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function reserve(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getAvailableQuantity(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function deliver(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function viewProduct(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getProductListStores(array $products);

	/**
	 * @param array $items
	 *
	 * @return Sale\Result
	 */
	abstract public function checkBarcode(array $items);

	/**
	 * @param array $products
	 *
	 * @return Result
	 */
	abstract public function getAvailableQuantityAndPrice(array $products);

	public function getAvailableQuantityByStore(array $products): Result
	{
		$result = $this->getAvailableQuantity($products);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$data = $result->getData();
		if (empty($data) || empty($data[self::FLAT_AVAILABLE_QUANTITY_LIST]))
		{
			return $result;
		}
		if (!is_array($data[self::FLAT_AVAILABLE_QUANTITY_LIST]))
		{
			$result->setData([]);

			return $result;
		}

		$quantityByStore = [];

		foreach ($products as $productId => $item)
		{
			$quantityByStore[$productId] = $this->distributeQuantityByStore($item['QUANTITY_LIST_BY_STORE'], $data[self::FLAT_AVAILABLE_QUANTITY_LIST][$productId]);
		}

		$result->setData([
			self::STORE_AVAILABLE_QUANTITY_LIST => $quantityByStore
		]);

		return $result;
	}

	private function distributeQuantityByStore($needQuantityList, $availableQuantity) : array
	{
		$result = [];

		foreach ($needQuantityList as $quantityByStore)
		{
			foreach ($quantityByStore as $storeId => $quantity)
			{
				if (abs($quantity) < abs($availableQuantity))
				{
					$result[$storeId] = $quantity;
					$availableQuantity -= $quantity;
				}
				else
				{
					$result[$storeId] = $availableQuantity;
					$availableQuantity = 0;
				}
			}
		}

		return $result;
	}

	public function getAvailableQuantityAndPriceByStore(array $products): Result
	{
		$result = $this->getAvailableQuantityAndPrice($products);

		if (!$result->isSuccess())
		{
			return $result;
		}

		$data = $result->getData();
		if (empty($data) || empty($data[self::SUMMMARY_PRODUCT_LIST]))
		{
			return $result;
		}
		if (!is_array($data[self::SUMMMARY_PRODUCT_LIST]))
		{
			$result->setData([]);

			return $result;
		}

		$summary = $data[self::SUMMMARY_PRODUCT_LIST];

		$priceList = (
				!empty($summary[self::FLAT_PRICE_LIST])
				&& is_array($summary[self::FLAT_PRICE_LIST])
			)
			? $summary[self::FLAT_PRICE_LIST]
			: []
		;
		$quantityList = (
				!empty($summary[self::FLAT_AVAILABLE_QUANTITY_LIST])
				&& is_array($summary[self::FLAT_AVAILABLE_QUANTITY_LIST])
			)
			? $this->getFilledDefaultStore($summary[self::FLAT_AVAILABLE_QUANTITY_LIST])
			: []
		;

		$result->setData([
			self::SUMMMARY_PRODUCT_LIST => [
				self::FLAT_PRICE_LIST => $priceList,
				self::STORE_AVAILABLE_QUANTITY_LIST => $quantityList,
			],
		]);
		unset($quantityList, $priceList, $summary, $data);

		return $result;
	}

	protected function getFilledDefaultStore(array $quantityList): array
	{
		$result = [];
		$storeId = static::getDefaultStoreId();
		foreach (array_keys($quantityList) as $productId)
		{
			$result[$productId] = [
				$storeId => $quantityList[$productId],
			];
		}

		return $result;
	}

	public static function getDefaultStoreId(): int
	{
		return Configuration::getDefaultStoreId();
	}
}
