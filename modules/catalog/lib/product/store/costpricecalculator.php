<?php
namespace Bitrix\Catalog\Product\Store;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Main\Config\Option;
use Bitrix\Catalog\Config\State;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class CostPriceCalculator
 *
 * Calculate purchasing price by different methods.
 *
 * @package Bitrix\Catalog\Product\Price
 */
class CostPriceCalculator
{
	public const METHOD_FIFO = 'fifo';
	public const METHOD_AVERAGE = 'average';
	private const OPTION_NAME  = 'cost_price_calculation_method';

	private static string $method;
	private static bool $isUsedInventoryManagement;

	protected ?array $productFields = null;
	protected bool $isCurrencyModuleIncluded;

	private BatchManager $batchManager;

	/**
	 * @param BatchManager $batchManager
	 */
	public function __construct(BatchManager $batchManager)
	{
		$this->batchManager = $batchManager;

		$this->isCurrencyModuleIncluded = Loader::includeModule('currency');
	}

	/**
	 * Return current purchasing price calculation method.
	 *
	 * @return string
	 */
	public static function getMethod(): string
	{
		self::$method ??= Option::get('catalog', self::OPTION_NAME);

		return self::$method;
	}

	/**
	 * Set current purchasing price calculation method value.
	 *
	 * @param string $method
	 * @return void
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function setMethod(string $method): void
	{
		self::$method = ($method === self::METHOD_FIFO) ? self::METHOD_FIFO : self::METHOD_AVERAGE;

		Option::set('catalog', self::OPTION_NAME, self::$method);
	}

	private static function isUsedInventoryManagement(): bool
	{
		static::$isUsedInventoryManagement ??= State::isUsedInventoryManagement();

		return static::$isUsedInventoryManagement;
	}

	private function getCatalogPurchasingFields(): ?array
	{
		if ($this->productFields === null)
		{
			$this->productFields = ProductTable::getRow([
				'filter' => ['=ID' => $this->batchManager->getProductId()],
				'select' => ['PURCHASING_PRICE', 'PURCHASING_CURRENCY'],
				'cache' => ['ttl' => 3600]
			]);

		}

		return $this->productFields;
	}

	private function getCatalogPurchasingPrice(): float
	{
		return (float)($this->getCatalogPurchasingFields()['PURCHASING_PRICE'] ?? 0);
	}

	private function getCatalogPurchasingCurrency(): string
	{
		return (string)($this->getCatalogPurchasingFields()['PURCHASING_CURRENCY'] ?? '');
	}

	/**
	 * Calculate purchasing price by quantity. Is able to calculate in certain store and get result in required currency.
	 *
	 * @param float $quantity
	 * @param int $storeId
	 * @param string|null $currency
	 * @return float
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function calculate(float $quantity, int $storeId, string $currency = null): float
	{
		if ($quantity <= 0 || !self::isUsedInventoryManagement())
		{
			$catalogPrice = $this->getCatalogPurchasingPrice();

			if (
				empty($currency)
				|| !$this->isCurrencyModuleIncluded
				|| $currency === $this->getCatalogPurchasingCurrency()
			)
			{
				return $catalogPrice;
			}

			return \CCurrencyRates::convertCurrency($catalogPrice, $this->getCatalogPurchasingCurrency(), $currency);
		}

		if (self::getMethod() === self::METHOD_FIFO)
		{
			return $this->calculateFifo($quantity, $storeId, $currency);
		}

		return $this->calculateAverage($storeId, $currency);
	}

	private function calculateFifo(float $quantity, int $storeId, string $currency = null): float
	{
		$commonAmount = 0;
		$commonSum = 0;
		foreach ($this->batchManager->getAvailableStoreCollection($storeId) as $item)
		{
			$itemAvailableAmount = $item->getAvailableAmount();
			$itemPurchasingPrice = $item->getPurchasingPrice();
			if ($this->isCurrencyModuleIncluded && $currency && $item->getPurchasingCurrency() !== $currency)
			{
				$itemPurchasingPrice = \CCurrencyRates::convertCurrency(
					$itemPurchasingPrice,
					$item->getPurchasingCurrency(),
					$currency
				);
			}

			if ($itemAvailableAmount >= $quantity)
			{
				$commonAmount += $quantity;
				$commonSum += ($itemPurchasingPrice * $quantity);

				break;
			}

			$quantity -= $itemAvailableAmount;
			$commonAmount += $itemAvailableAmount;
			$commonSum += ($itemPurchasingPrice * $itemAvailableAmount);
		}

		if ($commonAmount === 0)
		{
			return $this->getCatalogPurchasingPrice();
		}

		return $this->roundCalculation($commonSum / $commonAmount);
	}

	private function calculateAverage(int $storeId, string $currency = null): float
	{
		$batchCollection = $this->batchManager->getAvailableStoreCollection($storeId);
		$batch = $batchCollection->current();

		if (!$batch)
		{
			$itemPurchasingPrice = $this->getCatalogPurchasingPrice();
			$itemPurchasingCurrency = $this->getCatalogPurchasingCurrency();
		}
		else
		{
			$itemPurchasingPrice = $batch->getPurchasingPrice();
			$itemPurchasingCurrency = $batch->getPurchasingCurrency();
		}

		if (
			$itemPurchasingPrice > 0
			&& $this->isCurrencyModuleIncluded
			&& $currency
			&& $itemPurchasingCurrency !== $currency
		)
		{
			$itemPurchasingPrice = \CCurrencyRates::convertCurrency(
				$itemPurchasingPrice,
				$itemPurchasingCurrency,
				$currency
			);
		}

		return $this->roundCalculation($itemPurchasingPrice);
	}

	private function roundCalculation(float $value): float
	{
		return round($value, $this->getRoundPrecision());
	}

	private function getRoundPrecision(): int
	{
		return (int)Option::get('sale', 'value_precision', 2);
	}

	public static function getMethodList(): array
	{
		return [
			self::METHOD_AVERAGE => Loc::getMessage('COST_PRICE_CALCULATION_MODE_AVERAGE'),
			self::METHOD_FIFO => Loc::getMessage('COST_PRICE_CALCULATION_MODE_FIFO'),
		];
	}
}
