<?php

namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Services\Base\ConcreteProductRestriction;
use Bitrix\Sale\ShipmentItem;

/**
 * Class ByConcreteProduct
 * Restrictions delivery by concrete products
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByConcreteProduct extends ConcreteProductRestriction
{
	/**
	 * @return string
	 */
	protected static function getJsHandler(): string
	{
		return "BX.Sale.Delivery";
	}

	/**
	 * Returns the restriction description
	 * @return string
	 */
	public static function getClassDescription(): string
	{
		return Loc::getMessage('SALE_DLVR_RSTR_BY_CONCRETE_PRODUCT_DESC');
	}

	/**
	 * @param Shipment $entity
	 * @return array
	 */
	protected static function getBasketItems(Entity $entity): array
	{
		if (!$entity instanceof Shipment)
		{
			return [];
		}

		$basketItems = [];

		/** @var ShipmentItem $shipmentItem */
		foreach ($entity->getShipmentItemCollection()->getSellableItems() as $shipmentItem)
		{
			$basketItems[] = $shipmentItem->getBasketItem();
		}

		return $basketItems;
	}
}
