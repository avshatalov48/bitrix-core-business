<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Api;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\TimeIsOverException;
use Bitrix\Sale\TradingPlatform\Vk\Vk;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\IO;
use Bitrix\Sale\TradingPlatform\Timer;
use Bitrix\Sale\TradingPlatform\Vk\Logger;

Loc::loadMessages(__FILE__);

/**
 * Class Api
 * Work with VK API through http requsts
 * @package Bitrix\Sale\TradingPlatform\Vk\Api
 */
class PhotoUploader
{
	private $exportId;
	private $vk;
	private $api;
	private $logger;
	private $type;
	private $timer;
	private $vkGroupId;
	private $params = [];
	
	const TYPE_PRODUCT_MAIN_PHOTO = 'PRODUCT_MAIN_PHOTO';
	const TYPE_PRODUCT_PHOTOS = 'PRODUCT_PHOTOS';
	const TYPE_ALBUM_PHOTO = 'ALBUM_PHOTO';
	
	public function __construct($exportId, $photoType, Timer $timer = null)
	{
		if (empty($exportId))
		{
			throw new ArgumentNullException('exportId');
		}
		
		if (empty($photoType))
		{
			throw new ArgumentNullException('photoType');
		}
		
		$this->exportId = $exportId;
		$this->type = $photoType;
		$this->timer = $timer;
		$this->vk = Vk::getInstance();
		$this->vkGroupId = $this->vk->getGroupId($this->exportId);
		$this->api = $this->vk->getApi($exportId);
		$this->logger = new Logger($this->exportId);
		
		$this->initByType();
	}
	
	
	/**
	 * Save params for different upload types
	 * Like a fabric, but no )
	 */
	protected function initByType()
	{
		$this->type;
		
		switch ($this->type)
		{
			case self::TYPE_PRODUCT_MAIN_PHOTO:
				$this->params['uploadServerMethod']= 'photos.getMarketUploadServer';
				$this->params['saveMethod'] = 'photos.saveMarketPhoto';
				$this->params['keyReference'] = 'BX_ID';
				$this->params['keyPhotoVk'] = 'PHOTO_MAIN_VK_ID';
				$this->params['keyPhotoBx'] = 'PHOTO_MAIN_BX_ID';
				$this->params['keyPhotoUrl']= 'PHOTO_MAIN_URL';
				break;
			
			case self::TYPE_PRODUCT_PHOTOS:
				$this->params['uploadServerMethod']= 'photos.getMarketUploadServer';
				$this->params['saveMethod'] = 'photos.saveMarketPhoto';
				$this->params['keyReference'] = 'PHOTO_BX_ID';
				$this->params['keyPhotoVk'] = 'PHOTO_VK_ID';
				$this->params['keyPhotoBx'] = 'PHOTO_BX_ID';
				$this->params['keyPhotoUrl']= 'PHOTO_URL';
				break;
			
			case self::TYPE_ALBUM_PHOTO:
				$this->params['uploadServerMethod']= 'photos.getMarketAlbumUploadServer';
				$this->params['saveMethod'] = 'photos.saveMarketAlbumPhoto';
				$this->params['keyReference'] = 'SECTION_ID';
				$this->params['keyPhotoVk'] = 'PHOTO_VK_ID';
				$this->params['keyPhotoBx'] = 'PHOTO_BX_ID';
				$this->params['keyPhotoUrl']= 'PHOTO_URL';
				break;
			
			default:
				throw new SystemException("Wrong photo upload type");
				break;
		}
	}
	
	/**
	 * Check photo size, get upload server, upload photo and save them
	 *
	 * @param $data
	 * @return array - array of save photos results
	 * @throws SystemException
	 */
	public function upload($data)
	{
//		todo: this is a little kostyl. In cool variant we must separately do http-upload,
//		todo: and photo save run through execute method
//		todo: but now VK can't run savePhotoMethod through execute. Sadness ((

//		PARAMS set
		$photoSaveResults = array();
		

//		PROCESSED
		foreach ($data as $item)
		{
//			check EXISTING photo
			if (!array_key_exists($this->params['keyPhotoBx'], $item) || empty($item[$this->params['keyPhotoBx']]))
			{
				continue;
			}

//			GET upload server by type
			$getServerParams = array("group_id" => str_replace("-", "", $this->vkGroupId));
			if ($this->type == 'PRODUCT_MAIN_PHOTO')
			{
				$getServerParams += self::setUploadServerMainPhotoParams($item[$this->params['keyPhotoBx']]);
			}
			
			$uploadServer = $this->api->run($this->params['uploadServerMethod'], $getServerParams);
//			todo: may be this error in upload server response
			$this->logger->addLog("Get photo upload server", [
				'PARAMS' => $getServerParams,
				'RESULT' => $uploadServer,
			]);
			$uploadServer = $uploadServer["upload_url"];


//			UPLOAD photo by http
			$this->logger->addLog("Upload photo HTTP before", array(
				"UPLOAD_TYPE" => $this->type,
				"ITEM" => array_key_exists("BX_ID", $item) ?
					$item["BX_ID"] . ': ' . $item["NAME"] :
					$item["SECTION_ID"] . ': ' . $item["TITLE"],
				"PHOTO_BX_ID" => array_key_exists("PHOTO_MAIN_BX_ID",
					$item) ? $item["PHOTO_MAIN_BX_ID"] : $item["PHOTO_BX_ID"],
				"PHOTO_URL" => array_key_exists("PHOTO_MAIN_URL", $item) ? $item["PHOTO_MAIN_URL"] : $item["PHOTO_URL"],
				"PHOTOS" => $item["PHOTOS"]    //only for products
			));
			$uploadHttpResult = $this->uploadHttp($item, $uploadServer);
//			if not response - was be ERROR in http upload. SKIP saving
			if($uploadHttpResult === false)
			{
				if(!$photoSaveResults['errors'])
				{
					$photoSaveResults['errors'] = [];
				}
				$photoSaveResults['errors'][] = $item[$this->params['keyReference']];
				continue;
			}
			
			$savePhotoResult = $this->savePhoto($uploadHttpResult);
//			RESULT
			$photoSaveResults[$item[$this->params['keyReference']]] = array(
				$this->params['keyReference'] => $item[$this->params['keyReference']],
				$this->params['keyPhotoVk'] => $savePhotoResult[0]["id"],
			);
//			todo: photo mapping. po odnomu, navernoe, ved timer
		}
		
		return $photoSaveResults;
	}
	

	/**
	 * Execute http requst
	 *
	 * @param $uploadServer
	 * @param $params
	 * @return bool|string - result of http request
	 * @throws TimeIsOverException
	 */
	private function uploadHttp($data, $uploadServer)
	{
		$postParams = array(
			"url" => $data[$this->params['keyPhotoUrl']],
			"filename" => IO\Path::getName($data[$this->params['keyPhotoUrl']]),
			"param_name" => 'file',
		);

		$http = new HttpClient();

		$boundary = md5(rand() . time());
		$file = $this->getFile($postParams["url"]);

		$request = '--' . $boundary . "\r\n";
		$request .= 'Content-Disposition: form-data; name="' . $postParams["param_name"] . '"; filename="' . $postParams["filename"] . '"' . "\r\n";
		$request .= 'Content-Type: application/octet-stream' . "\r\n\r\n";
		$request .= $file . "\r\n";
		$request .= '--' . $boundary . "--\r\n";
		
		$http->setHeader('Content-type', 'multipart/form-data; boundary=' . $boundary);
		$http->setHeader('Content-length', \Bitrix\Main\Text\BinaryString::getLength($request));
		
		$this->logger->addLog("Upload photo HTTP params", [
			'SERVER' => $uploadServer,
			'PARAMS' => $postParams,
			'FILE_OK' => $file ? 'Y' : 'N',
		]);
		$result = $http->post($uploadServer, $request);

		$result = Json::decode($result);
		$this->logger->addLog("Upload photo HTTP response", $result);

//		check http upload status. If error - need skip this image
		if($result['error'])
		{
//			get error code from error string
			$this->logger->addError(
				explode(':', $result['error'])[0] . '_' . $this->type,
				$data[$this->params['keyReference']]
			);
			
			return false;
		}

//		check TIMER if set
		if($this->timer !== null && !$this->timer->check())
		{
			throw new TimeIsOverException();
		}
		
		return $result;
	}

	private function getFile(string $url)
	{
		$http = new HttpClient();

		return $http->get($url);
	}
	
	
	/**
	 * Save photo after http upload
	 *
	 * @param $uploadResult - array of http upload result
	 */
	private function savePhoto($uploadResult)
	{
		$photoSaveParams = array(
			"group_id" => str_replace('-', '', $this->vkGroupId),
			"photo" => $uploadResult["photo"],
			"server" => $uploadResult["server"],
			"hash" => $uploadResult["hash"],
		);
		
		// for product photo we need more params
		if ($this->params['saveMethod'] == "photos.saveMarketPhoto")
		{
			if (isset($uploadResult["crop_hash"]) && $uploadResult["crop_hash"])
				$photoSaveParams["crop_hash"] = $uploadResult["crop_hash"];
			if (isset($uploadResult["crop_data"]) && $uploadResult["crop_data"])
				$photoSaveParams["crop_data"] = $uploadResult["crop_data"];
		}
		
		return $this->api->run($this->params['saveMethod'], $photoSaveParams);
	}
	
	
	/**
	 * When loaded product main photo need additional params
	 *
	 * @param $photoId
	 * @return array
	 */
	private static function setUploadServerMainPhotoParams($photoId)
	{
		$result = array();
		$result["main_photo"] = 1;
		
		$photoParams = \CFile::GetFileArray($photoId);
		$w = $photoParams["WIDTH"];
		$h = $photoParams["HEIGHT"];
		
		if ($w >= $h)
		{
			$result["crop_x"] = ceil(($w + $h) / 2);
			$result["crop_y"] = 0;
			$result["crop_width"] = $h;
		}
		else
		{
			$result["crop_x"] = 0;
			$result["crop_y"] = ceil(($w + $h) / 2);
			$result["crop_width"] = $w;
		}
		
		return $result;
	}
}