<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

/**
 * Interface HasPropertyFeatureCollection
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasPropertyFeatureCollection
{
	public function getPropertyFeatureCollection();

	public function setPropertyFeatureCollection(PropertyFeatureCollection $propertyFeatureCollection);
}