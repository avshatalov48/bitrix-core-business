<?php

namespace Bitrix\Catalog\Integration\Report\Handler\Chart\StoreInfoCombiner;

use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreInfo;

interface StoreInfoCombiner
{
	/**
	 * Summarize all stores to one combined store and return it
	 * @param StoreInfo ...$storesInfo
	 * @return StoreInfo
	 */
	public function summarizeStores(...$storesInfo): StoreInfo;
}
