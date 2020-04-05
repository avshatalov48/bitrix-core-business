<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Crm\Invoice\EntityMarker;
use Bitrix\Main\ArgumentException;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

class ShipmentInvoice extends ShipmentImport
{
	public function getOwnerTypeId()
	{
		return EntityType::INVOICE_SHIPMENT;
	}

	/**
	 * @param $order
	 * @param $entity
	 * @param $result
	 */
	protected function addMarker($invoice, $entity, $result)
	{
		EntityMarker::addMarker($invoice, $entity, $result);
	}

	/**
	 * @param array $fields
	 * @return Order|null
	 */
	protected function loadParentEntity(array $fields)
	{
		$entity = null;

		if(!empty($fields['ID']))
		{
			/** @var Order $entity */
			$entity = \Bitrix\Crm\Invoice\Invoice::load($fields['ID']);
		}
		return $entity;
	}

	/**
	 * @param Entity $shipment
	 * @return int
	 * @throws ArgumentException
	 */
	public static function resolveEntityTypeId(Entity $shipment)
	{
		if(!($shipment instanceof Shipment))
			throw new ArgumentException("Entity must be instanceof Shipment");

		return EntityType::INVOICE_SHIPMENT;
	}

	/**
	 * @param BasketBase $basket
	 * @param array $item
	 * @return \Bitrix\Sale\BasketItem|bool
	 */
	protected function getBasketItemByItem(BasketBase $basket, array $item)
	{
		return Invoice::getBasketItemByItem($basket, $item);
	}

	/**
	 * @param BasketItem $basket
	 * @return array
	 */
	protected function getAttributesItem(BasketItem $basket)
	{
		return Invoice::getAttributesItem($basket);
	}
}