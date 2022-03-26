<?php

namespace Bitrix\Catalog\v2\StoreProduct;

use Bitrix\Catalog\v2\RepositoryContract;
use Bitrix\Catalog\v2\Sku\BaseSku;

/**
 * Interface StoreProductRepositoryContract
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface StoreProductRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(BaseSku $sku): StoreProductCollection;
}
