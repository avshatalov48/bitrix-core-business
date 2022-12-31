<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store;

use Bitrix\Catalog\Integration\Report\StoreStock\Entity\ProductInfo;

class StoreWithProductsInfo extends StoreInfo
{
	protected array $productList = [];

	/**
	 * Return array of <b>ProductInfo</b> instances
	 * @return array
	 */
	public function getProductList(): array
	{
		return $this->productList;
	}

	/**
	 * Add product to storage instance or summarize quantity if it exists
	 * @param ProductInfo ...$product
	 * @return void
	 */
	public function addProduct(ProductInfo ...$product): void
	{
		array_push($this->productList, ...$product);
	}

	/**
	 * Return sum of price * amount of all products in StoreInfo
	 * @return float
	 */
	public function getCalculatedSumPrice(): float
	{
		$sum = 0.0;

		/** @var ProductInfo $product */
		foreach ($this->getProductList() as $product)
		{
			$sum += $product->getPrice() * $product->getQuantity();
		}

		return $sum;
	}
}