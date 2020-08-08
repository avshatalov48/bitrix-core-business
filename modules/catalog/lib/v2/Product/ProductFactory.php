<?php

namespace Bitrix\Catalog\v2\Product;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\BaseIblockElementFactory;
use Bitrix\Main\NotSupportedException;

/**
 * Class ProductFactory
 *
 * @package Bitrix\Catalog\v2\Product
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ProductFactory extends BaseIblockElementFactory
{
	public const PRODUCT = Product::class;

	/**
	 * @param string $entityClass
	 * @return \Bitrix\Catalog\v2\Product\BaseProduct
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function createEntity(string $entityClass = self::PRODUCT): BaseIblockElementEntity
	{
		if (!is_subclass_of($entityClass, BaseProduct::class))
		{
			throw new NotSupportedException(sprintf(
				'Entity with type {%s} must be an instance of {%s}.',
				$entityClass, BaseProduct::class
			));
		}

		return $this->makeEntity($entityClass);
	}
}