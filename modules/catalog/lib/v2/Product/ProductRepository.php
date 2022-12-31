<?php

namespace Bitrix\Catalog\v2\Product;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\BaseIblockElementRepository;
use Bitrix\Catalog\v2\Iblock\IblockInfo;

/**
 * Class ProductRepository
 *
 * @package Bitrix\Catalog\v2\Product
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ProductRepository extends BaseIblockElementRepository implements ProductRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Product\ProductFactory */
	protected $factory;

	/**
	 * ProductRepository constructor.
	 *
	 * @param \Bitrix\Catalog\v2\Product\ProductFactory $factory
	 * @param \Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo
	 */
	public function __construct(ProductFactory $factory, IblockInfo $iblockInfo)
	{
		parent::__construct($factory, $iblockInfo);
	}

	protected function getAdditionalFilter(): array
	{
		$filter = parent::getAdditionalFilter();
		$filter['IBLOCK_ID'] = $this->iblockInfo->getProductIblockId();

		return $filter;
	}

	protected function getAdditionalProductFilter(): array
	{
		$filter = parent::getAdditionalProductFilter();

		$filter['@TYPE'] = [
			ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_SKU,
			ProductTable::TYPE_EMPTY_SKU,
			ProductTable::TYPE_SERVICE,
		];

		return $filter;
	}

	protected function makeEntity(array $fields = []): BaseIblockElementEntity
	{
		return $this->factory->createEntity();
	}
}