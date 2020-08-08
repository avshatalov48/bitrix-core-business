<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\BaseIblockElementRepository;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Product\ProductRepositoryContract;

/**
 * Class SkuRepository
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SkuRepository extends BaseIblockElementRepository implements SkuRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Sku\SkuFactory */
	protected $factory;
	/** @var \Bitrix\Catalog\v2\Product\ProductRepositoryContract */
	protected $productRepository;

	/**
	 * SkuRepository constructor.
	 *
	 * @param \Bitrix\Catalog\v2\Sku\SkuFactory $factory
	 * @param \Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo
	 * @param \Bitrix\Catalog\v2\Product\ProductRepositoryContract $productRepository
	 */
	public function __construct(
		SkuFactory $factory,
		IblockInfo $iblockInfo,
		ProductRepositoryContract $productRepository
	)
	{
		parent::__construct($factory, $iblockInfo);
		$this->productRepository = $productRepository;
	}

	/**
	 * @param \Bitrix\Catalog\v2\Product\BaseProduct $product
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	public function getCollectionByProduct(BaseProduct $product): SkuCollection
	{
		$callback = function (array $params) use ($product) {
			yield from $this->getSkuIteratorForProduct($product, $params);
		};

		return $this->factory
			->createCollection($product)
			->setIteratorCallback($callback)
			;
	}

	public function getEntitiesBy($params): array
	{
		$sku = parent::getEntitiesBy($params);

		if (!empty($sku))
		{
			$this->loadParentProducts(...$sku);
		}

		return $sku;
	}

	protected function getAdditionalFilter(): array
	{
		$filter = parent::getAdditionalFilter();
		$filter['IBLOCK_ID'] = $this->iblockInfo->getSkuIblockId();

		return $filter;
	}

	protected function getAdditionalProductFilter(): array
	{
		$filter = parent::getAdditionalProductFilter();

		$filter['@TYPE'] = [
			ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_OFFER,
			ProductTable::TYPE_FREE_OFFER,
		];

		return $filter;
	}

	protected function makeEntity(array $fields): BaseIblockElementEntity
	{
		$type = (int)($fields['TYPE'] ?? 0);

		if ($type === ProductTable::TYPE_OFFER || $type === ProductTable::TYPE_FREE_OFFER)
		{
			$entityClass = $this->factory::SKU;
		}
		else
		{
			$entityClass = $this->factory::SIMPLE_SKU;
		}

		return $this->factory->createEntity($entityClass);
	}

	private function loadParentProducts(BaseSku ...$skuItems): void
	{
		$skuByProductMap = $this->getSkuByProductMap($skuItems);

		if (!empty($skuByProductMap))
		{
			$products = $this->productRepository->getEntitiesBy([
				'filter' => [
					'=ID' => array_keys($skuByProductMap),
				],
			]);

			/** @var BaseProduct $product */
			foreach ($products as $product)
			{
				$productSkuItems = $skuByProductMap[$product->getId()];

				$this
					->getCollectionByProduct($product)
					->add(...$productSkuItems)
				;
			}
		}
	}

	private function getSkuByProductMap(array $skuItems): array
	{
		$skuByProductMap = [];

		$skuMap = [];
		/** @var \Bitrix\Catalog\v2\Sku\BaseSku $sku */
		foreach ($skuItems as $sku)
		{
			if ($sku->getParent() === null)
			{
				$skuMap[$sku->getId()] = $sku;
			}
		}

		if (!empty($skuMap))
		{
			$skuPropertyId = $this->iblockInfo->getSkuPropertyId();
			$propertyValuesIterator = \CIBlockElement::GetPropertyValues(
				$this->iblockInfo->getSkuIblockId(),
				['ID' => array_keys($skuMap)],
				false,
				['ID' => $skuPropertyId]
			);

			while ($propertyValues = $propertyValuesIterator->fetch())
			{
				$productId = $propertyValues[$skuPropertyId];
				$sku = $skuMap[$propertyValues['IBLOCK_ELEMENT_ID']];

				$skuByProductMap[$productId][] = $sku;
			}
		}

		return $skuByProductMap;
	}

	private function getSkuIteratorForProduct(BaseProduct $product, array $params = []): \Generator
	{
		if ($product->isSimple())
		{
			if ($product->getSkuCollection()->isEmpty())
			{
				yield $this->createEntity();
			}
		}
		elseif (!$product->isNew())
		{
			$params['filter']['PROPERTY_'.$this->iblockInfo->getSkuPropertyId()] = $product->getId();
			$params['order']['ID'] = 'DESC';

			foreach ($this->getList($params) as $item)
			{
				yield $this->createEntity($item);
			}
		}
	}
}