<?php

namespace Bitrix\Sale;

/**
 * Class PropertyBase
 * @package Bitrix\Sale
 */
abstract class PropertyBase extends EntityProperty
{
	/**
	 * @return string Registry::ENTITY_ORDER
	 */
	protected static function getEntityType(): string
	{
		return Registry::ENTITY_ORDER;
	}
}
