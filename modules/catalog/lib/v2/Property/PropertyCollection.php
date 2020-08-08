<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Result;

/**
 * Class PropertyCollection
 *
 * @package Bitrix\Catalog\v2\Property
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PropertyCollection extends BaseCollection
{
	/** @var \Bitrix\Catalog\v2\Property\PropertyRepositoryContract */
	protected $repository;

	public function __construct(PropertyRepositoryContract $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|\Bitrix\Catalog\v2\Property\HasPropertyCollection|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 */
	public function setParent(?BaseEntity $parent): BaseCollection
	{
		parent::setParent($parent);

		if ($parent)
		{
			if (!($parent instanceof HasPropertyCollection))
			{
				throw new InvalidOperationException(sprintf(
					'Parent entity must implement {%s} interface',
					HasPropertyCollection::class
				));
			}

			$parent->setPropertyCollection($this);
		}

		return $this;
	}

	public function findBySetting(string $field, $value): ?Property
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item->getSetting($field) == $value)
			{
				return $item;
			}
		}

		return null;
	}

	public function findByIndex(string $index): ?Property
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item->getIndex() === $index)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param array $propertyValues
	 * @return $this
	 */
	public function setValues(array $propertyValues): self
	{
		foreach ($propertyValues as $index => $values)
		{
			$property = $this->findByIndex($index);

			if ($property)
			{
				$property->getPropertyValueCollection()->setValues($values);
			}
		}

		return $this;
	}

	public function getValues(): array
	{
		$values = [];

		/** @var \Bitrix\Catalog\v2\Property\Property $property */
		foreach ($this->getIterator() as $property)
		{
			$values[$property->getIndex()] = $property->getPropertyValueCollection()->toArray();
		}

		return $values;
	}

	public function saveInternal(): Result
	{
		$result = new Result();

		// ToDo make lazyLoad for getPropertyCollection() to not load collection everytime
		if ($this->isChanged())
		{
			// ToDo re-initialize saved ids from database after file save - check in \CIBlockElement::SetPropertyValues
			// property collection can't be saved one by one, all simultaneously
			$res = $this->repository->save(...$this->getIterator());

			if ($res->isSuccess())
			{
				$this->clearChanged();
			}
			else
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	public function deleteInternal(): Result
	{
		// properties deletes with iblock element entity by CIBlockElement api
		return new Result();
	}
}