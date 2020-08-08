<?php

namespace Bitrix\Catalog\v2\PropertyValue;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
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
	 * @return \Bitrix\Catalog\v2\BaseEntity
	 */
	public function createEntity(): BaseEntity
	{
		return $this->container->make(self::PROPERTY_VALUE);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection $collection */
		$collection = $this->container->make(self::PROPERTY_VALUE_COLLECTION);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}