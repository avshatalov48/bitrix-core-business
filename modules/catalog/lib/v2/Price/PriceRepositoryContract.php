<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\v2\RepositoryContract;
use Bitrix\Catalog\v2\Sku\BaseSku;

/**
 * Interface PriceRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Price
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface PriceRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(BaseSku $sku): PriceCollection;
}