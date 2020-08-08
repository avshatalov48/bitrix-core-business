<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class PriceRepository
 *
 * @package Bitrix\Catalog\v2\Price
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PriceRepository implements PriceRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Price\PriceFactory */
	protected $factory;

	public function __construct(PriceFactory $factory)
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

		/** @var \Bitrix\Catalog\v2\Price\BasePrice $entity */
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

	public function getCollectionByParent(BaseSku $sku): PriceCollection
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
		return PriceTable::getList($params)
			->fetchAll()
			;
	}

	protected function createEntity(array $fields): BaseEntity
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields, BaseSku $sku): PriceCollection
	{
		/** @var \Bitrix\Catalog\v2\Price\PriceCollection $collection */
		$collection = $this->factory->createCollection($sku);

		foreach ($entityFields as $fields)
		{
			// ToDo make all collections with factories?
			/** @var \Bitrix\Catalog\v2\Price\BasePrice $price */
			$price = $this->createEntity($fields);
			$collection->add($price);
		}

		return $collection;
	}

	protected function addInternal(array $fields): Result
	{
		$result = new Result();

		// ToDo external_fields and actions?
		$res = Price::add([
			'fields' => $fields,
			// 'external_fields' => [
			// 	'IBLOCK_ID' => $destinationPrice['ELEMENT_IBLOCK_ID']
			// ],
			// 'actions' => [
			// 	'RECOUNT_PRICES' => true
			// ],
		]);

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

		// ToDo external_fields and actions?
		$res = Price::update($id, [
			'fields' => $fields,
			// 'external_fields' => [
			// 	'IBLOCK_ID' => $destinationPrice['ELEMENT_IBLOCK_ID']
			// ],
			// 'actions' => [
			// 	'RECOUNT_PRICES' => true
			// ],
		]);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function deleteInternal(int $id): Result
	{
		$result = new Result();

		$res = Price::delete($id);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}
}