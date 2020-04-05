<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Crm\Order\Shipment;

final class Director
{
	public function createOrder(OrderBuilder $builder, array $fields)
	{
		try{
			$builder->build($fields);
		}
		catch(BuildingException $e)
		{
			return null;
		}

		return $builder->getOrder();
	}

	/**
	 * @param OrderBuilder $builder
	 * @param array $shipmentData
	 * @return Shipment
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getUpdatedShipment(OrderBuilder $builder, array $shipmentData)
	{
		try{
			$builder->initFields(array(
				'ID' => $shipmentData['ORDER_ID'],
				'SITE_ID' => $shipmentData['SITE_ID'],
				'SHIPMENT' => array($shipmentData)
			))
				->delegate()
				->createOrder()
				->setDiscounts() //?
				->buildShipments()
				->setDiscounts() //?
				->finalActions();
		}
		catch(BuildingException $e)
		{
			return null;
		}

		$order = $builder->getOrder();
		$collection = $order->getShipmentCollection();

		if((int)$shipmentData['ID'] > 0)
		{
			return $collection->getItemById($shipmentData['ID']);
		}
		else
		{
			foreach($collection as $shipment)
			{
				if($shipment->getId() <= 0)
				{
					return $shipment;
				}
			}
		}

		return null;
	}

	/**
	 * @param OrderBuilder $builder
	 * @param array $shipmentData
	 * @return \Bitrix\Sale\Payment
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getUpdatedPayment(OrderBuilder $builder, array $paymentData)
	{
		try{
			$builder->initFields(array(
				'ID' => $paymentData['ORDER_ID'],
				'SITE_ID' => $paymentData['SITE_ID'],
				'PAYMENT' => array($paymentData)
			))
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

		$order = $builder->getOrder();
		$collection = $order->getPaymentCollection();

		if((int)$paymentData['ID'] > 0)
		{
			return $collection->getItemById($paymentData['ID']);
		}
		else
		{
			foreach($collection as $payment)
			{
				if($payment->getId() <= 0)
				{
					return $payment;
				}
			}
		}

		return null;
	}
}