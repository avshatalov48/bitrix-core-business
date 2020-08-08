<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
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
	 * @return \Bitrix\Catalog\v2\BaseEntity
	 */
	public function createEntity(): BaseEntity
	{
		return $this->container->make(self::PROPERTY);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\Property\PropertyCollection $collection */
		$collection = $this->container->make(self::PROPERTY_COLLECTION);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}