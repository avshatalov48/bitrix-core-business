<?php

namespace Bitrix\Catalog\v2\MeasureRatio;

use Bitrix\Catalog\MeasureRatioTable;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class MeasureRatioRepository
 *
 * @package Bitrix\Catalog\v2\MeasureRatio
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MeasureRatioRepository implements MeasureRatioRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory */
	protected $factory;

	public function __construct(MeasureRatioFactory $factory)
	{
		$this->factory = $factory;
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

	public function getProductId(BaseEntity $entity): ?int
	{
		$id = null;

		$parent = $entity->getParent();

		if ($parent && !$parent->isNew())
		{
			$id = $parent->getId();
		}

		return $id;
	}

	public function save(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var \Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio $entity */
		foreach ($entities as $entity)
		{
			if (!$entity->getProductId())
			{
				$productId = $this->getProductId($entity);

				if ($productId)
				{
					$entity->setProductId($productId);
				}
				else
				{
					$result->addError(new Error('Wrong product id'));
					continue;
				}
			}

			if ($entityId = $entity->getId())
			{
				$res = $this->updateInternal($entityId, $entity->getChangedFields());

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
			else
			{
				$res = $this->addInternal($entity->getFields());

				if ($res->isSuccess())
				{
					$entity->setId($res->getData()['ID']);
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	public function delete(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var \Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio $entity */
		foreach ($entities as $entity)
		{
			if ($entityId = $entity->getId())
			{
				$res = $this->deleteInternal($entityId);

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	public function getCollectionByParent(BaseSku $sku): MeasureRatioCollection
	{
		if ($sku->isNew())
		{
			return $this->createCollection([], $sku);
		}

		$result = $this->getByProductId($sku->getId());

		return $this->createCollection($result, $sku);
	}

	protected function getByProductId(int $skuId): array
	{
		return $this->getList([
			'filter' => [
				'=PRODUCT_ID' => $skuId,
			],
		]);
	}

	protected function getList(array $params): array
	{
		return MeasureRatioTable::getList($params)
			->fetchAll()
			;
	}

	protected function createEntity(array $fields): BaseEntity
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields, BaseSku $sku): MeasureRatioCollection
	{
		/** @var \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection $collection */
		$collection = $this->factory->createCollection($sku);

		foreach ($entityFields as $fields)
		{
			/** @var \Bitrix\Catalog\v2\MeasureRatio\BaseMeasureRatio $measureRatio */
			$measureRatio = $this->createEntity($fields);
			$collection->add($measureRatio);
		}

		return $collection;
	}

	protected function addInternal(array $fields): Result
	{
		$result = new Result();

		$res = MeasureRatioTable::add($fields);

		if ($res->isSuccess())
		{
			$result->setData(['ID' => $res->getId()]);
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function updateInternal(int $id, array $fields): Result
	{
		$result = new Result();

		$res = MeasureRatioTable::update($id, $fields);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function deleteInternal(int $id): Result
	{
		$result = new Result();

		$res = MeasureRatioTable::delete($id);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}
}