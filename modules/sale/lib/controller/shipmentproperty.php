<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Sale\Registry;

/**
 * Class ShipmentProperty
 * @package Bitrix\Sale\Controller
 */
class ShipmentProperty extends Property
{
	/**
	 * @inheritDoc
	 */
	protected function getPropertyClassName(): string
	{
		return \Bitrix\Sale\ShipmentProperty::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityType(): string
	{
		return Registry::ENTITY_SHIPMENT;
	}
}
