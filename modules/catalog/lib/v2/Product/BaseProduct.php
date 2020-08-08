<?php

namespace Bitrix\Catalog\v2\Product;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Catalog\v2\Section\HasSectionCollection;
use Bitrix\Catalog\v2\Section\SectionCollection;
use Bitrix\Catalog\v2\Section\SectionRepositoryContract;
use Bitrix\Catalog\v2\Sku\HasSkuCollection;
use Bitrix\Catalog\v2\Sku\SkuCollection;
use Bitrix\Catalog\v2\Sku\SkuFactory;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Main\Result;

/**
 * Class BaseProduct
 *
 * @package Bitrix\Catalog\v2\Product
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseProduct extends BaseIblockElementEntity implements HasSectionCollection, HasSkuCollection
{
	/** @var \Bitrix\Catalog\v2\Section\SectionRepositoryContract */
	protected $sectionRepository;
	/** @var \Bitrix\Catalog\v2\Sku\SkuRepositoryContract */
	protected $skuRepository;
	/** @var \Bitrix\Catalog\v2\Sku\SkuFactory */
	protected $skuFactory;

	/** @var \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[] */
	protected $sectionCollection;
	/** @var \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\Sku[] */
	protected $skuCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		ProductRepositoryContract $productRepository,
		PropertyRepositoryContract $propertyRepository,
		SectionRepositoryContract $sectionRepository,
		SkuRepositoryContract $skuRepository,
		SkuFactory $skuFactory
	)
	{
		parent::__construct($iblockInfo, $productRepository, $propertyRepository);
		$this->sectionRepository = $sectionRepository;
		$this->skuRepository = $skuRepository;
		$this->skuFactory = $skuFactory;

		$this->setIblockId($this->iblockInfo->getProductIblockId());
		$this->setType($this->iblockInfo->canHaveSku() ? ProductTable::TYPE_SKU : ProductTable::TYPE_PRODUCT);
	}

	/**
	 * @return \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[]
	 */
	public function getSectionCollection(): SectionCollection
	{
		if ($this->sectionCollection === null)
		{
			// ToDo make lazy load like sku collection with iterator callback?
			$this->setSectionCollection($this->loadSectionCollection());
		}

		return $this->sectionCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[]
	 */
	protected function loadSectionCollection(): BaseCollection
	{
		return $this->sectionRepository->getCollectionByProduct($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Section\SectionCollection $sectionCollection
	 * @return \Bitrix\Catalog\v2\Product\BaseProduct
	 *
	 * @internal
	 */
	public function setSectionCollection(SectionCollection $sectionCollection): self
	{
		$this->sectionCollection = $sectionCollection;

		return $this;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	public function getSkuCollection(): SkuCollection
	{
		if ($this->skuCollection === null)
		{
			$this->setSkuCollection($this->loadSkuCollection());
		}

		return $this->skuCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	protected function loadSkuCollection(): SkuCollection
	{
		return $this->skuRepository->getCollectionByProduct($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection
	 * @return \Bitrix\Catalog\v2\Product\BaseProduct
	 *
	 * @internal
	 */
	public function setSkuCollection(SkuCollection $skuCollection): self
	{
		$this->skuCollection = $skuCollection;

		return $this;
	}

	public function delete(): Result
	{
		return $this->deleteInternal();
	}
}