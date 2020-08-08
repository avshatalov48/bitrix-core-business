<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\BaseIblockElementFactory;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Main\NotSupportedException;

/**
 * Class SkuFactory
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SkuFactory extends BaseIblockElementFactory
{
	public const SIMPLE_SKU = SimpleSku::class;
	public const SKU = Sku::class;
	public const SKU_COLLECTION = SkuCollection::class;

	/**
	 * @param string|null $entityClass
	 * @return \Bitrix\Catalog\v2\Sku\BaseSku
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function createEntity(string $entityClass = null): BaseIblockElementEntity
	{
		if ($entityClass === null)
		{
			$entityClass = $this->iblockInfo->canHaveSku() ? self::SKU : self::SIMPLE_SKU;
		}

		if ($entityClass === self::SKU && !$this->iblockInfo->canHaveSku())
		{
			throw new NotSupportedException(sprintf(
				'Product catalog {%s} does not support {%s} type.',
				$this->iblockInfo->getProductIblockId(), $entityClass
			));
		}

		if (!is_subclass_of($entityClass, BaseSku::class))
		{
			throw new NotSupportedException(sprintf(
				'Entity with type {%s} must be an instance of {%s}.',
				$entityClass, BaseSku::class
			));
		}

		return $this->makeEntity($entityClass);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\Sku\SkuCollection $collection */
		$collection = $this->container->make(self::SKU_COLLECTION, [
			Dependency::IBLOCK_INFO => $this->iblockInfo,
		]);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}