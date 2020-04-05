<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Catalog\VatTable;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Internals;
use \Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

/**
 * Class Shipment
 * @package Bitrix\Sale
 */
class Shipment extends Internals\CollectableEntity implements IBusinessValueProvider, \IEntityMarker
{
	/** @var array ShipmentItemCollection */
	protected $shipmentItemCollection;

	/** @var  Delivery\Services\Base */
	protected $deliveryService = null;

	protected $extraServices = null;

	protected $storeId = null;

	/** @var int */
	protected $internalId = 0;

	protected static $idShipment = 0;

	private static $eventClassName = null;

	const ENTITY_MARKER_AUTOFIX_TYPE_ACTION_RESERVE = "RESERVE";
	const ENTITY_MARKER_AUTOFIX_TYPE_ACTION_SHIP = "SHIP";

	protected function __construct(array $fields = array())
	{
		$priceRoundedFields = ['BASE_PRICE_DELIVERY', 'PRICE_DELIVERY', 'DISCOUNT_PRICE'];

		foreach ($priceRoundedFields as $code)
		{
			if (isset($fields[$code]))
			{
				$fields[$code] = PriceMaths::roundPrecision($fields[$code]);
			}
		}

		parent::__construct($fields);
	}

	/**
	 * @return int
	 */
	public function getShipmentCode()
	{
		if ($this->internalId == 0)
		{
			if ($this->getId() > 0)
			{
				$this->internalId = $this->getId();
			}
			else
			{
				static::$idShipment++;
				$this->internalId = static::$idShipment;
			}
		}
		return $this->internalId;
	}


	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array(
			"STATUS_ID",
			"BASE_PRICE_DELIVERY",
			"PRICE_DELIVERY",
			"ALLOW_DELIVERY",
			"DATE_ALLOW_DELIVERY",
			"EMP_ALLOW_DELIVERY_ID",
			"DEDUCTED",
			"DATE_DEDUCTED",
			"EMP_DEDUCTED_ID",
			"REASON_UNDO_DEDUCTED",
			"DELIVERY_ID",
			"DELIVERY_DOC_NUM",
			"DELIVERY_DOC_DATE",
			"TRACKING_NUMBER",
			"XML_ID",
			"PARAMS",
			"DELIVERY_NAME",
			"COMPANY_ID",
			"MARKED",
			"DATE_MARKED",
			"EMP_MARKED_ID",
			"REASON_MARKED",
			"CANCELED",
			"DATE_CANCELED",
			"EMP_CANCELED_ID",
			"RESPONSIBLE_ID",
			"DATE_RESPONSIBLE_ID",
			"EMP_RESPONSIBLE_ID",
			"COMMENTS",
			"CURRENCY",
			"CUSTOM_PRICE_DELIVERY",
			"UPDATED_1C",
			"EXTERNAL_DELIVERY",
			"VERSION_1C","ID_1C",
			"TRACKING_STATUS",
			"TRACKING_LAST_CHECK",
			"TRACKING_DESCRIPTION",
			"ACCOUNT_NUMBER",
			'DISCOUNT_PRICE'
		);
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('BASE_PRICE_DELIVERY', 'DELIVERY_ID');
	}

	/**
	 * @param Delivery\Services\Base $deliveryService
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setDeliveryService(Delivery\Services\Base $deliveryService)
	{
		$this->deliveryService = $deliveryService;
		$resultSetting = $this->setField("DELIVERY_ID", $deliveryService->getId());
		if ($resultSetting->isSuccess())
		{
			$this->setField("DELIVERY_NAME", $deliveryService->getName());
		}
	}

	/**
	 * Use ShipmentCollection::createShipment instead
	 *
	 * @param ShipmentCollection $collection
	 * @param Delivery\Services\Base|null $deliveryService
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public static function create(ShipmentCollection $collection, Delivery\Services\Base $deliveryService = null)
	{
		$fields = array(
			'DATE_INSERT' => new Main\Type\DateTime(),
			'ALLOW_DELIVERY' => 'N',
			'DEDUCTED' => 'N',
			'CUSTOM_PRICE_DELIVERY' => 'N',
			'MARKED' => 'N',
			'CANCELED' => 'N',
			'SYSTEM' => 'N',
			'XML_ID' => static::generateXmlId(),
			'RESERVED' => 'N'
		);

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var DeliveryStatus $deliveryStatusClassName */
		$deliveryStatusClassName = $registry->getDeliveryStatusClassName();
		$fields['STATUS_ID'] = $deliveryStatusClassName::getInitialStatus();

		$shipment = static::createShipmentObject();
		$shipment->setFieldsNoDemand($fields);
		$shipment->setCollection($collection);

		if ($deliveryService !== null)
		{
			$shipment->setDeliveryService($deliveryService);
		}

		return $shipment;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param array $fields
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private static function createShipmentObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$shipmentClassName = $registry->getShipmentClassName();

		return new $shipmentClassName($fields);
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @internal
	 *
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function needReservation()
	{
		$condition = Configuration::getProductReservationCondition();

		if ($condition === Configuration::RESERVE_ON_CREATE)
			return true;

		if ($condition === Configuration::RESERVE_ON_PAY
			|| $condition === Configuration::RESERVE_ON_FULL_PAY)
		{
			/** @var ShipmentCollection $collection */
			if (!$collection = $this->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			/** @var Order $order */
			if (!$order = $collection->getOrder())
			{
				throw new Main\ObjectNotFoundException('Entity "Order" not found');
			}
			if ($condition === Configuration::RESERVE_ON_FULL_PAY)
				return $order->isPaid();

			/** @var PaymentCollection $paymentCollection */
			if (!$paymentCollection = $order->getPaymentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
			}

			return $paymentCollection->hasPaidPayment();
		}

		if ($this->isSystem())
			return false;

		return (($condition === Configuration::RESERVE_ON_ALLOW_DELIVERY) && $this->isAllowDelivery()
			|| ($condition === Configuration::RESERVE_ON_SHIP) && $this->isShipped());
	}

	/**
	 * @param ShipmentItem $sourceItem
	 * @param $quantity
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	private function transferItem2SystemShipment(ShipmentItem $sourceItem, $quantity)
	{
		/** @var ShipmentItemCollection $sourceItemCollection */
		$sourceItemCollection = $sourceItem->getCollection();
		if ($this !== $sourceItemCollection->getShipment())
			throw new Main\ArgumentException("item");

		$quantity = floatval($quantity);

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Shipment $systemShipment */
		if (!$systemShipment = $shipmentCollection->getSystemShipment())
		{
			throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
		}

		/** @var BasketItem $basketItem */
		if (!$basketItem = $sourceItem->getBasketItem())
		{
			throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
		}

		/** @var Basket $basket */
		if (!$basket = $basketItem->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		if (!$order = $basket->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$shipmentItemCode = $sourceItem->getBasketCode();

		if ($quantity === 0)
			return new Result();

		/** @var ShipmentItemCollection $systemShipmentItemCollection */
		if (!$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "System ShipmentItemCollection" not found');
		}

		$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($shipmentItemCode);
		if (is_null($systemShipmentItem))
			$systemShipmentItem = $systemShipmentItemCollection->createItem($basketItem);

		$newSystemShipmentItemQuantity = $systemShipmentItem->getQuantity() + $quantity;
		if ($newSystemShipmentItemQuantity < 0)
		{
			$result = new Result();
			$result->addError(
				new ResultError(
					str_replace(
						array("#NAME#", "#QUANTITY#"),
						array($sourceItem->getBasketItem()->getField("NAME"), abs($quantity)),
						Loc::getMessage('SALE_SHIPMENT_QUANTITY_MISMATCH')
					),
					'SALE_SHIPMENT_QUANTITY_MISMATCH'
				)
			);
			return $result;
		}

		$systemShipmentItem->setFieldNoDemand('QUANTITY', $newSystemShipmentItemQuantity);
		if ($newSystemShipmentItemQuantity == 0)
		{
			$systemShipmentItem->delete();
		}

		$affectedQuantity = 0;

		if ($quantity > 0)  // transfer to system shipment
		{
			if ($sourceItem->getReservedQuantity() > 0)
			{
				$affectedQuantity = $quantity;
				$originalQuantity = $sourceItem->getQuantity() + $quantity;
				if ($sourceItem->getReservedQuantity() < $originalQuantity)
					$affectedQuantity -= $originalQuantity - $sourceItem->getReservedQuantity();
			}
		}
		elseif ($quantity < 0)  // transfer from system shipment
		{
			if ($systemShipmentItem->getReservedQuantity() > 0)
			{
				$affectedQuantity = $quantity;
				if ($systemShipmentItem->getReservedQuantity() < -$affectedQuantity)
					$affectedQuantity = -1 * $systemShipmentItem->getReservedQuantity();
			}
		}

		if ($affectedQuantity != 0)  // if there are reserved items among transfered
		{
			$result = $sourceItem->setField(
				"RESERVED_QUANTITY", $sourceItem->getField('RESERVED_QUANTITY') - $affectedQuantity
			);
//			if (!$result->isSuccess(true))
//				return $result;

			$systemShipmentItem->setFieldNoDemand(
				'RESERVED_QUANTITY',
				$systemShipmentItem->getField('RESERVED_QUANTITY') + $affectedQuantity
			);

			$systemShipment->setFieldNoDemand(
				'RESERVED',
				($systemShipmentItem->getField("RESERVED_QUANTITY") > 0) ? "Y" : "N"
			);

			$shipmentItemForPool = $sourceItem;
			$sourceShipmentItemForPool = $systemShipmentItem;

			if ($quantity > 0)
			{
				$shipmentItemForPool = $systemShipmentItem;
				$sourceShipmentItemForPool = $sourceItem;
			}

			$productId = $basketItem->getProductId();

			$foundItem = false;
			$poolItems = Internals\ItemsPool::get($order->getInternalId(), $productId);
			if (!empty($poolItems))
			{
				$oldItem = null;
				foreach ($poolItems as $poolIndex => $poolItem)
				{
					if ($poolItem->getInternalIndex() == $shipmentItemForPool->getInternalIndex())
					{
						$foundItem = true;
					}
					
					if ($sourceShipmentItemForPool && $poolItem->getInternalIndex() == $sourceShipmentItemForPool->getInternalIndex())
					{
						$reserveQuantity = $sourceShipmentItemForPool->getReservedQuantity();
						if ($reserveQuantity == 0)
						{
							Internals\ItemsPool::delete($order->getInternalId(), $productId, $poolIndex);
						}
					}
				}
			}

			if (!$foundItem)
			{
				Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItemForPool);
			}
		}

		$tryReserveResult = null;

		$context = [
			'USER_ID' => $order->getUserId(),
			'SITE_ID' => $order->getSiteId(),
			'CURRENCY' => $order->getCurrency(),
		];

		if ($quantity > 0)
		{
			if ($systemShipment->needReservation())
			{


				/** @var Result $tryReserveResult */
				$tryReserveResult = Internals\Catalog\Provider::tryReserveShipmentItem($systemShipmentItem, $context);
			}
			else
			{
				/** @var Result $tryReserveResult */
				$tryReserveResult = Internals\Catalog\Provider::tryUnreserveShipmentItem($systemShipmentItem);
			}
		}
		elseif ($quantity < 0)  // transfer from system shipment
		{
			if ($sourceItemCollection->getShipment()->needReservation())
			{
				/** @var Result $tryReserveResult */
				$tryReserveResult = Internals\Catalog\Provider::tryReserveShipmentItem($sourceItem, $context);
			}
		}

		$canReserve = false;

		if ($tryReserveResult === null)
			$canReserve = true;

		if ($tryReserveResult !== null && ($tryReserveResult->isSuccess() && ($tryReserveResultData = $tryReserveResult->getData())))
		{
			if (array_key_exists('CAN_RESERVE', $tryReserveResultData))
			{
				$canReserve = $tryReserveResultData['CAN_RESERVE'];
			}
		}

		if ($systemShipment->needReservation() && $canReserve)
		{
			$order = $this->getParentOrder();
			if ($order &&
				!Internals\ActionEntity::isTypeExists(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY
				)
			)
			{
				Internals\ActionEntity::add(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY,
					array(
						'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
						'PARAMS' => array($systemShipment->getCollection())
					)
				);
			}
		}


		return new Result();
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function updateReservedFlag(Shipment $shipment)
	{
		$shipmentReserved = true;

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $shipment->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$shipmentItemList = $shipmentItemCollection->getShippableItems();

		if ($shipmentItemList->count() == 0)
		{
			$shipmentReserved = false;
		}

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			if ($shipmentItem->getQuantity() - $shipmentItem->getReservedQuantity())
			{
				$shipmentReserved = false;
				break;
			}
		}

		$shipmentReservedValue = $shipmentReserved ? "Y" : "N";
		$currentValue = $shipment->getField('RESERVED');
		if ($shipment->getField('RESERVED') != $shipmentReservedValue)
		{
			$eventManager = Main\EventManager::getInstance();
			$eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_BEFORE_SHIPMENT_RESERVE);
			if (!empty($eventsList))
			{
				/** @var Main\Entity\Event $event */
				$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_SHIPMENT_RESERVE, array(
					'ENTITY' => $shipment,
					'VALUE' => $shipmentReservedValue,
				));

				$event->send();

				if ($event->getResults())
				{
					$result = new Result();
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() == Main\EventResult::ERROR)
						{
							$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_SHIPMENT_RESERVE_ERROR'), 'SALE_EVENT_ON_BEFORE_SHIPMENT_RESERVE_ERROR');

							$eventResultData = $eventResult->getParameters();
							if ($eventResultData)
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
			}

			$shipment->setFieldNoDemand('RESERVED', $shipmentReserved ? "Y" : "N");

			Internals\EventsPool::addEvent('s'.$shipment->getInternalIndex(), EventActions::EVENT_ON_SHIPMENT_RESERVED, array(
				'ENTITY' => $shipment,
				'VALUE' => $shipmentReservedValue,
				'OLD_VALUE' => $currentValue,
			));
		}

		return new Result();
	}

	/**
	 * @param $action
	 * @param ShipmentItem $shipmentItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\SystemException
	 */
	public function onShipmentItemCollectionModify($action, ShipmentItem $shipmentItem, $name = null, $oldValue = null, $value = null)
	{
		if ($action != EventActions::UPDATE)
			return new Result();

		if ($this->isSystem() && ($name != 'RESERVED_QUANTITY'))
			throw new Main\NotSupportedException();

		if ($name === "QUANTITY")
		{
			return $this->transferItem2SystemShipment($shipmentItem, $oldValue - $value);
		}
		elseif ($name === 'RESERVED_QUANTITY')
		{
			$order = $this->getParentOrder();
			if ($order &&
				!Internals\ActionEntity::isTypeExists(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY
				)
			)
			{
				Internals\ActionEntity::add(
					$order->getInternalId(),
					Internals\ActionEntity::ACTION_ENTITY_SHIPMENT_COLLECTION_RESERVED_QUANTITY,
					array(
						'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
						'PARAMS' => array($this->getCollection())
					)
				);
			}
		}

		return new Result();
	}

	/**
	 * @internal
	 *
	 * Deletes shipment without demands.
	 *
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();
		
		$shipmentDataList = static::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID")
			)	
		);

		while ($shipment = $shipmentDataList->fetch())
		{
			$r = static::deleteInternal($shipment['ID']);
			if ($r -> isSuccess())
			{
				Internals\ShipmentExtraServiceTable::deleteByShipmentId($shipment['ID']);
			}
			else
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Deletes shipment
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function delete()
	{
		if ($this->isShipped())
		{
			$result = new Result();
			$result->addError(new ResultError(Loc::getMessage('SALE_SHIPMENT_EXIST_SHIPPED'), 'SALE_SHIPMENT_EXIST_SHIPPED'));
			return $result;
		}

		if ($this->isAllowDelivery())
			$this->disallowDelivery();

		if (!$this->isSystem())
			$this->setField('BASE_PRICE_DELIVERY', 0);

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$this->deleteDeliveryRequest();

		$shipmentItemCollection->clearCollection();
		return parent::delete();
	}

	/**
	 * @return void
	 */
	protected function deleteDeliveryRequest()
	{
		Requests\Manager::onBeforeShipmentDelete($this);
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
		if ($this->isSystem())
		{
			throw new Main\NotSupportedException();
		}

		if ($name === "DELIVERY_ID")
		{
			if (strval($value) != '' && !Delivery\Services\Manager::isServiceExist($value))
			{
				$result = new Result();
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_SHIPMENT_WRONG_DELIVERY_SERVICE'),
						'SALE_SHIPMENT_WRONG_DELIVERY_SERVICE'
					)
				);

				return $result;
			}
		}
		elseif ($name === "REASON_MARKED" && strlen($value) > 255)
		{
			$value = substr($value, 0, 255);
		}

		$priceRoundedFields = array(
			'BASE_PRICE_DELIVERY' => 'BASE_PRICE_DELIVERY',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'DISCOUNT_PRICE' => 'DISCOUNT_PRICE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		return parent::setField($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$priceRoundedFields = array(
			'BASE_PRICE_DELIVERY' => 'BASE_PRICE_DELIVERY',
			'PRICE_DELIVERY' => 'PRICE_DELIVERY',
			'DISCOUNT_PRICE' => 'DISCOUNT_PRICE',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * @param $id
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function loadForOrder($id)
	{
		if (intval($id) <= 0)
			throw new Main\ArgumentNullException("id");

		$shipments = array();

		$shipmentDataList = static::getList(
			array(
				'filter' => array('ORDER_ID' => $id),
				'order' => array('SYSTEM' => 'ASC', 'DATE_INSERT' => 'ASC', 'ID' => 'ASC')
			)
		);
		while ($shipmentData = $shipmentDataList->fetch())
			$shipments[] = static::createShipmentObject($shipmentData);


		return $shipments;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		$registry = Registry::getInstance(static::getRegistryType());

		$id = $this->getId();
		$isNew = ($this->getId() == 0);

		$this->checkRelatedEntities();

		if ($this->isChanged())
		{
			$this->callEventOnBeforeEntitySaved();
		}

		if ($id > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
		}

		if (!$r->isSuccess())
		{
			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addAction(
				'SHIPMENT',
				$this->getParentOrderId(),
				$isNew ? 'SHIPMENT_ADD_ERROR' : 'SHIPMENT_UPDATE_ERROR',
				$isNew ? null : $id,
				$this,
				array("ERROR" => $r->getErrorMessages())
			);

			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($r->getId() > 0)
		{
			$id = $r->getId();
		}

		if ($this->fields->isChanged('ALLOW_DELIVERY')
			&& ($this->getField('ALLOW_DELIVERY') === "Y" || !$isNew)
		)
		{
			$this->callEventOnAllowDelivery();

			/** @var Notify $notifyClassName */
			$notifyClassName = $registry->getNotifyClassName();
			$notifyClassName::callNotify($this, EventActions::EVENT_ON_SHIPMENT_ALLOW_DELIVERY);
		}

		if ($this->fields->isChanged('DEDUCTED')
			&& ($this->getField('DEDUCTED') === "Y" || !$isNew)
		)
		{
			$this->callEventOnDeducted();

			/** @var Notify $notifyClassName */
			$notifyClassName = $registry->getNotifyClassName();
			$notifyClassName::callNotify($this, EventActions::EVENT_ON_SHIPMENT_DEDUCTED);
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		if (!$this->isSystem())
		{
			$this->saveExtraServices();
			$this->saveStoreId();
		}

		if ($this->fields->isChanged("DEDUCTED"))
		{
			Cashbox\Internals\Pool::addDoc($this->getParentOrder()->getInternalId(), $this);
		}

		if ($this->isChanged())
		{
			$this->callEventOnEntitySaved();
		}

		$this->callDelayedEvents();

		$shipmentItemCollection = $this->getShipmentItemCollection();
		$r = $shipmentItemCollection->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if (!$this->isSystem())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('SHIPMENT', $this->getParentOrderId(), $id);
		}

		$this->onAfterSave($isNew);

		$this->clearChanged();

		return $result;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 * @return void
	 */
	private function checkRelatedEntities()
	{
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}
	}

	/**
	 * @return void
	 */
	private function callEventOnAllowDelivery()
	{
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', EventActions::EVENT_ON_SHIPMENT_ALLOW_DELIVERY, array(
			'ENTITY' => $this,
			'VALUES' => $oldEntityValues,
		));

		$event->send();
	}

	/**
	 * @return void
	 */
	private function callEventOnDeducted()
	{
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', EventActions::EVENT_ON_SHIPMENT_DEDUCTED, array(
			'ENTITY' => $this,
			'VALUES' => $oldEntityValues,
		));

		$event->send();
	}


	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	private function add()
	{
		$result = new Result();

		$this->setFieldNoDemand('ORDER_ID', $this->getParentOrderId());

		$fields = $this->fields->getValues();

		$r = static::addInternal($fields);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($resultData = $r->getData())
		{
			$result->setData($resultData);
		}

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$this->setAccountNumber($id);

		if (!$this->isSystem())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addAction(
				'SHIPMENT',
				$this->getParentOrderId(),
				'SHIPMENT_ADDED',
				$id,
				$this
			);
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws \Exception
	 */
	private function update()
	{
		$result = new Result();

		$fields = $this->fields->getChangedValues();
		if ($fields)
		{
			$this->setDeliveryRequestMarker();

			$r = static::updateInternal($this->getId(), $fields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$resultData = $r->getData();
			if ($resultData)
			{
				$result->setData($resultData);
			}

			if ($fields['TRACKING_NUMBER'])
			{
				$this->callEventOnTrackingNumberChange();

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var Notify $notifyClassName */
				$notifyClassName = $registry->getNotifyClassName();
				$notifyClassName::callNotify($this, EventActions::EVENT_ON_SHIPMENT_TRACKING_NUMBER_CHANGE);
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function setDeliveryRequestMarker()
	{
		$order = $this->getParentOrder();

		Requests\Manager::onBeforeShipmentSave($order, $this);
	}

	/**
	 * @return void
	 */
	private function callDelayedEvents()
	{
		$eventList = Internals\EventsPool::getEvents('s'.$this->getInternalIndex());

		if ($eventList)
		{
			foreach ($eventList as $eventName => $eventData)
			{
				$event = new Main\Event('sale', $eventName, $eventData);
				$event->send();

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var Notify $notifyClassName */
				$notifyClassName = $registry->getNotifyClassName();
				$notifyClassName::callNotify($this, $eventName);
			}

			Internals\EventsPool::resetEvents('s'.$this->getInternalIndex());
		}
	}

	/**
	 * @return void
	 */
	private function callEventOnBeforeEntitySaved()
	{
		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'EntitySaved', array(
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues()
		));

		$event->send();
	}

	/**
	 * @return void
	 */
	private function callEventOnEntitySaved()
	{
		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues(),
		));

		$event->send();
	}

	/**
	 * @param $isNew
	 * @return void
	 */
	protected function onAfterSave($isNew)
	{
		return;
	}

	/**
	 * @return void
	 */
	private function callEventOnTrackingNumberChange()
	{
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale',
			EventActions::EVENT_ON_SHIPMENT_TRACKING_NUMBER_CHANGE,
			array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
			)
		);

		$event->send();
	}

	/**
	 * @return bool|int
	 * @throws Main\ObjectNotFoundException
	 */
	public function getParentOrderId()
	{
		$order = $this->getParentOrder();
		if (!$order)
		{
			return false;
		}

		return $order->getId();
	}

	/**
	 * @internal
	 * @return Order
	 * @throws Main\ObjectNotFoundException
	 */
	public function getParentOrder()
	{
		/** @var ShipmentCollection $collection */
		$collection = $this->getCollection();
		if (!$collection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Order $order */
		$order = $collection->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		return $order;
	}

	/**
	 * @return array|ShipmentItemCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function getShipmentItemCollection()
	{
		if (empty($this->shipmentItemCollection))
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var ShipmentItemCollection $itemCollectionClassName */
			$itemCollectionClassName = $registry->getShipmentItemCollectionClassName();
			$this->shipmentItemCollection = $itemCollectionClassName::load($this);
		}

		return $this->shipmentItemCollection;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function markSystem()
	{
		$this->setFieldNoDemand("SYSTEM", 'Y');
	}

	/**
	 * @param ShipmentCollection $collection
	 * @param Delivery\Services\Base|null $deliveryService
	 * @return Shipment
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\SystemException
	 */
	public static function createSystem(ShipmentCollection $collection, Delivery\Services\Base $deliveryService = null)
	{
		$shipment = static::create($collection, $deliveryService);
		$shipment->markSystem();

		if ($deliveryService === null)
		{
			$shipment->setFieldNoDemand('DELIVERY_ID', Delivery\Services\Manager::getEmptyDeliveryServiceId());
		}

		return $shipment;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->getField('ID');
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->getField('PRICE_DELIVERY');
	}

	/**
	 * @return bool
	 */
	public function isCustomPrice()
	{
		return $this->getField('CUSTOM_PRICE_DELIVERY') === "Y";
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->getField('CURRENCY');
	}

	/**
	 * @return int
	 */
	public function getDeliveryId()
	{
		return $this->getField('DELIVERY_ID');
	}

	/**
	 * @return string
	 */
	public function getDeliveryName()
	{
		return $this->getField('DELIVERY_NAME');
	}

	/**
	 * @param $orderId
	 */
	public function setOrderId($orderId)
	{
		$this->setField('ORDER_ID', $orderId);
	}

	/**
	 * @return Delivery\Services\Base
	 */
	public function getDelivery()
	{
		if ($this->deliveryService === null)
		{
			$this->deliveryService = $this->loadDeliveryService();
		}

		return $this->deliveryService;
	}

	/**
	 * @return Delivery\Services\Base
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 */
	protected function loadDeliveryService()
	{
		if ($deliveryId = $this->getDeliveryId())
		{
			return Delivery\Services\Manager::getObjectById($deliveryId);
		}

		return null;
	}


	/**
	 * @return bool
	 */
	public function isSystem()
	{
		return $this->getField('SYSTEM') === 'Y';
	}

	/** @return bool */
	public function isCanceled()
	{
		return $this->getField('CANCELED') === 'Y';
	}

	/**
	 * @return bool
	 */
	public function isShipped()
	{
		return $this->getField('DEDUCTED') === 'Y';
	}

	/**
	 * @return Main\Type\DateTime
	 */
	public function getShippedDate()
	{
		return $this->getField('DATE_DEDUCTED');
	}

	/**
	 * @return int
	 */
	public function getShippedUserId()
	{
		return $this->getField('EMP_DEDUCTED_ID');
	}

	/**
	 * @return string
	 */
	public function getUnshipReason()
	{
		return $this->getField('REASON_UNDO_DEDUCTED');
	}

	/**
	 * @return bool
	 */
	public function isMarked()
	{
		return $this->getField('MARKED') === "Y";
	}

	/**
	 * @return bool
	 */
	public function isReserved()
	{
		return $this->getField('RESERVED') === "Y";
	}

	/**
	 * @return bool
	 */
	public function isAllowDelivery()
	{
		return $this->getField('ALLOW_DELIVERY') === "Y";
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getShipmentItemCollection())
		{
			return true;
		}

		return $shipmentItemCollection->isEmpty();
	}

	/**
	 * @return Main\Type\DateTime
	 */
	public function getAllowDeliveryDate()
	{
		return $this->getField('DATE_ALLOW_DELIVERY');
	}

	/**
	 * @return int
	 */
	public function getAllowDeliveryUserId()
	{
		return $this->getField('EMP_ALLOW_DELIVERY_ID');
	}

	/**
	 * @return int
	 */
	public function getCompanyId()
	{
		return $this->getField('COMPANY_ID');
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function tryReserve()
	{
		return Internals\Catalog\Provider::tryReserveShipment($this);
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function tryUnreserve()
	{
		return Internals\Catalog\Provider::tryUnreserveShipment($this);
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function tryShip()
	{
		$result = new Result();

		/** @var Result $r */
		$r = Internals\Catalog\Provider::tryShipShipment($this);
		if ($r->isSuccess())
		{
			$resultList = $r->getData();

			if (!empty($resultList) && is_array($resultList))
			{
				/** @var Result $resultDat */
				foreach ($resultList as $resultDat)
				{
					if (!$resultDat->isSuccess())
					{
						$result->addErrors( $resultDat->getErrors() );
					}
				}
			}
		}
		else
		{
			$result->addErrors( $r->getErrors() );
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings( $r->getWarnings() );
		}
		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function tryUnship()
	{
		return $this->tryShip();
	}

	/**
	 *
	 */
	public function needShip()
	{
		if ($this->fields->isChanged('DEDUCTED'))
		{
			if ($this->getField('DEDUCTED') === "Y")
			{
				return true;
			}
			elseif ($this->getField('DEDUCTED') === "N" && $this->getId() != 0)
			{
				return false;
			}
		}

		return null;
	}

	/**
	 *
	 */
	public function needDeliver()
	{
		if ($this->fields->isChanged('ALLOW_DELIVERY'))
		{
			return $this->getField('ALLOW_DELIVERY') === "Y";
		}

		return null;
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function deliver()
	{
		$order = $this->getParentOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$result = new Result();

		$context = array(
			'USER_ID' => $order->getUserId(),
			'SITE_ID' => $order->getSiteId(),
		);

		$creator = Internals\ProviderCreator::create($context);

		$shipmentItemCollection = $this->getShipmentItemCollection();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$creator->addShipmentItem($shipmentItem);
		}

		$r = $creator->deliver();
		if ($r->isSuccess())
		{
			$r = $creator->createItemsResultAfterDeliver($r);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (array_key_exists('RESULT_AFTER_DELIVER_LIST', $data))
				{
					$resultList = $data['RESULT_AFTER_DELIVER_LIST'];
				}
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		if (!empty($resultList) && is_array($resultList))
		{
			Recurring::repeat($order, $resultList);
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function allowDelivery()
	{
		return $this->setField('ALLOW_DELIVERY', "Y");
	}

	/**
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function disallowDelivery()
	{
		return $this->setField('ALLOW_DELIVERY', "N");
	}

	/**
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

		$shipmentItemCollection = $this->getShipmentItemCollection();
		$r = $shipmentItemCollection->onBeforeBasketItemDelete($basketItem);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($this->isSystem())
		{
			return $this->syncQuantityAfterModify($basketItem);
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
	 * @throws Main\ArgumentException
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
			if (!$this->isSystem())
			{
				return $result;
			}

			return $this->getShipmentItemCollection()->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		}
		elseif ($action === EventActions::UPDATE)
		{
			if (!$this->isSystem())
			{
				return $result;
			}

			if ($name === "QUANTITY")
			{
				return $this->syncQuantityAfterModify($basketItem, $value, $oldValue);
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name === "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Main\Type\DateTime());
				if ($USER)
				{
					$this->setField('EMP_MARKED_ID', $USER->GetID());
				}
			}
			elseif ($value === "N")
			{
				$this->setField('REASON_MARKED', '');
			}

		}
		elseif ($name === "ALLOW_DELIVERY")
		{
			if ($oldValue != $value)
			{
				$this->setField('DATE_ALLOW_DELIVERY', new Main\Type\DateTime());
				if ($USER)
				{
					$this->setField('EMP_ALLOW_DELIVERY_ID', $USER->GetID());
				}
			}

			if ($oldValue === 'N')
			{
				$shipmentStatus = Main\Config\Option::get('sale', 'shipment_status_on_allow_delivery', '');

				$registry = Registry::getInstance(static::getRegistryType());
				/** @var DeliveryStatus $deliveryStatus */
				$deliveryStatusClassName = $registry->getDeliveryStatusClassName();

				if (strval($shipmentStatus) != '' && $this->getField('STATUS_ID') != $deliveryStatusClassName::getFinalStatus())
				{
					$r = $this->setStatus($shipmentStatus);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}
		elseif ($name === "DEDUCTED")
		{
			if ($oldValue != $value)
			{
				$this->setField('DATE_DEDUCTED', new Main\Type\DateTime());
				if ($USER)
				{
					$this->setField('EMP_DEDUCTED_ID', $USER->GetID());
				}
			}

			if ($oldValue === 'N')
			{
				$shipmentStatus = Main\Config\Option::get('sale', 'shipment_status_on_shipped', '');

				$registry = Registry::getInstance(static::getRegistryType());
				/** @var DeliveryStatus $deliveryStatus */
				$deliveryStatusClassName = $registry->getDeliveryStatusClassName();

				if (strval($shipmentStatus) != '' && $this->getField('STATUS_ID') != $deliveryStatusClassName::getFinalStatus())
				{
					$r = $this->setStatus($shipmentStatus);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}
		elseif ($name === "STATUS_ID")
		{

			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_SHIPMENT_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));
			$event->send();

			Internals\EventsPool::addEvent('s'.$this->getInternalIndex(), EventActions::EVENT_ON_SHIPMENT_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			Internals\EventsPool::addEvent('s'.$this->getInternalIndex(), EventActions::EVENT_ON_SHIPMENT_STATUS_CHANGE_SEND_MAIL, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));
		}


		$r = parent::onFieldModify($name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (($resultData = $r->getData()) && !empty($resultData))
		{
			$result->addData($resultData);
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param BasketItem $basketItem
	 * @param null $value
	 * @param null $oldValue
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function syncQuantityAfterModify(BasketItem $basketItem, $value = null, $oldValue = null)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		$shipmentItem = $shipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode());

		if ($value == 0)
		{
			if ($shipmentItem !== null)
			{
				$shipmentItem->setFieldNoDemand('QUANTITY', 0);
			}

			return $result;
		}

		if ($shipmentItem === null)
		{
			$shipmentItem = $shipmentItemCollection->createItem($basketItem);
		}

		$deltaQuantity = $value - $oldValue;


		/** @var Basket $basket */
		$basket = $basketItem->getCollection();
		if (!$basket)
		{
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		}

		/** @var Order $order */
		$order = $basket->getOrder();
		if (!$order)
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$context = [
			'USER_ID' => $order->getUserId(),
			'SITE_ID' => $order->getSiteId(),
			'CURRENCY' => $order->getCurrency(),
		];

		if ($deltaQuantity > 0)     // plus
		{
			$shipmentItem->setFieldNoDemand(
				"QUANTITY",
				$shipmentItem->getField("QUANTITY") + $deltaQuantity
			);

			if ($this->needReservation())
			{
				/** @var Result $tryReserveResult */
				Internals\Catalog\Provider::tryReserveShipmentItem($shipmentItem, $context);
			}
		}
		else        // minus
		{
			if (floatval($shipmentItem->getField("QUANTITY")) <= 0)
			{
				return new Result();
			}

			if ($value != 0 && roundEx($shipmentItem->getField("QUANTITY"), SALE_VALUE_PRECISION) < roundEx(-$deltaQuantity, SALE_VALUE_PRECISION))
			{
				$result->addError(
					new ResultError(
						str_replace(
							array("#NAME#", "#QUANTITY#", "#DELTA_QUANTITY#"),
							array($basketItem->getField("NAME"), $shipmentItem->getField("QUANTITY"), abs($deltaQuantity)),
							Loc::getMessage('SALE_SHIPMENT_SYSTEM_QUANTITY_ERROR')
						),
						'SALE_SHIPMENT_SYSTEM_QUANTITY_ERROR'
					)
				);
				return $result;
			}

			if ($value > 0)
			{
				$shipmentItem->setFieldNoDemand(
					"QUANTITY",
					$shipmentItem->getField("QUANTITY") + $deltaQuantity
				);
				if ($this->needReservation())
				{
					Internals\Catalog\Provider::tryReserveShipmentItem($shipmentItem, $context);
				}
			}

		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getServiceParams()
	{
		$params = $this->getField('PARAMS');
		return isset($params["SERVICE_PARAMS"]) ? $params["SERVICE_PARAMS"] : array();
	}

	/**
	 * @param array $serviceParams
	 * @throws Main\NotSupportedException
	 */
	public function setServiceParams(array $serviceParams)
	{
		$params = $this->getField('PARAMS');
		$params["SERVICE_PARAMS"] = $serviceParams;
		$this->setField("PARAMS", $params);
	}

	/**
	 * @return null
	 */
	public function getExtraServices()
	{
		if($this->extraServices === null)
		{
			$this->setExtraServices(
				Delivery\ExtraServices\Manager::getValuesForShipment(
					$this->getId(),
					$this->getDeliveryId()
				)
			);
		}

		return $this->extraServices;
	}

	/**
	 * @param array $extraServices
	 */
	public function setExtraServices(array $extraServices)
	{
		$this->extraServices = $extraServices;
	}

	/**
	 * @return Result
	 */
	protected function saveExtraServices()
	{
		return Delivery\ExtraServices\Manager::saveValuesForShipment($this->getId(), $this->getExtraServices());
	}

	/**
	 * @return int
	 */
	public function getStoreId()
	{
		if($this->storeId === null)
		{
			$this->setStoreId(
				Delivery\ExtraServices\Manager::getStoreIdForShipment(
					$this->getId(),
					$this->getDeliveryId()
			));
		}

		return $this->storeId;
	}

	/**
	 * @param $storeId
	 */
	public function setStoreId($storeId)
	{
		$this->storeId = (int)$storeId;
	}

	/**
	 * @return Result
	 */
	protected function saveStoreId()
	{
		return Delivery\ExtraServices\Manager::saveStoreIdForShipment($this->getId(), $this->getDeliveryId(), $this->getStoreId());
	}

	/**
	 * @return float|int
	 * @throws Main\ObjectNotFoundException
	 */
	public function getWeight()
	{
		$weight = 0;
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if ($shipmentItemCollection = $this->getShipmentItemCollection())
		{
			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemCollection->getShippableItems() as $shipmentItem)
			{
				/** @var BasketItem $basketItem */
				if (!$basketItem = $shipmentItem->getBasketItem())
				{
					continue;
				}

				$weight += $basketItem->getWeight() * $shipmentItem->getQuantity();
			}
		}

		return $weight;
	}


	/**
	 * @return Delivery\CalculationResult
	 * @throws Main\NotSupportedException
	 */
	public function calculateDelivery()
	{
		if ($this->isSystem())
		{
			throw new Main\NotSupportedException();
		}

		if ($this->getDeliveryId() == 0)
		{
			return new Delivery\CalculationResult();
		}

		return Delivery\Services\Manager::calculateDeliveryPrice($this);
	}


	/**
	 *
	 */
	public function resetData()
	{
		$this->setFieldNoDemand('PRICE_DELIVERY', 0);

		if ($this->isCustomPrice())
		{
			$basePriceDelivery = $this->getField("BASE_PRICE_DELIVERY");
		}

		$this->setFieldNoDemand('BASE_PRICE_DELIVERY', 0);

		if ($this->isCustomPrice())
		{
			$this->setField('BASE_PRICE_DELIVERY', $basePriceDelivery);
		}

	}

	/**
	 * @param BasketItem $basketItem
	 * @return float|int
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function getBasketItemQuantity(BasketItem $basketItem)
	{
		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $this->getShipmentItemCollection();

		return $shipmentItemCollection->getBasketItemQuantity($basketItem);
	}

	/**
	 * @param string $name
	 * @param null $oldValue
	 * @param null $value
	 * @throws Main\ObjectNotFoundException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0 && !$this->isSystem())
		{
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			/** @var Order $order */
			if (($order = $shipmentCollection->getOrder()) && $order->getId() > 0)
			{
				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addField(
					'SHIPMENT',
					$order->getId(),
					$name,
					$oldValue,
					$value,
					$this->getId(),
					$this
				);
			}
		}
	}

	/**
	 * @param BasketItem $basketItem
	 *
	 * @return bool
	 * @throws Main\ObjectNotFoundException
	 */
	public function isExistBasketItem(BasketItem $basketItem)
	{
		/** @var ShipmentItemCollection $shipmentItemCollection */
		if (!$shipmentItemCollection = $this->getShipmentItemCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		return $shipmentItemCollection->isExistBasketItem($basketItem);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();

		if ($this->getDeliveryId() <= 0)
		{
			$result->addError(new ResultError(Loc::getMessage("SALE_SHIPMENT_DELIVERY_SERVICE_EMPTY"), 'SALE_SHIPMENT_DELIVERY_SERVICE_EMPTY'));
		}

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if ($shipmentItemCollection = $this->getShipmentItemCollection())
		{
			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemCollection as $shipmentItem)
			{
				$r = $shipmentItem->verify();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}
		
		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function setAccountNumber($id)
	{
		$result = new Result();
		$accountNumber = null;
		$id = intval($id);
		if ($id <= 0)
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_PAYMENT_GENERATE_ACCOUNT_NUMBER_ORDER_NUMBER_WRONG_ID'), 'SALE_PAYMENT_GENERATE_ACCOUNT_NUMBER_ORDER_NUMBER_WRONG_ID'));
			return $result;
		}

		$value = Internals\AccountNumberGenerator::generateForShipment($this);

		try
		{
			/** @var \Bitrix\Sale\Result $r */
			$r = static::updateInternal($id, array("ACCOUNT_NUMBER" => $value));
			$res = $r->isSuccess(true);
		}
		catch (Main\DB\SqlQueryException $exception)
		{
			$res = false;
		}

		if ($res)
		{
			if ($this->isSystem())
			{
				$this->setFieldNoDemand('ACCOUNT_NUMBER', $value);
			}
			else
			{
				$r = $this->setField('ACCOUNT_NUMBER', $value);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param $mapping
	 * @return Shipment|null|string
	 */
	public function getBusinessValueProviderInstance($mapping)
	{
		$providerInstance = null;

		if (is_array($mapping))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'SHIPMENT': $providerInstance = $this; break;
				case 'COMPANY' : $providerInstance = $this->getField('COMPANY_ID'); break;
				default:
					/** @var ShipmentCollection $collection */
					if (($collection = $this->getCollection()) && ($order = $collection->getOrder()))
						$providerInstance = $order->getBusinessValueProviderInstance($mapping);
			}
		}

		return $providerInstance;
	}

	/**
	 * @return int|null
	 */
	public function getPersonTypeId()
	{
		/** @var ShipmentCollection $collection */
		return ($collection = $this->getCollection()) && ($order = $collection->getOrder())
			? $order->getPersonTypeId()
			: null;
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters)
	{
		return Internals\ShipmentTable::getList($parameters);
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return Internals\CollectableEntity|Shipment|object
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

		/** @var Shipment $shipmentClone */
		$shipmentClone = parent::createClone($cloneEntity);

		/** @var ShipmentItemCollection $shipmentItemCollection */
		if ($shipmentItemCollection = $this->getShipmentItemCollection())
		{
			if (!$cloneEntity->contains($shipmentItemCollection))
			{
				$cloneEntity[$shipmentItemCollection] = $shipmentItemCollection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($shipmentItemCollection))
			{
				$shipmentClone->shipmentItemCollection = $cloneEntity[$shipmentItemCollection];
			}
		}

		/** @var Delivery\Services\Base $deliveryService */
		if ($deliveryService = $this->getDelivery())
		{
			if (!$cloneEntity->contains($deliveryService))
			{
				$cloneEntity[$deliveryService] = $deliveryService->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($deliveryService))
			{
				$shipmentClone->deliveryService = $cloneEntity[$deliveryService];
			}
		}

		return $shipmentClone;
	}

	/**
	 * @param $status
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	protected function setStatus($status)
	{
		global $USER;

		$result = new Result();

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var DeliveryStatus $deliveryStatus */
		$deliveryStatusClassName = $registry->getDeliveryStatusClassName();

		if ($USER && $USER->isAuthorized())
		{
			$statusesList = $deliveryStatusClassName::getAllowedUserStatuses($USER->getID(), $this->getField('STATUS_ID'));
		}
		else
		{
			$statusesList = $deliveryStatusClassName::getAllStatuses();
		}

		if($this->getField('STATUS_ID') != $status && array_key_exists($status, $statusesList))
		{
			/** @var Result $r */
			$r = $this->setField('STATUS_ID', $status);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		$className = null;
		$errorsList = static::getAutoFixErrorsList();
		if (is_array($errorsList) && in_array($value, $errorsList))
		{
			$className = static::getClassName();
		}
		else
		{
			/** @var ShipmentItemCollection $shipmentItemCollection */
			if ($shipmentItemCollection = $this->getShipmentItemCollection())
			{
				$className = $shipmentItemCollection->getErrorEntity($value);
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
			/** @var ShipmentItemCollection $shipmentItemCollection */
			if ($shipmentItemCollection = $this->getShipmentItemCollection())
			{
				$autoFix = $shipmentItemCollection->canAutoFixError($value);
			}
		}
		
		return $autoFix;
	}

	/**
	 * @return array
	 */
	public function getAutoFixErrorsList()
	{
		return array_keys(static::getAutoFixRules());
	}

	/**
	 * @param $code
	 *
	 * @return Result
	 */
	public function tryFixError($code)
	{
		$result = new Result();

		$method = static::getFixMethod($code);
		$r = call_user_func_array($method, array($this));
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		return $result;
	}

	/**
	 * @param $code
	 * @return mixed|null
	 */
	protected static function getFixMethod($code)
	{
		$codeList = static::getAutoFixRules();

		if (!empty($codeList[$code]))
		{
			return $codeList[$code];
		}
		return null;
	}

	/**
	 * @param Shipment $entity
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function fixReserveErrors(Shipment $entity)
	{
		$result = new Result();
		/** @var ShipmentCollection $collection */
		if (!$collection = $entity->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		if (!$order = $collection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}
		
		$r = $entity->tryReserve();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		return $result;
	}

	/**
	 * @param Shipment $entity
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function fixShipErrors(Shipment $entity)
	{
		$result = new Result();
		/** @var ShipmentCollection $collection */
		if (!$collection = $entity->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		if (!$order = $collection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$r = $entity->setField('DEDUCTED', 'Y');
		if (!$r->isSuccess())
		{
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		$r = $entity->tryShip();
		if (!$r->isSuccess())
		{
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getAutoFixRules()
	{
		return array(
			'PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY' => array('\Bitrix\Sale\Shipment', "fixReserveErrors"),
			'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY' => array('\Bitrix\Sale\Shipment', "fixReserveErrors"),
			'PROVIDER_UNRESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY' => array('\Bitrix\Sale\Shipment', "fixReserveErrors"),
			'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_QUANTITY_NOT_ENOUGH' => array('\Bitrix\Sale\Shipment', "fixReserveErrors"),

			'SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY' => array('\Bitrix\Sale\Shipment', "fixShipErrors"),
			'SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY' => array('\Bitrix\Sale\Shipment', "fixShipErrors"),
			'DDCT_DEDUCTION_QUANTITY_STORE_ERROR' => array('\Bitrix\Sale\Shipment', "fixShipErrors"),
			'SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH' => array('\Bitrix\Sale\Shipment', "fixShipErrors"),
			'DDCT_DEDUCTION_QUANTITY_ERROR' => array('\Bitrix\Sale\Shipment', "fixShipErrors"),
		);
	}

	/**
	 * @return bool
	 */
	public function canMarked()
	{
		return true;
	}

	/**
	 * @return string
	 */
	public function getMarkField()
	{
		return 'MARKED';
	}

	/**
	 * @internal
	 */
	public function clearChanged()
	{
		parent::clearChanged();

		if ($shipmentItemCollection = $this->getShipmentItemCollection())
		{
			/** @var ShipmentItem $shipmentItem */
			foreach ($shipmentItemCollection as $shipmentItem)
			{
				$shipmentItem->clearChanged();
			}
		}
	}

	/**
	 * @return float|int
	 * @throws Main\LoaderException
	 */
	public function getVatRate()
	{
		$vatRate = 0;

		$service = $this->getDelivery();
		if ($service)
		{
			if (!Main\Loader::includeModule('catalog'))
				return $vatRate;

			$vatId = $service->getVatId();
			if ($vatId <= 0)
				return $vatRate;

			$dbRes = VatTable::getById($vatId);
			$vatInfo = $dbRes->fetch();
			if ($vatInfo)
			{
				$vatRate = $vatInfo['RATE'] / 100;
			}
		}

		return $vatRate;
	}

	/**
	 * @return float
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	public function getVatSum()
	{
		$vatRate = $this->getVatRate();
		$price = $this->getPrice() * $vatRate / (1 + $vatRate);
		
		return PriceMaths::roundPrecision($price);
	}

	/**
	 * @param array $data
	 * @return Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		return Internals\ShipmentTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\ShipmentTable::update($primary, $data);
	}

	/**
	 * @param $primary
	 * @return Entity\DeleteResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	protected static function deleteInternal($primary)
	{
		return Internals\ShipmentTable::deleteWithItems($primary);
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\ShipmentTable::getMap();
	}

	/**
	 * @return null
	 */
	public static function getUfId()
	{
		return Internals\ShipmentTable::getUfId();
	}

	/**
	 * @param $value
	 * @param bool $custom
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setBasePriceDelivery($value, $custom = false)
	{
		$result = new Result();

		$r = $this->setField('CUSTOM_PRICE_DELIVERY', ($custom ? 'Y' : 'N'));
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$r = $this->setField('BASE_PRICE_DELIVERY', $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}
}

