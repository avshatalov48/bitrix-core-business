<?php

namespace Bitrix\Catalog\v2\Sku;

use Bitrix\Catalog\v2\IblockElementRepositoryContract;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Iterator;

/**
 * Interface SkuRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface SkuRepositoryContract extends IblockElementRepositoryContract
{
	public function getCollectionByProduct(BaseProduct $product): SkuCollection;

	public function getEntitiesByProduct(BaseProduct $product, array $params): Iterator;

	public function getCountByProductId(int $productId): int;
}
