<?php

namespace Bitrix\Catalog\v2\Section;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\RepositoryContract;

/**
 * Interface SectionRepositoryContract
 *
 * @package Bitrix\Catalog\v2\Section
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface SectionRepositoryContract extends RepositoryContract
{
	public function getCollectionByProduct(BaseProduct $product): BaseCollection;
}