<?php

namespace Bitrix\Catalog\v2\StoreProduct;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class StoreProductFactory
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class StoreProductFactory
{
	public const STORE_PRODUCT = StoreProduct::class;
	public const STORE_PRODUCT_COLLECTION = StoreProductCollection::class;

	protected $container;

	/**
	 * StoreFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\StoreProduct\StoreProduct
	 */
	public function createEntity(): StoreProduct
	{
		return $this->container->make(self::STORE_PRODUCT);
	}

	/**
	 * @return \Bitrix\Catalog\v2\StoreProduct\StoreProductCollection
	 */
	public function createCollection(): StoreProductCollection
	{
		return $this->container->make(self::STORE_PRODUCT_COLLECTION);
	}
}
