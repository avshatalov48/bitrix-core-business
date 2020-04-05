<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;

/**
 * Class AlbumsDelete - processor for delete from VK only exported products
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class AlbumsDelete extends DataProcessor
{
	/**
	 * Main export process method. Delete from VK only albums, which were adding via export
	 * Have not input params - get values from VK and from mapping and compare them.
	 *
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 */
	public function process($data = NULL, Timer $timer = NULL)
	{
		$apiHelper = new Vk\Api\ApiHelper($this->exportId);
		$albumsFromVk = $apiHelper->getALbumsFromVk($this->vkGroupId);
		$albumsMapped = Vk\Map::getMappedAlbums($this->exportId);
//		remove from mapping albums which not exist in VK
		$albumsMappedToRemove = array();
		foreach ($albumsMapped as $key => $albumMapped)
		{
			if (!isset($albumsFromVk[$albumMapped["ALBUM_VK_ID"]]))
			{
				$albumsMappedToRemove[] = array("VALUE_EXTERNAL" => $albumMapped["ALBUM_VK_ID"]);
				unset($albumsMapped[$key]);
			}
		}
// 		remove not exists in VK items
		if (!empty($albumsMappedToRemove))
			Vk\Map::removeAlbumMapping($albumsMappedToRemove, $this->exportId);

//		In delete procedure we not need http file upload.
// 		It means that we can not limit max items by settings and using max possible count.
		$albumsMapped = array_chunk($albumsMapped, Vk\Vk::MAX_EXECUTION_ITEMS);    // max 25 items in execute()
		foreach ($albumsMapped as $chunk)
		{
			$resDelete = $this->executer->executeMarketAlbumDelete(array(
				"owner_id" => $this->vkGroupId,
				"data" => $chunk,
				"count" => count($chunk),
			));

			foreach ($resDelete as $res)
			{
				if ($res["flag_album_delete_result"])
					$albumsMappedToRemove[] = array("VALUE_EXTERNAL" => $res["ALBUM_VK_ID"]);
			}

// 			remove success deleted items
			if (!empty($albumsMappedToRemove))
				Vk\Map::removeAlbumMapping($albumsMappedToRemove, $this->exportId);

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
