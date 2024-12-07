<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Internals\CollectableEntity;

Loc::loadMessages(__FILE__);

class ShipmentItemCollection
	extends Internals\EntityCollection
{
	/** @var Shipment */
	protected $shipment;

	protected $shipmentItemIndexMap = array();

	/**
	 * @return Shipment
	 */
	protected function getEntityParent()
	{
		return $this->getShipment();
	}

	/**
	 * @param Basket $basket
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function resetCollection(Basket $basket)
	{
		if ($this->getShipment()->isShipped())
		{
			throw new Main\NotSupportedException();
		}

		if (!empty($this->collection))
		{
			/** @var ShipmentItem $shipmentItem */
			foreach ($this->collection as $shipmentItem)
			{
				$shipmentItem->setFieldNoDemand('QUANTITY', 0);
				$shipmentItem->delete();
			}
		}

		$quantityList = [];

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->getShipment()->getCollection();

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$quantityList[$basketItem->getBasketCode()] = $shipmentCollection->getBasketItemDistributedQuantity($basketItem);
		}

		/** @var ShipmentItem $itemClassName */
		$itemClassName = static::getItemCollectionClassName();

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$shipmentItem = $itemClassName::create($this, $basketItem);
			$this->addItem($shipmentItem);

			$basketItemQuantity = 0;

			if (array_key_exists($basketItem->getBasketCode(), $quantityList))
			{
				$basketItemQuantity = $quantityList[$basketItem->getBasketCode()];
			}

			$quantity = $basketItem->getQuantity() - $basketItemQuantity;

			$shipmentItem->setFieldNoDemand("QUANTITY", $quantity);

			if ($basketItem->isBundleParent())
			{
				$this->addBundleToCollection($basketItem);
			}
		}
	}

	/**
	 * @param BasketItem $basketItem
	 * @return ShipmentItem|null
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function createItem(BasketItem $basketItem)
	{
		if ($this->getShipment()->isShipped())
		{
			return null;
		}

		$shipmentItem = $this->getItemByBasketCode($basketItem->getBasketCode());
		if ($shipmentItem !== null)
		{
			return $shipmentItem;
		}

		/** @var ShipmentItem $itemClassName */
		$itemClassName = static::getItemCollectionClassName();

		$shipmentItem = $itemClassName::create($this, $basketItem);

		$shipmentItem->setCollection($this);
		$this->addItem($shipmentItem);

		$shipment = $this->getShipment();

		if ($basketItem->isBundleParent() && !$shipment->isSystem())
		{
			$this->addBundleToCollection($basketItem);
		}

		return $shipmentItem;
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectNotFoundException
	 */
	private function addBundleToCollection(BasketItem $basketItem)
	{
		$result = new Result();

		/** @var Basket $bundleCollection */
		if (!$bundleCollection = $basketItem->getBundleCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "BundleCollection" not found');
		}

		if ($bundleCollection->getOrder() === null)
		{
			/** @var Basket $basketCollection */
			if ($basketCollection = $basketItem->getCollection())
			{
				if ($order = $basketCollection->getOrder())
				{
					$bundleCollection->setOrder($order);
				}
			}
		}

		/** @var Shipment $shipment */
		$shipment = $this->getShipment();

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Shipment $systemShipment */
		if (!$systemShipment = $shipmentCollection->getSystemShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "System Shipment" not found');
		}

		/** @var ShipmentItemCollection $systemShipmentItemCollection */
		if (!$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$baseQuantity = $basketItem->getQuantity();

		/** @var ShipmentItem $systemShipmentItem */
		if ($systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode()))
		{
			$baseQuantity = $systemShipmentItem->getQuantity();
		}

		$bundleBaseQuantity = $basketItem->getBundleBaseQuantity();

		/** @var BasketItem $bundleBasketItem */
		foreach ($bundleCollection as $bundleBasketItem)
		{

			if ($this->isExistsBasketItem($bundleBasketItem))
			{
				continue;
			}

			$bundleProductId = $bundleBasketItem->getProductId();

			if (!isset($bundleBaseQuantity[$bundleProductId]))
				throw new Main\ArgumentOutOfRangeException("bundle product id");

			$quantity = $bundleBaseQuantity[$bundleProductId] * $baseQuantity;

			if ($quantity == 0)
				continue;

			/** @var ShipmentItem $itemClassName */
			$itemClassName = static::getItemCollectionClassName();
			$shipmentItemBundle = $itemClassName::create($this, $bundleBasketItem);
			$this->addItem($shipmentItemBundle);

			if ($shipment->isSystem())
			{
				$shipmentItemBundle->setFieldNoDemand('QUANTITY', $quantity);
			}
			else
			{
				$r = $shipmentItemBundle->setQuantity($quantity);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param Internals\CollectableEntity $shipmentItem
	 * @return Internals\CollectableEntity|void
	 */
	protected function addItem(Internals\CollectableEntity $shipmentItem)
	{
		parent::addItem($shipmentItem);

		/** @var Shipment $shipment */
		$shipment = $this->getShipment();
		$shipment->onShipmentItemCollectionModify(EventActions::ADD, $shipmentItem);
	}

	protected function bindItem(CollectableEntity $shipmentItem): CollectableEntity
	{
		$item = parent::bindItem($shipmentItem);

		$this->shipmentItemIndexMap[$shipmentItem->getBasketCode()] = $shipmentItem->getInternalIndex();

		return $item;
	}

	protected function createIndex()
	{
		$index = parent::createIndex();
		$shipment = $this->getShipment();
		return $shipment->getInternalIndex()."_".$index;
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return mixed|void
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public function deleteItem($index)
	{
		$oldShipmentItem = parent::deleteItem($index);

		unset($this->shipmentItemIndexMap[$oldShipmentItem->getBasketCode()]);

		$shipment = $this->getShipment();
		$shipment->onShipmentItemCollectionModify(EventActions::DELETE, $oldShipmentItem);
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\SystemException
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		$shipment = $this->getShipment();
		return $shipment->onShipmentItemCollectionModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
	}

	/**
	 * @param $itemCode
	 * @return ShipmentItem|null
	 */
	public function getItemByBasketCode($itemCode)
	{
		if (
			isset($this->shipmentItemIndexMap[$itemCode])
			&& isset($this->collection[$this->shipmentItemIndexMap[$itemCode]])
		)
		{
			return $this->collection[$this->shipmentItemIndexMap[$itemCode]];
		}

		return null;
	}

	/**
	 * @param $itemId
	 * @return ShipmentItem|null
	 */
	public function getItemByBasketId($itemId)
	{
		$itemId = (int)$itemId;
		foreach ($this->collection as $shippedItem)
		{
			/** @var ShipmentItem $shippedItem */
			$shippedItemId = (int)($shippedItem->getBasketId());
			if ($itemId === $shippedItemId)
				return $shippedItem;
		}

		return null;
	}

	/**
	 * Returns shippable items
	 *
	 * @return Internals\CollectionFilterIterator
	 */
	public function getShippableItems()
	{
		$callback = function (ShipmentItem $shipmentItem)
		{
			return $shipmentItem->isShippable();
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

	/**
	 * @return Internals\CollectionFilterIterator
	 */
	public function getSellableItems()
	{
		$callback = function (ShipmentItem $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			if ($basketItem)
				return !$basketItem->isBundleChild();

			return true;
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getPrice()
	{
		$price = 0;

		$sellableItems = $this->getSellableItems();
		/** @var ShipmentItem $shipmentItem */
		foreach ($sellableItems as $shipmentItem)
		{
			/** @var BasketItem $basketItem */
			if ($basketItem = $shipmentItem->getBasketItem())
			{
				$price += PriceMaths::roundPrecision($basketItem->getPriceWithVat() * $shipmentItem->getQuantity());
			}
		}

		return $price;
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 */
	public function getWeight() : float
	{
		$weight = 0;

		/** @var ShipmentItem $shipmentItem */
		foreach ($this->getShippableItems() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			$weight += $basketItem->getWeight() * $shipmentItem->getQuantity();
		}

		return $weight;
	}

	/**
	 * @return Shipment
	 */
	public function getShipment()
	{
		return $this->shipment;
	}

	/**
	 * @return Main\Entity\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$result = new Main\Entity\Result();

		/** @var Shipment $shipment */
		if (!$shipment = $this->getShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$itemsFromDb = array();
		if ($this->getShipment()->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_DELIVERY_ID" => $this->getShipment()->getId()),
					"select" => array("ID", 'BASKET_ID')
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
		}


		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			/** @var BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			if ($basketItem->isBundleParent())
			{
				$this->addBundleToCollection($basketItem);
			}
		}

		/** @var Shipment $shipment */
		if (!$shipment = $this->getShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		$changeMeaningfulFields = array(
			"QUANTITY",
			"RESERVED_QUANTITY",
		);

		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			/** @var BasketItem $basketItem */
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			$isNew = (bool)($shipmentItem->getId() <= 0);
			$isChanged = $shipmentItem->isChanged();

			if ($order->getId() > 0 && $isChanged)
			{
				$logFields = array(
					"BASKET_ID" => $basketItem->getId(),
					"BASKET_ITEM_NAME" => $basketItem->getField("NAME"),
					"BASKET_ITEM_PRODUCT_ID" => $basketItem->getField("PRODUCT_ID"),
					"ORDER_DELIVERY_ID" => $shipmentItem->getField("ORDER_DELIVERY_ID"),
				);

				$fields = $shipmentItem->getFields();
				$originalValues = $fields->getOriginalValues();

				foreach($originalValues as $originalFieldName => $originalFieldValue)
				{
					if (in_array($originalFieldName, $changeMeaningfulFields) && $shipmentItem->getField($originalFieldName) != $originalFieldValue)
					{
						$logFields[$originalFieldName] = $shipmentItem->getField($originalFieldName);
						if (!$isNew)
							$logFields['OLD_'.$originalFieldName] = $originalFieldValue;
					}
				}
			}

			$r = $shipmentItem->save();
			if ($r->isSuccess())
			{
				if ($order->getId() > 0 && $isChanged)
				{
					$registry = Registry::getInstance(static::getRegistryType());

					/** @var OrderHistory $orderHistory */
					$orderHistory = $registry->getOrderHistoryClassName();
					$orderHistory::addLog(
						'SHIPMENT_ITEM',
						$order->getId(),
						$isNew ? 'SHIPMENT_ITEM_ADD' : 'SHIPMENT_ITEM_UPDATE',
						$shipmentItem->getId(),
						$shipmentItem,
						$logFields,
						$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
					);
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($itemsFromDb[$shipmentItem->getId()]))
			{
				unset($itemsFromDb[$shipmentItem->getId()]);
			}
		}

		/** @var Basket $basket */
		if (!$basket = $order->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		foreach ($itemsFromDb as $k => $v)
		{
			$v['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

			/** @var Main\Event $event */
			$event = new Main\Event('sale', "OnBeforeSaleShipmentItemDeleted", array(
					'VALUES' => $v,
			));
			$event->send();

			static::deleteInternal($k);

			/** @var Main\Event $event */
			$event = new Main\Event('sale', "OnSaleShipmentItemDeleted", array(
					'VALUES' => $v,
			));
			$event->send();

			/** @var BasketItem $basketItem */
			if ($basketItem = $basket->getItemById($k))
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addAction(
					'SHIPMENT',
					$order->getId(),
					'SHIPMENT_ITEM_BASKET_REMOVED',
					$shipment->getId(),
					null,
					array(
						'NAME' => $basketItem->getField('NAME'),
						'QUANTITY' => $basketItem->getQuantity(),
						'PRODUCT_ID' => $basketItem->getProductId(),
					)
				);
			}
		}

		if ($order->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('SHIPMENT_ITEM', $order->getId());
		}

		return $result;
	}

	/**
	 * @return ShipmentItemCollection
	 */
	private static function createShipmentItemCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$shipmentItemCollectionClassName = $registry->getShipmentItemCollectionClassName();

		return new $shipmentItemCollectionClassName();
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return string
	 */
	private static function getItemCollectionClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getShipmentItemClassName();
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return ShipmentItemCollection
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function load(Shipment $shipment)
	{
		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = static::createShipmentItemCollectionObject();
		$shipmentItemCollection->shipment = $shipment;

		if ($shipment->getId() > 0)
		{
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			/** @var Order $order */
			if (!$order = $shipmentCollection->getOrder())
			{
				throw new Main\ObjectNotFoundException('Entity "Order" not found');
			}

			/** @var ShipmentItem $itemClassName */
			$itemClassName = static::getItemCollectionClassName();
			$shipmentItemList = $itemClassName::loadForShipment($shipment->getId());

			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemList as $shipmentItem)
			{
				$shipmentItem->setCollection($shipmentItemCollection);
				$shipmentItemCollection->bindItem($shipmentItem);
				
				if (!$basketItem = $shipmentItem->getBasketItem())
				{
					$msg = Loc::getMessage("SALE_SHIPMENT_ITEM_COLLECTION_BASKET_ITEM_NOT_FOUND", array(
						'#BASKET_ITEM_ID#' => $shipmentItem->getBasketId(),
						'#SHIPMENT_ID#' => $shipment->getId(),
						'#SHIPMENT_ITEM_ID#' => $shipmentItem->getId(),
					));

					$r = new Result();
					$r->addError( new ResultError($msg, 'SALE_SHIPMENT_ITEM_COLLECTION_BASKET_ITEM_NOT_FOUND'));

					$registry = Registry::getInstance(static::getRegistryType());
					/** @var EntityMarker $entityMarker */
					$entityMarker = $registry->getEntityMarkerClassName();
					$entityMarker::addMarker($order, $shipment, $r);
					if (!$shipment->isSystem())
					{
						$shipment->setField('MARKED', 'Y');
					}
				}
			}
		}

		return $shipmentItemCollection;
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\ShipmentItemTable::getList($parameters);
	}

	/**
	 * @param $primary
	 * @return Main\Entity\DeleteResult
	 */
	protected function deleteInternal($primary)
	{
		return Internals\ShipmentItemTable::deleteWithItems($primary);
	}

	/**
	 * @param BasketItem $basketItem
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBeforeBasketItemDelete(BasketItem $basketItem)
	{
		$result = new Result();

		$r = $this->deleteByBasketItem($basketItem);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $action
	 * @param BasketItem $basketItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBasketModify($action, BasketItem $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if ($action === EventActions::ADD)
		{
			$shipmentItem = $this->createItem($basketItem);
			if ($shipmentItem)
			{
				$shipmentItem->onBasketModify($action, $basketItem, $name, $oldValue, $value);
			}

			return $result;
		}
		elseif ($action === EventActions::UPDATE)
		{
			$shipmentItem = $this->getItemByBasketCode($basketItem->getBasketCode());

			if (!$shipmentItem)
			{
				$shipmentItem = $this->createItem($basketItem);
			}

			$r = $shipmentItem->setField('QUANTITY', $value);
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return bool
	 * @throws Main\ObjectNotFoundException
	 */
	protected function isExistsBasketItem(BasketItem $basketItem)
	{
		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			if ($shipmentItem->getBasketCode() == $basketItem->getBasketCode())
				return true;
		}

		return false;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function deleteByBasketItem(BasketItem $basketItem)
	{
		$result = new Result();
		$systemShipmentItem = null;

		/** @var Shipment $shipment */
		if (!$shipment = $this->getShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			if ($shipmentItem->getBasketCode() == $basketItem->getBasketCode())
			{
				if ($shipment->isSystem())
				{
					$systemShipmentItem = $shipmentItem;
					continue;
				}

				$r = $shipmentItem->delete();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		if ($systemShipmentItem !== null)
		{
			if ($systemShipmentItem->getReservedQuantity() > 0)
			{
				/** @var Result $r */
				$r = $systemShipmentItem->tryUnreserve();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if ($result->isSuccess())
			{
				$systemShipmentItem->setFieldNoDemand('QUANTITY', 0);
				$r = $systemShipmentItem->delete();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		if (count($this->collection) == 0)
			return true;

		/** @var ShipmentItem $item */
		foreach ($this->collection as $item)
		{
			if ($item->getQuantity() > 0)
				return false;
		}

		return true;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getBasketItemQuantity(BasketItem $basketItem)
	{
		$quantity = 0;

		$shipmentItem = $this->getItemByBasketCode($basketItem->getBasketCode());
		if ($shipmentItem)
		{
			$quantity = $shipmentItem->getQuantity();
		}

		return $quantity;
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return bool
	 * @throws Main\ObjectNotFoundException
	 */
	public function isExistBasketItem(BasketItem $basketItem)
	{
		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			if ($shipmentItem->getBasketCode() == $basketItem->getBasketCode())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return Internals\EntityCollection|object
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var ShipmentItemCollection $shipmentItemCollectionClone */
		$shipmentItemCollectionClone = parent::createClone($cloneEntity);

		/** @var Shipment $shipment */
		if ($shipment = $this->shipment)
		{
			if (!$cloneEntity->contains($shipment))
			{
				$cloneEntity[$shipment] = $shipment->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($shipment))
			{
				$shipmentItemCollectionClone->shipment = $cloneEntity[$shipment];
			}
		}

		return $shipmentItemCollectionClone;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		$className = null;

		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			if ($className = $shipmentItem->getErrorEntity($value))
			{
				break;
			}
		}

		return $className;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function canAutoFixError($value)
	{
		$autoFix = false;

		/** @var ShipmentItem $shipmentItem */
		foreach ($this->collection as $shipmentItem)
		{
			if ($autoFix = $shipmentItem->canAutoFixError($value))
			{
				break;
			}
		}
		return $autoFix;
	}

}
