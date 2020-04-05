<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Sale\TradingPlatform\Vk\Api\ApiHelper;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;

/**
 * Class ProductsDelete - processor for delete from VK only exported products
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class ProductsDelete extends DataProcessor
{
	/**
	 * Main export process method. Delete from VK only products, which were adding via export
	 * Have not input params - get values from VK and from mapping and compare them.
	 * 
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 */
	public function process($data = NULL, Timer $timer = NULL)
	{
		$apiHelper = new ApiHelper($this->exportId);
		
		$productsFromVk = $apiHelper->getProductsFromVk($this->vkGroupId);
		$productsMapped = Vk\Map::getMappedProducts($this->exportId);

//		remove from mapping products which not exist in VK
		$productsMappedToRemove = array();
		foreach ($productsMapped as $key => $productMapped)
		{
			if (!isset($productsFromVk[$productMapped["VK_ID"]]))
			{
				$productsMappedToRemove[] = array("VALUE_EXTERNAL" => $productMapped["VK_ID"]);
				unset($productsMapped[$key]);
			}
		}
//		remove not exists in VK items
		if (!empty($productsMappedToRemove))
			Vk\Map::removeProductMapping($productsMappedToRemove, $this->exportId);

//		In delete procedure we not need http file upload.
// 		It means that we can not limit max items by settings and using max possible count.
		$productsMapped = array_chunk($productsMapped, Vk\Vk::MAX_EXECUTION_ITEMS);    // max 25 items in execute()
		foreach ($productsMapped as $chunk)
		{
			$resDelete = $this->executer->executeMarketProductDelete(array(
				"owner_id" => $this->vkGroupId,
				"data" => $chunk,
				"count" => count($chunk),
			));

			foreach ($resDelete as $res)
			{
				if ($res["flag_product_delete_result"])
					$productsMappedToRemove[] = array("VALUE_EXTERNAL" => $res["VK_ID"]);
			}

			// remove success deleted items
			if (!empty($productsMappedToRemove))
				Vk\Map::removeProductMapping($productsMappedToRemove, $this->exportId);

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
