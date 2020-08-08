<?php

namespace Bitrix\Catalog\v2\PropertyValue;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Main\InvalidOperationException;

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
	 * @param \Bitrix\Catalog\v2\BaseEntity|\Bitrix\Catalog\v2\PropertyValue\HasPropertyValueCollection|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 */
	public function setParent(?BaseEntity $parent): BaseCollection
	{
		parent::setParent($parent);

		if ($parent)
		{
			if (!($parent instanceof HasPropertyValueCollection))
			{
				throw new InvalidOperationException(sprintf(
					'Parent entity must implement {%s} interface',
					HasPropertyValueCollection::class
				));
			}

			$parent->setPropertyValueCollection($this);
		}

		return $this;
	}

	/**
	 * @param mixed $values
	 * @return $this
	 */
	public function setValues($values): self
	{
		$values = $this->prepareValues($values);

		$this->removeOldValues($values);

		foreach ($values as $fields)
		{
			$this->setValue($fields);
		}

		return $this;
	}

	/**
	 * @param array $values
	 * @return $this
	 *
	 * @internal
	 */
	public function initValues($values): self
	{
		$entities = [];

		foreach ($this->prepareValues($values) as $index => $fields)
		{
			/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $entity */
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
		elseif (isset($values['VALUE']) || isset($values['DESCRIPTION']))
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

	private function removeOldValues(array $values): void
	{
		$valuesToSave = [];

		foreach ($values as $value)
		{
			if (isset($value['VALUE']) && is_scalar($value['VALUE']))
			{
				$valuesToSave[] = $value['VALUE'];
			}
		}

		foreach ($this->getIterator() as $entity)
		{
			if ($entity->isNew() || !in_array($entity->getValue(), $valuesToSave, true))
			{
				$this->remove($entity);
			}
		}
	}

	private function setValue($fields): void
	{
		$entity = null;

		if ($this->isPropertyMultiple())
		{
			if (is_scalar($fields['VALUE']))
			{
				$entity = $this->findByValue($fields['VALUE']);
			}
		}
		else
		{
			$entity = !empty($this->items) ? reset($this->items) : null;
		}

		if ($entity === null)
		{
			/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValue $entity */
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