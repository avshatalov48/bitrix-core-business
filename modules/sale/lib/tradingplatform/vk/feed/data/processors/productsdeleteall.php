<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;

/**
 * Class ProductsDeleteAll - processing delete all products from VK
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class ProductsDeleteAll extends DataProcessor
{
	/**
	 * Main export process method. Delete from VK ALL products
	 * Have not input params - get values from VK and from mapping and delete them all.
	 *
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 */
	public function process($data = NULL, Timer $timer = NULL)
	{
		$apiHelper = new Vk\Api\ApiHelper($this->exportId);
		$productsFromVk = $apiHelper->getProductsFromVk($this->vkGroupId);
		$productsMapped = Vk\Map::getMappedProducts($this->exportId);

//		delete ALL from mapping
		$productsMappedToRemove = array();
		foreach ($productsMapped as $productMapped)
			$productsMappedToRemove[] = array("VALUE_EXTERNAL" => $productMapped["VK_ID"]);
		
		if (!empty($productsMappedToRemove))
			Vk\Map::removeProductMapping($productsMappedToRemove, $this->exportId);


		$productsFromVk = Vk\Api\ApiHelper::extractItemsFromArray($productsFromVk, array("VK_ID"));
		$productsFromVk = array_chunk($productsFromVk, Vk\Vk::MAX_EXECUTION_ITEMS);    // max 25 items in execute()
		foreach ($productsFromVk as $chunk)
		{
			$this->executer->executeMarketProductDelete(array(
				"owner_id" => $this->vkGroupId,
				"data" => $chunk,
				"count" => count($chunk),
			));

//			abstract start position - only for continue export, not for rewind to position
			if ($timer !== NULL && !$timer->check())
				throw new TimeIsOverException("Timelimit for export is over", '1');
		}

//		remove products from cache
		$vkExportedData = new Vk\VkExportedData($this->exportId, 'PRODUCTS');
		$vkExportedData->removeData();
		
		return true;
	}
}
