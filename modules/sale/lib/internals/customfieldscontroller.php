<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CustomFieldsController
 * @package Bitrix\Sale\Internals
 */
final class CustomFieldsController
{
	private static $instance = null;

	/**
	 * CustomFieldsController constructor.
	 */
	private function __construct() {}

	/**
	 * @return CustomFieldsController
	 */
	public static function getInstance() : CustomFieldsController
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param Entity $entity
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function save(Entity $entity)
	{
		if ($entity->getId() <= 0)
		{
			throw new Main\SystemException(
				Main\Localization\Loc::getMessage('CUSTOM_FIELDS_CONTROLLER_ERROR_INCORRECT_ENTITY_ID')
			);
		}

		$dbRes = CustomFieldsTable::getList([
			'select' => ['ID', 'FIELD'],
			'filter' => [
				'=ENTITY_ID' => $entity->getId(),
				'=ENTITY_TYPE' => $entity::getRegistryEntity(),
				'=ENTITY_REGISTRY_TYPE' => $entity::getRegistryType()
			]
		]);

		$customFieldArray = [];
		while ($data = $dbRes->fetch())
		{
			$customFieldArray[$data['FIELD']] = $data;
		}

		foreach ($entity::getCustomizableFields() as $field)
		{
			if ($entity->isMarkedFieldCustom($field))
			{
				if (!isset($customFieldArray[$field]))
				{
					CustomFieldsTable::add([
						'ENTITY_ID' => $entity->getId(),
						'ENTITY_TYPE' => $entity::getRegistryEntity(),
						'ENTITY_REGISTRY_TYPE' => $entity::getRegistryType(),
						'FIELD' => $field
					]);
				}
			}
			else
			{
				if (isset($customFieldArray[$field]))
				{
					CustomFieldsTable::delete($customFieldArray[$field]['ID']);
				}
			}
		}

		return $entity;
	}

	/**
	 * @param Entity $entity
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function initialize(Entity $entity) : Entity
	{
		if ($entity->getId() <= 0)
		{
			return $entity;
		}

		$dbRes = CustomFieldsTable::getList([
			'select' => ['ID', 'FIELD'],
			'filter' => [
				'=ENTITY_ID' => $entity->getId(),
				'=ENTITY_TYPE' => $entity::getRegistryEntity(),
				'=ENTITY_REGISTRY_TYPE' => $entity::getRegistryType()
			]
		]);

		while ($data = $dbRes->fetch())
		{
			$entity->markFieldCustom($data['FIELD']);
		}

		return $entity;
	}

	/**
	 * @param EntityCollection $collection
	 * @return EntityCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function initializeCollection(EntityCollection $collection) : EntityCollection
	{
		$filter = $this->buildFilter($collection);
		if ($filter)
		{
			$dbRes = CustomFieldsTable::getList([
				'select' => ['FIELD', 'ENTITY_ID'],
				'filter' => $filter
			]);

			while ($data = $dbRes->fetch())
			{
				$entity = $collection->getItemById($data['ENTITY_ID']);
				$entity->markFieldCustom($data['FIELD']);
			}
		}

		return $collection;
	}

	/**
	 * @param EntityCollection $collection
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	private function buildFilter(EntityCollection $collection) : array
	{
		$entityIdList = [];
		$entityTypeList = [];
		$entityRegistryTypeList = [];

		/** @var CollectableEntity $entity */
		foreach ($collection as $entity)
		{
			if ((int)$entity->getId() === 0)
			{
				continue;
			}

			if (!in_array($entity->getId(), $entityIdList))
			{
				$entityIdList[] = $entity->getId();
			}

			if (!in_array($entity::getRegistryEntity(), $entityTypeList))
			{
				$entityTypeList[] = $entity::getRegistryEntity();
			}

			if (!in_array($entity::getRegistryType(), $entityRegistryTypeList))
			{
				$entityRegistryTypeList[] = $entity::getRegistryType();
			}
		}

		if (
			empty($entityIdList)
			|| empty($entityTypeList)
			|| empty($entityRegistryTypeList)
		)
		{
			return [];
		}

		$filter = $this->buildFilterForField('ENTITY_ID', $entityIdList);
		$filter += $this->buildFilterForField('ENTITY_TYPE', $entityTypeList);
		$filter += $this->buildFilterForField('ENTITY_REGISTRY_TYPE', $entityRegistryTypeList);

		return $filter;
	}

	private function buildFilterForField(string $field, array $data) : array
	{
		if (count($data) === 1)
		{
			return ['='.$field => array_shift($data)];
		}

		return ['@'.$field => $data];
	}
}