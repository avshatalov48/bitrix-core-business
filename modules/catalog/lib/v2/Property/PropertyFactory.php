<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class PropertyFactory
 *
 * @package Bitrix\Catalog\v2\Property
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyFactory
{
	public const PROPERTY = Property::class;
	public const PROPERTY_COLLECTION = PropertyCollection::class;

	protected $container;

	/**
	 * PropertyFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Property\Property
	 */
	public function createEntity(): Property
	{
		return $this->container->make(self::PROPERTY);
	}

	/**
	 * @return \Bitrix\Catalog\v2\Property\PropertyCollection
	 */
	public function createCollection(): PropertyCollection
	{
		return $this->container->make(self::PROPERTY_COLLECTION);
	}
}