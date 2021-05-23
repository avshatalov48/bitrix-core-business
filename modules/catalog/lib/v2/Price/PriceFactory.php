<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class PriceFactory
 *
 * @package Bitrix\Catalog\v2\Price
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PriceFactory
{
	public const SIMPLE_PRICE = SimplePrice::class;
	public const QUANTITY_DEPENDENT_PRICE = QuantityDependentPrice::class;
	public const PRICE_COLLECTION = PriceCollection::class;

	protected $container;

	/**
	 * PriceFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Price\BasePrice
	 */
	public function createEntity(): BasePrice
	{
		// $price = $this->container->make(self::QUANTITY_DEPENDENT_PRICE);
		return $this->container->make(self::SIMPLE_PRICE);
	}

	/**
	 * @return \Bitrix\Catalog\v2\Price\PriceCollection
	 */
	public function createCollection(): PriceCollection
	{
		return $this->container->make(self::PRICE_COLLECTION);
	}
}