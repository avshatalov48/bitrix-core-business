<?php
namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;

Loc::loadMessages(__FILE__);

/**
 * Class Site
 * @package Bitrix\Sale\Services\PaySystem\Restrictions
 */
class Site extends Restrictions\BySite
{
	/**
	 * @param Internals\Entity $entity
	 * @return null|string
	 */
	protected static function extractParams(Internals\Entity $entity)
	{
		if (!($entity instanceof Payment))
			return false;

		if ($entity instanceof Internals\CollectableEntity)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $collection->getOrder();
		}
		elseif ($entity instanceof Order)
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $entity;
		}

		if (!$order)
			return false;

		return $order->getSiteId();
	}
} 