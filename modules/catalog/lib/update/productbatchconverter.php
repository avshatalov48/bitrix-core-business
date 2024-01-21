<?php
namespace Bitrix\Catalog\Update;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Product\Store\BatchBalancer\Balancer;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;

/**
 * Class ProductBatchConverter
 * 
 * Fill product batches into table from StoreProductTable for support calculation purchasing prices.
 *
 * @package Bitrix\Catalog\Update
 */
class ProductBatchConverter extends Stepper
{
	protected const PRODUCT_LIMIT = 100;

	protected static $moduleId = 'catalog';

	public function execute(array &$option): bool
	{
		if (!Loader::includeModule('catalog') || !State::isUsedInventoryManagement())
		{
			return self::FINISH_EXECUTION;
		}

		$option["count"] = StoreProductTable::getCount();
		$option["steps"] ??= 0;
		$option["lastProductId"] ??= 0;
		$storeProductDataRaw = StoreProductTable::getList([
			'limit' => self::PRODUCT_LIMIT,
			'select' => ['PRODUCT_ID'],
			'filter' => [
				'>PRODUCT_ID' => (int)$option["lastProductId"],
			],
			'group' => ['PRODUCT_ID'],
			'order' => ['PRODUCT_ID' => 'ASC'],
		]);

		$batchCount = 0;
		while ($storeProduct = $storeProductDataRaw->fetch())
		{
			$storeProductId = $storeProduct['PRODUCT_ID'];
			$option["lastProductId"] = $storeProductId;

			(new Balancer($storeProductId))->fill();

			$batchCount++;
		}

		$option["steps"] += $batchCount;

		return $batchCount === 0 ? self::FINISH_EXECUTION : self::CONTINUE_EXECUTION;
	}

	public static function getTitle()
	{
		return Loc::getMessage('CATALOG_PRODUCT_BATCH_CONVERTER_TITLE');
	}
}