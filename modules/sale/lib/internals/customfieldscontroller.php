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
		$dbRes = CustomFieldsTable::getList([
			'select' => ['FIELD', 'ENTITY_ID'],
			'filter' => $this->buildFilter($collection)
		]);

		while ($data = $dbRes->fetch())
		{
			$entity = $collection->getItemById($data['ENTITY_ID']);
			$entity->markFieldCustom($data['FIELD']);
		}

		return $collection;
	}

	/**
	 * @param EntityCollection $collection
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	private function buildFilter(EntityCollection $collection)
	{
		$filter = [
			'ENTITY_ID' => [],
			'ENTITY_TYPE' => [],
			'ENTITY_REGISTRY_TYPE' => [],
		];

		/** @var CollectableEntity $entity */
		foreach ($collection as $entity)
		{
			if ((int)$entity->getId() === 0)
			{
				continue;
			}

			if (!in_array($entity->getId(), $filter['ENTITY_ID']))
			{
				$filter['ENTITY_ID'][] = $entity->getId();
			}

			if (!in_array($entity::getRegistryEntity(), $filter['ENTITY_TYPE']))
			{
				$filter['ENTITY_TYPE'][] = $entity::getRegistryEntity();
			}

			if (!in_array($entity::getRegistryType(), $filter['ENTITY_REGISTRY_TYPE']))
			{
				$filter['ENTITY_REGISTRY_TYPE'][] = $entity::getRegistryType();
			}
		}

		return $filter;
	}
}