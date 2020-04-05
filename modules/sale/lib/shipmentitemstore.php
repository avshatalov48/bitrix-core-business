<?php
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class ShipmentItemStore
 * @package Bitrix\Sale
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
		return array("ORDER_DELIVERY_BASKET_ID", "STORE_ID", "QUANTITY", "BARCODE", 'BASKET_ID', 'MARKING_CODE');
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array();
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function create(ShipmentItemStoreCollection $collection, BasketItem $basketItem)
	{
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

		return parent::onFieldModify($name, $oldValue, $value);
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

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBeforeSaleShipmentItemStoreEntityDeleted", [
			'ENTITY' => $this,
			'VALUES' => $oldEntityValues,
		]);
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(
						Loc::getMessage('SALE_EVENT_ON_BEFORE_SALESHIPMENTITEMSTORE_ENTITY_DELETED_ERROR'),
						'SALE_EVENT_ON_BEFORE_SALESHIPMENTITEMSTORE_ENTITY_DELETED_ERROR'
					);
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

		$r = parent::delete();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnSaleShipmentItemStoreEntityDeleted", array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(
						Loc::getMessage('SALE_EVENT_ON_SALESHIPMENTITEMSTORE_ENTITY_DELETED_ERROR'),
						'SALE_EVENT_ON_SALESHIPMENTITEMSTORE_ENTITY_DELETED_ERROR'
					);
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

			if ($basketItem->isBarcodeMulti())
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