<?php

namespace Bitrix\Catalog\v2\PropertyValue;

/**
 * Interface HasPropertyValueCollection
 *
 * @package Bitrix\Catalog\v2\PropertyValue
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasPropertyValueCollection
{
	public function getPropertyValueCollection();

	public function setPropertyValueCollection(PropertyValueCollection $propertyValueCollection);
}