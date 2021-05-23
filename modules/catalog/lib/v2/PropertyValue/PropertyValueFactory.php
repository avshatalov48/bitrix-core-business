<?php

namespace Bitrix\Catalog\v2\PropertyValue;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class PropertyValueFactory
 *
 * @package Bitrix\Catalog\v2\PropertyValue
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyValueFactory
{
	public const PROPERTY_VALUE = PropertyValue::class;
	public const PROPERTY_VALUE_COLLECTION = PropertyValueCollection::class;

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
	 * @return \Bitrix\Catalog\v2\PropertyValue\PropertyValue
	 */
	public function createEntity(): PropertyValue
	{
		return $this->container->make(self::PROPERTY_VALUE);
	}

	/**
	 * @return \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection
	 */
	public function createCollection(): PropertyValueCollection
	{
		return $this->container->make(self::PROPERTY_VALUE_COLLECTION);
	}
}