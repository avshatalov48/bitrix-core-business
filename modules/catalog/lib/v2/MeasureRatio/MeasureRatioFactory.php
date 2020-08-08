<?php

namespace Bitrix\Catalog\v2\MeasureRatio;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class MeasureRatioFactory
 *
 * @package Bitrix\Catalog\v2\MeasureRatio
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MeasureRatioFactory
{
	public const SIMPLE_MEASURE_RATIO = SimpleMeasureRatio::class;
	public const MEASURE_RATIO_COLLECTION = MeasureRatioCollection::class;

	protected $container;

	/**
	 * MeasureRatioFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio
	 */
	public function createEntity(): BaseEntity
	{
		return $this->container->make(self::SIMPLE_MEASURE_RATIO);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection $collection */
		$collection = $this->container->make(self::MEASURE_RATIO_COLLECTION);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}