<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

/**
 * Class VkExportedData
 * Short-time cqche of exported vk-albums or products.
 * Need for accelerate, because this data requested all step and waste more time.
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class VkExportedData
{
	
	const CACHE_DIR = '/sale/vkexport/';
	const CACHE_TTL = 3600;        // one hour, for update during long export
	const CACHE_ID_PREFIX = "vkexporteddata_cache";
	private $exportId;
	private $type;
	private $cacheId;
	
	/**
	 * VkExportedData constructor.
	 * @param $exportId - int, ID of export profile
	 * @param $type - string of export type
	 */
	public function __construct($exportId, $type)
	{
		$this->exportId = intval($exportId);
		
		if (in_array($type, array('PRODUCTS', 'ALBUMS')))
			$this->type = $type;
		else
			throw new ArgumentNullException("EXPORT_ID");
		
		$this->cacheId = $this->getCacheId();
	}
	
	
	/**
	 * Get data from cache or load them from VK
	 *
	 * @return array|bool|mixed|null - data
	 */
	public function getData()
	{
		$cacheManager = Application::getInstance()->getManagedCache();
		$result = NULL;
		
		if ($cacheManager->read(self::CACHE_TTL, $this->cacheId))
		{
			$result = $cacheManager->get($this->cacheId);
		}
		else
		{
			$result = $this->getDataFromVk();
			
			$cacheManager->set($this->cacheId, $result);
		}
		
		return $result;
	}
	
	
	/**
	 * Clean cache
	 * @return void
	 */
	public function removeData()
	{
		$cacheManager = Application::getInstance()->getManagedCache();
		$cacheManager->clean($this->cacheId);
	}
	
	
	/**
	 * Add data to saveed array
	 *
	 * @param $newData - array to special format
	 * @return void
	 */
	public function addData($newData)
	{
		$cacheManager = Application::getInstance()->getManagedCache();

//		get saved data from cache, if exist...
		if ($cacheManager->read(self::CACHE_TTL, $this->cacheId))
			$savedData = $cacheManager->get($this->cacheId);

//		...or from VK
		else
			$savedData = $this->getDataFromVk();

//		add new data to existing
		$dataToSave = $savedData + $newData;
		$cacheManager->clean($this->cacheId);
		$cacheManager->read(self::CACHE_TTL, $this->cacheId);
		$cacheManager->set($this->cacheId, $dataToSave);
	}
	
	
	/**
	 * Generate name for cache
	 *
	 * @return string
	 */
	private function getCacheId()
	{
		return self::CACHE_ID_PREFIX . '_' . $this->exportId . '_' . $this->type;
	}
	
	
	/**
	 * Get Albums or Products data from VK for current export ID
	 *
	 * @return array|bool    - array of data or false in data is null or errors
	 */
	private function getDataFromVk()
	{
		$apiHelper = new Api\ApiHelper($this->exportId);
		$data = false;
		
		switch ($this->type)
		{
			case 'ALBUMS':
				$data = $apiHelper->getALbumsFromVk($this->getVkGroupId());
				break;
			
			case 'PRODUCTS':
				$data = $apiHelper->getProductsFromVk($this->getVkGroupId());
				break;
			
			default:
				break;
		}
		
		return $data;
	}
	
	/**
	 * Get VK group ID for current export ID
	 *
	 * @return string -    export ID
	 */
	private function getVkGroupId()
	{
		$vk = Vk::getInstance();
		
		return $vk->getGroupId($this->exportId);
	}
}