<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Main\Result;

/**
 * Class PropertyFeatureRepository
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyFeatureRepository implements PropertyFeatureRepositoryContract
{
	/** @var PropertyFeatureFactory */
	protected $factory;

	public function __construct(PropertyFeatureFactory $factory)
	{
		$this->factory = $factory;
	}

	public function getEntityById(int $id)
	{
		return $this->getEntitiesBy([
			'filter' => ['=ID' => $id],
			'limit' => 1,
		]);
	}

	public function getEntitiesBy($params)
	{
		return PropertyFeatureTable::getList($params)->fetchAll();
	}

	public function save(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function delete(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function getCollectionByParent(Property $entity): PropertyFeatureCollection
	{
		$collection = $this->factory->createCollection();

		$featureSettings = $this->getEntitiesBy([
			'filter' => ['=PROPERTY_ID' => $entity->getId()],
		]);

		foreach ($featureSettings as $settings)
		{
			$feature = $this->createEntity();
			$feature->setSettings($settings);
			$collection->add($feature);
		}

		return $collection;
	}

	protected function createEntity(array $fields = []): PropertyFeature
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}
}