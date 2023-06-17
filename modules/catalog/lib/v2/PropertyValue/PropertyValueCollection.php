<?php

namespace Bitrix\Catalog\v2\PropertyValue;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;

/**
 * Class PropertyValueCollection
 *
 * @package Bitrix\Catalog\v2\PropertyValue
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyValueCollection extends BaseCollection
{
	/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory */
	protected $propertyValueFactory;

	public function __construct(PropertyValueFactory $propertyValueFactory)
	{
		$this->propertyValueFactory = $propertyValueFactory;
	}

	/**
	 * @param mixed $values
	 * @return $this
	 */
	public function setValues($values): self
	{
		$values = $this->prepareValues($values);
		$values = $this->prepareValuesIds($values);

		if ($this->isPropertyMultiple())
		{
			$this->removeUnchangedItems($values);
		}

		foreach ($values as $fields)
		{
			$this->setValue($fields);
		}

		return $this;
	}

	private function prepareValuesIds($values): array
	{
		if (empty($values))
		{
			return $values;
		}

		foreach ($values as $key => $value)
		{
			$values[$key]['ID'] = 0;
		}

		$valueIndex = 0;
		foreach ($this->getIterator() as $entity)
		{
			$values[$valueIndex]['ID'] = $entity->getId();
			$valueIndex++;
			if ($valueIndex > count($values) - 1)
			{
				break;
			}
		}

		return $values;
	}

	/**
	 * @param mixed $values
	 * @return $this
	 *
	 * @internal
	 */
	public function initValues($values): self
	{
		$entities = [];

		foreach ($this->prepareValues($values) as $index => $fields)
		{
			$entity = $this->propertyValueFactory->createEntity();

			$fieldsToInitialize = [
				'VALUE' => $fields['VALUE'] ?? null,
				'DESCRIPTION' => $fields['DESCRIPTION'] ?? null,
			];

			$id = (int)($fields['ID'] ?? 0);
			if ($id > 0)
			{
				$fieldsToInitialize['ID'] = $id;
			}

			$entity->initFields($fieldsToInitialize);
			$entities[] = $entity;
		}

		$this->items = [];
		$this->add(...$entities);

		return $this;
	}

	public function getValues()
	{
		$values = [];

		/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $item */
		foreach ($this->getIterator() as $item)
		{
			$values[] = $item->getValue();
		}

		if (!$this->isPropertyMultiple())
		{
			$values = !empty($values) ? reset($values) : null;
		}

		return $values;
	}

	public function findByValue($value): ?BaseEntity
	{
		/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item->getValue() === $value)
			{
				return $item;
			}
		}

		return null;
	}

	private function isPropertyMultiple(): bool
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $property */
		$property = $this->getParent();

		return $property && $property->isMultiple();
	}

	private function prepareValues($values): array
	{
		if (!is_array($values))
		{
			$values = [
				['VALUE' => $values],
			];
		}
		elseif (isset($values['VALUE']) || isset($values['DESCRIPTION']) || isset($values['CUR_PATH']))
		{
			$values = [$values];
		}

		foreach ($values as &$value)
		{
			if (!isset($value['VALUE']) && !isset($value['DESCRIPTION']))
			{
				$value = ['VALUE' => $value];
			}
		}

		return $values;
	}

	private function removeUnchangedItems($values): void
	{
		$idsToSave = [];

		foreach ($values as $value)
		{
			if ($value['ID'] > 0)
			{
				$idsToSave[] = $value['ID'];
			}
		}

		foreach ($this->getIterator() as $entity)
		{
			if ($entity->isNew() || !in_array($entity->getId(), $idsToSave, true))
			{
				$entity->remove();
			}
		}
	}

	private function setValue($fields): void
	{
		$entity = null;

		if ($this->isPropertyMultiple())
		{
			if (isset($fields['ID']) && $fields['ID'] > 0)
			{
				$entity = $this->findById($fields['ID']);
			}
		}
		else
		{
			$entity = !empty($this->items) ? reset($this->items) : null;
		}

		if ($entity === null)
		{
			$entity = $this->propertyValueFactory->createEntity();
			$this->add($entity);
		}

		$entity->setValue($fields['VALUE'] ?? null);

		if (isset($fields['DESCRIPTION']))
		{
			$entity->setDescription($fields['DESCRIPTION']);
		}
	}
}
