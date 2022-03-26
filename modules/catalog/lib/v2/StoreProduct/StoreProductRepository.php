<?php

namespace Bitrix\Catalog\v2\StoreProduct;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Result;
use Bitrix\Catalog;

/**
 * Class StoreProductRepository
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */

class StoreProductRepository implements StoreProductRepositoryContract
{
	/** @var StoreProductFactory */
	protected $factory;

	public function __construct(StoreProductFactory $factory)
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
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(BaseEntity ...$entities): Result
	{
		return new Result();
	}

	public function getCollectionByParent(BaseSku $sku): StoreProductCollection
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
			],
		]);
	}

	protected function getList(array $params): array
	{
		$rows = Catalog\StoreProductTable::getList($params)->fetchAll();

		return array_column($rows, null, 'STORE_ID');
	}

	protected function createEntity(array $fields = []): StoreProduct
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields = []): StoreProductCollection
	{
		$collection = $this->factory->createCollection();

		foreach ($this->getStoreSettings() as $settings)
		{
			$fields = $entityFields[$settings['ID']]
				?? [
					'STORE_ID' => $settings['ID'],
				];
			$storeProduct = $this->createEntity($fields);
			$storeProduct->setSettings($settings);
			$collection->add($storeProduct);
		}

		return $collection;
	}

	protected function addInternal(array $fields): Result
	{
		$result = new Result();

		$res = Catalog\StoreProductTable::add($fields);
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

		$res = Catalog\StoreProductTable::update($id, $fields);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	protected function deleteInternal(int $id): Result
	{
		$result = new Result();

		$res = Catalog\StoreProductTable::delete($id);

		if (!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	private function getStoreSettings(): array
	{
		static $storeSettings = null;

		if ($storeSettings === null)
		{
			$storeSettings = Catalog\StoreTable::getList([
				'filter' => ['=ACTIVE' => 'Y'],
			])->fetchAll();
		}

		return $storeSettings;
	}
}
