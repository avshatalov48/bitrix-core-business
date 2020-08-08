<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory;
use Bitrix\Catalog\v2\Section\HasSectionCollection;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class PropertyRepository
 *
 * @package Bitrix\Catalog\v2\Property
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyRepository implements PropertyRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Property\PropertyFactory */
	protected $factory;
	/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory */
	private $propertyValueFactory;

	public function __construct(PropertyFactory $factory, PropertyValueFactory $propertyValueFactory)
	{
		$this->factory = $factory;
		$this->propertyValueFactory = $propertyValueFactory;
	}

	public function getEntityById(int $id): ?BaseEntity
	{
		if ($id <= 0)
		{
			throw new \OutOfRangeException($id);
		}

		$entities = $this->getEntitiesBy([
			'filter' => [
				'=ID' => $id,
			],
		]);

		return reset($entities) ?: null;
	}

	public function getEntitiesBy($params): array
	{
		$entities = [];

		foreach ($this->getList((array)$params) as $item)
		{
			$entities[] = $this->createEntity($item);
		}

		return $entities;
	}

	public function save(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var \Bitrix\Catalog\v2\BaseIblockElementEntity $parentEntity */
		$parentEntity = null;
		$props = [];

		/** @var \Bitrix\Catalog\v2\Property\Property $property */
		foreach ($entities as $property)
		{
			if ($parentEntity && $parentEntity !== $property->getParent())
			{
				$result->addError(new Error('Saving should only be done with properties of a common parent.'));
			}

			if ($parentEntity === null)
			{
				$parentEntity = $property->getParent();
			}

			$valueCollection = $property->getPropertyValueCollection();

			$props[$property->getId()] = $valueCollection->toArray();

			if ($property->getPropertyType() === PropertyTable::TYPE_FILE)
			{
				foreach ($props[$property->getId()] as $id => $prop)
				{
					if (is_numeric($id))
					{
						$props[$property->getId()][$id] = \CAllIBlock::makeFilePropArray($prop);
					}
				}

				foreach ($valueCollection->getRemovedItems() as $removed)
				{
					if ($removed->isNew())
					{
						continue;
					}

					$fieldsToDelete = \CAllIBlock::makeFilePropArray($removed->getFields(), true);
					$props[$property->getId()][$removed->getId()] = $fieldsToDelete;
				}
			}
		}

		if (!$parentEntity)
		{
			$result->addError(new Error('Parent entity not found while saving properties.'));
		}

		if (!($parentEntity instanceof BaseIblockElementEntity))
		{
			$result->addError(new Error(sprintf(
				'Parent entity of property must be an instance of {%s}.',
				BaseIblockElementEntity::class
			)));
		}

		if (!empty($props) && $result->isSuccess())
		{
			\CIBlockElement::setPropertyValues(
				$parentEntity->getId(),
				$parentEntity->getIblockId(),
				$props
			);
		}

		return $result;
	}

	public function delete(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function getCollectionByParent(BaseIblockElementEntity $entity): BaseCollection
	{
		if ($entity->isNew())
		{
			return $this->createCollection([], $entity);
		}

		$result = $this->getList([
			'filter' => [
				'IBLOCK_ID' => $entity->getIblockId(),
				'ID' => $entity->getId(),
			],
		]);

		return $this->createCollection($result, $entity);
	}

	protected function getList(array $params): array
	{
		$result = [];

		$filter = $params['filter'] ?? [];
		$propertyValuesIterator = \CIBlockElement::getPropertyValues($filter['IBLOCK_ID'], $filter, true);

		while ($propertyValues = $propertyValuesIterator->fetch())
		{
			$descriptions = $propertyValues['DESCRIPTION'] ?? [];
			$propertyValueIds = $propertyValues['PROPERTY_VALUE_ID'] ?? [];
			unset($propertyValues['IBLOCK_ELEMENT_ID'], $propertyValues['PROPERTY_VALUE_ID'], $propertyValues['DESCRIPTION']);

			// ToDo empty properties with false (?: '') or null?
			foreach ($propertyValues as $id => $value)
			{
				$result[$id] = [];
				$description = $descriptions[$id] ?? null;

				if ($value !== false || $description !== null)
				{
					if (is_array($value))
					{
						foreach ($value as $key => $item)
						{
							$fields = [
								'VALUE' => $item ?: '',
								'DESCRIPTION' => $description[$key] ?: null,
							];

							if (isset($propertyValueIds[$id][$key]))
							{
								$fields['ID'] = $propertyValueIds[$id][$key];
							}

							$result[$id][$key] = $fields;
						}
					}
					else
					{
						$fields = [
							'VALUE' => $value ?: '',
							'DESCRIPTION' => $descriptions[$id] ?: null,
						];

						if (isset($propertyValueIds[$id]))
						{
							$fields['ID'] = $propertyValueIds[$id];
						}

						$result[$id][] = $fields;
					}
				}
			}
		}

		return $result;
	}

	protected function createCollection(array $entityFields, BaseIblockElementEntity $parent): BaseCollection
	{
		$collection = $this->factory->createCollection($parent);

		$propertySettings = null;
		// ToDo if has no section collection - check parents? (in case when SKU)
		if ($parent instanceof HasSectionCollection)
		{
			$linkedProperties = $this->getLinkedProperties($parent->getIblockId(), $parent);

			if (!empty($linkedProperties))
			{
				$propertySettings = $this->getPropertiesSettingsByFilter([
					'@ID' => array_keys($linkedProperties),
				]);
			}
		}

		if ($propertySettings === null)
		{
			$propertySettings = $this->getPropertiesSettingsByFilter([
				'=IBLOCK_ID' => $parent->getIblockId(),
			]);
		}

		foreach ($propertySettings as $settings)
		{
			$settings = $this->prepareSettings($settings);
			$fields = $this->prepareField($entityFields[$settings['ID']] ?? [], $settings);

			/** @var \Bitrix\Catalog\v2\Property\Property $property */
			$property = $this->createEntity();
			$property->setSettings($settings);

			/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection $propertyValueCollection */
			$propertyValueCollection = $this->propertyValueFactory->createCollection($property);
			$propertyValueCollection->initValues($fields);

			$property->setPropertyValueCollection($propertyValueCollection);

			$collection->add($property);
		}

		return $collection;
	}

	protected function getLinkedProperties(int $iblockId, HasSectionCollection $parent): array
	{
		$linkedProperties = \CIBlockSectionPropertyLink::getArray($iblockId, 0);

		/** @var \Bitrix\Catalog\v2\Section\Section $section */
		foreach ($parent->getSectionCollection() as $section)
		{
			$linkedProperties += \CIBlockSectionPropertyLink::getArray($iblockId, $section->getValue());
		}

		return $linkedProperties;
	}

	private function getPropertiesSettingsByFilter(array $filter): array
	{
		return PropertyTable::getList([
			'select' => ['*'],
			'filter' => $filter,
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		])
			->fetchAll()
			;
	}

	protected function prepareField(array $fields, array $settings): array
	{
		foreach ($fields as &$field)
		{
			if ($settings['PROPERTY_TYPE'] === 'S' && $settings['USER_TYPE'] === 'HTML')
			{
				if (!empty($field['VALUE']) && !is_array($field['VALUE']) && CheckSerializedData($fields['VALUE']))
				{
					$field['VALUE'] = unserialize($field['VALUE'], [
						'allowed_classes' => false,
					]);
				}
				else
				{
					$field['VALUE'] = [
						'TEXT' => '',
						'TYPE' => 'HTML',
					];
				}
			}
		}

		return $fields;
	}

	protected function prepareSettings(array $settings): array
	{
		if (isset($settings['USER_TYPE_SETTINGS_LIST']))
		{
			$settings['USER_TYPE_SETTINGS'] = $settings['USER_TYPE_SETTINGS_LIST'];
			unset($settings['USER_TYPE_SETTINGS_LIST']);
		}

		return $settings;
	}

	protected function createEntity(array $fields = []): BaseEntity
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}
}