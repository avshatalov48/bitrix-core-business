<?php
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ShipmentItem
	extends Internals\CollectableEntity
	implements \IEntityMarker
{
	/** @var BasketItem */
	protected $basketItem;

	/** @var  ShipmentItemStoreCollection */
	protected $shipmentItemStoreCollection;

	/** @var array */
	protected static $errors = array();

	private static $eventClassName = null;

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array("QUANTITY", "RESERVED_QUANTITY", "XML_ID");
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('QUANTITY');
	}

	/**
	 * Internal method, use ShipmentItemCollection::createItem()
	 *
	 * @internal
	 * @see ShipmentItemCollection::createItem()
	 *
	 * @param ShipmentItemCollection $collection
	 * @param BasketItem $basketItem
	 * @return ShipmentItem
	 */
	public static function create(ShipmentItemCollection $collection, BasketItem $basketItem = null)
	{
		$fields = [
			'XML_ID' => static::generateXmlId()
		];

		if ($basketItem !== null && $basketItem->getId() > 0)
		{
			$fields["BASKET_ID"] = $basketItem->getId();
		}

		$shipmentItem = static::createShipmentItemObject();
		$shipmentItem->setFieldsNoDemand($fields);
		$shipmentItem->setCollection($collection);

		if ($basketItem !== null)
		{
			$shipmentItem->basketItem = $basketItem;
		}

		return $shipmentItem;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * Deletes shipment item
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function delete()
	{
		$result = new Result();
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBefore".self::$eventClassName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(Loc::getMessage('SALE_EVENT_ON_BEFORE_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR'), 'SALE_EVENT_ON_BEFORE_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR');
					if ($eventResultData = $eventResult->getParameters())
					{
						if (isset($eventResultData) && $eventResultData instanceof ResultError)
						{
							/** @var ResultError $errorMsg */
							$errorMsg = $eventResultData;
						}
					}

					$result->addError($errorMsg);
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}
		}


		if (!$shipment->isSystem())
		{
			if ($shipment->isShipped())
			{
				/** @var BasketItem $basketItem */
				if (!$basketItem = $this->getBasketItem())
				{
					throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
				}

				$result->addError(new ResultError(Loc::getMessage(
					'SALE_SHIPMENT_ITEM_SHIPMENT_ALREADY_SHIPPED_CANNOT_DELETE',
					array(
						'#PRODUCT_NAME#' => $basketItem->getField('NAME')
					)), 'SALE_SHIPMENT_ITEM_SHIPMENT_ALREADY_SHIPPED_CANNOT_DELETE'));

				return $result;
			}

			$r = $this->setField("QUANTITY", 0);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}
		elseif ($shipment->isSystem() && $this->getQuantity() > 0)
		{
			throw new \ErrorException('System shipment not empty');
		}

		$r = parent::delete();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}


		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "On".self::$eventClassName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(Loc::getMessage('SALE_EVENT_ON_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR'), 'SALE_EVENT_ON_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR');
					$eventResultData = $eventResult->getParameters();
					if (isset($eventResultData) && $eventResultData instanceof ResultError)
					{
						/** @var ResultError $errorMsg */
						$errorMsg = $eventResultData;
					}

					$result->addError($errorMsg);
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Sets new value to specified field of shipment item
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setField($name, $value)
	{
		/** @var ShipmentItemCollection $collection */
		$collection = $this->getCollection();
		$shipment = $collection->getShipment();
		if ($shipment->isSystem() && ($name != 'RESERVED_QUANTITY'))
			throw new Main\NotSupportedException();

		return parent::setField($name, $value);
	}

	/**
	 * @return float
	 */
	public function getQuantity()
	{
		return (float)$this->getField('QUANTITY');
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
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

		if ($shipment->isShipped())
		{
			$result = new Result();
			$result->addError(new ResultError(Loc::getMessage('SALE_SHIPMENT_ITEM_SHIPMENT_ALREADY_SHIPPED_CANNOT_EDIT'), 'SALE_SHIPMENT_ITEM_SHIPMENT_ALREADY_SHIPPED_CANNOT_EDIT'));
			return $result;
		}

		if ($name == "QUANTITY")
		{
			/** @var BasketItem $basketItem */
			if (!$basketItem = $this->getBasketItem())
			{
				if ($value != 0)
				{
					throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
				}
				
			}


			$deltaQuantity = $value - $oldValue;

			if ($basketItem && $deltaQuantity > 0)
			{

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

				$systemBasketItemQuantity = $systemShipment->getBasketItemQuantity($basketItem);
				if ($systemBasketItemQuantity < abs($deltaQuantity))
				{
					$errorBasketCode = $basketItem->getBasketCode();

					if ($basketItem->isBundleChild())
					{
						/** @var BasketItem $parentBasketItem */
						if (!($parentBasketItem = $basketItem->getParentBasketItem()))
						{
							throw new Main\ObjectNotFoundException('Entity "Parent Basket Item" not found');
						}

						$errorBasketCode = $parentBasketItem->getBasketCode();
					}

					if (isset(static::$errors[$errorBasketCode][$basketItem->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED']))
					{
						static::$errors[$errorBasketCode][$basketItem->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED'] += $basketItem->getQuantity();
					}
					else
					{
						$result->addError(
							new ResultError(
								Loc::getMessage(
									'SALE_SHIPMENT_ITEM_LESS_AVAILABLE_QUANTITY',
									array(
										'#PRODUCT_NAME#' => $basketItem->getField('NAME'),
									)
								), 'SALE_SHIPMENT_ITEM_LESS_AVAILABLE_QUANTITY'
							)
						);

						static::$errors[$errorBasketCode][$basketItem->getField('ORDER_DELIVERY_BASKET_ID')]['STORE_QUANTITY_LARGER_ALLOWED'] = $basketItem->getQuantity();
					}

					return $result;
				}
			}


			if ($basketItem)
			{
				if (!$this->isMathActionOnly())
				{
					$r = $this->calculateDelivery();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}

			/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
			if (!$shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentItemStoreCollection" not found');
			}

			

			if ($value == 0)
			{
				$basketItemName = Loc::getMessage("SALE_SHIPMENT_ITEM_BASKET_WRONG_BASKET_ITEM");
				$basketItemProductId = '1';

				if ($basketItem)
				{
					$basketItemName = $basketItem->getField('NAME');
					$basketItemProductId = $basketItem->getProductId();
				}

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
						'NAME' => $basketItemName,
						'PRODUCT_ID' => $basketItemProductId,
					)
				);

				/** @var ShipmentItemStore $shipmentItemStore */
				foreach ($shipmentItemStoreCollection as $shipmentItemStore)
				{
					$shipmentItemStore->delete();
				}

			}
			elseif (!$basketItem->isBundleParent())
			{
				// check barcodes
				$r = $shipmentItemStoreCollection->onShipmentItemModify(EventActions::UPDATE, $this, $name, $oldValue, $value);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

				$barcodeQuantity = $shipmentItemStoreCollection->getQuantityByBasketCode($basketItem->getBasketCode());
				if ($barcodeQuantity > $value)
				{
					$result->addError(
						new ResultError(
							Loc::getMessage('SALE_SHIPMENT_ITEM_BARCODE_MORE_ITEM_QUANTITY'),
							'BARCODE_MORE_ITEM_QUANTITY'
						)
					);

					return $result;
				}
			}

			if (!$basketItem)
			{
				return $result;
			}
		}


		return parent::onFieldModify($name, $oldValue, $value);
	}


	/**
	 * @param float $quantity
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setQuantity($quantity)
	{
		if (!is_numeric($quantity))
			throw new Main\ArgumentTypeException("quantity");

		return $this->setField('QUANTITY', (float)$quantity);
	}

	/**
	 * @return float
	 */
	public function getReservedQuantity()
	{
		return floatval($this->getField('RESERVED_QUANTITY'));
	}

	/**
	 * @return int
	 */
	public function getBasketId()
	{
		return $this->getField('BASKET_ID');
	}

	/**
	 * @return int
	 * @throws Main\SystemException
	 */
	public function getBasketCode()
	{
		if ($basketItem = $this->getBasketItem())
		{
			return $basketItem->getBasketCode();
		}

		return null;
	}

	/**
	 * @return int
	 * @throws Main\SystemException
	 */
	public function getProductId()
	{
		$basketItem = $this->getBasketItem();
		if ($basketItem)
		{
			return $basketItem->getProductId();
		}

		return null;
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$result = new Result();

		$id = $this->getId();
		$fields = $this->fields->getValues();

		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
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


		if ($this->isChanged() && self::$eventClassName)
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'EntitySaved', array(
					'ENTITY' => $this,
					'VALUES' => $this->fields->getOriginalValues()
			));
			$event->send();
		}


		if ($id > 0)
		{
			$fields = $this->fields->getChangedValues();

			if (!empty($fields) && is_array($fields))
			{
				/** @var ShipmentItemCollection $shipmentItemCollection */
				if (!$shipmentItemCollection = $this->getCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
				}

				/** @var Shipment $shipment */
				if (!$shipment = $shipmentItemCollection->getShipment())
				{
					throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
				}

				if (!$shipment->isSystem())
				{
					if (isset($fields["QUANTITY"]) && (floatval($fields["QUANTITY"]) == 0))
						return $result;
				}

				$r = $this->updateInternal($id, $fields);
				if (!$r->isSuccess())
				{
					$registry = Registry::getInstance(static::getRegistryType());

					/** @var OrderHistory $orderHistory */
					$orderHistory = $registry->getOrderHistoryClassName();
					$orderHistory::addAction(
						'SHIPMENT',
						$order->getId(),
						'SHIPMENT_ITEM_UPDATE_ERROR',
						$id,
						$this,
						array("ERROR" => $r->getErrorMessages())
					);

					$result->addErrors($r->getErrors());
					return $result;
				}

				if ($resultData = $r->getData())
					$result->setData($resultData);
			}

			if ($order && $order->getId() > 0)
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::collectEntityFields('SHIPMENT_ITEM_STORE', $order->getId(), $id);
			}
		}
		else
		{
			$fields['ORDER_DELIVERY_ID'] = $this->getParentShipmentId();
			$this->setFieldNoDemand('ORDER_DELIVERY_ID', $fields['ORDER_DELIVERY_ID']);

			$fields['DATE_INSERT'] = new Main\Type\DateTime();
			$this->setFieldNoDemand('DATE_INSERT', $fields['DATE_INSERT']);

			$fields["BASKET_ID"] = $this->basketItem->getId();
			$this->setFieldNoDemand('BASKET_ID', $fields['BASKET_ID']);

			if (intval($fields['BASKET_ID']) <= 0)
			{

				$error = Loc::getMessage(
					'SALE_SHIPMENT_ITEM_BASKET_ITEM_ID_EMPTY',
					array(
						'#PRODUCT_NAME#' => $this->basketItem->getField('NAME')
					)
				);

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addAction(
					'SHIPMENT',
					$order->getId(),
					'SHIPMENT_ITEM_BASKET_ITEM_EMPTY_ERROR',
					null,
					$this,
					array(
						"ERROR" => $error
					)
				);

				$result->addError(new ResultError($error, 'SALE_SHIPMENT_ITEM_BASKET_ITEM_ID_EMPTY'));

				return $result;
			}

			if (!isset($fields["QUANTITY"]) || (floatval($fields["QUANTITY"]) == 0))
				return $result;

			if (!isset($fields['RESERVED_QUANTITY']))
			{
				$fields['RESERVED_QUANTITY'] = $this->getReservedQuantity() === null ? 0 : $this->getReservedQuantity();
				$this->setFieldNoDemand('RESERVED_QUANTITY', $fields['RESERVED_QUANTITY']);
			}

			$r = $this->addInternal($fields);
			if (!$r->isSuccess())
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addAction(
					'SHIPMENT',
					$order->getId(),
					'SHIPMENT_ITEM_ADD_ERROR',
					null,
					$this,
					array("ERROR" => $r->getErrorMessages())
				);

				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($resultData = $r->getData())
				$result->setData($resultData);

			$id = $r->getId();
			$this->setFieldNoDemand('ID', $id);

			if (!$shipment->isSystem())
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addAction(
					'SHIPMENT',
					$order->getId(),
					'SHIPMENT_ITEM_BASKET_ADDED',
					$shipment->getId(),
					$this->basketItem,
					array(
						'QUANTITY' => $this->getQuantity(),
					)
				);
			}
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		if ($this->isChanged() && self::$eventClassName)
		{
			/** @var Main\Event $event */
			$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues(),
			));
			$event->send();
		}

		$shipmentItemStoreCollection = $this->getShipmentItemStoreCollection();
		$r = $shipmentItemStoreCollection->save();
		if (!$r->isSuccess())
			$result->addErrors($r->getErrors());

		if ($result->isSuccess())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('SHIPMENT_ITEM', $order->getId(), $id);
		}

		return $result;
	}

	private function getParentShipmentId()
	{
		/** @var ShipmentItemCollection $collection */
		$collection = $this->getCollection();
		$shipment = $collection->getShipment();
		return $shipment->getId();
	}

	/**
	 * @param $id
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function loadForShipment($id)
	{
		if (intval($id) <= 0)
			throw new Main\ArgumentNullException("id");

		$items = array();

		$itemDataList = static::getList(
			array(
				'filter' => array('ORDER_DELIVERY_ID' => $id),
				'order' => array('DATE_INSERT' => 'ASC', 'ID' => 'ASC')
			)
		);

		while ($itemData = $itemDataList->fetch())
			$items[] = static::createShipmentItemObject($itemData);

		return $items;
	}

	/**
	 * @param $itemData
	 * @return ShipmentItem
	 */
	private static function createShipmentItemObject(array $itemData = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$shipmentItemClassName = $registry->getShipmentItemClassName();

		return new $shipmentItemClassName($itemData);
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return Internals\CollectableEntity|bool
	 * @throws Main\ObjectNotFoundException
	 */
	protected function loadBasketItem()
	{
		/** @var ShipmentItemCollection $collection */
		$collection = $this->getCollection();

		/** @var Shipment $shipment */
		if (!$shipment = $collection->getShipment())
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

		/** @var Basket $basket */
		if (!$basket = $order->getBasket())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		// [performance]
		// new ShipmentItem binds BasketItem on initialization
		// here we find BasketItem for already saved ShipmentItem (his ID is equal to CODE)
		return $basket->getItemByBasketCode($this->getBasketId());
	}

	/**
	 * @return BasketItem
	 */
	public function getBasketItem()
	{
		if ($this->basketItem == null)
		{
			$this->basketItem = $this->loadBasketItem();
		}

		return $this->basketItem;
	}

	/**
	 * @return ShipmentItemStoreCollection
	 */
	public function getShipmentItemStoreCollection()
	{
		if (empty($this->shipmentItemStoreCollection))
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var ShipmentItemStoreCollection $itemStoreCollectionClassName */
			$itemStoreCollectionClassName = $registry->getShipmentItemStoreCollectionClassName();
			$this->shipmentItemStoreCollection = $itemStoreCollectionClassName::load($this);
		}
		return $this->shipmentItemStoreCollection;
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
			$this->setFieldNoDemand('QUANTITY', $basketItem->getQuantity());
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	protected function calculateDelivery()
	{
		$result = new Result();

		/** @var ShipmentItemCollection $collection */
		$collection = $this->getCollection();
		/** @var Shipment $shipment */
		$shipment = $collection->getShipment();

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();

		/** @var Order $order */
		$order = $shipmentCollection->getOrder();

		if ($order->getId() > 0)
		{
			return $result;
		}

		$deliveryCalculate = $shipment->calculateDelivery();
		if (!$deliveryCalculate->isSuccess())
		{
			$result->addErrors($deliveryCalculate->getErrors());
		}

		if ($deliveryCalculate->getPrice() > 0)
		{
			$shipment->setField('BASE_PRICE_DELIVERY', $deliveryCalculate->getPrice());
		}

		return $result;
	}


	/**
	 * @param string $name
	 * @param null|string $oldValue
	 * @param null|string $value
	 * @throws Main\ObjectNotFoundException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			/** @var ShipmentItemCollection $shipmentItemCollection */
			if (!$shipmentItemCollection = $this->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
			}

			/** @var Shipment $shipment */
			if (!$shipment = $shipmentItemCollection->getShipment())
			{
				throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
			}

			if ($shipment->isSystem())
				return;

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$historyFields = array();

			/** @var BasketItem $basketItem */
			if ($basketItem = $this->getBasketItem())
			{
				$historyFields = array(
					'NAME' => $basketItem->getField('NAME'),
					'PRODUCT_ID' => $basketItem->getField('PRODUCT_ID'),
				);
			}

			/** @var Order $order */
			if (($order = $shipmentCollection->getOrder()) && $order->getId() > 0)
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addField(
					'SHIPMENT_ITEM',
					$order->getId(),
					$name,
					$oldValue,
					$value,
					$this->getId(),
					$this,
					$historyFields
				);
			}


		}
	}


	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (parent::isChanged())
		{
			return true;
		}

		/** @var ShipmentItemStoreCollection $shipmentItemCollection */
		if ($shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
		{
			if ($shipmentItemStoreCollection->isChanged())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function tryReserve()
	{
		return Internals\Catalog\Provider::tryReserveShipmentItem($this);
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function tryUnreserve()
	{
		return Internals\Catalog\Provider::tryUnreserveShipmentItem($this);
	}


	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		if (!$shipment = $shipmentItemCollection->getShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		if (!$basketItem = $this->getBasketItem())
		{
			$result->addError(
				new ResultError(
					Loc::getMessage(
						'SALE_SHIPMENT_ITEM_BASKET_ITEM_NOT_FOUND',
						array(
							'#BASKET_ITEM_ID#' => $this->getBasketId(),
							'#SHIPMENT_ID#' => $shipment->getId(),
							'#SHIPMENT_ITEM_ID#' => $this->getId(),
						)
					), 'SALE_SHIPMENT_ITEM_BASKET_ITEM_NOT_FOUND'
				)
			);

			return $result;
		}

		if ($this->getQuantity() <= 0)
		{
			$result->addError(
				new ResultError(
					Loc::getMessage(
						'SALE_SHIPMENT_ITEM_ERR_QUANTITY_EMPTY',
						array(
							'#BASKET_ITEM_NAME#' => $this->getBasketItem()->getField('NAME'),
						)
					), 'SALE_SHIPMENT_ITEM_ERR_QUANTITY_EMPTY'
				)
			);

			return $result;
		}

		return $result;
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters)
	{
		return Internals\ShipmentItemTable::getList($parameters);
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return Internals\CollectableEntity|ShipmentItem|object
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var ShipmentItem $shipmentItemClone */
		$shipmentItemClone = parent::createClone($cloneEntity);

		/** @var BasketItem $basketItem */
		if ($basketItem = $this->getBasketItem())
		{
			if (!$cloneEntity->contains($basketItem))
			{
				$cloneEntity[$basketItem] = $basketItem->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($basketItem))
			{
				$shipmentItemClone->basketItem = $cloneEntity[$basketItem];
			}
		}

		/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
		if ($shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
		{
			if (!$cloneEntity->contains($shipmentItemStoreCollection))
			{
				$cloneEntity[$shipmentItemStoreCollection] = $shipmentItemStoreCollection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($shipmentItemStoreCollection))
			{
				$shipmentItemClone->shipmentItemStoreCollection = $cloneEntity[$shipmentItemStoreCollection];
			}
		}

		return $shipmentItemClone;
	}


	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		static $className = null;
		$errorsList = static::getAutoFixErrorsList();
		if (is_array($errorsList) && in_array($value, $errorsList))
		{
			if ($className === null)
				$className = static::getClassName();
		}
		else
		{
			/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
			if ($shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
			{
				$className = $shipmentItemStoreCollection->getErrorEntity($value);
			}
		}

		return $className;
	}


	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function canAutoFixError($value)
	{
		$autoFix = false;
		$errorsList = static::getAutoFixErrorsList();
		if (is_array($errorsList) && in_array($value, $errorsList))
		{
			$autoFix = true;
		}
		else
		{
			/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
			if ($shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
			{
				$autoFix = $shipmentItemStoreCollection->canAutoFixError($value);
			}
		}

		return $autoFix;
	}
	
	/**
	 * @return array
	 */
	public function getAutoFixErrorsList()
	{
		return array();
	}

	/**
	 * @param $code
	 *
	 * @return Result
	 */
	public function tryFixError($code)
	{
		return new Result();
	}

	public function canMarked()
	{
		return false;
	}

	public function getMarkField()
	{
		return null;
	}

	/**
	 * @internal
	 */
	public function clearChanged()
	{
		parent::clearChanged();

		if ($shipmentItemStoreCollection = $this->getShipmentItemStoreCollection())
		{
			foreach ($shipmentItemStoreCollection as $shipmentItemStore)
			{
				$shipmentItemStore->clearChanged();
			}
		}
	}

	/**
	 * @return bool
	 * @throws Main\ObjectNotFoundException
	 */
	public function needReserve()
	{
		$changedFields = $this->fields->getChangedValues();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $this->getCollection();
		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		if (!$shipment)
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		return array_key_exists('RESERVED_QUANTITY', $changedFields) || $shipment->getField('RESERVED') == 'Y';
	}

	/**
	 * @return float
	 */
	public function getNeedReserveQuantity()
	{
		if (!$this->needReserve())
			return 0;

		$changedFields = $this->fields->getChangedValues();
		$originalFields = $this->fields->getOriginalValues();

		return $changedFields['RESERVED_QUANTITY'] - $originalFields['RESERVED_QUANTITY'];
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return Internals\ShipmentItemTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\ShipmentItemTable::update($primary, $data);
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\ShipmentItemTable::getMap();
	}

}