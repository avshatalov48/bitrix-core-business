<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\CollectableEntity;
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
	 * @return Order || null
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return TradeBindingCollection
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
	 */
	public static function getList(array $parameters = array())
	{
		return TradingPlatform\OrderTable::getList($parameters);
	}

	/**
	 * @param TradingPlatform\Platform|null $platform
	 * @return mixed
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

	public function onItemModify(CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		/** @var Order $order */
		$order = $this->getOrder();

		if ($item instanceof TradeBindingEntity)
		{
			$order->onTradeBindingCollectionModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
		}

		return parent::onItemModify($item, $name, $oldValue, $value);
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @return Internals\CollectableEntity
	 * @throws Main\NotSupportedException
	 */
	public function addItem(Internals\CollectableEntity $item)
	{
		if (!($item instanceof TradeBindingEntity))
		{
			throw new Main\NotSupportedException();
		}

		/** @var TradeBindingEntity $entity */
		$entity = parent::addItem($item);

		$order = $this->getOrder();
		$order->onTradeBindingCollectionModify(EventActions::ADD, $entity);

		return  $entity;
	}

	/**
	 * @param $index
	 * @return mixed|void
	 */
	public function deleteItem($index)
	{
		/** @var TradeBindingEntity $oldItem */
		$oldItem = parent::deleteItem($index);

		$order = $this->getOrder();
		$order->onTradeBindingCollectionModify(EventActions::DELETE, $oldItem);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
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

	/**
	 * Returns an indicator showing either the collection contains the specified platform
	 *
	 * @param string $platformCode
	 * @param string|null $type
	 * @return bool
	 */
	public function hasTradingPlatform(string $platformCode, string $type = null): bool
	{
		return $this->getTradingPlatform($platformCode, $type) ? true : false;
	}

	/**
	 * Returns the first trading platform by the specified code and type from the collection
	 *
	 * @param string $platformCode
	 * @param string|null $type
	 * @return TradingPlatform\Platform|null
	 */
	public function getTradingPlatform(string $platformCode, string $type = null)
	{
		foreach ($this->collection as $item)
		{
			$tradingPlatform = $item->getTradePlatform();
			if (!$tradingPlatform || $tradingPlatform::TRADING_PLATFORM_CODE !== $platformCode)
			{
				continue;
			}

			if (!is_null($type) && !$tradingPlatform->isOfType($type))
			{
				continue;
			}

			return $tradingPlatform;
		}

		return null;
	}

	public function getTradingPlatformIdList() : array
	{
		$result = [];

		foreach ($this->collection as $item)
		{
			$result[] = $item->getField('TRADING_PLATFORM_ID');
		}

		return $result;
	}
}
