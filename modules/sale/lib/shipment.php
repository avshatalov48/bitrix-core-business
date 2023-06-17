<?php

namespace Bitrix\Sale;

use Bitrix\Catalog\VatTable;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Internals;
use \Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Reservation\Configuration\ReserveCondition;

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
	protected $service = null;

	protected $extraServices = null;

	protected $storeId = null;

	/** @var int */
	protected $internalId = 0;

	protected static $idShipment = 0;

	/** @var ShipmentPropertyValueCollection */
	protected $propertyCollection;

	/** @var bool $isNew */
	protected $isNew = true;

	/**
	 * @return string|void
	 */
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_SHIPMENT;
	}

	/**
	 * @return int
	 */
	public function getShipmentCode()
	{
		if ($this->internalId === 0)
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
		return [
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
			"WEIGHT",
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
		];
	}

	/**
	 * @return array
	 */
	public static function getCustomizableFields() : array
	{
		return ['PRICE_DELIVERY' => 'PRICE_DELIVERY', 'WEIGHT' => 'WEIGHT'];
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function onBeforeSetFields(array $values)
	{
		if (isset($values['DEDUCTED']))
		{
			if ($this->getField('DEDUCTED') === 'Y')
			{
				if ($values['DEDUCTED'] === 'N')
				{
					$values = ['DEDUCTED' => $values['DEDUCTED']] + $values;
				}
			}
			else
			{
				if ($values['DEDUCTED'] === 'Y')
				{
					// move to the end of array
					unset($values['DEDUCTED']);
					$values['DEDUCTED'] = 'Y';
				}
			}
		}

		return $values;
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('BASE_PRICE_DELIVERY', 'DELIVERY_ID');
	}

	/**
	 * @param Delivery\Services\Base $service
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setDeliveryService(Delivery\Services\Base $service)
	{
		$this->service = $service;

		$result = $this->setField("DELIVERY_ID", $service->getId());
		if ($result->isSuccess())
		{
			$this->setField("DELIVERY_NAME", $service->getName());
		}
	}

	/**
	 * @param ShipmentCollection $collection
	 * @param Delivery\Services\Base|null $service
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function create(ShipmentCollection $collection, Delivery\Services\Base $service = null)
	{
		$emptyService = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
		$fields = [
			'DATE_INSERT' => new Main\Type\DateTime(),
			'DELIVERY_ID' => $emptyService['ID'],
			'DELIVERY_NAME' => $emptyService['NAME'],
			'ALLOW_DELIVERY' => 'N',
			'DEDUCTED' => 'N',
			'CUSTOM_PRICE_DELIVERY' => 'N',
			'MARKED' => 'N',
			'CANCELED' => 'N',
			'SYSTEM' => 'N',
			'XML_ID' => static::generateXmlId(),
			'RESERVED' => 'N'
		];

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var DeliveryStatus $deliveryStatusClassName */
		$deliveryStatusClassName = $registry->getDeliveryStatusClassName();
		$fields['STATUS_ID'] = $deliveryStatusClassName::getInitialStatus();

		$shipment = static::createShipmentObject();
		$shipment->setFieldsNoDemand($fields);
		$shipment->setCollection($collection);

		if ($service !== null)
		{
			$shipment->setDeliveryService($service);
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

		if ($condition === ReserveCondition::ON_CREATE)
		{
			return true;
		}

		if ($condition === ReserveCondition::ON_PAY
			|| $condition === ReserveCondition::ON_FULL_PAY)
		{
			$order = $this->getOrder();
			if ($condition === ReserveCondition::ON_FULL_PAY)
			{
				return $order->isPaid();
			}

			return $order->getPaymentCollection()->hasPaidPayment();
		}

		if ($this->isSystem())
		{
			return false;
		}

		return
			(
				$condition === ReserveCondition::ON_ALLOW_DELIVERY
				&& $this->isAllowDelivery()
			)
			|| (
				$condition === ReserveCondition::ON_SHIP
				&& $this->isShipped()
			)
		;
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
		$sourceItemCollection = $sourceItem->getCollection();
		if ($this !== $sourceItemCollection->getShipment())
		{
			throw new Main\ArgumentException("item");
		}

		$quantity = floatval($quantity);

		/** @var Shipment $systemShipment */
		$systemShipment = $this->getCollection()->getSystemShipment();

		/** @var BasketItem $basketItem */
		$basketItem = $sourceItem->getBasketItem();

		/** @var Order $order */
		$order = $basketItem->getCollection()->getOrder();

		$shipmentItemCode = $sourceItem->getBasketCode();

		if ($quantity === 0)
		{
			return new Result();
		}

		/** @var ShipmentItemCollection $systemShipmentItemCollection */
		$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection();

		$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($shipmentItemCode);
		if (is_null($systemShipmentItem))
		{
			$systemShipmentItem = $systemShipmentItemCollection->createItem($basketItem);
		}

		$newSystemShipmentItemQuantity = $systemShipmentItem->getQuantity() + $quantity;
		if ($newSystemShipmentItemQuantity < 0)
		{
			$result = new Result();
			$result->addError(
				new ResultError(
					str_replace(
						["#NAME#", "#QUANTITY#"],
						[$sourceItem->getBasketItem()->getField("NAME"), abs($quantity)],
						Loc::getMessage('SALE_SHIPMENT_QUANTITY_MISMATCH')
					),
					'SALE_SHIPMENT_QUANTITY_MISMATCH'
				)
			);
			return $result;
		}

		$systemShipmentItem->setFieldNoDemand('QUANTITY', $newSystemShipmentItemQuantity);
		if ($newSystemShipmentItemQuantity <= 1e-10)
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
				{
					$affectedQuantity -= $originalQuantity - $sourceItem->getReservedQuantity();
				}
			}
		}
		elseif ($quantity < 0)  // transfer from system shipment
		{
			if ($systemShipmentItem->getReservedQuantity() > 0)
			{
				$affectedQuantity = $quantity;
				if ($systemShipmentItem->getReservedQuantity() < -$affectedQuantity)
				{
					$affectedQuantity = -1 * $systemShipmentItem->getReservedQuantity();
				}
			}
		}

		if ($affectedQuantity != 0)  // if there are reserved items among transferred
		{
			$sourceItem->getFields()->set(
				'RESERVED_QUANTITY',
				$sourceItem->getField('RESERVED_QUANTITY') - $affectedQuantity
			);

			$systemShipmentItem->getFields()->set(
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
				foreach ($poolItems as $poolIndex => $poolItem)
				{
					if ($poolItem->getInternalIndex() === $shipmentItemForPool->getInternalIndex())
					{
						$foundItem = true;
					}

					if (
						$sourceShipmentItemForPool
						&& $poolItem instanceof ShipmentItem
						&& $poolItem->getInternalIndex() === $sourceShipmentItemForPool->getInternalIndex()
					)
					{
						$reserveQuantity = $sourceShipmentItemForPool->getReservedQuantity();
						if (abs($reserveQuantity) <= 1e-6)
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

		if ($quantity > 0)
		{
			if (Configuration::isEnableAutomaticReservation())
			{
				if ($systemShipment->needReservation())
				{
					$tryReserveResult = Internals\Catalog\Provider::tryReserveShipmentItem($systemShipmentItem);
				}
				else
				{
					$tryReserveResult = Internals\Catalog\Provider::tryUnreserveShipmentItem($systemShipmentItem);
				}
			}
		}
		elseif ($quantity < 0)  // transfer from system shipment
		{
			if (
				Configuration::isEnableAutomaticReservation()
				&& $sourceItemCollection->getShipment()->needReservation()
			)
			{
				$tryReserveResult = Internals\Catalog\Provider::tryReserveShipmentItem($sourceItem);
			}
		}

		$canReserve = false;

		if ($tryReserveResult === null)
		{
			$canReserve = true;
		}

		if ($tryReserveResult !== null && ($tryReserveResult->isSuccess() && ($tryReserveResultData = $tryReserveResult->getData())))
		{
			if (array_key_exists('CAN_RESERVE', $tryReserveResultData))
			{
				$canReserve = $tryReserveResultData['CAN_RESERVE'];
			}
		}

		if (
			Configuration::isEnableAutomaticReservation()
			&& $systemShipment->needReservation()
			&& $canReserve
		)
		{
			$order = $this->getOrder();
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
					[
						'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
						'PARAMS' => [$systemShipment->getCollection()]
					]
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

		$shipmentItemList = $shipment->getShipmentItemCollection()->getShippableItems();

		if ($shipmentItemList->count() === 0)
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
				$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_SHIPMENT_RESERVE, [
					'ENTITY' => $shipment,
					'VALUE' => $shipmentReservedValue,
				]);

				$event->send();

				if ($event->getResults())
				{
					$result = new Result();
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() === Main\EventResult::ERROR)
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

			Internals\EventsPool::addEvent('s'.$shipment->getInternalIndex(), EventActions::EVENT_ON_SHIPMENT_RESERVED, [
				'ENTITY' => $shipment,
				'VALUE' => $shipmentReservedValue,
				'OLD_VALUE' => $currentValue,
			]);
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
		{
			return new Result();
		}

		if ($this->isSystem()
			&& $name != 'RESERVED_QUANTITY'
		)
		{
			throw new Main\NotSupportedException(Loc::getMessage('SALE_SHIPMENT_SYSTEM_SHIPMENT_CHANGE'));
		}

		if ($name === "QUANTITY")
		{
			$result = $this->transferItem2SystemShipment($shipmentItem, $oldValue - $value);

			if (!$this->isMarkedFieldCustom('WEIGHT'))
			{
				$this->setField(
					'WEIGHT',
					$this->getShipmentItemCollection()->getWeight()
				);
			}

			return $result;
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
					[
						'METHOD' => 'Bitrix\Sale\ShipmentCollection::updateReservedFlag',
						'PARAMS' => [$this->getCollection()]
					]
				);
			}
		}

		return new Result();
	}

	/**
	 * @param $orderId
	 * @return Result
	 * @throws Main\ArgumentException
	 * @internal
	 *
	 * Deletes shipment without demands.
	 *
	 */
	public static function deleteNoDemand($orderId)
	{
		$result = new Result();

		$shipmentDataList = static::getList(
			[
				"filter" => ["=ORDER_ID" => $orderId],
				"select" => ["ID"]
			]
		);

		while ($shipment = $shipmentDataList->fetch())
		{
			$res = static::deleteInternal($shipment['ID']);

			if ($res -> isSuccess())
			{
				Internals\ShipmentExtraServiceTable::deleteByShipmentId($shipment['ID']);
			}
			else
			{
				$result->addErrors($res->getErrors());
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
			return $result->addError(
				new ResultError(
					Loc::getMessage('SALE_SHIPMENT_EXIST_SHIPPED'),
					'SALE_SHIPMENT_EXIST_SHIPPED'
				)
			);
		}

		if (!$this->isSystem())
		{
			$this->setField('BASE_PRICE_DELIVERY', 0);

			if ($this->getFields()->isMarkedCustom('PRICE_DELIVERY'))
			{
				$this->setField('PRICE_DELIVERY', 0);
			}

			$this->disallowDelivery();
		}

		$this->getPropertyCollection()->deleteNoDemand($this->getId());
		$this->deleteDeliveryRequest();

		$this->getShipmentItemCollection()->clearCollection();

		return parent::delete();
	}

	/**
	 * @return void
	 */
	protected function deleteDeliveryRequest()
	{
		Requests\Manager::onBeforeShipmentDelete($this);
	}

	protected function normalizeValue($name, $value)
	{
		if ($this->isPriceField($name))
		{
			$value = PriceMaths::roundPrecision($value);
		}
		elseif ($name === 'REASON_MARKED')
		{
			$value = (string)$value;
			if (mb_strlen($value) > 255)
			{
				$value = mb_substr($value, 0, 255);
			}
		}

		return parent::normalizeValue($name, $value);
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

		if ($name === 'CUSTOM_PRICE_DELIVERY')
		{
			if ($value === 'Y')
			{
				$this->markFieldCustom('PRICE_DELIVERY');
			}
			else
			{
				$this->unmarkFieldCustom('PRICE_DELIVERY');
			}
		}

		return parent::setField($name, $value);
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function checkValueBeforeSet($name, $value)
	{
		$result = parent::checkValueBeforeSet($name, $value);

		if ($name === "DELIVERY_ID")
		{
			if (intval($value) > 0 && !Delivery\Services\Manager::isServiceExist($value))
			{
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_SHIPMENT_WRONG_DELIVERY_SERVICE'),
						'SALE_SHIPMENT_WRONG_DELIVERY_SERVICE'
					)
				);
			}
		}
		elseif ($name === 'ACCOUNT_NUMBER')
		{
			$dbRes = static::getList([
				'select' => ['ID'],
				'filter' => ['=ACCOUNT_NUMBER' => $value]
			]);

			if ($dbRes->fetch())
			{
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_SHIPMENT_ACCOUNT_NUMBER_EXISTS')
					)
				);
			}
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public function setFieldNoDemand($name, $value)
	{
		if ($name === 'CUSTOM_PRICE_DELIVERY')
		{
			if ($value === 'Y')
			{
				$this->markFieldCustom('PRICE_DELIVERY');
			}
			else
			{
				$this->unmarkFieldCustom('PRICE_DELIVERY');
			}
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
		{
			throw new Main\ArgumentNullException("id");
		}

		$shipments = [];

		$shipmentDataList = static::getList(static::getParametersForLoad($id));
		while ($shipmentData = $shipmentDataList->fetch())
		{
			$shipments[] = static::createShipmentObject($shipmentData);
		}


		return $shipments;
	}

	protected static function getParametersForLoad($id) : array
	{
		return [
			'filter' => [
				'ORDER_ID' => $id
			],
			'order' => [
				'SYSTEM' => 'ASC',
				'DATE_INSERT' => 'ASC',
				'ID' => 'ASC'
			]
		];
	}

	/**
	 * @internal
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$this->checkCallingContext();

		$result = new Result();

		$id = $this->getId();
		$this->isNew = ($this->getId() === 0);

		$this->callEventOnBeforeEntitySaved();

		if ($id > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();

			if ($r->getId() > 0)
			{
				$id = $r->getId();
			}
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($id > 0)
		{
			$result->setId($id);

			$controller = Internals\CustomFieldsController::getInstance();
			$controller->save($this);
		}

		if (!$this->isSystem())
		{
			$this->saveExtraServices();
			$this->saveStoreId();
		}

		$this->callEventOnEntitySaved();

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

		/** @var ShipmentPropertyValueCollection $propertyCollection */
		$propertyCollection = $this->getPropertyCollection();

		/** @var Result $res */
		$res = $propertyCollection->save();
		if (!$res->isSuccess())
		{
			$result->addWarnings($res->getErrors());
		}

		$this->onAfterSave($this->isNew);

		$this->isNew = false;

		return $result;
	}

	/**
	 * @return void
	 */
	private function checkCallingContext()
	{
		$order = $this->getOrder();

		if (!$order->isSaveRunning())
		{
			trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity", E_USER_WARNING);
		}
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

		$registry = Registry::getInstance(static::getRegistryType());

		$this->setFieldNoDemand('ORDER_ID', $this->getParentOrderId());

		$r = static::addInternal($this->getFields()->getValues());
		if (!$r->isSuccess())
		{
			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();

			$orderHistory::addAction(
				'SHIPMENT',
				$this->getParentOrderId(),
				'SHIPMENT_ADD_ERROR',
				null,
				$this,
				["ERROR" => $r->getErrorMessages()]
			);

			$result->addErrors($r->getErrors());
			return $result;
		}

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		$this->setAccountNumber($id);

		if (!$this->isSystem())
		{
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

		$registry = Registry::getInstance(static::getRegistryType());

		$this->setDeliveryRequestMarker();

		$r = static::updateInternal($this->getId(), $this->getFields()->getChangedValues());
		if (!$r->isSuccess())
		{
			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();

			$orderHistory::addAction(
				'SHIPMENT',
				$this->getParentOrderId(),
				'SHIPMENT_UPDATE_ERROR',
				$this->getId(),
				$this,
				["ERROR" => $r->getErrorMessages()]
			);

			$result->addErrors($r->getErrors());
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
	 *
	 * @throws Main\ArgumentException
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
		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBeforeSaleShipmentEntitySaved', [
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues()
		]);

		$event->send();
	}

	/**
	 * @return void
	 */
	private function callEventOnEntitySaved()
	{
		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'OnSaleShipmentEntitySaved', [
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
			'IS_NEW' => $this->isNew,
		]);

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
	 * @return bool|int
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
	 * @return Order|null
	 */
	public function getOrder()
	{
		return $this->getCollection()->getOrder();
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
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function markSystem()
	{
		$this->setFieldNoDemand("SYSTEM", 'Y');
	}

	/**
	 * @internal
	 *
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
	 * @return float
	 */
	public function getPrice()
	{
		return (float)$this->getField('PRICE_DELIVERY');
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function isCustomPrice()
	{
		return $this->isMarkedFieldCustom('PRICE_DELIVERY');
	}

	protected function isPriceField(string $name) : bool
	{
		return
			$name === 'BASE_PRICE_DELIVERY'
			|| $name === 'PRICE_DELIVERY'
			|| $name === 'DISCOUNT_PRICE'
		;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return (string)$this->getField('CURRENCY');
	}

	/**
	 * @return int
	 */
	public function getDeliveryId()
	{
		return (int)$this->getField('DELIVERY_ID');
	}

	/**
	 * @return string
	 */
	public function getDeliveryName()
	{
		return (string)$this->getField('DELIVERY_NAME');
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
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 */
	public function getDelivery()
	{
		if ($this->service === null)
		{
			$this->service = $this->loadDeliveryService();
		}

		return $this->service;
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
		return (string)$this->getField('REASON_UNDO_DEDUCTED');
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function isReserved()
	{
		/** @var ShipmentItem $shipmentItem */
		foreach ($this->getShipmentItemCollection() as $shipmentItem)
		{
			if ($shipmentItem->getReservedQuantity() !== $shipmentItem->getQuantity())
			{
				return false;
			}
		}

		return true;
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
		return $this->getShipmentItemCollection()->isEmpty();
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
		return (int)$this->getField('EMP_ALLOW_DELIVERY_ID');
	}

	/**
	 * @return int
	 */
	public function getCompanyId()
	{
		return (int)$this->getField('COMPANY_ID');
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
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function deliver()
	{
		$result = Internals\Catalog\Provider::deliver($this);
		if ($result->isSuccess())
		{
			Recurring::repeat($this->getOrder(), $result->getData());
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public function allowDelivery()
	{
		return $this->setField('ALLOW_DELIVERY', "Y");
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
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
			return $result->addErrors($r->getErrors());
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
			if ($name === "QUANTITY")
			{
				if ($this->isSystem())
				{
					return $this->syncQuantityAfterModify($basketItem, $value, $oldValue);
				}

				/** @var ShipmentItemCollection $shipmentItemCollection */
				$shipmentItemCollection = $this->getShipmentItemCollection();

				$r = $shipmentItemCollection->onBasketModify($action, $basketItem, $name, $oldValue, $value);
				if ($r->isSuccess())
				{
					if (!$this->isCustomPrice())
					{
						$deliveryCalculate = $this->calculateDelivery();
						if ($deliveryCalculate->isSuccess())
						{
							$this->setField('BASE_PRICE_DELIVERY', $deliveryCalculate->getPrice());
						}
						else
						{
							$result->addWarnings($deliveryCalculate->getErrors());
						}
					}
				}
				else
				{
					$result->addErrors($r->getErrors());
				}
			}
			elseif ($name === 'WEIGHT')
			{
				if (!$this->isMarkedFieldCustom('WEIGHT'))
				{
					if ($this->getShipmentItemCollection()->isExistBasketItem($basketItem))
					{
						$this->setField('WEIGHT', $this->getShipmentItemCollection()->getWeight());
					}
				}
			}
			elseif ($name === 'PRICE')
			{
				if (!$this->isCustomPrice())
				{
					if ($this->getShipmentItemCollection()->isExistBasketItem($basketItem))
					{
						$r = $this->calculateDelivery();
						if ($r->isSuccess())
						{
							$this->setField('BASE_PRICE_DELIVERY', $r->getPrice());
						}
						else
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name === 'DELIVERY_ID')
		{
			if (
				$value > 0
				&& (
					$this->service === null
					|| $this->service->getId() !== (int)$value
				)
			)
			{
				$service = Delivery\Services\Manager::getObjectById($value);
				if ($service)
				{
					$this->service = $service;

					$this->setField('DELIVERY_NAME', $this->service->getName());
				}
			}

			$this->getPropertyCollection()->refreshRelated();
		}
		elseif ($name === "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Main\Type\DateTime());

				if (is_object($USER))
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
			$this->setField('DATE_ALLOW_DELIVERY', new Main\Type\DateTime());

			if (is_object($USER))
			{
				$this->setField('EMP_ALLOW_DELIVERY_ID', $USER->GetID());
			}

			if ($oldValue === 'N')
			{
				$shipmentStatus = Main\Config\Option::get('sale', 'shipment_status_on_allow_delivery', '');

				$registry = Registry::getInstance(static::getRegistryType());
				/** @var DeliveryStatus $deliveryStatus */
				$deliveryStatusClassName = $registry->getDeliveryStatusClassName();

				if (
					$shipmentStatus !== ''
					&& $this->getField('STATUS_ID') != $deliveryStatusClassName::getFinalStatus()
				)
				{
					$r = $this->setStatus($shipmentStatus);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}

			Internals\EventsPool::addEvent(
				's'.$this->getInternalIndex(),
				EventActions::EVENT_ON_SHIPMENT_ALLOW_DELIVERY,
				[
					'ENTITY' => $this,
					'VALUES' => $this->fields->getOriginalValues()
				]
			);
		}
		elseif ($name === "DEDUCTED")
		{
			$this->setField('DATE_DEDUCTED', new Main\Type\DateTime());

			if (is_object($USER))
			{
				$this->setField('EMP_DEDUCTED_ID', $USER->GetID());
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

			if ($value === 'Y')
			{
				/** @var ShipmentItem $shipmentItem */
				foreach ($this->getShipmentItemCollection() as $shipmentItem)
				{
					$r = $shipmentItem->checkMarkingCodeOnDeducted();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}

			Internals\EventsPool::addEvent(
				's'.$this->getInternalIndex(),
				EventActions::EVENT_ON_SHIPMENT_DEDUCTED,
				[
					'ENTITY' => $this,
					'VALUES' => $this->fields->getOriginalValues()
				]
			);

			Cashbox\Internals\Pool::addDoc($this->getOrder()->getInternalId(), $this);
		}
		elseif ($name === "STATUS_ID")
		{
			$event = new Main\Event(
				'sale',
				EventActions::EVENT_ON_BEFORE_SHIPMENT_STATUS_CHANGE,
				[
					'ENTITY' => $this,
					'VALUE' => $value,
					'OLD_VALUE' => $oldValue,
				]
			);
			$event->send();

			Internals\EventsPool::addEvent(
				's'.$this->getInternalIndex(),
				EventActions::EVENT_ON_SHIPMENT_STATUS_CHANGE,
				[
					'ENTITY' => $this,
					'VALUE' => $value,
					'OLD_VALUE' => $oldValue,
				]
			);

			Internals\EventsPool::addEvent(
				's'.$this->getInternalIndex(),
				EventActions::EVENT_ON_SHIPMENT_STATUS_CHANGE_SEND_MAIL,
				[
					'ENTITY' => $this,
					'VALUE' => $value,
					'OLD_VALUE' => $oldValue,
				]
			);
		}
		elseif ($name === 'RESPONSIBLE_ID')
		{
			$this->setField('DATE_RESPONSIBLE_ID', new Main\Type\DateTime());
		}
		elseif ($name === 'TRACKING_NUMBER')
		{
			if ($value)
			{
				Internals\EventsPool::addEvent(
					's'.$this->getInternalIndex(),
					EventActions::EVENT_ON_SHIPMENT_TRACKING_NUMBER_CHANGE,
					[
						'ENTITY' => $this,
						'VALUES' => $this->getFields()->getOriginalValues(),
					]
				);
			}
		}

		$r = parent::onFieldModify($name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		if (
			$name === 'BASE_PRICE_DELIVERY'
			&& !$this->isMarkedFieldCustom('PRICE_DELIVERY')
		)
		{
			$value -= $this->getField('DISCOUNT_PRICE');

			$r = $this->setField('PRICE_DELIVERY', $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$result->addData($r->getData());

		if ($result->isSuccess())
		{
			$this->setFieldNoDemand('DATE_UPDATE', new Main\Type\DateTime());
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param BasketItem $basketItem
	 * @param null $value
	 * @param null $oldValue
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function syncQuantityAfterModify(BasketItem $basketItem, $value = null, $oldValue = null)
	{
		$result = new Result();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $this->getShipmentItemCollection();

		$shipmentItem = $shipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode());

		if ($value === 0)
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

		if ($deltaQuantity > 0)     // plus
		{
			$shipmentItem->setFieldNoDemand(
				"QUANTITY",
				$shipmentItem->getField("QUANTITY") + $deltaQuantity
			);

			if (
				Configuration::isEnableAutomaticReservation()
				&& $this->needReservation()
			)
			{
				Internals\Catalog\Provider::tryReserveShipmentItem($shipmentItem);
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

				if (
					Configuration::isEnableAutomaticReservation()
					&& $this->needReservation()
				)
				{
					Internals\Catalog\Provider::tryReserveShipmentItem($shipmentItem);
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
	 * @return Delivery\ExtraServices\Base[]
	 */
	public function getExtraServicesObjects()
	{
		return Delivery\ExtraServices\Manager::getObjectsForShipment(
			$this->getId(),
			$this->getDeliveryId(),
			$this->getCurrency()
		);
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
	 * @return float
	 */
	public function getWeight() : float
	{
		return (float)$this->getField('WEIGHT');
	}

	/**
	 * @param float $weight
	 * @return string|null
	 */
	public function setWeight(float $weight)
	{
		return $this->setField('WEIGHT', $weight);
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

		if ($this->getDeliveryId() === 0)
		{
			return new Delivery\CalculationResult();
		}

		return Delivery\Services\Manager::calculateDeliveryPrice($this);
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public function resetData()
	{
		if (!$this->isCustomPrice())
		{
			$this->setField('BASE_PRICE_DELIVERY', 0);
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
			$order = $this->getOrder();

			if ($order && $order->getId() > 0)
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
			$result->addError(
				new ResultError(
					Loc::getMessage("SALE_SHIPMENT_DELIVERY_SERVICE_EMPTY"),
					'SALE_SHIPMENT_DELIVERY_SERVICE_EMPTY'
				)
			);
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
			$this->setFieldNoDemand('ACCOUNT_NUMBER', $value);
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

		if (is_array($mapping) && isset($mapping['PROVIDER_KEY']))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'SHIPMENT': $providerInstance = $this; break;
				case 'COMPANY' : $providerInstance = $this->getField('COMPANY_ID'); break;
				default:
					$order = $this->getOrder();
					if ($order)
					{
						$providerInstance = $order->getBusinessValueProviderInstance($mapping);
					}
			}
		}

		return $providerInstance;
	}

	/**
	 * @return int|null
	 */
	public function getPersonTypeId()
	{
		$order = $this->getOrder();
		if ($order)
		{
			return $order->getPersonTypeId();
		}

		return null;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
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
	 * @throws Main\SystemException
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

		/** @var Delivery\Services\Base $service */
		if ($service = $this->getDelivery())
		{
			if (!$cloneEntity->contains($service))
			{
				$cloneEntity[$service] = $service->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($service))
			{
				$shipmentClone->service = $cloneEntity[$service];
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

		if (is_object($USER)  && $USER->isAuthorized())
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
		return [
			'PROVIDER_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY' => ['\Bitrix\Sale\Shipment', "fixReserveErrors"],
			'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY' => ['\Bitrix\Sale\Shipment', "fixReserveErrors"],
			'PROVIDER_UNRESERVE_SHIPMENT_ITEM_WRONG_AVAILABLE_QUANTITY' => ['\Bitrix\Sale\Shipment', "fixReserveErrors"],
			'SALE_PROVIDER_RESERVE_SHIPMENT_ITEM_QUANTITY_NOT_ENOUGH' => ['\Bitrix\Sale\Shipment', "fixReserveErrors"],

			'SALE_PROVIDER_SHIPMENT_SHIPPED_LESS_QUANTITY' => ['\Bitrix\Sale\Shipment', "fixShipErrors"],
			'SALE_PROVIDER_SHIPMENT_SHIPPED_MORE_QUANTITY' => ['\Bitrix\Sale\Shipment', "fixShipErrors"],
			'DDCT_DEDUCTION_QUANTITY_STORE_ERROR' => ['\Bitrix\Sale\Shipment', "fixShipErrors"],
			'SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH' => ['\Bitrix\Sale\Shipment', "fixShipErrors"],
			'DDCT_DEDUCTION_QUANTITY_ERROR' => ['\Bitrix\Sale\Shipment', "fixShipErrors"],
		];
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
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function isChanged()
	{
		if (parent::isChanged())
		{
			return true;
		}

		return $this->getShipmentItemCollection()->isChanged();
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getVatRate()
	{
		$vatRate = 0;

		$service = $this->getDelivery();
		if ($service)
		{
			if (!Main\Loader::includeModule('catalog'))
			{
				return $vatRate;
			}

			$vatId = $service->getVatId();
			if ($vatId <= 0)
			{
				return $vatRate;
			}

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

		if ($custom === true)
		{
			$this->markFieldCustom('PRICE_DELIVERY');
		}

		$r = $this->setField('BASE_PRICE_DELIVERY', $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SaleShipment';
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function toArray() : array
	{
		$result = parent::toArray();

		$result['ITEMS'] = $this->getShipmentItemCollection()->toArray();

		return $result;
	}

	public function getPropertyCollection(): ShipmentPropertyValueCollection
	{
		if(empty($this->propertyCollection))
		{
			$this->propertyCollection = $this->loadPropertyCollection();
		}

		return $this->propertyCollection;
	}

	public function loadPropertyCollection(): ShipmentPropertyValueCollection
	{
		$registry = Registry::getInstance(static::getRegistryType());
		/** @var ShipmentPropertyValueCollection $propertyCollectionClassName */
		$propertyCollectionClassName = $registry->getShipmentPropertyValueCollectionClassName();

		return $propertyCollectionClassName::load($this);
	}

	/**
	 * @deprecated Use getOrder instead
	 *
	 * @return Order|null
	 */
	public function getParentOrder()
	{
		return $this->getOrder();
	}
}

