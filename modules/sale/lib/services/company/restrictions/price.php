<?php

namespace Bitrix\Sale\Services\Company\Restrictions;

use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Services\PaySystem\Restrictions;
use Bitrix\Sale\Shipment;

class Price extends Restrictions\Price
{
	/**
	 * @param Entity $entity
	 *
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		/** @var \Bitrix\Sale\PaymentCollection|\Bitrix\Sale\ShipmentCollection|null $collection */
		$collection = null;

		if ($entity instanceof Payment)
			$collection = $entity->getCollection();
		elseif ($entity instanceof Shipment)
			$collection = $entity->getCollection();
		elseif ($entity instanceof Order)
		{
			return array('PRICE_PAYMENT' => $entity->getPrice());
		}

		if ($collection)
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $collection->getOrder();

			return array('PRICE_PAYMENT' => $order->getPrice());
		}

		return array('PRICE_PAYMENT' => null);
	}
}