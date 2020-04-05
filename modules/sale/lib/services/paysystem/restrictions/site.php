<?php
namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Services\Base;

Loc::loadMessages(__FILE__);

/**
 * Class Site
 * @package Bitrix\Sale\Services\PaySystem\Restrictions
 */
class Site extends Base\SiteRestriction
{
	/**
	 * @param Sale\Internals\Entity $entity
	 * @return Sale\Order|null
	 */
	protected static function getOrder(Sale\Internals\Entity $entity)
	{
		if (!($entity instanceof Sale\Payment))
		{
			return null;
		}

		/** @var \Bitrix\Sale\PaymentCollection $collection */
		$collection = $entity->getCollection();

		return $collection->getOrder();
	}
} 