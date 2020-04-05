<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Sale;

/**
 * Class ByUserGroup
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByUserGroup extends \Bitrix\Sale\Services\Base\UserGroupRestriction
{
	protected static function getEntityTypeId()
	{
		return \Bitrix\Sale\Internals\UserGroupRestrictionTable::ENTITY_TYPE_SHIPMENT;
	}

	/**
	 * @param Sale\Internals\Entity $entity
	 * @return Sale\Order|null
	 */
	protected static function getOrder(Sale\Internals\Entity $entity)
	{
		if ($entity instanceof Sale\Shipment)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			return $collection->getOrder();
		}
		elseif ($entity instanceof Sale\Order)
		{
			/** @var \Bitrix\Sale\Order $order */
			return $entity;
		}

		return null;
	}
} 