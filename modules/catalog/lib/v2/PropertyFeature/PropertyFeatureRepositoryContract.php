<?php

namespace Bitrix\Catalog\v2\PropertyFeature;

use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\v2\RepositoryContract;

/**
 * Interface PropertyFeatureRepositoryContract
 *
 * @package Bitrix\Catalog\v2\PropertyFeature
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface PropertyFeatureRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(Property $entity): PropertyFeatureCollection;
}