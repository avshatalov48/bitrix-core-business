<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock\Entity;

use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

final class ProductInfo
{
	private static array $productNameList = [];

	private int $productId;
	private float $quantity;
	private static array $productPrice;

	public function __construct(int $productId, float $quantity)
	{
		$this->productId = $productId;
		$this->quantity = $quantity;
	}

	public function getProductId(): int
	{
		return $this->productId;
	}

	public function getQuantity(): float
	{
		return $this->quantity;
	}

	/**
	 * Return float value of product price
	 * <br>if price was initialized by <b>setPrice</b> method, it returns it
	 * <br>otherwise it will try to initialize a base price of a product. On failure, it will return 0
	 * @return float
	 */
	public function getPrice(): float
	{
		if (!isset(self::$productPrice[$this->productId]))
		{
			self::initBasePrice($this->productId);
		}

		if (!isset(self::$productPrice[$this->productId]))
		{
			self::$productPrice[$this->productId] = 0.0;
		}

		return self::$productPrice[$this->productId];
	}

	/**
	 * Initialize base prices of products with id <b>$productId</b> in base currency
	 * @param int ...$productId ids of products
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function initBasePrice(int ...$productId): void
	{
		$defaultCurrency = CurrencyManager::getBaseCurrency();
		$productsData = ProductTable::getList([
			'select' => [
				'ID',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
				'PURCHASING_CURRENCY_AMOUNT' => 'CURRENCY_TABLE.CURRENT_BASE_RATE',
			],
			'filter' => [
				'=ID' => $productId,
			],
			'runtime' => [
				(new Reference(
					'CURRENCY_TABLE',
					CurrencyTable::class,
					Join::on('this.PURCHASING_CURRENCY', 'ref.CURRENCY')
				))->configureJoinType(Join::TYPE_LEFT),
			],
		])->fetchAll();

		foreach ($productsData as $product)
		{
			$productPrice = (float)$product['PURCHASING_PRICE'];
			if ($product['PURCHASING_CURRENCY'] !== $defaultCurrency)
			{
				$defaultCurrencyAmount = (float)\CCurrency::getCurrency($defaultCurrency)['CURRENT_BASE_RATE'];
				$currentCurrencyAmount = (float)$product['PURCHASING_CURRENCY_AMOUNT'];

				$productPrice *= $currentCurrencyAmount;
				$productPrice /= $defaultCurrencyAmount;
			}

			self::setPrice($product['ID'], $productPrice);
		}
	}

	/**
	 * Set price of product.
	 * <br>Use it if you need to set up a custom price of product.
	 * @param int $productId
	 * @param float $productPrice
	 * @return void
	 */
	public static function setPrice(int $productId, float $productPrice): void
	{
		self::$productPrice[$productId] = $productPrice;
	}
}