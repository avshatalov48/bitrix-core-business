<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;


use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;

/**
 * Class AlbumsDeleteAll - processor for delete all albums from VK
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class AlbumsDeleteAll extends DataProcessor
{
	/**
	 * Main export process method. Delete from VK ALL albums
	 * Have not input params - get values from VK and from mapping and delete them all.
	 *
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 */
	public function process($data = NULL, Timer $timer = NULL)
	{
		$apiHelper = new Vk\Api\ApiHelper($this->exportId);
		$albumsFromVk = $apiHelper->getALbumsFromVk($this->vkGroupId, false);
		$albumsMapped = Vk\Map::getMappedAlbums($this->exportId);

//		delete ALL from mapping
		$albumsMappedToRemove = array();
		foreach ($albumsMapped as $albumMapped)
			$albumsMappedToRemove[] = array("VALUE_EXTERNAL" => $albumMapped["ALBUM_VK_ID"]);

		if (!empty($albumsMappedToRemove))
			Vk\Map::removeAlbumMapping($albumsMappedToRemove, $this->exportId);


//		formatted data
		foreach ($albumsFromVk as &$album)
		{
			$album = array('ALBUM_VK_ID' => $album);
		}
		$albumsFromVk = array_chunk($albumsFromVk, Vk\Vk::MAX_EXECUTION_ITEMS);    // max 25 items in execute()
		foreach ($albumsFromVk as $chunk)
		{
			$this->executer->executeMarketAlbumDelete(array(
				"owner_id" => $this->vkGroupId,
				"data" => $chunk,
				"count" => count($chunk),
			));

//			abstract start position - only for continue export, not for rewind to position
			if ($timer !== NULL && !$timer->check())
				throw new TimeIsOverException("Timelimit for export is over", '1');
		}

//		remove products from cache
		$vkExportedData = new Vk\VkExportedData($this->exportId, 'ALBUMS');
		$vkExportedData->removeData();

		return true;
	}
}
