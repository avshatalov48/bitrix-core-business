<?php
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * @method ShipmentItemStoreCollection getCollection()
 */
class ShipmentItemStore
	extends Internals\CollectableEntity
	implements \IEntityMarker
{
	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return [
			'ORDER_DELIVERY_BASKET_ID', 'STORE_ID', 'QUANTITY', 
			'BARCODE', 'BASKET_ID', 'MARKING_CODE',
		];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return [];
	}

	/**
	 * @param array $itemData
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private static function createShipmentItemStoreObject(array $itemData = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$shipmentItemStoreClassName = $registry->getShipmentItemStoreClassName();

		return new $shipmentItemStoreClassName($itemData);
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
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_SHIPMENT_ITEM_STORE;
	}

	/**
	 * @param ShipmentItemStoreCollection $collection
	 * @param BasketItem $basketItem
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public static function create(ShipmentItemStoreCollection $collection, BasketItem $basketItem)
	{
		if (!$basketItem->isReservableItem())
		{
			throw new Main\SystemException('Basket item is not available for reservation');
		}

		$shipmentItemStore = static::createShipmentItemStoreObject();
		$shipmentItemStore->setCollection($collection);

		$shipmentItem = $collection->getShipmentItem();
		if ($shipmentItem)
		{
			$fields = array(
				'ORDER_DELIVERY_BASKET_ID' => $collection->getShipmentItem()->getId(),
				'BASKET_ID' => $shipmentItem->getBasketItem()->getId(),
			);

			$shipmentItemStore->setFieldsNoDemand($fields);
		}

		return $shipmentItemStore;

	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		if (is_object($USER) && $USER->isAuthorized())
		{
			$this->setFieldNoDemand('MODIFIED_BY', $USER->GetID());
		}

		$this->setFieldNoDemand('DATE_MODIFY', new Main\Type\DateTime());

		$result = parent::onFieldModify($name, $oldValue, $value);
		if (!$result->isSuccess())
		{
			return $result;
		}

		if (
			$name === 'STORE_ID'
			&& $this->needMoveReserve()
		)
		{
			$shipmentItem = $this->getCollection()->getShipmentItem();
			$basketItem = $shipmentItem->getBasketItem();

			/** @var ReserveQuantityCollection $reserveCollection */
			$reserveCollection = $basketItem->getReserveQuantityCollection();

			if (
				$reserveCollection
				&& $shipmentItem->getReservedQuantity() > 0
				&& $this->getQuantity() > 0
			)
			{
				$storeIdFrom = (int)$oldValue ?: Configuration::getDefaultStoreId();
				$storeIdTo = (int)$value;

				if (
					$storeIdFrom === 0
					|| $storeIdTo === 0
					|| $storeIdFrom === $storeIdTo
				)
				{
					return $result;
				}

				$reserveTo = $reserveFrom = null;

				/** @var ReserveQuantity $reserve */
				foreach ($reserveCollection as $reserve)
				{
					if ($reserve->getStoreId() === $storeIdFrom)
					{
						$reserveFrom = $reserve;
					}
					elseif ($reserve->getStoreId() === $storeIdTo)
					{
						$reserveTo = $reserve;
					}

					if ($reserveTo && $reserveFrom)
					{
						break;
					}
				}

				if ($reserveFrom)
				{
					$settableQuantity = $reserveFrom->getQuantity() - $this->getQuantity();
					if ($settableQuantity > 0)
					{
						$reserveFrom->setField('QUANTITY', $settableQuantity);
					}
					else
					{
						$reserveFrom->delete();
					}
				}

				if (!$reserveTo)
				{
					$reserveTo = $reserveCollection->create();
					$reserveTo->setStoreId($storeIdTo);
				}

				$reserveTo->setQuantity($reserveTo->getQuantity() + $this->getQuantity());
			}
		}
		elseif (
			$name === 'QUANTITY'
			&& $this->needMoveReserve()
		)
		{
			$shipmentItem = $this->getCollection()->getShipmentItem();
			$basketItem = $shipmentItem->getBasketItem();

			/** @var ReserveQuantityCollection $reserveCollection */
			$reserveCollection = $basketItem->getReserveQuantityCollection();

			if ($reserveCollection && $shipmentItem->getReservedQuantity() > 0)
			{
				if ($value > $oldValue)
				{
					$storeIdFrom = Configuration::getDefaultStoreId();
					$storeIdTo = $this->getStoreId();
				}
				else
				{
					$storeIdTo = Configuration::getDefaultStoreId();
					$storeIdFrom = $this->getStoreId();
				}

				if (
					$storeIdFrom === 0
					|| $storeIdTo === 0
					|| $storeIdFrom === $storeIdTo
				)
				{
					return $result;
				}

				$reserveTo = $reserveFrom = null;

				/** @var ReserveQuantity $reserve */
				foreach ($reserveCollection as $reserve)
				{
					if ($reserve->getStoreId() === $storeIdFrom)
					{
						$reserveFrom = $reserve;
					}
					elseif ($reserve->getStoreId() === $storeIdTo)
					{
						$reserveTo = $reserve;
					}

					if ($reserveTo && $reserveFrom)
					{
						break;
					}
				}

				$delta = abs($oldValue - $value);

				if ($reserveFrom)
				{
					$settableQuantity = $reserveFrom->getQuantity() - $delta;
					if ($settableQuantity > 0)
					{
						$reserveFrom->setField('QUANTITY', $settableQuantity);
					}
					else
					{
						$reserveFrom->delete();
					}
				}

				if (!$reserveTo)
				{
					$reserveTo = $reserveCollection->create();
					$reserveTo->setStoreId($storeIdTo);
				}

				$reserveTo->setQuantity($reserveTo->getQuantity() + $delta);
			}
		}

		return $result;
	}

	protected function needMoveReserve() : bool
	{
		return true;
	}

	/**
	 * Deletes shipment item
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function delete()
	{
		$result = new Result();

		$oldEntityValues = $this->fields->getOriginalValues();

		$event = new Main\Event('sale', "OnBeforeSaleShipmentItemStoreEntityDeleted", [
			'ENTITY' => $this,
			'VALUES' => $oldEntityValues,
		]);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				$eventResultData = $eventResult->getParameters();
				if ($eventResultData instanceof ResultError)
				{
					return $result->addError($eventResultData);
				}
			}
		}

		$r = parent::delete();
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$shipmentItem = $this->getCollection()->getShipmentItem();
		$basketItem = $shipmentItem->getBasketItem();

		/** @var ReserveQuantityCollection $reserveCollection */
		$reserveCollection = $basketItem->getReserveQuantityCollection();

		if ($reserveCollection && $shipmentItem->getReservedQuantity() > 0)
		{
			$storeIdTo = Configuration::getDefaultStoreId();
			$storeIdFrom = $this->getStoreId();

			if ($storeIdFrom === $storeIdTo)
			{
				return $result;
			}

			$reserveTo = $reserveFrom = null;

			/** @var ReserveQuantity $reserve */
			foreach ($reserveCollection as $reserve)
			{
				if ($reserve->getStoreId() === $storeIdFrom)
				{
					$reserveFrom = $reserve;
				}
				elseif ($reserve->getStoreId() === $storeIdTo)
				{
					$reserveTo = $reserve;
				}

				if ($reserveTo && $reserveFrom)
				{
					break;
				}
			}

			$delta = $this->getQuantity();

			if ($reserveFrom)
			{
				$settableQuantity = $reserveFrom->getQuantity() - $delta;
				if ($settableQuantity > 0)
				{
					$reserveFrom->setField('QUANTITY', $settableQuantity);
				}
				else
				{
					$reserveFrom->delete();
				}
			}

			if (!$reserveTo)
			{
				$reserveTo = $reserveCollection->create();
				$reserveTo->setStoreId($storeIdTo);
			}

			$reserveTo->setQuantity($reserveTo->getQuantity() + $delta);
		}

		$event = new Main\Event('sale', "OnSaleShipmentItemStoreEntityDeleted", array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		));
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				$eventResultData = $eventResult->getParameters();
				if ($eventResultData instanceof ResultError)
				{
					$result->addError($eventResultData);
				}
			}
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function getBasketId() : int
	{
		return (int)$this->getField('BASKET_ID');
	}

	/**
	 * @return float
	 */
	public function getQuantity() : float
	{
		return (float)$this->getField('QUANTITY');
	}

	/**
	 * @return int
	 */
	public function getStoreId() : int
	{
		return (int)$this->getField('STORE_ID');
	}

	/**
	 * @return string
	 */
	public function getBarcode() : string
	{
		return (string)$this->getField('BARCODE');
	}

	/**
	 * @return string
	 */
	public function getMarkingCode() : string
	{
		return (string)$this->getField('MARKING_CODE');
	}

	/**
	 * @param $id
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadForShipmentItem($id)
	{
		if ((int)$id <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$items = [];

		$itemDataList = static::getList([
			'filter' => ['=ORDER_DELIVERY_BASKET_ID' => $id],
			'order' => ['DATE_CREATE' => 'ASC', 'ID' => 'ASC']
		]);

		while ($itemData = $itemDataList->fetch())
		{
			$items[] = static::createShipmentItemStoreObject($itemData);
		}

		return $items;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function verify()
	{
		$result = new Result();

		if ($this->getBarcode() === "")
		{
			/** @var ShipmentItemStoreCollection $itemStoreCollection */
			$itemStoreCollection = $this->getCollection();

			/** @var BasketItem $itemCollection */
			$basketItem = $itemStoreCollection->getShipmentItem()->getBasketItem();

			/** @var Shipment $shipent */
			$shipment = $itemStoreCollection->getShipmentItem()->getCollection()->getShipment();

			if ($basketItem->isBarcodeMulti() && $shipment->isShipped())
			{
				$result->addError(
					new ResultError(
						Loc::getMessage(
							'SHIPMENT_ITEM_STORE_BARCODE_MULTI_EMPTY',
							[
								'#PRODUCT_NAME#' => $basketItem->getField('NAME'),
								'#STORE_ID#' => $this->getStoreId(),
							]
						),
						'SHIPMENT_ITEM_STORE_BARCODE_MULTI_EMPTY'
					)
				);
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function checkCallingContext()
	{
		/** @var ShipmentItemStoreCollection $itemStoreCollection */
		$itemStoreCollection = $this->getCollection();

		/** @var ShipmentItemCollection $itemCollection */
		$itemCollection = $itemStoreCollection->getShipmentItem()->getCollection();

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $itemCollection->getShipment()->getCollection();

		if (!$shipmentCollection->getOrder()->isSaveRunning())
		{
			trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity", E_USER_WARNING);
		}
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 */
	public function save()
	{
		$result = new Result();

		$this->checkCallingContext();

		if (!$this->isChanged() || $this->getQuantity() === 0)
		{
			return $result;
		}

		$this->callEventOnBeforeItemStoreEntitySaved();

		$id = $this->getId();

		if ($id > 0)
		{
			$r = $this->updateInternal($id, $this->getFields()->getChangedValues());
		}
		else
		{
			/** @var ShipmentItemStoreCollection $itemStoreCollection */
			$itemStoreCollection = $this->getCollection();

			if (!$this->getField("ORDER_DELIVERY_BASKET_ID"))
			{
				$this->setFieldNoDemand('ORDER_DELIVERY_BASKET_ID', $itemStoreCollection->getShipmentItem()->getId());
			}

			if (!$this->getField("BASKET_ID"))
			{
				$this->setFieldNoDemand('BASKET_ID', $itemStoreCollection->getShipmentItem()->getBasketItem()->getId());
			}

			$this->setFieldNoDemand('DATE_CREATE', new Main\Type\DateTime());

			$r = $this->addInternal($this->getFields()->getValues());
			if ($r->isSuccess())
			{
				$id = $r->getId();

				$this->setFieldNoDemand('ID', $id);
			}
		}

		if (!$r->isSuccess())
		{
			$this->addErrorMessagesToHistory($r->getErrorMessages());

			$result->addErrors($r->getErrors());

			return $result;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		$this->callEventOnItemStoreEntitySaved();

		return $result;
	}

	/**
	 * @return void
	 */
	protected function callEventOnBeforeItemStoreEntitySaved()
	{
		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBeforeSaleShipmentItemStoreEntitySaved', [
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues()
		]);

		$event->send();
	}

	/**
	 * @return void
	 */
	protected function callEventOnItemStoreEntitySaved()
	{
		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'OnSaleShipmentItemStoreEntitySaved', [
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		]);

		$event->send();
	}

	/**
	 * @param $errors
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function addErrorMessagesToHistory($errors)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var ShipmentItemStoreCollection $shipmentItemStoreCollection */
		$shipmentItemStoreCollection = $this->getCollection();

		/** @var \Bitrix\Crm\Order\ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItemStoreCollection->getShipmentItem()->getCollection();

		$order = $shipmentItemCollection->getShipment()->getOrder();

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::addAction(
			'SHIPMENT',
			$order->getId(),
			($this->getId() > 0) ? 'SHIPMENT_ITEM_STORE_UPDATE_ERROR' : 'SHIPMENT_ITEM_STORE_ADD_ERROR',
			($this->getId() > 0) ? $this->getId() : null,
			$this,
			[
				"ERROR" => $errors
			]
		);
	}

	/**
	 * @param string $name
	 * @param null $oldValue
	 * @param null $value
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() <= 0)
		{
			return;
		}

		/** @var ShipmentItem $shipmentItem */
		$shipmentItem = $this->getCollection()->getShipmentItem();

		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();

		$shipmentItemCollection->getShipment()->getOrder();

		$shipment = $shipmentItemCollection->getShipment();
		if ($shipment->isSystem())
		{
			return;
		}

		$basketItem = $shipmentItem->getBasketItem();

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::addField(
			'SHIPMENT_ITEM_STORE',
			$shipment->getOrder()->getId(),
			$name,
			$oldValue,
			$value,
			$this->getId(),
			$this,
			[
				'NAME' => $basketItem->getField('NAME'),
				'PRODUCT_ID' => $basketItem->getField('PRODUCT_ID'),
			]
		);
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result|Internals\EO_ShipmentItemStore_Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		return Sale\Internals\ShipmentItemStoreTable::getList($parameters);
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
			{
				$className = static::getClassName();
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
		$errorsList = static::getAutoFixErrorsList();
		return (is_array($errorsList) && in_array($value, $errorsList));
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

	/**
	 * @return bool
	 */
	public function canMarked()
	{
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getMarkField()
	{
		return null;
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return Internals\ShipmentItemStoreTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\ShipmentItemStoreTable::update($primary, $data);
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\ShipmentItemStoreTable::getMap();
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SaleShipmentItemStore';
	}
}