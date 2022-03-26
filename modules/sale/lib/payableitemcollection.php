<?php

namespace Bitrix\Sale;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class PayableItemCollection
 * @package Bitrix\Sale
 */
class PayableItemCollection extends Internals\EntityCollection
{
	protected $payment;

	/**
	 * @return Internals\Entity
	 */
	protected function getEntityParent()
	{
		return $this->getPayment();
	}

	/**
	 * @return Payment
	 */
	public function getPayment() : Payment
	{
		return $this->payment;
	}

	/**
	 * @return PayableItemCollection
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected static function createCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$className = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);

		return new $className();
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param Payment $payment
	 */
	public function setPayment(Payment $payment)
	{
		$this->payment = $payment;
	}

	/**
	 * @param Payment $payment
	 * @return PayableItemCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public static function load(Payment $payment)
	{
		$collection = static::createCollectionObject();
		$collection->setPayment($payment);

		if ($payment->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var PayableItem $entity */
			$entity = $registry->get(Registry::ENTITY_PAYABLE_SHIPMENT);

			$items = $entity::loadForPayment($payment->getId());
			foreach ($items as $item)
			{
				$item->setCollection($collection);
				$collection->addItem($item);
			}

			/** @var PayableItem $entity */
			$entity = $registry->get(Registry::ENTITY_PAYABLE_BASKET_ITEM);

			$items = $entity::loadForPayment($payment->getId());
			foreach ($items as $item)
			{
				$item->setCollection($collection);
				$collection->addItem($item);
			}
		}

		return $collection;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\PayableItemTable::getList($parameters);
	}

	protected function addItem(Internals\CollectableEntity $item)
	{
		if (!$item instanceof PayableItem)
		{
			throw new Main\SystemException(
				Main\Localization\Loc::getMessage(
					'SALE_PAYABLE_ITEM_COLLECTION_INCOMPATIBLE_ITEM_TYPE',
					['#CLASS#' => PayableItem::class]
				)
			);
		}

		return parent::addItem($item);
	}

	/**
	 * @param BasketItem $basketItem
	 * @return PayableBasketItem
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function createItemByBasketItem(BasketItem $basketItem) : PayableBasketItem
	{
		/** @var PayableBasketItem $item */
		foreach ($this->getBasketItems() as $item)
		{
			$payableBasketItem = $item->getEntityObject();
			if (
				$payableBasketItem
				&& $basketItem->getBasketCode() === $payableBasketItem->getBasketCode())
			{
				return $item;
			}
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PayableBasketItem $payableItemClass */
		$payableItemClass = $registry->get(Registry::ENTITY_PAYABLE_BASKET_ITEM);

		/** @var PayableBasketItem $payableItem */
		$payableItem = $payableItemClass::create($this, $basketItem);
		$this->addItem($payableItem);

		return $payableItem;
	}

	public function onBeforeBasketItemDelete(BasketItem $basketItem)
	{
		$result = new Result();

		/** @var PayableBasketItem $item */
		foreach ($this->getBasketItems() as $item)
		{
			/** @var BasketItem $entity */
			$entity = $item->getEntityObject();
			if ($entity->getBasketCode() === $basketItem->getBasketCode())
			{
				$r = $item->delete();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return PayableShipmentItem
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function createItemByShipment(Shipment $shipment) : PayableShipmentItem
	{
		/** @var PayableShipmentItem $item */
		foreach ($this->getShipments() as $item)
		{
			if ($shipment->getInternalIndex() === $item->getEntityObject()->getInternalIndex())
			{
				return $item;
			}
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PayableShipmentItem $payableItemClass */
		$payableItemClass = $registry->get(Registry::ENTITY_PAYABLE_SHIPMENT);

		/** @var PayableShipmentItem $payableItem */
		$payableItem = $payableItemClass::create($this, $shipment);
		$this->addItem($payableItem);

		return $payableItem;
	}

	/**
	 * @return Internals\CollectionFilterIterator
	 */
	public function getBasketItems() : Internals\CollectionFilterIterator
	{
		$callback = function (PayableItem $entity)
		{
			return $entity instanceof PayableBasketItem;
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

	/**
	 * @return Internals\CollectionFilterIterator
	 */
	public function getShipments() : Internals\CollectionFilterIterator
	{
		$callback = function (PayableItem $entity)
		{
			return $entity instanceof PayableShipmentItem;
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		$dbRes = static::getList([
			'filter' => ['PAYMENT_ID' => $this->getPayment()->getId()]
		]);

		while ($item = $dbRes->fetch())
		{
			if (!$this->getItemById($item['ID']))
			{
				static::deleteInternal($item['ID']);
			}
		}

		/** @var PayableItem $entity */
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
	 * @param $paymentId
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 *@internal
	 *
	 */
	public static function deleteNoDemand($paymentId)
	{
		$result = new Result();

		$dbRes = static::getList([
			"filter" => ["=PAYMENT_ID" => $paymentId],
			"select" => ["ID"]
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

	/**
	 * @param $primary
	 * @return Main\ORM\Data\DeleteResult
	 * @throws \Exception
	 */
	protected static function deleteInternal($primary)
	{
		return Internals\PayableItemTable::delete($primary);
	}

	/**
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return PayableItemCollection
	 * @internal
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var PayableItemCollection $payableItemCollection */
		$payableItemCollection = parent::createClone($cloneEntity);

		if ($this->payment)
		{
			if ($cloneEntity->contains($this->payment))
			{
				$payableItemCollection->payment = $cloneEntity[$this->payment];
			}
		}

		return $payableItemCollection;
	}
}