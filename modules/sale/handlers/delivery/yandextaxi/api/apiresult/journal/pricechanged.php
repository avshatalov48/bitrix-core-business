<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal;

/**
 * Class PriceChanged
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal
 * @internal
 */
final class PriceChanged extends Event
{
	public const EVENT_CODE = 'price_changed';

	/** @var string */
	protected $newPrice;

	/** @var string */
	protected $newCurrency;

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return static::EVENT_CODE;
	}

	/**
	 * @return string
	 */
	public function getNewPrice()
	{
		return $this->newPrice;
	}

	/**
	 * @param string $newPrice
	 * @return PriceChanged
	 */
	public function setNewPrice(string $newPrice): PriceChanged
	{
		$this->newPrice = $newPrice;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNewCurrency()
	{
		return $this->newCurrency;
	}

	/**
	 * @param string $newCurrency
	 * @return PriceChanged
	 */
	public function setNewCurrency(string $newCurrency): PriceChanged
	{
		$this->newCurrency = $newCurrency;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function provideUpdateFields(): array
	{
		return [
			'EXTERNAL_CURRENCY' => $this->newCurrency,
			'EXTERNAL_FINAL_PRICE' => $this->newPrice,
		];
	}
}
