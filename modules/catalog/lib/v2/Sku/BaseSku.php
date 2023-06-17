<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\Barcode\BarcodeCollection;
use Bitrix\Catalog\v2\Barcode\BarcodeRepositoryContract;
use Bitrix\Catalog\v2\Barcode\HasBarcodeCollection;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Image\ImageCollection;
use Bitrix\Catalog\v2\Image\ImageRepositoryContract;
use Bitrix\Catalog\v2\MeasureRatio\HasMeasureRatioCollection;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract;
use Bitrix\Catalog\v2\Price\HasPriceCollection;
use Bitrix\Catalog\v2\Price\PriceCollection;
use Bitrix\Catalog\v2\Price\PriceRepositoryContract;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Catalog\v2\StoreProduct\StoreProductCollection;
use Bitrix\Catalog\v2\StoreProduct\StoreProductRepositoryContract;
use Bitrix\Catalog\v2\StoreProduct\HasStoreProductCollection;
use Bitrix\Catalog\v2\StoreProduct\StoreProduct;

/**
 * Class BaseSku
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseSku extends BaseIblockElementEntity
	implements HasPriceCollection, HasMeasureRatioCollection, HasBarcodeCollection, HasStoreProductCollection
{
	/** @var \Bitrix\Catalog\v2\Price\PriceCollection|\Bitrix\Catalog\v2\Price\BasePrice[] */
	protected $priceCollection;
	/** @var \Bitrix\Catalog\v2\Price\PriceRepositoryContract */
	protected $priceRepository;

	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract */
	protected $measureRatioRepository;
	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection|\Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio[] */
	protected $measureRatioCollection;

	/** @var \Bitrix\Catalog\v2\Barcode\BarcodeRepositoryContract */
	protected $barcodeRepository;
	/** @var \Bitrix\Catalog\v2\Barcode\BarcodeCollection|\Bitrix\Catalog\v2\Barcode\Barcode[] */
	protected $barcodeCollection;

	/** @var StoreProductRepositoryContract */
	protected $storeProductRepository;
	/** @var StoreProductCollection|StoreProduct[] */
	protected $storeProductCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		SkuRepositoryContract $skuRepository,
		PropertyRepositoryContract $propertyRepository,
		ImageRepositoryContract $imageRepository,
		PriceRepositoryContract $priceRepository,
		MeasureRatioRepositoryContract $measureRatioRepository,
		BarcodeRepositoryContract $barcodeRepository,
		StoreProductRepositoryContract $storeProductRepository
	)
	{
		parent::__construct($iblockInfo, $skuRepository, $propertyRepository, $imageRepository);
		$this->priceRepository = $priceRepository;
		$this->measureRatioRepository = $measureRatioRepository;
		$this->barcodeRepository = $barcodeRepository;
		$this->storeProductRepository = $storeProductRepository;
	}

	/**
	 * @return ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[]
	 */
	public function getFrontImageCollection(): ImageCollection
	{
		$collection = $this->getImageCollection();
		if ($collection->isEmpty() && $this->getParent())
		{
			$parentCollection = $this->getParent()->getImageCollection();
			if (!$parentCollection->isEmpty())
			{
				return $parentCollection;
			}
		}

		return $collection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Price\PriceCollection|\Bitrix\Catalog\v2\Price\BasePrice[]
	 */
	public function getPriceCollection(): PriceCollection
	{
		if ($this->priceCollection === null)
		{
			// ToDo make lazy load like sku collection with iterator callback?
			$this->setPriceCollection($this->loadPriceCollection());
		}

		return $this->priceCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Price\PriceCollection|\Bitrix\Catalog\v2\Price\BasePrice[]
	 */
	protected function loadPriceCollection(): PriceCollection
	{
		return $this->priceRepository->getCollectionByParent($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Price\PriceCollection $priceCollection
	 * @return \Bitrix\Catalog\v2\Sku\BaseSku
	 *
	 * @internal
	 */
	public function setPriceCollection(PriceCollection $priceCollection): self
	{
		$priceCollection->setParent($this);

		$this->priceCollection = $priceCollection;

		return $this;
	}

	/**
	 * @return \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection|\Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio[]
	 */
	public function getMeasureRatioCollection(): MeasureRatioCollection
	{
		if ($this->measureRatioCollection === null)
		{
			$this->setMeasureRatioCollection($this->loadMeasureRatioCollection());
		}

		return $this->measureRatioCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection|\Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio[]
	 */
	protected function loadMeasureRatioCollection(): MeasureRatioCollection
	{
		return $this->measureRatioRepository->getCollectionByParent($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection $measureRatioCollection
	 * @return \Bitrix\Catalog\v2\Sku\BaseSku
	 *
	 * @internal
	 */
	public function setMeasureRatioCollection(MeasureRatioCollection $measureRatioCollection): self
	{
		$measureRatioCollection->setParent($this);

		$this->measureRatioCollection = $measureRatioCollection;

		return $this;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Barcode\BarcodeCollection|\Bitrix\Catalog\v2\Barcode\Barcode[]
	 */
	public function getBarcodeCollection(): BarcodeCollection
	{
		if ($this->barcodeCollection === null)
		{
			$this->setBarcodeCollection($this->loadBarcodeCollection());
		}

		return $this->barcodeCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Barcode\BarcodeCollection|\Bitrix\Catalog\v2\Barcode\BarcodeCollection[]
	 */
	protected function loadBarcodeCollection(): BarcodeCollection
	{
		return $this->barcodeRepository->getCollectionByParent($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Barcode\BarcodeCollection $barcodeCollection
	 * @return BaseSku
	 *
	 * @internal
	 */
	public function setBarcodeCollection(BarcodeCollection $barcodeCollection): self
	{
		$barcodeCollection->setParent($this);

		$this->barcodeCollection = $barcodeCollection;

		return $this;
	}

	/**
	 * @return StoreProductCollection|StoreProduct[]
	 */
	public function getStoreProductCollection(): StoreProductCollection
	{
		if ($this->storeProductCollection === null)
		{
			$this->setStoreProductCollection($this->loadStoreProductCollection());
		}

		return $this->storeProductCollection;
	}

	/**
	 * @return StoreProductCollection|StoreProduct[]
	 */
	protected function loadStoreProductCollection(): StoreProductCollection
	{
		return $this->storeProductRepository->getCollectionByParent($this);
	}

	/**
	 * @param StoreProductCollection $storeProductCollection
	 * @return BaseSku
	 *
	 * @internal
	 */
	public function setStoreProductCollection(StoreProductCollection $storeProductCollection): self
	{
		$storeProductCollection->setParent($this);

		$this->storeProductCollection = $storeProductCollection;

		return $this;
	}
}
