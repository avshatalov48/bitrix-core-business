<?php

namespace Bitrix\Catalog\v2\Section;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class SectionFactory
 *
 * @package Bitrix\Catalog\v2\Section
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SectionFactory
{
	public const SECTION = Section::class;
	public const SECTION_COLLECTION = SectionCollection::class;

	protected $container;

	/**
	 * SectionFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Section\Section
	 */
	public function createEntity(): BaseEntity
	{
		return $this->container->make(self::SECTION);
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|null $parent
	 * @return \Bitrix\Catalog\v2\Section\SectionCollection
	 */
	public function createCollection(BaseEntity $parent = null): BaseCollection
	{
		/** @var \Bitrix\Catalog\v2\Section\SectionCollection $collection */
		$collection = $this->container->make(self::SECTION_COLLECTION);

		if ($parent)
		{
			$collection->setParent($parent);
		}

		return $collection;
	}
}