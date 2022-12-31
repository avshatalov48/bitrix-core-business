<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\BaseIblockElementRepository;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Product\ProductRepositoryContract;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\v2\Property\PropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;

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
	/** @var \Bitrix\Catalog\v2\Property\PropertyRepositoryContract */
	protected $propertyRepository;

	/**
	 * SkuRepository constructor.
	 *
	 * @param \Bitrix\Catalog\v2\Sku\SkuFactory $factory
	 * @param \Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo
	 * @param \Bitrix\Catalog\v2\Product\ProductRepositoryContract $productRepository
	 * @param \Bitrix\Catalog\v2\Property\PropertyRepositoryContract $propertyRepository
	 */
	public function __construct(
		SkuFactory $factory,
		IblockInfo $iblockInfo,
		ProductRepositoryContract $productRepository,
		PropertyRepositoryContract $propertyRepository
	)
	{
		parent::__construct($factory, $iblockInfo);
		$this->productRepository = $productRepository;
		$this->propertyRepository = $propertyRepository;
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
			->createCollection()
			->setIteratorCallback($callback)
		;
	}

	/**
	 * Sku entities for product.
	 *
	 * @param BaseProduct $product
	 * @param array $params parameters for `getList` method
	 *
	 * @return \Bitrix\Catalog\v2\Sku\Sku[]
	 */
	public function getEntitiesByProduct(BaseProduct $product, array $params): \Generator
	{
		return $this->getSkuIteratorForProduct($product, $params);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Product\BaseProduct $product
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	public function loadEagerCollectionByProduct(BaseProduct $product): SkuCollection
	{
		$callback = function (array $params) use ($product) {
			yield from $this->getSkuIteratorEagerLoading($product, $params);
		};

		return $this->factory
			->createCollection()
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

	protected function makeEntity(array $fields = []): BaseIblockElementEntity
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
				$productSkuItems = $skuByProductMap[$product->getId()] ?? [];
				$skuCollection = $this->getCollectionByProduct($product)
					->setParent($product)
					->add(...$productSkuItems)
				;
				$product->setSkuCollection($skuCollection);
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
			$params['filter']['PROPERTY_' . $this->iblockInfo->getSkuPropertyId()] = $product->getId();
			$params['order']['ID'] = 'DESC';

			foreach ($this->getList($params) as $item)
			{
				yield $this->createEntity($item);
			}
		}
	}

	private function getSkuIteratorEagerLoading(BaseProduct $product, array $params = []): \Generator
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
			$params['filter']['PROPERTY_' . $this->iblockInfo->getSkuPropertyId()] = $product->getId();
			$params['order']['ID'] = 'DESC';

			$items = [];
			foreach ($this->getList($params) as $item)
			{
				$items[$item['ID']] = $item;
			}

			$skuIds = array_keys($items);

			$propertySettings = $this->propertyRepository->getPropertiesSettingsByFilter([
				'=IBLOCK_ID' => $this->iblockInfo->getSkuIblockId(),
			]);

			$propertyElementMap = $this->getPropertyMapBySkuIds($skuIds, $propertySettings);

			foreach ($items as $skuId => $item)
			{
				$propertyCollection = $this->propertyRepository->createCollection();

				foreach ($propertySettings as $setting)
				{
					if (isset($propertyElementMap[$skuId][$setting['ID']]))
					{
						$propertyItem = $propertyElementMap[$skuId][$setting['ID']];
					}
					else
					{
						$propertyItem = $this->propertyRepository->createEntity([], $setting);
					}

					if ($propertyItem)
					{
						$propertyCollection->add($propertyItem);
					}
				}

				yield $this->createEntity($item, $propertyCollection);
			}
		}
	}

	/**
	 * @param array $skuIds
	 * @param array $propertySettings
	 * @return array
	 */
	private function getPropertyMapBySkuIds(array $skuIds, array $propertySettings): array
	{
		$skuPropertyFilter = [
			'filter' => [
				'IBLOCK_ID' => $this->iblockInfo->getSkuIblockId(),
				'ID' => $skuIds,
			],
		];

		$properties = $this->propertyRepository->getEntitiesBy($skuPropertyFilter, $propertySettings);
		$propertyElementMap = [];

		/** @var Property $property */
		foreach ($properties as $property)
		{
			$elementId = $property->getSetting('IBLOCK_ELEMENT_ID');

			if ($elementId > 0)
			{
				$propertyElementMap[$elementId] = $propertyElementMap[$elementId] ?? [];
				$propertyElementMap[$elementId][$property->getSetting('ID')] = $property;
			}
		}

		return $propertyElementMap;
	}

	protected function createEntity(array $fields = [], PropertyCollection $propertyCollection = null): BaseIblockElementEntity
	{
		$entity = parent::createEntity($fields);

		if ($propertyCollection)
		{
			$entity->setPropertyCollection($propertyCollection);
		}

		return $entity;
	}

	public function setDetailUrlTemplate(?string $template): BaseIblockElementRepository
	{
		if ($this->productRepository->getDetailUrlTemplate() === null)
		{
			$this->productRepository->setDetailUrlTemplate($template);
		}

		return parent::setDetailUrlTemplate($template);
	}

	public function getCountByProductId(int $productId): int
	{
		$filter = [
			'PROPERTY_' . $this->iblockInfo->getSkuPropertyId() => $productId,
		];

		return \CIBlockElement::GetList(
			[],
			array_merge(
				[
					// 'ACTIVE' => 'Y',
					// 'ACTIVE_DATE' => 'Y',
				],
				$filter,
				$this->getAdditionalFilter()
			),
			[]
		);
	}
}
