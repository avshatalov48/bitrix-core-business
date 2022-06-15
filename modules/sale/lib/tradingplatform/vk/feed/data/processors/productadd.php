<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Processors;

use Bitrix\Main\SystemException;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\Vk\Api\PhotoUploader;

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
	public function process($data, Timer $timer = null)
	{
//		logger use always, but rich log need only if set this option
		$logger = new Vk\Logger($this->exportId);

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
				$logger->addLog("Upload main photo");
				$photoUploader = new PhotoUploader($this->exportId, PhotoUploader::TYPE_PRODUCT_MAIN_PHOTO, $timer);
				$mainPhotoSaveResults = $photoUploader->upload($data);

//				photos UPLOAD may be FAILED on VK side. SKIP product
				if(array_key_exists('errors', $mainPhotoSaveResults))
				{
					foreach($mainPhotoSaveResults['errors'] as $errorId)
					{
						unset($data[$errorId]);
					}
					unset($mainPhotoSaveResults['errors']);
				}

				$data = Vk\Api\ApiHelper::addResultToData($data, $mainPhotoSaveResults, "BX_ID");
			}


//			UPLOAD photoS
			foreach ($data as &$product)
			{
//				todo: if fail load photo - just log
				if ($product["PHOTOS"])
				{
//					if error in some photo - just skip them
					$logger->addLog("Upload product photos");
					$photoUploader = new PhotoUploader($this->exportId, PhotoUploader::TYPE_PRODUCT_PHOTOS, $timer);
					$photosSaveResults = $photoUploader->upload($product["PHOTOS"]);

//					photos UPLOAD may be FAILED on VK side. SKIP photo
					if(array_key_exists('errors', $photosSaveResults))
					{
						foreach($photosSaveResults['errors'] as $errorId)
						{
							unset($product["PHOTOS"][$errorId]);
						}
						unset($photosSaveResults['errors']);
					}

					$product["PHOTOS"] = Vk\Api\ApiHelper::addResultToData(
						$product["PHOTOS"],
						$photosSaveResults,
						"PHOTO_BX_ID"
					);
				}
			}
			unset($product);

//			ADD or EDIT products
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

			foreach ($productsMultiSections as $productId => $product)
			{
				foreach ($product as $sectionId)
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

						if (count($productsToAlbums) === Vk\Vk::MAX_EXECUTION_ITEMS)
						{
							$this->addProductsToAlbums($logger, $productsToAlbums);
							$productsToAlbums = [];
						}
					}
				}
			}
			$this->addProductsToAlbums($logger, $productsToAlbums);

//			WRITE successful results TO MAP
//			we don't need use timer in last operation	. Timer will be checked in feed cycle.
			if (!empty($dataToMapping))
			{
				Vk\Map::addProductMapping($dataToMapping, $this->exportId);
			}
			unset($dataToMapping, $product);


//			add saved data to CACHE to accelereate export process. Cache updated every hour (for long exports)
			if (!empty($data))
			{
				$dataToCache = Vk\Api\ApiHelper::extractItemsFromArray($data, array('VK_ID'));
				$dataToCache = Vk\Api\ApiHelper::changeArrayMainKey($dataToCache, 'VK_ID');
				$vkExportedData->addData($dataToCache);
			}

			$logger->addLog("Finish product add chunk");

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

	private function addProductsToAlbums($logger, $productsToAlbums): void
	{
		$logger->addLog("Add products to albums", $productsToAlbums);
		$this->executer->executeMarketProductAddToAlbums(array(
			"owner_id" => $this->vkGroupId,
			"data" => $productsToAlbums,
			"count" => count($productsToAlbums),
		));
	}
}
