<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
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

	public function findByCode(string $code): ?Property
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $item */
		foreach ($this->getIterator() as $item)
		{
			if ($item->getCode() === $code)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * Used when you don't want to download the entire collection.
	 *
	 * @param string $code
	 * @return Property|null
	 */
	public function findByCodeLazy(string $code): ?Property
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $item */
		foreach ($this->items as $item)
		{
			if ($item->getCode() === $code)
			{
				return $item;
			}
		}

		$propertySettings = $this->repository->getPropertiesSettingsByFilter([
			'=IBLOCK_ID' => $this->getParent()->getIblockId(),
			'=CODE' => $code,
		]);
		$items = $this->repository->getEntitiesBy(
			[
				'filter' => [
					'IBLOCK_ID' => $this->getParent()->getIblockId(),
					'ID' => $this->getParent()->getId(),
					'PROPERTY_CODE' => $code,
				],
			],
			$propertySettings,
		);
		if ($items)
		{
			$item = reset($items) ?: null;
			$this->addInternal($item);

			return $item;
		}

		return null;
	}

	/**
	 * Used when you don't want to download the entire collection.
	 *
	 * @param int $id
	 * @return Property|null
	 */
	public function findByIdLazy(int $id): ?Property
	{
		/** @var \Bitrix\Catalog\v2\Property\Property $item */
		foreach ($this->items as $item)
		{
			if ($item->getId() === $id)
			{
				return $item;
			}
		}

		$propertySettings = $this->repository->getPropertiesSettingsByFilter([
			'=ID' => $id,
		]);
		$items = $this->repository->getEntitiesBy(
			[
				'filter' => [
					'IBLOCK_ID' => $this->getParent()->getIblockId(),
					'ID' => $this->getParent()->getId(),
					'PROPERTY_ID' => $id,
				],
			],
			$propertySettings,
		);
		if ($items)
		{
			$item = reset($items) ?: null;
			$this->addInternal($item);

			return $item;
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
			$property = null;

			if (is_numeric($index))
			{
				$property = $this->findById((int)$index);
			}

			if (!$property)
			{
				$property = $this->findByCode($index);
			}

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
			$values[$property->getId()] = $property->getPropertyValueCollection()->toArray();
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

	protected function getAlreadyLoadedFilter(): array
	{
		$filter = parent::getAlreadyLoadedFilter();

		foreach ($this->items as $item)
		{
			if (!$item->isNew())
			{
				$filter['!PROPERTY_ID'][] = $item->getId();
			}
		}

		return $filter;
	}
}
