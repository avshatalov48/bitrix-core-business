<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\TradingPlatform;

/**
 * Class TradeBindingCollection
 * @package Bitrix\Sale
 */
class TradeBindingCollection extends Internals\EntityCollection
{
	protected $order = null;

	/**
	 * @return Internals\Entity
	 */
	protected function getEntityParent()
	{
		return $this->order;
	}

	/**
	 * @return null
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return TradeBindingCollection
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function createCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$className = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);

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
	 * @param Order $order
	 */
	public function setOrder(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @param Order $order
	 * @return TradeBindingCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public static function load(Order $order)
	{
		$collection = static::createCollectionObject();
		$collection->setOrder($order);

		if (!$order->isNew())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var TradeBindingEntity $entity */
			$entity = $registry->get(Registry::ENTITY_TRADE_BINDING_ENTITY);

			$bindingList = $entity::loadForOrder($order->getId());
			/** @var TradeBindingEntity $item */
			foreach ($bindingList as $item)
			{
				$item->setCollection($collection);
				$collection->addItem($item);
			}
		}

		return $collection;
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		return TradingPlatform\OrderTable::getList($parameters);
	}

	/**
	 * @param TradingPlatform\Platform|null $platform
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws Main\SystemException
	 */
	public function createItem(TradingPlatform\Platform $platform = null)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var TradeBindingEntity $tradeBindingEntity */
		$tradeBindingEntity = $registry->get(Registry::ENTITY_TRADE_BINDING_ENTITY);

		$tradeBinding = $tradeBindingEntity::create($this, $platform);
		$this->addItem($tradeBinding);

		return $tradeBinding;
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @return Internals\CollectableEntity
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function addItem(Internals\CollectableEntity $item)
	{
		if (!($item instanceof TradeBindingEntity))
		{
			throw new Main\NotSupportedException();
		}

		return parent::addItem($item);
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

		/** @var Order $order */
		if (!$order = $this->getEntityParent())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		if (!$order->isNew())
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_ID" => $order->getId())
				)
			);

			while ($item = $itemsFromDbList->fetch())
			{
				if (!$this->getItemById($item['ID']))
				{
					static::deleteInternal($item['ID']);
				}
			}
		}

		/** @var TradeBindingEntity $entity */
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
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();

		$dbRes = static::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID")
			)
		);

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
		return TradingPlatform\OrderTable::delete($primary);
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return TradeBindingCollection
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var TradeBindingCollection $tradeBindingCollection */
		$tradeBindingCollection = parent::createClone($cloneEntity);

		if ($this->order)
		{
			if ($cloneEntity->contains($this->order))
			{
				$tradeBindingCollection->order = $cloneEntity[$this->order];
			}
		}

		return $tradeBindingCollection;
	}
}