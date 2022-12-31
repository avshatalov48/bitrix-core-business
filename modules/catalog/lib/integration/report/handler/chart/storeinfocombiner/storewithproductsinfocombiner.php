<?php

namespace Bitrix\Catalog\Integration\Report\Handler\Chart\StoreInfoCombiner;

use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreInfo;
use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreWithProductsInfo;

class StoreWithProductsInfoCombiner implements StoreInfoCombiner
{

	/**
	 * @inheritDoc
	 * @param StoreWithProductsInfo ...$storesInfo
	 * @return StoreWithProductsInfo
	 */
	public function summarizeStores(...$storesInfo): StoreInfo
	{
		if (count($storesInfo) === 0)
		{
			return new StoreWithProductsInfo(0);
		}

		$outputStore = new StoreWithProductsInfo($storesInfo[0]->getStoreId());
		foreach ($storesInfo as $storeInfo)
		{
			$outputStore->addProduct(...$storeInfo->getProductList());
		}

		return $outputStore;
	}
}
