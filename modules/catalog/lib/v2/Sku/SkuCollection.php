<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Main\InvalidOperationException;

/**
 * Class SkuCollection
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SkuCollection extends BaseCollection
{
	/** @var ContainerContract */
	protected $container;
	/** @var \Bitrix\Catalog\v2\Sku\SkuFactory */
	protected $factory;

	public function __construct(ContainerContract $container, SkuFactory $factory)
	{
		$this->container = $container;
		$this->factory = $factory;
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|\Bitrix\Catalog\v2\Sku\HasSkuCollection|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 * @throws \Bitrix\Main\InvalidOperationException
	 */
	public function setParent(?BaseEntity $parent): BaseCollection
	{
		parent::setParent($parent);

		if ($parent)
		{
			if (!($parent instanceof HasSkuCollection))
			{
				throw new InvalidOperationException(sprintf(
					'Parent entity must implement {%s} interface',
					HasSkuCollection::class
				));
			}

			$parent->setSkuCollection($this);
		}

		return $this;
	}

	public function create(): BaseSku
	{
		/** @var \Bitrix\Catalog\v2\Product\BaseProduct $parent */
		$parent = $this->getParent();

		if ($parent && $parent->isSimple())
		{
			/** @var \Bitrix\Catalog\v2\Converter\ProductConverter $converter */
			$converter = $this->container->get(Dependency::PRODUCT_CONVERTER);
			$converter->convert($parent, $converter::SKU_PRODUCT);
		}

		$sku = $this->createEntity();
		$this->add($sku);

		if (!$sku->hasName() && $parent->hasName())
		{
			$sku->setName($parent->getName());
		}

		return $sku;
	}

	protected function createEntity(): BaseSku
	{
		/** @var \Bitrix\Catalog\v2\Product\BaseProduct $parent */
		$parent = $this->getParent();

		if ($parent && $parent->isSimple())
		{
			$type = SkuFactory::SIMPLE_SKU;
		}
		else
		{
			$type = SkuFactory::SKU;
		}

		return $this->factory->createEntity($type);
	}

	protected function getAlreadyLoadedFilter(): array
	{
		$filter = parent::getAlreadyLoadedFilter();

		foreach ($this->items as $item)
		{
			if (!$item->isNew())
			{
				$filter['!ID'][] = $item->getId();
			}
		}

		return $filter;
	}
}