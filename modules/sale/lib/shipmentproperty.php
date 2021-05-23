<?php

namespace Bitrix\Sale;

/**
 * Class ShipmentProperty
 * @package Bitrix\Sale
 */
class ShipmentProperty extends EntityProperty
{
	/**
	 * @return string Registry::ENTITY_SHIPMENT
	 */
	protected static function getEntityType(): string
	{
		return Registry::ENTITY_SHIPMENT;
	}
}
