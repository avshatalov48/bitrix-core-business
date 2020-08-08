<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\MeasureRatio\HasMeasureRatioCollection;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract;
use Bitrix\Catalog\v2\Price\HasPriceCollection;
use Bitrix\Catalog\v2\Price\PriceCollection;
use Bitrix\Catalog\v2\Price\PriceRepositoryContract;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;

/**
 * Class BaseSku
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseSku extends BaseIblockElementEntity implements HasPriceCollection, HasMeasureRatioCollection
{
	/** @var \Bitrix\Catalog\v2\Price\PriceCollection|\Bitrix\Catalog\v2\Price\BasePrice[] */
	protected $priceCollection;
	/** @var \Bitrix\Catalog\v2\Price\PriceRepositoryContract */
	protected $priceRepository;

	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract */
	protected $measureRatioRepository;
	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection|\Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio[] */
	protected $measureRatioCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		SkuRepositoryContract $skuRepository,
		PropertyRepositoryContract $propertyRepository,
		PriceRepositoryContract $priceRepository,
		MeasureRatioRepositoryContract $measureRatioRepository
	)
	{
		parent::__construct($iblockInfo, $skuRepository, $propertyRepository);
		$this->priceRepository = $priceRepository;
		$this->measureRatioRepository = $measureRatioRepository;
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
		$this->measureRatioCollection = $measureRatioCollection;

		return $this;
	}
}