<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Services\Base\ProductCategoryRestriction;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class ByProductCategory
 * Restricts delivery by product category
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByProductCategory extends ProductCategoryRestriction
{
	public static $easeSort = 400;

	/**
	 * @return string
	 */
	protected static function getJsHandler(): string
	{
		return 'BX.Sale.Delivery';
	}

	/**
	 * Returns the restriction description
	 * @return string
	 */
	public static function getClassDescription() : string
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_PC_DESCRIPT");
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

		/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
		foreach ($entity->getShipmentItemCollection()->getSellableItems() as $shipmentItem)
		{
			$basketItems[] = $shipmentItem->getBasketItem();
		}

		return $basketItems;
	}
}