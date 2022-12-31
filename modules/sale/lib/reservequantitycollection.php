<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Sale\Reservation\BasketReservationService;

class ReserveQuantityCollection extends Internals\EntityCollection
{
	/** @var BasketItem */
	protected $basketItem;

	/**
	 * @return Internals\Entity
	 */
	protected function getEntityParent()
	{
		return $this->getBasketItem();
	}

	protected function setBasketItem(BasketItemBase $item)
	{
		$this->basketItem = $item;
	}

	public function getBasketItem()
	{
		return $this->basketItem;
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return static
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected static function createCollectionObject() : self
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$className = $registry->get(Registry::ENTITY_BASKET_RESERVE_COLLECTION);

		return new $className;
	}

	/**
	 * @return ReserveQuantity
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function create()
	{
		$registry  = Registry::getInstance(static::getRegistryType());

		/** @var ReserveQuantity $reservedItemClassName */
		$reservedItemClassName = $registry->getReservedItemClassName();

		$reservedItem = $reservedItemClassName::create($this);
		$this->addItem($reservedItem);

		return $reservedItem;
	}

	/**
	 * @param BasketItemBase $basketItem
	 * @return static
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public static function load(BasketItemBase $basketItem) : self
	{
		if (!$basketItem->isReservableItem())
		{
			throw new Main\SystemException('Basket item is not available for reservation');
		}

		$collection = static::createCollectionObject();
		$collection->setBasketItem($basketItem);

		if ($basketItem->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var ReserveQuantity $reservedItemClassName */
			$reservedItemClassName = $registry->getReservedItemClassName();

			$reservedQuantityList = $reservedItemClassName::loadForBasketItem($basketItem->getId());
			foreach ($reservedQuantityList as $item)
			{
				$item->setCollection($collection);
				$collection->addItem($item);
			}
		}

		return $collection;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		$basketItem = $this->getEntityParent();

		if ($basketItem->getId() > 0)
		{
			$itemsFromDbList = static::getList([
				'filter' => ["BASKET_ID" => $basketItem->getId()],
			]);
			while ($item = $itemsFromDbList->fetch())
			{
				if (!$this->getItemById($item['ID']))
				{
					static::deleteInternal($item['ID']);
				}
			}
		}

		/** @var ReserveQuantity $entity */
		foreach ($this->collection as $entity)
		{
			$r = $entity->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		$this->clearChanged();

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param $basketId
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function deleteNoDemand($basketId)
	{
		$result = new Result();

		$dbRes = static::getList([
			'select' => ['ID'],
			'filter' => ['=BASKET_ID' => $basketId],
		]);

		while ($entity = $dbRes->fetch())
		{
			$r = static::deleteInternal($entity['ID']);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	public function getQuantity() : float
	{
		$quantity = 0;

		/** @var ReserveQuantity $reservation */
		foreach ($this->collection as $reservation)
		{
			$quantity += $reservation->getQuantity();
		}

		return $quantity;
	}

	public function getQuantityByStoreId(int $storeId) : float
	{
		$quantity = 0;

		/** @var ReserveQuantity $reservation */
		foreach ($this->collection as $reservation)
		{
			if ($reservation->getStoreId() === $storeId)
			{
				$quantity += $reservation->getQuantity();
			}
		}

		return $quantity;
	}

	/**
	 * @param $primary
	 * @return Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	protected static function deleteInternal($primary)
	{
		/** @var BasketReservationService */
		$service = ServiceLocator::getInstance()->get('sale.basketReservation');
		return $service->delete($primary);
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = [])
	{
		return Reservation\Internals\BasketReservationTable::getList($parameters);
	}
}
