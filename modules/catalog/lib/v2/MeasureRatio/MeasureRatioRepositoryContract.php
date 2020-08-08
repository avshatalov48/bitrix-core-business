<?php

namespace Bitrix\Catalog\v2\MeasureRatio;

use Bitrix\Catalog\v2\RepositoryContract;
use Bitrix\Catalog\v2\Sku\BaseSku;

/**
 * Interface MeasureRatioRepositoryContract
 *
 * @package Bitrix\Catalog\v2\MeasureRatio
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface MeasureRatioRepositoryContract extends RepositoryContract
{
	public function getCollectionByParent(BaseSku $sku): MeasureRatioCollection;
}