<?php


namespace Bitrix\Sale;


use Bitrix\Catalog\Product\QuantityControl;
use Bitrix\Sale;

/**
 * Class SaleProviderBase
 * @package Bitrix\Sale
 */
abstract class SaleProviderBase
{
	protected $context = array();
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
		$result->setData(
			array(
				'IS_NEED_SHIP' => array()
			)
		);
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
	 * @param $products
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
}