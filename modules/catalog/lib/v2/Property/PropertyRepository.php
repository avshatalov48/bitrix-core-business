<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory;
use Bitrix\Catalog\v2\Section\HasSectionCollection;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Collection;

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

	public function getEntitiesBy($params, array $propertySettings = []): array
	{
		$entities = [];

		$sortedSettings = [];
		foreach ($propertySettings as $setting)
		{
			if ((int)$setting['ID'] > 0)
			{
				$sortedSettings[(int)$setting['ID']] = $setting;
			}
		}

		foreach ($this->getList((array)$params) as $elementId => $properties)
		{
			if (!is_array($properties))
			{
				continue;
			}

			foreach ($properties as $propertyId => $item)
			{
				$settings = [];
				if ($sortedSettings[$propertyId])
				{
					$settings = $sortedSettings[$propertyId];
					$settings['IBLOCK_ELEMENT_ID'] = $elementId;
				}
				$entities[] = $this->createEntity($item, $settings);
			}
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
						$props[$property->getId()][$id] = \CIBlock::makeFilePropArray(
							$prop,
							$prop['VALUE'] === '',
							$prop['DESCRIPTION'],
							['allow_file_id' => true]
						);
					}
				}

				foreach ($valueCollection->getRemovedItems() as $removed)
				{
					if ($removed->isNew())
					{
						continue;
					}

					$fieldsToDelete = \CIBlock::makeFilePropArray($removed->getFields(), true);
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
			$element = new \CIBlockElement();
			$res = $element->update($parentEntity->getId(), [
				'PROPERTY_VALUES' => $props,
			]);
			if (!$res)
			{
				$result->addError(new Error($element->LAST_ERROR));
			}
		}

		return $result;
	}

	public function delete(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function getCollectionByParent(BaseIblockElementEntity $entity): PropertyCollection
	{
		if ($entity->isNew())
		{
			return $this->loadCollection([], $entity);
		}

		$result = $this->getList([
			'filter' => [
				'IBLOCK_ID' => $entity->getIblockId(),
				'ID' => $entity->getId(),
			],
		]);

		$entityFields = $result[$entity->getId()] ?? [];

		return $this->loadCollection($entityFields, $entity);
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
			$elementId = $propertyValues['IBLOCK_ELEMENT_ID'];
			unset($propertyValues['IBLOCK_ELEMENT_ID'], $propertyValues['PROPERTY_VALUE_ID'], $propertyValues['DESCRIPTION']);

			$entityFields = [];
			// ToDo empty properties with false (?: '') or null?
			foreach ($propertyValues as $id => $value)
			{
				$entityFields[$id] = [];
				$description = $descriptions[$id] ?? null;

				if ($value !== false || $description !== null)
				{
					if (is_array($value))
					{
						foreach ($value as $key => $item)
						{
							$fields = [
								'VALUE' => $item ?? '',
								'DESCRIPTION' => empty($description[$key]) ? null : $description[$key],
							];

							if (isset($propertyValueIds[$id][$key]))
							{
								$fields['ID'] = $propertyValueIds[$id][$key];
							}

							$entityFields[$id][$key] = $fields;
						}
					}
					else
					{
						$fields = [
							'VALUE' => $value ?? '',
							'DESCRIPTION' => empty($descriptions[$id]) ? null : $descriptions[$id],
						];

						if (isset($propertyValueIds[$id]))
						{
							$fields['ID'] = $propertyValueIds[$id];
						}

						$entityFields[$id][] = $fields;
					}
				}
			}

			$result[$elementId] = $entityFields;
		}

		return $result;
	}

	public function createCollection(): PropertyCollection
	{
		return $this->factory->createCollection();
	}

	protected function loadCollection(array $entityFields, BaseIblockElementEntity $parent): PropertyCollection
	{
		$propertySettings = [];

		if ($parent instanceof HasSectionCollection)
		{
			$linkedPropertyIds = $this->getLinkedPropertyIds($parent->getIblockId(), $parent);
			if (!empty($linkedPropertyIds))
			{
				$propertySettings = $this->getPropertiesSettingsByFilter([
					'@ID' => $linkedPropertyIds,
				]);
			}
		}
		else
		{
			// variation properties don't use any section links right now
			$propertySettings = $this->getPropertiesSettingsByFilter([
				'=IBLOCK_ID' => $parent->getIblockId(),
			]);
		}

		$collection = $this->createCollection();

		foreach ($propertySettings as $settings)
		{
			$fields = $entityFields[$settings['ID']] ?? [];
			$settings['IBLOCK_ELEMENT_ID'] = $parent->getId();
			$property = $this->createEntity($fields, $settings);

			$collection->add($property);
		}

		return $collection;
	}

	protected function getLinkedPropertyIds(int $iblockId, HasSectionCollection $parent): array
	{
		$linkedPropertyIds = [$this->loadPropertyIdsWithoutAnyLink($iblockId)];

		if ($parent->getSectionCollection()->isEmpty())
		{
			$linkedPropertyIds[] = array_keys(\CIBlockSectionPropertyLink::getArray($iblockId));
		}

		/** @var \Bitrix\Catalog\v2\Section\Section $section */
		foreach ($parent->getSectionCollection() as $section)
		{
			$linkedPropertyIds[] = array_keys(\CIBlockSectionPropertyLink::getArray($iblockId, $section->getValue()));
		}

		if (!empty($linkedPropertyIds))
		{
			$linkedPropertyIds = array_merge(...$linkedPropertyIds);
			Collection::normalizeArrayValuesByInt($linkedPropertyIds, false);
			$linkedPropertyIds = array_unique($linkedPropertyIds);
		}

		return $linkedPropertyIds;
	}

	private function loadPropertyIdsWithoutAnyLink(int $iblockId): array
	{
		$propertyIds = PropertyTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'==SECTION_LINK.SECTION_ID' => null,
			],
			'runtime' => [
				new ReferenceField(
					'SECTION_LINK',
					'\Bitrix\Iblock\SectionPropertyTable',
					[
						'=this.ID' => 'ref.PROPERTY_ID',
						'=this.IBLOCK_ID' => 'ref.IBLOCK_ID',
					],
					['join_type' => 'LEFT']
				),
			],
		])
			->fetchAll()
		;

		return array_column($propertyIds, 'ID');
	}

	public function getPropertiesSettingsByFilter(array $filter): array
	{
		$settings = PropertyTable::getList([
			'select' => ['*'],
			'filter' => $filter,
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		])
			->fetchAll()
			;

		return $this->loadEnumSettings($settings);
	}

	protected function prepareField(array $fields, array $settings): array
	{
		foreach ($fields as &$field)
		{
			if (!empty($settings['USER_TYPE']))
			{
				$userType = \CIBlockProperty::GetUserType($settings['USER_TYPE']);

				if (isset($userType['ConvertFromDB']))
				{
					$field = call_user_func($userType['ConvertFromDB'], $settings, $field);
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

	public function createEntity(array $fields = [], array $settings = []): Property
	{
		$entity = $this->factory->createEntity();

		if ($settings)
		{
			$settings = $this->prepareSettings($settings);
			$fields = $this->prepareField($fields, $settings);
			$entity->setSettings($settings);
		}

		$propertyValueCollection = $this->propertyValueFactory->createCollection();
		$propertyValueCollection->initValues($fields);

		$entity->setPropertyValueCollection($propertyValueCollection);

		return $entity;
	}

	private function loadEnumSettings(array $settings): array
	{
		$enumIds = [];

		foreach ($settings as $setting)
		{
			if ($setting['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST)
			{
				$enumIds[] = $setting['ID'];
			}
		}

		$enumSettings = PropertyEnumerationTable::getList([
			'select' => ['ID', 'PROPERTY_ID'],
			'filter' => [
				'PROPERTY_ID' => $enumIds,
				'=DEF' => 'Y',
			],
		])
			->fetchAll()
		;
		$enumSettings = array_column($enumSettings, 'ID', 'PROPERTY_ID');

		if (!empty($enumSettings))
		{
			foreach ($settings as &$setting)
			{
				if (isset($enumSettings[$setting['ID']]))
				{
					$setting['DEFAULT_VALUE'] = $enumSettings[$setting['ID']];
				}
			}
		}

		return $settings;
	}
}
