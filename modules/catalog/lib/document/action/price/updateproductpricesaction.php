<?php

namespace Bitrix\Catalog\Document\Action\Price;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\PriceTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use CCatalogGroup;

/**
 * Update product prices action.
 *
 * If the price values is `null`, they are not processed.
 * To reset price values, you need to set `0`.
 */
class UpdateProductPricesAction implements Action
{
	private int $productId;
	private ?float $purchasePrice;
	private ?string $purchasePriceCurrency;
	private ?float $basePrice;
	private ?string $basePriceCurrency;

	/**
	 * @param int $productId
	 * @param float|null $purchasePrice
	 * @param string|null $purchasePriceCurrency
	 * @param float|null $basePrice
	 * @param string|null $basePriceCurrency
	 */
	public function __construct(
		int $productId,
		?float $purchasePrice,
		?string $purchasePriceCurrency,
		?float $basePrice = null,
		?string $basePriceCurrency = null
	)
	{
		$this->productId = $productId;
		$this->purchasePrice = $purchasePrice;
		$this->purchasePriceCurrency = $purchasePriceCurrency;
		$this->basePrice = $basePrice;
		$this->basePriceCurrency = $basePriceCurrency;
	}

	/**
	 * Default currency.
	 *
	 * If module `currency` is installed, get the value from it.
	 * Else get currency by lang.
	 *
	 * @return string
	 */
	private function getDefaultCurrency(): string
	{
		static $currency;

		if ($currency)
		{
			return $currency;
		}
		elseif (Loader::includeModule('currency'))
		{
			$currency = CurrencyManager::getBaseCurrency();
		}
		else
		{
			$currency = LANGUAGE_ID === 'ru' ? 'RUB' : 'EUR';
		}

		return $currency;
	}

	/**
	 * Row id with base price type of product.
	 *
	 * @return int|null
	 */
	private function getBasePriceRowId(): ?int
	{
		$row = PriceTable::getRow([
			'select' => [
				'ID',
			],
			'filter' => [
				'=PRODUCT_ID' => $this->productId,
				'=CATALOG_GROUP_ID' => $this->getBasePriceGroupId(),
			],
			'order' => [
				'ID' => 'asc',
			],
		]);
		return $row['ID'] ?? null;
	}

	/**
	 * Base price type id.
	 *
	 * @return int|null
	 */
	private function getBasePriceGroupId(): ?int
	{
		return CCatalogGroup::GetBaseGroupId();
	}

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$result = new Result();

		if (isset($this->purchasePrice))
		{
			$saveResult = Product::update($this->productId, [
				'PURCHASING_PRICE' => $this->purchasePrice ?: null, // if price is 0.0 - clear value
				'PURCHASING_CURRENCY' => $this->purchasePriceCurrency ?? $this->getDefaultCurrency(),
			]);
			if (!$saveResult->isSuccess())
			{
				$result->addErrors(
					$saveResult->getErrors()
				);
			}
		}

		if (isset($this->basePrice))
		{
			$basePriceRowId = $this->getBasePriceRowId();
			if ($basePriceRowId)
			{
				$saveResult = Price::update($basePriceRowId, [
					'PRICE' => $this->basePrice,
					'CURRENCY' => $this->basePriceCurrency,
				]);
			}
			else
			{
				$saveResult = Price::add([
					'PRODUCT_ID' => $this->productId,
					'CATALOG_GROUP_ID' => $this->getBasePriceGroupId(),
					'PRICE' => $this->basePrice,
					'CURRENCY' => $this->basePriceCurrency ?? $this->getDefaultCurrency(),
				]);
			}

			if (!$saveResult->isSuccess())
			{
				$result->addErrors(
					$saveResult->getErrors()
				);
			}
		}

		return $result;
	}
}