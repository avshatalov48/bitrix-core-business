<?php

namespace Bitrix\Sale;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class OrderFacade
 * @package Bitrix\Sale
 */
class OrderFacade
{
	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function payOrder($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::load($id);
		if (!$order)
		{
			$result->addError(new Error(Loc::getMessage('SALE_GROUP_ACTION_ERR_ORDER_NOT_FOUND')));
			return $result;
		}

		$collection = $order->getPaymentCollection();
		/** @var Payment $payment */
		foreach ($collection as $payment)
		{
			if (!$payment->isPaid())
			{
				$r = $payment->setPaid('Y');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
		}

		if (!$order->isPaid())
		{
			$payment = static::createFinalPayment($order);
			if ($payment === null)
			{
				$result->addError(
					new Error(
						Loc::getMessage('SALE_GROUP_ACTION_ERR_PAYMENT_CREATE')
					)
				);
				return $result;
			}
		}

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public static function cancelPayOrder($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::load($id);
		if (!$order)
		{
			$result->addError(new Error(Loc::getMessage('SALE_GROUP_ACTION_ERR_ORDER_NOT_FOUND')));
			return $result;
		}

		$collection = $order->getPaymentCollection();
		/** @var Payment $payment */
		foreach ($collection as $payment)
		{
			if ($payment->isPaid())
			{
				$r = $payment->setPaid('N');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
		}

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deductOrder($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::load($id);
		if (!$order)
		{
			$result->addError(new Error(Loc::getMessage('SALE_GROUP_ACTION_ERR_ORDER_NOT_FOUND')));
			return $result;
		}

		$collection = $order->getShipmentCollection()->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			if (!$shipment->isShipped())
			{
				$r = $shipment->setField('DEDUCTED', 'Y');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
		}

		if (!$order->isShipped())
		{
			$shipment = static::createFinalShipment($order);
			if ($shipment === null)
			{
				$result->addError(
					new Error(
						Loc::getMessage('SALE_GROUP_ACTION_ERR_SHIPMENT_CREATE')
					)
				);
				return $result;
			}
		}

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function cancelDeductOrder($id)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::load($id);
		if (!$order)
		{
			$result->addError(new Error(Loc::getMessage('SALE_GROUP_ACTION_ERR_ORDER_NOT_FOUND')));
			return $result;
		}

		$collection = $order->getShipmentCollection()->getNotSystemItems();

		/** @var Shipment $shipment */
		foreach ($collection as $shipment)
		{
			if ($shipment->isShipped())
			{
				$r = $shipment->setField('DEDUCTED', 'N');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
		}

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param Order $order
	 * @return Payment|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function createFinalPayment(Order $order)
	{
		$price = $order->getPrice();
		$paidSum = $order->getPaymentCollection()->getPaidSum();

		$payment = $order->getPaymentCollection()->createItem();
		$payment->setField('SUM', $price - $paidSum);

		$paySystemId = static::getPaySystemId($payment);
		if ($paySystemId === 0)
		{
			return null;
		}

		$service = Sale\PaySystem\Manager::getObjectById($paySystemId);
		$payment->setPaySystemService($service);

		$payment->setPaid('Y');

		return $payment;
	}

	/**
	 * @param Order $order
	 * @return Shipment
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function createFinalShipment(Order $order)
	{
		$collection = $order->getShipmentCollection();

		$deliveryId = static::getDeliveryId();
		if ((int)$deliveryId == 0)
		{
			return null;
		}

		$delivery = Sale\Delivery\Services\Manager::getObjectById($deliveryId);
		$shipment = $collection->createItem($delivery);

		$itemCollection = $shipment->getShipmentItemCollection();

		$system = $collection->getSystemShipment();
		$systemItemCollection = $system->getShipmentItemCollection();

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($systemItemCollection as $shipmentItem)
		{
			$item = $itemCollection->createItem($shipmentItem->getBasketItem());
			$item->setQuantity($shipmentItem->getQuantity());
		}

		$shipment->setField('DEDUCTED', 'Y');

		return $shipment;
	}

	/**
	 * @param Payment $payment
	 * @return array|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getPaySystemId(Payment $payment)
	{
		$paySystemList = Sale\PaySystem\Manager::getListWithRestrictions($payment);
		foreach ($paySystemList as $paySystem)
		{
			if ((int)$paySystem['ID'] === (int)Sale\PaySystem\Manager::getInnerPaySystemId())
			{
				continue;
			}

			return $paySystem['ID'];
		}

		return 0;
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getDeliveryId()
	{
		return Sale\Delivery\Services\Manager::getEmptyDeliveryServiceId();
	}
}