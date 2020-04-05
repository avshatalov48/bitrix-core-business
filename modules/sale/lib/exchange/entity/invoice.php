<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Crm\Invoice\EntityMarker;
use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;

class Invoice extends OrderImport
{
	/**
	 * @return int
	 */
	public function getOwnerTypeId()
	{
		return EntityType::INVOICE;
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
	 * @param array $fileds
	 * @return \Bitrix\Sale\Order
	 */
	protected function createEntity(array $fileds)
	{
		return \Bitrix\Crm\Invoice\Invoice::create($this->settings->getSiteId(), $fileds['USER_ID'], $this->settings->getCurrency());
	}

	/**
	 * @param array $fields
	 * @return Order
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
	 * @param Entity $order
	 * @return int
	 * @throws ArgumentException
	 */
	public static function resolveEntityTypeId(Entity $order)
	{
		if(!($order instanceof Order))
			throw new ArgumentException("Entity must be instanceof Order");

		return EntityType::INVOICE;
	}

	/**
	 * @return int
	 * @internal
	 */
	protected function getShipmentTypeId()
	{
		return EntityType::INVOICE_SHIPMENT;
	}
}