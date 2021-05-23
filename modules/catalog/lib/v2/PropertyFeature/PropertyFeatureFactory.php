<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class PropertyFeatureFactory
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyFeatureFactory
{
	public const PROPERTY_FEATURE = PropertyFeature::class;
	public const PROPERTY_FEATURE_COLLECTION = PropertyFeatureCollection::class;

	protected $container;

	/**
	 * PropertyFactory constructor.
	 *
	 * @param ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return PropertyFeature
	 */
	public function createEntity(): PropertyFeature
	{
		return $this->container->make(self::PROPERTY_FEATURE);
	}

	/**
	 * @return PropertyFeatureCollection
	 */
	public function createCollection(): PropertyFeatureCollection
	{
		return $this->container->make(self::PROPERTY_FEATURE_COLLECTION);
	}
}