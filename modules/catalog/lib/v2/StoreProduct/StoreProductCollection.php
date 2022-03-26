<?php

namespace Bitrix\Catalog\v2\StoreProduct;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Catalog\v2\IoC\Dependency;

/**
 * Class StoreProductCollection
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class StoreProductCollection extends BaseCollection
{
	/** @var ContainerContract */
	protected $container;
	/** @var \Bitrix\Catalog\v2\StoreProduct\StoreProductFactory */
	protected $factory;

	public function __construct(ContainerContract $container, StoreProductFactory $factory)
	{
		$this->container = $container;
		$this->factory = $factory;
	}
}
