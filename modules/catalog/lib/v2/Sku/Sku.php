<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract;
use Bitrix\Catalog\v2\Price\PriceRepositoryContract;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Main\Result;

/**
 * Class Sku
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class Sku extends BaseSku
{
	public function __construct(
		IblockInfo $iblockInfo,
		SkuRepositoryContract $skuRepository,
		PropertyRepositoryContract $propertyRepository,
		PriceRepositoryContract $priceRepository,
		MeasureRatioRepositoryContract $measureRatioRepository
	)
	{
		parent::__construct(
			$iblockInfo,
			$skuRepository,
			$propertyRepository,
			$priceRepository,
			$measureRatioRepository
		);

		$this->setIblockId($this->iblockInfo->getSkuIblockId());
		$this->setType(ProductTable::TYPE_FREE_OFFER);
	}

	public function setParentCollection(?BaseCollection $collection): BaseEntity
	{
		// ToDo check for correct parent iblock for iblockelemententities!
		parent::setParentCollection($collection);

		$this->checkProductLink();

		return $this;
	}

	/**
	 * @return $this
	 * @internal
	 */
	public function checkProductLink(): self
	{
		/** @var \Bitrix\Catalog\v2\Product\BaseProduct $product */
		$product = $this->getParent();

		if ($product)
		{
			$this->linkProduct($product);
		}
		else
		{
			$this->unlinkProduct();
		}

		return $this;
	}

	protected function linkProduct(BaseProduct $product): self
	{
		if (!$this->isNew())
		{
			$this->setProductLinkProperty($product->getId());
		}

		$this->setType(ProductTable::TYPE_OFFER);

		return $this;
	}

	protected function unlinkProduct(): self
	{
		$this->unsetProductLinkProperty();
		$this->setType(ProductTable::TYPE_FREE_OFFER);

		return $this;
	}

	protected function getProductLinkProperty(): ?Property
	{
		$skuPropertyId = $this->iblockInfo->getSkuPropertyId();

		if ($skuPropertyId)
		{
			return $this->getPropertyCollection()->findBySetting('ID', $skuPropertyId);
		}

		return null;
	}

	protected function setProductLinkProperty(int $productId): self
	{
		$property = $this->getProductLinkProperty();

		if ($property)
		{
			// ToDo do we need property values type casting?
			$property->getPropertyValueCollection()->setValues((string)$productId);
		}

		return $this;
	}

	protected function unsetProductLinkProperty(): self
	{
		$property = $this->getProductLinkProperty();

		if ($property)
		{
			$property->getPropertyValueCollection()->setValues(null);
		}

		return $this;
	}

	public function saveInternalEntity(): Result
	{
		$result = parent::saveInternalEntity();

		if ($result->isSuccess())
		{
			$this->checkProductLink();
		}

		return $result;
	}
}