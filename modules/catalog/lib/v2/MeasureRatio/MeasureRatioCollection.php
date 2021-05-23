<?php

namespace Bitrix\Catalog\v2\MeasureRatio;

use Bitrix\Catalog\v2\BaseCollection;

/**
 * Class MeasureRatioCollection
 *
 * @package Bitrix\Catalog\v2\MeasureRatio
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MeasureRatioCollection extends BaseCollection
{
	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory */
	protected $factory;

	public function __construct(MeasureRatioFactory $factory)
	{
		$this->factory = $factory;
	}

	public function findDefault(): ?BaseMeasureRatio
	{
		/** @var \Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio $measureRatio */
		foreach ($this->getIterator() as $measureRatio)
		{
			if ($measureRatio->isDefault())
			{
				return $measureRatio;
			}
		}

		return null;
	}

	// ToDo set product id by default? in prices too?
	public function create(): BaseMeasureRatio
	{
		$measureRatio = $this->factory->createEntity();

		$this->add($measureRatio);

		return $measureRatio;
	}

	public function setDefault(float $ratio): BaseMeasureRatio
	{
		$measureRatio = $this->findDefault();

		if (!$measureRatio)
		{
			$measureRatio = $this->create()->setDefault();
		}

		return $measureRatio->setRatio($ratio);
	}
}