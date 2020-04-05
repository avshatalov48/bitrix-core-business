<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Main\SystemException;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;

/**
 * Class ProductAdd - Processor for adding product to VK
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors
 */
class ProductAdd extends DataProcessor
{
	private static $firstRunning = true;
	private static $albumsMapped;
	private static $apiHelper;
	private static $isAgressive;
	
	/**
	 * Main export process method. Adding products in VK
	 *
	 * @param $data
	 * @param Timer $timer - Tradingplatform\Timer to control execution time
	 * @return bool - return true if OK or if errors it not critical. Expression if timer is over
	 * @throws SystemException
	 * @throws TimeIsOverException
	 * @throws Vk\ExecuteException
	 */
	public function process($data, Timer $timer = NULL)
	{
//		logger use always, but rich log need only if set this option
		$logger = new Vk\Logger($this->exportId);
		$richLog = self::$vk->getRichLog($this->exportId);
		
		if (count($data) > Vk\Vk::MAX_EXECUTION_ITEMS)
		{
			$data = array_slice($data, 0, Vk\Vk::MAX_ALBUMS);
			$logger->addError('TOO_MANY_PRODUCTS_TO_EXPORT');
		}

//		get STARTPOSITION for create next process step
		reset($data);
		$startPosition = current($data);
		$startPosition = $startPosition["BX_ID"];

//		set STATIC variables for several cycles
		if (self::$firstRunning)
		{
			self::$apiHelper = new Vk\Api\ApiHelper($this->exportId);
			self::$albumsMapped = Vk\Map::getMappedAlbums($this->exportId);
			self::$albumsMapped = Vk\Api\ApiHelper::changeArrayMainKey(self::$albumsMapped, 'SECTION_ID');
			self::$isAgressive = self::$vk->isAgressiveExport($this->exportId);
			self::$firstRunning = false;
		}

//		CHECK existing products
		$vkExportedData = new Vk\VkExportedData($this->exportId, 'PRODUCTS');
		$productsFromVk = $vkExportedData->getData();
		$data = Vk\Map::checkMappingMatches($data, $productsFromVk, $this->exportId, 'PRODUCTS', self::$isAgressive);
		
		try
		{
//			check MAIN PHOTO and delete items. NO photo = NO product!
			foreach ($data as $item)
			{
				if (!isset($item["PHOTO_MAIN_BX_ID"]) || !$item["PHOTO_MAIN_BX_ID"])
				{
					$logger->addError("PRODUCT_WRONG_PHOTOS", $item["BX_ID"]);
					unset($data[$item["BX_ID"]]);
				}
			}

//			UPLOAD main photo
//			todo: need a photo mapping check before upload.
			if (!empty($data))
			{
				if ($richLog)
					$logger->addLog("Upload main photo");
				
				$mainPhotoSaveResults = self::$apiHelper->uploadPhotos($data, $this->vkGroupId, 'PRODUCT_MAIN_PHOTO', $timer);
				$data = Vk\Api\ApiHelper::addResultToData($data, $mainPhotoSaveResults, "BX_ID");
			}


//			UPLOAD photoS
			foreach ($data as &$product)
			{
				if ($product["PHOTOS"])
				{
					if ($richLog)
						$logger->addLog("Upload product photos");
					$productPhotosSaveResults = self::$apiHelper->uploadPhotos($product["PHOTOS"], $this->vkGroupId, 'PRODUCT_PHOTOS', $timer);
					$product["PHOTOS"] = Vk\Api\ApiHelper::addResultToData($product["PHOTOS"], $productPhotosSaveResults, "PHOTO_BX_ID");
				}
			}
			unset($product);

//			ADD or EDIT products
			if ($richLog)
				$logger->addLog("Add or edit products", $data);
			$productsData = Vk\Api\ApiHelper::prepareProductsDataToVk($data);
			$productsAddEditResults = $this->executer->executeMarketProductAddEdit(array(
				"owner_id" => $this->vkGroupId,
				"data" => $productsData,
				"count" => count($productsData),
			));
			$data = Vk\Api\ApiHelper::addResultToData($data, $productsAddEditResults, "BX_ID");
			unset($productsAddEditResults, $productsData);


//			construct MAPPING data for success results
			$dataToMapping = array();
			foreach ($data as $product)
			{
				if (isset($product["FLAG_PRODUCT_ADD_RESULT"]) && $product["FLAG_PRODUCT_ADD_RESULT"])
				{
					$dataToMapping[] = array(
						"value_external" => $product["VK_ID"],
						"value_internal" => $product["BX_ID"],
					);
				}
			}

//			adding to ALBUMS
			$productsToAlbums = array();
			$sectionsList = new Vk\SectionsList($this->exportId);
			
//			product may have multisections - find all them
			$productsIds = array_keys($data);
			$productsMultiSections = $sectionsList->getMultiSectionsToProduct($productsIds);
			
			foreach($productsMultiSections as $productId => $product)
			{
				foreach($product as $sectionId)
				{
//					find album to adding current product
					$toAlbumSectionId = $sectionsList->getToAlbumBySection($sectionId);

//					prepare array to ADDING products TO ALBUMS
					if (isset(self::$albumsMapped[$toAlbumSectionId]))
					{
						$productsToAlbums[] = array(
							"BX_ID" => $productId,
							"VK_ID" => $data[$productId]["VK_ID"],
							"ALBUM_VK_ID" => self::$albumsMapped[$toAlbumSectionId]["ALBUM_VK_ID"],
						);
					}
				}
			}
			
			if ($richLog)
				$logger->addLog("Add products to albums", $productsToAlbums);
			$this->executer->executeMarketProductAddToAlbums(array(
				"owner_id" => $this->vkGroupId,
				"data" => $productsToAlbums,
				"count" => count($productsToAlbums),
			));

//			WRITE successful results TO MAP
//			we don't need use timer in last operation	. Timer will be checked in feed cycle.
			if (!empty($dataToMapping))
				Vk\Map::addProductMapping($dataToMapping, $this->exportId);
			unset($dataToMapping, $product);


//			add saved data to CACHE to accelereate export process. Cache updated every hour (for long exports)
			if (!empty($data))
			{
				$dataToCache = Vk\Api\ApiHelper::extractItemsFromArray($data, array('VK_ID'));
				$dataToCache = Vk\Api\ApiHelper::changeArrayMainKey($dataToCache, 'VK_ID');
				$vkExportedData->addData($dataToCache);
			}
			
			if ($richLog)
				$logger->addLog("Finish product add chunk");

//			check timer before next step, because not-agressive export can be run very long time
			if ($timer !== NULL && !$timer->check())
				throw new TimeIsOverException();
		}
		
		catch (TimeIsOverException $e)
		{
			throw new TimeIsOverException("Timelimit for export is over", $startPosition);
		}
		
		return true;
	}
	
	
}
