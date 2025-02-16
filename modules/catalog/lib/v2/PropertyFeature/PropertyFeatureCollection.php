<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Main\Result;

/**
 * Class PropertyFeatureCollection
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyFeatureCollection extends BaseCollection
{
	/** @var PropertyFeatureRepositoryContract */
	protected $repository;

	public function __construct(PropertyFeatureRepositoryContract $repository)
	{
		$this->repository = $repository;
	}

	public function findByFeatureId(string $featureId): ?PropertyFeature
	{
		/** @var PropertyFeature $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item->getFeatureId() === $featureId)
			{
				return $item;
			}
		}

		return null;
	}

	public function saveInternal(): Result
	{
		return new Result();
	}

	public function deleteInternal(): Result
	{
		return new Result();
	}
}