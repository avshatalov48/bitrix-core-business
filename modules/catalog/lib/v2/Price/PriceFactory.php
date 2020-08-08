<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
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
	public function createEntity(): BaseEntity
	{
		// $price = $this->container->make(self::QUANTITY_DEPENDENT_PRICE);
		return $this->container->make(self::SIMPLE_PRICE);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\Price\PriceCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\Price\PriceCollection $collection */
		$collection = $this->container->make(self::PRICE_COLLECTION);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}