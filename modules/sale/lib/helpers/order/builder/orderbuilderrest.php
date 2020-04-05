<?php


namespace Bitrix\Sale\Helpers\Order\Builder;


use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Cashbox\Errors\Error;
use Bitrix\Sale\Delivery\Services\EmptyDeliveryService;
use Bitrix\Sale\Internals\Input\File;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\PropertyValueBase;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Bitrix\Sale\ShipmentItemCollection;

/**
 * Class OrderBuilderRestSale
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
class OrderBuilderRest extends OrderBuilder
{
	public function __construct(SettingsContainer $settings)
	{
		parent::__construct($settings);
		$this->setBasketBuilder(new BasketBuilderRest($this));
	}

	protected function prepareFields(array $fields)
	{
		$fields = array_merge(
			\Bitrix\Sale\Controller\Order::prepareFields($fields),
			\Bitrix\Sale\Controller\PropertyValue::prepareFields($fields['ORDER']),
			\Bitrix\Sale\Controller\BasketItem::prepareFields($fields['ORDER']),
			\Bitrix\Sale\Controller\Payment::prepareFields($fields['ORDER']),
			\Bitrix\Sale\Controller\Shipment::prepareFields($fields['ORDER']),
			\Bitrix\Sale\Controller\TradeBinding::prepareFields($fields['ORDER'])
		);

		return parent::prepareFields($fields);
	}

	protected function createEmptyPayment()
	{
		if($this->getSettingsContainer()->getItemValue('createDefaultPaymentIfNeed'))
		{
			$this->formData["PAYMENT"] = [
				[
					'SUM'=>$this->getOrder()->getPrice(),
					'PAID'=>'N',
					'PAY_SYSTEM_ID'=>Manager::getInnerPaySystemId()
				]
			];

			parent::buildPayments();
		}
		return $this;
	}

	protected function createEmptyShipment()
	{
		if($this->getSettingsContainer()->getItemValue('createDefaultShipmentIfNeed'))
		{
			$this->formData["SHIPMENT"] = [
				[
					'DEDUCTED'=>'N',
					'DELIVERY_ID'=>EmptyDeliveryService::getEmptyDeliveryServiceId()
				]
			];
			parent::buildShipments();
		}
		return $this;
	}

	protected function prepareFieldsStatusId($isNew, $item, $defaultFields)
	{
		$statusId = '';
		if($isNew)
		{
			if (isset($item['STATUS_ID']))
			{
				$statusId = $item['STATUS_ID'];
			}
		}
		else
		{
			$statusId = parent::prepareFieldsStatusId($isNew, $item, $defaultFields);
		}

		return $statusId;
	}

	protected function removeShipmentItems(\Bitrix\Sale\Shipment $shipment, $products, $idsFromForm)
	{
		$result = new Result();

		if(is_array($products))// если передан products, то считаем, что табличная счасть для отгрузки передана
		{
			if($this->getSettingsContainer()->getItemValue('deleteShipmentItemIfNotExists'))
			{
				$shipmentItemCollection = $shipment->getShipmentItemCollection();

				$shipmentItemIds = [];
				foreach($products as $items)
				{
					if(!isset($items['ORDER_DELIVERY_BASKET_ID']))
						continue;

					$shipmentItemProduct = $shipmentItemCollection->getItemById($items['ORDER_DELIVERY_BASKET_ID']);

					if ($shipmentItemProduct == null)
						continue;

					$shipmentItemIds[] = $shipmentItemProduct->getId();
				}

				/** @var ShipmentItem $shipmentItem */
				foreach ($shipmentItemCollection as $shipmentItem)
				{
					if(!in_array($shipmentItem->getId(), $shipmentItemIds))
					{
						$r = $shipmentItem->delete();
						if (!$r->isSuccess())
						{
							$this->errorsContainer->addErrors($r->getErrors());
							$result->addErrors($r->getErrors());
						}
					}
				}
			}
		}
		return $result;
	}

	protected function prepareDataForSetFields(\Bitrix\Sale\Shipment $shipment, $items)
	{
		$result = new Result();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		//only for update shipmentItem, where BASKET_ID is imutable
		if(isset($items['ORDER_DELIVERY_BASKET_ID']) && intval($items['ORDER_DELIVERY_BASKET_ID']) > 0)
		{
			if (!$shipmentItem = $shipmentItemCollection->getItemById($items['ORDER_DELIVERY_BASKET_ID']))
			{
				$result->addError( new Error( 'SALE_ORDER_SHIPMENT_BASKET_ORDER_DELIVERY_ID_NOT_FOUND'));
			}
			else
			{
				/** @var ShipmentItem $shipmentItem */
				$items['BASKET_ID'] = $shipmentItem->getBasketId();
			}
		}
		return $result->setData([$items]);
	}

	protected function modifyQuantityShipmentItem(ShipmentItem $shipmentItem, array $params)
	{
		$r = new Result();

		$basketItem = $shipmentItem->getBasketItem();
		/** @var BasketItemCollection $basket */
		$basket = $basketItem->getCollection();
		/** @var Sale\Order $order */
		$order = $basket->getOrder();

		$allAllowedQuantity = $this->getQuantityBasketItemFromShipmentCollection($basketItem);

		$deltaQuantity = $params['AMOUNT'] - $shipmentItem->getQuantity();

		if($deltaQuantity < 0)
		{
			$this->setQuantityShipmentItem($shipmentItem, 0, abs($deltaQuantity));
		}
		elseif($deltaQuantity > 0)
		{
			if($allAllowedQuantity >= $params['AMOUNT'])
			{
				$systemShipment = $order->getShipmentCollection()->getSystemShipment();
				$systemBasketQuantity = $systemShipment->getBasketItemQuantity($basketItem);

				if($systemBasketQuantity >= $deltaQuantity)
				{
					$this->setQuantityShipmentItem($shipmentItem, $params['AMOUNT'], $shipmentItem->getQuantity());
				}
				else
				{
					$needQuantity = $deltaQuantity - $systemBasketQuantity;

					$r = $this->synchronizeQuantityShipmentItems($shipmentItem, $needQuantity);
					if($r->isSuccess())
					{
						$this->setQuantityShipmentItem($shipmentItem, $params['AMOUNT'], $shipmentItem->getQuantity());
					}
				}
			}
			else
			{
				$r->addError(new \Bitrix\Main\Error('Attempt to increase the quantity of goods in shipment to a quantity that exceeds not shipped in the order.'));
			}
		}
		return $r;
	}

	private function getQuantityBasketItemFromShipmentCollection(BasketItem $basketItem)
	{
		/** @var BasketItemCollection $basket */
		$basket = $basketItem->getCollection();
		/** @var Sale\Order $order */
		$order = $basket->getOrder();

		$allQuantity = 0;
		/** @var Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if($shipment->isShipped())
				continue;

			$allQuantity += $shipment->getBasketItemQuantity($basketItem);
		}

		return $allQuantity;
	}

	protected function setQuantityShipmentItem(ShipmentItem $shipmentItem, $value, $oldValue)
	{
		$deltaQuantity = $value - $oldValue;

		if($shipmentItem->getQuantity() + $deltaQuantity == 0)
		{
			$r = $shipmentItem->delete();
		}
		else
		{
			$r = $shipmentItem->setField(
				"QUANTITY",
				$shipmentItem->getQuantity() + $deltaQuantity
			);
		}

		return $r;
	}

	public function synchronizeQuantityShipmentItems(ShipmentItem $shipmentItem, $needQuantity)
	{
		$result = new Result();

		if(intval($needQuantity) <= 0)
		{
			return $result;
		}

		$basketItem = $shipmentItem->getBasketItem();
		/** @var ShipmentItemCollection $shipmentItemCollection */
		$shipmentItemCollection = $shipmentItem->getCollection();
		$parentEntity = $shipmentItemCollection->getShipment();

		foreach ($parentEntity->getCollection() as $shipment)
		{
			/** @var Shipment $shipment */
			if($parentEntity->getId() == $shipment->getId())
				continue;

			if($shipment->isShipped() || $shipment->isSystem())
				continue;

			$basketQuantity = $shipment->getBasketItemQuantity($basketItem);
			if(empty($basketQuantity))
				continue;

			$shipmentItem = $shipment->getShipmentItemCollection()->getItemByBasketCode($basketItem->getBasketCode());

			if($basketQuantity >= $needQuantity)
			{
				$this->setQuantityShipmentItem($shipmentItem, 0, $needQuantity);
				$needQuantity = 0;
			}
			else
			{
				$this->setQuantityShipmentItem($shipmentItem, 0, $basketQuantity);
				$needQuantity -= $basketQuantity;
			}

			if($needQuantity == 0)
				break;
		}

		if($needQuantity != 0)
			$result->addError(new Error('Not enough unallocated goods in shipments'));

		return $result;
	}

	public function setProperties()
	{
		if(!isset($this->formData["PROPERTIES"]))
		{
			return $this;
		}

		$r = $this->removePropertyValues();
		if($r->isSuccess() == false)
		{
			$this->getErrorsContainer()->addErrors($r->getErrors());
			return $this;
		}

		$this->formData["PROPERTIES"] = File::getPostWithFiles(
			$this->formData["PROPERTIES"],
			$this->settingsContainer->getItemValue('propsFiles')
		);

		$propCollection = $this->order->getPropertyCollection();

		foreach ($this->formData["PROPERTIES"] as $id=>$value)
		{
			if(($propertyValue = $propCollection->getItemByOrderPropertyId($id)))
			{
				$propertyValue->setValue($value);
			}
		}

		return $this;
	}

	public function setUser()
	{
		return $this;
	}

	public function setDiscounts()
	{
		return $this;
	}

	protected function removePropertyValues()
	{
		$result = new Result();

		if($this->getSettingsContainer()->getItemValue('deletePropertyValuesIfNotExists'))
		{
			$propCollection = $this->order->getPropertyCollection();
			/** @var PropertyValueBase $propertyValue */
			foreach($propCollection as $propertyValue)
			{
				if(is_set($this->formData["PROPERTIES"],$propertyValue->getPropertyId()) == false)
				{
					$r = $propertyValue->delete();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}

		return $result;
	}

	protected function getSettableOrderFields()
	{
		/** @var Sale\Order $orderClass */
		$orderClass = $this->getRegistry()->getOrderClassName();

		return $orderClass::getAvailableFields();
	}

	protected function checkDeliveryRestricted($shipment, $deliveryService, $shipmentFields)
	{
		// для rest нет проверки на ограничения. всё что приходит от ключениа считаем корреткным
		return true;
	}

	public function buildEntityShipments(array $fields)
	{
		try{
			$this->initFields($fields)
				->delegate()
				->createOrder()
				->setDiscounts()
				->buildShipments()
				->setDiscounts()
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $this->getOrder();
	}

	public function buildEntityPayments(array $fields)
	{
		try{
			$this->initFields($fields)
				->delegate()
				->createOrder()
				->setDiscounts()
				->buildPayments()
				->setDiscounts()
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $this->getOrder();
	}

	public function buildEntityBasket(array $fields)
	{
		try{
			$this->initFields($fields)
				->delegate()
				->createOrder()
				->setDiscounts() //?
				->buildBasket()
				->setDiscounts() //?
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $this->getOrder();
	}

	public function buildEntityOrder(array $fields)
	{
		try{
			$this->initFields($fields)
				->delegate()
				->createOrder()
				->setDiscounts() //?
				->setFields()
				->setUser()
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $this->getOrder();
	}

	public function buildEntityProperties(array $fields)
	{
		try{
			$this->initFields($fields)
				->delegate()
				->createOrder()
				->setDiscounts() //?
				->setProperties()
				->setDiscounts() //?
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $this->getOrder();
	}
}