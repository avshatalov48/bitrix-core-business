<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class ShipmentCollection
 * @package Bitrix\Sale
 */
class ShipmentCollection
	extends Internals\EntityCollection
{
	/** @var Order */
	protected $order;

	/** @var array */
	private $errors = array();

	private static $eventClassName = null;

	/**
	 * Getting the parent entity
	 * @return Order - order entity
	 */
	protected function getEntityParent()
	{
		return $this->getOrder();
	}

	/**
	 *
	 * Deletes all shipments and creates system shipment containing the whole basket
	 *
	 * @internal
	 *
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function resetCollection()
	{
		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var Basket $basket */
		if (!$basket = $order->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		$result = new Result();

		$deliveryInfo = array();

		if (count($this->collection) > 0)
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if (empty($deliveryInfo))
				{
					if ($shipment->isSystem() && $shipment->getDeliveryId() > 0)
					{
						foreach (static::getClonedFields() as $field)
						{
							if (strval(trim($shipment->getField($field))) != '')
								$deliveryInfo[$field] = trim($shipment->getField($field));
						}
					}
				}
				$shipment->delete();
			}
		}

		/** @var Shipment $systemShipment */
		if (!$systemShipment = $this->getSystemShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var ShipmentItemCollection $systemShipmentItemCollection */
		if (!$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$systemShipmentItemCollection->resetCollection($basket);

		if (!empty($deliveryInfo))
		{
			$systemShipment->setFieldsNoDemand($deliveryInfo);
		}

		if (Configuration::getProductReservationCondition() == Configuration::RESERVE_ON_CREATE)
		{
			/** @var Result $r */
			$r = $this->tryReserve();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Creates new shipment
	 *
	 * @param Delivery\Services\Base $delivery
	 * @return Shipment
	 */
	public function createItem(Delivery\Services\Base $delivery = null)
	{
		/** @var Shipment $shipmentClassName */
		$shipmentClassName = static::getItemCollectionClassName();
		$shipment = $shipmentClassName::create($this, $delivery);
		$this->addItem($shipment);

		return $shipment;
	}

	/**
	 * Adding shipping to the collection
	 *
	 * @param Internals\CollectableEntity $shipment
	 * @return Internals\CollectableEntity|Shipment
	 * @throws Main\ObjectNotFoundException
	 */
	protected function addItem(Internals\CollectableEntity $shipment)
	{
		/** @var Shipment $shipment */
		$shipment = parent::addItem($shipment);

		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$order->onShipmentCollectionModify(EventActions::ADD, $shipment);

		return $shipment;
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return mixed|void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function deleteItem($index)
	{
		$result = new Result();
		/** @var Shipment $oldItem */
		$oldItem = parent::deleteItem($index);

		/** @var Shipment $systemShipment */
		if ($oldItem->getId() > 0 && !$oldItem->isSystem() && ($systemShipment = $this->getSystemShipment()) && $systemShipment->getId() == 0)
		{
			$r = $this->cloneShipment($oldItem, $systemShipment);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		$order = $this->getOrder();
		$order->onShipmentCollectionModify(EventActions::DELETE, $oldItem);
	}

	/**
	 * Processing changes the essence of the shipment fields
	 *
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		/** @var Order $order */
		$order = $this->getOrder();

		if ($item instanceof Shipment)
		{
			return $order->onShipmentCollectionModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
		}

		return new Result();
	}

	/**
	 * Getting entity of the order
	 *
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * Loaded from the database collection shipments Order
	 *
	 * @param Order $order
	 * @return ShipmentCollection
	 * @throws Main\ArgumentNullException
	 */
	public static function load(Order $order)
	{
		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = static::createShipmentCollectionObject();
		$shipmentCollection->setOrder($order);

		if ($order->getId() > 0)
		{
			/** @var Shipment $shipmentClassName */
			$shipmentClassName = static::getItemCollectionClassName();
			$shipmentList = $shipmentClassName::loadForOrder($order->getId());
			/** @var Shipment $shipment */
			foreach ($shipmentList as $shipment)
			{
				$shipment->setCollection($shipmentCollection);
				$shipmentCollection->addItem($shipment);
			}
		}

		return $shipmentCollection;
	}

	/**
	 * @return ShipmentCollection
	 */
	private static function createShipmentCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$className = $registry->getShipmentCollectionClassName();

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
	 * Getting the system shipment
	 *
	 * @return Shipment
	 */
	public function getSystemShipment()
	{
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isSystem())
			{
				return $shipment;
			}
		}

		/** @var Shipment $shipmentClassName */
		$shipmentClassName = static::getItemCollectionClassName();
		$shipment = $shipmentClassName::createSystem($this);
		$this->addItem($shipment);

		return $shipment;
	}

	/**
	 * Check whether there is a system collection of shipping
	 *
	 * @return bool
	 */
	public function isExistsSystemShipment()
	{
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isSystem())
				return true;
		}

		return false;
	}

	/**
	 * Saving data collection
	 *
	 * @return Entity\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$result = new Entity\Result();

		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$itemsFromDb = array();
		if ($order->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_ID" => $order->getId()),
					"select" => array("ID" , "DELIVERY_NAME", "DELIVERY_ID")
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
		}

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isSystem())
				continue;

			if (($systemShipment = $this->getSystemShipment()) && $systemShipment->getId() == 0)
			{
				/** @var Result $r */
				$r = $this->cloneShipment($shipment, $systemShipment);
				if ($r->isSuccess())
				{
					break;
				}
				else
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		$changeMeaningfulFields = array(
			"DELIVERY_LOCATION",
			"PRICE_DELIVERY",
			"CUSTOM_PRICE_DELIVERY",
			"ALLOW_DELIVERY",
			"DEDUCTED",
			"RESERVED",
			"DELIVERY_NAME",
			"DELIVERY_ID",
			"CANCELED",
			"MARKED",
			"SYSTEM",
			"COMPANY_ID",
			"DISCOUNT_PRICE",
			"BASE_PRICE_DELIVERY",
			"EXTERNAL_DELIVERY",
		);

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			$isNew = (bool)($shipment->getId() <= 0);
			$isChanged = $shipment->isChanged();

			if ($order->getId() > 0 && $isChanged)
			{
				$logFields = array();


				$fields = $shipment->getFields();
				$originalValues = $fields->getOriginalValues();

				foreach($originalValues as $originalFieldName => $originalFieldValue)
				{
					if (in_array($originalFieldName, $changeMeaningfulFields) && $shipment->getField($originalFieldName) != $originalFieldValue)
					{
						$logFields[$originalFieldName] = $shipment->getField($originalFieldName);
						if (!$isNew)
							$logFields['OLD_'.$originalFieldName] = $originalFieldValue;
					}
				}

			}

			$r = $shipment->save();
			if ($r->isSuccess())
			{
				if ($order->getId() > 0)
				{
					if ($isChanged)
					{
						$registry = Registry::getInstance(static::getRegistryType());

						/** @var OrderHistory $orderHistory */
						$orderHistory = $registry->getOrderHistoryClassName();
						$orderHistory::addLog(
							'SHIPMENT',
							$order->getId(),
							$isNew ? 'SHIPMENT_ADD' : 'SHIPMENT_UPDATE',
							$shipment->getId(),
							$shipment,
							$logFields,
							$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
						);

						$orderHistory::addAction(
							'SHIPMENT',
							$order->getId(),
							"SHIPMENT_SAVED",
							$shipment->getId(),
							$shipment,
							array(),
							OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
						);
					}
				}

			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (isset($itemsFromDb[$shipment->getId()]))
				unset($itemsFromDb[$shipment->getId()]);
		}

		if (self::$eventClassName === null)
		{
			$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
			$shipmentClassName = $registry->getShipmentClassName();
			self::$eventClassName = $shipmentClassName::getEntityEventName();
		}

		foreach ($itemsFromDb as $k => $v)
		{
			$v['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

			/** @var Main\Event $event */
			$event = new Main\Event('sale', "OnBefore".self::$eventClassName."Deleted", array(
					'VALUES' => $v,
			));
			$event->send();

			$this->deleteInternal($k);
			$this->deleteExtraServiceInternal($k);

			/** @var Main\Event $event */
			$event = new Main\Event('sale', "On".self::$eventClassName."Deleted", array(
					'VALUES' => $v,
			));
			$event->send();

			if ($order->getId() > 0)
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addAction(
					'SHIPMENT',
					$order->getId(),
					'SHIPMENT_REMOVED',
					$k,
					null,
					array(
						'ID' => $k,
						'DELIVERY_NAME' => $v['DELIVERY_NAME'],
						'DELIVERY_ID' => $v['DELIVERY_ID'],
					)
				);

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var EntityMarker $entityMarker */
				$entityMarker = $registry->getEntityMarkerClassName();
				$entityMarker::deleteByFilter(array(
					 '=ORDER_ID' => $order->getId(),
					 '=ENTITY_TYPE' => $entityMarker::ENTITY_TYPE_SHIPMENT,
					 '=ENTITY_ID' => $k,
				 ));
			}

		}

		if ($order->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('SHIPMENT', $order->getId());
		}

		return $result;
	}

	/**
	 * The attachment order to the collection
	 *
	 * @param OrderBase $order
	 */
	public function setOrder(OrderBase $order)
	{
		$this->order = $order;
	}

	/**
	 * @internal
	 * @param Shipment $parentShipment
	 * @param Shipment $childShipment
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function cloneShipment(Shipment $parentShipment, Shipment $childShipment)
	{
		foreach (static::getClonedFields() as $fieldName)
		{
			/** @var Result $r */
			$childShipment->setFieldNoDemand($fieldName, $parentShipment->getField($fieldName));
		}

		$childShipment->setExtraServices($parentShipment->getExtraServices());
		$childShipment->setStoreId($parentShipment->getStoreId());
		return new Result();
	}

	/**
	 * Fields that are cloned into the system from a conventional shipping
	 *
	 * @return array
	 */
	protected static function getClonedFields()
	{
		return array(
			'DELIVERY_LOCATION',
			'PARAMS',
			'DELIVERY_ID',
			'DELIVERY_NAME',
		);
	}

	/**
	 * Is the entire collection shipped
	 *
	 * @return bool
	 */
	public function isShipped()
	{
		if (!empty($this->collection) && is_array($this->collection))
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if ($shipment->isSystem())
				{
					if (!$shipment->isEmpty())
					{
						return false;
					}

					continue;
				}

				if (!$shipment->isShipped() && !$shipment->isEmpty())
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Is the entire collection shipped
	 *
	 * @return bool
	 */
	public function hasShipped()
	{
		$emptyShipment = true;
		if (!empty($this->collection) && is_array($this->collection))
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if ($shipment->isSystem())
					continue;

				if ($shipment->isShipped() && !$shipment->isEmpty())
					return true;

				if (!$shipment->isEmpty())
					$emptyShipment = false;
			}

			if ($this->isExistsSystemShipment() && $this->isEmptySystemShipment())
				return true;

			if ($emptyShipment)
				return false;
		}

		return false;
	}

	/**
	 * Is the entire collection of marked
	 *
	 * @return bool
	 */
	public function isMarked()
	{
		if (!empty($this->collection) && is_array($this->collection))
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if ($shipment->isSystem())
					continue;

				if ($shipment->isMarked())
					return true;
			}
		}

		return false;
	}

	/**
	 * Is the entire collection reserved
	 *
	 * @return bool
	 */
	public function isReserved()
	{
		if (!empty($this->collection) && is_array($this->collection))
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if ($shipment->isSystem())
				{
					if (count($this->collection) == 1)
						return $shipment->isReserved();

					continue;
				}

				if (!$shipment->isReserved())
					return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Is the entire collection allowed for shipment
	 *
	 * @return bool
	 */
	public function isAllowDelivery()
	{
		if (!empty($this->collection) && is_array($this->collection))
		{
			/** @var Shipment $shipment */
			foreach ($this->collection as $shipment)
			{
				if ($shipment->isSystem())
				{
					if (!$shipment->isEmpty())
					{
						return false;
					}

					continue;
				}

				if (!$shipment->isAllowDelivery() && !$shipment->isEmpty())
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function hasAllowDelivery()
	{
		$collection = $this->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			if ($shipment->isAllowDelivery())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Is the a system shipped empty
	 *
	 * @return bool
	 */
	public function isEmptySystemShipment()
	{
		return $this->getSystemShipment()->isEmpty();
	}

	/**
	 * Resolution fact shipment to shipment collection
	 *
	 * @return Result
	 */
	public function allowDelivery()
	{
		$result = new Result();

		$collection = $this->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$r = $shipment->allowDelivery();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		return $result;
	}

	/**
	 * Prohibition upon shipment to shipment collection
	 * @return Result
	 */
	public function disallowDelivery()
	{
		$result = new Result();

		$collection = $this->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$r = $shipment->disallowDelivery();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Trying to reserve the contents of the shipment collection
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function tryReserve()
	{
		$result = new Result();

		/** @var Order $order */
		if (!($order = $this->getOrder()))
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isReserved() || $shipment->isShipped())
				continue;

			$r = $shipment->tryReserve();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());

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
		return $result;
	}

	/**
	 * Trying to reserve the contents of the shipment collection
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function tryUnreserve()
	{
		$result = new Result();

		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isShipped())
			{
				if ($order &&
					!Internals\ActionEntity::isTypeExists(
						$order->getInternalId(),
						Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_RESERVED_QUANTITY
					)
				)
				{
					Internals\ActionEntity::add(
						$order->getInternalId(),
						Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_RESERVED_QUANTITY,
						array(
							'METHOD' => 'Bitrix\Sale\Shipment::updateReservedFlag',
							'PARAMS' => array($shipment)
						)
					);
				}

				continue;
			}

			$r = $shipment->tryUnreserve();
			if (!$r->isSuccess())
			{
				if (!$shipment->isSystem())
				{
					$registry = Registry::getInstance(static::getRegistryType());

					/** @var EntityMarker $entityMarker */
					$entityMarker = $registry->getEntityMarkerClassName();
					$entityMarker::addMarker($order, $shipment, $r);

					$shipment->setField('MARKED', 'Y');
				}
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param BasketItem $basketItem
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBeforeBasketItemDelete(BasketItem $basketItem)
	{
		$result = new Result();

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			$r = $shipment->onBeforeBasketItemDelete($basketItem);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $action
	 * @param BasketItemBase $basketItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if (!($basketItem instanceof BasketItem))
		{
			return $result;
		}

		if ($action === EventActions::DELETE)
		{
			$order = $this->getOrder();
			if ($order->getId() == 0 && !$order->isMathActionOnly())
			{
				$this->refreshData();
			}

			return $result;
		}
		elseif ($action === EventActions::ADD)
		{
			$systemShipment = $this->getSystemShipment();
			return $systemShipment->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		}
		elseif ($action !== EventActions::UPDATE)
		{
			return $result;
		}

		$currentShipment = null;
		$allowQuantityChange = false;

		if ($name == 'QUANTITY')
		{
			$deltaQuantity = $value - $oldValue;

			if ($deltaQuantity != 0)
			{
				if (count($this->collection) == 1 || (count($this->collection) == 2) && $this->isExistsSystemShipment())
				{
					/** @var Shipment $shipment */
					foreach ($this->collection as $shipment)
					{
						if ($shipment->isSystem())
						{
							if ($shipment->isExistBasketItem($basketItem))
							{
								$allowQuantityChange = false;
								$currentShipment = null;
								break;
							}
						}
						elseif ($shipment->isExistBasketItem($basketItem))
						{
							$allowQuantityChange = true;
							$currentShipment = $shipment;
						}
						elseif ($basketItem->getId() == 0)
						{
							$allowQuantityChange = true;
							$currentShipment = $shipment;
							break;
						}
					}
				}

				if ($allowQuantityChange && $currentShipment)
				{
					$allowQuantityChange = (bool)(!$currentShipment->isAllowDelivery() && !$currentShipment->isCanceled() && !$currentShipment->isShipped());

					if ($allowQuantityChange)
					{
						/** @var Delivery\Services\Base $deliveryService */
						if ($deliveryService = $currentShipment->getDelivery())
						{
							$allowQuantityChange = $deliveryService->isAllowEditShipment();
						}
					}
				}

				if (!$allowQuantityChange && $deltaQuantity < 0)
				{
					$basketItemQuantity = $this->getBasketItemDistributedQuantity($basketItem);
					if ($basketItemQuantity > $value)
					{
						if (!$basketItem->isBundleChild() && !isset($this->errors[$basketItem->getBasketCode()]['SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY']))
						{
							$result->addError(new ResultError(
								Loc::getMessage('SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY',
									array(
										'#PRODUCT_NAME#' => $basketItem->getField("NAME"),
										'#BASKET_ITEM_QUANTITY#' => ($basketItemQuantity),
										'#BASKET_ITEM_MEASURE#' => $basketItem->getField("MEASURE_NAME"),
										'#QUANTITY#' => ($basketItemQuantity - $value)
									)
								),
								'SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY'));

							$this->errors[$basketItem->getBasketCode()]['SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY'] = $basketItemQuantity - $value;
						}

						return $result;
					}
				}
			}
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		$systemShipment = $this->getSystemShipment();

		$r = $systemShipment->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($name == 'QUANTITY')
		{
			if ($allowQuantityChange)
			{
				if ($currentShipment)
				{
					/** @var ShipmentItemCollection $shipmentItemCollection */
					if (!$shipmentItemCollection = $currentShipment->getShipmentItemCollection())
					{
						throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
					}

					if ($shipmentItem = $shipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode()))
					{
						$r = $shipmentItem->setField(
								"QUANTITY",
								$shipmentItem->getField("QUANTITY") + $deltaQuantity
						);

						if ($r->isSuccess())
						{
							if ($deltaQuantity < 0)
							{
								$r = $systemShipment->onBasketModify($action, $basketItem, $name, $oldValue, $value);
								if (!$r->isSuccess())
								{
									$result->addErrors($r->getErrors());
									return $result;
								}
							}

							/** @var Delivery\CalculationResult $deliveryCalculate */
							$deliveryCalculate = $currentShipment->calculateDelivery();
							if (!$deliveryCalculate->isSuccess())
							{
								$result->addErrors($deliveryCalculate->getErrors());
							}

							if ($deliveryCalculate->getPrice() > 0)
							{
								$currentShipment->setField('BASE_PRICE_DELIVERY', $deliveryCalculate->getPrice());
							}
						}
						else
						{
							$result->addErrors($r->getErrors());
						}
					}
					else
					{
						if ($shipmentItem = $shipmentItemCollection->createItem($basketItem))
						{
							$shipmentItem->setField("QUANTITY", $basketItem->getQuantity());
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onOrderModify($name, $oldValue, $value)
	{
		$result = new Result();

		switch($name)
		{
			case "CANCELED":
				if ($value == "Y")
				{
					$isShipped = false;
					/** @var Shipment $shipment */
					foreach ($this->collection as $shipment)
					{
						if ($shipment->isShipped())
						{
							$isShipped = true;
							break;
						}
					}

					if ($isShipped)
					{
						$result->addError(
							new ResultError(
								Loc::getMessage('SALE_ORDER_CANCEL_SHIPMENT_EXIST_SHIPPED'),
								'SALE_ORDER_CANCEL_SHIPMENT_EXIST_SHIPPED'
							)
						);

						return $result;
					}

					$this->tryUnreserve();
				}
				else
				{
					if (!$order = $this->getOrder())
					{
						throw new Main\ObjectNotFoundException('Entity "Order" not found');
					}
					
					/** @var Shipment $shipment */
					foreach ($this->collection as $shipment)
					{
						if ($shipment->needReservation())
						{
							/** @var Result $r */
							$r = $shipment->tryReserve();
							if (!$r->isSuccess())
							{
								$registry = Registry::getInstance(static::getRegistryType());

								/** @var EntityMarker $entityMarker */
								$entityMarker = $registry->getEntityMarkerClassName();
								$entityMarker::addMarker($order, $shipment, $r);
								if (!$shipment->isSystem())
								{
									$shipment->setField('MARKED', 'Y');
								}

								$result->addErrors($r->getErrors());
							}
						}
					}

				}
			break;

			case "MARKED":
				if ($value == "N")
				{
					/** @var Shipment $shipment */
					foreach ($this->collection as $shipment)
					{
						if ($shipment->isSystem())
							continue;

						$shipment->setField('MARKED', $value);
					}
				}
			break;
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function refreshData()
	{
		$result = new Result();

		$r = $this->calculateDelivery();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function calculateDelivery()
	{
		/** @var Result $result */
		$result = new Result();

		$calculatedDeliveries = [];

		$collection = $this->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			if ($shipment->getDeliveryId() == 0)
				continue;

			if ($shipment->isCustomPrice())
			{
				$priceDelivery = $shipment->getPrice();

				$calcResult = new Delivery\CalculationResult();
				$calcResult->setDeliveryPrice($priceDelivery);
			}
			else
			{
				/** @var Delivery\CalculationResult $calcResult */
				$calcResult = $shipment->calculateDelivery();
				if (!$calcResult->isSuccess())
				{
					$result->addErrors($calcResult->getErrors());
					continue;
				}

				$priceDelivery = $calcResult->getPrice();
				if ($priceDelivery < 0)
				{
					$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_SHIPMENT_WRONG_DELIVERY_PRICE'), 'WRONG_DELIVERY_PRICE'));
					continue;
				}
			}

			$priceDelivery = PriceMaths::roundPrecision($priceDelivery);
			$shipment->setField('BASE_PRICE_DELIVERY', $priceDelivery);

			$calculatedDeliveries[] = $calcResult;
		}

		$result->setData(['CALCULATED_DELIVERIES' => $calculatedDeliveries]);

		return $result;
	}

	/**
	 *
	 */
	public function resetData()
	{
		$collection = $this->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$shipment->resetData();
		}
	}

	/**
	 * @param BasketItem $basketItem
	 * @return float|int
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function getBasketItemDistributedQuantity(BasketItem $basketItem)
	{
		$collection = $this->getNotSystemItems();

		$allQuantity = 0;

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$allQuantity += $shipment->getBasketItemQuantity($basketItem);
		}

		return $allQuantity;
	}

	/**
	 * @param BasketItem $basketItem
	 * @param bool|false $includeSystemShipment
	 *
	 * @return bool
	 * @throws Main\ObjectNotFoundException
	 */
	public function isExistBasketItem(BasketItem $basketItem, $includeSystemShipment = false)
	{
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if (!$includeSystemShipment && $shipment->isSystem())
			{
				continue;
			}

			return $shipment->isExistBasketItem($basketItem);
		}

		return false;
	}
	/**
	 * @return float
	 */
	public function getBasePriceDelivery()
	{
		$collection = $this->getNotSystemItems();

		$sum = 0;
		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$sum += $shipment->getField('BASE_PRICE_DELIVERY');
		}

		return $sum;
	}

	/**
	 * @return float
	 */
	public function getPriceDelivery()
	{
		$collection = $this->getNotSystemItems();

		$sum = 0;
		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			$sum += $shipment->getPrice();
		}


		return $sum;
	}

	/**
	 * @param $itemCode
	 * @return Shipment|null
	 */
	public function getItemByShipmentCode($itemCode)
	{
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			$shipmentCode = $shipment->getShipmentCode();
			if ($itemCode == $shipmentCode)
				return $shipment;

		}

		return null;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($shipment->isSystem())
			{
				continue;
			}

			$r = $shipment->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var EntityMarker $entityMarker */
				$entityMarker = $registry->getEntityMarkerClassName();
				$entityMarker::addMarker($this->getOrder(), $shipment, $r);

				$shipment->setField('MARKED', 'Y');
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return ShipmentCollection
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}
		
		/** @var ShipmentCollection $shipmentCollectionClone */
		$shipmentCollectionClone = parent::createClone($cloneEntity);

		if ($this->order)
		{
			if ($cloneEntity->contains($this->order))
			{
				$shipmentCollectionClone->order = $cloneEntity[$this->order];
			}
		}

		return $shipmentCollectionClone;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		$className = null;
		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($className = $shipment->getErrorEntity($value))
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

		/** @var Shipment $shipment */
		foreach ($this->collection as $shipment)
		{
			if ($autoFix = $shipment->canAutoFixError($value))
			{
				break;
			}
		}
		return $autoFix;
	}

	/**
	 * @param ShipmentCollection $collection
	 *
	 * @return Result
	 */
	public function updateReservedFlag(ShipmentCollection $collection)
	{
		$result = new Result();
		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			/** @var Shipment $shipmentClassName */
			$shipmentClassName = static::getItemCollectionClassName();
			$r = $shipmentClassName::updateReservedFlag($shipment);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private static function getItemCollectionClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getShipmentClassName();
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\ShipmentTable::getList($parameters);
	}

	/**
	 * @param $primary
	 * @return Entity\DeleteResult
	 */
	protected function deleteInternal($primary)
	{
		return Internals\ShipmentTable::deleteWithItems($primary);
	}

	/**
	 * @param $shipmentId
	 */
	protected function deleteExtraServiceInternal($shipmentId)
	{
		Internals\ShipmentExtraServiceTable::deleteByShipmentId($shipmentId);
	}

	/**
	 * @return Internals\CollectionFilterIterator
	 */
	public function getNotSystemItems()
	{
		$callback = function (Shipment $shipment)
		{
			return !$shipment->isSystem();
		};

		return new Internals\CollectionFilterIterator($this->getIterator(), $callback);
	}

}
