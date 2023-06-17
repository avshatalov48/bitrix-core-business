<?php

namespace Bitrix\Im\V2\Entity;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Rest\RestEntity;

abstract class EntityCollection extends Registry implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	protected array $entitiesWithId = [];

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([], $excludedList);

		foreach ($this as $entity)
		{
			$data->mergeFromEntity($entity, $excludedList);
		}

		return $data;
	}

	/**
	 * @return static
	 */
	public function getUnique(): self
	{
		return static::initByArray($this->entitiesWithId);
	}

	public function getIds(): array
	{
		$ids = [];

		foreach ($this as $entity)
		{
			$ids[$entity->getId()] = $entity->getId();
		}

		return $ids;
	}

	public function getById(int $id): ?RestEntity
	{
		return $this->entitiesWithId[$id] ?? null;
	}

	public function toRestFormat(array $option = []): array
	{
		$collection = [];

		foreach ($this as $entity)
		{
			$collection[] = $entity->toRestFormat($option);
		}

		return $collection;
	}

	/**
	 * @param RestEntity[] $entities
	 * @return static
	 */
	public static function initByArray(array $entities): self
	{
		$collection = new static();

		foreach ($entities as $entity)
		{
			$collection[] = $entity;
		}

		return $collection;
	}

	//region Collection interface

	public function offsetSet($offset, $value): void
	{
		if ($offset === null)
		{
			if ($value->getId() !== null)
			{
				$this->entitiesWithId[$value->getId()] = $value;
			}
		}

		parent::offsetSet($offset, $value);
	}

	//endregion
}