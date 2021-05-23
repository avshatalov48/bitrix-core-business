<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\GroupTable;
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
			if (!$entity->hasPrice())
			{
				if (!$entity->isNew())
				{
					$res = $this->deleteInternal($entity->getId());

					if ($res->isSuccess())
					{
						$entity->setField('ID', null);
					}
					else
					{
						$result->addErrors($res->getErrors());
					}
				}

				continue;
			}

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
		$prices = PriceTable::getList($params)->fetchAll();

		return array_column($prices, null, 'CATALOG_GROUP_ID');
	}

	protected function createEntity(array $fields = []): BasePrice
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields = []): PriceCollection
	{
		$collection = $this->factory->createCollection();

		foreach ($this->getPriceSettings() as $settings)
		{
			$fields = $entityFields[$settings['ID']]
				?? [
					'CATALOG_GROUP_ID' => $settings['ID'],
				];
			$price = $this->createEntity($fields);
			$price->setSettings($settings);
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

	private function getPriceSettings(): array
	{
		static $priceSettings = null;

		if ($priceSettings === null)
		{
			$priceSettings = GroupTable::getList()->fetchAll();
		}

		return $priceSettings;
	}
}