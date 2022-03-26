<?php

namespace Bitrix\Catalog\v2\Barcode;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Catalog;

/**
 * Class BarcodeRepository
 *
 * @package Bitrix\Catalog\v2\Barcode
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */

class BarcodeRepository implements BarcodeRepositoryContract
{
	/** @var BarcodeFactory */
	protected $factory;

	public function __construct(BarcodeFactory $factory)
	{
		$this->factory = $factory;
	}
	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
	public function save(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var Barcode $entity */
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

	/**
	 * @inheritDoc
	 */
	public function delete(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var Barcode $entity */
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

	public function getCollectionByParent(BaseSku $sku): BarcodeCollection
	{
		if ($sku->isNew())
		{
			return $this->createCollection();
		}

		$result = $this->getByProductId($sku->getId());

		return $this->createCollection($result);
	}

	protected function getByProductId(int $skuId): array
	{
		return $this->getList([
			'filter' => [
				'=PRODUCT_ID' => $skuId,
				'=ORDER_ID' => null,
			],
		]);
	}

	protected function getList(array $params): array
	{
		$rows = Catalog\StoreBarcodeTable::getList($params)->fetchAll();

		return array_column($rows, null, 'ID');
	}

	protected function createEntity(array $fields = []): Barcode
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields = []): BarcodeCollection
	{
		$collection = $this->factory->createCollection();

		foreach ($entityFields as $fields)
		{
			$barcode = $this->createEntity($fields);
			$collection->add($barcode);
		}

		return $collection;
	}

	protected function addInternal(array $fields): Result
	{
		$result = new Result();

		$res = Catalog\StoreBarcodeTable::add($fields);
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

		$res = Catalog\StoreBarcodeTable::update($id, $fields);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function deleteInternal(int $id): Result
	{
		$result = new Result();

		$res = Catalog\StoreBarcodeTable::delete($id);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}
}
