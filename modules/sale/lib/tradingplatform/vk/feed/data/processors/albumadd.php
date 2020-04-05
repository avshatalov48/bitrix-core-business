<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use \Bitrix\Main\SystemException;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\Vk\Api\PhotoUploader;

/**
 * Class AlbumAdd - Processor for adding albums to VK
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class AlbumAdd extends DataProcessor
{
	private static $firstRunning = true;
	private static $apiHelper;
	private static $isAgressive;
	
	/**
	 * Main export process method. Adding albums in VK
	 *
	 * @param $data - array of albums to export. Max 25 items
	 * @param null $timer - Tradingplatform\Timer to control execution time
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 * @throws SystemException
	 * @throws TimeIsOverException
	 * @throws Vk\ExecuteException
	 */
	public function process($data = null, Timer $timer = null)
	{
		$logger = new Vk\Logger($this->exportId);
		
		if (count($data) > Vk\Vk::MAX_EXECUTION_ITEMS)
		{
			$data = array_slice($data, 0, Vk\Vk::MAX_ALBUMS);
			$logger->addError('TOO_MANY_SECTIONS_TO_EXPORT');
		}

//		get STARTPOSITION for create next process step
		reset($data);
		$startPosition = current($data);
		$startPosition = $startPosition["SECTION_ID"];


//		set STATIC variables for several cycles
		if (self::$firstRunning)
		{
			self::$apiHelper = new Vk\Api\ApiHelper($this->exportId);
			self::$isAgressive = self::$vk->isAgressiveExport($this->exportId);
			self::$firstRunning = false;
		}

//		CHECK existing albums
		$vkExportedData = new Vk\VkExportedData($this->exportId, 'ALBUMS');
		$albumsFromVk = $vkExportedData->getData();
//		$albumsFromVk = self::$apiHelper->getALbumsFromVk($this->vkGroupId);
		$data = Vk\Map::checkMappingMatches($data, $albumsFromVk, $this->exportId, 'ALBUMS', self::$isAgressive);
		
		try
		{
//			UPLOAD photo
//			todo: need a photo mapping check before upload
//			todo: and maybe we need comments and likes
			$logger->addLog("Upload album photo");
			
			$photoUploader = new PhotoUploader($this->exportId, PhotoUploader::TYPE_ALBUM_PHOTO, $timer);
			$albumPhotoSaveResults = $photoUploader->upload($data);
			
//			photos UPLOAD may be FAILED on VK side. For albums - do nothing
			if(!array_key_exists('errors', $albumPhotoSaveResults))
			{
				$data = Vk\Api\ApiHelper::addResultToData($data, $albumPhotoSaveResults, "SECTION_ID");
			}

//			ADD or EDIT albums
			$logger->addLog("Add or edit albums", $data);
			$albumsData = Vk\Api\ApiHelper::extractItemsFromArray($data,
				array("SECTION_ID", "TITLE", "FLAG_EDIT", "PHOTO_VK_ID", "ALBUM_VK_ID"));
			$albumsAddEditResults = $this->executer->executeMarketAlbumAddEdit(array(
				"owner_id" => $this->vkGroupId,
				"data" => $albumsData,
				"count" => count($data),
			));
			$data = Vk\Api\ApiHelper::addResultToData($data, $albumsAddEditResults, "SECTION_ID");


//			MAPPING for success results
			$dataToMapping = array();
			foreach ($albumsAddEditResults as $item)
			{
				if (isset($item["flag_album_add_result"]) && $item["flag_album_add_result"])
					$dataToMapping[] = array(
						"value_external" => $item["ALBUM_VK_ID"],
						"value_internal" => $item["SECTION_ID"],
					);
			}
//			we don't need use timer in last operation. Timer will be checked in feed cycle.
			if (!empty($dataToMapping))
			{
				Vk\Map::addAlbumMapping($dataToMapping, $this->exportId);
			}


//			add saved data to CACHE to accelereate export process. Cache updated every hour (for long exports)
			if (!empty($data))
			{
				$dataToCache = Vk\Api\ApiHelper::extractItemsFromArray($data, array('ALBUM_VK_ID'));
				$dataToCache = Vk\Api\ApiHelper::changeArrayMainKey($dataToCache, 'ALBUM_VK_ID');
				$vkExportedData->addData($dataToCache);
			}

//			check timer before next step, because not-agressive export can be run very long time
			if ($timer !== null && !$timer->check())
			{
				throw new TimeIsOverException();
			}
		}
		catch (TimeIsOverException $e)
		{
			throw new TimeIsOverException("Timelimit for export is over", $startPosition);
		}
		
		return true;
	}
}
