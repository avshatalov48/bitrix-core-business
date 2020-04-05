<?php
namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Services\Base;

Loc::loadMessages(__FILE__);

/**
 * Class TradeBinding
 * @package Bitrix\Sale\Services\PaySystem\Restrictions
 */
class TradeBinding extends Base\TradeBindingRestriction
{
	/**
	 * @param Sale\Internals\Entity $entity
	 * @return Sale\Order|null
	 */
	protected static function getOrder(Sale\Internals\Entity $entity)
	{
		if ($entity instanceof Sale\Payment)
		{
			/** @var \Bitrix\Sale\PaymentCollection $collection */
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