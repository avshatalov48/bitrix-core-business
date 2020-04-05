<?php
namespace Bitrix\Sale\Services\Company\Restrictions;

use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Services;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;

/**
 * Class Site
 * @package Bitrix\Sale\Services\Company\Restrictions
 */
class Site extends Services\Base\SiteRestriction
{
	/**
	 * @param Internals\Entity $entity
	 * @return Internals\Entity|Order|null
	 */
	protected static function getOrder(Internals\Entity $entity)
	{
		if (!($entity instanceof Payment) && !($entity instanceof Shipment) && !($entity instanceof Order))
		{
			return null;
		}

		if ($entity instanceof Order)
		{
			return $entity;
		}
		else
		{
			/** @var PaymentCollection|ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var Order $order */
			return $collection->getOrder();
		}
	}
}