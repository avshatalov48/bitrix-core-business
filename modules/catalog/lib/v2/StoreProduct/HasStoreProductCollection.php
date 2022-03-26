<?php

namespace Bitrix\Catalog\v2\StoreProduct;

/**
 * Interface HasStoreProductCollection
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasStoreProductCollection
{
	public function getStoreProductCollection(): StoreProductCollection;

	public function setStoreProductCollection(StoreProductCollection $storeCollection);
}
