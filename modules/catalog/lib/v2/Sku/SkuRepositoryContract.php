<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\RepositoryContract;

/**
 * Interface SkuRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface SkuRepositoryContract extends RepositoryContract
{
	public function getCollectionByProduct(BaseProduct $product): SkuCollection;
}