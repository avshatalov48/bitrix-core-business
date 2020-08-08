<?php

namespace Bitrix\Catalog\v2\Sku;

/**
 * Interface HasSkuCollection
 *
 * @package Bitrix\Catalog\v2\Sku
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface HasSkuCollection
{
	public function getSkuCollection(): SkuCollection;

	public function setSkuCollection(SkuCollection $skuCollection);
}